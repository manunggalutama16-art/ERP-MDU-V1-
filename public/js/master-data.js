import { dbReady, listVendors, listProjects } from './db.js'

const set = (id, v) => {
  const el = document.getElementById(id)
  if (el) el.textContent = v
}

async function load() {
  if (!dbReady) return
  const [venRes, projRes] = await Promise.all([listVendors(), listProjects()])

  const vendors = (venRes.data || [])
  const projects = (projRes.data || [])

  // Kartu statistik
  set('md-vendor-count', vendors.length)
  set('md-project-count', projects.length)
  set('md-project-active', projects.filter((p) => p.status === 'ACTIVE').length)

  // Kartu Data Vendor & Data Project
  set('md-vendor-total', vendors.length + ' data')
  set('md-project-total', projects.length + ' data')
  set('md-vendor-desc', vendors.length + ' vendor terdaftar')
  set('md-project-desc', projects.length + ' project terdaftar')
}

load()