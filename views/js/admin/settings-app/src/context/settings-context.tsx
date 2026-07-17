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
  generateFieldAccessToken: () => Promise<{ success: boolean; message?: string; token?: string }>
  refreshPaymentMethods: () => Promise<void>
  paymentMethods: PaymentMethodData[]
  updatePaymentMethod: (name: string, updates: Partial<PaymentMethodData>) => void
  savingSections: Set<SavingSection>
}

const SettingsContext = createContext<SettingsContextValue | null>(null)

export function SettingsProvider({ children }: { children: React.ReactNode }) {
  const [settings, setSettings] = useState<SaferpaySettingsData>(() => window.saferpaySettingsData)
  const [paymentMethods, setPaymentMethods] = useState<PaymentMethodData[]>(
    () => Array.isArray(window.saferpaySettingsData.paymentMethods) ? window.saferpaySettingsData.paymentMethods : [],
  )
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
    saveFn: () => Promise<{ success: boolean; message?: string; warning?: boolean }>,
    label: string,
    section: SavingSection,
  ) => {
    setSavingSections((prev) => new Set(prev).add(section))
    try {
      const result = await saveFn()
      if (result.success) {
        const variant = result.warning ? 'warning' : 'default'
        toast({ title: result.message || t('savedSuccessfully', label), variant })
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
    await handleSave(async () => {
      const result = await api.saveCredentials({
        testMode: currentSettings.testMode,
        testUsername: currentSettings.testUsername,
        testPassword: currentSettings.testPassword,
        testTerminalId: currentSettings.testTerminalId,
        testMerchantEmails: currentSettings.testMerchantEmails,
        testFieldAccessToken: currentSettings.testFieldAccessToken,
        testFieldJsUrl: currentSettings.testFieldJsUrl,
        liveUsername: currentSettings.liveUsername,
        livePassword: currentSettings.livePassword,
        liveTerminalId: currentSettings.liveTerminalId,
        liveMerchantEmails: currentSettings.liveMerchantEmails,
        liveFieldAccessToken: currentSettings.liveFieldAccessToken,
        liveFieldJsUrl: currentSettings.liveFieldJsUrl,
      })
      const data = result as unknown as Record<string, unknown>
      if (result.success) {
        setSettings((prev) => ({
          ...prev,
          ...(typeof data.testHasBusinessLicense === 'boolean' ? { testHasBusinessLicense: data.testHasBusinessLicense as boolean } : {}),
          ...(typeof data.liveHasBusinessLicense === 'boolean' ? { liveHasBusinessLicense: data.liveHasBusinessLicense as boolean } : {}),
        }))
      }
      return { ...result, warning: data.warning === true }
    }, 'API Credentials', 'credentials')
  }, [handleSave])

  const savePaymentProcessing = useCallback(async () => {
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

  const saveEmailSettings = useCallback(async () => {
    const currentSettings = settingsRef.current
    await handleSave(() => api.saveEmailSettings({
      allowSaferpayMail: currentSettings.allowSaferpayMail,
      sendNewOrderMail: currentSettings.sendNewOrderMail,
      sendOrderConfMail: currentSettings.sendOrderConfMail,
    }), 'Email Settings', 'emailSettings')
  }, [handleSave])

  const saveGeneralSettings = useCallback(async () => {
    const currentSettings = settingsRef.current
    await handleSave(() => api.saveGeneralSettings({
      orderStateAwaitingPayment: currentSettings.orderStateAwaitingPayment,
      paymentDescription: currentSettings.paymentDescription,
      configurationName: currentSettings.configurationName,
      orderIdOption: currentSettings.orderIdOption,
      debugMode: currentSettings.debugMode,
    }), 'General Settings', 'generalSettings')
  }, [handleSave])

  const savePaymentMethods = useCallback(async () => {
    await handleSave(
      () => api.savePaymentMethods(paymentMethodsRef.current),
      'Payment Methods',
      'paymentMethods',
    )
  }, [handleSave])

  const refreshPaymentMethods = useCallback(async () => {
    try {
      const result = await api.refreshData()
      if (result.success && Array.isArray(result.data?.paymentMethods)) {
        setPaymentMethods(result.data.paymentMethods as PaymentMethodData[])
      }
      if (result.data?.paymentMethodsFetchFailed === true) {
        toast({ title: t('paymentMethodsUnreachable'), variant: 'warning' })
      }
    } catch {
      toast({ title: t('errorRefreshingPaymentMethods'), variant: 'destructive' })
    }
  }, [])

  const generateFieldAccessToken = useCallback(async () => {
    const s = settingsRef.current
    const env = s.testMode ? 'test' : 'live'
    const username = s.testMode ? s.testUsername : s.liveUsername
    const password = s.testMode ? s.testPassword : s.livePassword
    const terminalId = s.testMode ? s.testTerminalId : s.liveTerminalId

    const result = await api.generateFieldAccessToken(env, username, password, terminalId)
    if (!result.success) {
      throw new Error(result.message || t('failedToGenerateToken'))
    }

    if (result.token) {
      const fieldKey = s.testMode ? 'testFieldAccessToken' : 'liveFieldAccessToken'
      setSettings((prev) => ({ ...prev, [fieldKey]: result.token }))
    }

    return result
  }, [])

  const fetchTerminals = useCallback(async (env: string, username: string, password: string) => {
    const result = await api.getTerminals(env, username, password)
    if (!result.success) {
      throw new Error(result.message || t('failedToFetchTerminals'))
    }
    return result.terminals
  }, [])

  const value = useMemo<SettingsContextValue>(() => ({
    settings,
    updateSettings,
    saveCredentials,
    savePaymentProcessing,
    saveEmailSettings,
    saveGeneralSettings,
    savePaymentMethods,
    fetchTerminals,
    generateFieldAccessToken,
    refreshPaymentMethods,
    paymentMethods,
    updatePaymentMethod,
    savingSections,
  }), [
    settings,
    updateSettings,
    saveCredentials,
    savePaymentProcessing,
    saveEmailSettings,
    saveGeneralSettings,
    savePaymentMethods,
    fetchTerminals,
    generateFieldAccessToken,
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
