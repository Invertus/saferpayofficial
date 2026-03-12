import { useState, useEffect, useCallback, useRef } from 'react'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Switch } from '@/components/ui/switch'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { AlertCircle, Eye, EyeOff, Key, Shield, Loader2, Info } from 'lucide-react'
import { useSettings } from '@/context/settings-context'
import { t } from '@/utils/translations'
import type { TerminalOption } from '@/types'

export function ApiCredentials() {
  const { settings, updateSettings, saveCredentials, fetchTerminals, savingSections } = useSettings()
  const saving = savingSections.has('credentials')
  const [showApiPassword, setShowApiPassword] = useState(false)
  const [isLoadingTerminals, setIsLoadingTerminals] = useState(false)
  const [terminals, setTerminals] = useState<TerminalOption[]>([])

  const environment = settings.testMode ? 'test' : 'live'
  const isTest = settings.testMode

  const username = isTest ? settings.testUsername : settings.liveUsername
  const password = isTest ? settings.testPassword : settings.livePassword
  const terminalId = isTest ? settings.testTerminalId : settings.liveTerminalId
  const merchantEmails = isTest ? settings.testMerchantEmails : settings.liveMerchantEmails
  const fieldAccessToken = isTest ? settings.testFieldAccessToken : settings.liveFieldAccessToken
  const fieldJsUrl = isTest ? settings.testFieldJsUrl : settings.liveFieldJsUrl
  const hasBusinessLicense = isTest ? settings.testBusinessLicense : settings.liveBusinessLicense

  const prefix = isTest ? 'test' : 'live'
  const setField = (field: string, value: string | boolean) => {
    updateSettings({ [`${prefix}${field.charAt(0).toUpperCase() + field.slice(1)}`]: value } as Record<string, string | boolean>)
  }

  const envLabel = isTest ? t('test') : t('live')
  const hasCredentials = username.length > 0 && password.length > 0

  const handleFetchTerminals = useCallback(async () => {
    if (!username || !password) return
    setIsLoadingTerminals(true)
    try {
      const result = await fetchTerminals(environment, username, password)
      setTerminals(result)
    } finally {
      setIsLoadingTerminals(false)
    }
  }, [environment, username, password, fetchTerminals])

  // Reset terminals when switching environment
  useEffect(() => {
    setTerminals([])
  }, [environment])

  // Auto-fetch terminals when credentials are complete (debounced)
  const fetchTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null)
  useEffect(() => {
    if (fetchTimerRef.current) {
      clearTimeout(fetchTimerRef.current)
    }
    if (hasCredentials && terminals.length === 0) {
      fetchTimerRef.current = setTimeout(() => {
        handleFetchTerminals()
      }, 800)
    }
    return () => {
      if (fetchTimerRef.current) clearTimeout(fetchTimerRef.current)
    }
  }, [hasCredentials, environment]) // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <div className="sp-flex sp-flex-col sp-gap-6">
      {/* Environment Selector */}
      <Card>
        <CardHeader>
          <div className="sp-flex sp-items-center sp-justify-between">
            <div className="sp-flex sp-flex-col sp-gap-1.5">
              <CardTitle className="sp-text-base sp-font-semibold">{t('environment')}</CardTitle>
              <CardDescription>
                {t('envDescription')}
              </CardDescription>
            </div>
            <Select
              value={environment}
              onValueChange={(val: string) => updateSettings({ testMode: val === 'test' })}
            >
              <SelectTrigger className="sp-w-[180px]" aria-label={t('selectEnvironment')}>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="test">
                  <span className="sp-flex sp-items-center sp-gap-2">
                    <span className="sp-h-2 sp-w-2 sp-rounded-full sp-bg-amber-500" />
                    {t('testEnvironment')}
                  </span>
                </SelectItem>
                <SelectItem value="live">
                  <span className="sp-flex sp-items-center sp-gap-2">
                    <span className="sp-h-2 sp-w-2 sp-rounded-full sp-bg-emerald-500" />
                    {t('liveEnvironment')}
                  </span>
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </CardHeader>
        <CardContent>
          <div className={`sp-flex sp-items-center sp-gap-2 sp-rounded-lg sp-px-4 sp-py-3 sp-text-sm ${
            isTest
              ? 'sp-bg-amber-50 sp-text-amber-800 sp-border sp-border-amber-200'
              : 'sp-bg-emerald-50 sp-text-emerald-800 sp-border sp-border-emerald-200'
          }`}>
            {isTest ? (
              <AlertCircle className="sp-h-4 sp-w-4 sp-shrink-0" />
            ) : (
              <Shield className="sp-h-4 sp-w-4 sp-shrink-0" />
            )}
            <span>
              {isTest
                ? t('testModeWarning')
                : t('liveModeWarning')}
            </span>
          </div>
        </CardContent>
      </Card>

      {/* API Credentials */}
      <Card>
        <CardHeader>
          <div className="sp-flex sp-items-center sp-gap-2">
            <Key className="sp-h-5 sp-w-5 sp-text-muted-foreground" />
            <div className="sp-flex sp-flex-col sp-gap-1.5">
              <CardTitle className="sp-text-base sp-font-semibold">
                {t('apiCredentials')}
              </CardTitle>
              <CardDescription>
                {t('enterSaferpayCredentials', envLabel.toLowerCase())}
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="sp-grid sp-gap-5">
            <div className="sp-grid sp-gap-5 md:sp-grid-cols-2">
              <div className="sp-flex sp-flex-col sp-gap-2">
                <Label htmlFor="api-username">{t('jsonApiUsername')}</Label>
                <Input
                  id="api-username"
                  type="text"
                  placeholder={t('enterApiUsername', envLabel.toLowerCase())}
                  value={username}
                  onChange={(e) => setField('username', e.target.value)}
                />
              </div>
              <div className="sp-flex sp-flex-col sp-gap-2">
                <Label htmlFor="api-password">{t('jsonApiPassword')}</Label>
                <div className="sp-relative">
                  <Input
                    id="api-password"
                    type={showApiPassword ? 'text' : 'password'}
                    placeholder={t('enterApiPassword', envLabel.toLowerCase())}
                    value={password}
                    onChange={(e) => setField('password', e.target.value)}
                    className="sp-pr-10"
                  />
                  <button
                    type="button"
                    onClick={() => setShowApiPassword(!showApiPassword)}
                    className="sp-absolute sp-right-3 sp-top-1/2 sp--translate-y-1/2 sp-text-muted-foreground hover:sp-text-foreground sp-transition-colors"
                    aria-label={showApiPassword ? t('hidePassword') : t('showPassword')}
                  >
                    {showApiPassword ? <EyeOff className="sp-h-4 sp-w-4" /> : <Eye className="sp-h-4 sp-w-4" />}
                  </button>
                </div>
              </div>
            </div>

            {hasCredentials && (
              <div className="sp-grid sp-gap-5 md:sp-grid-cols-2">
                <div className="sp-flex sp-flex-col sp-gap-2">
                  <Label htmlFor="terminal-id">{t('terminalId')}</Label>
                  <div className="sp-flex sp-gap-2">
                    <Select
                      value={terminalId}
                      onValueChange={(val) => setField('terminalId', val)}
                    >
                      <SelectTrigger id="terminal-id" className="sp-flex-1">
                        <SelectValue placeholder={t('selectTerminal')} />
                      </SelectTrigger>
                      <SelectContent>
                        {terminals.map((terminal) => (
                          <SelectItem key={terminal.id} value={terminal.id}>{terminal.name}</SelectItem>
                        ))}
                        {terminalId && terminals.length === 0 && (
                          <SelectItem value={terminalId}>{terminalId}</SelectItem>
                        )}
                      </SelectContent>
                    </Select>
                    <Button
                      variant="outline"
                      size="icon"
                      onClick={handleFetchTerminals}
                      disabled={isLoadingTerminals}
                      aria-label={t('refreshTerminals')}
                      title={t('fetchTerminalsFromApi')}
                    >
                      {isLoadingTerminals ? (
                        <Loader2 className="sp-h-4 sp-w-4 sp-animate-spin" />
                      ) : (
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
                      )}
                    </Button>
                  </div>
                </div>
              </div>
            )}

            <div className="sp-flex sp-flex-col sp-gap-2">
              <Label htmlFor="merchant-emails">{t('merchantEmails')}</Label>
              <Input
                id="merchant-emails"
                type="text"
                placeholder={t('enterMerchantEmails')}
                value={merchantEmails}
                onChange={(e) => setField('merchantEmails', e.target.value)}
              />
              <p className="sp-text-xs sp-text-muted-foreground">
                {t('separateEmails')}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Saferpay Fields Configuration */}
      <Card>
        <CardHeader>
          <CardTitle className="sp-text-base sp-font-semibold">{t('saferpayFields')}</CardTitle>
          <CardDescription>
            {t('saferpayFieldsDescription')}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="sp-grid sp-gap-5">
            <div className="sp-flex sp-items-center sp-gap-3 sp-rounded-lg sp-bg-[#294e57]/5 sp-border sp-border-[#294e57]/30 sp-border-l-[3px] sp-border-l-[#294e57] sp-px-4 sp-py-3 sp-text-sm sp-text-foreground">
              <Info className="sp-h-4 sp-w-4 sp-shrink-0 sp-text-[#294e57]" />
              <p className="sp-mb-0">
                {t('fieldAccessTokenInfo')}{' '}
                <span className="sp-font-medium">{t('fieldAccessTokenPath')}</span>.
              </p>
            </div>

            <div className="sp-grid sp-gap-5 md:sp-grid-cols-2">
              <div className="sp-flex sp-flex-col sp-gap-2">
                <Label htmlFor="field-access-token">{t('fieldAccessToken')}</Label>
                <Input
                  id="field-access-token"
                  type="text"
                  placeholder={t('enterFieldAccessToken')}
                  value={fieldAccessToken}
                  onChange={(e) => setField('fieldAccessToken', e.target.value)}
                />
              </div>
              <div className="sp-flex sp-flex-col sp-gap-2">
                <Label htmlFor="field-js-url">{t('fieldJsUrl')}</Label>
                <Input
                  id="field-js-url"
                  type="url"
                  placeholder="https://www.saferpay.com/Fields/lib/1/"
                  value={fieldJsUrl}
                  onChange={(e) => setField('fieldJsUrl', e.target.value)}
                />
              </div>
            </div>

            <div className="sp-flex sp-items-center sp-justify-between sp-rounded-lg sp-border sp-bg-secondary/50 sp-px-4 sp-py-3">
              <div className="sp-flex sp-flex-col sp-gap-0.5">
                <Label htmlFor="business-license" className="sp-font-medium sp-cursor-pointer">
                  {t('businessLicense')}
                </Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  {t('businessLicenseDescription')}
                </p>
              </div>
              <Switch
                id="business-license"
                checked={hasBusinessLicense}
                onCheckedChange={(checked) => setField('businessLicense', checked)}
              />
            </div>
          </div>
        </CardContent>
      </Card>

      <div className="sp-flex sp-justify-end">
        <Button className="sp-min-w-[120px]" onClick={saveCredentials} disabled={saving}>
          {saving ? <Loader2 className="sp-h-4 sp-w-4 sp-animate-spin" /> : t('saveChanges')}
        </Button>
      </div>
    </div>
  )
}
