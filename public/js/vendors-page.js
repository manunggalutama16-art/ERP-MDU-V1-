import { dbReady, listVendors, addVendor, updateVendor, deleteVendor, esc, toastOk, toastError } from './db.js'
import { showFormModal } from './ui-modal.js'

const FIELDS = [
  { id: 'name', label: 'Nama Vendor', required: true, placeholder: 'PT Contoh Sejahtera' },
  { id: 'category', label: 'Kategori', placeholder: 'Technology & Hardware' },
  { id: 'address', label: 'Alamat', type: 'textarea' },
  { id: 'pic_name', label: 'NAMA PIC', placeholder: 'Nama contact person' },
  { id: 'npwp', label: 'NPWP', placeholder: '00.000.000.0-000.000' },
  { id: 'phone', label: 'Kontak / Telepon', placeholder: '+62 8xx xxxx xxxx' },
  { id: 'email', label: 'Email', type: 'email', placeholder: 'kontak@perusahaan.com' },
]

let current = []

function initials(name) {
  const first = (name || '').trim().split(/\s+/)[0] || 'V'
  return first.slice(0, 2).toUpperCase()
}

function row(v) {
  return `<tr class="hover:bg-primary/5 transition-colors group">
<td class="px-lg py-md">
  <div class="flex items-center gap-md">
    <div class="w-8 h-8 rounded bg-secondary/10 flex items-center justify-center text-secondary font-bold">${esc(initials(v.name))}</div>
    <div>
      <p class="font-label-md text-label-md text-on-surface">${esc(v.name)}</p>
      <p class="text-body-sm font-body-sm text-on-surface-variant">${esc(v.category || '')}</p>
    </div>
  </div>
</td>
<td class="px-lg py-md text-body-md font-body-md text-on-surface-variant max-w-xs truncate">${esc(v.address || '-')}</td>
<td class="px-lg py-md text-body-md font-body-md text-on-surface-variant">${esc(v.pic_name || '-')}</td>
<td class="px-lg py-md font-data-tabular text-data-tabular">${esc(v.npwp || '-')}</td>
<td class="px-lg py-md text-body-md font-body-md text-on-surface-variant">${esc(v.phone || '-')}</td>
<td class="px-lg py-md text-body-md font-body-md text-secondary">${esc(v.email || '-')}</td>
<td class="px-lg py-md">
  <div class="flex items-center justify-center gap-sm">
    <button class="p-1.5 text-on-surface-variant hover:text-secondary hover:bg-secondary/10 rounded transition-all" title="Edit" data-edit="${v.id}"><span class="material-symbols-outlined">edit</span></button>
    <button class="p-1.5 text-on-surface-variant hover:text-error hover:bg-error/10 rounded transition-all" title="Delete" data-del="${v.id}"><span class="material-symbols-outlined">delete</span></button>
  </div>
</td>
</tr>`
}

function render() {
  const tbody = document.getElementById('vendor-tbody')
  const label = document.getElementById('vendor-count-label')
  const stat = document.getElementById('vendor-stat-active')
  if (tbody) {
    tbody.innerHTML = current.length
      ? current.map(row).join('')
      : `<tr><td colspan="7" class="px-lg py-xl text-center text-on-surface-variant">Belum ada vendor. Klik <b>Tambah Vendor</b> untuk menambah.</td></tr>`
  }
  if (label) label.textContent = `Showing ${current.length} of ${current.length} vendors`
  if (stat) stat.textContent = current.length
}

async function load() {
  if (!dbReady) return
  const { data, error } = await listVendors()
  if (error) {
    toastError('Gagal memuat vendor: ' + error.message)
    return
  }
  current = data || []
  render()
}

async function openAdd() {
  const values = await showFormModal({
    title: 'Tambah Vendor Baru',
    submitLabel: 'Simpan Vendor',
    fields: FIELDS,
    onSubmit: async (v) => {
      const { error } = await addVendor(v)
      return error ? error.message : null
    },
  })
  if (values) {
    toastOk('Vendor ' + values.name + ' berhasil ditambahkan')
    load()
  }
}

async function openEdit(v) {
  const values = await showFormModal({
    title: 'Edit Vendor',
    submitLabel: 'Simpan Perubahan',
    fields: FIELDS,
    initial: v,
    onSubmit: async (patch) => {
      const { error } = await updateVendor(v.id, patch)
      return error ? error.message : null
    },
  })
  if (values) {
    toastOk('Perubahan vendor disimpan')
    load()
  }
}

async function remove(v) {
  if (!confirm(`Hapus vendor "${v.name}"? PO yang memakai vendor ini akan menampilkan "-".`)) return
  const { error } = await deleteVendor(v.id)
  if (error) {
    toastError('Gagal menghapus: ' + error.message)
    return
  }
  toastOk('Vendor dihapus')
  load()
}

document.getElementById('vendor-tbody')?.addEventListener('click', (e) => {
  const editBtn = e.target.closest('[data-edit]')
  const delBtn = e.target.closest('[data-del]')
  if (editBtn) {
    const v = current.find((x) => x.id === editBtn.dataset.edit)
    if (v) openEdit(v)
  } else if (delBtn) {
    const v = current.find((x) => x.id === delBtn.dataset.del)
    if (v) remove(v)
  }
})

document.getElementById('btn-add-vendor')?.addEventListener('click', openAdd)

const searchBox = document.querySelector('input[placeholder*="Search by name"]')
searchBox?.addEventListener('input', (e) => {
  const term = e.target.value.toLowerCase()
  document.querySelectorAll('#vendor-tbody tr').forEach((tr) => {
    tr.style.display = tr.innerText.toLowerCase().includes(term) ? '' : 'none'
  })
})

load()
