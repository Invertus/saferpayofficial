import { t } from '@/utils/translations'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { ApiCredentials } from './api-credentials'
import { PaymentProcessing } from './payment-processing'
import { PaymentMethods } from './payment-methods'
import { EmailNotifications } from './email-notifications'
import { GeneralSettings } from './general-settings'
import { ToastContainer } from './toast-container'
import { Key, CreditCard, Wallet, Mail, Settings2 } from 'lucide-react'

export function SaferpaySettings() {
  return (
    <div className="sp-mx-auto sp-max-w-5xl sp-px-4 sp-py-8 md:sp-px-6 lg:sp-px-8">
      <div className="sp-mb-8">
        <h1 className="sp-text-2xl sp-font-semibold sp-tracking-tight sp-text-foreground">
          {t('saferpaySettings')}
        </h1>
        <p className="sp-mt-1 sp-text-sm sp-text-muted-foreground">
          {t('configureIntegration')}
        </p>
      </div>

      <Tabs defaultValue="credentials" className="sp-flex sp-flex-col sp-gap-6">
        <TabsList className="sp-h-auto sp-w-full sp-justify-start sp-gap-1 sp-rounded-lg sp-bg-white sp-border sp-border-border sp-p-1">
          <TabsTrigger
            value="credentials"
            aria-label={t('tabApiCredentials')}
            className="sp-flex sp-items-center sp-gap-2 sp-rounded-md sp-px-2.5 sp-py-2.5 sm:sp-px-4 sp-text-sm sp-font-normal sp-bg-transparent data-[state=active]:sp-bg-primary data-[state=active]:sp-text-primary-foreground data-[state=active]:sp-shadow-none"
          >
            <Key className="sp-h-4 sp-w-4 sp-shrink-0" />
            <span className="sp-hidden sm:sp-inline">{t('tabApiCredentials')}</span>
          </TabsTrigger>
          <TabsTrigger
            value="methods"
            aria-label={t('tabPaymentMethods')}
            className="sp-flex sp-items-center sp-gap-2 sp-rounded-md sp-px-2.5 sp-py-2.5 sm:sp-px-4 sp-text-sm sp-font-normal sp-bg-transparent data-[state=active]:sp-bg-primary data-[state=active]:sp-text-primary-foreground data-[state=active]:sp-shadow-none"
          >
            <Wallet className="sp-h-4 sp-w-4 sp-shrink-0" />
            <span className="sp-hidden sm:sp-inline">{t('tabPaymentMethods')}</span>
          </TabsTrigger>
          <TabsTrigger
            value="payment"
            aria-label={t('tabPaymentProcessing')}
            className="sp-flex sp-items-center sp-gap-2 sp-rounded-md sp-px-2.5 sp-py-2.5 sm:sp-px-4 sp-text-sm sp-font-normal sp-bg-transparent data-[state=active]:sp-bg-primary data-[state=active]:sp-text-primary-foreground data-[state=active]:sp-shadow-none"
          >
            <CreditCard className="sp-h-4 sp-w-4 sp-shrink-0" />
            <span className="sp-hidden sm:sp-inline">{t('tabPaymentProcessing')}</span>
          </TabsTrigger>
          <TabsTrigger
            value="email"
            aria-label={t('tabEmailNotifications')}
            className="sp-flex sp-items-center sp-gap-2 sp-rounded-md sp-px-2.5 sp-py-2.5 sm:sp-px-4 sp-text-sm sp-font-normal sp-bg-transparent data-[state=active]:sp-bg-primary data-[state=active]:sp-text-primary-foreground data-[state=active]:sp-shadow-none"
          >
            <Mail className="sp-h-4 sp-w-4 sp-shrink-0" />
            <span className="sp-hidden sm:sp-inline">{t('tabEmailNotifications')}</span>
          </TabsTrigger>
          <TabsTrigger
            value="general"
            aria-label={t('tabGeneralSettings')}
            className="sp-flex sp-items-center sp-gap-2 sp-rounded-md sp-px-2.5 sp-py-2.5 sm:sp-px-4 sp-text-sm sp-font-normal sp-bg-transparent data-[state=active]:sp-bg-primary data-[state=active]:sp-text-primary-foreground data-[state=active]:sp-shadow-none"
          >
            <Settings2 className="sp-h-4 sp-w-4 sp-shrink-0" />
            <span className="sp-hidden sm:sp-inline">{t('tabGeneralSettings')}</span>
          </TabsTrigger>
        </TabsList>

        <TabsContent value="credentials">
          <ApiCredentials />
        </TabsContent>

        <TabsContent value="methods">
          <PaymentMethods />
        </TabsContent>

        <TabsContent value="payment">
          <PaymentProcessing />
        </TabsContent>

        <TabsContent value="email">
          <EmailNotifications />
        </TabsContent>

        <TabsContent value="general">
          <GeneralSettings />
        </TabsContent>
      </Tabs>

      <ToastContainer />
    </div>
  )
}
