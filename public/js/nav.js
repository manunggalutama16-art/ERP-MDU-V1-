// Pasang tautan nyata pada menu sidebar (dan breadcrumb) di semua halaman aplikasi.
const LINKS = [
  ['Dashboard', 'purchase-orders.html'],
  ['Master Data', 'vendors.html'],
  ['Purchase Orders', 'purchase-orders.html'],
  ['Reports', 'reports.html'],
  ['Settings', 'settings.html'],
]

function wire() {
  const anchors = document.querySelectorAll('aside a, header nav a, ol a, .breadcrumb a')
  anchors.forEach((a) => {
    const label = (a.textContent || '').replace(/\s+/g, ' ').trim()
    const hit = LINKS.find(([text]) => text === label)
    if (hit) a.setAttribute('href', hit[1])
  })
}

wire()
