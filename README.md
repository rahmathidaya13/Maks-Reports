# 🧾 Web-Based Point of Sale (POS) System

<p align="center"> <img src="storage/ss/login.png" width="800"> </p> <p align="center"> <img src="storage/ss/dashboard_admin.png" width="800"> </p> <p align="center"> <img src="storage/ss/dashboard_user.png" width="800"> </p>

## 📌 Deskripsi

Aplikasi Point of Sale (POS) berbasis web yang digunakan untuk mencatat dan mengelola transaksi penjualan secara real-time.
Sistem ini dirancang untuk membantu pengelolaan data produk, pencatatan transaksi, serta monitoring laporan penjualan secara terstruktur dan efisien.
Fitur utama meliputi manajemen produk, transaksi penjualan, serta dashboard monitoring untuk melihat ringkasan performa penjualan.

## Teknologi Yang Digunakan

- PHP 8+
- Laravel 10
- Vue3
- Inertia.js
- MySql
- Bootstrap 5+
- Restful API

## Persyaratan Sistem 
- PHP >= 8.0
- Composer
- Mysql
- Node.js & Npm

## Instalasi

Ikuti langkah-langkah di bawah ini untuk dapat menggunakan aplikasi ini

### 1. Clone repositori

```bash
git clone https://github.com/rahmathidaya13/Maks-Reports.git
cd Maks-Reports

```
### 2. Install Depedency

```bash
composer install
npm install
npm run dev

```
### 3. Konfigurasi lingkungan
Salin file .env.example menjadi .env dengan cara berikut ini.

```bash
cp .env.example .env

```
Setelah itu, buat kunci aplikasi Laravel:
```bash
php artisan key:generate

```
### 4. Migrasi dan Seed Database
Jalankan migrasi database dan seed data awal:
```bash
php artisan migrate
php artisan db:seed
```
### 5. Menjalankan Server
Jalankan server pengembangan Laravel:
```bash
npm run build 
php artisan serve


