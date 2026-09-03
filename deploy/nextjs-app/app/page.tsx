'use client'

import { useEffect } from 'react'
import { useRouter } from 'next/navigation'
import { supabase } from '@/lib/supabase'

export default function Home() {
  const router = useRouter()

  useEffect(() => {
    const checkAuth = async () => {
      const { data: { session } } = await supabase.auth.getSession()
      
      if (session) {
        router.push('/dashboard')
      } else {
        router.push('/login')
      }
    }

    checkAuth()
  }, [router])

  return (
    <div className="min-h-screen flex items-center justify-center bg-[#fbf8fa]">
      <div className="text-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-[#0058be] mx-auto"></div>
        <p className="mt-4 text-[#45474c]">Loading...</p>
      </div>
    </div>
  )
}
