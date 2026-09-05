import { dbReady, getPO, getPOItems, getSettings, fmtDate, esc, toastError } from './db.js'

const num = (n) => (Number(n) || 0).toLocaleString('id-ID')
const fmtLong = (d) => {
  const dt = new Date(d)
  return isNaN(dt) ? String(d || '-') : dt.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
}

function itemRows(items) {
  return (items || []).map((it, i) => `<tr class="border-b border-outline-variant">
    <td class="px-sm py-md text-center">${i + 1}</td>
    <td class="px-md py-md font-semibold text-primary">${esc(it.description)}</td>
    <td class="px-md py-md text-right">${num(it.qty)}</td>
    <td class="px-md py-md text-center">${esc(it.unit || 'Pcs')}</td>
    <td class="px-md py-md text-right">${num(it.unit_price)}</td>
    <td class="px-md py-md text-right">${num(it.line_total)}</td>
  </tr>`).join('')
}

function docHTML(po, items, st) {
  const vendor = po.vendors || {}
  const project = po.projects || {}
  const ppn = po.ppn_type === 'ppn'
  const company = st || {}
  const companyName = company.company_name || 'ProcureCorp Solutions'
  const logo = company.logo_url
  const sig = company.signer_signature
  const approved = po.approval

  return `
<div class="flex justify-between items-start mb-xl">
  <div class="flex gap-md items-center">
    <div class="w-16 h-16 rounded-lg overflow-hidden flex items-center justify-center border border-outline-variant" style="background:#fff">
      ${logo
        ? `<img class="max-h-full max-w-full object-contain" src="${esc(logo)}" alt="logo" onerror="this.style.display='none'">`
        : `<span class="material-symbols-outlined text-[32px] text-primary">corporate_fare</span>`}
    </div>
    <div>
      <h2 class="font-headline-md text-headline-md text-primary tracking-tight">${esc(companyName)}</h2>
      <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">${esc(company.company_address || '')}</p>
    </div>
  </div>
  <div class="text-right">
    <h1 class="font-display-lg text-display-lg text-primary uppercase font-extrabold mb-xs">Purchase Order</h1>
    <div class="grid grid-cols-2 gap-x-md text-right">
      <span class="font-label-md text-label-md text-on-surface-variant">PO Number:</span>
      <span class="font-label-md text-label-md text-primary">${esc(po.po_number)}</span>
      <span class="font-label-md text-label-md text-on-surface-variant">Date:</span>
      <span class="font-label-md text-label-md text-primary">${fmtLong(po.po_date)}</span>
      <span class="font-label-md text-label-md text-on-surface-variant">Status:</span>
      <span class="font-label-md text-label-md text-primary">${esc(po.status)}</span>
    </div>
  </div>
</div>

<div class="grid grid-cols-2 gap-xl mb-xl border-t border-b border-outline-variant py-lg">
  <div>
    <h3 class="font-label-sm text-label-sm text-on-surface-variant uppercase mb-sm tracking-wider">Vendor</h3>
    <div class="font-body-md text-body-md text-primary">
      <p class="font-bold">${esc(vendor.name || '-')}</p>
      ${vendor.address ? `<p>${esc(vendor.address)}</p>` : ''}
      ${vendor.npwp ? `<p>NPWP: ${esc(vendor.npwp)}</p>` : ''}
      ${vendor.pic_name ? `<p class="mt-xs">Attn: ${esc(vendor.pic_name)}</p>` : ''}
      ${vendor.phone ? `<p>Tel: ${esc(vendor.phone)}</p>` : ''}
      ${vendor.email ? `<p>Email: ${esc(vendor.email)}</p>` : ''}
    </div>
  </div>
  <div>
    <h3 class="font-label-sm text-label-sm text-on-surface-variant uppercase mb-sm tracking-wider">Ship To</h3>
    <div class="font-body-md text-body-md text-primary">
      <p class="font-bold">${esc(project.name || po.ship_to || '-')}</p>
      ${project.code ? `<p>${esc(project.code)}</p>` : ''}
      ${project.address ? `<p>${esc(project.address)}</p>` : ''}
      ${project.gmap_url ? `<p class="mt-xs"><a class="text-secondary underline" href="${esc(project.gmap_url)}" target="_blank" rel="noopener">Lihat di Google Maps</a></p>` : ''}
      ${po.ship_to ? `<p>${esc(po.ship_to)}</p>` : ''}
      ${po.top ? `<p class="mt-xs">TOP: ${esc(po.top)}</p>` : ''}
    </div>
  </div>
</div>

<div class="flex-grow">
  <table class="w-full text-left border-collapse">
    <thead>
      <tr class="bg-surface-container-low border-b border-outline">
        <th class="px-sm py-md font-label-sm text-label-sm text-on-surface-variant uppercase text-center w-12">No.</th>
        <th class="px-md py-md font-label-sm text-label-sm text-on-surface-variant uppercase">Description</th>
        <th class="px-md py-md font-label-sm text-label-sm text-on-surface-variant uppercase text-right">Qty</th>
        <th class="px-md py-md font-label-sm text-label-sm text-on-surface-variant uppercase text-center">Unit</th>
        <th class="px-md py-md font-label-sm text-label-sm text-on-surface-variant uppercase text-right">Price (Rp)</th>
        <th class="px-md py-md font-label-sm text-label-sm text-on-surface-variant uppercase text-right">Total (Rp)</th>
      </tr>
    </thead>
    <tbody class="font-data-tabular text-data-tabular">
      ${itemRows(items)}
    </tbody>
  </table>
</div>

<div class="mt-xl grid grid-cols-2 gap-xl">
  <div class="flex flex-col justify-end">
    <div class="p-md bg-surface-container-low border border-outline-variant rounded-lg">
      <h4 class="font-label-sm text-label-sm text-on-surface-variant uppercase mb-xs">Notes</h4>
      <p class="font-body-sm text-body-sm text-on-surface-variant">${esc(po.notes || '-')}</p>
    </div>
  </div>
  <div class="flex flex-col gap-sm">
    <div class="flex justify-between items-center px-md py-xs">
      <span class="font-label-md text-label-md text-on-surface-variant">Subtotal</span>
      <span class="font-data-tabular text-data-tabular text-primary">${num(po.subtotal)}</span>
    </div>
    <div class="flex justify-between items-center px-md py-xs">
      <span class="font-label-md text-label-md text-on-surface-variant">${ppn ? 'PPN (11%)' : 'PPN'}</span>
      <span class="font-data-tabular text-data-tabular text-primary">${ppn ? num(po.tax_amount) : 'Non PPN'}</span>
    </div>
    <div class="flex justify-between items-center px-md py-md bg-primary text-on-primary rounded-sm">
      <span class="font-headline-sm text-headline-sm">Grand Total (Rp)</span>
      <span class="font-headline-sm text-headline-sm">${num(po.grand_total)}</span>
    </div>
  </div>
</div>

<div class="mt-xl flex justify-end">
  <div class="text-center w-64">
    <p class="font-label-md text-label-md text-on-surface-variant mb-xl">${approved ? 'Authorized By' : 'Menunggu Persetujuan'}</p>
    <div class="h-24 flex items-center justify-center relative mb-sm">
      ${approved
        ? (sig
            ? `<img class="max-h-full max-w-full mix-blend-multiply opacity-90" src="${esc(sig)}" alt="ttd" onerror="this.style.display='none'">`
            : `<span class="font-headline-sm text-primary italic">Disetujui</span>`)
        : `<span class="font-body-sm text-on-surface-variant italic">Belum ada tanda tangan</span>`}
      <div class="absolute bottom-0 left-0 right-0 h-px bg-on-surface-variant"></div>
    </div>
    <p class="font-label-md text-label-md text-primary font-bold">${esc((company.signer_name || '-').trim())}</p>
    <p class="font-label-sm text-label-sm text-on-surface-variant">${esc(company.signer_position || '')}</p>
  </div>
</div>

<div class="mt-auto pt-xl border-t border-outline-variant text-center">
  <p class="font-body-sm text-body-sm text-on-surface-variant">${esc(company.footer_note || companyName)}</p>
</div>`
}

