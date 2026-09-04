import { createClient } from '@supabase/supabase-js'

// Baca dari .env.local (dev) / Environment Variables Vercel (production)
// Pastikan nama variabel diawali VITE_ agar bisa diakses oleh frontend Vite.
const supabaseUrl = import.meta.env.VITE_SUPABASE_URL
const supabaseAnonKey = import.meta.env.VITE_SUPABASE_ANON_KEY

export const isSupabaseConfigured = Boolean(supabaseUrl && supabaseAnonKey)

export const supabase = isSupabaseConfigured
  ? createClient(supabaseUrl, supabaseAnonKey)
  : null

// Helper: cek sesi saat ini
export async function getSession() {
  if (!supabase) return { session: null }
  const { data, error } = await supabase.auth.getSession()
  if (error) {
    console.error('Gagal membaca sesi:', error.message)
    return { session: null }
  }
  return data
}

// Helper: login email/password
export async function signIn(email, password) {
  if (!supabase) {
    return { error: { message: 'Supabase belum dikonfigurasi. Cek variabel VITE_SUPABASE_URL dan VITE_SUPABASE_ANON_KEY.' } }
  }
  return supabase.auth.signInWithPassword({ email, password })
}

// Helper: logout
export async function signOut() {
  if (!supabase) return
  await supabase.auth.signOut()
}