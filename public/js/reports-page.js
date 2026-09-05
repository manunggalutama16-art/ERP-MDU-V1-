import { dbReady, listPOs, listProjects, listVendors, fmtRp, fmtDate, esc, toastOk, toastError } from './db.js'

const STATUS_STYLE = {
  Draft: 'bg-surface-container-highest text-on-surface-variant',
  Printed: 'bg-primary-fixed text-on-primary-fixed-variant',
  Signed: 'bg-secondary/10 text-secondary',
  Invoiced: 'bg-tertiary-fixed-dim/20 text-on-tertiary-fixed-variant',
  Completed: 'bg-tertiary-fixed-dim/20 text-on-tertiary-fixed-variant',
}

let pos = []

function renderRows() {
  const tbody = document.getElementById('rep-tbody')
  const label = document.getElementById('rep-label')
  const status = document.getElementById('rep-status')?.value || ''
  const project = document.getElementById('rep-project')?.value || ''

  let list = pos
  if (status) list = list.filter((p) => p.status === status)
  if (project) list = list.filter((p) => String(p.projects?.name || '').includes(project))

  if (tbody) {
    tbody.innerHTML = list.length
      ? list.map((po) => {
          const pill = STATUS_STYLE[po.status] || 'bg-surface-container-highest text-on-surface-variant'
          return `<tr class="table-row-hover transition-colors">
<td class="px-md py-md font-data-tabular text-data-tabular text-primary font-medium"><a class="hover:underline" href="po-detail.html?id=${po.id}">${esc(po.po_number)}</a></td>
<td class="px-md py-md font-data-tabular text-data-tabular text-on-surface-variant">${fmtDate(po.po_date)}</td>
<td class="px-md py-md font-body-md text-body-md text-on-surface-variant">${esc(po.projects?.name || '-')}</td>
<td class="px-md py-md font-data-tabular text-data-tabular text-right text-on-surface-variant">${po.projects?.value_excl_ppn ? fmtRp(po.projects.value_excl_ppn) : '-'}</td>
<td class="px-md py-md font-data-tabular text-data-tabular text-right text-on-surface-variant">${po.projects?.value_incl_ppn ? fmtRp(po.projects.value_incl_ppn) : '-'}</td>
<td class="px-md py-md font-body-md text-body-md text-on-surface-variant">${esc(po.vendors?.name || '-')}</td>
<td class="px-md py-md font-data-tabular text-data-tabular text-right text-primary font-bold">${fmtRp(po.grand_total)}</td>
<td class="px-md py-md text-center"><span class="inline-flex px-sm py-1 rounded-full ${pill} font-label-sm text-[10px] uppercase">${esc(po.status)}</span></td>
</tr>`
        }).join('')
      : `<tr><td colspan="8" class="px-md py-xl text-center text-on-surface-variant">Tidak ada data PO.</td></tr>`
  }
  if (label) label.textContent = `Menampilkan ${list.length} dari ${pos.length} data`
}

async function load() {
  const [poR, projR, venR] = await Promise.all([listPOs(), listProjects(), listVendors()])
  if (poR.error) {
    toastError('Gagal memuat laporan: ' + poR.error.message)
    return
  }
  pos = poR.data || []

  const sum = pos.reduce((a, p) => a + (Number(p.grand_total) || 0), 0)
  const set = (id, v) => {
    const el = document.getElementById(id)
    if (el) el.textContent = v
  }
  set('rep-total', fmtRp(sum))
  set('rep-count', pos.length)
  set('rep-vendors', (venR.data || []).length)

  const projectSel = document.getElementById('rep-project')
  if (projectSel && !projectSel.dataset.filled) {
    projectSel.dataset.filled = '1'
    const opts = (projR.data || []).map((p) => `<option value="${esc(p.name)}">${esc(p.name)}</option>`).join('')
    projectSel.insertAdjacentHTML('beforeend', opts)
  }

  renderRows()
}

function filteredList() {
  const status = document.getElementById('rep-status')?.value || ''
  const project = document.getElementById('rep-project')?.value || ''
  let list = pos
  if (status) list = list.filter((p) => p.status === status)
  if (project) list = list.filter((p) => String(p.projects?.name || '').includes(project))
  return list
}

// Ekspor data yang sedang terfilter ke file Excel (.xlsx) via SheetJS.
function exportExcel() {
  if (typeof XLSX === 'undefined') {
    toastError('Library SheetJS belum termuat. Periksa koneksi internet lalu muat ulang halaman.')
    return
  }
  const list = filteredList()
  if (!list.length) {
    toastError('Tidak ada data untuk diekspor. Sesuaikan filter terlebih dahulu.')
    return
  }
  const rows = list.map((po) => ({
    'PO Number': po.po_number,
    'Tanggal': po.po_date ? new Date(po.po_date).toLocaleDateString('en-GB') : '-',
    'Project': po.projects?.name || '-',
    'Nilai Project (Sebelum PPN)': Number(po.projects?.value_excl_ppn) || 0,
    'Nilai Project (Setelah PPN)': Number(po.projects?.value_incl_ppn) || 0,
    'Vendor': po.vendors?.name || '-',
    'Total PO (Rp)': Number(po.grand_total) || 0,
    'Status': po.status || '',
  }))
  const ws = XLSX.utils.json_to_sheet(rows)
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'Laporan PO')
  XLSX.writeFile(wb, 'laporan-purchase-order.xlsx')
  toastOk('File Excel berhasil diunduh')
}

document.getElementById('rep-status')?.addEventListener('change', renderRows)
document.getElementById('rep-project')?.addEventListener('change', renderRows)
document.querySelectorAll('button').forEach((b) => {
  if ((b.textContent || '').includes('Terapkan Filter')) b.addEventListener('click', renderRows)
  if ((b.textContent || '').includes('Download Excel')) b.addEventListener('click', exportExcel)
})

if (dbReady) load()
