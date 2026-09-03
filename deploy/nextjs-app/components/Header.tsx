'use client'

interface HeaderProps {
  user: any
}

export default function Header({ user }: HeaderProps) {
  return (
    <header className="sticky top-0 z-40 bg-white border-b border-[#c5c6cd] flex justify-between items-center h-16 px-6">
      <div className="flex items-center gap-8">
        <span className="font-semibold text-lg text-[#091426]">Procurement Management</span>
        <div className="relative w-80">
          <svg className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-[#45474c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Pencarian Global..."
            className="w-full pl-10 pr-4 py-2 bg-[#f5f3f4] border border-[#c5c6cd] rounded-lg text-sm focus:ring-2 focus:ring-[#0058be] focus:border-[#0058be] outline-none transition-all"
          />
        </div>
      </div>
      <div className="flex items-center gap-4">
        <button className="p-2 text-[#45474c] hover:text-[#0058be] transition-all">
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
        </button>
        <button className="p-2 text-[#45474c] hover:text-[#0058be] transition-all">
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </button>
      </div>
    </header>
  )
}
