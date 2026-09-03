================================================================================
         ERP PROCUREMENT MDU - NEXUS PROCUREMENT
         Deployment Guide & Documentation
================================================================================

PROJECT OVERVIEW
----------------
Aplikasi ERP Procurement untuk mengelola proses pengadaan barang/jasa.
Dikembangkan dari prototype HTML menjadi sistem full-stack dengan PHP backend
dan MySQL database, siap deploy ke Niagahoster.

DOMAIN: http://procurement.mdutama.com
TECH STACK: PHP 7.4+ / MySQL / Tailwind CSS / JavaScript (Vanilla)


================================================================================
FILE STRUCTURE (di server Niagahoster)
================================================================================

public_html/
├── .htaccess                    # Routing & security configuration
├── index.php                    # Redirect ke login/dashboard
├── login.php                    # Halaman login
├── dashboard.php                # Dashboard utama
├── vendors.php                  # Master data vendor
├── projects.php                 # Master data project
├── po_create.php                # Form create purchase order
├── po_list.php                  # Daftar purchase order
├── po_detail.php                # Detail PO + upload lampiran
├── pratinjau_cetak_po_pdf.php   # Print/Preview PO PDF format A4
├── reports.php                  # Laporan & ekspor data
├── settings.php                 # Pengaturan template PO
├── api/
│   ├── config.php               # Database connection & utilities
│   ├── auth.php                 # Login/logout API
│   ├── vendors.php              # CRUD vendors API
│   ├── projects.php             # CRUD projects API
│   ├── po.php                   # CRUD purchase orders API
│   ├── reports.php              # Report data API
│   ├── uploads.php              # File upload handler
│   ├── settings.php             # System settings API
│   └── generate_po_number.php   # Auto-generate PO number
├── assets/
│   ├── css/                     # Custom CSS (jika diperlukan)
│   └── js/                      # Custom JS (jika diperlukan)
├── uploads/
│   ├── npwp/                    # Upload dokumen NPWP vendor
│   ├── signatures/              # Upload tanda tangan digital
│   ├── quotations/              # Upload dokumen penawaran
│   ├── invoices/                # Upload invoice supplier
│   ├── supporting/              # Upload dokumen pendukung
│   └── logo/                    # Upload logo perusahaan (otomatis dibuat)
└── database.sql                 # Schema database MySQL


================================================================================
DATABASE SCHEMA
================================================================================

TABLES:
-------
1. users
   - id, name, email, password, role (admin/user)
   - Default admin: admin@procurement.mdutama.com / password: admin123

2. vendors
   - id, name, address, npwp, npwp_file, phone, contact_person, email
   - Contoh data: PT Sinar Teknologi, CV Logistik Mandiri, dll.

3. projects
   - id, code (unique), name, location, client, pic
   - value_before_ppn, value_inc_ppn, status (ACTIVE/ON HOLD/PENDING/COMPLETED)

4. purchase_orders
   - id, po_number (unique), vendor_id, project_id
   - top, delivery_location, status (Draft/Printed/Signed/Invoiced/Completed)
   - quotation_attached, approved, subtotal, ppn_percent, ppn_amount, grand_total
   - notes, created_by, created_at, updated_at

5. po_items
   - id, po_id, item_name, quantity, unit, price, total (generated)
   - sort_order untuk urutan item

6. po_attachments
   - id, po_id, type (invoice_supplier/quotation/wet_signature/supporting)
   - file_name, file_path, file_size, uploaded_by, uploaded_at

7. system_settings
   - id, setting_key (unique), setting_value
   - Menyimpan: company_name, company_address, signatory_name, signatory_title
   - signature_position, footer_note, signature_file, logo_file

8. po_activity_log
   - id, po_id, action (created/updated/status_changed/attachment_uploaded)
   - description, created_by, created_at
   - Menyimpan riwayat aktivitas tiap PO (dibuat otomatis saat pertama dipakai)


================================================================================
DEPLOYMENT STEPS TO NIAGAHOSTER
================================================================================

STEP 1: PREPARE DATABASE
-------------------------
1. Login ke cPanel Niagahoster
2. Buka menu "MySQL Database Wizard"
3. Buat database baru (contoh: mdutama_procurement)
4. Buat user database (contoh: mdutama_user) dan catat passwordnya
5. Berikan "All Privileges" ke user untuk database tersebut

STEP 2: UPLOAD FILES
---------------------
1. Di cPanel, buka "File Manager"
2. Navigasi ke folder public_html (atau folder domain Anda)
3. Upload seluruh isi folder deploy/ ke public_html/
4. Pastikan struktur folder seperti yang dijelaskan di atas

