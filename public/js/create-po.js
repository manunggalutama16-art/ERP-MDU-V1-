import {
  dbReady, listVendors, listProjects, nextPONumber, createPO,
  fmtRp, esc, toastOk, toastError,
} from './db.js'

const $ = (id) => document.getElementById(id)

function currentPPN() {
  const checked = document.querySelector('input[name="ppn_type"]:checked')
  return checked ? checked.value : 'non'
}

// Hitung ulang total (termasuk PPN) — menggantikan versi mock inline.
window.calculateTotal = function () {
  const rows = document.querySelectorAll('#table-body tr')
  let subtotal = 0
  rows.forEach((row) => {
    const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0
    const price = parseFloat(row.querySelector('.price-input')?.value) || 0
    const total = qty * price
    row.querySelector('.row-total').textContent = total.toLocaleString('id-ID')
    subtotal += total
  })
  const tax = currentPPN() === 'ppn' ? subtotal * 0.11 : 0
  const grand = subtotal + tax
  const taxEl = $('tax-amount')
  const grandEl = $('grand-total')
  if (taxEl) taxEl.textContent = fmtRp(tax)
  if (grandEl) grandEl.textContent = fmtRp(grand)
}

async function fillSelects() {
  const venEl = $('vendor')
  const projEl = $('project')
  const [venRes, projRes] = await Promise.all([listVendors(), listProjects()])

  if (venRes.error) {
    toastError('Gagal memuat vendor: ' + venRes.error.message)
  } else if (venEl) {
    const opts = (venRes.data || []).map((v) => `<option value="${v.id}">${esc(v.name)}</option>`).join('')
    venEl.innerHTML = '<option disabled selected value="">Pilih Vendor dari Master Data</option>' + opts
  }

  if (projRes.error) {
    toastError('Gagal memuat project: ' + projRes.error.message)
  } else if (projEl) {
    const opts = (projRes.data || []).map((p) => `<option value="${p.id}">${esc(p.code)} - ${esc(p.name)}</option>`).join('')
    projEl.innerHTML = '<option disabled selected value="">Pilih Project dari Master Data</option>' + opts
  }
}

function collectItems() {
  const items = []
  document.querySelectorAll('#table-body tr').forEach((row) => {
    const desc = row.querySelector('input[type="text"]')?.value.trim()
    const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0
    const unit = row.querySelector('select')?.value || 'Pcs'
    const price = parseFloat(row.querySelector('.price-input')?.value) || 0
    if (desc) items.push({ description: desc, qty, unit, unit_price: price, line_total: qty * price })
  })
  return items
}

async function savePO(mode) {
  if (!dbReady) {
    toastError('Supabase belum dikonfigurasi. Cek VITE_SUPABASE_URL / VITE_SUPABASE_ANON_KEY.')
    return
  }
  const vendorId = $('vendor')?.value
  const projectId = $('project')?.value
  if (!vendorId || !projectId) {
    toastError('Vendor dan Project wajib dipilih.')
    return
  }
  const items = collectItems()
  if (!items.length) {
    toastError('Minimal satu item dengan deskripsi wajib diisi.')
    return
  }
  const subtotal = items.reduce((a, i) => a + i.line_total, 0)
  const tax = currentPPN() === 'ppn' ? subtotal * 0.11 : 0

  const approved = document.querySelector('input[name="approval_status"]:checked')?.value === 'yes'
  const quoted = document.querySelector('input[name="quotation"]:checked')?.value === 'yes'

  // Cari tombol lalu nonaktifkan sementara
  let poNumber = ($('po-number')?.value || '').trim()
  if (!poNumber) {
    const { data } = await nextPONumber()
    poNumber = data || 'PO-UNKNOWN'
  }

  const po = {
    po_number: poNumber,
    po_date: new Date().toISOString().slice(0, 10),
    vendor_id: vendorId,
    project_id: projectId,
    top: $('top')?.value.trim() || '',
    ship_to: $('delivery')?.value.trim() || '',
    ppn_type: currentPPN(),
    approval: approved,
    quotation: quoted,
    status: mode === 'print' ? (approved ? 'Signed' : 'Draft') : 'Draft',
    notes: $('notes')?.value.trim() || '',
    subtotal,
    tax_amount: tax,
    grand_total: subtotal + tax,
  }

  const { data, error } = await createPO(po, items)
  if (error) {
    let msg = error.message
    if (msg && msg.includes('duplicate')) msg = 'Nomor PO sudah dipakai. Ubah nomor lalu simpan lagi.'
    toastError('Gagal menyimpan PO: ' + msg)
    return
  }

  toastOk('PO ' + poNumber + ' berhasil disimpan')
  if (mode === 'print') {
    setTimeout(() => { window.location.href = 'preview-po.html?id=' + data.id }, 600)
  } else {
    setTimeout(() => { window.location.href = 'purchase-orders.html' }, 600)
  }
}

function wireButtons() {
  document.querySelectorAll('button').forEach((btn) => {
    const text = (btn.textContent || '').replace(/\s+/g, ' ').trim()
    if (text.startsWith('Simpan Draft')) {
      btn.addEventListener('click', () => savePO('draft'))
    } else if (text.startsWith('Simpan & Cetak')) {
      btn.addEventListener('click', () => savePO('print'))
    }
  })
}

// ---------- init ----------
fillSelects()
nextPONumber().then(({ data }) => {
  if (data && $('po-number') && !$('po-number').value.trim()) $('po-number').value = data
})
document.querySelectorAll('input[name="ppn_type"]').forEach((r) => r.addEventListener('change', window.calculateTotal))
wireButtons()
window.calculateTotal()
