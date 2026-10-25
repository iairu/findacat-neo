<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id', 'breed_id', 'code', 'english_name'])]
class EmsColor extends Model
{
    use HasUuids;

    public function breed(): BelongsTo
    {
        return $this->belongsTo(Breed::class);
    }
}
