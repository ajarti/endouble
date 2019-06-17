<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Source extends Model
{
    use softDeletes;

    // Allow mass inserts.
    protected $guarded = [];

    // Cast Extra Dates.
    protected $dates = ['check_at', 'last_updated_at'];

}
