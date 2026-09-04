import { dbReady, getSettings, saveSettings, toastOk, toastError } from './db.js'

const $ = (id) => document.getElementById(id)
let logoData = ''
let sigData = ''

function makeFilePicker(cb) {
  const input = document.createElement('input')
  input.type = 'file'
  input.accept = 'image/png,image/jpeg'
  input.style.display = 'none'
  document.body.appendChild(input)
  input.addEventListener('change', () => {
    const file = input.files && input.files[0]
    input.remove()
    if (!file) return
    const reader = new FileReader()
    reader.onload = () => cb(reader.result)
    reader.readAsDataURL(file)
  })
  input.click()
}

function attachImageButtons() {
  document.querySelectorAll('button').forEach((btn) => {
    const text = (btn.textContent || '').trim()
    if (text === 'Ganti Gambar') {
      const card = btn.closest('.rounded-xl')
      const isLogo = !!(card && card.querySelector('#logo-preview'))
      btn.addEventListener('click', () => {
        makeFilePicker((dataUrl) => {
          if (isLogo) {
            logoData = dataUrl
            const img = $('logo-preview')
            if (img) img.src = dataUrl
          } else {
            sigData = dataUrl
            const box = $('sig-upload-box')
            if (box) {
              const ph = $('sig-placeholder')
              if (ph) ph.remove()
              box.innerHTML = `<img class="max-h-full max-w-full object-contain p-1" src="${dataUrl}" alt="ttd">`
            }
          }
        })
      })
    } else if (text === 'Hapus Logo') {
      btn.addEventListener('click', () => {
        logoData = ''
        const img = $('logo-preview')
        if (img) img.removeAttribute('src')
      })
    } else if (text === 'Hapus') {
      const card = btn.closest('.rounded-xl')
      const isSig = !!(card && card.querySelector('#sig-upload-box'))
      if (isSig) {
        btn.addEventListener('click', () => {
          sigData = ''
          const box = $('sig-upload-box')
          if (box) box.innerHTML = '<div class="text-on-surface-variant opacity-40"><span class="material-symbols-outlined text-3xl">draw</span></div>'
        })
      }
    }
  })
}

function readForm() {
  const posLeft = $('pos-left')
  return {
    company_name: $('set-company')?.value.trim() || '',
    company_address: $('set-address')?.value.trim() || '',
    signer_name: $('set-signer')?.value.trim() || '',
    signer_position: $('set-title')?.value.trim() || '',
    footer_note: $('set-footer')?.value.trim() || '',
    sign_position: posLeft && posLeft.checked ? 'left' : 'right',
    logo_url: logoData,
    signer_signature: sigData,
  }
}

async function save() {
  if (!dbReady) {
    toastError('Supabase belum dikonfigurasi. Cek VITE_SUPABASE_URL / VITE_SUPABASE_ANON_KEY.')
    return
  }
  const btn = $('btn-save-settings')
  if (btn) {
    btn.disabled = true
    btn.textContent = 'Menyimpan...'
  }
  const { error } = await saveSettings(readForm())
  if (btn) {
    btn.disabled = false
    btn.textContent = 'Simpan Perubahan'
  }
  if (error) {
    toastError('Gagal menyimpan pengaturan: ' + error.message)
    return
  }
  toastOk('Pengaturan template PO tersimpan')
}

async function load() {
  if (!dbReady) return
  const { data, error } = await getSettings()
  if (error) {
    toastError('Gagal memuat pengaturan: ' + error.message + '. Jalankan supabase/schema.sql dulu.')
    return
  }
  if (!data) return
  const s = data
  if ($('set-company')) $('set-company').value = s.company_name || ''
  if ($('set-address')) $('set-address').value = s.company_address || ''
  if ($('set-signer')) $('set-signer').value = s.signer_name || ''
  if ($('set-title')) $('set-title').value = s.signer_position || ''
  if ($('set-footer')) $('set-footer').value = s.footer_note || ''
  const left = $('pos-left')
  const right = $('pos-right')
  if (s.sign_position === 'left' && left) left.checked = true
  else if (right) right.checked = true
  if (s.logo_url) {
    logoData = s.logo_url
    const img = $('logo-preview')
    if (img) img.src = s.logo_url
  }
  if (s.signer_signature) {
    sigData = s.signer_signature
    const box = $('sig-upload-box')
    if (box) {
      const ph = $('sig-placeholder')
      if (ph) ph.remove()
      box.innerHTML = `<img class="max-h-full max-w-full object-contain p-1" src="${s.signer_signature}" alt="ttd">`
    }
  }
}

attachImageButtons()
$('btn-save-settings')?.addEventListener('click', save)
load()
