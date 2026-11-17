# 🎀 Nnaco_SA — Smart E-Commerce & Inventory Management Platform

> **Proyek Akhir Mata Kuliah Rekayasa Perangkat Lunak (RPL)**  
> Program Studi Teknik Informatika • Jurusan Teknik Informatika

---

## 👥 1. Identitas Anggota Kelompok

* **Nomor / Nama Kelompok:** Kelompok [Isi Nomor/Nama Kelompok Anda, Contoh: Kelompok 3]
* **Kelas:** TI-1C

### Anggota Pengembang:
1. **Nama Lengkap:** **Talita Nabila**  
   **NIM:** **41257242**  
   **Peran:** Fullstack Developer (Backend PHP MVC, Frontend UI/UX, AI Integration & DevOps CI/CD)
2. **Nama Lengkap:** [Isi Nama Anggota 2 jika ada / Kosongkan jika mandiri]  
   **NIM:** [Isi NIM Anggota 2 jika ada]  
   **Peran:** [Isi Peran Anggota 2]
3. **Nama Lengkap:** [Isi Nama Anggota 3 jika ada / Kosongkan jika mandiri]  
   **NIM:** [Isi NIM Anggota 3 jika ada]  
   **Peran:** [Isi Peran Anggota 3]

---

## 🎯 2. Tema atau Topik Kelompok

**"Sistem Informasi E-Commerce & Otomasi Operasional Toko Aksesoris Berbasis Web Terintegrasi WhatsApp Gateway, Trigger PL/SQL Database untuk Validasi & Audit Stok, serta Chatbot AI Asisten Toko Cerdas"**

---

## 📖 3. Deskripsi Aplikasi

### 3.1 Penjelasan Aplikasi
**Nnaco_SA** adalah platform e-commerce dan manajemen inventaris cerdas berbasis web yang dirancang khusus untuk operasional ritel toko pernak-pernik estetik, aksesoris wanita, dan produk *handmade*. Aplikasi ini dibangun menggunakan bahasa pemrograman **PHP Native** dengan pola arsitektur **Model-View-Controller (MVC)** murni, basis data relasional **MySQL**, antarmuka modern bernuansa *Modern Glassmorphism Pastel* berbasis **Tailwind CSS**, serta integrasi layanan pesan instan dan kecerdasan buatan (*Cloud LLM*).

### 3.2 Tujuan Pembuatan Aplikasi
- Membangun ekosistem e-commerce terpadu satu pintu (*end-to-end*) yang menghubungkan alur belanja pelanggan, operasional fisik gudang, hingga pembukuan bisnis.
- Mengotomatiskan proses pengiriman nomor resi kurir dan konfirmasi pesanan ke ponsel pembeli tanpa perlu pengetikan manual oleh staf toko.
- Menjamin integritas dan konsistensi data ketersediaan barang di level mesin basis data melalui validasi terprogram (*Trigger PL/SQL*).
- Menghadirkan asisten layanan pelanggan virtual berbasis kecerdasan buatan yang mampu melayani konsultasi produk secara faktual selama 24 jam sehari.

### 3.3 Permasalahan Nyata yang Diselesaikan
1. **Selisih Stok Akibat Rekapitulasi Manual:**  
   Pencatatan inventaris konvensional sering menimbulkan ketidakcocokan antara jumlah stok di etalase web dengan ketersediaan fisik nyata di rak gudang, sehingga toko rawan menerima pesanan barang yang sebenarnya telah habis (*overselling*). Nnaco_SA mengatasinya dengan pemotongan stok otomatis dan proteksi transaksi atomik.
2. **Keluhan Keterlambatan Nomor Resi Pengiriman:**  
   Pelanggan sering cemas mengenai kepastian pengiriman paket mereka karena staf toko harus mengirimkan resi satu per satu melalui chat pribadi. Nnaco_SA mengatasinya dengan otomatisasi WhatsApp Gateway yang langsung mengirim notifikasi resi seketika setelah operator gudang mengemas barang.
3. **Ketiadaan Data Omset Penjualan Riil Bagi Pemilik Toko:**  
   Pemilik usaha (*owner*) kesulitan memantau perputaran omset harian dan performa produk terlaris secara langsung. Nnaco_SA mengatasinya melalui *Executive Dashboard* yang menyajikan analitik finansial secara *real-time*.

---

## ✨ 4. Fitur-Fitur Aplikasi

