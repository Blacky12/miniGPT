<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Services\ChatService;
use App\Http\Requests\StoreMessageRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;

class ConversationController extends Controller
{
    use AuthorizesRequests;

    private ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function index(Request $request)
    {
        $conversations = Conversation::where('user_id', Auth::id())
            ->withCount('messages')
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'messages_count' => $conversation->messages_count,
                    'last_message' => $conversation->messages->first()?->content,
                    'updated_at' => $conversation->updated_at,
                ];
            });

        if ($request->wantsJson()) {
            return response()->json(['conversations' => $conversations]);
        }

        return Inertia::render('Ask/Index', [
            'conversations' => $conversations,
        ]);
    }

    public function destroy(Conversation $conversation)
    {
        if ($conversation->user_id !== Auth::id()) {
            abort(403);
        }

        $conversation->delete();

        return redirect()->route('conversations.index')
            ->with('success', 'Conversation supprimée avec succès.');
    }

    public function show(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $conversations = Conversation::where('user_id', Auth::id())
            ->orderByDesc('updated_at')
            ->get();

        $messages = $conversation->messages()->orderBy('created_at')->get();

        return Inertia::render('Ask/Chat', [
            'conversations' => $conversations,
            'currentConversationId' => $conversation->id,
            'conversation' => $conversation,
            'messages' => $messages,
            'models' => $this->chatService->getModels(),
        ]);
    }

    public function storeMessage(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $request->validate([
            'message' => 'required|string',
            'model' => 'nullable|string',
        ]);

        // Sauvegarde du message utilisateur
        $userMessage = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => Auth::id(),
            'content' => $request->input('message'),
            'role' => 'user',
        ]);

        // Appel à l'IA pour générer la réponse
        $chatService = app(ChatService::class);
        $allMessages = $conversation->messages()->orderBy('created_at')->get()->map(function($msg) {
            return [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        })->toArray();
        $allMessages[] = [
            'role' => 'user',
            'content' => $request->input('message'),
        ];
        $model = $request->input('model') ?? $conversation->model;
        $aiResponse = $chatService->sendMessage($allMessages, $model);

        // Sauvegarde de la réponse IA
        Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => null,
            'content' => $aiResponse,
            'role' => 'assistant',
        ]);

        // Mise à jour du modèle si changé
        if ($model && $model !== $conversation->model) {
            $conversation->model = $model;
            $conversation->save();
        }

        // Génération automatique du titre si la conversation n'en a pas
        if (empty($conversation->title)) {
            try {
                $titlePrompt = "Donne-moi un titre court et pertinent pour cette conversation :\n"
                    . "Utilisateur : " . $request->input('message') . "\n"
                    . "IA : " . $aiResponse;

                $title = $this->chatService->sendMessage([
                    ['role' => 'user', 'content' => $titlePrompt]
                ], $model);

                $conversation->title = mb_substr(trim(explode("\n", $title)[0]), 0, 60);
                $conversation->save();
            } catch (\Exception $e) {
                // On ignore l'erreur de titre pour ne pas bloquer la conversation
            }
        }

        // Redirection vers la conversation
        return redirect()->route('conversations.show', $conversation->id);
    }

    public function storeSimple(Request $request)
    {
        $conversation = Conversation::create([
            'user_id' => Auth::id(),
            'title' => null,
            'model' => $request->input('model') ?? null,
        ]);
        return response()->json(['id' => $conversation->id]);
    }

    public function sendMessageStream(StoreMessageRequest $request, Conversation $conversation)
    {
        $this->authorize('update', $conversation);

        // Créer le message utilisateur
        Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'data' => $request->getText(),
                ],
            ],
        ]);

        // Préparer les messages pour l'API
        $apiMessages = $conversation
            ->messages()
            ->get()
            ->map(function (Message $message) {
                return [
                    'role' => $message->role,
                    'content' => is_array($message->content)
                        ? $message->content[0]['data']
                        : $message->content,
                ];
            })
            ->toArray();

        return response()->stream(function () use ($conversation, $apiMessages, $request) {
            $fullResponse = '';
            $model = $request->getModel() ?? $conversation->model ?? null;

            $stream = $this->chatService->stream(
                messages: $apiMessages,
                model: $model,
                temperature: 0.7
            );

            foreach ($stream as $response) {
                $content = $response->choices[0]->delta->content ?? '';
                $fullResponse .= $content;

                // Envoyer les données au format SSE
                echo "data: " . json_encode(['type' => 'content', 'content' => $content]) . "\n\n";
                ob_flush();
                flush();
            }

            // Créer le message de l'assistant avec la réponse complète
            Message::create([
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => [
                    [
                        'type' => 'text',
                        'data' => $fullResponse,
                    ],
                ],
            ]);

            // Génération automatique du titre si la conversation n'en a pas
            if (empty($conversation->title)) {
                try {
                    Log::info('Tentative de génération de titre pour la conversation', [
                        'conversation_id' => $conversation->id,
                        'user_message' => $request->getText(),
                        'ai_response_length' => strlen($fullResponse)
                    ]);

                    $titlePrompt = "Donne-moi un titre court et pertinent (maximum 60 caractères) pour cette conversation. Réponds uniquement avec le titre, sans guillemets ni ponctuation :\n"
                        . "Utilisateur : " . $request->getText() . "\n"
                        . "IA : " . mb_substr($fullResponse, 0, 500); // Limiter la réponse pour éviter les tokens excessifs

                    $title = $this->chatService->sendMessage([
                        ['role' => 'user', 'content' => $titlePrompt]
                    ], $model);

                    // Nettoyer le titre
                    $cleanTitle = trim($title);
                    $cleanTitle = preg_replace('/^["\']|["\']$/', '', $cleanTitle); // Enlever les guillemets
                    $cleanTitle = mb_substr($cleanTitle, 0, 60); // Limiter à 60 caractères

                    if (!empty($cleanTitle)) {
                        $conversation->title = $cleanTitle;
                        $conversation->save();

                        Log::info('Titre généré avec succès', [
                            'conversation_id' => $conversation->id,
                            'title' => $cleanTitle
                        ]);

                        // Envoyer le titre au format SSE
                        echo "data: " . json_encode(['type' => 'title', 'content' => $cleanTitle]) . "\n\n";
                        ob_flush();
                        flush();
                    } else {
                        Log::warning('Titre généré vide', [
                            'conversation_id' => $conversation->id,
                            'raw_title' => $title
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la génération du titre : ' . $e->getMessage(), [
                        'conversation_id' => $conversation->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
            'X-CSRF-TOKEN' => csrf_token(),
        ]);
    }
}