async function init() {
  const canvas = document.querySelector('.page-canvas')
  const id = new URLSearchParams(location.search).get('id')
  const fail = (msg) => {
    if (canvas) canvas.innerHTML = `<div class="p-xl text-center text-on-surface-variant">${esc(msg)}</div>`
  }
  if (!dbReady) {
    fail('Supabase belum dikonfigurasi. Cek VITE_SUPABASE_URL / VITE_SUPABASE_ANON_KEY.')
    return
  }
  if (!id) {
    fail('Tidak ada PO dipilih. Kembali ke daftar dan klik ikon cetak pada salah satu PO.')
    return
  }

  const [poR, stR] = await Promise.all([getPO(id), getSettings()])
  if (poR.error || !poR.data) {
    fail('Gagal memuat PO: ' + (poR.error ? poR.error.message : 'PO tidak ditemukan.'))
    return
  }
  const po = poR.data
  const st = stR.data || {}
  const { data: items, error: itemErr } = await getPOItems(id)
  if (itemErr) toastError('Gagal memuat item: ' + itemErr.message)

  const title = document.querySelector('header h1')
  if (title) title.textContent = po.po_number
  document.title = po.po_number + ' - Preview PO'
  if (canvas) canvas.innerHTML = docHTML(po, items || [], st)
}

init()
