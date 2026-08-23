<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bukti extends Model
{
    protected $table = 'bukti'; // tabel bukti

    protected $fillable = ['user_id', 'path', 'keterangan', 'status', 'tanggal', 'step'];

    // app/Models/Bukti.php
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
