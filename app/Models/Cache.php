<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cache extends Model
{
    // Allow mass inserts.
    protected $guarded = [];

    // Cast extra dates.
    protected $dates = ['dated_at'];

    // Cast JSON -> Array.
    protected $casts = [
        'item' => 'array',
    ];
}
