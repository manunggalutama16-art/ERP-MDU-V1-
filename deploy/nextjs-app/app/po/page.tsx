'use client'

import { useEffect, useState } from 'react'
import { useRouter } from 'next/navigation'
import { supabase, getCurrentUser } from '@/lib/supabase'
import Sidebar from '@/components/Sidebar'
import Header from '@/components/Header'

interface PO {
  id: number
  po_number: string
  vendor_name: string
  project_name: string
  grand_total: number
  status: string
  created_at: string
}

export default function POListPage() {
  const router = useRouter()
  const [user, setUser] = useState<any>(null)
  const [pos, setPOs] = useState<PO[]>([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [statusFilter, setStatusFilter] = useState('')

  useEffect(() => {
    const checkAuth = async () => {
      const currentUser = await getCurrentUser()
      if (!currentUser) {
        router.push('/login')
        return
      }
      setUser(currentUser)
      await loadPOs()
    }

    checkAuth()
  }, [router])

  const loadPOs = async () => {
    setLoading(true)
    let query = supabase
      .from('purchase_orders')
      .select('*, vendors(name), projects(name)')
      .order('id', { ascending: false })
    
    if (search) {
      query = query.ilike('po_number', `%${search}%`)
    }
    
    if (statusFilter) {
      query = query.eq('status', statusFilter)
    }
    
    const { data, error } = await query
    if (data) {
      setPOs(data.map(po => ({
        ...po,
        vendor_name: po.vendors?.name || 'N/A',
        project_name: po.projects?.name || 'N/A'
      })))
    }
    setLoading(false)
  }

  useEffect(() => {
    loadPOs()
  }, [search, statusFilter])

  const formatCurrency = (value: number) => {
    return 'Rp ' + value.toLocaleString('id-ID')
  }

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', { 
      day: 'numeric', 
      month: 'short', 
      year: 'numeric' 
    })
  }

  const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
      'Draft': 'bg-gray-100 text-gray-800',
      'Printed': 'bg-blue-100 text-blue-800',
      'Signed': 'bg-green-100 text-green-800',
      'Invoiced': 'bg-purple-100 text-purple-800',
      'Completed': 'bg-emerald-100 text-emerald-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
  }

  return (
    <div className="min-h-screen bg-[#fbf8fa]">
      <Sidebar user={user} />
      
      <div className="ml-[260px] min-h-screen flex flex-col">
        <Header user={user} />
        
        <main className="p-6 flex-1">
          {/* Breadcrumb & Header */}
          <div className="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <nav className="flex text-xs text-[#45474c] mb-1">
                <a href="/dashboard" className="hover:text-[#0058be]">Dashboard</a>
                <span className="mx-2">›</span>
                <span className="text-[#091426] font-semibold">Purchase Orders</span>
              </nav>
              <h1 className="text-3xl font-bold text-[#091426]">Purchase Order Registry</h1>
            </div>
            <a
              href="/po/create"
              className="bg-[#0058be] text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 hover:bg-[#0058be]/90 transition-all shadow-sm"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Buat PO Baru
            </a>
          </div>

          {/* Filters */}
          <div className="bg-white p-4 rounded-xl border border-[#c5c6cd] shadow-sm mb-6 flex flex-wrap items-center gap-4">
            <div className="flex-1 min-w-[200px] relative">
              <svg className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-[#45474c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input
                type="text"
                placeholder="Search PO Number, Vendor..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="w-full pl-10 pr-4 py-2 bg-[#f5f3f4] border-none rounded-full text-sm focus:ring-2 focus:ring-[#0058be] outline-none"
              />
            </div>
            <select
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value)}
              className="border border-[#c5c6cd] rounded bg-white text-sm py-2 px-4 focus:ring-2 focus:ring-[#0058be] outline-none"
            >
              <option value="">Status: All</option>
              <option value="Draft">Draft</option>
              <option value="Printed">Printed</option>
              <option value="Signed">Signed</option>
              <option value="Invoiced">Invoiced</option>
              <option value="Completed">Completed</option>
            </select>
          </div>

          {/* Table */}
          <div className="bg-white rounded-xl border border-[#c5c6cd] shadow-sm overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-[#f5f3f4] border-b border-[#c5c6cd]">
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider">PO Number</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider">Date</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider">Vendor</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider">Project</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider text-right">Total</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider text-center">Status</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-[#c5c6cd]">
                  {loading ? (
                    <tr>
                      <td colSpan={7} className="px-6 py-8 text-center text-[#45474c]">
                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0058be] mx-auto"></div>
                      </td>
                    </tr>
                  ) : pos.length === 0 ? (
                    <tr>
                      <td colSpan={7} className="px-6 py-8 text-center text-[#45474c]">Tidak ada data PO</td>
                    </tr>
                  ) : (
                    pos.map((po) => (
                      <tr key={po.id} className="hover:bg-[#091426]/5 transition-colors">
                        <td className="px-6 py-4 font-bold text-[#0058be]">{po.po_number}</td>
                        <td className="px-6 py-4 text-sm text-[#45474c]">{formatDate(po.created_at)}</td>
                        <td className="px-6 py-4 text-sm font-semibold">{po.vendor_name}</td>
                        <td className="px-6 py-4 text-sm text-[#45474c]">{po.project_name}</td>
                        <td className="px-6 py-4 text-sm text-right font-bold">{formatCurrency(po.grand_total)}</td>
                        <td className="px-6 py-4 text-center">
                          <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${getStatusColor(po.status)}`}>
                            {po.status}
                          </span>
                        </td>
                        <td className="px-6 py-4 text-right">
                          <div className="flex items-center justify-end gap-2">
                            <a
                              href={`/po/${po.id}`}
                              className="p-2 text-[#45474c] hover:text-[#0058be] transition-colors"
                              title="View"
                            >
                              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                              </svg>
                            </a>
                            <a
                              href={`/po/${po.id}/edit`}
                              className="p-2 text-[#45474c] hover:text-[#0058be] transition-colors"
                              title="Edit"
                            >
                              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                              </svg>
                            </a>
                          </div>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </main>
      </div>
    </div>
  )
}
