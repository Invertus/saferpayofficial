import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App'
import './globals.css'
import type { SaferpaySettingsData } from '@/types'
import { initTranslations } from '@/utils/translations'

// The settings controller passes the data through Media::addJsDef().
function getSettingsData(): SaferpaySettingsData | null {
  const data: unknown = window.saferpaySettingsData
  if (!data || typeof data !== 'object') return null

  return data as SaferpaySettingsData
}

document.addEventListener('DOMContentLoaded', () => {
  const rootEl = document.getElementById('saferpay-settings-root')
  if (!rootEl) return

  const data = getSettingsData()
  if (!data) {
    const errorMessage = rootEl.dataset.errorMessage || 'Failed to load settings data.'
    const errorEl = document.createElement('div')
    errorEl.style.cssText = 'padding:20px;color:#c00'
    errorEl.textContent = errorMessage
    rootEl.replaceChildren(errorEl)
    return
  }

  initTranslations(data.translations || {})

  ReactDOM.createRoot(rootEl).render(
    <React.StrictMode>
      <App />
    </React.StrictMode>,
  )
})
