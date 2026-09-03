# 🚀 Deployment Guide: Vercel + Supabase

## Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────────┐
│                    Vercel (Frontend)                        │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  Next.js Application                                │   │
│  │  - React Components                                 │   │
│  │  - Supabase JS Client                               │   │
│  │  - Tailwind CSS                                     │   │
│  └─────────────────────────────────────────────────────┘   │
│  URL: https://www.procurement.mdutama.com                  │
└─────────────────────────────────────────────────────────────┘
                              │
                              │ HTTPS (Browser)
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    Supabase Cloud                           │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  1. PostgreSQL Database                             │   │
│  │  2. Supabase Auth                                   │   │
│  │  3. Row Level Security (RLS)                        │   │
│  │  4. File Storage                                    │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

---

## 📋 Prasyarat

1. ✅ Akun **Vercel** (gratis): https://vercel.com
2. ✅ Akun **Supabase** (gratis): https://supabase.com
3. ✅ **Node.js 18+** terinstall di komputer lokal
4. ✅ **Git** terinstall

---

## 🚀 Langkah 1: Setup Supabase

### 1.1 Buat Project Supabase
1. Login ke https://supabase.com/dashboard
2. Klik **"New Project"**
3. Isi:
   - **Organization**: Pilih atau buat baru
   - **Project name**: `procurement-mdutama`
   - **Database Password**: Buat password yang kuat (CATAT!)
   - **Region**: Singapore (terdekat)
4. Klik **"Create new project"**
5. Tunggu ~2 menit

### 1.2 Dapatkan API Keys
1. Buka project Supabase
2. Klik **Settings** → **API**
3. Copy:
   - **Project URL**: `https://xxxxxxxx.supabase.co`
   - **anon public key**: `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...`

### 1.3 Import Schema Database
1. Buka **SQL Editor** di Supabase Dashboard
2. Copy seluruh isi file `supabase_setup.sql`
3. Paste ke SQL Editor
4. Klik **"Run"**

### 1.4 Buat Admin User
1. Buka **Authentication** → **Users**
2. Klik **"Add user"**
3. Isi:
   - **Email**: `admin@procurement.mdutama.com`
   - **Password**: Buat password yang kuat
4. Klik **"Create user"**
5. Buka **SQL Editor**, jalankan:
   ```sql
   UPDATE public.users 
   SET role = 'admin' 
   WHERE email = 'admin@procurement.mdutama.com';
   ```

---

## 🚀 Langkah 2: Setup Vercel

### 2.1 Push ke GitHub
```bash
cd deploy/nextjs-app
git init
git add .
git commit -m "Initial commit: Nexus Procurement"
git remote add origin https://github.com/USERNAME/nexus-procurement.git
git push -u origin main
```

### 2.2 Deploy ke Vercel
1. Login ke https://vercel.com
2. Klik **"New Project"**
3. Pilih **"Import Git Repository"**
4. Pilih repository `nexus-procurement`
5. Klik **"Import"**

### 2.3 Konfigurasi Environment Variables
Di Vercel Dashboard, tab **"Environment Variables"**, tambahkan:

| Name | Value |
|------|-------|
| `NEXT_PUBLIC_SUPABASE_URL` | `https://xxxxxxxx.supabase.co` |
| `NEXT_PUBLIC_SUPABASE_ANON_KEY` | `eyJhbGciOiJIUzI1NiIs...` |

Klik **"Save"**

### 2.4 Deploy
1. Klik **"Deploy"**
2. Tunggu ~2 menit
3. Vercel akan memberikan URL: `https://nexus-procurement-xxxx.vercel.app`

---

## 🚀 Langkah 3: Setup Custom Domain

### 3.1 Tambah Domain di Vercel
1. Buka project di Vercel
2. Klik **"Settings"** → **"Domains"**
3. Masukkan: `www.procurement.mdutama.com`
4. Klik **"Add"**

### 3.2 Update DNS di Domain Registrar
Buka domain registrar (dimana Anda membeli domain), tambahkan:

**Type A Record:**
```
Name: @
Value: 76.76.21.21
```

**Type CNAME Record:**
```
Name: www
Value: cname.vercel-dns.com
```

