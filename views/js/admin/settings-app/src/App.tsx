import React from 'react'
import { SettingsProvider } from './context/settings-context'
import { SaferpaySettings } from './components/settings/saferpay-settings'
import { t } from '@/utils/translations'

class ErrorBoundary extends React.Component<
  { children: React.ReactNode },
  { hasError: boolean }
> {
  constructor(props: { children: React.ReactNode }) {
    super(props)
    this.state = { hasError: false }
  }

  static getDerivedStateFromError() {
    return { hasError: true }
  }

  render() {
    if (this.state.hasError) {
      return (
        <div style={{ padding: '20px', color: '#c00' }}>
          {t('errorLoadingSettings')}
        </div>
      )
    }
    return this.props.children
  }
}

export default function App() {
  return (
    <ErrorBoundary>
      <SettingsProvider>
        <SaferpaySettings />
      </SettingsProvider>
    </ErrorBoundary>
  )
}
