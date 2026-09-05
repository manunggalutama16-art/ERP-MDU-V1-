// Pasang tautan di menu sidebar. LinkedList dipakai hanya sebagai fallback
// bila href halaman belum di-set langsung di HTML.
const LINKS = new Map([
  ['Dashboard', 'purchase-orders.html'],
  ['Master Data', 'master-data.html'],
  ['Purchase Orders', 'purchase-orders.html'],
  ['Reports', 'reports.html'],
  ['Settings', 'settings.html'],
])

function wire() {
  document.querySelectorAll('aside nav a').forEach((a) => {
    const label = (a.textContent || '').replace(/\s+/g, ' ').trim()
    if (a.getAttribute('href') === '#' || !a.getAttribute('href')) {
      const target = LINKS.get(label)
      if (target) a.setAttribute('href', target)
    }
  })
}

wire()