STEP 3: CONFIGURE DATABASE CONNECTION
--------------------------------------
Edit file: api/config.php

Cari baris:
  define('DB_USER', 'USERNAME_DATABASE_ANDA');
  define('DB_PASS', 'PASSWORD_DATABASE_ANDA');
  define('DB_NAME', 'NAMA_DATABASE_ANDA');

Ganti dengan:
  define('DB_USER', 'mdutama_user');        // Username dari Step 1
  define('DB_PASS', 'password_anda');        // Password dari Step 1
  define('DB_NAME', 'mdutama_procurement');  // Nama database dari Step 1

JIKA DEPLOY DI SUBFOLDER (misal: procurement.mdutama.com/erp/):
  define('APP_URL', 'http://procurement.mdutama.com/erp');

STEP 4: IMPORT DATABASE
------------------------
1. Di cPanel, buka "phpMyAdmin"
2. Pilih database yang dibuat di Step 1
3. Klik tab "Import"
4. Upload file: database.sql
5. Klik "Go"

STEP 5: SET FOLDER PERMISSIONS
-------------------------------
1. Di File Manager, klik kanan folder "uploads/"
2. Pilih "Change Permissions"
3. Set permission ke 755 (atau 775 jika diperlukan)
4. Klik "Change Permissions"

STEP 6: TESTING
---------------
1. Buka browser: http://procurement.mdutama.com/login.php
2. Login dengan:
   - Email: admin@procurement.mdutama.com
   - Password: admin123
3. Ganti password default segera setelah login pertama!


================================================================================
DEFAULT LOGIN CREDENTIALS
================================================================================

URL:      http://procurement.mdutama.com/login.php
Email:    admin@procurement.mdutama.com
Password: admin123

*** SEGERA UBAH PASSWORD SETELAH LOGIN PERTAMA ***


================================================================================
FITUR YANG TERSEDIA
================================================================================

1. AUTHENTICATION
   - Login/logout dengan session
   - Role-based access (admin/user)
   - Default admin user

2. MASTER DATA VENDOR
   - CRUD vendor (Create, Read, Update, Delete)
   - Field: nama, alamat, NPWP, kontak, email, PIC
   - Upload dokumen NPWP
   - Search & filter
   - Pagination

3. MASTER DATA PROJECT
   - CRUD project
   - Field: kode, nama, lokasi, client, PIC
   - Nilai sebelum PPN & incl PPN
   - Status: ACTIVE, ON HOLD, PENDING, COMPLETED
   - Search & filter

4. PURCHASE ORDER (PO)
   - Auto-generate PO number (format: PO-YYYYMM-XXX)
   - Dynamic item rows (add/remove)
   - Kalkulasi otomatis: subtotal, PPN 11%, grand total
   - PPN toggle (PPN / Non-PPN)
   - Select vendor & project dari master data
   - Approval status tracking
   - Quotation checklist
   - Simpan draft / simpan & cetak PDF

5. PO LIST & MANAGEMENT
   - Daftar PO dengan pagination
   - Filter by status & date range
   - Search real-time
   - Actions: Edit, Detail, Print, Upload Attachment
   - Ubah status PO (Draft / Printed / Signed / Invoiced / Completed)
     langsung dari halaman detail
   - Riwayat aktivitas (activity log) per PO: dibuat, diperbarui,
     perubahan status, dan unggahan lampiran

6. PO DETAIL & ATTACHMENTS
   - Tab: Detail PO & Dokumen & Lampiran
   - Upload invoice supplier
   - Upload dokumen penawaran (quotation)
   - Upload PO yang sudah di-TTD basah
   - Upload dokumen pendukung
   - Validation status tracking
   - Document activity timeline (otomatis dari po_activity_log)

7. REPORTS & EXPORT
   - Summary cards: total nilai PO, jumlah PO, vendor aktif
   - Filter by tanggal, proyek, status
   - Tabel data PO
   - Export ke Excel (fitur dalam pengembangan)

8. SETTINGS & TEMPLATE
   - Upload logo perusahaan
   - Edit identitas perusahaan (nama, alamat)
   - Edit detail pejabat penandatangan
   - Upload tanda tangan digital
   - Posisi tanda tangan (kiri/kanan)
   - Edit catatan kaki/footer
   - Live preview PDF


================================================================================
API ENDPOINTS
================================================================================

Base URL: http://procurement.mdutama.com/api/

1. auth.php
   - POST   /api/auth.php          - Login
   - GET    /api/auth.php?action=check  - Check auth status
   - POST   /api/auth.php          - Logout (action=logout)

