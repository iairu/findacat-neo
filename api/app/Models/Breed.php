<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id', 'code', 'name'])]
class Breed extends Model
{
    use HasUuids;

    public function emsColors(): HasMany
    {
        return $this->hasMany(EmsColor::class);
    }
}
