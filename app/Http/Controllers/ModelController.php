<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Conversation;

class ModelController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'model' => 'required|string',
            'conversation_id' => 'nullable|integer|exists:conversations,id',
        ]);

        $user = Auth::user();
        $user->preferred_model = $request->input('model');
        $user->save();

        if ($request->filled('conversation_id')) {
            $conversation = Conversation::where('id', $request->input('conversation_id'))
                ->where('user_id', $user->id)
                ->first();
            if ($conversation) {
                $conversation->model = $request->input('model');
                $conversation->save();
            }
        }

        return response()->json(['success' => true]);
    }
}
