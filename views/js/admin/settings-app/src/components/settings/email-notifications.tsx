import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Switch } from '@/components/ui/switch'
import { Mail, Info, Loader2 } from 'lucide-react'
import { useSettings } from '@/context/settings-context'
import { t } from '@/utils/translations'

export function EmailNotifications() {
  const { settings, updateSettings, saveEmailSettings, savingSections } = useSettings()
  const saving = savingSections.has('emailSettings')

  return (
    <div className="sp-flex sp-flex-col sp-gap-6">
      <Card>
        <CardHeader>
          <div className="sp-flex sp-items-center sp-gap-2">
            <Mail className="sp-h-5 sp-w-5 sp-text-muted-foreground" />
            <div className="sp-flex sp-flex-col sp-gap-1.5">
              <CardTitle className="sp-text-base sp-font-semibold">{t('emailSending')}</CardTitle>
              <CardDescription>
                {t('emailSendingDescription')}
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="sp-grid sp-gap-4">
            <div className="sp-flex sp-items-center sp-justify-between sp-rounded-lg sp-border sp-bg-secondary/50 sp-px-4 sp-py-3">
              <div className="sp-flex sp-flex-col sp-gap-0.5 sp-pr-4">
                <Label htmlFor="email-completion" className="sp-font-medium sp-cursor-pointer">
                  {t('saferpayCustomerMail')}
                </Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  {t('saferpayCustomerMailDescription')}
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
                  {t('newOrderMail')}
                </Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  {t('newOrderMailDescription')}
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
                  {t('orderConfMail')}
                </Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  {t('orderConfMailDescription')}
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
                  {t('emailConfInfo')}
                </p>
                <p className="sp-mb-0 sp-text-muted-foreground">
                  {t('emailConfMailAlert')}
                </p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <div className="sp-flex sp-justify-end">
        <Button className="sp-min-w-[120px]" onClick={saveEmailSettings} disabled={saving}>
          {saving ? <Loader2 className="sp-h-4 sp-w-4 sp-animate-spin" /> : t('saveChanges')}
        </Button>
      </div>
    </div>
  )
}
