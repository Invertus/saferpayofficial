import type { PaymentMethodData, TerminalOption } from '@/types'

interface AjaxResponse {
  success: boolean
  message?: string
}

function getConfig() {
  return window.saferpaySettingsData
}

async function postAjax(action: string, data: Record<string, unknown> = {}): Promise<AjaxResponse> {
  const config = getConfig()
  const separator = config.ajaxUrl.includes('?') ? '&' : '?'
  const url = `${config.ajaxUrl}${separator}ajax=1&action=${action}&token=${encodeURIComponent(config.adminToken)}`

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

  const result = await response.json()
  if (typeof result !== 'object' || result === null || typeof result.success !== 'boolean') {
    throw new Error('Invalid response format')
  }

  return result
}

export async function saveCredentials(data: Record<string, unknown>): Promise<AjaxResponse> {
  return postAjax('saveCredentials', data)
}

export async function savePaymentProcessing(data: Record<string, unknown>): Promise<AjaxResponse> {
  return postAjax('savePaymentProcessing', data)
}

export async function saveEmailSettings(data: Record<string, unknown>): Promise<AjaxResponse> {
  return postAjax('saveEmailSettings', data)
}

export async function saveGeneralSettings(data: Record<string, unknown>): Promise<AjaxResponse> {
  return postAjax('saveGeneralSettings', data)
}

export async function savePaymentMethods(methods: PaymentMethodData[]): Promise<AjaxResponse> {
  return postAjax('savePaymentMethods', { paymentMethods: methods })
}

export async function getTerminals(
  env: string,
  username: string,
  password: string,
): Promise<{ success: boolean; terminals: TerminalOption[] }> {
  return postAjax('getTerminals', { env, username, password }) as Promise<{
    success: boolean
    terminals: TerminalOption[]
  }>
}

export async function refreshData(): Promise<{ success: boolean; data: Record<string, unknown> }> {
  return postAjax('refreshData') as Promise<{ success: boolean; data: Record<string, unknown> }>
}
