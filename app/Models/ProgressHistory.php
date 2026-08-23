<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressHistory extends Model
{
    protected $fillable = [
        'user_id',
        'step',
        'status_lama',
        'status_baru',
        'changed_by',
    ];
}
