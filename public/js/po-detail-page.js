import { dbReady, getPO, getPOItems, fmtRp, fmtDate, esc, toastError } from './db.js'

const PILL = {
  Draft: 'bg-surface-container-highest text-on-surface-variant',
  Printed: 'bg-primary-fixed text-on-primary-fixed-variant',
  Signed: 'bg-tertiary-container/10 text-on-tertiary-container border border-tertiary-container/20',
  Invoiced: 'bg-tertiary-fixed text-on-tertiary-fixed-variant',
  Completed: 'bg-secondary-fixed text-on-secondary-fixed-variant',
}

async function init() {
  const id = new URLSearchParams(location.search).get('id')
  if (!id) {
    toastError('PO tidak ditemukan. Parameter ?id= tidak ada.')
    return
  }

  const { data: po, error } = await getPO(id)
  if (error || !po) {
    toastError('Gagal memuat PO: ' + (error ? error.message : 'Data tidak ditemukan'))
    return
  }
  const { data: items, error: itemErr } = await getPOItems(id)
  if (itemErr) toastError('Gagal memuat item: ' + itemErr.message)

  const header = document.querySelector('main > section')
  if (header) {
    // Status pill + nomor PO
    const pill = header.querySelector('span[class*="bg-tertiary-container/10"]')
    if (pill) {
      const cls = PILL[po.status] || PILL.Draft
      pill.className = 'px-sm py-xs rounded-full text-[10px] font-bold uppercase tracking-tighter ' + cls
      pill.textContent = po.status
    }
    const h3 = header.querySelector('h3')
    if (h3) h3.textContent = po.po_number

    // Vendor / Tanggal / Total (tiga <p class="font-label-md text-primary"> di blok kanan)
    const vals = header.querySelectorAll('p.font-label-md.text-primary')
    if (vals[0]) vals[0].textContent = po.vendors?.name || '-'
    if (vals[1]) vals[1].textContent = fmtDate(po.po_date)
    if (vals[2]) vals[2].textContent = fmtRp(po.grand_total)

    // Ganti tombol header: Print PDF -> preview; Edit PO -> kembali
    const btns = header.querySelectorAll('button')
    btns.forEach((btn) => {
      const text = (btn.textContent || '').trim()
      if (text.includes('Print PDF')) {
        const a = document.createElement('a')
        a.href = 'preview-po.html?id=' + po.id
        a.className = btn.className
        a.style.cssText = btn.style.cssText
        a.innerHTML = btn.innerHTML
        a.addEventListener('click', () => {})
        btn.replaceWith(a)
      } else if (text.includes('Edit PO')) {
        const a = document.createElement('a')
        a.href = 'purchase-orders.html'
        a.className = btn.className
        a.style.cssText = btn.style.cssText
        a.innerHTML = '<span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke Daftar'
        btn.replaceWith(a)
      }
    })
  }

  // Tab Detail PO: isi ringkasan + daftar item
  const body = document.getElementById('content-detail-po')
  if (body) {
    const vendor = po.vendors || {}
    const project = po.projects || {}
    const ppn = po.ppn_type === 'ppn'
    body.innerHTML = `
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-md mb-lg">
        ${metaCard('Vendor', vendor.name || '-', vendor.address || '', vendor.email || '')}
        ${metaCard('Project', project.code ? project.code + ' - ' + project.name : '-', project.pm_name ? 'Project Manager: ' + project.pm_name : '', project.top ? 'TOP: ' + project.top : '')}
        ${metaCard('Term of Payment (TOP)', po.top || '-', '', '')}
        ${metaCard('Delivery Location', po.ship_to || '-', '', '')}
        ${metaCard('Pajak', ppn ? 'PPN 11%' : 'Non PPN', 'Quotation: ' + (po.quotation ? 'Ya, terlampir' : 'Belum'), '')}
        ${metaCard('Persetujuan', po.approval ? 'Sudah Disetujui' : 'Belum Disetujui', 'Catatan: ' + (po.notes || '-'), '')}
      </div>
      <div class="overflow-x-auto border border-outline-variant rounded-xl">
        <table class="w-full text-left border-collapse">
          <thead><tr class="bg-surface-container-low">
            <th class="px-md py-sm text-label-sm uppercase text-on-surface-variant w-12">#</th>
            <th class="px-md py-sm text-label-sm uppercase text-on-surface-variant">Deskripsi Item</th>
            <th class="px-md py-sm text-label-sm uppercase text-on-surface-variant text-right">Qty</th>
            <th class="px-md py-sm text-label-sm uppercase text-on-surface-variant">Unit</th>
            <th class="px-md py-sm text-label-sm uppercase text-on-surface-variant text-right">Harga Satuan</th>
            <th class="px-md py-sm text-label-sm uppercase text-on-surface-variant text-right">Total</th>
          </tr></thead>
          <tbody class="divide-y divide-outline-variant font-data-tabular">
            ${(items || []).map((it, i) => `<tr class="hover:bg-surface-container-low/40">
              <td class="px-md py-md text-center text-on-surface-variant">${i + 1}</td>
              <td class="px-md py-md text-primary font-semibold">${esc(it.description)}</td>
              <td class="px-md py-md text-right">${Number(it.qty)}</td>
              <td class="px-md py-md text-center">${esc(it.unit || 'Pcs')}</td>
              <td class="px-md py-md text-right">${fmtRp(it.unit_price)}</td>
              <td class="px-md py-md text-right font-bold">${fmtRp(it.line_total)}</td>
            </tr>`).join('') || `<tr><td colspan="6" class="px-md py-lg text-center text-on-surface-variant">Tidak ada item.</td></tr>`}
            <tr class="bg-surface-container-low/60">
              <td colspan="5" class="px-md py-md text-right font-label-md text-on-surface-variant">Subtotal</td>
              <td class="px-md py-md text-right font-bold">${fmtRp(po.subtotal)}</td>
            </tr>
            ${ppn ? `<tr class="bg-surface-container-low/60"><td colspan="5" class="px-md py-md text-right font-label-md text-on-surface-variant">PPN (11%)</td><td class="px-md py-md text-right font-bold">${fmtRp(po.tax_amount)}</td></tr>` : ''}
            <tr class="bg-primary text-on-primary"><td colspan="5" class="px-md py-md text-right font-headline-sm">Grand Total</td><td class="px-md py-md text-right font-headline-sm">${fmtRp(po.grand_total)}</td></tr>
          </tbody>
        </table>
      </div>`

    // Update status validasi (panel kanan)
    const quoRow = findRowByLabel('Quotation Checklist')
    if (quoRow) setRowValue(quoRow, po.quotation ? 'Ya, Sudah Dilampirkan' : 'Belum Dilampirkan')
    const apprRow = findRowByLabel('Vendor Tax ID (NPWP)')
    if (apprRow) {
      setRowLabel(apprRow, 'Approval Status')
      setRowValue(apprRow, po.approval ? 'Sudah Disetujui' : 'Belum Disetujui')
    }
  }
}