Sistem Nnaco_SA mengimplementasikan pembagian peran berbasis hak akses (*Role-Based Access Control* / RBAC):

### 4.1 Modul Pengguna Publik / Pembeli (Customer)
- **Katalog Produk Dinamis:** Menjelajahi produk aksesoris pastel lengkap dengan foto resolusi tinggi, label harga, dan filter kategori interaktif (jepit rambut, kalung, stiker, kotak kado).
- **Keranjang Belanja (*Shopping Cart*):** Menambah, mengubah kuantitas, serta menghitung subtotal belanja secara instan.
- **Checkout Pengiriman Ringkas:** Pengisian data nama, nomor WhatsApp penerima, alamat pengiriman, opsi kurir ekspedisi (J&T, SiCepat), dan metode pembayaran (Transfer Bank, QRIS, COD).
- **Pelacakan Status Pesanan:** Pembeli dapat memantau status pesanan (Pending, Shipped, Completed) berdasarkan kode unik transaksi `#ORD`.

### 4.2 Modul Pengelola Toko (Admin)
- **Manajemen Master Produk (CRUD):** Tambah, lihat, perbarui, dan hapus katalog barang, deskripsi, upload foto produk, serta pengaturan stok awal.
- **Manajemen Kategori:** Pengelompokan jenis aksesoris untuk mempermudah navigasi pembeli.
- **Manajemen Pengguna (User Management):** Pengelolaan data akun, hak akses peran (Owner, Admin, Operator, Customer), dan status aktifasi.
- **Pengaturan API Eksternal:** Konfigurasi token WhatsApp Fonnte dan kredensial Cloud AI Provider (Groq / Gemini).

### 4.3 Modul Operasional Gudang (Operator)
- **Verifikasi Pesanan Pending:** Memeriksa rincian pesanan baru dan mencocokkan ketersediaan fisik barang di rak gudang.
- **Pengemasan & Input Resi Ekspedisi:** Mengubah status pesanan menjadi *Shipped* serta menginput nomor resi kurir.
- **Otomatisasi Kirim Resi WhatsApp:** Memerintahkan sistem memicu API Fonnte untuk mengirim pesan WhatsApp resmi ke nomor pembeli secara otomatis.

### 4.4 Modul Pemilik Usaha (Owner)
- **Executive Dashboard:** Visualisasi metrik bisnis utama: total omset hari ini, total pesanan sukses, dan grafik tren penjualan.
- **Laporan Finansial:** Laporan laba kotor, produk paling laris (*best-seller*), dengan fitur filter rentang tanggal dan cetak dokumen.

### 4.5 Modul Integritas Basis Data & Pengujian (Quality Assurance)
- **Trigger PL/SQL Database:** Menjalankan pemotongan kuantitas stok fisik otomatis saat pesanan diproses dan mencatat log audit transaksi.
- **Automated Unit Testing:** Pengujian fungsi logika keamanan enkripsi, perhitungan transaksi, dan hak akses peran melalui skrip `test_runner.php`.
- **Continuous Integration (CI/CD):** Terintegrasi dengan GitHub Actions untuk verifikasi otomatis status build hijau (*Passed*).

---

## 🤖 5. Fitur Kecerdasan Buatan (AI Assistant)

### 5.1 Penjelasan Fitur AI
Aplikasi Nnaco_SA dilengkapi asisten virtual cerdas bernama **Nnaco AI Shopping Assistant** yang tampil sebagai *floating widget* interaktif di pojok kanan bawah antarmuka web.

### 5.2 Fungsi AI dalam Aplikasi
- **Rekomendasi Produk Personal:** Membantu calon pembeli menemukan aksesoris atau kado yang cocok berdasarkan preferensi warna, gaya, dan anggaran belanja.
- **Pemeriksaan Stok Faktual:** Menjawab ketersediaan stok fisik barang di rak gudang secara akurat.
- **Pelacakan Status Pesanan Mandiri:** Menjawab pertanyaan pembeli mengenai status pengiriman dan nomor resi kurir berdasarkan nomor invoice `#ORD`.
- **Asistensi Multi-Role (Role-Awareness):** 
  - Sesi Pembeli: Memberikan rekomendasi belanja dan info katalog.
  - Sesi Operator: Memberikan informasi antrean pesanan belum dipacking.
  - Sesi Owner/Admin: Memberikan ringkasan omset penjualan harian secara instan.

