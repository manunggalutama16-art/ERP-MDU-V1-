// Modal form generik.
// fields: [{ id, label, type?, required?, placeholder?, options?: [{value,label}] }]
// onSubmit(values) -> return string pesan error (atau null/undefined bila sukses)
// resolve(values) bila berhasil disubmit, resolve(null) bila ditutup tanpa submit.
export function showFormModal({ title, fields = [], initial = {}, submitLabel = 'Simpan', onSubmit }) {
  return new Promise((resolve) => {
    const esc = (s) =>
      String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;')

    const fieldHTML = fields
      .map((f) => {
        const val = esc(initial[f.id] ?? '')
        let input
        if (f.type === 'select') {
          const opts = (f.options || [])
            .map((o) => `<option value="${esc(o.value)}" ${String(o.value) === String(initial[f.id] ?? '') ? 'selected' : ''}>${esc(o.label)}</option>`)
            .join('')
          input = `<select id="fm-${f.id}" class="w-full px-md py-2.5 border border-outline-variant rounded-lg bg-surface text-body-md focus:border-secondary focus:ring-2 focus:ring-secondary/10 outline-none transition-all">${opts}</select>`
        } else {
          const isArea = f.type === 'textarea'
          const typeAttr = isArea ? '' : ` type="${esc(f.type || 'text')}"`
          const baseAttrs = `id="fm-${f.id}" placeholder="${esc(f.placeholder || '')}" class="w-full px-md py-2.5 border border-outline-variant rounded-lg bg-surface text-body-md focus:border-secondary focus:ring-2 focus:ring-secondary/10 outline-none transition-all"`
          input = isArea
            ? `<textarea ${baseAttrs} rows="3">${esc(val)}</textarea>`
            : `<input ${baseAttrs}${typeAttr} value="${val}">`
        }
        return `<div class="space-y-xs">
          <label class="block text-label-sm text-on-surface-variant font-semibold uppercase tracking-wider" for="fm-${f.id}">${esc(f.label)}${f.required ? ' <span class="text-error">*</span>' : ''}</label>
          ${input}
        </div>`
      })
      .join('')

    const overlay = document.createElement('div')
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(9,20,38,.5);z-index:100000;display:flex;align-items:flex-start;justify-content:center;padding:40px 16px;overflow-y:auto;'
    overlay.innerHTML = `
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-lg" style="animation:fmPop .18s ease">
        <div class="flex justify-between items-center mb-md">
          <h3 class="font-headline-sm text-headline-sm text-primary">${esc(title)}</h3>
          <button type="button" class="p-1 text-on-surface-variant hover:text-on-surface transition-colors" data-close>
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="space-y-md max-h-[65vh] overflow-y-auto pr-xs">${fieldHTML}
          <p class="text-body-sm text-error hidden" data-err></p>
        </div>
        <div class="mt-lg flex justify-end gap-md">
          <button type="button" data-close class="px-lg py-md border border-outline-variant rounded-lg font-label-md text-on-surface hover:bg-surface-container-low transition-colors">Batal</button>
          <button type="button" data-submit class="px-lg py-md bg-secondary text-on-secondary rounded-lg font-label-md hover:opacity-90 transition-opacity">${esc(submitLabel)}</button>
        </div>
      </div>`
    document.body.appendChild(overlay)

    const errEl = overlay.querySelector('[data-err]')
    const submitBtn = overlay.querySelector('[data-submit]')

    function readValues() {
      const values = {}
      fields.forEach((f) => {
        const el = overlay.querySelector(`#fm-${f.id}`)
        values[f.id] = el ? el.value.trim() : ''
      })
      return values
    }

    function close(val) {
      overlay.remove()
      resolve(val)
    }

    overlay.querySelectorAll('[data-close]').forEach((b) => b.addEventListener('click', () => close(null)))
    overlay.addEventListener('mousedown', (e) => { if (e.target === overlay) close(null) })

    submitBtn.addEventListener('click', async () => {
      const values = readValues()
      const missing = fields.filter((f) => f.required && !values[f.id]).map((f) => f.label)
      if (missing.length) {
        errEl.textContent = 'Wajib diisi: ' + missing.join(', ')
        errEl.classList.remove('hidden')
        return
      }
      submitBtn.disabled = true
      submitBtn.textContent = 'Menyimpan...'
      const err = await onSubmit(values)
      submitBtn.disabled = false
      submitBtn.textContent = submitLabel
      if (err) {
        errEl.textContent = typeof err === 'string' ? err : 'Gagal menyimpan. Coba lagi.'
        errEl.classList.remove('hidden')
      } else {
        close(values)
      }
    })

    const style = document.createElement('style')
    style.textContent = '@keyframes fmPop{from{opacity:0;transform:translateY(-6px) scale(.98)}to{opacity:1;transform:none}}'
    document.head.appendChild(style)
    overlay.querySelector('#fm-' + fields[0]?.id)?.focus()
  })
}
