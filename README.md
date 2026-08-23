# TrackMaklon — Tracker Progres Maklon

Aplikasi web untuk tracking progres produksi maklon 8 tahap (konsultasi, pembayaran,
desain label, produksi, pengemasan, pengiriman, foto & video, kesimpulan) dengan
panel admin untuk update status dan upload bukti.

## Fitur

- Tracking progres produksi 8 tahap secara real-time
- Panel admin untuk update status tiap tahap
- Upload bukti berupa foto & video per tahap (disimpan di disk privat)
- Halaman order tracker untuk cek status pesanan
- Login dengan email + password (aktivasi pertama memakai nomor HP)
- Admin dapat menambah customer langsung dari panel

## Persyaratan

- PHP >= 8.1
- Composer
- MySQL

## Instalasi

1. `composer install`
2. `cp .env.example .env`
3. `php artisan key:generate`
4. `php artisan migrate`
5. Set `ADMIN_PHONE` di file `.env` untuk menentukan akun admin
6. `php artisan serve`

## Cara Pakai

### Login

Login memakai **email + password** (min. 8 karakter). Pesan kesalahan selalu generik
("Email atau kata sandi salah.") baik email tak terdaftar maupun password salah.

### Aktivasi pertama (user lama pra-migrasi)

User lama yang belum punya password login dengan cara berikut:

1. Isi email + password baru (min. 8 karakter).
2. Isi kolom opsional **No HP** — harus sama persis dengan nomor HP terdaftar.
3. Berhasil → password tersimpan (ter-hash) dan langsung masuk. Registrasi akun
   baru lewat halaman login sudah ditutup; customer baru dibuat admin via panel.

### Tambah customer (admin)

Buka `/admin/customers`, buka blok **Tambah Customer**, isi email, no HP, dan
password (min. 8 karakter), lalu simpan. Customer baru otomatis role `user`.

### Reset password (sementara, via tinker)

```bash
php artisan tinker
```

```php
$u = App\Models\User::where('email', 'customer@example.com')->first();
$u->update(['password' => Illuminate\Support\Facades\Hash::make('password-baru')]);
```

### Lain-lain

- **Cabut akses admin:** kosongkan/ganti `ADMIN_PHONE` di `.env` lalu simpan; pada
  login berikutnya role akun lama otomatis turun menjadi user.
- Setiap perubahan status tahap tercatat di tabel `progress_histories`
  (status lama, status baru, siapa yang mengubah).

## Catatan Deployment

- Jalankan **sekali** setelah migrate: `php artisan maklon:pindah-bukti` —
  memindahkan file bukti lama dari `storage/app/public/bukti` ke
  `storage/app/bukti` (disk privat, tak bisa diakses publik). Idempotent.
- Deploy flat shared hosting: salin `index.php` & `.htaccess` ke root dokumen.
- Pastikan `.htaccess` aktif karena memblokir akses langsung ke `/.env`,
  `/storage`, folder internal, dan file tooling lainnya.
