import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Switch } from '@/components/ui/switch'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import { CreditCard, ShieldCheck, Loader2 } from 'lucide-react'
import { useSettings } from '@/context/settings-context'
import { t } from '@/utils/translations'

export function PaymentProcessing() {
  const { settings, updateSettings, savePaymentProcessing, savingSections } = useSettings()
  const saving = savingSections.has('paymentProcessing')

  return (
    <div className="sp-flex sp-flex-col sp-gap-6">
      {/* Transaction Handling */}
      <Card>
        <CardHeader>
          <div className="sp-flex sp-items-center sp-gap-2">
            <ShieldCheck className="sp-h-5 sp-w-5 sp-text-muted-foreground" />
            <div className="sp-flex sp-flex-col sp-gap-1.5">
              <CardTitle className="sp-text-base sp-font-semibold">{t('transactionHandling')}</CardTitle>
              <CardDescription>
                {t('transactionHandlingDescription')}
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="sp-grid sp-gap-6">
            {/* Default Payment Behavior */}
            <div className="sp-flex sp-flex-col sp-gap-3">
              <div className="sp-flex sp-flex-col sp-gap-1">
                <Label className="sp-text-sm sp-font-medium">{t('defaultPaymentBehavior')}</Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  {t('paymentBehaviorDescription')}
                </p>
              </div>
              <RadioGroup
                value={String(settings.paymentBehavior)}
                onValueChange={(val) => updateSettings({ paymentBehavior: Number(val) })}
                className="sp-flex sp-gap-3"
              >
                <label
                  htmlFor="behavior-capture"
                  className={`sp-flex sp-flex-1 sp-cursor-pointer sp-items-center sp-gap-3 sp-rounded-lg sp-border sp-px-4 sp-py-3 sp-transition-colors ${
                    settings.paymentBehavior === 0
                      ? 'sp-border-primary sp-bg-primary/5'
                      : 'sp-border-border hover:sp-bg-secondary/50'
                  }`}
                >
                  <RadioGroupItem value="0" id="behavior-capture" />
                  <div className="sp-flex sp-flex-col">
                    <span className="sp-text-sm sp-font-medium">{t('capture')}</span>
                    <span className="sp-text-xs sp-text-muted-foreground">{t('chargeImmediately')}</span>
                  </div>
                </label>
                <label
                  htmlFor="behavior-authorize"
                  className={`sp-flex sp-flex-1 sp-cursor-pointer sp-items-center sp-gap-3 sp-rounded-lg sp-border sp-px-4 sp-py-3 sp-transition-colors ${
                    settings.paymentBehavior === 1
                      ? 'sp-border-primary sp-bg-primary/5'
                      : 'sp-border-border hover:sp-bg-secondary/50'
                  }`}
                >
                  <RadioGroupItem value="1" id="behavior-authorize" />
                  <div className="sp-flex sp-flex-col">
                    <span className="sp-text-sm sp-font-medium">{t('authorize')}</span>
                    <span className="sp-text-xs sp-text-muted-foreground">{t('reserveAndCaptureLater')}</span>
                  </div>
                </label>
              </RadioGroup>
            </div>

            {/* 3D Secure Behavior */}
            <div className="sp-flex sp-flex-col sp-gap-3">
              <div className="sp-flex sp-flex-col sp-gap-1">
                <Label className="sp-text-sm sp-font-medium">{t('behaviourWhen3dsFails')}</Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  {t('behaviourWhen3dsDescription')}
                </p>
              </div>
              <RadioGroup
                value={String(settings.paymentBehaviorWithout3D)}
                onValueChange={(val) => updateSettings({ paymentBehaviorWithout3D: Number(val) })}
                className="sp-flex sp-gap-3"
              >
                <label
                  htmlFor="3ds-cancel"
                  className={`sp-flex sp-flex-1 sp-cursor-pointer sp-items-center sp-gap-3 sp-rounded-lg sp-border sp-px-4 sp-py-3 sp-transition-colors ${
                    settings.paymentBehaviorWithout3D === 0
                      ? 'sp-border-primary sp-bg-primary/5'
                      : 'sp-border-border hover:sp-bg-secondary/50'
                  }`}
                >
                  <RadioGroupItem value="0" id="3ds-cancel" />
                  <div className="sp-flex sp-flex-col">
                    <span className="sp-text-sm sp-font-medium">{t('cancel')}</span>
                    <span className="sp-text-xs sp-text-muted-foreground">{t('rejectPayment')}</span>
                  </div>
                </label>
                <label
                  htmlFor="3ds-authorize"
                  className={`sp-flex sp-flex-1 sp-cursor-pointer sp-items-center sp-gap-3 sp-rounded-lg sp-border sp-px-4 sp-py-3 sp-transition-colors ${
                    settings.paymentBehaviorWithout3D === 1
                      ? 'sp-border-primary sp-bg-primary/5'
                      : 'sp-border-border hover:sp-bg-secondary/50'
                  }`}
                >
                  <RadioGroupItem value="1" id="3ds-authorize" />
                  <div className="sp-flex sp-flex-col">
                    <span className="sp-text-sm sp-font-medium">{t('authorize')}</span>
                    <span className="sp-text-xs sp-text-muted-foreground">{t('continueWithout3ds')}</span>
                  </div>
                </label>
                <label
                  htmlFor="3ds-capture"
                  className={`sp-flex sp-flex-1 sp-cursor-pointer sp-items-center sp-gap-3 sp-rounded-lg sp-border sp-px-4 sp-py-3 sp-transition-colors ${
                    settings.paymentBehaviorWithout3D === 2
                      ? 'sp-border-primary sp-bg-primary/5'
                      : 'sp-border-border hover:sp-bg-secondary/50'
                  }`}
                >
                  <RadioGroupItem value="2" id="3ds-capture" />
                  <div className="sp-flex sp-flex-col">
                    <span className="sp-text-sm sp-font-medium">{t('capture')}</span>
                    <span className="sp-text-xs sp-text-muted-foreground">{t('captureWithout3ds')}</span>
                  </div>
                </label>
              </RadioGroup>
            </div>

            {/* Restrict Refund */}
            <div className="sp-flex sp-flex-col sp-gap-3">
              <div className="sp-flex sp-flex-col sp-gap-1">
                <Label className="sp-text-sm sp-font-medium">{t('restrictRefundAmount')}</Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  {t('restrictRefundDescription')}
                </p>
              </div>
              <RadioGroup
                value={String(settings.restrictRefund)}
                onValueChange={(val) => updateSettings({ restrictRefund: Number(val) })}
                className="sp-flex sp-gap-3"
              >
                <label
                  htmlFor="refund-enable"
                  className={`sp-flex sp-flex-1 sp-cursor-pointer sp-items-center sp-gap-3 sp-rounded-lg sp-border sp-px-4 sp-py-3 sp-transition-colors ${
                    settings.restrictRefund === 1
                      ? 'sp-border-primary sp-bg-primary/5'
                      : 'sp-border-border hover:sp-bg-secondary/50'
                  }`}
                >
                  <RadioGroupItem value="1" id="refund-enable" />
                  <span className="sp-text-sm sp-font-medium">{t('enable')}</span>
                </label>
                <label
                  htmlFor="refund-disable"
                  className={`sp-flex sp-flex-1 sp-cursor-pointer sp-items-center sp-gap-3 sp-rounded-lg sp-border sp-px-4 sp-py-3 sp-transition-colors ${
                    settings.restrictRefund === 0
                      ? 'sp-border-primary sp-bg-primary/5'
                      : 'sp-border-border hover:sp-bg-secondary/50'
                  }`}
                >
                  <RadioGroupItem value="0" id="refund-disable" />
                  <span className="sp-text-sm sp-font-medium">{t('disable')}</span>
                </label>
              </RadioGroup>
            </div>

            {/* Order Creation Rule */}
            <div className="sp-flex sp-flex-col sp-gap-3">
              <div className="sp-flex sp-flex-col sp-gap-1">
                <Label className="sp-text-sm sp-font-medium">{t('orderCreationRule')}</Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  {t('orderCreationDescription')}
                </p>
              </div>
              <RadioGroup
                value={String(settings.orderCreationAfterAuth)}
                onValueChange={(val) => updateSettings({ orderCreationAfterAuth: Number(val) })}
                className="sp-flex sp-gap-3"
              >
                <label
                  htmlFor="order-after"
                  className={`sp-flex sp-flex-1 sp-cursor-pointer sp-items-center sp-gap-3 sp-rounded-lg sp-border sp-px-4 sp-py-3 sp-transition-colors ${
                    settings.orderCreationAfterAuth === 1
                      ? 'sp-border-primary sp-bg-primary/5'
                      : 'sp-border-border hover:sp-bg-secondary/50'
                  }`}
                >
                  <RadioGroupItem value="1" id="order-after" />
                  <div className="sp-flex sp-flex-col">
                    <span className="sp-text-sm sp-font-medium">{t('afterAuthorization')}</span>
                    <span className="sp-text-xs sp-text-muted-foreground">{t('createWhenAuthorized')}</span>
                  </div>
                </label>
                <label
                  htmlFor="order-before"
                  className={`sp-flex sp-flex-1 sp-cursor-pointer sp-items-center sp-gap-3 sp-rounded-lg sp-border sp-px-4 sp-py-3 sp-transition-colors ${
                    settings.orderCreationAfterAuth === 0
                      ? 'sp-border-primary sp-bg-primary/5'
                      : 'sp-border-border hover:sp-bg-secondary/50'
                  }`}
                >
                  <RadioGroupItem value="0" id="order-before" />
                  <div className="sp-flex sp-flex-col">
                    <span className="sp-text-sm sp-font-medium">{t('beforeAuthorization')}</span>
                    <span className="sp-text-xs sp-text-muted-foreground">{t('createBeforePayment')}</span>
                  </div>
                </label>
              </RadioGroup>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Card Display */}
      <Card>
        <CardHeader>
          <div className="sp-flex sp-items-center sp-gap-2">
            <CreditCard className="sp-h-5 sp-w-5 sp-text-muted-foreground" />
            <div className="sp-flex sp-flex-col sp-gap-1.5">
              <CardTitle className="sp-text-base sp-font-semibold">{t('cardDisplay')}</CardTitle>
              <CardDescription>
                {t('cardDisplayDescription')}
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="sp-grid sp-gap-4">
            <div className="sp-flex sp-items-center sp-justify-between sp-rounded-lg sp-border sp-bg-secondary/50 sp-px-4 sp-py-3">
              <div className="sp-flex sp-flex-col sp-gap-0.5">
                <Label htmlFor="group-cards" className="sp-font-medium sp-cursor-pointer">
                  {t('groupCardsLabel')}
                </Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  {t('groupCardsDescription')}
                </p>
              </div>
              <Switch
                id="group-cards"
                checked={settings.groupCards}
                onCheckedChange={(checked) => updateSettings({ groupCards: checked })}
              />
            </div>

            {settings.groupCards && (
              <div className="sp-flex sp-items-center sp-justify-between sp-rounded-lg sp-border sp-bg-secondary/50 sp-px-4 sp-py-3">
                <div className="sp-flex sp-flex-col sp-gap-0.5">
                  <Label htmlFor="show-cards-logo" className="sp-font-medium sp-cursor-pointer">
                    {t('showCardsLogo')}
                  </Label>
                  <p className="sp-text-xs sp-text-muted-foreground">
                    {t('showCardsLogoDescription')}
                  </p>
                </div>
                <Switch
                  id="show-cards-logo"
                  checked={settings.groupCardsLogo}
                  onCheckedChange={(checked) => updateSettings({ groupCardsLogo: checked })}
                />
              </div>
            )}
          </div>
        </CardContent>
      </Card>

      {/* Card Saving for Customers */}
      <Card>
        <CardHeader>
          <div className="sp-flex sp-items-center sp-gap-2">
            <CreditCard className="sp-h-5 sp-w-5 sp-text-muted-foreground" />
            <div className="sp-flex sp-flex-col sp-gap-1.5">
              <CardTitle className="sp-text-base sp-font-semibold">{t('cardSavingForCustomers')}</CardTitle>
              <CardDescription>
                {t('creditCardSavingDescription')}
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="sp-grid sp-gap-4">
            <div className="sp-flex sp-flex-col sp-gap-3">
              <RadioGroup
                value={String(settings.creditCardSave)}
                onValueChange={(val) => updateSettings({ creditCardSave: Number(val) })}
                className="sp-flex sp-gap-3"
              >
                <label
                  htmlFor="card-save-enable"
                  className={`sp-flex sp-flex-1 sp-cursor-pointer sp-items-center sp-gap-3 sp-rounded-lg sp-border sp-px-4 sp-py-3 sp-transition-colors ${
                    settings.creditCardSave === 1
                      ? 'sp-border-primary sp-bg-primary/5'
                      : 'sp-border-border hover:sp-bg-secondary/50'
                  }`}
                >
                  <RadioGroupItem value="1" id="card-save-enable" />
                  <span className="sp-text-sm sp-font-medium">{t('enable')}</span>
                </label>
                <label
                  htmlFor="card-save-disable"
                  className={`sp-flex sp-flex-1 sp-cursor-pointer sp-items-center sp-gap-3 sp-rounded-lg sp-border sp-px-4 sp-py-3 sp-transition-colors ${
                    settings.creditCardSave === 0
                      ? 'sp-border-primary sp-bg-primary/5'
                      : 'sp-border-border hover:sp-bg-secondary/50'
                  }`}
                >
                  <RadioGroupItem value="0" id="card-save-disable" />
                  <span className="sp-text-sm sp-font-medium">{t('disable')}</span>
                </label>
              </RadioGroup>
            </div>
          </div>
        </CardContent>
      </Card>

      <div className="sp-flex sp-justify-end">
        <Button className="sp-min-w-[120px]" onClick={savePaymentProcessing} disabled={saving} aria-label={saving ? t('saving') : undefined}>
          {saving ? <Loader2 className="sp-h-4 sp-w-4 sp-animate-spin" /> : t('saveChanges')}
        </Button>
      </div>
    </div>
  )
}
