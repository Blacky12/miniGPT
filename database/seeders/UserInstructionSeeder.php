<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserInstruction;

class UserInstructionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer le premier utilisateur ou créer un utilisateur de test
        $user = User::first();

        if (!$user) {
            $this->command->info('Aucun utilisateur trouvé. Créez d\'abord un utilisateur.');
            return;
        }

        $instructions = [
            [
                'type' => 'about_me',
                'title' => 'Profil développeur',
                'content' => 'Je suis développeur web avec une expertise en PHP/Laravel, Vue.js et JavaScript. Je travaille sur des applications web modernes et j\'apprécie les explications techniques détaillées avec des exemples de code.',
                'order' => 0
            ],
            [
                'type' => 'assistant_behavior',
                'title' => 'Style de communication',
                'content' => 'Utilise un ton professionnel mais accessible. Préfère les réponses structurées avec des listes à puces quand c\'est pertinent. Inclus toujours des exemples pratiques pour illustrer les concepts techniques. Limite les explications à 2-3 paragraphes maximum.',
                'order' => 0
            ],
            [
                'type' => 'custom_commands',
                'title' => 'Raccourcis de développement',
                'content' => 'Quand je mentionne "debug", aide-moi à identifier et résoudre les problèmes de code. Pour "optimiser", suggère des améliorations de performance. Avec "refactor", propose des améliorations de structure et de lisibilité du code. Pour "test", aide-moi à écrire des tests unitaires.',
                'order' => 0
            ],
            [
                'type' => 'about_me',
                'title' => 'Préférences d\'apprentissage',
                'content' => 'J\'apprends mieux avec des exemples concrets et des explications étape par étape. J\'apprécie quand on me montre les bonnes pratiques et les pièges à éviter.',
                'order' => 1
            ]
        ];

        foreach ($instructions as $instruction) {
            UserInstruction::create([
                'user_id' => $user->id,
                'type' => $instruction['type'],
                'title' => $instruction['title'],
                'content' => $instruction['content'],
                'is_active' => true,
                'order' => $instruction['order']
            ]);
        }

        $this->command->info('Instructions personnalisées créées avec succès pour l\'utilisateur: ' . $user->name);
    }
}
