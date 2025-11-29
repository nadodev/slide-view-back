<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Slide extends Model
{
    use HasFactory;

    protected $fillable = [
        'presentation_id',
        'order',
        'title',
        'content',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * Apresentação que contém este slide
     */
    public function presentation(): BelongsTo
    {
        return $this->belongsTo(Presentation::class);
    }

    /**
     * Boot do model - atualiza contagem de slides ao criar/deletar
     */
    protected static function booted(): void
    {
        static::created(function (Slide $slide) {
            $slide->presentation->updateSlideCount();
        });

        static::deleted(function (Slide $slide) {
            $slide->presentation->updateSlideCount();
        });
    }
}

