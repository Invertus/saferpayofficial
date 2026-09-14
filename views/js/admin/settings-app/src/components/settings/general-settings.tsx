import { useEffect, useRef, useState } from 'react'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Switch } from '@/components/ui/switch'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Settings2, Paintbrush, ClipboardList, Loader2, Info } from 'lucide-react'
import { useSettings } from '@/context/settings-context'
import { t } from '@/utils/translations'

// Radix treats an empty SelectItem value as "no value", so the deliberate
// "no configuration" choice needs a sentinel that maps back to '' on change.
const NO_CONFIG_VALUE = '__none__'

export function GeneralSettings() {
  const { settings, updateSettings, saveGeneralSettings, savingSections, fetchPaymentPageConfigurations } = useSettings()
  const saving = savingSections.has('generalSettings')

  const environment = settings.testMode ? 'test' : 'live'
  const username = settings.testMode ? settings.testUsername : settings.liveUsername
  const password = settings.testMode ? settings.testPassword : settings.livePassword
  const hasCredentials = Boolean(username && password)

  const [configurations, setConfigurations] = useState<string[]>([])
  const [configurationsLoaded, setConfigurationsLoaded] = useState(false)
  const [loadingConfigurations, setLoadingConfigurations] = useState(false)
  const [configurationsError, setConfigurationsError] = useState('')
  const debounceTimer = useRef<ReturnType<typeof setTimeout> | null>(null)

  // Credentials live on another tab, so this reacts to the shared state rather
  // than to local input - same debounce as the terminal lookup.
  useEffect(() => {
    if (debounceTimer.current) clearTimeout(debounceTimer.current)

    if (!hasCredentials) {
      setConfigurations([])
      setConfigurationsLoaded(false)
      setConfigurationsError('')
      return
    }

    debounceTimer.current = setTimeout(async () => {
      setLoadingConfigurations(true)
      setConfigurationsError('')
      try {
        setConfigurations(await fetchPaymentPageConfigurations(environment, username, password))
        setConfigurationsLoaded(true)
      } catch {
        setConfigurations([])
        setConfigurationsLoaded(false)
        setConfigurationsError(t('failedToFetchConfigNames'))
      } finally {
        setLoadingConfigurations(false)
      }
    }, 1000)

    return () => {
      if (debounceTimer.current) clearTimeout(debounceTimer.current)
    }
  }, [environment, username, password])

  const savedName = settings.configurationName
  const savedNameNotInList = Boolean(savedName) && !configurations.includes(savedName)

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
            <Select
              value={savedName || NO_CONFIG_VALUE}
              onValueChange={(val) =>
                updateSettings({ configurationName: val === NO_CONFIG_VALUE ? '' : val })
              }
              disabled={!hasCredentials && !savedName}
            >
              <SelectTrigger id="page-config-name">
                <SelectValue placeholder={hasCredentials ? t('selectConfigName') : t('enterCredentialsFirst')} />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value={NO_CONFIG_VALUE}>{t('noConfigNameOption')}</SelectItem>
                {configurations.map((name) => (
                  <SelectItem key={name} value={name}>{name}</SelectItem>
                ))}
                {savedNameNotInList && (
                  <SelectItem value={savedName}>
                    {configurationsLoaded ? t('configNameNotInAccount', savedName) : savedName}
                  </SelectItem>
                )}
              </SelectContent>
            </Select>
            {loadingConfigurations && (
              <p className="sp-text-xs sp-text-muted-foreground sp-flex sp-items-center sp-gap-1">
                <Loader2 className="sp-h-3 sp-w-3 sp-animate-spin" />
                {t('loadingConfigNames')}
              </p>
            )}
            {configurationsError && (
              <p className="sp-text-xs sp-text-destructive">{configurationsError}</p>
            )}
            {configurationsLoaded && savedNameNotInList && (
              <p className="sp-text-xs sp-text-destructive">{t('savedConfigNameMissing')}</p>
            )}
            {configurationsLoaded && configurations.length === 0 && (
              <p className="sp-text-xs sp-text-muted-foreground">{t('noConfigNamesInAccount')}</p>
            )}
            {!hasCredentials && (
              <p className="sp-text-xs sp-text-muted-foreground">
                {t('enterCredentialsToLoadConfigNames')}
              </p>
            )}
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
            {/* Order reference on payment page */}
            <div className="sp-flex sp-flex-col sp-gap-3">
              <Label className="sp-text-sm sp-font-medium">{t('orderReferenceOnPaymentPage')}</Label>
              <RadioGroup
                value={String(settings.orderIdOption)}
                onValueChange={(val) => updateSettings({ orderIdOption: Number(val) })}
                className="sp-flex sp-flex-col sp-gap-3"
              >
                <label
                  htmlFor="order-id-prestashop"
                  className={`sp-flex sp-cursor-pointer sp-items-center sp-gap-3 sp-rounded-lg sp-border sp-px-4 sp-py-3 sp-transition-colors ${
                    settings.orderIdOption === 0
                      ? 'sp-border-primary sp-bg-primary/5'
                      : 'sp-border-border hover:sp-bg-secondary/50'
                  }`}
                >
                  <RadioGroupItem value="0" id="order-id-prestashop" />
                  <span className="sp-text-sm sp-font-medium">{t('usePrestaShopOrderReference')}</span>
                </label>
                <label
                  htmlFor="order-id-description"
                  className={`sp-flex sp-cursor-pointer sp-items-center sp-gap-3 sp-rounded-lg sp-border sp-px-4 sp-py-3 sp-transition-colors ${
                    settings.orderIdOption === 1
                      ? 'sp-border-primary sp-bg-primary/5'
                      : 'sp-border-border hover:sp-bg-secondary/50'
                  }`}
                >
                  <RadioGroupItem value="1" id="order-id-description" />
                  <span className="sp-text-sm sp-font-medium">{t('useDescriptionFieldValue')}</span>
                </label>
              </RadioGroup>
            </div>

            {settings.orderIdOption === 1 && (
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
            )}

            {/* Info banner */}
            <div className="sp-flex sp-items-center sp-justify-center sp-gap-3 sp-rounded-lg sp-border sp-bg-secondary/50 sp-px-5 sp-py-4">
              <Info className="sp-h-5 sp-w-5 sp-text-primary sp-shrink-0" />
              <p className="sp-text-sm sp-text-foreground">
                {t('orderReferenceFallbackInfo')}
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
        <Button className="sp-min-w-[120px]" onClick={saveGeneralSettings} disabled={saving} aria-label={saving ? t('saving') : undefined}>
          {saving ? <Loader2 className="sp-h-4 sp-w-4 sp-animate-spin" /> : t('saveChanges')}
        </Button>
      </div>
    </div>
  )
}
