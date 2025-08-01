# Panduan Deployment TMT Silsilah

Dokumen ini berisi langkah-langkah standar untuk men-deploy aplikasi TMT Silsilah ke server produksi berbasis Linux (seperti cPanel atau VPS).

---

## Prasyarat Server

Pastikan server Anda memenuhi persyaratan dasar Laravel:
- PHP >= 8.2
- Composer
- Node.js & NPM
- Ekstensi PHP yang dibutuhkan Laravel (Ctype, cURL, DOM, Fileinfo, mbstring, XML, dll.)
- Database (MariaDB/MySQL)

---

## Langkah-Langkah Deployment

Proses ini dibagi menjadi dua bagian: **Deployment Awal** (hanya dilakukan sekali) dan **Deployment Pembaruan** (dilakukan setiap kali ada versi baru).

### A. Deployment Awal (Setup Pertama Kali)

1.  **Clone Repository**
    Hubungkan ke server Anda melalui SSH dan clone repository dari Github.
    ```bash
    git clone [URL_REPOSITORY_ANDA] .
    ```

2.  **Salin File `.env`**
    Salin file konfigurasi contoh dan sesuaikan isinya.
    ```bash
    cp .env.example .env
    ```

3.  **Sesuaikan Variabel `.env`**
    Buka file `.env` dan atur variabel-variabel berikut untuk produksi:
    ```ini
    APP_NAME="TMT Silsilah"
    APP_ENV=production
    APP_DEBUG=false
    APP_URL=[https://domain-anda.com](https://domain-anda.com)

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=nama_database_anda
    DB_USERNAME=user_database_anda
    DB_PASSWORD=password_database_anda
    ```

4.  **Instal Dependensi Backend**
    Instal semua library PHP yang dibutuhkan tanpa dependensi pengembangan.
    ```bash
    composer install --no-dev --optimize-autoloader
    ```

5.  **Instal Dependensi Frontend & Build Aset**
    Instal paket NPM dan kompilasi file CSS/JS untuk produksi.
    ```bash
    npm install
    npm run build
    ```

6.  **Hasilkan Kunci Aplikasi (App Key)**
    Buat kunci enkripsi unik untuk aplikasi Anda.
    ```bash
    php artisan key:generate
    ```

7.  **Jalankan Migrasi Database**
    Buat semua tabel yang dibutuhkan di database Anda.
    ```bash
    php artisan migrate --force
    ```

8.  **Buat Symbolic Link untuk Storage**
    Agar file yang diunggah (foto, dll.) dapat diakses dari web.
    ```bash
    php artisan storage:link
    ```

9.  **Cache Konfigurasi**
    Optimalkan aplikasi untuk performa produksi.
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

### B. Deployment Pembaruan (Update Kode)

Setiap kali Anda ingin men-deploy versi baru yang sudah di-merge ke branch `develop` atau `main`:

1.  **Tarik Kode Terbaru**
    Ambil perubahan terbaru dari repository.
    ```bash
    git pull origin develop
    ```

2.  **Instal Dependensi (jika ada perubahan)**
    Jika file `composer.json` atau `package.json` berubah, jalankan kembali perintah instalasi.
    ```bash
    composer install --no-dev --optimize-autoloader
    npm install
    ```

3.  **Build Ulang Aset Frontend**
    Selalu build ulang aset jika ada perubahan pada file CSS atau JS.
    ```bash
    npm run build
    ```

4.  **Jalankan Migrasi (jika ada)**
    Jika ada migrasi database baru, jalankan perintah ini.
    ```bash
    php artisan migrate --force
    ```

5.  **Hapus Cache Lama dan Buat Cache Baru**
    Ini adalah langkah paling penting saat update untuk memastikan semua perubahan terbaca.
    ```bash
    php artisan optimize:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

---