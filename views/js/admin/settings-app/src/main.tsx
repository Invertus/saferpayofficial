import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App'
import './globals.css'

document.addEventListener('DOMContentLoaded', () => {
  const rootEl = document.getElementById('saferpay-settings-root')
  if (rootEl) {
    ReactDOM.createRoot(rootEl).render(
      <React.StrictMode>
        <App />
      </React.StrictMode>,
    )
  }
})
