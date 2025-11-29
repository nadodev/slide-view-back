<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Presentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'thumbnail',
        'status',
        'settings',
        'slide_count',
        'last_edited_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'slide_count' => 'integer',
            'last_edited_at' => 'datetime',
        ];
    }

    /**
     * Usuário dono da apresentação
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Slides da apresentação
     */
    public function slides(): HasMany
    {
        return $this->hasMany(Slide::class)->orderBy('order');
    }

    /**
     * Atualiza a contagem de slides
     */
    public function updateSlideCount(): void
    {
        $this->update([
            'slide_count' => $this->slides()->count(),
            'last_edited_at' => now(),
        ]);
    }

    /**
     * Scope para apresentações do usuário
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para apresentações por status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para apresentações recentes
     */
    public function scopeRecent($query)
    {
        return $query->orderByDesc('last_edited_at')->orderByDesc('updated_at');
    }
}

