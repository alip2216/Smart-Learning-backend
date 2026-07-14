# Smart Learning App - Backend (API)

Ini adalah *source code* untuk backend (API) dari aplikasi **Smart Learning**. Proyek ini dibangun menggunakan Framework Laravel.

---

## 📌 PANDUAN PENGUJIAN UNTUK DOSEN PENILAI

Agar aplikasi Flutter (Mobile/Frontend) dapat terhubung dengan backend ini dan berjalan tanpa error (seperti *Network Error* atau *No route to host*), **mohon ikuti langkah-langkah di bawah ini dengan saksama.**

### Tahap 1: Instalasi & Persiapan Database
Jika ini adalah pertama kalinya proyek ini dijalankan di komputer Bapak/Ibu, jalankan perintah berikut di terminal:

1. Instalasi dependensi:
   ```bash
   composer install
   ```
2. Buat file `.env` (jika belum ada):
   Copy file `.env.example` dan ubah namanya menjadi `.env`.
3. Generate Application Key:
   ```bash
   php artisan key:generate
   ```
4. Konfigurasi Database di file `.env`:
   Pastikan Anda sudah membuat database kosong (misalnya bernama `smart_learning`) di phpMyAdmin / MySQL, lalu sesuaikan `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=smart_learning
   DB_USERNAME=root
   DB_PASSWORD=
   ```
5. Jalankan Migrasi Database:
   ```bash
   php artisan migrate
   ```

### Tahap 2: Menjalankan Server (SANGAT PENTING!) ⚠️
Ini adalah langkah paling krusial agar aplikasi yang dijalankan di **HP atau Emulator Android** bisa berkomunikasi dengan backend ini.

Di terminal/CMD Anda, **JANGAN** hanya menjalankan `php artisan serve`.
**WAJIB** tambahkan parameter `--host=0.0.0.0`:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

**Alasan Mengapa Ini Wajib:**
* Jika hanya menggunakan `php artisan serve`, server Laravel hanya akan berjalan di `127.0.0.1` (localhost internal laptop). Akibatnya, koneksi dari luar (seperti HP atau Emulator) **akan ditolak (Connection Refused)**.
* Dengan menambahkan `--host=0.0.0.0`, server Laravel membuka diri untuk menerima koneksi masuk dari jaringan lokal WiFi Anda (misal dari IP `192.168.x.x`), sehingga HP yang menggunakan WiFi yang sama dapat mengakses API backend ini.

---
**Catatan untuk Pengecekan di Aplikasi Flutter:**
Pastikan IP Address di file `lib/api.config.dart` pada project Flutter sudah disesuaikan dengan IP WiFi laptop Bapak/Ibu (bisa dicek melalui perintah `ipconfig` di CMD).
