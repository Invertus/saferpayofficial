import type { PaymentMethodData, TerminalOption } from '@/types'

function getConfig() {
  return window.saferpaySettingsData
}

async function postAjax(action: string, data: Record<string, unknown> = {}) {
  const config = getConfig()
  const separator = config.ajaxUrl.includes('?') ? '&' : '?'
  const url = `${config.ajaxUrl}${separator}ajax=1&action=${action}`

  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: JSON.stringify(data),
  })

  if (!response.ok) {
    throw new Error(`HTTP error ${response.status}`)
  }

  return response.json()
}

export async function saveCredentials(data: Record<string, unknown>): Promise<{ success: boolean; message?: string }> {
  return postAjax('saveCredentials', data)
}

export async function savePaymentProcessing(data: Record<string, unknown>): Promise<{ success: boolean; message?: string }> {
  return postAjax('savePaymentProcessing', data)
}

export async function saveEmailSettings(data: Record<string, unknown>): Promise<{ success: boolean; message?: string }> {
  return postAjax('saveEmailSettings', data)
}

export async function saveGeneralSettings(data: Record<string, unknown>): Promise<{ success: boolean; message?: string }> {
  return postAjax('saveGeneralSettings', data)
}

export async function savePaymentMethods(methods: PaymentMethodData[]): Promise<{ success: boolean; message?: string }> {
  return postAjax('savePaymentMethods', { paymentMethods: methods })
}

export async function getTerminals(
  env: string,
  username: string,
  password: string,
  customerId: string,
): Promise<{ success: boolean; terminals: TerminalOption[] }> {
  return postAjax('getTerminals', { env, username, password, customerId })
}

export async function refreshData(): Promise<{ success: boolean; data: Record<string, unknown> }> {
  return postAjax('refreshData')
}
