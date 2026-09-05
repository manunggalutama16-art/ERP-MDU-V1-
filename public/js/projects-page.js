import { dbReady, listProjects, addProject, updateProject, deleteProject, fmtRp, fmtDate, esc, toastOk, toastError } from './db.js'
import { showFormModal } from './ui-modal.js'

const STATUS_STYLE = {
  ACTIVE: 'bg-on-tertiary-container/10 text-on-tertiary-container',
  'ON HOLD': 'bg-secondary-fixed-dim/30 text-on-secondary-fixed-variant',
  PENDING: 'bg-error-container/20 text-error',
}

function statusPill(s) {
  const cls = STATUS_STYLE[s] || 'bg-on-tertiary-container/10 text-on-tertiary-container'
  return `<span class="inline-flex items-center px-2 py-0.5 rounded-full ${cls} font-label-sm text-[11px]">${esc(s || '')}</span>`
}

let current = []

function row(p) {
  const incl = Number(p.value_incl_ppn) > 0 ? Number(p.value_incl_ppn) : (Number(p.value_excl_ppn) || 0) * 1.11
  return `<tr class="hover:bg-primary/5 transition-colors group">
<td class="px-md py-4"><span class="code-pill px-3 py-1 rounded font-bold font-data-tabular text-[13px] inline-block tracking-wide">${esc(p.code || '-')}</span></td>
<td class="px-md py-4 font-label-md text-label-md text-primary whitespace-nowrap">${esc(p.name)}</td>
<td class="px-md py-4 font-body-md text-body-md text-on-surface-variant">${fmtDate(p.date)}</td>
<td class="px-md py-4 font-body-md text-body-md ${p.pm_name ? 'text-primary' : 'text-on-surface-variant italic'}">${esc(p.pm_name || 'Not Assigned')}</td>
<td class="px-md py-4 font-body-md text-body-md text-on-surface-variant max-w-[220px] truncate" title="${esc(p.address || '')}">${esc(p.address || '-')}</td>
<td class="px-md py-4 text-center">${p.gmap_url
    ? `<a class="inline-flex items-center gap-xs text-secondary hover:underline" href="${esc(p.gmap_url)}" target="_blank" rel="noopener" title="${esc(p.gmap_url)}"><span class="material-symbols-outlined text-[18px]">map</span>Maps</a>`
    : '<span class="text-on-surface-variant opacity-50">-</span>'}</td>
<td class="px-md py-4 font-data-tabular text-body-md text-primary whitespace-nowrap">${fmtRp(p.value_excl_ppn)}</td>
<td class="px-md py-4 font-data-tabular text-body-md text-primary whitespace-nowrap">${fmtRp(incl)}</td>
<td class="px-md py-4 font-body-md text-body-md text-on-surface-variant whitespace-nowrap">${esc(p.top || '-')}</td>
<td class="px-md py-4">${statusPill(p.status)}</td>
<td class="px-md py-4 text-right"><div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
<button class="p-2 text-on-surface-variant hover:text-secondary hover:bg-secondary/10 rounded transition-all" title="Edit" data-edit="${p.id}"><span class="material-symbols-outlined text-[20px]">edit</span></button>
<button class="p-2 text-on-surface-variant hover:text-error hover:bg-error/10 rounded transition-all" title="Delete" data-del="${p.id}"><span class="material-symbols-outlined text-[20px]">delete</span></button>
</div></td>
</tr>`
}

function render() {
  const tbody = document.getElementById('project-tbody')
  const label = document.getElementById('project-count-label')
  const stat = document.getElementById('project-stat-active')
  if (tbody) {
    tbody.innerHTML = current.length
      ? current.map(row).join('')
      : `<tr><td colspan="11" class="px-md py-xl text-center text-on-surface-variant">Belum ada project. Klik <b>Tambah Project</b> untuk menambah.</td></tr>`
  }
  if (label) label.textContent = `Showing ${current.length} of ${current.length} projects`
  if (stat) stat.textContent = current.filter((p) => p.status === 'ACTIVE').length
}

async function load() {
  if (!dbReady) return
  const { data, error } = await listProjects()
  if (error) {
    toastError('Gagal memuat project: ' + error.message)
    return
  }
  current = data || []
  render()
}

