import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
  root: 'public',
  envDir: resolve(__dirname), // muat .env.local dari root repo, bukan dari public/
  build: {
    outDir: '../dist',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        index: resolve(__dirname, 'public/index.html'),
        'purchase-orders': resolve(__dirname, 'public/purchase-orders.html'),
        'create-po': resolve(__dirname, 'public/create-po.html'),
        'po-detail': resolve(__dirname, 'public/po-detail.html'),
        'preview-po': resolve(__dirname, 'public/preview-po.html'),
        projects: resolve(__dirname, 'public/projects.html'),
        vendors: resolve(__dirname, 'public/vendors.html'),
        reports: resolve(__dirname, 'public/reports.html'),
        settings: resolve(__dirname, 'public/settings.html'),
      'master-data': resolve(__dirname, 'public/master-data.html'),
      },
    },
  },
  server: {
    port: 3000
  },
  preview: {
    port: 3000,
    host: true
  }
})