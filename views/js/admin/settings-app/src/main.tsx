import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App'
import './globals.css'
import type { SaferpaySettingsData } from '@/types'
import { initTranslations } from '@/utils/translations'

function parseSettingsData(): SaferpaySettingsData | null {
  const el = document.getElementById('saferpay-settings-data')
  if (!el?.textContent) return null

  try {
    return JSON.parse(el.textContent) as SaferpaySettingsData
  } catch {
    return null
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const rootEl = document.getElementById('saferpay-settings-root')
  if (!rootEl) return

  const data = parseSettingsData()
  if (!data) {
    const errorMessage = rootEl.dataset.errorMessage || 'Failed to load settings data.'
    const errorEl = document.createElement('div')
    errorEl.style.cssText = 'padding:20px;color:#c00'
    errorEl.textContent = errorMessage
    rootEl.replaceChildren(errorEl)
    return
  }

  window.saferpaySettingsData = data
  initTranslations(data.translations || {})

  ReactDOM.createRoot(rootEl).render(
    <React.StrictMode>
      <App />
    </React.StrictMode>,
  )
})
