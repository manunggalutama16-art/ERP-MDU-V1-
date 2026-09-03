# Panduan Deployment ERP Procurement MDU
## Menggunakan Supabase Database + Rumahweb Hosting

---

## 🏗️ Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────────┐
│                    www.procurement.mdutama.com               │
│                    (Rumahweb PHP Hosting)                    │
├─────────────────────────────────────────────────────────────┤
│  Frontend: HTML + Tailwind CSS + JavaScript                 │
│  Backend:  PHP 7.4+ / PHP 8.x                              │
│  Database: Supabase (PostgreSQL)                            │
└─────────────────────────────────────────────────────────────┘
                              │
                              │ SSL Connection
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    Supabase Cloud                           │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  PostgreSQL 15+ Database                            │   │
│  │  - Connection Pooling (PgBouncer)                   │   │
│  │  - SSL/TLS Encryption                               │   │
│  │  - Automatic Backups                                │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

---

## 📋 Prasyarat

1. **Akun Supabase** (gratis): https://supabase.com
2. **Hosting Rumahweb** dengan PHP 7.4+ / 8.x
3. **Domain** `www.procurement.mdutama.com` sudah terdaftar di Rumahweb
4. **php_pgsql extension** aktif di hosting (untuk koneksi PostgreSQL)

---

## 🚀 Langkah 1: Setup Supabase

### 1.1 Buat Project Supabase
1. Login ke https://supabase.com/dashboard
2. Klik **"New Project"**
3. Isi:
   - **Organization**: Pilih atau buat baru
   - **Project name**: `procurement-mdutama`
   - **Database Password**: Buat password yang kuat (CATAT!)
   - **Region**: Pilih terdekat (Singapore recommended)
4. Klik **"Create new project"**
5. Tunggu ~2 menit hingga project selesai dibuat

### 1.2 Dapatkan Connection String
1. Buka project Supabase
2. Klik **Settings** (ikon gear) → **Database**
3. Scroll ke **Connection string**
4. Pilih **URI** tab
5. Copy connection string, contoh:
   ```
   postgresql://postgres.YOUR_PROJECT_REF:YOUR_PASSWORD@aws-0-ap-southeast-1.pooler.supabase.com:6543/postgres
   ```

### 1.3 Import Schema Database
1. Buka **SQL Editor** di Supabase Dashboard
2. Copy seluruh isi file `database_supabase.sql`
3. Paste ke SQL Editor
4. Klik **"Run"** untuk menjalankan schema

---

## 🚀 Langkah 2: Setup Rumahweb

### 2.1 Upload Files
1. Login ke **cPanel Rumahweb**
2. Buka **File Manager**
3. Navigasi ke folder domain: `public_html/www.procurement.mdutama.com/`
4. Upload seluruh isi folder `deploy/` ke `public_html/`

### 2.2 Enable php_pgsql Extension
1. Di cPanel, cari **"Select PHP Version"** atau **"PHP Selector"**
2. Pastikan PHP 7.4 atau 8.x terpilih
3. Centang extension: **`pgsql`** dan **`pdo_pgsql`**
4. Klik **"Apply"**

### 2.3 Konfigurasi Database Connection
Edit file `api/config.php`, ganti bagian berikut:

```php
// Database Configuration - Supabase PostgreSQL
define('DB_HOST', 'aws-0-ap-southeast-1.pooler.supabase.com');
define('DB_PORT', '6543');
define('DB_USER', 'postgres.YOUR_PROJECT_REF');
define('DB_PASS', 'YOUR_SUPABASE_PASSWORD');
define('DB_NAME', 'postgres');
define('DB_SSLMODE', 'require');
```

**Contoh nyata:**
```php
define('DB_HOST', 'aws-0-ap-southeast-1.pooler.supabase.com');
define('DB_PORT', '6543');
define('DB_USER', 'postgres.abcdefghij');
define('DB_PASS', 'MyS3cur3P@ssw0rd!');
define('DB_NAME', 'postgres');
define('DB_SSLMODE', 'require');
```

### 2.4 Set Folder Permissions
1. Klik kanan folder `uploads/`
2. Pilih **Change Permissions**
3. Set ke **755**

---

## 🔐 Langkah 3: Login & Test

1. Buka browser: `https://www.procurement.mdutama.com/login.php`
2. Login dengan:
   - **Email**: `admin@procurement.mdutama.com`
   - **Password**: `admin123`
3. ⚠️ **SEGERA UBAH PASSWORD!**

---

## 📁 Struktur File (di Rumahweb)

