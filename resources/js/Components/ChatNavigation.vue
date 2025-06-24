<script setup>
import { computed, ref } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import Logo from '@/Components/Logo.vue'
import axios from 'axios'

const page = usePage()
const conversation = computed(() => page.props.conversation)

const errorMsg = ref('')

const emit = defineEmits(['openInstructions'])

const logout = async () => {
  try {
    await axios.post('/logout')
    window.location.href = '/login'
  } catch (e) {
    errorMsg.value = "Erreur lors de la déconnexion."
  }
}
</script>

<template>
  <nav class="bg-white border-b px-6 py-3">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-6">
        <Logo size="sm" color="indigo">NexusAI</Logo>
        <h1 class="text-xl font-semibold text-gray-800">{{ conversation?.title || 'Nouvelle conversation' }}</h1>
      </div>

      <div class="flex items-center space-x-4">
        <!-- Bouton Instructions -->
        <button
          class="text-sm bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg border shadow-sm transition-all duration-200 flex items-center gap-2"
          @click="emit('openInstructions')"
        >
          <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Instructions
        </button>

        <!-- Bouton Accueil -->
        <button
          class="text-sm bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg border shadow-sm transition-all duration-200 flex items-center gap-2"
          @click="router.visit('/ask')"
        >
          <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          Accueil
        </button>

        <!-- Bouton de déconnexion -->
        <button
          @click="logout"
          class="text-sm bg-white hover:bg-red-50 text-gray-700 hover:text-red-600 px-4 py-2 rounded-lg border shadow-sm transition-all duration-200 flex items-center gap-2"
          title="Se déconnecter"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          Déconnexion
        </button>
      </div>
    </div>
    <div v-if="errorMsg" class="mt-2 text-sm text-red-600">{{ errorMsg }}</div>
  </nav>
</template>
