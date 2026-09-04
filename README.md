# Nexus Procurement - ERP System

Aplikasi web procurement enterprise yang siap deploy ke Vercel.

## 📦 Struktur Project

Project ini telah dikonfigurasi untuk deployment ke **Vercel** sebagai static site.

### File Utama:
- `public/` - Berisi semua file HTML yang siap deploy
  - `index.html` - Halaman login
  - `purchase-orders.html` - Daftar Purchase Order
  - `create-po.html` - Buat PO baru
  - `po-detail.html` - Detail PO
  - `vendors.html` - Master Data Vendor
  - `projects.html` - Master Data Project
  - `reports.html` - Laporan & Ekspor Excel
  - `settings.html` - Pengaturan Template PO
  - `preview-po.html` - Preview Cetak PDF

### Konfigurasi:
- `package.json` - Dependencies (Vite)
- `vite.config.js` - Konfigurasi build tool
- `vercel.json` - Routing rules untuk Vercel
- `.gitignore` - File yang di-ignore dari git

## 🚀 Deploy ke Vercel

### Cara 1: Melalui Vercel Dashboard (Recommended)

1. Buka [vercel.com](https://vercel.com) dan login
2. Klik **"Add New Project"**
3. Import repository GitHub Anda
4. Vercel akan otomatis mendeteksi konfigurasi
5. Klik **"Deploy"**

### Cara 2: Melalui Vercel CLI

```bash
# Install Vercel CLI
npm i -g vercel

# Login ke Vercel
vercel login

# Deploy
vercel --prod
```

## 🔧 Development

```bash
# Install dependencies
npm install

# Jalankan development server
npm run dev

# Build untuk production
npm run build

# Preview build lokal
npm run preview
```

## 📝 Catatan Penting

### Integrasi Supabase

Untuk menghubungkan aplikasi dengan Supabase:

1. Buat project di [supabase.com](https://supabase.com)
2. Dapatkan API Key dan URL dari Settings > API
3. Buat file `.env.local`:
   ```
   VITE_SUPABASE_URL=your_supabase_url
   VITE_SUPABASE_ANON_KEY=your_supabase_anon_key
   ```
4. Update kode untuk memanggil Supabase API

### Environment Variables di Vercel

Jangan lupa set environment variables di Vercel Dashboard:
- `VITE_SUPABASE_URL`
- `VITE_SUPABASE_ANON_KEY`

## 🛠 Troubleshooting Error 404

Jika masih muncul error 404 di Vercel:

1. Pastikan file `vercel.json` ada di root folder
2. Cek bahwa `public/index.html` ada
3. Verify di Vercel Dashboard > Settings > Build & Development
4. Coba redeploy setelah push perubahan

## 📄 License

© 2024 ProcureCorp Solutions 
