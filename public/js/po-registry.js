import { listPOs, listVendors, fmtRp, fmtDate, esc, dbReady, toastError } from './db.js'

const STATUS_PILL = {
  Draft: 'bg-surface-container-highest text-on-surface-variant',
  Printed: 'bg-primary-fixed text-on-primary-fixed-variant',
  Signed: 'bg-secondary-fixed text-on-secondary-fixed-variant',
  Invoiced: 'bg-tertiary-fixed text-on-tertiary-fixed-variant',
  Completed: 'bg-on-tertiary-container/10 text-on-tertiary-container',
}

function pill(status) {
  const cls = STATUS_PILL[status] || 'bg-surface-container-highest text-on-surface-variant'
  return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${cls}">${esc(status)}</span>`
}

function row(po) {
  const vendor = po.vendors?.name || '-'
  const project = po.projects?.name || '-'
  return `<tr class="table-row-hover transition-colors">
<td class="px-lg py-md font-bold text-secondary"><a class="hover:underline" href="po-detail.html?id=${po.id}">${esc(po.po_number)}</a></td>
<td class="px-lg py-md text-on-surface-variant">${fmtDate(po.po_date)}</td>
<td class="px-lg py-md font-semibold">${esc(vendor)}</td>
<td class="px-lg py-md">${esc(project)}</td>
<td class="px-lg py-md text-right font-bold">${fmtRp(po.grand_total)}</td>
<td class="px-lg py-md text-center">${pill(po.status)}</td>
<td class="px-lg py-md text-right"><div class="flex items-center justify-end gap-md">
<a class="text-on-surface-variant hover:text-secondary transition-colors" title="Detail" href="po-detail.html?id=${po.id}"><span class="material-symbols-outlined" data-icon="visibility">visibility</span></a>
<a class="text-on-surface-variant hover:text-secondary transition-colors" title="Cetak" href="preview-po.html?id=${po.id}"><span class="material-symbols-outlined" data-icon="print">print</span></a>
</div></td>
</tr>`
}

function applyFilter() {
  const term = (document.getElementById('registry-filter')?.value || '').toLowerCase()
  const term2 = (document.getElementById('registry-filter-2')?.value || '').toLowerCase()
  document.querySelectorAll('#po-tbody tr').forEach((tr) => {
    const text = tr.innerText.toLowerCase()
    tr.style.display = text.includes(term) && text.includes(term2) ? '' : 'none'
  })
}

async function load() {
  const tbody = document.getElementById('po-tbody')
  const countLabel = document.getElementById('po-count-label')
  if (!dbReady) {
    if (countLabel) countLabel.textContent = 'Supabase belum dikonfigurasi'
    return
  }

  const [poRes, venRes] = await Promise.all([listPOs(), listVendors()])

  if (poRes.error) {
    toastError('Gagal memuat PO: ' + poRes.error.message)
    if (countLabel) countLabel.textContent = 'Gagal memuat data'
    return
  }

  const pos = poRes.data || []
  const vendors = (venRes.data || [])

  if (tbody) {
    tbody.innerHTML = pos.length
      ? pos.map(row).join('')
      : `<tr><td colspan="7" class="px-lg py-xl text-center text-on-surface-variant">Belum ada Purchase Order. Klik <b>Buat PO Baru</b> untuk membuat yang pertama.</td></tr>`
  }
  if (countLabel) {
    countLabel.textContent = `Showing 1 to ${pos.length} of ${pos.length} results`
  }

  // Statistik kartu
  const total = pos.length
  const pending = pos.filter((p) => p.status === 'Draft' || p.status === 'Printed').length
  const sum = pos.reduce((acc, p) => acc + (Number(p.grand_total) || 0), 0)
  const set = (id, v) => {
    const el = document.getElementById(id)
    if (el) el.textContent = v
  }
  set('stat-total', total)
  set('stat-pending', pending)
  set('stat-value', fmtRp(sum))
  set('stat-vendors', vendors.length)
}

document.getElementById('registry-filter')?.addEventListener('input', applyFilter)
document.getElementById('registry-filter-2')?.addEventListener('input', applyFilter)

load()