function buildFields(initial = {}) {
  return [
    { id: 'code', label: 'Kode Project', required: true, placeholder: 'PRJ-2024-001' },
    { id: 'name', label: 'Nama Project', required: true, placeholder: 'Nama project' },
    { id: 'date', label: 'Tanggal', type: 'date' },
    { id: 'pm_name', label: 'Nama Project Manager', placeholder: 'Nama penanggung jawab' },
    { id: 'address', label: 'Alamat', type: 'textarea', placeholder: 'Alamat lengkap lokasi project' },
    { id: 'gmap_url', label: 'Link Google Maps', placeholder: 'https://maps.google.com/?q=...' },
    { id: 'value_excl_ppn', label: 'Nilai Sebelum PPN', type: 'number', placeholder: '100000000' },
    { id: 'value_incl_ppn', label: 'Nilai Setelah PPN (11%)', type: 'number', placeholder: 'Otomatis: sebelum PPN x 1.11' },
    { id: 'top', label: 'Tempo Pembayaran (TOP)', placeholder: 'Net 30, COD, Net 60...' },
    { id: 'status', label: 'Status', type: 'select', options: [
      { value: 'ACTIVE', label: 'ACTIVE' },
      { value: 'ON HOLD', label: 'ON HOLD' },
      { value: 'PENDING', label: 'PENDING' },
    ] },
  ]
}

// Nilai setelah PPN: pakai input manual bila diisi, jika kosong hitung otomatis (PPN 11%).
function resolveInclPPN(exclRaw, inclRaw) {
  const excl = Number(exclRaw) || 0
  const incl = Number(inclRaw)
  return incl > 0 ? incl : excl * 1.11
}

async function openAdd() {
  const values = await showFormModal({
    title: 'Tambah Project Baru',
    submitLabel: 'Simpan Project',
    fields: buildFields(),
    onSubmit: async (v) => {
      const { error } = await addProject({
        ...v,
        value_excl_ppn: Number(v.value_excl_ppn) || 0,
        value_incl_ppn: resolveInclPPN(v.value_excl_ppn, v.value_incl_ppn),
        date: v.date || new Date().toISOString().slice(0, 10),
      })
      return error ? error.message : null
    },
  })
  if (values) {
    toastOk('Project ' + values.code + ' berhasil ditambahkan')
    load()
  }
}

async function openEdit(p) {
  const values = await showFormModal({
    title: 'Edit Project',
    submitLabel: 'Simpan Perubahan',
    fields: buildFields(p),
    initial: p,
    onSubmit: async (patch) => {
      const { error } = await updateProject(p.id, {
        ...patch,
        value_excl_ppn: Number(patch.value_excl_ppn) || 0,
        value_incl_ppn: resolveInclPPN(patch.value_excl_ppn, patch.value_incl_ppn),
        date: patch.date || p.date,
      })
      return error ? error.message : null
    },
  })
  if (values) {
    toastOk('Perubahan project disimpan')
    load()
  }
}

async function remove(p) {
  if (!confirm(`Hapus project "${p.name}"?`)) return
  const { error } = await deleteProject(p.id)
  if (error) {
    toastError('Gagal menghapus: ' + error.message)
    return
  }
  toastOk('Project dihapus')
  load()
}

document.getElementById('project-tbody')?.addEventListener('click', (e) => {
  const editBtn = e.target.closest('[data-edit]')
  const delBtn = e.target.closest('[data-del]')
  if (editBtn) {
    const p = current.find((x) => x.id === editBtn.dataset.edit)
    if (p) openEdit(p)
  } else if (delBtn) {
    const p = current.find((x) => x.id === delBtn.dataset.del)
    if (p) remove(p)
  }
})

document.getElementById('btn-add-project')?.addEventListener('click', openAdd)

const searchBoxes = document.querySelectorAll('input[placeholder*="Filter by project code"], input[placeholder*="Quick search projects"]')
searchBoxes.forEach((input) =>
  input.addEventListener('input', (e) => {
    const term = e.target.value.toLowerCase()
    document.querySelectorAll('#project-tbody tr').forEach((tr) => {
      tr.style.display = tr.innerText.toLowerCase().includes(term) ? '' : 'none'
    })
  })
)

load()
