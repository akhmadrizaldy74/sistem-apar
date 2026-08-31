=====================================================
PANDUAN LENGKAP INSTALASI & PENGGUNAAN APLIKASI SISTEM APAR
PD. ANUGRAH UTAMA
=====================================================

PILIHAN CARA INSTALASI:
[METODE A] CARA INSTALASI PRAKTIS VIA DOCKER (Disarankan untuk Lab Kampus / PC Baru - Tanpa Repot Install PHP & MySQL)
[METODE B] CARA INSTALASI MANUAL VIA LARAGON / XAMPP

=====================================================
[METODE A] CARA INSTALASI PRAKTIS VIA DOCKER (SERBA OTOMATIS)
=====================================================
Sangat cocok untuk Komputer Lab Kampus. Anda tidak perlu install PHP 8.2, Composer, atau MySQL secara manual.

--- TAHAP 1: PERSIAPAN & JALANKAN DOCKER ---
1. Pastikan aplikasi **Docker Desktop** sudah ter-install dan dalam posisi aktif/running di komputer.
2. Buka Terminal / Command Prompt (CMD) di dalam folder `sistem-apar`.
3. Jalankan perintah berikut:

   ```bash
   docker compose up -d --build
   ```
   *(Docker akan otomatis mendownload PHP 8.2, Web Server, serta membuatkan database dan mengimport file `sistem_apar.sql` secara otomatis).*

--- TAHAP 2: AKSES APLIKASI ---
Setelah container berjalan (tunggu ~15 detik untuk inisialisasi awal database & aplikasi otomatis):
- **Aplikasi Web:** Buka browser dan akses **http://localhost:8000**
- **phpMyAdmin (Database):** Buka browser dan akses **http://localhost:8080**

*(Catatan: Konfigurasi .env, APP_KEY, dan link storage sudah otomatis dibuatkan oleh Docker).*

--- TAHAP 3: MEMATIKAN DOCKER (Jika Selesai) ---
Untuk mematikan container setelah selesai praktikum/demo, jalankan:
```bash
docker compose down
```


=====================================================
[METODE B] CARA INSTALASI MANUAL VIA LARAGON / XAMPP
=====================================================

--- TAHAP 1: PERSIAPAN DATABASE ---
1. Buka Laragon / XAMPP, pastikan Apache & MySQL sudah START (berwarna hijau).
2. Buka pengelola database (HeidiSQL/phpMyAdmin) lalu buat database baru dengan nama: **sistem_apar**
3. Import file `sistem_apar.sql` ke dalam database `sistem_apar`.

--- TAHAP 2: KONFIGURASI APLIKASI (.env) ---
1. Copy file **.env.example** di folder root project, lalu rename menjadi **.env**
2. Buka file **.env** menggunakan text editor (Notepad/VS Code), cari bagian database lalu sesuaikan seperti berikut:

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sistem_apar
   DB_USERNAME=root
   DB_PASSWORD=

   *(Catatan: DB_PASSWORD biarkan kosong).* Jangan lupa Save (Ctrl + S).

--- TAHAP 3: INSTALASI FILE PENDUKUNG (Cukup 1x Saja) ---
Pastikan komputer terkoneksi ke internet, lalu buka Terminal/CMD di folder project dan jalankan perintah-perintah berikut:

```bash
composer install
npm install
php artisan key:generate
php artisan migrate --seed
```
*(Perintah di atas akan mendownload library, membuat key keamanan, membuat tabel database, serta otomatis mengisi data bawaan awal).*

--- TAHAP 4: CARA JALANKAN APLIKASI ---
Pilih salah satu cara di bawah ini:

>> CARA 1 (Disarankan - Fitur Realtime & Background Job Aktif):
1. Buka Terminal/CMD di folder project, ketik perintah: **composer dev** lalu Enter.
2. Buka browser dan akses URL: **http://127.0.0.1:8000**
*(Biarkan Terminal tetap terbuka selama menggunakan aplikasi).*

>> CARA 2 (Akses Langsung Tanpa Terminal):
Pastikan Laragon/XAMPP tetap aktif, lalu buka browser dan akses URL:
- Pengguna Laragon: **http://sistem-apar.test**
- Pengguna XAMPP: **http://localhost/sistem-apar/public**

=====================================================
SETTING API RAJAONGKIR (Untuk Ongkir Otomatis)
=====================================================
1. Login/Register di **https://rajaongkir.komerce.id/** lalu copy **API Key** dari dashboard Anda.
2. Buka file **.env** Anda, temukan bagian RajaOngkir dan sesuaikan konfigurasinya:

   ```env
   RAJAONGKIR_API_KEY=isi_api_key_disini
   RAJAONGKIR_BASE_URL=https://rajaongkir.komerce.id/api/v1
   RAJAONGKIR_ORIGIN_ID=isi_id_kota_asal_pengiriman
   RAJAONGKIR_COURIER=jne,jnt,sicepat
   RAJAONGKIR_DEFAULT_WEIGHT=1000
   ```

=====================================================
AKUN LOGIN UJI COBA
=====================================================
Gunakan akun default berikut untuk masuk ke sistem:

[Admin]
- Email: **admin@gmail.com** | Password: **password**

[Teknisi]
- Email: **teknisi@gmail.com** | Password: **password**

[Pelanggan]
- Email: **akhmadrizaldy69@gmail.com** | Password: **password**

=====================================================
TIPS SINGKAT CARA PAKAI
=====================================================
1. Login: Masuk dulu pakai akun Admin di atas.
2. Kelola Pelanggan & APAR: Masuk ke menu Pelanggan atau Unit APAR, lalu input datanya. Sistem akan otomatis mendata kondisi tabung APAR beserta tanggal kadaluwarsanya (expired).
3. Transaksi & Service: Pelanggan bisa memesan tabung APAR baru atau mengajukan Service/Refill lewat halaman depan. Masuk kembali sebagai Admin untuk menyetujui transaksi dan menugaskan pengerjaan ke Teknisi.
4. Laporan: Buka menu Laporan kalau mau download rekap penjualan, pesanan, service, atau keuangan ke format PDF.
