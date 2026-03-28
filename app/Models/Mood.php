<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mood extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'mood_score',
        'mood_label',
        'emoji',
        'sleep_score',
        'stress_score',
        'productivity_score',
        'ate_well',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'mood_score' => 'integer',
            'sleep_score' => 'integer',
            'stress_score' => 'integer',
            'productivity_score' => 'integer',
            'ate_well' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function followUpComplete(): bool
    {
        return $this->sleep_score !== null
            && $this->stress_score !== null
            && $this->productivity_score !== null
            && $this->ate_well !== null;
    }
}
