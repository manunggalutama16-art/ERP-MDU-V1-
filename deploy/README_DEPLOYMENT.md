# Panduan Deployment ERP Procurement MDU ke Niagahoster

## 📋 Prasyarat

1. Domain `procurement.mdutama.com` sudah terdaftar dan mengarah ke hosting Niagahoster
2. Anda memiliki akses cPanel Niagahoster
3. Database MySQL sudah dibuat di cPanel

---

## 🚀 Langkah 1: Persiapan Database MySQL

1. Login ke **cPanel Niagahoster**
2. Buka menu **MySQL Database Wizard**
3. Buat database baru, misal: `mdutama_procurement`
4. Buat user database dan catat:
   - **Username**: (misal: `mdutama_user`)
   - **Password**: (pilih password yang kuat)
5. Berikan **All Privileges** ke user untuk database tersebut

---

## 📤 Langkah 2: Upload File ke Niagahoster

1. Di cPanel, buka **File Manager**
2. Navigasi ke folder `public_html` (atau folder domain Anda)
3. Upload semua file dari folder `deploy/` ke dalam `public_html/`
4. Struktur akhir di server harus seperti ini:

```
public_html/
├── .htaccess
├── index.php
├── login.php
├── dashboard.php
├── vendors.php
├── projects.php
├── po_create.php
├── po_list.php
├── po_detail.php
├── pratinjau_cetak_po_pdf.php
├── reports.php
├── settings.php
├── api/
│   ├── auth.php
│   ├── config.php
│   ├── po.php
│   ├── projects.php
│   ├── vendors.php
│   ├── reports.php
│   ├── uploads.php
│   └── settings.php
├── assets/
│   ├── css/
│   └── js/
├── uploads/
│   ├── npwp/
│   ├── signatures/
│   ├── quotations/
│   ├── invoices/
│   ├── supporting/
│   └── logo/
└── database.sql
```

---

## ⚙️ Langkah 3: Konfigurasi Koneksi Database

Edit file `api/config.php` dan sesuaikan kredensial database Anda:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'mdutama_user');      // Ganti dengan username database Anda
define('DB_PASS', 'password_db');      // Ganti dengan password database Anda
define('DB_NAME', 'mdutama_procurement'); // Ganti dengan nama database Anda
```

**PENTING**: Jika Anda deploy di subfolder (misal `public_html/erp/`), ubah juga:
```php
define('APP_URL', 'http://procurement.mdutama.com/erp');
```

---

## 🗄️ Langkah 4: Import Database

1. Di cPanel, buka **phpMyAdmin**
2. Pilih database yang Anda buat di Langkah 1
3. Klik tab **Import**
4. Upload file `database.sql` dari folder `deploy/`
5. Klik **Go** untuk menjalankan import

---

## 🔐 Langkah 5: Login Administrator

Setelah setup selesai, buka browser dan kunjungi:

```
http://procurement.mdutama.com/login.php
```

**Default Login:**
- **Email**: `admin@procurement.mdutama.com`
- **Password**: `admin123`

⚠️ **PENTING**: Segera ubah password default setelah login pertama!

---

## 📁 Langkah 6: Set Permission Folder Uploads

Pastikan folder uploads memiliki permission yang benar:

1. Di File Manager, klik kanan folder `uploads/`
2. Pilih **Change Permissions**
3. Set permission ke **755** (atau 775 jika diperlukan)
4. Klik **Change Permissions**

---

## ✅ Verifikasi Setup

Setelah semua langkah selesai, test fitur berikut:

1. ✅ Login page berfungsi (admin@procurement.mdutama.com / admin123)
2. ✅ Dashboard menampilkan data
3. ✅ Master Vendor bisa diakses (CRUD)
4. ✅ Master Project bisa diakses (CRUD)
5. ✅ Create PO dengan dynamic items
6. ✅ PO List dengan filter dan search
7. ✅ Detail PO dengan upload lampiran
8. ✅ Ubah status PO (Draft → Printed → Signed → Invoiced → Completed)
9. ✅ Riwayat aktivitas PO (activity log) tampil di halaman detail
10. ✅ Settings: upload logo & tanda tangan digital
11. ✅ Reports & Export Excel
12. ✅ Settings & Template Preview

---

## 🐛 Troubleshooting Umum

### Error 500 - Internal Server Error
- Cek file `.htaccess` apakah formatnya benar
- Pastikan `mod_rewrite` aktif di cPanel
- Cek error log di cPanel > Metrics > Errors

### Database Connection Error
- Pastikan kredensial di `api/config.php` benar
- Pastikan database sudah di-import
- Cek apakah user database memiliki privilege yang cukup

### Upload File Tidak Bekerja
- Pastikan folder `uploads/` memiliki permission 755
- Cek `upload_max_filesize` dan `post_max_size` di php.ini
- Pastikan tipe file yang di-upload sesuai allowed types

### CSS/JS Tidak Load
- Pastikan file di folder `assets/` sudah terupload
- Cek apakah CDN Tailwind CSS bisa diakses
- Clear browser cache

---

## 📞 Support

Jika mengalami kendala, hubungi:
- **Email**: admin@procurement.mdutama.com
- **Dokumentasi**: Lihat file `system_architecture_schema.md` untuk detail arsitektur

---

## 🔄 Update di masa depan

Untuk update aplikasi:
1. Backup database terlebih dahulu
2. Backup file-file yang telah dimodifikasi
3. Upload file-file baru
4. Jalankan migrasi database jika ada perubahan schema

---

## 📝 Catatan Perubahan Penting

1. **Password default admin**: `admin123` (hash bcrypt di `database.sql` sudah sesuai).
2. **Tabel `po_activity_log`** untuk riwayat aktivitas PO dibuat otomatis oleh aplikasi
   (CREATE TABLE IF NOT EXISTS) — tidak perlu migrasi manual untuk database lama.
3. **PPN opsional**: saat membuat/mengedit PO Anda bisa memilih Non-PPN; sistem hanya
   menghitung PPN 11% bila opsi PPN dipilih. Dokumen cetak menyesuaikan otomatis.
4. **Tanda tangan di PDF** hanya tampil untuk PO yang sudah disetujui (approved);
   gunakan gambar tanda tangan digital dari menu Settings.
5. **Folder `uploads/logo/`** dibuat otomatis saat logo pertama diunggah.

---

**Catatan**: Aplikasi ini menggunakan PHP 7.4+ dan MySQL 5.7+. Pastikan hosting Niagahoster Anda mendukung versi tersebut.