```
public_html/
├── .htaccess                    # Routing & security
├── index.php                    # Redirect ke login/dashboard
├── login.php                    # Halaman login
├── dashboard.php                # Dashboard utama
├── vendors.php                  # Master data vendor
├── projects.php                 # Master data project
├── po_create.php                # Form create/edit PO
├── po_list.php                  # Daftar PO
├── po_detail.php                # Detail PO + upload lampiran
├── pratinjau_cetak_po_pdf.php   # Print/Preview PO PDF
├── reports.php                  # Laporan & ekspor Excel
├── settings.php                 # Pengaturan template PO
├── api/
│   ├── config.php               # ⚠️ Koneksi Supabase PostgreSQL
│   ├── auth.php                 # Login/logout API
│   ├── dashboard.php            # Dashboard stats API
│   ├── vendors.php              # CRUD vendors API
│   ├── projects.php             # CRUD projects API
│   ├── po.php                   # CRUD purchase orders API
│   ├── reports.php              # Report data API
│   ├── uploads.php              # File upload handler
│   ├── settings.php             # System settings API
│   └── generate_po_number.php   # Auto PO number
├── assets/                      # Static assets
├── uploads/                     # File uploads
│   ├── npwp/
│   ├── signatures/
│   ├── quotations/
│   ├── invoices/
│   ├── supporting/
│   └── logo/
├── database.sql                 # Schema MySQL (lama)
├── database_supabase.sql        # ⭐ Schema PostgreSQL (baru)
├── README.txt                   # Dokumentasi lama
├── README_DEPLOYMENT.md         # Panduan deployment lama
└── README_SUPABASE.md           # ⭐ Panduan ini
```

---

## 🗄️ Database Schema (PostgreSQL)

| Table | Purpose |
|-------|---------|
| `users` | Admin/user accounts |
| `vendors` | Vendor master data |
| `projects` | Project registry |
| `purchase_orders` | PO headers |
| `po_items` | Line items per PO |
| `po_attachments` | File uploads |
| `po_activity_log` | Audit trail |
| `system_settings` | Config key-value |

---

## 🔧 Troubleshooting

### Error: "PostgreSQL extension not available"
- Pastikan php_pgsql sudah diaktifkan di cPanel → PHP Selector
- Hubungi support Rumahweb jika tidak bisa diaktifkan

### Error: "Koneksi database gagal"
- Cek kredensial di `api/config.php`
- Pastikan host, port, user, password benar
- Cek apakah IP hosting di-whitelist di Supabase (Settings → Database → Network)

### Error: "SSL connection required"
- Pastikan `DB_SSLMODE` di-set ke `'require'`
- Beberapa hosting memerlukan `prefer` atau `require`

### Upload Error
- Cek folder permissions (755)
- Pastikan `upload_max_filesize` >= 10M

### CSS/JS Tidak Load
- Pastikan koneksi internet aktif (CDN Tailwind CSS)
- Clear browser cache

---

## 🔒 Security Notes

1. **SSL/TLS**: Semua koneksi ke Supabase menggunakan SSL
2. **Prepared Statements**: Semua query menggunakan parameterized queries
3. **Input Sanitization**: `htmlspecialchars()` + `strip_tags()` pada semua input
4. **Session Security**: httpOnly, secure, samesite=Lax cookies
5. **File Upload**: Validasi MIME type, ukuran, nama unik
6. **Rate Limiting**: Proteksi brute force pada login

---

## 📊 Fitur yang Tersedia

✅ Authentication (Login/Logout/Session)
✅ Master Vendor CRUD
✅ Master Project CRUD
✅ Purchase Order CRUD (Create/Read/Update/Delete)
✅ Dynamic Item Rows
✅ Auto PO Number (PO-YYYYMM-XXX)
✅ PPN Toggle (PPN 11% / Non-PPN)
✅ Status Tracking (Draft → Printed → Signed → Invoiced → Completed)
✅ File Attachments (Invoice, Quotation, Wet Signature, Supporting)
✅ Activity Log
✅ PDF Preview & Print
✅ Reports with Filters
✅ Excel Export
✅ Settings & Template Configuration
✅ Logo & Digital Signature Upload

---

## 📞 Support

- **Email**: admin@procurement.mdutama.com
- **Supabase Dashboard**: https://supabase.com/dashboard
- **Rumahweb Support**: https://www.rumahweb.com/support

---

**Versi**: 2.0.0 (Supabase + Rumahweb)
**Terakhir diperbarui**: September 2026
