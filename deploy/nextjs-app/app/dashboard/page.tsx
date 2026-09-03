'use client'

import { useEffect, useState } from 'react'
import { useRouter } from 'next/navigation'
import { supabase, getCurrentUser } from '@/lib/supabase'
import Sidebar from '@/components/Sidebar'
import Header from '@/components/Header'

interface DashboardStats {
  total_active_po: number
  pending_signed: number
  total_value: number
  total_vendors: number
}

interface Activity {
  id: number
  action: string
  description: string
  user_name: string
  created_at: string
}

export default function DashboardPage() {
  const router = useRouter()
  const [user, setUser] = useState<any>(null)
  const [stats, setStats] = useState<DashboardStats | null>(null)
  const [activities, setActivities] = useState<Activity[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const checkAuth = async () => {
      const currentUser = await getCurrentUser()
      if (!currentUser) {
        router.push('/login')
        return
      }
      setUser(currentUser)
      await loadDashboardData()
    }

    checkAuth()
  }, [router])

  const loadDashboardData = async () => {
    try {
      // Get stats
      const [poResult, vendorsResult] = await Promise.all([
        supabase.from('purchase_orders').select('id, status, grand_total'),
        supabase.from('vendors').select('id')
      ])

      const pos = poResult.data || []
      const activePOs = pos.filter(p => p.status !== 'Completed')
      const pendingSigned = pos.filter(p => p.status === 'Signed')
      const totalValue = pos.reduce((sum, p) => sum + (parseFloat(p.grand_total) || 0), 0)

      setStats({
        total_active_po: activePOs.length,
        pending_signed: pendingSigned.length,
        total_value: totalValue,
        total_vendors: vendorsResult.data?.length || 0
      })

      // Get recent activity
      const { data: activitiesData } = await supabase
        .from('po_activity_log')
        .select('*, users(name)')
        .order('created_at', { ascending: false })
        .limit(5)

      setActivities(activitiesData || [])
    } catch (error) {
      console.error('Error loading dashboard:', error)
    } finally {
      setLoading(false)
    }
  }

  const formatCurrency = (value: number) => {
    return 'Rp ' + value.toLocaleString('id-ID')
  }

  const getTimeAgo = (dateString: string) => {
    const date = new Date(dateString)
    const now = new Date()
    const diff = Math.floor((now.getTime() - date.getTime()) / 1000)

    if (diff < 60) return 'Baru saja'
    if (diff < 3600) return Math.floor(diff / 60) + ' menit yang lalu'
    if (diff < 86400) return Math.floor(diff / 3600) + ' jam yang lalu'
    if (diff < 604800) return Math.floor(diff / 86400) + ' hari yang lalu'
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
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
          {/* Breadcrumb */}
          <div className="mb-8">
            <nav className="flex text-xs text-[#45474c] mb-1">
              <span>Home</span>
              <span className="mx-2">›</span>
              <span className="text-[#091426] font-semibold">Dashboard</span>
            </nav>
            <h1 className="text-3xl font-bold text-[#091426]">Dashboard</h1>
          </div>

          {/* Stats Cards */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div className="bg-white p-6 rounded-xl border border-[#c5c6cd] shadow-sm">
              <div className="flex justify-between items-start mb-4">
                <span className="text-xs uppercase text-[#45474c] tracking-wider font-semibold">Total Active PO</span>
                <svg className="w-6 h-6 text-[#0058be]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <p className="text-2xl font-bold text-[#091426]">{stats?.total_active_po || 0}</p>
              <p className="text-xs text-[#4edea3] mt-1">PO aktif saat ini</p>
            </div>

            <div className="bg-white p-6 rounded-xl border border-[#c5c6cd] shadow-sm">
              <div className="flex justify-between items-start mb-4">
                <span className="text-xs uppercase text-[#45474c] tracking-wider font-semibold">Pending Signed</span>
                <svg className="w-6 h-6 text-[#ba1a1a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
              </div>
              <p className="text-2xl font-bold text-[#091426]">{stats?.pending_signed || 0}</p>
              <p className="text-xs text-[#45474c] italic">Menunggu persetujuan</p>
            </div>

            <div className="bg-white p-6 rounded-xl border border-[#c5c6cd] shadow-sm">
              <div className="flex justify-between items-start mb-4">
                <span className="text-xs uppercase text-[#45474c] tracking-wider font-semibold">Total Value</span>
                <svg className="w-6 h-6 text-[#4edea3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <p className="text-2xl font-bold text-[#091426]">{formatCurrency(stats?.total_value || 0)}</p>
              <p className="text-xs text-[#45474c]">Nilai total PO aktif</p>
            </div>

            <div className="bg-white p-6 rounded-xl border border-[#c5c6cd] shadow-sm">
              <div className="flex justify-between items-start mb-4">
                <span className="text-xs uppercase text-[#45474c] tracking-wider font-semibold">Total Vendors</span>
                <svg className="w-6 h-6 text-[#0058be]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <p className="text-2xl font-bold text-[#091426]">{stats?.total_vendors || 0}</p>
              <p className="text-xs text-[#45474c]">Mitra terdaftar</p>
            </div>
          </div>

          {/* Quick Actions & Activity */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Quick Actions */}
            <div className="bg-white p-6 rounded-xl border border-[#c5c6cd] shadow-sm">
              <h3 className="text-xl font-semibold text-[#091426] mb-4">Akses Cepat</h3>
              <div className="grid grid-cols-2 gap-4">
                <a href="/po/create" className="flex items-center gap-3 p-4 border border-[#c5c6cd] rounded-lg hover:bg-[#f5f3f4] transition-colors">
                  <svg className="w-6 h-6 text-[#0058be]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  <span className="font-semibold text-sm">Buat PO Baru</span>
                </a>
                <a href="/vendors" className="flex items-center gap-3 p-4 border border-[#c5c6cd] rounded-lg hover:bg-[#f5f3f4] transition-colors">
                  <svg className="w-6 h-6 text-[#0058be]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                  </svg>
                  <span className="font-semibold text-sm">Tambah Vendor</span>
                </a>
                <a href="/projects" className="flex items-center gap-3 p-4 border border-[#c5c6cd] rounded-lg hover:bg-[#f5f3f4] transition-colors">
                  <svg className="w-6 h-6 text-[#0058be]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                  </svg>
                  <span className="font-semibold text-sm">Tambah Project</span>
                </a>
                <a href="/reports" className="flex items-center gap-3 p-4 border border-[#c5c6cd] rounded-lg hover:bg-[#f5f3f4] transition-colors">
                  <svg className="w-6 h-6 text-[#0058be]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                  </svg>
                  <span className="font-semibold text-sm">Ekspor Laporan</span>
                </a>
              </div>
            </div>

            {/* Recent Activity */}
            <div className="bg-white p-6 rounded-xl border border-[#c5c6cd] shadow-sm">
              <h3 className="text-xl font-semibold text-[#091426] mb-4">Aktivitas Terakhir</h3>
              <div className="space-y-4">
                {activities.length === 0 ? (
                  <p className="text-center text-[#45474c] py-4">Belum ada aktivitas</p>
                ) : (
                  activities.map((activity) => (
                    <div key={activity.id} className="flex items-start gap-3">
                      <div className="w-8 h-8 rounded-full bg-[#d8e2ff] flex items-center justify-center flex-shrink-0">
                        <svg className="w-4 h-4 text-[#004395]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                      </div>
                      <div>
                        <p className="text-sm font-semibold text-[#091426]">{activity.description || activity.action}</p>
                        <p className="text-xs text-[#45474c]">{getTimeAgo(activity.created_at)}</p>
                      </div>
                    </div>
                  ))
                )}
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  )
}
