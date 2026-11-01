
# Monitoring Keamanan Makanan MBG

Mini Project Proyek 3 - Kasus A

## Deskripsi

Aplikasi web berbasis Laravel untuk pelaporan,
peninjauan, dan pemantauan keamanan makanan
pada program Makan Bergizi Gratis (MBG).

## Teknologi

- Laravel 13
- PHP 8.5
- MySQL
- Blade Template
- JavaScript Fetch API

## Role Pengguna

1. Pelapor
   - Membuat laporan insiden
   - Mengunggah bukti
   - Melihat dan mengubah laporan miliknya
     sesuai ketentuan status

2. Pemantau Komunitas
   - Melihat laporan
   - Memulai peninjauan
   - Menyimpan catatan dan hasil peninjauan

3. Koordinator Komunitas
   - Melihat laporan
   - Mencatat penerusan informasi
   - Menutup proses laporan

## Fitur Utama

- Login dan logout
- Role-based authorization
- CRUD laporan sesuai hak akses
- Upload bukti laporan
- Peninjauan laporan
- Penerusan informasi
- Validasi business rule
- Filter, pencarian, dan pagination
- Pilihan distribusi dinamis dengan Fetch API
- Observer untuk perubahan status
- Halaman rekap laporan

## Persyaratan

- PHP 8.5
- Composer
- MySQL
- Git

## Instalasi

1. Clone repository:

   git clone https://github.com/danishunjukbakat/Mini-Project-MBG.git

2. Masuk ke direktori project

3. Instal dependency:

   composer install

4. Buat file environment:

   copy .env.example .env

5. Generate application key:

   php artisan key:generate

6. Konfigurasikan database MySQL di .env.

7. Jalankan migration dan seeder:

   php artisan migrate --seed

8. Jalankan aplikasi:

   php artisan serve

9. Buka http://127.0.0.1:8000

## Akun Demo

Lihat akun demo yang dibuat di DatabaseSeeder.

Gunakan akun dengan role:
- Pelapor
- Pemantau Komunitas
- Koordinator Komunitas

## Alur Status Laporan

submitted
  -> under_review
     -> closed
     -> needs_forwarding
        -> forwarded
           -> closed

## Business Rules

BR-01:
Pelapor hanya dapat mengubah laporan miliknya
ketika status masih submitted.

BR-02:
Distribusi yang dipilih wajib sesuai
dengan sekolah yang dipilih.

BR-03:
Perubahan status mengikuti alur dan
hak akses yang telah ditentukan.

BR-04:
Penerusan informasi hanya dilakukan
oleh Koordinator sesuai status laporan.

## Pengujian

Jalankan pengujian otomatis:

php artisan test

Pengujian manual dilakukan terhadap:
- Authentication
- Authorization
- Pelaporan
- Business rules
- Upload bukti
- Peninjauan dan penerusan
- Fetch API
- Search, filter, dan pagination
- Observer
- Rekap laporan

## Catatan

Aplikasi digunakan sebagai implementasi
mini project pembelajaran Laravel.
