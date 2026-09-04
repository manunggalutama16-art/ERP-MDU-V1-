import { supabase, isSupabaseConfigured, getSession, signIn } from './supabase.js'

const form = document.querySelector('form')
const emailInput = document.getElementById('email')
const passwordInput = document.getElementById('password')
const submitBtn = document.querySelector('button[type="submit"]')
const errorBox = document.getElementById('login-error')

function showError(message) {
  if (!errorBox) return
  errorBox.textContent = message
  errorBox.classList.remove('hidden')
}

function clearError() {
  if (!errorBox) return
  errorBox.classList.add('hidden')
}

function setLoading(loading) {
  if (!submitBtn) return
  submitBtn.disabled = loading
  submitBtn.classList.toggle('opacity-60', loading)
  const label = submitBtn.querySelector('span:not(.material-symbols-outlined)')
  if (label) label.textContent = loading ? 'Signing In...' : 'Sign In'
}

// Jika sudah login, langsung masuk ke aplikasi
async function redirectIfLoggedIn() {
  if (!isSupabaseConfigured) return
  const { session } = await getSession()
  if (session) window.location.href = 'purchase-orders.html'
}

form.addEventListener('submit', async (e) => {
  e.preventDefault()
  clearError()

  if (!isSupabaseConfigured) {
    showError('Supabase belum dikonfigurasi. Pastikan VITE_SUPABASE_URL dan VITE_SUPABASE_ANON_KEY sudah diisi.')
    return
  }

  const email = emailInput.value.trim()
  const password = passwordInput.value

  if (!email || !password) {
    showError('Email dan password wajib diisi.')
    return
  }

  setLoading(true)
  const { error } = await signIn(email, password)
  setLoading(false)

  if (error) {
    showError(mapAuthError(error.message))
  } else {
    window.location.href = 'purchase-orders.html'
  }
})

function mapAuthError(message) {
  const lower = (message || '').toLowerCase()
  if (lower.includes('invalid login credentials')) return 'Email atau password salah.'
  if (lower.includes('email not confirmed')) return 'Email belum diverifikasi. Cek inbox Anda untuk tautan konfirmasi.'
  if (lower.includes('rate limit') || lower.includes('too many')) return 'Terlalu banyak percobaan. Tunggu sebentar lalu coba lagi.'
  return message || 'Terjadi kesalahan. Coba lagi.'
}

redirectIfLoggedIn()