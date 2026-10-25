<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cat extends Model
{
    use HasUuids;

    public const MALE = 'male';
    public const FEMALE = 'female';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'born_on' => 'date',
            'died_on' => 'date',
            'metadata' => 'array',
        ];
    }

    public function sire(): BelongsTo
    {
        return $this->belongsTo(self::class, 'sire_id');
    }

    public function dam(): BelongsTo
    {
        return $this->belongsTo(self::class, 'dam_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** Kittens, whichever parent this cat is. */
    public function children(): HasMany
    {
        return $this->sex === self::FEMALE
            ? $this->hasMany(self::class, 'dam_id')
            : $this->hasMany(self::class, 'sire_id');
    }
}