2. vendors.php
   - GET    /api/vendors.php       - List vendors (with pagination & search)
   - GET    /api/vendors.php?id=X  - Get single vendor
   - POST   /api/vendors.php       - Create vendor (admin)
   - PUT    /api/vendors.php       - Update vendor (admin)
   - DELETE /api/vendors.php       - Delete vendor (admin)

3. projects.php
   - GET    /api/projects.php      - List projects (with pagination & search)
   - GET    /api/projects.php?id=X - Get single project
   - POST   /api/projects.php      - Create project (admin)
   - PUT    /api/projects.php      - Update project (admin)
   - DELETE /api/projects.php      - Delete project (admin)

4. po.php
   - GET    /api/po.php            - List POs (with pagination & search)
   - GET    /api/po.php?id=X       - Get single PO with items & attachments
   - POST   /api/po.php            - Create PO (admin)
   - PUT    /api/po.php            - Update PO (admin)
   - PUT    /api/po.php            - Update status only, body: {action:"status", id, status} (admin)
   - DELETE /api/po.php            - Delete PO (admin)

5. reports.php
   - GET    /api/reports.php       - Get report data with filters

6. uploads.php
   - POST   /api/uploads.php       - Upload file (multipart/form-data)
   - DELETE /api/uploads.php       - Delete file

7. settings.php
   - GET    /api/settings.php      - Get all settings
   - PUT    /api/settings.php      - Update settings (admin)

8. generate_po_number.php
   - GET    /api/generate_po_number.php - Generate next PO number


================================================================================
SECURITY NOTES
================================================================================

1. .htaccess configurations:
   - Block access to .env, .sql, .log, .md files
   - Block direct access to config.php
   - Security headers (X-Content-Type-Options, X-Frame-Options, etc.)
   - Compression & caching

2. Input sanitization:
   - All user inputs are sanitized via sanitize() function
   - SQL queries use prepared statements

3. File upload security:
   - File type validation
   - File size limit (10MB)
   - Unique file naming (uniqid())
   - Restricted upload directories
   - Akses langsung ke file PHP di dalam uploads/ diblokir via mod_rewrite

4. Authentication:
   - Session-based authentication
   - Role-based access control
   - Admin required for sensitive operations

5. Password:
   - Default password should be changed immediately
   - Consider implementing password hashing upgrade if needed


================================================================================
TROUBLESHOOTING
================================================================================

ERROR 500 - INTERNAL SERVER ERROR
- Check .htaccess syntax
- Ensure mod_rewrite is enabled
- Check error log in cPanel > Metrics > Errors
- Verify PHP version (7.4+ or 8.x)

DATABASE CONNECTION ERROR
- Verify DB credentials in api/config.php
- Ensure database is imported
- Check user privileges
- Try connecting via phpMyAdmin

UPLOAD NOT WORKING
- Check folder permissions (755 for uploads/)
- Verify upload_max_filesize in php.ini (should be 10M)
- Check post_max_size in php.ini
- Ensure file type is allowed

CSS/JS NOT LOADING
- Verify CDN Tailwind CSS is accessible
- Check assets folder uploaded correctly
- Clear browser cache
- Check browser console for errors

LOGIN FAILS
- Verify database import successful
- Check admin user exists in users table
- Verify session is working (check php.ini session settings)


================================================================================
PRODUCTION CHECKLIST
================================================================================

□ Change default admin password
□ Configure database credentials in api/config.php
□ Import database.sql via phpMyAdmin
□ Set uploads/ folder permission to 755
□ Test all CRUD operations (vendors, projects, PO)
□ Test file uploads
□ Test PDF print functionality
□ Enable HTTPS (SSL) in cPanel
□ Update APP_URL to https://procurement.mdutama.com
□ Enable error logging (display_errors = 0 in production)
□ Setup regular database backups
□ Test on mobile devices (responsive design)
□ Verify email functionality (if needed)


================================================================================
NEXT STEPS / ROADMAP
================================================================================

1. Email Notifications
   - PO approval notifications
   - PO status change alerts
   - Invoice reminders

2. Advanced Reporting
   - Charts & graphs (Chart.js)
   - Budget tracking
   - Vendor performance metrics

3. User Management
   - Add/edit users
   - Role management
   - Activity logs

4. PO Approval Workflow
   - Multi-level approval
   - Digital signature integration
   - Email notifications

5. Inventory Integration
   - Link PO to inventory
   - Stock tracking
   - Receiving reports

6. Invoice Management
   - Create invoice from PO
   - Payment tracking
   - Vendor payment history


================================================================================
SUPPORT & CONTACT
================================================================================

Documentation: See system_architecture_schema.md for detailed schema
Email: admin@procurement.mdutama.com
Domain: http://procurement.mdutama.com

================================================================================
Generated: 2026-08-18
Version: 1.0.0
================================================================================
