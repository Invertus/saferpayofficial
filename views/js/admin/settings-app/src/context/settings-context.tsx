import React, { createContext, useContext, useState, useCallback } from 'react'
import type { SaferpaySettingsData, PaymentMethodData, TerminalOption } from '@/types'
import * as api from '@/api/client'
import { toast } from '@/hooks/use-toast'

interface SettingsContextValue {
  settings: SaferpaySettingsData
  updateSettings: (updates: Partial<SaferpaySettingsData>) => void
  saveCredentials: () => Promise<void>
  savePaymentProcessing: () => Promise<void>
  saveEmailSettings: () => Promise<void>
  saveGeneralSettings: () => Promise<void>
  savePaymentMethods: () => Promise<void>
  fetchTerminals: (env: string, username: string, password: string, customerId: string) => Promise<TerminalOption[]>
  refreshPaymentMethods: () => Promise<void>
  paymentMethods: PaymentMethodData[]
  updatePaymentMethod: (name: string, updates: Partial<PaymentMethodData>) => void
  saving: boolean
}

const SettingsContext = createContext<SettingsContextValue | null>(null)

export function SettingsProvider({ children }: { children: React.ReactNode }) {
  const [settings, setSettings] = useState<SaferpaySettingsData>(() => window.saferpaySettingsData)
  const [paymentMethods, setPaymentMethods] = useState<PaymentMethodData[]>(() => window.saferpaySettingsData.paymentMethods || [])
  const [saving, setSaving] = useState(false)

  const updateSettings = useCallback((updates: Partial<SaferpaySettingsData>) => {
    setSettings((prev) => ({ ...prev, ...updates }))
  }, [])

  const updatePaymentMethod = useCallback((name: string, updates: Partial<PaymentMethodData>) => {
    setPaymentMethods((prev) =>
      prev.map((m) => (m.name === name ? { ...m, ...updates } : m)),
    )
  }, [])

  const handleSave = useCallback(async (saveFn: () => Promise<{ success: boolean; message?: string }>, label: string) => {
    setSaving(true)
    try {
      const result = await saveFn()
      if (result.success) {
        toast({ title: `${label} saved successfully`, variant: 'default' })
      } else {
        toast({ title: result.message || `Failed to save ${label}`, variant: 'destructive' })
      }
    } catch (e) {
      toast({ title: `Error saving ${label}`, variant: 'destructive' })
    } finally {
      setSaving(false)
    }
  }, [])

  const saveCredentials = useCallback(async () => {
    await handleSave(() => api.saveCredentials({
      testMode: settings.testMode,
      testUsername: settings.testUsername,
      testPassword: settings.testPassword,
      testCustomerId: settings.testCustomerId,
      testTerminalId: settings.testTerminalId,
      testMerchantEmails: settings.testMerchantEmails,
      testFieldAccessToken: settings.testFieldAccessToken,
      testFieldJsUrl: settings.testFieldJsUrl,
      testBusinessLicense: settings.testBusinessLicense,
      liveUsername: settings.liveUsername,
      livePassword: settings.livePassword,
      liveCustomerId: settings.liveCustomerId,
      liveTerminalId: settings.liveTerminalId,
      liveMerchantEmails: settings.liveMerchantEmails,
      liveFieldAccessToken: settings.liveFieldAccessToken,
      liveFieldJsUrl: settings.liveFieldJsUrl,
      liveBusinessLicense: settings.liveBusinessLicense,
    }), 'API Credentials')
  }, [settings, handleSave])

  const savePaymentProcessingFn = useCallback(async () => {
    await handleSave(() => api.savePaymentProcessing({
      paymentBehavior: settings.paymentBehavior,
      paymentBehaviorWithout3D: settings.paymentBehaviorWithout3D,
      restrictRefund: settings.restrictRefund,
      orderCreationAfterAuth: settings.orderCreationAfterAuth,
      groupCards: settings.groupCards,
      groupCardsLogo: settings.groupCardsLogo,
      creditCardSave: settings.creditCardSave,
    }), 'Payment Processing')
  }, [settings, handleSave])

  const saveEmailSettingsFn = useCallback(async () => {
    await handleSave(() => api.saveEmailSettings({
      allowSaferpayMail: settings.allowSaferpayMail,
      sendNewOrderMail: settings.sendNewOrderMail,
      sendOrderConfMail: settings.sendOrderConfMail,
    }), 'Email Settings')
  }, [settings, handleSave])

  const saveGeneralSettingsFn = useCallback(async () => {
    await handleSave(() => api.saveGeneralSettings({
      orderStateAwaitingPayment: settings.orderStateAwaitingPayment,
      paymentDescription: settings.paymentDescription,
      configurationName: settings.configurationName,
      debugMode: settings.debugMode,
    }), 'General Settings')
  }, [settings, handleSave])

  const savePaymentMethodsFn = useCallback(async () => {
    await handleSave(() => api.savePaymentMethods(paymentMethods), 'Payment Methods')
  }, [paymentMethods, handleSave])

  const refreshPaymentMethods = useCallback(async () => {
    try {
      const result = await api.refreshData()
      if (result.success && result.data?.paymentMethods) {
        setPaymentMethods(result.data.paymentMethods as PaymentMethodData[])
      }
    } catch {
      // silently fail, user still has initial data
    }
  }, [])

  const fetchTerminals = useCallback(async (env: string, username: string, password: string, customerId: string) => {
    try {
      const result = await api.getTerminals(env, username, password, customerId)
      if (result.success) {
        return result.terminals
      }
      toast({ title: 'Failed to fetch terminals', variant: 'destructive' })
      return []
    } catch {
      toast({ title: 'Error fetching terminals', variant: 'destructive' })
      return []
    }
  }, [])

  return (
    <SettingsContext.Provider
      value={{
        settings,
        updateSettings,
        saveCredentials,
        savePaymentProcessing: savePaymentProcessingFn,
        saveEmailSettings: saveEmailSettingsFn,
        saveGeneralSettings: saveGeneralSettingsFn,
        savePaymentMethods: savePaymentMethodsFn,
        fetchTerminals,
        refreshPaymentMethods,
        paymentMethods,
        updatePaymentMethod,
        saving,
      }}
    >
      {children}
    </SettingsContext.Provider>
  )
}

export function useSettings() {
  const context = useContext(SettingsContext)
  if (!context) {
    throw new Error('useSettings must be used within a SettingsProvider')
  }
  return context
}
