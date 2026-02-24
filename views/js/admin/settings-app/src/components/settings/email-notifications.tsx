import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Switch } from '@/components/ui/switch'
import { Mail, Info, Loader2 } from 'lucide-react'
import { useSettings } from '@/context/settings-context'

export function EmailNotifications() {
  const { settings, updateSettings, saveEmailSettings, saving } = useSettings()

  return (
    <div className="sp-flex sp-flex-col sp-gap-6">
      <Card>
        <CardHeader>
          <div className="sp-flex sp-items-center sp-gap-2">
            <Mail className="sp-h-5 sp-w-5 sp-text-muted-foreground" />
            <div className="sp-flex sp-flex-col sp-gap-1.5">
              <CardTitle className="sp-text-base sp-font-semibold">Email Sending</CardTitle>
              <CardDescription>
                Configure which emails are sent during the payment process.
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="sp-grid sp-gap-4">
            <div className="sp-flex sp-items-center sp-justify-between sp-rounded-lg sp-border sp-bg-secondary/50 sp-px-4 sp-py-3">
              <div className="sp-flex sp-flex-col sp-gap-0.5 sp-pr-4">
                <Label htmlFor="email-completion" className="sp-font-medium sp-cursor-pointer">
                  Send an email from Saferpay on payment completion
                </Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  With this setting enabled an email from the Saferpay system will be sent to the customer.
                </p>
              </div>
              <Switch
                id="email-completion"
                checked={settings.allowSaferpayMail}
                onCheckedChange={(checked) => updateSettings({ allowSaferpayMail: checked })}
              />
            </div>

            <div className="sp-flex sp-items-center sp-justify-between sp-rounded-lg sp-border sp-bg-secondary/50 sp-px-4 sp-py-3">
              <div className="sp-flex sp-flex-col sp-gap-0.5 sp-pr-4">
                <Label htmlFor="new-order-mail" className="sp-font-medium sp-cursor-pointer">
                  Send new order mail on authorization
                </Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  Receive a notification when an order is authorized by Saferpay (Using the Mail alert module).
                </p>
              </div>
              <Switch
                id="new-order-mail"
                checked={settings.sendNewOrderMail}
                onCheckedChange={(checked) => updateSettings({ sendNewOrderMail: checked })}
              />
            </div>

            <div className="sp-flex sp-items-center sp-justify-between sp-rounded-lg sp-border sp-bg-secondary/50 sp-px-4 sp-py-3">
              <div className="sp-flex sp-flex-col sp-gap-0.5 sp-pr-4">
                <Label htmlFor="order-confirmation" className="sp-font-medium sp-cursor-pointer">
                  Send order confirmation mail on payment completion
                </Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  Send an email from Saferpay on payment completion.
                </p>
              </div>
              <Switch
                id="order-confirmation"
                checked={settings.sendOrderConfMail}
                onCheckedChange={(checked) => updateSettings({ sendOrderConfMail: checked })}
              />
            </div>

            <div className="sp-flex sp-items-center sp-gap-3 sp-rounded-lg sp-bg-[#294e57]/5 sp-border sp-border-[#294e57]/30 sp-border-l-[3px] sp-border-l-[#294e57] sp-px-4 sp-py-3 sp-text-sm sp-text-foreground">
              <Info className="sp-h-4 sp-w-4 sp-shrink-0 sp-text-[#294e57]" />
              <div className="sp-flex sp-flex-col sp-gap-1">
                <p className="sp-mb-0">
                  When this feature is enabled, a confirmation email will be only sent once the payment is authorized by Saferpay.
                </p>
                <p className="sp-mb-0 sp-text-muted-foreground">
                  For this feature to be functioning you need to have the Mail Alert module configured.
                </p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <div className="sp-flex sp-justify-end">
        <Button className="sp-min-w-[120px]" onClick={saveEmailSettings} disabled={saving}>
          {saving ? <Loader2 className="sp-h-4 sp-w-4 sp-animate-spin" /> : 'Save Changes'}
        </Button>
      </div>
    </div>
  )
}
