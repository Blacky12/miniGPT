<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInstruction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'content',
        'is_active',
        'order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir les instructions actives d'un utilisateur par type
     */
    public static function getActiveByType(int $userId, string $type): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('user_id', $userId)
            ->where('type', $type)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    /**
     * Obtenir toutes les instructions actives d'un utilisateur
     */
    public static function getAllActive(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('order')
            ->get();
    }
}
