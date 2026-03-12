import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Switch } from '@/components/ui/switch'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Settings2, Paintbrush, ClipboardList, Loader2 } from 'lucide-react'
import { useSettings } from '@/context/settings-context'
import { t } from '@/utils/translations'

export function GeneralSettings() {
  const { settings, updateSettings, saveGeneralSettings, savingSections } = useSettings()
  const saving = savingSections.has('generalSettings')

  return (
    <div className="sp-flex sp-flex-col sp-gap-6">
      {/* Order State */}
      <Card>
        <CardHeader>
          <div className="sp-flex sp-items-center sp-gap-2">
            <ClipboardList className="sp-h-5 sp-w-5 sp-text-muted-foreground" />
            <div className="sp-flex sp-flex-col sp-gap-1.5">
              <CardTitle className="sp-text-base sp-font-semibold">{t('orderState')}</CardTitle>
              <CardDescription>
                {t('orderStateDescription')}
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="sp-flex sp-flex-col sp-gap-2">
            <Label htmlFor="order-status">{t('statusAwaitingPayment')}</Label>
            <Select
              value={String(settings.orderStateAwaitingPayment)}
              onValueChange={(val) => updateSettings({ orderStateAwaitingPayment: Number(val) })}
            >
              <SelectTrigger id="order-status">
                <SelectValue placeholder={t('selectOrderStatus')} />
              </SelectTrigger>
              <SelectContent>
                {settings.orderStates.map((state) => (
                  <SelectItem key={state.id} value={String(state.id)}>
                    {state.name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            <p className="sp-text-xs sp-text-muted-foreground">
              {t('defaultStatusDescription')}
            </p>
          </div>
        </CardContent>
      </Card>

      {/* Styling */}
      <Card>
        <CardHeader>
          <div className="sp-flex sp-items-center sp-gap-2">
            <Paintbrush className="sp-h-5 sp-w-5 sp-text-muted-foreground" />
            <div className="sp-flex sp-flex-col sp-gap-1.5">
              <CardTitle className="sp-text-base sp-font-semibold">{t('styling')}</CardTitle>
              <CardDescription>
                {t('stylingDescription')}
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="sp-flex sp-flex-col sp-gap-2">
            <Label htmlFor="page-config-name">{t('configName')}</Label>
            <Input
              id="page-config-name"
              type="text"
              placeholder={t('enterConfigName')}
              value={settings.configurationName}
              onChange={(e) => updateSettings({ configurationName: e.target.value })}
            />
            <p className="sp-text-xs sp-text-muted-foreground">
              {t('configNameDescription')}
            </p>
          </div>
        </CardContent>
      </Card>

      {/* Configuration */}
      <Card>
        <CardHeader>
          <div className="sp-flex sp-items-center sp-gap-2">
            <Settings2 className="sp-h-5 sp-w-5 sp-text-muted-foreground" />
            <div className="sp-flex sp-flex-col sp-gap-1.5">
              <CardTitle className="sp-text-base sp-font-semibold">{t('configuration')}</CardTitle>
              <CardDescription>
                {t('configurationDescription')}
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="sp-grid sp-gap-5">
            <div className="sp-flex sp-flex-col sp-gap-2">
              <Label htmlFor="description">{t('description')}</Label>
              <Input
                id="description"
                type="text"
                placeholder={t('enterDescription')}
                value={settings.paymentDescription}
                onChange={(e) => updateSettings({ paymentDescription: e.target.value })}
              />
              <p className="sp-text-xs sp-text-muted-foreground">
                {t('descriptionHelp')}
              </p>
            </div>

            <div className="sp-flex sp-items-center sp-justify-between sp-rounded-lg sp-border sp-bg-secondary/50 sp-px-4 sp-py-3">
              <div className="sp-flex sp-flex-col sp-gap-0.5">
                <Label htmlFor="debug-mode" className="sp-font-medium sp-cursor-pointer">
                  {t('debugMode')}
                </Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  {t('debugModeDescription')}
                </p>
              </div>
              <Switch
                id="debug-mode"
                checked={settings.debugMode}
                onCheckedChange={(checked) => updateSettings({ debugMode: checked })}
              />
            </div>
          </div>
        </CardContent>
      </Card>

      <div className="sp-flex sp-justify-end">
        <Button className="sp-min-w-[120px]" onClick={saveGeneralSettings} disabled={saving}>
          {saving ? <Loader2 className="sp-h-4 sp-w-4 sp-animate-spin" /> : t('saveChanges')}
        </Button>
      </div>
    </div>
  )
}