### 3.3 Verifikasi Domain
1. Kembali ke Vercel → **Settings** → **Domains**
2. Tunggu beberapa menit hingga DNS terpropagasi
3. Status akan berubah menjadi **"Valid"**

---

## 🚀 Langkah 4: Test Aplikasi

1. Buka: `https://www.procurement.mdutama.com`
2. Login dengan:
   - **Email**: `admin@procurement.mdutama.com`
   - **Password**: (password yang dibuat di Step 1.4)
3. ✅ Test semua fitur:
   - Dashboard
   - Master Vendor (CRUD)
   - Master Project (CRUD)
   - Purchase Orders (CRUD)
   - Reports & Export Excel
   - Settings

---

## 📁 Struktur File

```
nextjs-app/
├── app/
│   ├── page.tsx              # Root redirect
│   ├── globals.css           # Global styles
│   ├── login/page.tsx        # Login page
│   ├── dashboard/page.tsx    # Dashboard
│   ├── vendors/page.tsx      # Vendor CRUD
│   ├── projects/page.tsx     # Project CRUD
│   ├── po/page.tsx           # PO list
│   ├── reports/page.tsx      # Reports & export
│   └── settings/page.tsx     # Settings
├── components/
│   ├── Sidebar.tsx           # Sidebar navigation
│   └── Header.tsx            # Top header
├── lib/
│   └── supabase.ts           # Supabase client config
├── .env.local                # Environment variables (lokal)
├── package.json              # Dependencies
├── tailwind.config.js        # Tailwind config
├── next.config.js            # Next.js config
└── tsconfig.json             # TypeScript config
```

---

## 🔧 Troubleshooting

### Error: "Invalid API key"
- Pastikan `NEXT_PUBLIC_SUPABASE_URL` dan `NEXT_PUBLIC_SUPABASE_ANON_KEY` benar
- Cek di Vercel → Settings → Environment Variables

### Error: "relation does not exist"
- Pastikan SQL schema sudah di-import ke Supabase
- Buka SQL Editor → jalankan `supabase_setup.sql`

### Error: "new row violates row-level security policy"
- Pastikan RLS policies sudah di-setup
- Pastikan user sudah login dan role benar

### Domain tidak bisa diakses
- Tunggu 5-10 menit untuk DNS propagation
- Cek DNS records sudah benar
- Gunakan https://dnschecker.org untuk verifikasi

---

## 🔒 Security Notes

1. **RLS (Row Level Security)**: Semua tabel di-protected
2. **Supabase Auth**: Authentication ditangani oleh Supabase
3. **HTTPS**: Vercel otomatis menyediakan SSL
4. **Environment Variables**: Tidak di-commit ke Git
5. **Client-side Only**: API keys yang digunakan adalah anon key (public, aman untuk browser)

---

## 📊 Fitur yang Tersedia

✅ Authentication (Login/Logout via Supabase Auth)
✅ Master Vendor CRUD
✅ Master Project CRUD
✅ Purchase Order CRUD
✅ Auto PO Number
✅ PPN Toggle (11% / Non-PPN)
✅ Status Tracking
✅ Activity Log
✅ Reports with Filters
✅ Excel Export
✅ Settings & Template Configuration
✅ Real-time Updates

---

## 🔄 Updating Application

### Update Code
```bash
git add .
git commit -m "Update description"
git push origin main
```
Vercel akan otomatis rebuild dan deploy!

### Update Database Schema
1. Buka Supabase SQL Editor
2. Jalankan migration SQL
3. Deploy akan otomatis menggunakan schema baru

---

## 💰 Cost

- **Vercel**: Gratis (Hobby plan, unlimited deployments)
- **Supabase**: Gratis (500MB database, 1GB storage, 50K monthly active users)
- **Domain**: ~Rp 150.000/tahun

---

## 📞 Support

- **Vercel Docs**: https://vercel.com/docs
- **Supabase Docs**: https://supabase.com/docs
- **Email**: admin@procurement.mdutama.com

---

**Versi**: 2.0.0 (Vercel + Supabase)
**Terakhir diperbarui**: September 2026
