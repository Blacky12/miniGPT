<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import axios from 'axios'

const emit = defineEmits(['close'])

const types = {
  about_me: 'À propos de vous',
  assistant_behavior: 'Comportement de l\'assistant',
  custom_commands: 'Commandes personnalisées'
}

const selectedType = ref('about_me')
const instructions = ref([])
const showForm = ref(false)
const errorMessage = ref('')
const isLoading = ref(false)

const form = useForm({
  id: null,
  type: 'about_me',
  title: '',
  content: '',
  is_active: true,
  order: 0
})

// Charger les instructions existantes
const loadInstructions = async () => {
  try {
    const response = await axios.get('/user-instructions-api/active')
    instructions.value = response.data.instructions
  } catch (error) {
    console.error('Erreur lors du chargement des instructions:', error)
    errorMessage.value = 'Erreur lors du chargement des instructions'
  }
}

// Obtenir l'instruction actuelle pour le type sélectionné
const currentInstruction = computed(() => {
  return instructions.value.find(instruction => instruction.type === selectedType.value)
})

// Éditer l'instruction du type sélectionné
const editCurrentInstruction = () => {
  const instruction = currentInstruction.value
  if (instruction) {
    form.id = instruction.id
    form.type = instruction.type
    form.title = instruction.title
    form.content = instruction.content
    form.is_active = instruction.is_active
    form.order = instruction.order
  } else {
    form.id = null
    form.type = selectedType.value
    form.title = types[selectedType.value]
    form.content = ''
    form.is_active = true
    form.order = 0
  }
  showForm.value = true
  errorMessage.value = ''
}

// Sauvegarder l'instruction
const saveInstruction = async () => {
  errorMessage.value = ''
  isLoading.value = true

  try {
    const data = {
      type: form.type,
      title: form.title,
      content: form.content,
      is_active: form.is_active,
      order: form.order
    }

    const response = form.id
      ? await axios.put(`/user-instructions/${form.id}`, data)
      : await axios.post('/user-instructions', data)

    // Mettre à jour l'instruction dans la liste locale
    if (form.id) {
      const index = instructions.value.findIndex(i => i.id === form.id)
      if (index !== -1) {
        instructions.value[index] = response.data
      }
    } else {
      instructions.value.push(response.data)
    }

    showForm.value = false
    form.reset()
  } catch (error) {
    console.error('Erreur lors de la sauvegarde:', error)
    errorMessage.value = 'Erreur lors de la sauvegarde. Veuillez réessayer.'
  } finally {
    isLoading.value = false
  }
}

// Retour à la liste sans sauvegarder
const backToList = () => {
  showForm.value = false
  form.reset()
  errorMessage.value = ''
}

onMounted(() => {
  loadInstructions()
})
</script>

<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Instructions Utilisateur</h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Message d'erreur global -->
      <div v-if="errorMessage" class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
        {{ errorMessage }}
      </div>

      <!-- Sélection du type -->
      <div class="mb-6">
        <div class="flex space-x-2">
          <button
            v-for="(label, type) in types"
            :key="type"
            @click="selectedType = type; showForm = false"
            class="px-4 py-2 rounded-full text-sm"
            :class="selectedType === type ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
          >
            {{ label }}
          </button>
        </div>
      </div>

      <!-- Affichage/Edition de l'instruction -->
      <div v-if="!showForm" class="space-y-4 mb-6">
        <!-- Instruction existante -->
        <div v-if="currentInstruction" class="border rounded-lg p-4">
          <h3 class="font-medium text-gray-900 mb-2">{{ currentInstruction.title }}</h3>
          <p class="text-gray-600 text-sm mb-4">{{ currentInstruction.content }}</p>
          <button
            @click="editCurrentInstruction"
            class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            Modifier cette instruction
          </button>
        </div>

        <!-- Pas d'instruction existante -->
        <div v-else class="text-center py-8">
          <p class="text-gray-500 mb-4">Aucune instruction pour "{{ types[selectedType] }}"</p>
          <button
            @click="editCurrentInstruction"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            Créer l'instruction
          </button>
        </div>
      </div>

      <!-- Formulaire d'édition -->
      <form v-if="showForm" @submit.prevent="saveInstruction" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Titre
          </label>
          <input
            v-model="form.title"
            type="text"
            class="w-full border rounded-md p-2"
            placeholder="Donnez un titre à votre instruction"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Contenu de l'instruction
          </label>
          <textarea
            v-model="form.content"
            rows="6"
            class="w-full border rounded-md p-2"
            placeholder="Décrivez vos préférences, votre contexte ou vos besoins..."
            required
          ></textarea>
        </div>

        <div class="flex justify-end space-x-2 mt-4">
          <button
            type="button"
            @click="backToList"
            class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-50"
          >
            Retour
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
            :disabled="isLoading || !form.title || !form.content"
          >
            <span v-if="isLoading">Sauvegarde en cours...</span>
            <span v-else>Sauvegarder</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
