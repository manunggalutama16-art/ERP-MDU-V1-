'use client'

import { useEffect, useState } from 'react'
import { useRouter } from 'next/navigation'
import { supabase, getCurrentUser } from '@/lib/supabase'
import Sidebar from '@/components/Sidebar'
import Header from '@/components/Header'
import * as XLSX from 'xlsx'

interface POReport {
  id: number
  po_number: string
  created_at: string
  vendor_name: string
  project_name: string
  subtotal: number
  ppn_amount: number
  grand_total: number
  status: string
}

export default function ReportsPage() {
  const router = useRouter()
  const [user, setUser] = useState<any>(null)
  const [reports, setReports] = useState<POReport[]>([])
  const [loading, setLoading] = useState(true)
  const [startDate, setStartDate] = useState('')
  const [endDate, setEndDate] = useState('')
  const [statusFilter, setStatusFilter] = useState('')
  const [summary, setSummary] = useState({
    totalPO: 0,
    totalValue: 0,
    activeVendors: 0
  })

  useEffect(() => {
    const checkAuth = async () => {
      const currentUser = await getCurrentUser()
      if (!currentUser) {
        router.push('/login')
        return
      }
      setUser(currentUser)
      
      // Set default date range
      const today = new Date()
      const firstDay = new Date(today.getFullYear(), today.getMonth(), 1)
      setStartDate(firstDay.toISOString().split('T')[0])
      setEndDate(today.toISOString().split('T')[0])
      
      await loadReports()
    }

    checkAuth()
  }, [router])

  const loadReports = async () => {
    setLoading(true)
    
    let query = supabase
      .from('purchase_orders')
      .select('*, vendors(name), projects(name)')
      .order('created_at', { ascending: false })
    
    if (startDate) {
      query = query.gte('created_at', startDate + 'T00:00:00')
    }
    
    if (endDate) {
      query = query.lte('created_at', endDate + 'T23:59:59')
    }
    
    if (statusFilter) {
      query = query.eq('status', statusFilter)
    }
    
    const { data, error } = await query
    
    if (data) {
      const reportData = data.map(po => ({
        ...po,
        vendor_name: po.vendors?.name || 'N/A',
        project_name: po.projects?.name || 'N/A'
      }))
      
      setReports(reportData)
      
      // Calculate summary
      const totalValue = reportData.reduce((sum, po) => sum + (parseFloat(po.grand_total) || 0), 0)
      const uniqueVendors = new Set(reportData.map(po => po.vendor_name)).size
      
      setSummary({
        totalPO: reportData.length,
        totalValue: totalValue,
        activeVendors: uniqueVendors
      })
    }
    
    setLoading(false)
  }

  useEffect(() => {
    if (startDate && endDate) {
      loadReports()
    }
  }, [startDate, endDate, statusFilter])

  const exportToExcel = () => {
    if (reports.length === 0) {
      alert('Tidak ada data untuk diekspor')
      return
    }

    const excelData = reports.map(po => ({
      'PO Number': po.po_number,
      'Tanggal': new Date(po.created_at).toLocaleDateString('id-ID'),
      'Project': po.project_name,
      'Vendor': po.vendor_name,
      'Subtotal': po.subtotal,
      'PPN': po.ppn_amount,
      'Grand Total': po.grand_total,
      'Status': po.status
    }))

    const wb = XLSX.utils.book_new()
    const ws = XLSX.utils.json_to_sheet(excelData)
    
    ws['!cols'] = [
      { wch: 20 },
      { wch: 15 },
      { wch: 30 },
      { wch: 30 },
      { wch: 18 },
      { wch: 18 },
      { wch: 18 },
      { wch: 12 }
    ]
    
    XLSX.utils.book_append_sheet(wb, ws, 'Laporan PO')
    
    const fileName = `Laporan_PO_${new Date().toISOString().split('T')[0]}.xlsx`
    XLSX.writeFile(wb, fileName)
  }

  const formatCurrency = (value: number) => {
    return 'Rp ' + value.toLocaleString('id-ID')
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
          {/* Header */}
          <div className="mb-6 flex justify-between items-end">
            <div>
              <nav className="flex text-xs text-[#45474c] mb-1">
                <a href="/dashboard" className="hover:text-[#0058be]">Dashboard</a>
                <span className="mx-2">›</span>
                <span className="text-[#091426] font-semibold">Reports</span>
              </nav>
              <h1 className="text-3xl font-bold text-[#091426]">Laporan Pengadaan</h1>
            </div>
            <button
              onClick={exportToExcel}
              className="bg-[#0058be] text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 hover:bg-[#0058be]/90 transition-all shadow-sm"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              Download Excel
            </button>
          </div>

          {/* Summary Cards */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div className="bg-white p-6 rounded-xl border border-[#c5c6cd] shadow-sm">
              <div className="flex items-center gap-4">
                <div className="p-3 bg-[#d8e2ff] rounded-lg">
                  <svg className="w-6 h-6 text-[#004395]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div>
                  <p className="text-sm text-[#45474c]">Total Nilai PO</p>
                  <p className="text-2xl font-bold text-[#091426]">{formatCurrency(summary.totalValue)}</p>
                </div>
              </div>
            </div>
            
            <div className="bg-white p-6 rounded-xl border border-[#c5c6cd] shadow-sm">
              <div className="flex items-center gap-4">
                <div className="p-3 bg-[#d8e3fb] rounded-lg">
                  <svg className="w-6 h-6 text-[#091426]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <div>
                  <p className="text-sm text-[#45474c]">Jumlah PO</p>
                  <p className="text-2xl font-bold text-[#091426]">{summary.totalPO}</p>
                </div>
              </div>
            </div>
            
            <div className="bg-white p-6 rounded-xl border border-[#c5c6cd] shadow-sm">
              <div className="flex items-center gap-4">
                <div className="p-3 bg-[#e4e2e3] rounded-lg">
                  <svg className="w-6 h-6 text-[#45474c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <div>
                  <p className="text-sm text-[#45474c]">Vendor Aktif</p>
                  <p className="text-2xl font-bold text-[#091426]">{summary.activeVendors}</p>
                </div>
              </div>
            </div>
          </div>

          {/* Filters */}
          <div className="bg-white p-4 rounded-xl border border-[#c5c6cd] mb-6">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
              <div>
                <label className="block text-xs font-semibold text-[#45474c] mb-1">Tanggal Mulai</label>
                <input
                  type="date"
                  value={startDate}
                  onChange={(e) => setStartDate(e.target.value)}
                  className="w-full px-4 py-2 bg-[#fbf8fa] border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                />
              </div>
              <div>
                <label className="block text-xs font-semibold text-[#45474c] mb-1">Tanggal Akhir</label>
                <input
                  type="date"
                  value={endDate}
                  onChange={(e) => setEndDate(e.target.value)}
                  className="w-full px-4 py-2 bg-[#fbf8fa] border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                />
              </div>
              <div>
                <label className="block text-xs font-semibold text-[#45474c] mb-1">Status</label>
                <select
                  value={statusFilter}
                  onChange={(e) => setStatusFilter(e.target.value)}
                  className="w-full px-4 py-2 bg-[#fbf8fa] border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                >
                  <option value="">Semua Status</option>
                  <option value="Draft">Draft</option>
                  <option value="Printed">Printed</option>
                  <option value="Signed">Signed</option>
                  <option value="Invoiced">Invoiced</option>
                  <option value="Completed">Completed</option>
                </select>
              </div>
              <button
                onClick={loadReports}
                className="bg-[#0058be] text-white px-6 py-2 rounded-lg text-sm font-semibold hover:bg-[#0058be]/90 transition-all"
              >
                Terapkan Filter
              </button>
            </div>
          </div>

          {/* Table */}
          <div className="bg-white rounded-xl border border-[#c5c6cd] shadow-sm overflow-hidden">
            <div className="p-4 border-b border-[#c5c6cd] flex justify-between items-center">
              <h4 className="font-semibold text-[#091426]">Daftar Purchase Order</h4>
              <span className="text-sm text-[#45474c]">Menampilkan {reports.length} data</span>
            </div>
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-[#f5f3f4] border-b border-[#c5c6cd]">
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase">PO Number</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase">Tanggal</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase">Project</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase">Vendor</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase text-right">Subtotal</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase text-right">PPN</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase text-right">Grand Total</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase text-center">Status</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-[#c5c6cd]">
                  {loading ? (
                    <tr>
                      <td colSpan={8} className="px-6 py-8 text-center text-[#45474c]">
                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0058be] mx-auto"></div>
                      </td>
                    </tr>
                  ) : reports.length === 0 ? (
                    <tr>
                      <td colSpan={8} className="px-6 py-8 text-center text-[#45474c]">Tidak ada data</td>
                    </tr>
                  ) : (
                    reports.map((po) => (
                      <tr key={po.id} className="hover:bg-[#091426]/5 transition-colors">
                        <td className="px-6 py-4 font-bold text-[#0058be]">{po.po_number}</td>
                        <td className="px-6 py-4 text-sm text-[#45474c]">
                          {new Date(po.created_at).toLocaleDateString('id-ID')}
                        </td>
                        <td className="px-6 py-4 text-sm">{po.project_name}</td>
                        <td className="px-6 py-4 text-sm">{po.vendor_name}</td>
                        <td className="px-6 py-4 text-sm text-right">{formatCurrency(po.subtotal)}</td>
                        <td className="px-6 py-4 text-sm text-right text-[#45474c]">
                          {po.ppn_amount > 0 ? formatCurrency(po.ppn_amount) : '-'}
                        </td>
                        <td className="px-6 py-4 text-sm text-right font-bold">{formatCurrency(po.grand_total)}</td>
                        <td className="px-6 py-4 text-center">
                          <span className={`inline-flex px-2 py-1 rounded-full text-xs font-semibold ${getStatusColor(po.status)}`}>
                            {po.status}
                          </span>
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
