# TrackMaklon — Tracker Progres Maklon

Aplikasi web untuk tracking progres produksi maklon 8 tahap (konsultasi, pembayaran,
desain label, produksi, pengemasan, pengiriman, foto & video, kesimpulan) dengan
panel admin untuk update status dan upload bukti.

## Fitur

- Tracking progres produksi 8 tahap secara real-time
- Panel admin untuk update status tiap tahap
- Upload bukti berupa foto & video per tahap
- Halaman order tracker untuk cek status pesanan
- Login admin sederhana dengan email + nomor HP

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

Login sebagai admin cukup memasukkan email dan nomor HP yang terdaftar pada `ADMIN_PHONE`.
Setelah masuk, kelola customer dengan cara berikut:

- **Kelola customer:** buka `/admin/customers`, pilih customer, ubah status tahap +
  unggah bukti, lalu klik **Simpan Semua**.
- **Cabut akses admin:** kosongkan/ganti `ADMIN_PHONE` di `.env` lalu simpan; pada
  login berikutnya role akun lama otomatis turun menjadi user.

### Disclaimer Keamanan

**PENTING:** model autentikasi tanpa password ini lemah — siapa pun yang tahu
kombinasi email + nomor HP bisa masuk. Ganti ke metode password atau OTP
sebelum aplikasi dipakai publik.

## Catatan Deployment

- Deploy flat shared hosting: salin `index.php` & `.htaccess` ke root dokumen.
- Pastikan `.htaccess` aktif karena memblokir akses langsung ke `/.env`,
  `/storage`, folder internal, dan file tooling lainnya.
