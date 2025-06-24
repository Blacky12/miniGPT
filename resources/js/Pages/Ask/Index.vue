<script setup>
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import SidebarConversations from '@/Components/SidebarConversations.vue'
import UserInstructionsModal from '@/Components/UserInstructionsModal.vue'
import ChatNavigation from '@/Components/ChatNavigation.vue'
import Logo from '@/Components/Logo.vue'
import axios from 'axios'

const page = usePage()
const conversations = page.props.conversations || []
const isLoading = ref(false)
const errorMsg = ref('')
const showInstructionsModal = ref(false)

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

      <div class="flex-1 flex items-center justify-center p-8">
        <div class="w-full max-w-2xl mx-auto text-center">
          <div class="mb-8 flex justify-center">
            <Logo size="xl" color="indigo">NexusAI</Logo>
          </div>

          <p class="text-gray-600 mb-12 text-lg">Votre assistant intelligent pour des conversations enrichissantes</p>

          <div class="flex flex-col gap-4 items-center">
            <button
              @click="newConversation"
              class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-8 py-4 rounded-xl shadow-lg transition-all duration-200 transform hover:scale-105 disabled:opacity-50 w-full max-w-md flex items-center justify-center gap-2"
              :disabled="isLoading"
            >
              <svg v-if="isLoading" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>Nouvelle conversation</span>
            </button>

            <button
              @click="openInstructions"
              class="bg-white hover:bg-gray-50 text-gray-700 font-semibold px-8 py-4 rounded-xl shadow-md transition-all duration-200 w-full max-w-md flex items-center justify-center gap-3 border border-gray-200"
            >
              <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
              </svg>
              Gérer les instructions
            </button>
          </div>

          <div v-if="errorMsg" class="mt-6 text-red-600 bg-red-50 p-4 rounded-lg">{{ errorMsg }}</div>
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
