import React, { createContext, useContext, useState, useCallback, useMemo, useRef } from 'react'
import type { SaferpaySettingsData, PaymentMethodData, TerminalOption } from '@/types'
import * as api from '@/api/client'
import { toast } from '@/hooks/use-toast'
import { t } from '@/utils/translations'

type SavingSection = 'credentials' | 'paymentProcessing' | 'emailSettings' | 'generalSettings' | 'paymentMethods'

interface SettingsContextValue {
  settings: SaferpaySettingsData
  updateSettings: (updates: Partial<SaferpaySettingsData>) => void
  saveCredentials: () => Promise<void>
  savePaymentProcessing: () => Promise<void>
  saveEmailSettings: () => Promise<void>
  saveGeneralSettings: () => Promise<void>
  savePaymentMethods: () => Promise<void>
  fetchTerminals: (env: string, username: string, password: string) => Promise<TerminalOption[]>
  refreshPaymentMethods: () => Promise<void>
  paymentMethods: PaymentMethodData[]
  updatePaymentMethod: (name: string, updates: Partial<PaymentMethodData>) => void
  savingSections: Set<SavingSection>
}

const SettingsContext = createContext<SettingsContextValue | null>(null)

export function SettingsProvider({ children }: { children: React.ReactNode }) {
  const [settings, setSettings] = useState<SaferpaySettingsData>(() => window.saferpaySettingsData)
  const [paymentMethods, setPaymentMethods] = useState<PaymentMethodData[]>(() => {
    const methods = window.saferpaySettingsData.paymentMethods
    return Array.isArray(methods) ? methods : []
  })
  const [savingSections, setSavingSections] = useState<Set<SavingSection>>(new Set())

  const settingsRef = useRef(settings)
  settingsRef.current = settings

  const paymentMethodsRef = useRef(paymentMethods)
  paymentMethodsRef.current = paymentMethods

  const updateSettings = useCallback((updates: Partial<SaferpaySettingsData>) => {
    setSettings((prev) => ({ ...prev, ...updates }))
  }, [])

  const updatePaymentMethod = useCallback((name: string, updates: Partial<PaymentMethodData>) => {
    setPaymentMethods((prev) =>
      prev.map((m) => (m.name === name ? { ...m, ...updates } : m)),
    )
  }, [])

  const handleSave = useCallback(async (
    saveFn: () => Promise<{ success: boolean; message?: string }>,
    label: string,
    section: SavingSection,
  ) => {
    setSavingSections((prev) => new Set(prev).add(section))
    try {
      const result = await saveFn()
      if (result.success) {
        toast({ title: result.message || t('savedSuccessfully', label), variant: 'default' })
      } else {
        toast({ title: result.message || t('failedToSave', label), variant: 'destructive' })
      }
    } catch (e) {
      const message = e instanceof Error ? e.message : 'Unknown error'
      toast({ title: t('errorSaving', label, message), variant: 'destructive' })
    } finally {
      setSavingSections((prev) => {
        const next = new Set(prev)
        next.delete(section)
        return next
      })
    }
  }, [])

  const saveCredentials = useCallback(async () => {
    const currentSettings = settingsRef.current
    await handleSave(() => api.saveCredentials({
      testMode: currentSettings.testMode,
      testUsername: currentSettings.testUsername,
      testPassword: currentSettings.testPassword,
      testTerminalId: currentSettings.testTerminalId,
      testMerchantEmails: currentSettings.testMerchantEmails,
      testFieldAccessToken: currentSettings.testFieldAccessToken,
      testFieldJsUrl: currentSettings.testFieldJsUrl,
      testBusinessLicense: currentSettings.testBusinessLicense,
      liveUsername: currentSettings.liveUsername,
      livePassword: currentSettings.livePassword,
      liveTerminalId: currentSettings.liveTerminalId,
      liveMerchantEmails: currentSettings.liveMerchantEmails,
      liveFieldAccessToken: currentSettings.liveFieldAccessToken,
      liveFieldJsUrl: currentSettings.liveFieldJsUrl,
      liveBusinessLicense: currentSettings.liveBusinessLicense,
    }), 'API Credentials', 'credentials')
  }, [handleSave])

  const savePaymentProcessingFn = useCallback(async () => {
    const currentSettings = settingsRef.current
    await handleSave(() => api.savePaymentProcessing({
      paymentBehavior: currentSettings.paymentBehavior,
      paymentBehaviorWithout3D: currentSettings.paymentBehaviorWithout3D,
      restrictRefund: currentSettings.restrictRefund,
      orderCreationAfterAuth: currentSettings.orderCreationAfterAuth,
      groupCards: currentSettings.groupCards,
      groupCardsLogo: currentSettings.groupCardsLogo,
      creditCardSave: currentSettings.creditCardSave,
    }), 'Payment Processing', 'paymentProcessing')
  }, [handleSave])

  const saveEmailSettingsFn = useCallback(async () => {
    const currentSettings = settingsRef.current
    await handleSave(() => api.saveEmailSettings({
      allowSaferpayMail: currentSettings.allowSaferpayMail,
      sendNewOrderMail: currentSettings.sendNewOrderMail,
      sendOrderConfMail: currentSettings.sendOrderConfMail,
    }), 'Email Settings', 'emailSettings')
  }, [handleSave])

  const saveGeneralSettingsFn = useCallback(async () => {
    const currentSettings = settingsRef.current
    await handleSave(() => api.saveGeneralSettings({
      orderStateAwaitingPayment: currentSettings.orderStateAwaitingPayment,
      paymentDescription: currentSettings.paymentDescription,
      configurationName: currentSettings.configurationName,
      debugMode: currentSettings.debugMode,
    }), 'General Settings', 'generalSettings')
  }, [handleSave])

  const savePaymentMethodsFn = useCallback(async () => {
    await handleSave(
      () => api.savePaymentMethods(paymentMethodsRef.current),
      'Payment Methods',
      'paymentMethods',
    )
  }, [handleSave])

  const refreshPaymentMethods = useCallback(async () => {
    try {
      const result = await api.refreshData()
      if (result.success && result.data?.paymentMethods) {
        const methods = result.data.paymentMethods
        if (Array.isArray(methods)) {
          setPaymentMethods(methods as PaymentMethodData[])
        }
      }
    } catch (e) {
      const message = e instanceof Error ? e.message : 'Unknown error'
      toast({ title: t('errorRefreshingPaymentMethods', message), variant: 'destructive' })
    }
  }, [])

  const fetchTerminals = useCallback(async (env: string, username: string, password: string) => {
    try {
      const result = await api.getTerminals(env, username, password)
      if (result.success) {
        return result.terminals
      }
      toast({ title: t('failedToFetchTerminals'), variant: 'destructive' })
      return []
    } catch {
      toast({ title: t('errorFetchingTerminals'), variant: 'destructive' })
      return []
    }
  }, [])

  const value = useMemo<SettingsContextValue>(() => ({
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
    savingSections,
  }), [
    settings,
    updateSettings,
    saveCredentials,
    savePaymentProcessingFn,
    saveEmailSettingsFn,
    saveGeneralSettingsFn,
    savePaymentMethodsFn,
    fetchTerminals,
    refreshPaymentMethods,
    paymentMethods,
    updatePaymentMethod,
    savingSections,
  ])

  return (
    <SettingsContext.Provider value={value}>
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
