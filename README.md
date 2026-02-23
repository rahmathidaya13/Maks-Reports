# 🧾 Web-Based Point of Sale (POS) System

<img width="300" alt="login" src="https://github.com/user-attachments/assets/f5d8f9f4-859b-4858-87f4-d25d168f648c" /> <img width="600"  alt="dashboard Admin" src="https://github.com/user-attachments/assets/5ba3c10e-5bc3-4b24-9cd5-d9dfbd4f7761" /> <img width="600" alt="dashboard User" src="https://github.com/user-attachments/assets/0ce9c7ae-c221-4188-9e37-05ed4bd05f84" />

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


