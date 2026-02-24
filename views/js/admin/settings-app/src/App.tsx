import { SettingsProvider } from './context/settings-context'
import { SaferpaySettings } from './components/settings/saferpay-settings'

export default function App() {
  return (
    <SettingsProvider>
      <SaferpaySettings />
    </SettingsProvider>
  )
}
