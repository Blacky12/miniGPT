<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import { ref, computed, watch, nextTick } from 'vue'
import MarkdownIt from 'markdown-it'
import hljs from 'highlight.js'
import 'highlight.js/styles/github.css'
import SidebarConversations from '@/Components/SidebarConversations.vue'
import ChatNavigation from '@/Components/ChatNavigation.vue'
import UserInstructionsModal from '@/Components/UserInstructionsModal.vue'
import Logo from '@/Components/Logo.vue'
import axios from 'axios'

const page = usePage()
const flash = computed(() => page.props.flash)
const models = computed(() => page.props.models)
const conversation = computed(() => page.props.conversation)
const messages = ref(page.props.messages ?? [])
const conversations = computed(() => page.props.conversations ?? [])
const currentConversationId = computed(() => page.props.currentConversationId)
const user = computed(() => page.props.auth.user)

const selectedModel = ref(conversation.value?.model || user.value?.preferred_model || models.value[0]?.id)
const isStreaming = ref(false)
const newMessage = ref('')
const errorMsg = ref('')
const messagesEnd = ref(null)
const showInstructionsModal = ref(false)

const md = new MarkdownIt({
  highlight(str, lang) {
    if (lang && hljs.getLanguage(lang)) {
      try {
        return `<pre class="hljs"><code>${hljs.highlight(str, { language: lang, ignoreIllegals: true }).value}</code></pre>`
      } catch (_) {}
    }
    return `<pre class="hljs"><code>${md.utils.escapeHtml(str)}</code></pre>`
  }
})

// Scroll auto en bas
const scrollToBottom = async () => {
  await nextTick()
  if (messagesEnd.value) {
    messagesEnd.value.scrollIntoView({ behavior: 'smooth' })
  }
}

watch(messages, scrollToBottom)

// Synchronisation du modèle côté user + conversation
watch(selectedModel, async (model) => {
  try {
    await axios.post('/model', {
      model,
      conversation_id: conversation.value?.id ?? null
    })
  } catch (e) {
    errorMsg.value = "Erreur lors de la sauvegarde du modèle."
  }
})

const sendMessage = async () => {
  if (!newMessage.value.trim() || isStreaming.value) return

  errorMsg.value = ''
  isStreaming.value = true

  try {
    // Ajouter le message de l'utilisateur
    const userMessage = {
      id: Date.now(),
      conversation_id: conversation.value.id,
      user_id: user.value.id,
      content: [
        {
          type: 'text',
          data: newMessage.value
        }
      ],
      role: 'user',
      created_at: new Date().toISOString()
    }
    messages.value.push(userMessage)

    // Ajouter un message vide pour l'assistant
    const assistantMessage = {
      id: Date.now() + 1,
      conversation_id: conversation.value.id,
      user_id: null,
      content: [
        {
          type: 'text',
          data: ''
        }
      ],
      role: 'assistant',
      created_at: new Date().toISOString()
    }
    messages.value.push(assistantMessage)
    await scrollToBottom()

    // Récupérer le token CSRF actuel
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (!token) {
      throw new Error('CSRF token not found')
    }

    // Démarrer le stream
    const response = await fetch(`/conversations/${conversation.value.id}/stream`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        text: newMessage.value,
        model: selectedModel.value
      })
    })

    // Vérifier et mettre à jour le token CSRF si présent dans les headers
    const newToken = response.headers.get('X-CSRF-TOKEN')
    if (newToken) {
      document.querySelector('meta[name="csrf-token"]').setAttribute('content', newToken)
    }

    const reader = response.body.getReader()
    const decoder = new TextDecoder()

    while (true) {
      const { value, done } = await reader.read()
      if (done) break

      const text = decoder.decode(value)
      // Traiter les événements SSE
      const lines = text.split('\n')
      for (const line of lines) {
        if (line.startsWith('data: ')) {
          try {
            const data = JSON.parse(line.slice(6))

            if (data.type === 'content') {
              // Mettre à jour le dernier message de l'assistant
              const lastMessage = messages.value[messages.value.length - 1]
              if (lastMessage && lastMessage.role === 'assistant') {
                lastMessage.content[0].data += data.content
              }
            } else if (data.type === 'title') {
              // Mettre à jour le titre de la conversation
              conversation.value.title = data.content
            }
          } catch (e) {
            console.error('Erreur parsing SSE:', e)
          }
        }
      }
      await scrollToBottom()
    }

    newMessage.value = ''
  } catch (error) {
    console.error('Erreur lors de l\'envoi du message:', error)
    errorMsg.value = "Une erreur est survenue lors de l'envoi du message."
    // Supprimer le dernier message si c'est celui de l'assistant
    if (messages.value[messages.value.length - 1]?.role === 'assistant') {
      messages.value.pop()
    }
  } finally {
    isStreaming.value = false
  }
}

