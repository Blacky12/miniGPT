<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    public const ROLE_USER = 'user';
    public const ROLE_ASSISTANT = 'assistant';
    public const ROLE_SYSTEM = 'system';

    public const TYPE_TEXT = 'text';

    protected $fillable = [
        'conversation_id',
        'user_id',
        'role',
        'content'
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function toApiFormat(): array
    {
        return [
            'role' => $this->role,
            'content' => collect($this->content)
                ->where('type', self::TYPE_TEXT)
                ->pluck('data')
                ->implode("\n"),
        ];
    }
}
