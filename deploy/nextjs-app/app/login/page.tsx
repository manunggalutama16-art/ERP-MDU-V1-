'use client'

import { useState } from 'react'
import { useRouter } from 'next/navigation'
import { supabase } from '@/lib/supabase'

export default function LoginPage() {
  const router = useRouter()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const [showPassword, setShowPassword] = useState(false)

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    setLoading(true)

    try {
      const { data, error: authError } = await supabase.auth.signInWithPassword({
        email,
        password
      })

      if (authError) {
        setError(authError.message)
        setLoading(false)
        return
      }

      if (data.user) {
        router.push('/dashboard')
        router.refresh()
      }
    } catch (err) {
      setError('Terjadi kesalahan. Silakan coba lagi.')
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-[#fbf8fa] flex items-center justify-center p-4">
      {/* Background decorations */}
      <div className="fixed inset-0 z-0 overflow-hidden">
        <div className="absolute inset-0 opacity-10" style={{
          backgroundImage: 'radial-gradient(#091426 1px, transparent 1px)',
          backgroundSize: '32px 32px'
        }}></div>
        <div className="absolute right-0 bottom-0 w-[60vw] h-[614px] opacity-30 blur-[120px] bg-[#2170e4] rounded-full transform translate-x-1/2 translate-y-1/2"></div>
      </div>

      <div className="relative z-10 w-full max-w-[440px]">
        {/* Branding */}
        <div className="mb-8 text-center">
          <div className="flex items-center justify-center gap-2 mb-1">
            <div className="w-10 h-10 bg-[#091426] flex items-center justify-center rounded-lg shadow-lg">
              <svg className="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
              </svg>
            </div>
            <h1 className="text-2xl font-semibold text-[#091426] tracking-tight">ProcureCorp</h1>
          </div>
          <p className="text-xs text-[#45474c] uppercase tracking-widest font-semibold">Enterprise Procurement</p>
        </div>

        {/* Login Card */}
        <div className="bg-white/95 backdrop-blur-sm w-full p-8 rounded-xl border border-[#c5c6cd]/30 shadow-[0px_4px_24px_rgba(9,20,38,0.06)]">
          <div className="mb-6">
            <h2 className="text-xl font-semibold text-[#1b1b1d]">Selamat Datang</h2>
            <p className="text-sm text-[#45474c] mt-1">Masukkan kredensial Anda untuk mengakses Nexus.</p>
          </div>

          {error && (
            <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
              {error}
            </div>
          )}

          <form onSubmit={handleLogin} className="space-y-4">
            {/* Email */}
            <div className="space-y-1">
              <label className="text-xs font-semibold text-[#45474c] uppercase tracking-wider">Alamat Email</label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <svg className="w-5 h-5 text-[#75777d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                </div>
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="w-full pl-12 pr-4 py-3 bg-white border border-[#c5c6cd] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]/20 focus:border-[#0058be] transition-all"
                  placeholder="name@company.com"
                  required
                />
              </div>
            </div>

            {/* Password */}
            <div className="space-y-1">
              <div className="flex justify-between items-center">
                <label className="text-xs font-semibold text-[#45474c] uppercase tracking-wider">Password</label>
                <button type="button" className="text-xs text-[#0058be] hover:underline">Lupa Password?</button>
              </div>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <svg className="w-5 h-5 text-[#75777d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <input
                  type={showPassword ? 'text' : 'password'}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="w-full pl-12 pr-12 py-3 bg-white border border-[#c5c6cd] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]/20 focus:border-[#0058be] transition-all"
                  placeholder="••••••••"
                  required
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute inset-y-0 right-0 pr-4 flex items-center text-[#75777d] hover:text-[#1b1b1d]"
                >
                  {showPassword ? (
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                  ) : (
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  )}
                </button>
              </div>
            </div>

            {/* Remember me */}
            <div className="flex items-center gap-2">
              <input
                type="checkbox"
                id="remember"
                className="w-4 h-4 text-[#0058be] border-[#c5c6cd] rounded focus:ring-[#0058be]"
              />
              <label htmlFor="remember" className="text-sm text-[#45474c] cursor-pointer">Ingat perangkat ini selama 30 hari</label>
            </div>

            {/* Submit button */}
            <button
              type="submit"
              disabled={loading}
              className="w-full bg-[#091426] hover:bg-[#1e293b] text-white py-3 rounded-lg font-semibold transition-all active:scale-[0.98] flex items-center justify-center gap-2 disabled:opacity-50"
            >
              {loading ? (
                <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
              ) : (
                <>
                  Masuk
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14 5l7 7m0 0l-7 7m7-7H3" />
                  </svg>
                </>
              )}
            </button>
          </form>

          <div className="mt-6 pt-6 border-t border-[#c5c6cd]/30 text-center">
            <p className="text-sm text-[#45474c]">
              Belum memiliki akun?{' '}
              <a href="#" className="text-[#0058be] font-semibold hover:underline">Hubungi Administrator</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}