const openConversation = (id) => {
  router.visit(`/conversations/${id}`)
}

const newConversation = async () => {
  try {
    const res = await axios.post('/conversations')
    router.visit(`/conversations/${res.data.id}`)
  } catch (e) {
    errorMsg.value = "Erreur lors de la création d'une nouvelle conversation."
  }
}

const openInstructions = () => {
  showInstructionsModal.value = true
}

const closeInstructions = () => {
  showInstructionsModal.value = false
}
</script>

<template>
  <div class="flex bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <SidebarConversations />
    <main class="flex-1 flex flex-col">
      <ChatNavigation @openInstructions="openInstructions" />

      <div class="flex-1 overflow-y-auto px-4 py-6">
        <!-- Messages -->
        <div class="max-w-4xl mx-auto space-y-6">
          <template v-for="message in messages" :key="message.id">
            <div class="flex items-center gap-3 mb-4">
              <div :class="[
                'w-8 h-8 rounded-full flex items-center justify-center',
                message.role === 'assistant' ? 'bg-indigo-100 text-indigo-600' : 'bg-indigo-600 text-white'
              ]">
                <svg v-if="message.role === 'assistant'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <span class="font-medium">{{ message.user_id === user.id ? 'Vous' : 'NexusAI' }}</span>
            </div>
            <div class="prose dark:prose-invert prose-slate max-w-none" v-html="md.render(typeof message.content === 'string' ? message.content : Array.isArray(message.content) ? message.content[0]?.data || '' : JSON.stringify(message.content))"></div>
          </template>
          <div ref="messagesEnd"></div>
        </div>
      </div>

      <!-- Zone de saisie -->
      <div class="border-t bg-white p-4">
        <div class="max-w-4xl mx-auto">
          <!-- Sélecteur de modèle -->
          <div class="flex justify-end mb-4">
            <select
              v-model="selectedModel"
              class="text-sm border rounded-lg px-4 py-2 shadow-sm bg-white hover:bg-gray-50 transition-all duration-200"
              :disabled="isStreaming"
            >
              <option v-for="model in models" :key="model.id" :value="model.id">
                {{ model.name }}
              </option>
            </select>
          </div>

          <form @submit.prevent="sendMessage">
            <div class="relative">
              <textarea
                v-model="newMessage"
                rows="3"
                class="w-full border rounded-xl px-4 py-3 pr-12 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"
                placeholder="Écrivez votre message ici..."
                :disabled="isStreaming"
              ></textarea>
              <button
                type="submit"
                class="absolute bottom-3 right-3 text-indigo-600 hover:text-indigo-800 disabled:opacity-50 disabled:cursor-not-allowed p-2 rounded-lg hover:bg-indigo-50 transition-all duration-200"
                :disabled="!newMessage.trim() || isStreaming"
              >
                <svg v-if="!isStreaming" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                <svg v-else class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </button>
            </div>
            <div v-if="errorMsg" class="mt-2 text-red-600 text-sm">{{ errorMsg }}</div>
          </form>
        </div>
      </div>
    </main>

    <!-- Modal des instructions -->
    <UserInstructionsModal
      v-if="showInstructionsModal"
      @close="closeInstructions"
    />
  </div>
</template>

<style>
.prose pre {
  background-color: #f8fafc;
  border-radius: 0.5rem;
  padding: 1rem;
  margin: 1rem 0;
  overflow-x: auto;
}

.prose code {
  color: #475569;
  background-color: #f1f5f9;
  padding: 0.2rem 0.4rem;
  border-radius: 0.25rem;
  font-size: 0.875em;
}

.prose pre code {
  color: inherit;
  background-color: transparent;
  padding: 0;
}
</style>
