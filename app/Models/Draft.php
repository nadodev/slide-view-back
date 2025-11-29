<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Draft extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'presentation_id',
        'type',
        'title',
        'content',
        'metadata',
        'last_saved_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'last_saved_at' => 'datetime',
        ];
    }

    /**
     * Usuário dono do rascunho
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Apresentação relacionada (se existir)
     */
    public function presentation(): BelongsTo
    {
        return $this->belongsTo(Presentation::class);
    }

    /**
     * Scope para rascunhos de apresentação
     */
    public function scopePresentations($query)
    {
        return $query->where('type', 'presentation');
    }

    /**
     * Scope para rascunhos recentes
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('last_saved_at', 'desc');
    }
}

