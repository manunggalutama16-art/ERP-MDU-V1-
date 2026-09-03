'use client'

import { useEffect, useState } from 'react'
import { useRouter } from 'next/navigation'
import { supabase, getCurrentUser } from '@/lib/supabase'
import Sidebar from '@/components/Sidebar'
import Header from '@/components/Header'

export default function SettingsPage() {
  const router = useRouter()
  const [user, setUser] = useState<any>(null)
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [settings, setSettings] = useState({
    company_name: '',
    company_address: '',
    signatory_name: '',
    signatory_title: '',
    signature_position: 'right',
    footer_note: ''
  })

  useEffect(() => {
    const checkAuth = async () => {
      const currentUser = await getCurrentUser()
      if (!currentUser) {
        router.push('/login')
        return
      }
      if (currentUser.role !== 'admin') {
        router.push('/dashboard')
        return
      }
      setUser(currentUser)
      await loadSettings()
    }

    checkAuth()
  }, [router])

  const loadSettings = async () => {
    const { data, error } = await supabase
      .from('system_settings')
      .select('*')
    
    if (data) {
      const settingsMap: Record<string, string> = {}
      data.forEach((item: any) => {
        settingsMap[item.setting_key] = item.setting_value
      })
      setSettings({
        company_name: settingsMap.company_name || '',
        company_address: settingsMap.company_address || '',
        signatory_name: settingsMap.signatory_name || '',
        signatory_title: settingsMap.signatory_title || '',
        signature_position: settingsMap.signature_position || 'right',
        footer_note: settingsMap.footer_note || ''
      })
    }
    setLoading(false)
  }

  const handleSave = async () => {
    setSaving(true)
    
    const updates = Object.entries(settings).map(([key, value]) => ({
      setting_key: key,
      setting_value: value
    }))
    
    for (const update of updates) {
      const { error } = await supabase
        .from('system_settings')
        .upsert({ setting_key: update.setting_key, setting_value: update.setting_value }, { onConflict: 'setting_key' })
      
      if (error) {
        console.error('Error saving setting:', error)
      }
    }
    
    setSaving(false)
    alert('Settings saved successfully!')
  }

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-[#fbf8fa]">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-[#0058be]"></div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-[#fbf8fa]">
      <Sidebar user={user} />
      
      <div className="ml-[260px] min-h-screen flex flex-col">
        <Header user={user} />
        
        <main className="p-6 flex-1">
          {/* Header */}
          <div className="mb-6 flex justify-between items-end">
            <div>
              <nav className="flex text-xs text-[#45474c] mb-1">
                <a href="/dashboard" className="hover:text-[#0058be]">Dashboard</a>
                <span className="mx-2">›</span>
                <span className="text-[#091426] font-semibold">Settings</span>
              </nav>
              <h1 className="text-3xl font-bold text-[#091426]">Pengaturan Template PO</h1>
            </div>
            <button
              onClick={handleSave}
              disabled={saving}
              className="bg-[#0058be] text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 hover:bg-[#0058be]/90 transition-all shadow-sm disabled:opacity-50"
            >
              {saving ? (
                <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
              ) : (
                <>
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  Simpan Perubahan
                </>
              )}
            </button>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Left Column - Forms */}
            <div className="space-y-6">
              {/* Company Identity */}
              <div className="bg-white p-6 rounded-xl border border-[#c5c6cd] shadow-sm">
                <h3 className="text-lg font-semibold text-[#091426] mb-4 flex items-center gap-2">
                  <svg className="w-5 h-5 text-[#0058be]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  Identitas Perusahaan
                </h3>
                <div className="space-y-4">
                  <div>
                    <label className="block text-xs font-semibold text-[#45474c] mb-1">Nama Perusahaan</label>
                    <input
                      type="text"
                      value={settings.company_name}
                      onChange={(e) => setSettings({ ...settings, company_name: e.target.value })}
                      className="w-full px-4 py-2 border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-[#45474c] mb-1">Alamat Perusahaan</label>
                    <textarea
                      value={settings.company_address}
                      onChange={(e) => setSettings({ ...settings, company_address: e.target.value })}
                      className="w-full px-4 py-2 border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                      rows={3}
                    />
                  </div>
                </div>
              </div>

              {/* Authorization Details */}
              <div className="bg-white p-6 rounded-xl border border-[#c5c6cd] shadow-sm">
                <h3 className="text-lg font-semibold text-[#091426] mb-4 flex items-center gap-2">
                  <svg className="w-5 h-5 text-[#0058be]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                  </svg>
                  Detail Otorisasi
                </h3>
                <div className="space-y-4">
                  <div>
                    <label className="block text-xs font-semibold text-[#45474c] mb-1">Nama Pejabat Penandatangan</label>
                    <input
                      type="text"
                      value={settings.signatory_name}
                      onChange={(e) => setSettings({ ...settings, signatory_name: e.target.value })}
                      className="w-full px-4 py-2 border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-[#45474c] mb-1">Jabatan Resmi</label>
                    <input
                      type="text"
                      value={settings.signatory_title}
                      onChange={(e) => setSettings({ ...settings, signatory_title: e.target.value })}
                      className="w-full px-4 py-2 border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                    />
                  </div>
                </div>
              </div>

              {/* Signature Position */}
              <div className="bg-white p-6 rounded-xl border border-[#c5c6cd] shadow-sm">
                <h3 className="text-lg font-semibold text-[#091426] mb-4">Posisi Tanda Tangan</h3>
                <div className="grid grid-cols-2 gap-4">
                  <button
                    onClick={() => setSettings({ ...settings, signature_position: 'left' })}
                    className={`p-4 border rounded-xl text-center transition-all ${
                      settings.signature_position === 'left'
                        ? 'border-[#0058be] bg-[#f0f7ff]'
                        : 'border-[#c5c6cd] hover:border-[#0058be]/50'
                    }`}
                  >
                    <svg className="w-8 h-8 mx-auto mb-2 text-[#45474c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span className="font-semibold text-sm">Sisi Kiri</span>
                  </button>
                  <button
                    onClick={() => setSettings({ ...settings, signature_position: 'right' })}
                    className={`p-4 border rounded-xl text-center transition-all ${
                      settings.signature_position === 'right'
                        ? 'border-[#0058be] bg-[#f0f7ff]'
                        : 'border-[#c5c6cd] hover:border-[#0058be]/50'
                    }`}
                  >
                    <svg className="w-8 h-8 mx-auto mb-2 text-[#45474c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span className="font-semibold text-sm">Sisi Kanan</span>
                  </button>
                </div>
              </div>

              {/* Footer Notes */}
              <div className="bg-white p-6 rounded-xl border border-[#c5c6cd] shadow-sm">
                <h3 className="text-lg font-semibold text-[#091426] mb-4 flex items-center gap-2">
                  <svg className="w-5 h-5 text-[#0058be]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Catatan Kaki & Footer
                </h3>
                <div>
                  <label className="block text-xs font-semibold text-[#45474c] mb-1">Catatan Kaki</label>
                  <textarea
                    value={settings.footer_note}
                    onChange={(e) => setSettings({ ...settings, footer_note: e.target.value })}
                    className="w-full px-4 py-2 border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                    rows={4}
                    placeholder="Syarat & Ketentuan..."
                  />
                </div>
              </div>
            </div>

            {/* Right Column - Preview */}
            <div className="sticky top-24">
              <div className="bg-white rounded-xl border border-[#c5c6cd] shadow-sm p-6">
                <h3 className="text-sm font-semibold text-[#45474c] mb-4 flex items-center gap-2">
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  PRATINJAU LAYOUT PDF
                </h3>
                
                {/* PDF Preview */}
                <div className="aspect-[1/1.414] bg-white border border-[#c5c6cd] p-6 flex flex-col">
                  {/* Header */}
                  <div className="flex justify-between items-start mb-8">
                    <div>
                      <p className="font-bold text-lg">{settings.company_name || 'ProcureCorp'}</p>
                      <p className="text-xs text-[#45474c]">{settings.company_address}</p>
                    </div>
                    <div className="text-right">
                      <h4 className="font-bold text-xl">PURCHASE ORDER</h4>
                      <p className="text-xs text-[#45474c]">PO #2024-00001</p>
                    </div>
                  </div>
                  
                  {/* Content placeholder */}
                  <div className="flex-1 mb-8">
                    <div className="h-4 bg-[#f5f3f4] rounded mb-2"></div>
                    <div className="h-4 bg-[#f5f3f4] rounded mb-2 w-3/4"></div>
                    <div className="h-4 bg-[#f5f3f4] rounded mb-2 w-1/2"></div>
                  </div>
                  
                  {/* Signature */}
                  <div className={`flex ${settings.signature_position === 'left' ? 'justify-start' : 'justify-end'}`}>
                    <div className="text-center w-48">
                      <p className="text-xs mb-12">Authorized Signature,</p>
                      <div className="border-b border-[#1b1b1d] mx-auto w-32 mb-1"></div>
                      <p className="text-xs font-bold">{settings.signatory_name || 'Nama Pejabat'}</p>
                      <p className="text-[10px] text-[#45474c] italic">{settings.signatory_title || 'Jabatan'}</p>
                    </div>
                  </div>
                  
                  {/* Footer */}
                  <div className="mt-8 pt-4 border-t border-[#c5c6cd]">
                    <p className="text-[10px] text-[#45474c] italic">
                      {settings.footer_note || 'Footer note akan muncul di sini...'}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  )
}
