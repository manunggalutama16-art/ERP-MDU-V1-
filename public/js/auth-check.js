import { supabase, isSupabaseConfigured, getSession, signOut } from './supabase.js'

// Pelindung halaman: jika tidak ada sesi, kembalikan ke halaman login.
async function checkAuth() {
  if (!isSupabaseConfigured) return // dev mode tanpa Supabase: biarkan akses
  const { session } = await getSession()
  if (!session) {
    window.location.href = 'index.html'
    return
  }
  addLogoutButton()
}

function addLogoutButton() {
  // Tombol logout kecil di pojok kanan bawah agar mudah diakses dari halaman mana pun.
  const btn = document.createElement('button')
  btn.id = 'logout-btn'
  btn.title = 'Logout'
  btn.setAttribute('aria-label', 'Logout')
  btn.innerHTML = '<span class="material-symbols-outlined" style="font-size:20px">logout</span>'
  Object.assign(btn.style, {
    position: 'fixed',
    bottom: '16px',
    right: '16px',
    zIndex: '9999',
    width: '40px',
    height: '40px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: '9999px',
    background: '#091426',
    color: '#ffffff',
    border: '1px solid rgba(255,255,255,0.2)',
    boxShadow: '0 4px 12px rgba(9,20,38,0.3)',
    cursor: 'pointer',
    transition: 'opacity 0.15s',
  })
  btn.addEventListener('mouseenter', () => (btn.style.opacity = '0.85'))
  btn.addEventListener('mouseleave', () => (btn.style.opacity = '1'))
  btn.addEventListener('click', async () => {
    await signOut()
    window.location.href = 'index.html'
  })
  document.body.appendChild(btn)
}

checkAuth()