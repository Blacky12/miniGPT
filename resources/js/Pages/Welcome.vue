<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Logo from '@/Components/Logo.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <Head title="Bienvenue sur NexusAI" />
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-white dark:from-gray-900 dark:to-gray-800">
        <div class="relative min-h-screen flex flex-col">
            <!-- Navigation -->
            <nav class="w-full px-6 py-4 bg-white/80 backdrop-blur-sm border-b dark:bg-gray-900/80 dark:border-gray-800">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <Logo size="lg" color="indigo">NexusAI</Logo>

                    <div v-if="canLogin" class="flex items-center gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('ask.index')"
                            class="px-6 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-all duration-200 font-medium"
                        >
                            Accéder à l'application
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-6 py-2.5 rounded-lg border border-gray-200 hover:bg-gray-50 transition-all duration-200 dark:border-gray-700 dark:hover:bg-gray-800"
                            >
                                Connexion
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-6 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-all duration-200 font-medium"
                            >
                                Inscription
                            </Link>
                        </template>
                    </div>
                </div>
            </nav>

            <!-- Hero Section -->
            <main class="flex-1 flex items-center">
                <div class="max-w-7xl mx-auto px-6 py-16 w-full">
                    <div class="grid lg:grid-cols-2 gap-12 items-center">
                        <div>
                            <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                                Découvrez la puissance de l'IA conversationnelle
                            </h1>
                            <p class="text-lg text-gray-600 dark:text-gray-300 mb-8">
                                NexusAI est votre assistant intelligent, capable de comprendre, d'apprendre et d'interagir naturellement avec vous. Explorez de nouvelles possibilités grâce à notre technologie de pointe.
                            </p>
                            <div class="flex gap-4">
                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="px-8 py-3 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-all duration-200 font-medium text-lg"
                                >
                                    Commencer gratuitement
                                </Link>
                                <Link
                                    :href="route('login')"
                                    class="px-8 py-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-all duration-200 font-medium text-lg dark:border-gray-700 dark:hover:bg-gray-800"
                                >
                                    En savoir plus
                                </Link>
                            </div>
                        </div>
                        <div class="relative">
                            <div class="aspect-square rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 opacity-10 absolute inset-0 blur-3xl"></div>
                            <div class="relative">
                                <div class="p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center gap-4 mb-6">
                                        <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Assistant IA</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">En ligne</p>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="flex gap-4 items-start">
                                            <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center shrink-0">
                                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <div class="bg-gray-100 dark:bg-gray-700 rounded-2xl px-4 py-2 text-sm text-gray-600 dark:text-gray-300">
                                                Comment puis-je vous aider aujourd'hui ?
                                            </div>
                                        </div>
                                        <div class="flex gap-4 items-start justify-end">
                                            <div class="bg-indigo-600 rounded-2xl px-4 py-2 text-sm text-white">
                                                Je voudrais en savoir plus sur NexusAI.
                                            </div>
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center shrink-0">
                                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex gap-4 items-start">
                                            <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center shrink-0">
                                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <div class="bg-gray-100 dark:bg-gray-700 rounded-2xl px-4 py-2 text-sm text-gray-600 dark:text-gray-300">
                                                Je suis là pour répondre à toutes vos questions et vous aider dans vos tâches quotidiennes.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="w-full px-6 py-4 bg-white/80 backdrop-blur-sm border-t dark:bg-gray-900/80 dark:border-gray-800">
                <div class="max-w-7xl mx-auto text-center text-sm text-gray-500 dark:text-gray-400">
                    NexusAI © 2024 - Propulsé par Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
                </div>
            </footer>
        </div>
    </div>
</template>
