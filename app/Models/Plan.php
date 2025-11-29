<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_cycle',
        'features',
        'max_slides',
        'max_presentations',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'features' => 'array',
            'max_slides' => 'integer',
            'max_presentations' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Usuários que possuem este plano
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Verifica se o plano é gratuito
     */
    public function isFree(): bool
    {
        return $this->price == 0;
    }

    /**
     * Scope para planos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

