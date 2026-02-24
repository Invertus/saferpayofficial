import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Switch } from '@/components/ui/switch'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import { CreditCard, ShieldCheck, Loader2 } from 'lucide-react'
import { useSettings } from '@/context/settings-context'

export function PaymentProcessing() {
  const { settings, updateSettings, savePaymentProcessing, saving } = useSettings()

  return (
    <div className="sp-flex sp-flex-col sp-gap-6">
      {/* Transaction Handling */}
      <Card>
        <CardHeader>
          <div className="sp-flex sp-items-center sp-gap-2">
            <ShieldCheck className="sp-h-5 sp-w-5 sp-text-muted-foreground" />
            <div className="sp-flex sp-flex-col sp-gap-1.5">
              <CardTitle className="sp-text-base sp-font-semibold">Transaction Handling</CardTitle>
              <CardDescription>
                Configure how payments are processed, authorized, and captured.
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="sp-grid sp-gap-6">
            {/* Default Payment Behavior */}
            <div className="sp-flex sp-flex-col sp-gap-3">
              <div className="sp-flex sp-flex-col sp-gap-1">
                <Label className="sp-text-sm sp-font-medium">Default payment behavior</Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  How payment provider should behave when order is created.
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
                    <span className="sp-text-sm sp-font-medium">Capture</span>
                    <span className="sp-text-xs sp-text-muted-foreground">Charge immediately</span>
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
                    <span className="sp-text-sm sp-font-medium">Authorize</span>
                    <span className="sp-text-xs sp-text-muted-foreground">Reserve and capture later</span>
                  </div>
                </label>
              </RadioGroup>
            </div>

            {/* 3D Secure Behavior */}
            <div className="sp-flex sp-flex-col sp-gap-3">
              <div className="sp-flex sp-flex-col sp-gap-1">
                <Label className="sp-text-sm sp-font-medium">Behaviour when 3D Secure fails</Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  Default payment behavior for payment without 3-D Secure.
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
                    <span className="sp-text-sm sp-font-medium">Cancel</span>
                    <span className="sp-text-xs sp-text-muted-foreground">Reject the payment</span>
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
                    <span className="sp-text-sm sp-font-medium">Authorize</span>
                    <span className="sp-text-xs sp-text-muted-foreground">Continue without 3DS</span>
                  </div>
                </label>
              </RadioGroup>
            </div>

            {/* Restrict Refund */}
            <div className="sp-flex sp-flex-col sp-gap-3">
              <div className="sp-flex sp-flex-col sp-gap-1">
                <Label className="sp-text-sm sp-font-medium">Restrict RefundAmount to Captured Amount</Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  If set to true, the refund will be rejected if the sum of authorized refunds exceeds the capture value.
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
                  <span className="sp-text-sm sp-font-medium">Enable</span>
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
                  <span className="sp-text-sm sp-font-medium">Disable</span>
                </label>
              </RadioGroup>
            </div>

            {/* Order Creation Rule */}
            <div className="sp-flex sp-flex-col sp-gap-3">
              <div className="sp-flex sp-flex-col sp-gap-1">
                <Label className="sp-text-sm sp-font-medium">Order creation rule</Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  Select the option to determine whether the order should be created.
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
                    <span className="sp-text-sm sp-font-medium">After authorization</span>
                    <span className="sp-text-xs sp-text-muted-foreground">Create when authorized</span>
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
                    <span className="sp-text-sm sp-font-medium">Before authorization</span>
                    <span className="sp-text-xs sp-text-muted-foreground">Create before payment</span>
                  </div>
                </label>
              </RadioGroup>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Card Display & Saving */}
      <Card>
        <CardHeader>
          <div className="sp-flex sp-items-center sp-gap-2">
            <CreditCard className="sp-h-5 sp-w-5 sp-text-muted-foreground" />
            <div className="sp-flex sp-flex-col sp-gap-1.5">
              <CardTitle className="sp-text-base sp-font-semibold">Card Display & Saving</CardTitle>
              <CardDescription>
                Configure how cards appear at checkout and whether customers can save them.
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="sp-grid sp-gap-4">
            <div className="sp-flex sp-items-center sp-justify-between sp-rounded-lg sp-border sp-bg-secondary/50 sp-px-4 sp-py-3">
              <div className="sp-flex sp-flex-col sp-gap-0.5">
                <Label htmlFor="group-cards" className="sp-font-medium sp-cursor-pointer">
                  {"Group debit/credit cards as 'Cards' in checkout"}
                </Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  {"If enabled, all supported card brands will be grouped and shown as a single 'Cards' payment method at checkout."}
                </p>
              </div>
              <Switch
                id="group-cards"
                checked={settings.groupCards}
                onCheckedChange={(checked) => updateSettings({ groupCards: checked })}
              />
            </div>

            <div className="sp-flex sp-items-center sp-justify-between sp-rounded-lg sp-border sp-bg-secondary/50 sp-px-4 sp-py-3">
              <div className="sp-flex sp-flex-col sp-gap-0.5">
                <Label htmlFor="show-cards-logo" className="sp-font-medium sp-cursor-pointer">
                  {"Show 'Cards' payment method logo"}
                </Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  {"If enabled, a logo for the grouped 'Cards' payment method will be displayed at checkout."}
                </p>
              </div>
              <Switch
                id="show-cards-logo"
                checked={settings.groupCardsLogo}
                onCheckedChange={(checked) => updateSettings({ groupCardsLogo: checked })}
              />
            </div>

            <div className="sp-flex sp-flex-col sp-gap-3 sp-pt-2">
              <div className="sp-flex sp-flex-col sp-gap-1">
                <Label className="sp-text-sm sp-font-medium">Credit card saving for customers</Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  Allow customers to save credit card for faster purchase.
                </p>
              </div>
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
                  <span className="sp-text-sm sp-font-medium">Enable</span>
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
                  <span className="sp-text-sm sp-font-medium">Disable</span>
                </label>
              </RadioGroup>
            </div>
          </div>
        </CardContent>
      </Card>

      <div className="sp-flex sp-justify-end">
        <Button className="sp-min-w-[120px]" onClick={savePaymentProcessing} disabled={saving}>
          {saving ? <Loader2 className="sp-h-4 sp-w-4 sp-animate-spin" /> : 'Save Changes'}
        </Button>
      </div>
    </div>
  )
}
