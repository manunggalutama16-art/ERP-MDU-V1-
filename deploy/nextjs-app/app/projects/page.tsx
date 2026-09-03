'use client'

import { useEffect, useState } from 'react'
import { useRouter } from 'next/navigation'
import { supabase, getCurrentUser } from '@/lib/supabase'
import Sidebar from '@/components/Sidebar'
import Header from '@/components/Header'

interface Project {
  id: number
  code: string
  name: string
  location: string
  client: string
  pic: string
  value_before_ppn: number
  value_inc_ppn: number
  status: string
}

export default function ProjectsPage() {
  const router = useRouter()
  const [user, setUser] = useState<any>(null)
  const [projects, setProjects] = useState<Project[]>([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [showModal, setShowModal] = useState(false)
  const [editingProject, setEditingProject] = useState<Project | null>(null)
  const [formData, setFormData] = useState({
    code: '',
    name: '',
    location: '',
    client: '',
    pic: '',
    value_before_ppn: 0,
    value_inc_ppn: 0,
    status: 'PENDING'
  })

  useEffect(() => {
    const checkAuth = async () => {
      const currentUser = await getCurrentUser()
      if (!currentUser) {
        router.push('/login')
        return
      }
      setUser(currentUser)
      await loadProjects()
    }

    checkAuth()
  }, [router])

  const loadProjects = async () => {
    setLoading(true)
    let query = supabase.from('projects').select('*').order('id', { ascending: false })
    
    if (search) {
      query = query.or(`code.ilike.%${search}%,name.ilike.%${search}%,client.ilike.%${search}%`)
    }
    
    const { data, error } = await query
    if (data) setProjects(data)
    setLoading(false)
  }

  useEffect(() => {
    loadProjects()
  }, [search])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    
    if (editingProject) {
      const { error } = await supabase
        .from('projects')
        .update(formData)
        .eq('id', editingProject.id)
      
      if (!error) {
        setShowModal(false)
        setEditingProject(null)
        resetForm()
        loadProjects()
      }
    } else {
      const { error } = await supabase
        .from('projects')
        .insert([formData])
      
      if (!error) {
        setShowModal(false)
        resetForm()
        loadProjects()
      }
    }
  }

  const handleEdit = (project: Project) => {
    setEditingProject(project)
    setFormData({
      code: project.code,
      name: project.name,
      location: project.location || '',
      client: project.client || '',
      pic: project.pic || '',
      value_before_ppn: project.value_before_ppn || 0,
      value_inc_ppn: project.value_inc_ppn || 0,
      status: project.status || 'PENDING'
    })
    setShowModal(true)
  }

  const handleDelete = async (id: number) => {
    if (!confirm('Apakah Anda yakin ingin menghapus project ini?')) return
    
    const { error } = await supabase
      .from('projects')
      .delete()
      .eq('id', id)
    
    if (!error) loadProjects()
  }

  const resetForm = () => {
    setFormData({
      code: '',
      name: '',
      location: '',
      client: '',
      pic: '',
      value_before_ppn: 0,
      value_inc_ppn: 0,
      status: 'PENDING'
    })
  }

  const openModal = () => {
    setEditingProject(null)
    resetForm()
    setShowModal(true)
  }

  const formatCurrency = (value: number) => {
    return 'Rp ' + value.toLocaleString('id-ID')
  }

  const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
      'ACTIVE': 'bg-green-100 text-green-800',
      'ON HOLD': 'bg-yellow-100 text-yellow-800',
      'PENDING': 'bg-orange-100 text-orange-800',
      'COMPLETED': 'bg-gray-100 text-gray-800'
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
                <span className="text-[#091426] font-semibold">Project Registry</span>
              </nav>
              <h1 className="text-3xl font-bold text-[#091426]">Project Registry</h1>
            </div>
            <button
              onClick={openModal}
              className="bg-[#0058be] text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 hover:bg-[#0058be]/90 transition-all shadow-sm"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Tambah Project
            </button>
          </div>

          {/* Search */}
          <div className="bg-white border border-[#c5c6cd] rounded-xl overflow-hidden mb-6">
            <div className="p-4 border-b border-[#c5c6cd]">
              <div className="relative w-full md:w-96">
                <svg className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-[#45474c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                  type="text"
                  placeholder="Filter by project code, name, or client..."
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
                  <tr className="bg-[#f5f3f4] border-b border-[#c5c6cd]">
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider">Kode</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider">Nama Project</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider">PIC</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider text-right">Nilai</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider text-center">Status</th>
                    <th className="px-6 py-4 text-xs font-semibold text-[#45474c] uppercase tracking-wider text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-[#c5c6cd]">
                  {loading ? (
                    <tr>
                      <td colSpan={6} className="px-6 py-8 text-center text-[#45474c]">
                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0058be] mx-auto"></div>
                      </td>
                    </tr>
                  ) : projects.length === 0 ? (
                    <tr>
                      <td colSpan={6} className="px-6 py-8 text-center text-[#45474c]">Tidak ada data project</td>
                    </tr>
                  ) : (
                    projects.map((project) => (
                      <tr key={project.id} className="hover:bg-[#091426]/5 transition-colors">
                        <td className="px-6 py-4">
                          <span className="bg-gradient-to-r from-[#1e293b] to-[#091426] text-[#d8e2ff] px-3 py-1 rounded font-bold text-xs inline-block">{project.code}</span>
                        </td>
                        <td className="px-6 py-4 font-semibold text-sm text-[#091426]">{project.name}</td>
                        <td className="px-6 py-4 text-sm text-[#45474c]">{project.pic || '-'}</td>
                        <td className="px-6 py-4 text-sm text-right font-mono">{formatCurrency(project.value_inc_ppn)}</td>
                        <td className="px-6 py-4 text-center">
                          <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold ${getStatusColor(project.status)}`}>
                            {project.status}
                          </span>
                        </td>
                        <td className="px-6 py-4">
                          <div className="flex items-center justify-center gap-2">
                            <button
                              onClick={() => handleEdit(project)}
                              className="p-2 text-[#45474c] hover:text-[#0058be] hover:bg-[#0058be]/10 rounded transition-all"
                            >
                              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                              </svg>
                            </button>
                            <button
                              onClick={() => handleDelete(project.id)}
                              className="p-2 text-[#45474c] hover:text-[#ba1a1a] hover:bg-[#ba1a1a]/10 rounded transition-all"
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
              <h3 className="text-xl font-semibold text-[#091426]">{editingProject ? 'Edit Project' : 'Tambah Project'}</h3>
              <button onClick={() => setShowModal(false)} className="text-[#45474c] hover:text-[#091426]">
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-[#45474c] mb-1">Kode Project *</label>
                  <input
                    type="text"
                    value={formData.code}
                    onChange={(e) => setFormData({ ...formData, code: e.target.value })}
                    className="w-full px-4 py-2 border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                    placeholder="PRJ-2024-001"
                    required
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-[#45474c] mb-1">Nama Project *</label>
                  <input
                    type="text"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    className="w-full px-4 py-2 border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                    required
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-[#45474c] mb-1">Client</label>
                  <input
                    type="text"
                    value={formData.client}
                    onChange={(e) => setFormData({ ...formData, client: e.target.value })}
                    className="w-full px-4 py-2 border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-[#45474c] mb-1">PIC</label>
                  <input
                    type="text"
                    value={formData.pic}
                    onChange={(e) => setFormData({ ...formData, pic: e.target.value })}
                    className="w-full px-4 py-2 border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-[#45474c] mb-1">Status</label>
                  <select
                    value={formData.status}
                    onChange={(e) => setFormData({ ...formData, status: e.target.value })}
                    className="w-full px-4 py-2 border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                  >
                    <option value="PENDING">Pending</option>
                    <option value="ACTIVE">Active</option>
                    <option value="ON HOLD">On Hold</option>
                    <option value="COMPLETED">Completed</option>
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-semibold text-[#45474c] mb-1">Lokasi</label>
                  <input
                    type="text"
                    value={formData.location}
                    onChange={(e) => setFormData({ ...formData, location: e.target.value })}
                    className="w-full px-4 py-2 border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none"
                  />
                </div>
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
