<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class AskController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $models = (new \App\Services\ChatService())->getModels();
        $selectedModel = \App\Services\ChatService::DEFAULT_MODEL;

        // Récupérer la dernière conversation pour l'afficher dans la vue si elle existe
        $lastConversation = \App\Models\Conversation::where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->first();

        return Inertia::render('Ask/Index', [
            'models' => $models,
            'selectedModel' => $selectedModel,
            'lastConversation' => $lastConversation
        ]);
    }

    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'model' => 'required|string',
        ]);

        try {
            // 1. Créer une nouvelle conversation
            $conversation = \App\Models\Conversation::create([
                'user_id' => Auth::id(),
                'title' => null, // On pourra générer le titre plus tard
                'model' => $request->model,
            ]);

            // 2. Sauvegarder le message utilisateur
            $userMessage = \App\Models\Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => Auth::id(),
                'content' => $request->message,
                'role' => 'user',
            ]);

            // 3. Appeler l'IA et sauvegarder la réponse
            $messages = [[
                'role' => 'user',
                'content' => $request->message,
            ]];

            $response = (new \App\Services\ChatService())->sendMessage(
                messages: $messages,
                model: $request->model
            );

            \App\Models\Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => null,
                'content' => $response,
                'role' => 'assistant',
            ]);

            // Génération automatique du titre si la conversation n'en a pas
            if (empty($conversation->title)) {
                try {
                    $titlePrompt = "Donne-moi un titre court et pertinent pour cette conversation :\n"
                        . "Utilisateur : " . $request->message . "\n"
                        . "IA : " . $response;

                    $title = (new \App\Services\ChatService())->sendMessage([
                        ['role' => 'user', 'content' => $titlePrompt]
                    ], $request->model);

                    $conversation->title = mb_substr(trim(explode("\n", $title)[0]), 0, 60);
                    $conversation->save();
                } catch (\Exception $e) {
                    // On ignore l'erreur de titre pour ne pas bloquer la conversation
                }
            }

            // 4. Rediriger vers la page de la conversation
            return redirect()->route('conversations.show', $conversation->id);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }


}
