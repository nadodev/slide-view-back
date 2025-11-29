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
        'is_public',
        'share_token',
        'allow_embed',
        'shared_at',
        'view_count',
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
            'shared_at' => 'datetime',
            'is_public' => 'boolean',
            'allow_embed' => 'boolean',
            'view_count' => 'integer',
        ];
    }

    /**
     * Gerar token de compartilhamento
     */
    public function generateShareToken(): string
    {
        $token = bin2hex(random_bytes(16));
        $this->update([
            'share_token' => $token,
            'shared_at' => now(),
        ]);
        return $token;
    }

    /**
     * Habilitar compartilhamento público
     */
    public function enablePublicSharing(bool $allowEmbed = false): void
    {
        if (!$this->share_token) {
            $this->generateShareToken();
        }
        
        $this->update([
            'is_public' => true,
            'allow_embed' => $allowEmbed,
            'shared_at' => now(),
        ]);
    }

    /**
     * Desabilitar compartilhamento público
     */
    public function disablePublicSharing(): void
    {
        $this->update([
            'is_public' => false,
            'allow_embed' => false,
        ]);
    }

    /**
     * Incrementar contador de visualizações
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Scope para apresentações públicas
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
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

