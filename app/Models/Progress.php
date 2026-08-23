<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    use HasFactory;

    protected $table = 'progresses'; // ⬅️ Penting!

    protected $fillable = [
        'user_id',
        'status1',
        'tanggal1',
        'status2',
        'tanggal2',
        'status3',
        'tanggal3',
        'status4',
        'tanggal4',
        'status5',
        'tanggal5',
        'status6',
        'tanggal6',
        'status7',
        'tanggal7',
        'status8',
        'tanggal8',
    ];
}

