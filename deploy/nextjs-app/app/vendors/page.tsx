'use client'

import { useEffect, useState } from 'react'
import { useRouter } from 'next/navigation'
import { supabase, getCurrentUser } from '@/lib/supabase'
import Sidebar from '@/components/Sidebar'
import Header from '@/components/Header'

interface Vendor {
  id: number
  name: string
  address: string
  npwp: string
  phone: string
  contact_person: string
  email: string
}

export default function VendorsPage() {
  const router = useRouter()
  const [user, setUser] = useState<any>(null)
  const [vendors, setVendors] = useState<Vendor[]>([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [showModal, setShowModal] = useState(false)
  const [editingVendor, setEditingVendor] = useState<Vendor | null>(null)
  const [formData, setFormData] = useState({
    name: '',
    address: '',
    npwp: '',
    phone: '',
    contact_person: '',
    email: ''
  })

  useEffect(() => {
    const checkAuth = async () => {
      const currentUser = await getCurrentUser()
      if (!currentUser) {
        router.push('/login')
        return
      }
      setUser(currentUser)
      await loadVendors()
    }

    checkAuth()
  }, [router])

  const loadVendors = async () => {
    setLoading(true)
    let query = supabase.from('vendors').select('*').order('id', { ascending: false })
    
    if (search) {
      query = query.or(`name.ilike.%${search}%,npwp.ilike.%${search}%,email.ilike.%${search}%,contact_person.ilike.%${search}%`)
    }
    
    const { data, error } = await query
    if (data) setVendors(data)
    setLoading(false)
  }

  useEffect(() => {
    loadVendors()
  }, [search])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    
    if (editingVendor) {
      const { error } = await supabase
        .from('vendors')
        .update(formData)
        .eq('id', editingVendor.id)
      
      if (!error) {
        setShowModal(false)
        setEditingVendor(null)
        resetForm()
        loadVendors()
      }
    } else {
      const { error } = await supabase
        .from('vendors')
        .insert([formData])
      
      if (!error) {
        setShowModal(false)
        resetForm()
        loadVendors()
      }
    }
  }

  const handleEdit = (vendor: Vendor) => {
    setEditingVendor(vendor)
    setFormData({
      name: vendor.name,
      address: vendor.address || '',
      npwp: vendor.npwp || '',
      phone: vendor.phone || '',
      contact_person: vendor.contact_person || '',
      email: vendor.email || ''
    })
    setShowModal(true)
  }

  const handleDelete = async (id: number) => {
    if (!confirm('Apakah Anda yakin ingin menghapus vendor ini?')) return
    
    const { error } = await supabase
      .from('vendors')
      .delete()
      .eq('id', id)
    
    if (!error) loadVendors()
  }

  const resetForm = () => {
    setFormData({
      name: '',
      address: '',
      npwp: '',
      phone: '',
      contact_person: '',
      email: ''
    })
  }

  const openModal = () => {
    setEditingVendor(null)
    resetForm()
    setShowModal(true)
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
                <span className="text-[#091426] font-semibold">Master Vendor Management</span>
              </nav>
              <h1 className="text-3xl font-bold text-[#091426]">Master Vendor Management</h1>
            </div>
            <button
              onClick={openModal}
              className="bg-[#0058be] text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 hover:bg-[#0058be]/90 transition-all shadow-sm"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Tambah Vendor
            </button>
          </div>

          {/* Search & Filter */}
          <div className="bg-white border border-[#c5c6cd] rounded-xl overflow-hidden mb-6">
            <div className="p-4 border-b border-[#c5c6cd] flex flex-col md:flex-row justify-between items-center gap-4">
              <div className="relative w-full md:w-96">
                <svg className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-[#45474c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                  type="text"
                  placeholder="Cari nama, NPWP, atau email..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  className="w-full pl-10 pr-4 py-2 bg-[#f5f3f4] border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none transition-all"
                />
              </div>
            </div>

            {/* Table */}
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-[#eae7e9] border-b border-[#c5c6cd]">
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider">Nama Vendor</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider">Alamat</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider">PIC</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider">NPWP</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider">Kontak</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider">Email</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-[#c5c6cd]">
                  {loading ? (
                    <tr>
                      <td colSpan={7} className="px-6 py-8 text-center text-[#45474c]">
                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0058be] mx-auto"></div>
                        <p className="mt-2">Memuat data...</p>
                      </td>
                    </tr>
                  ) : vendors.length === 0 ? (
                    <tr>
                      <td colSpan={7} className="px-6 py-8 text-center text-[#45474c]">Tidak ada data vendor</td>
                    </tr>
                  ) : (
                    vendors.map((vendor) => (
                      <tr key={vendor.id} className="hover:bg-[#091426]/5 transition-colors">
                        <td className="px-6 py-4">
                          <div className="flex items-center gap-3">
                            <div className="w-8 h-8 rounded bg-[#0058be]/10 flex items-center justify-center text-[#0058be] font-bold text-sm">
                              {vendor.name.substring(0, 2).toUpperCase()}
                            </div>
                            <div>
                              <p className="font-semibold text-sm text-[#091426]">{vendor.name}</p>
                              <p className="text-xs text-[#45474c]">{vendor.email}</p>
                            </div>
                          </div>
                        </td>
                        <td className="px-6 py-4 text-sm text-[#45474c] max-w-xs truncate">{vendor.address || '-'}</td>
                        <td className="px-6 py-4 text-sm text-[#091426]">{vendor.contact_person || '-'}</td>
                        <td className="px-6 py-4 text-sm font-mono">{vendor.npwp || '-'}</td>
                        <td className="px-6 py-4 text-sm text-[#45474c]">{vendor.phone || '-'}</td>
                        <td className="px-6 py-4 text-sm text-[#0058be]">{vendor.email || '-'}</td>
                        <td className="px-6 py-4">
                          <div className="flex items-center justify-center gap-2">
                            <button
                              onClick={() => handleEdit(vendor)}
                              className="p-2 text-[#45474c] hover:text-[#0058be] hover:bg-[#0058be]/10 rounded transition-all"
                              title="Edit"
                            >
                              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                              </svg>
                            </button>
                            <button
                              onClick={() => handleDelete(vendor.id)}
                              className="p-2 text-[#45474c] hover:text-[#ba1a1a] hover:bg-[#ba1a1a]/10 rounded transition-all"
                              title="Hapus"
                            >
                              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                              </svg>
                            </button>
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

      {/* Modal */}
      {showModal && (
        <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
          <div className="bg-white rounded-xl p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-xl font-semibold text-[#091426]">{editingVendor ? 'Edit Vendor' : 'Tambah Vendor'}</h3>
              <button onClick={() => setShowModal(false)} className="text-[#45474c] hover:text-[#091426]">
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-[#45474c] mb-1">Nama Vendor *</label>
                  <input
                    type="text"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    className="w-full px-4 py-2 border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                    required
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-[#45474c] mb-1">PIC</label>
                  <input
                    type="text"
                    value={formData.contact_person}
                    onChange={(e) => setFormData({ ...formData, contact_person: e.target.value })}
                    className="w-full px-4 py-2 border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-[#45474c] mb-1">NPWP</label>
                  <input
                    type="text"
                    value={formData.npwp}
                    onChange={(e) => setFormData({ ...formData, npwp: e.target.value })}
                    className="w-full px-4 py-2 border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-[#45474c] mb-1">Telepon</label>
                  <input
                    type="text"
                    value={formData.phone}
                    onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                    className="w-full px-4 py-2 border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-[#45474c] mb-1">Email</label>
                  <input
                    type="email"
                    value={formData.email}
                    onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                    className="w-full px-4 py-2 border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                  />
                </div>
              </div>
              <div>
                <label className="block text-xs font-semibold text-[#45474c] mb-1">Alamat</label>
                <textarea
                  value={formData.address}
                  onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                  className="w-full px-4 py-2 border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                  rows={2}
                />
              </div>
              <div className="flex justify-end gap-3 mt-6">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-6 py-2 border border-[#c5c6cd] rounded-lg text-sm font-semibold hover:bg-[#f5f3f4] transition-colors"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="px-6 py-2 bg-[#0058be] text-white rounded-lg text-sm font-semibold hover:bg-[#0058be]/90 transition-colors"
                >
                  Simpan
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