### 5.3 Teknologi & Model AI yang Digunakan
- **Large Language Model (LLM):** Menggunakan **Llama-3.3-70B-Versatile** / **Llama-3-70B-8192** yang di-host pada infrastruktur cloud berkecepatan tinggi **Groq AI Engine** (mendukung opsi fallback ke Google Gemini / Nvidia AI).
- **Protokol Komunikasi:** RESTful API Client melalui protokol HTTPS dengan format pertukaran data JSON.

### 5.4 Cara AI Terintegrasi dengan Aplikasi (*Retrieval Context Injection*)
Fitur AI pada Nnaco_SA tidak bekerja secara statis atau mengarang bebas (*halusinasi*), melainkan menerapkan teknik **Retrieval Context Injection**:
1. Pengguna memasukkan pertanyaan melalui antarmuka chat.
2. Controller backend (`AIChatController.php`) mengekstrak maksud pesan dan melakukan query data riil ke basis data MySQL (membaca tabel `products`, kuantitas `stock`, status `orders`, dan tabel `settings`).
3. Data faktual tersebut disuntikkan (*injected*) ke dalam instruksi sistem prompt AI.
4. Model AI memproses konteks database tersebut dan menyusun respons dalam Bahasa Indonesia yang ramah, sopan, dan 100% akurat sesuai kondisi toko waktu-nyata.

---

## 📹 6. Link Video YouTube Demonstrasi Aplikasi

Tautan video rekaman presentasi dan demonstrasi sistem aplikasi Nnaco_SA:

