import { supabase, isSupabaseConfigured } from './supabase.js'

// ============================================================
// Lapisan data: semua akses ke tabel Supabase lewat helper ini.
// Setiap fungsi mengembalikan { data, error }.
// ============================================================

export const dbReady = isSupabaseConfigured

function table(name) {
  if (!supabase) {
    return { error: { message: 'Supabase belum dikonfigurasi. Cek VITE_SUPABASE_URL / VITE_SUPABASE_ANON_KEY.' } }
  }
  return supabase.from(name)
}

// ---------- VENDORS ----------
export async function listVendors() {
  const r = table('vendors')
  if (r.error) return r
  return r.select('*').order('name', { ascending: true })
}

export async function addVendor(vendor) {
  const r = table('vendors')
  if (r.error) return r
  return r.insert([vendor]).select().single()
}

export async function updateVendor(id, patch) {
  const r = table('vendors')
  if (r.error) return r
  return r.update(patch).eq('id', id).select().single()
}

export async function deleteVendor(id) {
  const r = table('vendors')
  if (r.error) return r
  return r.delete().eq('id', id)
}

// ---------- PROJECTS ----------
export async function listProjects() {
  const r = table('projects')
  if (r.error) return r
  return r.select('*').order('created_at', { ascending: false })
}

export async function addProject(project) {
  const r = table('projects')
  if (r.error) return r
  return r.insert([project]).select().single()
}

export async function updateProject(id, patch) {
  const r = table('projects')
  if (r.error) return r
  return r.update(patch).eq('id', id).select().single()
}

export async function deleteProject(id) {
  const r = table('projects')
  if (r.error) return r
  return r.delete().eq('id', id)
}

// ---------- PURCHASE ORDERS ----------
export async function listPOs() {
  const r = table('purchase_orders')
  if (r.error) return r
  return r.select('*, vendors(name), projects(name)').order('created_at', { ascending: false })
}

export async function getPO(id) {
  const r = table('purchase_orders')
  if (r.error) return r
  return r.select('*, vendors(*), projects(*)').eq('id', id).maybeSingle()
}

export async function getPOItems(poId) {
  const r = table('po_items')
  if (r.error) return r
  return r.select('*').eq('po_id', poId).order('created_at', { ascending: true })
}

// Nomor PO otomatis: PO-<TAHUN>-<0001>
export async function nextPONumber() {
  const year = new Date().getFullYear()
  const r = table('purchase_orders')
  if (r.error) return r
  const { data, error } = await r.select('po_number')
  if (error) return { error }
  const prefix = `PO-${year}-`
  let max = 0
  data.forEach((row) => {
    const m = String(row.po_number || '').match(/PO-\d{4}-(\d+)$/)
    if (m) max = Math.max(max, parseInt(m[1], 10))
  })
  return { data: `${prefix}${String(max + 1).padStart(4, '0')}` }
}

// Simpan PO beserta item-itemnya.
// po: { po_number, po_date, vendor_id, project_id, top, ship_to, ppn_type, approval, quotation, status, notes, subtotal, tax_amount, grand_total }
// items: [{ description, qty, unit, unit_price, line_total }]
export async function createPO(po, items) {
  const r = table('purchase_orders')
  if (r.error) return r
  const { data: poRow, error } = await r.insert([po]).select().single()
  if (error) return { error }

  if (items && items.length) {
    const rows = items.map((it) => ({ po_id: poRow.id, ...it }))
    const { error: itemErr } = await table('po_items').insert(rows)
    if (itemErr) return { error: itemErr }
  }
  return { data: poRow }
}

// ---------- COMPANY SETTINGS (template PO) ----------
export async function getSettings() {
  const r = table('company_settings')
  if (r.error) return r
  return r.select('*').eq('id', 1).maybeSingle()
}

export async function saveSettings(settings) {
  const r = table('company_settings')
  if (r.error) return r
  return r.update({ ...settings, updated_at: new Date().toISOString() }).eq('id', 1).select().single()
}

// ============================================================
// Helper kecil untuk tampilan
// ============================================================
export function fmtRp(n) {
  const v = Number(n) || 0
  return 'Rp ' + v.toLocaleString('id-ID')
}

export function fmtDate(d) {
  if (!d) return '-'
  const dt = new Date(d)
  if (isNaN(dt)) return String(d)
  return dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

export function esc(s) {
  return String(s == null ? '' : s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
}

// Toast sukses (hijau)
export function toastOk(msg, container = document.body) {
  const el = document.createElement('div')
  el.style.cssText =
    'position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:99999;max-width:90vw;' +
    'background:#005236;color:#fff;padding:10px 18px;border-radius:10px;font:500 13px Inter,sans-serif;' +
    'box-shadow:0 6px 20px rgba(0,0,0,.25);'
  el.textContent = msg
  container.appendChild(el)
  setTimeout(() => el.remove(), 4000)
}

// Tampilkan pesan error "berat" (mis. tabel belum dibuat) di atas halaman
export function toastError(msg, container = document.body) {
  const el = document.createElement('div')
  el.style.cssText =
    'position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:99999;max-width:90vw;' +
    'background:#ba1a1a;color:#fff;padding:10px 18px;border-radius:10px;font:500 13px Inter,sans-serif;' +
    'box-shadow:0 6px 20px rgba(0,0,0,.25);'
  el.textContent = msg
  container.appendChild(el)
  setTimeout(() => el.remove(), 6000)
}
