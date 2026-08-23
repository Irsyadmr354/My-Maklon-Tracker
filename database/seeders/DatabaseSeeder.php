<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Progress;
use App\Models\Bukti;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminPhone = config('maklon.admin_phone');

        // Admin — no_hp harus sama dengan ADMIN_PHONE di .env
        $admin = User::create([
            'email'    => 'admin@maduwildbee.com',
            'no_hp'    => $adminPhone ?? '085745276656',
            'role'     => 'admin',
            'password' => Hash::make('password'),
        ]);

        // Customer 1 — progres setengah jalan
        $cust1 = User::create([
            'email'    => 'budi@contoh.com',
            'no_hp'    => '081234567890',
            'role'     => 'user',
            'password' => Hash::make('password'),
        ]);

        $prog1 = Progress::create(['user_id' => $cust1->id]);
        foreach ([1, 2, 3] as $i) {
            $prog1->{"status{$i}"}  = 'done';
            $prog1->{"tanggal{$i}"} = now()->subDays(10 - $i)->toDateString();
        }
        $prog1->{"status4"}  = 'on_progress';
        $prog1->{"tanggal4"} = now()->toDateString();
        $prog1->save();

        Bukti::create([
            'user_id'     => $cust1->id,
            'step'        => 1,
            'status'      => 'done',
            'tanggal'     => now()->subDays(9)->toDateString(),
            'keterangan'  => 'konsultasi',
        ]);

        // Customer 2 — baru daftar, belum ada progres
        User::create([
            'email'    => 'sari@contoh.com',
            'no_hp'    => '085678901234',
            'role'     => 'user',
            'password' => Hash::make('password'),
        ]);

        $this->command->info('Seeder selesai:');
        $this->command->info('  Admin    : admin@maduwildbee.com / password');
        $this->command->info('  Customer : budi@contoh.com / password (progres 3/8)');
        $this->command->info('  Customer : sari@contoh.com / password (belum mulai)');
    }
}