* **Link Video Utama (YouTube / Google Drive):**  
  👉 **[TEMPELKAN LINK VIDEO YOUTUBE ANDA DI SINI](https://youtu.be/LINK_VIDEO_LENGKAP_ANDA)**

### ⏱️ Navigasi Bab & Timestamps Video:
- **00:00 – 04:30 | Bagian 1: PRD & Konsep Aplikasi (Bobot 20%)**  
  Identifikasi masalah nyata UMKM, nilai guna produk Nnaco_SA, 5 modul fungsional (RBAC 4 Aktor), kebutuhan non-fungsional, dan peran strategis AI.
- **04:30 – 10:45 | Bagian 2: Pemodelan UML & UI/UX Figma (Bobot 30%)**  
  Pembahasan runut 4 Diagram UML (Use Case, Activity, Class, Sequence) dan Walkthrough antarmuka interaktif Figma.
- **10:45 – 21:30 | Bagian 3: Live Demo Aplikasi Web & Sistem Utuh (Bobot 50%)**  
  Elevator pitch, arsitektur sistem MVC, Live Demo Web (CRUD, Checkout, **Pembuktian Trigger PL/SQL**, Chatbot AI), Automated Unit Test di terminal Antigravity, verifikasi centang hijau CI/CD GitHub Actions, dan refleksi SDLC mandiri.

---

## 🛠️ 7. Panduan Instalasi Aplikasi (Setup Instructions)

Ikuti instruksi berikut untuk menjalankan aplikasi Nnaco_SA di komputer lokal (*Local Development Environment*):

### 7.1 Persyaratan Perangkat & Software (*System Requirements*)
- **Sistem Operasi:** Windows 10/11, macOS, atau Linux
- **Web Server:** Apache (disarankan menggunakan paket **XAMPP**)
- **PHP Version:** PHP versi 8.0 atau yang lebih baru
- **Database Engine:** MySQL / MariaDB (InnoDB Storage Engine)
- **Ekstensi PHP Wajib:** `pdo_mysql`, `curl`, `json`, `mbstring`, `openssl`
- **Git:** Git Version Control terpasang

### 7.2 Cara Kloning Proyek (*Clone Repository*)
Buka Terminal atau Command Prompt (CMD), arahkan ke folder `htdocs` server lokal Anda, lalu jalankan perintah:
```bash
cd C:\xampp\htdocs
git clone https://github.com/TalitaNabila21/Proyek-RPL_Talita-Nabila_nnaco.sa.git nnaco_sa
cd nnaco_sa
```

### 7.3 Cara Konfigurasi Database (MySQL)
1. Buka aplikasi **XAMPP Control Panel**, lalu klik tombol **Start** pada modul **Apache** dan **MySQL**.
2. Buka browser web dan akses phpMyAdmin melalui URL:  
   `http://localhost/phpmyadmin`
3. Buat database baru bernama `nnaco_sa`:
   ```sql
   CREATE DATABASE nnaco_sa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
4. Impor struktur tabel:
   - Pilih database `nnaco_sa` di panel kiri.
   - Klik tab **Import** pada menu atas.
   - Klik tombol **Choose File**, pilih berkas `database.sql` yang berada di direktori utama proyek, lalu klik tombol **Go / Kirim** di bawah.
   - *(Opsional)* Jika diperlukan migrasi tambahan, impor juga berkas `database_order.sql`, `database_notification_migration.sql`, dan `database_otp_migration.sql`.

### 7.4 Cara Konfigurasi Environment Variable (`.env`)
1. Di folder utama proyek, salin berkas `.env.example` menjadi berkas bernama `.env`:
   ```bash
   copy .env.example .env
   ```
   *(atau pada Linux/macOS: `cp .env.example .env`)*
2. Buka berkas `.env` tersebut menggunakan teks editor (VS Code, Antigravity, atau Notepad), lalu isi konfigurasi API Anda:
   ```ini
   # API Key AI Provider (Dapatkan gratis di https://console.groq.com/)
   AI_API_KEY=gsk_your_groq_api_key_here

   # Token WhatsApp Gateway (Dapatkan di https://md.fonnte.com/)
   FONNTE_TOKEN=your_fonnte_token_here

   # Nomor WhatsApp Penerima Notifikasi Toko / Owner (Contoh: 6281234567890)
   ORDER_NOTIFICATION_PHONE=628XXXXXXXXXX
   ```

### 7.5 Mengisi Akun Demo Pengujian (*Seeder Accounts*)
Jalankan skrip seeder melalui terminal di dalam folder proyek untuk mengisi akun demo awal:
```bash
php seeder_accounts.php
```

### 7.6 Cara Menjalankan & Mengakses Aplikasi
1. Pastikan modul **Apache** dan **MySQL** pada XAMPP tetap berstatus *Running*.
2. Buka web browser (Google Chrome, Microsoft Edge, atau Mozilla Firefox).
3. Akses aplikasi melalui tautan lokal berikut:
   ```text
   http://localhost/nnaco_sa
   ```

---

## 🔑 8. Kredensial Akun Pengujian Demo (Multi-Role)

Gunakan akun-akun demo bawaan berikut untuk menguji seluruh modul sistem:

| Peran (Role) | Alamat Email | Kata Sandi (*Password*) | Hak Akses Utama |
| :--- | :--- | :--- | :--- |
| **Owner** | `owner@nnaco.com` | `owner123` | Executive Dashboard, Analitik Omset Harian, Laporan Finansial. |
| **Admin** | `admin@nnaco.com` | `admin123` | Master Produk, Stok Awal, Kategori, & Manajemen Pengguna. |
| **Operator** | `operator@nnaco.com` | `operator123` | Verifikasi Gudang, Pengecekan Rak Fisik, Input Resi & Kirim WA. |
| **Customer** | `customer@nnaco.com` | `customer123` | Katalog Etalase, Keranjang Belanja, Checkout, & Chatbot AI. |

---

## 🧪 9. Pengujian Sistem (Automated Testing & CI/CD)

### 9.1 Menjalankan Automated Unit Test Lokal
Pengujian fungsi logika keamanan enkripsi, formula pemotongan stok fisik, kalkulasi transaksi, dan matriks hak akses dapat dijalankan secara langsung di terminal:
```bash
.\run_test.bat
```
*(atau jalankan: `php test_runner.php`)*  
Target hasil pengujian: **5/5 UNIT TESTS PASSED (100% SUCCESS) - ZERO FAILURE**.

### 9.2 Status CI/CD Pipeline (GitHub Actions)
Repositori proyek ini telah terhubung dengan alur kerja otomasi **Continuous Integration** pada berkas `.github/workflows/main.yml`. Setiap aktivitas pembaruan kode ke repositori GitHub secara otomatis memicu runner virtual untuk melakukan verifikasi pengujian otomatis dengan status **Centang Hijau (Passed / Success)**.

---

## 📄 10. Lisensi & Hak Cipta
Dikembangkan untuk penilaian akademik Proyek Akhir Mata Kuliah Rekayasa Perangkat Lunak (RPL) Tahun Ajaran 2026.  
Hak Cipta &copy; 2026 **Talita Nabila (41257242) - Kelas TI-1C**. Seluruh hak cipta dilindungi undang-undang.
