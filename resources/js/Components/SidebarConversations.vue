<script setup>
import { computed, ref, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import Logo from '@/Components/Logo.vue'
import axios from 'axios'

const page = usePage()
const conversations = computed(() => page.props.conversations)
const currentConversationId = computed(() => page.props.currentConversationId)
const conversation = computed(() => page.props.conversation)
const errorMsg = ref('')
const isLoading = ref(false)

// Surveiller les changements de titre de la conversation courante
watch(() => conversation.value?.title, async (newTitle) => {
  if (newTitle && conversations.value) {
    // Mettre à jour le titre dans la liste des conversations
    const conv = conversations.value.find(c => c.id === currentConversationId.value)
    if (conv) {
      conv.title = newTitle
    }
  }
})

const openConversation = (id) => {
  router.visit(`/conversations/${id}`)
}

const newConversation = async () => {
  isLoading.value = true
  try {
    const res = await axios.post('/conversations', {}, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    router.visit(`/conversations/${res.data.id}`)
  } catch (e) {
    errorMsg.value = "Erreur lors de la création d'une nouvelle conversation."
  } finally {
    isLoading.value = false
  }
}

const deleteConversation = async (id) => {
  if (!id) {
    errorMsg.value = "ID de conversation manquant.";
    return;
  }
  if (!confirm('Voulez-vous vraiment supprimer cette conversation ?')) return
  isLoading.value = true
  try {
    router.delete(`/conversations/${id}`, {
      onSuccess: () => {
        errorMsg.value = ''
        isLoading.value = false
      },
      onError: () => {
        errorMsg.value = "Erreur lors de la suppression de la conversation."
        isLoading.value = false
      }
    })
  } catch (e) {
    errorMsg.value = "Erreur lors de la suppression de la conversation."
    isLoading.value = false
  }
}
</script>

<template>
  <aside class="w-72 bg-white border-r flex flex-col">
    <div class="px-6 py-4 border-b">
      <div class="flex items-center justify-between mb-4">
        <Logo size="sm" color="indigo">NexusAI</Logo>
        <button
          @click="newConversation"
          class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg w-8 h-8 flex items-center justify-center shadow-md transition-all duration-200 disabled:opacity-50 hover:scale-105"
          :disabled="isLoading"
          title="Nouvelle conversation"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
        </button>
      </div>
      <div class="text-sm text-gray-500">Vos conversations</div>
    </div>

    <div class="flex-1 overflow-y-auto px-3 py-4">
      <ul class="space-y-2">
        <li
          v-for="conv in conversations"
          :key="conv.id"
          :class="[
            'p-3 rounded-xl cursor-pointer transition-all duration-200 flex items-center justify-between group',
            conv.id === currentConversationId
              ? 'bg-indigo-50 text-indigo-700'
              : 'hover:bg-gray-50'
          ]"
        >
          <div class="truncate flex-1 flex items-center gap-3" @click="openConversation(conv.id)">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <span>{{ conv.title || 'Sans titre' }}</span>
          </div>
          <button
            class="opacity-0 group-hover:opacity-100 ml-2 text-gray-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition-all duration-200"
            title="Supprimer la conversation"
            @click.stop="deleteConversation(conv.id)"
            :disabled="isLoading"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </li>
      </ul>
      <div v-if="conversations.length === 0" class="text-center py-8 text-gray-500">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <p class="text-sm">Aucune conversation pour le moment.</p>
        <p class="text-xs text-gray-400 mt-1">Créez-en une nouvelle pour commencer !</p>
      </div>
    </div>
    <div v-if="errorMsg" class="p-4 mx-3 mb-3 text-sm text-red-600 bg-red-50 rounded-lg">{{ errorMsg }}</div>
    <div class="p-4 text-xs text-gray-400 text-center border-t">NexusAI © 2024</div>
  </aside>
</template>