function metaCard(title, value, sub, sub2) {
  return `<div class="bg-surface rounded-xl border border-outline-variant p-md">
    <p class="text-label-sm uppercase tracking-wider text-on-surface-variant mb-xs">${esc(title)}</p>
    <p class="font-label-md text-primary">${esc(value || '-')}</p>
    ${sub ? `<p class="text-body-sm text-on-surface-variant">${esc(sub)}</p>` : ''}
    ${sub2 ? `<p class="text-body-sm text-on-surface-variant">${esc(sub2)}</p>` : ''}
  </div>`
}

function findRowByLabel(label) {
  const labels = document.querySelectorAll('#content-dokumen span, #content-dokumen p')
  for (const el of labels) {
    if (el.textContent.trim() === label) return el.closest('.flex.justify-between')
  }
  return null
}

function setRowLabel(row, text) {
  const span = row.querySelector(':scope > span')
  if (span && !span.querySelector('.material-symbols-outlined')) span.textContent = text
}

function setRowValue(row, text) {
  const valueSpan = [...row.children].find((s) => s.querySelector('.material-symbols-outlined'))
  if (!valueSpan) return
  const icon = valueSpan.querySelector('.material-symbols-outlined')
  icon.textContent = text.startsWith('Belum') ? 'cancel' : 'check_circle'
  Array.from(valueSpan.childNodes).forEach((n) => {
    if (n.nodeType === Node.TEXT_NODE) valueSpan.removeChild(n)
  })
  valueSpan.appendChild(document.createTextNode(' ' + text))
}

init()
