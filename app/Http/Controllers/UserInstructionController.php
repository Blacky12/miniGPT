<?php

namespace App\Http\Controllers;

use App\Models\UserInstruction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserInstructionController extends Controller
{
    /**
     * Afficher la liste des instructions de l'utilisateur
     */
    public function index()
    {
        $instructions = UserInstruction::where('user_id', Auth::id())
            ->orderBy('type')
            ->orderBy('order')
            ->get()
            ->groupBy('type');

        return Inertia::render('UserInstructions/Index', [
            'instructions' => $instructions,
            'types' => [
                'about_me' => 'À propos de vous',
                'assistant_behavior' => 'Comportement de l\'assistant',
                'custom_commands' => 'Commandes personnalisées'
            ]
        ]);
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return Inertia::render('UserInstructions/Create', [
            'types' => [
                'about_me' => 'À propos de vous',
                'assistant_behavior' => 'Comportement de l\'assistant',
                'custom_commands' => 'Commandes personnalisées'
            ]
        ]);
    }

    /**
     * API: Obtenir les instructions actives d'un utilisateur
     */
    public function getActive(Request $request)
    {
        $type = $request->get('type');

        if ($type) {
            $instructions = UserInstruction::getActiveByType(Auth::id(), $type);
        } else {
            $instructions = UserInstruction::getAllActive(Auth::id());
        }

        return response()->json(['instructions' => $instructions]);
    }

    /**
     * Stocker une nouvelle instruction
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:about_me,assistant_behavior,custom_commands',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_active' => 'boolean',
            'order' => 'integer|min:0'
        ]);

        // Vérifier s'il existe déjà une instruction pour ce type
        $existingInstruction = UserInstruction::where('user_id', Auth::id())
            ->where('type', $request->type)
            ->first();

        if ($existingInstruction) {
            return response()->json([
                'message' => 'Une instruction de ce type existe déjà'
            ], 422);
        }

        $instruction = UserInstruction::create([
            'user_id' => Auth::id(),
            'type' => $request->type,
            'title' => $request->title,
            'content' => $request->content,
            'is_active' => $request->get('is_active', true),
            'order' => $request->get('order', 0)
        ]);

        return response()->json($instruction);
    }

    /**
     * Afficher une instruction spécifique
     */
    public function show(UserInstruction $userInstruction)
    {
        if ($userInstruction->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('UserInstructions/Show', [
            'instruction' => $userInstruction
        ]);
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(UserInstruction $userInstruction)
    {
        if ($userInstruction->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('UserInstructions/Edit', [
            'instruction' => $userInstruction,
            'types' => [
                'about_me' => 'À propos de vous',
                'assistant_behavior' => 'Comportement de l\'assistant',
                'custom_commands' => 'Commandes personnalisées'
            ]
        ]);
    }

    /**
     * Mettre à jour une instruction
     */
    public function update(Request $request, UserInstruction $userInstruction)
    {
        if ($userInstruction->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'type' => 'required|string|in:about_me,assistant_behavior,custom_commands',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_active' => 'boolean',
            'order' => 'integer|min:0'
        ]);

        // Vérifier si on essaie de changer le type et s'il existe déjà une instruction pour ce nouveau type
        if ($request->type !== $userInstruction->type) {
            $existingInstruction = UserInstruction::where('user_id', Auth::id())
                ->where('type', $request->type)
                ->where('id', '!=', $userInstruction->id)
                ->first();

            if ($existingInstruction) {
                return response()->json([
                    'message' => 'Une instruction de ce type existe déjà'
                ], 422);
            }
        }

        $userInstruction->update([
            'type' => $request->type,
            'title' => $request->title,
            'content' => $request->content,
            'is_active' => $request->get('is_active', true),
            'order' => $request->get('order', 0)
        ]);

        return response()->json($userInstruction);
    }

    /**
     * Supprimer une instruction
     */
    public function destroy(UserInstruction $userInstruction)
    {
        if ($userInstruction->user_id !== Auth::id()) {
            abort(403);
        }

        $userInstruction->delete();

        return redirect()->route('user-instructions.index')
            ->with('success', 'Instruction supprimée avec succès.');
    }
}
