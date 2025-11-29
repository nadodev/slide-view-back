<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'thumbnail',
        'icon',
        'slides',
        'settings',
        'is_premium',
        'is_active',
        'usage_count',
    ];

    protected function casts(): array
    {
        return [
            'slides' => 'array',
            'settings' => 'array',
            'is_premium' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope para templates ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para templates gratuitos
     */
    public function scopeFree($query)
    {
        return $query->where('is_premium', false);
    }

    /**
     * Scope por categoria
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Incrementar contador de uso
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}

