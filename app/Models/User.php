<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'plan_id',
        'plan_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'plan_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Plano do usuário
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Apresentações do usuário
     */
    public function presentations(): HasMany
    {
        return $this->hasMany(Presentation::class);
    }

    /**
     * Verifica se o usuário é admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Verifica se o usuário é um usuário comum
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Verifica se o plano do usuário está ativo (não expirou)
     */
    public function hasPlanActive(): bool
    {
        if (!$this->plan_id) {
            return false;
        }

        if (!$this->plan_expires_at) {
            return true; // Plano sem data de expiração = ativo
        }

        return $this->plan_expires_at->isFuture();
    }

    /**
     * Verifica se o usuário tem um plano premium (não gratuito)
     */
    public function hasPremiumPlan(): bool
    {
        return $this->plan && !$this->plan->isFree() && $this->hasPlanActive();
    }

    /**
     * Verifica se o usuário pode criar mais apresentações baseado no plano
     */
    public function canCreatePresentation(): bool
    {
        $plan = $this->plan;
        
        // Se não tem plano, permite criar (plano free padrão)
        if (!$plan) {
            return $this->presentations()->count() < 3; // Limite free padrão
        }

        // Se o plano não tem limite (null), permite
        if ($plan->max_presentations === null) {
            return true;
        }

        return $this->presentations()->count() < $plan->max_presentations;
    }
}
