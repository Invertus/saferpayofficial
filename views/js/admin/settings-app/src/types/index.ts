export interface PaymentMethodData {
  name: string
  displayName: string
  enabled: boolean
  showLogos: boolean
  showCustomForm: boolean
  hasCustomForm: boolean
  countries: number[]
  currencies: number[]
}

export interface TerminalOption {
  id: string
  name: string
}

export interface SaferpaySettingsData {
  // Environment
  testMode: boolean

  // Test credentials
  testUsername: string
  testPassword: string
  testTerminalId: string
  testMerchantEmails: string
  testFieldAccessToken: string
  testFieldJsUrl: string
  // Live credentials
  liveUsername: string
  livePassword: string
  liveTerminalId: string
  liveMerchantEmails: string
  liveFieldAccessToken: string
  liveFieldJsUrl: string

  // License (read-only, auto-detected from API)
  hasBusinessLicense: boolean

  // Payment Processing
  paymentBehavior: number
  paymentBehaviorWithout3D: number
  restrictRefund: number
  orderCreationAfterAuth: number
  groupCards: boolean
  groupCardsLogo: boolean
  creditCardSave: number

  // Email
  allowSaferpayMail: boolean
  sendNewOrderMail: boolean
  sendOrderConfMail: boolean

  // General
  orderStateAwaitingPayment: number
  paymentDescription: string
  configurationName: string
  debugMode: boolean

  // Reference data
  orderStates: Array<{ id: number; name: string }>
  countries: Array<{ id: number; name: string }>
  currencies: Array<{ id: number; iso_code: string }>
  paymentMethods: PaymentMethodData[]

  // Endpoints
  ajaxUrl: string
  adminToken: string

  // Translations
  translations: Record<string, string>
}

declare global {
  interface Window {
    saferpaySettingsData: SaferpaySettingsData
  }
}
