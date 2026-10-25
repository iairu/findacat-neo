<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatMetadata extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'cat_id', 'key', 'value'];
}
