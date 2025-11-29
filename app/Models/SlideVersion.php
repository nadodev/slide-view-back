<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlideVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'slide_id',
        'user_id',
        'version_number',
        'title',
        'content',
        'notes',
        'metadata',
        'change_description',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'version_number' => 'integer',
        ];
    }

    /**
     * Slide original
     */
    public function slide(): BelongsTo
    {
        return $this->belongsTo(Slide::class);
    }

    /**
     * Usuário que criou a versão
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

