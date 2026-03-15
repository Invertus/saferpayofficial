import { useState, useEffect, useRef } from 'react'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { AlertCircle, Eye, EyeOff, Key, Shield, Loader2, Info, CheckCircle2, Wand2, XCircle } from 'lucide-react'
import { useSettings } from '@/context/settings-context'
import { t } from '@/utils/translations'
import type { TerminalOption } from '@/types'

type CredentialStatus = 'idle' | 'checking' | 'valid' | 'invalid'

export function ApiCredentials() {
  const { settings, updateSettings, saveCredentials, fetchTerminals, savingSections } = useSettings()
  const saving = savingSections.has('credentials')
  const [showApiPassword, setShowApiPassword] = useState(false)
  const [terminals, setTerminals] = useState<TerminalOption[]>([])
  const [credentialStatus, setCredentialStatus] = useState<CredentialStatus>('idle')
  const [credentialError, setCredentialError] = useState('')
  const debounceTimer = useRef<ReturnType<typeof setTimeout> | null>(null)

  const isTest = settings.testMode
  const prefix = isTest ? 'test' : 'live'
  const envLabel = isTest ? t('test') : t('live')
  const environment = isTest ? 'test' : 'live'

  const username = isTest ? settings.testUsername : settings.liveUsername
  const password = isTest ? settings.testPassword : settings.livePassword
  const terminalId = isTest ? settings.testTerminalId : settings.liveTerminalId
  const merchantEmails = isTest ? settings.testMerchantEmails : settings.liveMerchantEmails
  const fieldAccessToken = isTest ? settings.testFieldAccessToken : settings.liveFieldAccessToken
  const fieldJsUrl = isTest ? settings.testFieldJsUrl : settings.liveFieldJsUrl

  const hasCredentials = username.length > 0 && password.length > 0

  const setField = (field: string, value: string | boolean) => {
    updateSettings({ [`${prefix}${field.charAt(0).toUpperCase() + field.slice(1)}`]: value } as Record<string, string | boolean>)
  }

  // Debounced credential check: when username+password are filled, validate and fetch terminals
  useEffect(() => {
    if (debounceTimer.current) clearTimeout(debounceTimer.current)

    if (!hasCredentials) {
      setCredentialStatus('idle')
      setCredentialError('')
      setTerminals([])
      return
    }

    debounceTimer.current = setTimeout(async () => {
      setCredentialStatus('checking')
      setCredentialError('')
      try {
        const result = await fetchTerminals(environment, username, password)
        setTerminals(result)
        setCredentialStatus('valid')
      } catch {
        setTerminals([])
        setCredentialStatus('invalid')
        setCredentialError(t('invalidCredentials'))
      }
    }, 1000)

    return () => {
      if (debounceTimer.current) clearTimeout(debounceTimer.current)
    }
  }, [username, password, environment])

  return (
    <div className="sp-flex sp-flex-col sp-gap-6">
      {/* Environment Selector */}
      <Card>
        <CardHeader>
          <div className="sp-flex sp-items-center sp-justify-between">
            <div className="sp-flex sp-flex-col sp-gap-1.5">
              <CardTitle className="sp-text-base sp-font-semibold">{t('environment')}</CardTitle>
              <CardDescription>{t('envDescription')}</CardDescription>
            </div>
            <Select
              value={environment}
              onValueChange={(val: string) => {
                updateSettings({ testMode: val === 'test' })
                setTerminals([])
                setCredentialStatus('idle')
                setCredentialError('')
              }}
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
            <span>{isTest ? t('testModeWarning') : t('liveModeWarning')}</span>
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
                {envLabel} {t('apiCredentials')}
              </CardTitle>
              <CardDescription>
                {t('enterSaferpayCredentials', envLabel.toLowerCase())}
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="sp-grid sp-gap-5">
            {/* Username & Password */}
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

            {/* Credential status feedback */}
            {credentialStatus === 'checking' && (
              <div className="sp-flex sp-items-center sp-gap-2 sp-text-sm sp-text-muted-foreground">
                <Loader2 className="sp-h-4 sp-w-4 sp-animate-spin" />
                {t('validatingCredentials')}
              </div>
            )}
            {credentialStatus === 'valid' && (
              <div className="sp-flex sp-items-center sp-gap-2 sp-text-sm sp-text-emerald-600">
                <CheckCircle2 className="sp-h-4 sp-w-4" />
                {t('credentialsValid')}
              </div>
            )}
            {credentialStatus === 'invalid' && (
              <div className="sp-flex sp-items-center sp-gap-2 sp-text-sm sp-text-red-600">
                <XCircle className="sp-h-4 sp-w-4" />
                {credentialError}
              </div>
            )}

            {/* Terminal ID */}
            <div className="sp-flex sp-flex-col sp-gap-2">
              <Label htmlFor="terminal-id">{t('terminalId')}</Label>
              <Select
                value={terminalId}
                onValueChange={(val) => setField('terminalId', val)}
                disabled={terminals.length === 0 && !terminalId}
              >
                <SelectTrigger id="terminal-id">
                  <SelectValue placeholder={hasCredentials ? t('selectTerminal') : t('enterCredentialsFirst')} />
                </SelectTrigger>
                <SelectContent>
                  {terminals.map((terminal) => (
                    <SelectItem key={terminal.id} value={terminal.id}>{terminal.name}</SelectItem>
                  ))}
                  {terminalId && !terminals.find((term) => term.id === terminalId) && (
                    <SelectItem value={terminalId}>{terminalId}</SelectItem>
                  )}
                </SelectContent>
              </Select>
              {!hasCredentials && (
                <p className="sp-text-xs sp-text-muted-foreground">
                  {t('enterCredentialsToLoadTerminals')}
                </p>
              )}
            </div>

            {/* Merchant Emails */}
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
          <CardDescription>{t('saferpayFieldsDescription')}</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="sp-grid sp-gap-5">
            {settings.hasBusinessLicense ? (
              <div className="sp-flex sp-items-center sp-gap-3 sp-rounded-lg sp-bg-emerald-50 sp-border sp-border-emerald-200 sp-px-4 sp-py-3">
                <CheckCircle2 className="sp-h-5 sp-w-5 sp-shrink-0 sp-text-emerald-600" />
                <div className="sp-flex sp-flex-col sp-gap-0.5">
                  <p className="sp-mb-0 sp-text-sm sp-font-medium sp-text-emerald-800">
                    {t('saferpayFieldsIncluded')}
                  </p>
                  <p className="sp-mb-0 sp-text-xs sp-text-emerald-600">
                    {t('saferpayFieldsIncludedDescription')}
                  </p>
                </div>
              </div>
            ) : (
              <div className="sp-flex sp-items-center sp-gap-3 sp-rounded-lg sp-bg-amber-50 sp-border sp-border-amber-200 sp-px-4 sp-py-3">
                <AlertCircle className="sp-h-5 sp-w-5 sp-shrink-0 sp-text-amber-600" />
                <div className="sp-flex sp-flex-col sp-gap-0.5">
                  <p className="sp-mb-0 sp-text-sm sp-font-medium sp-text-amber-800">
                    {t('saferpayFieldsNotIncluded')}
                  </p>
                  <p className="sp-mb-0 sp-text-xs sp-text-amber-600">
                    {t('saferpayFieldsNotIncludedDescription')}
                  </p>
                </div>
              </div>
            )}

            <div className="sp-flex sp-items-center sp-gap-3 sp-rounded-lg sp-bg-[#294e57]/5 sp-border sp-border-[#294e57]/30 sp-border-l-[3px] sp-border-l-[#294e57] sp-px-4 sp-py-3 sp-text-sm sp-text-foreground">
              <Info className="sp-h-4 sp-w-4 sp-shrink-0 sp-text-[#294e57]" />
              <p className="sp-mb-0">
                {t('fieldAccessTokenInfo')}{' '}
                <span className="sp-font-medium">{t('fieldAccessTokenPath')}</span>.{' '}
                <a href="https://docs.saferpay.com/home/integration-guide/licences-and-interfaces/saferpay-fields" target="_blank" rel="noopener noreferrer" className="sp-text-[#294e57] sp-underline hover:sp-no-underline">{t('moreInformation')}</a>
              </p>
            </div>

            <div className="sp-grid sp-gap-5 md:sp-grid-cols-2">
              <div className="sp-flex sp-flex-col sp-gap-2">
                <Label htmlFor="field-access-token">{t('fieldAccessToken')}</Label>
                <div className="sp-flex sp-gap-2">
                  <Input
                    id="field-access-token"
                    type="text"
                    placeholder={t('enterFieldAccessToken')}
                    value={fieldAccessToken}
                    onChange={(e) => setField('fieldAccessToken', e.target.value)}
                    disabled={!settings.hasBusinessLicense}
                    className="sp-flex-1"
                  />
                  <Button
                    variant="outline"
                    disabled={!settings.hasBusinessLicense || !hasCredentials}
                    className="sp-shrink-0"
                  >
                    <Wand2 className="sp-h-4 sp-w-4 sp-mr-1.5" />
                    {t('generate')}
                  </Button>
                </div>
                <p className="sp-text-xs sp-text-muted-foreground">
                  {t('enterCredentialsToGenerateToken')}
                </p>
              </div>
              <div className="sp-flex sp-flex-col sp-gap-2">
                <Label htmlFor="field-js-url">{t('fieldJsUrl')}</Label>
                <Input
                  id="field-js-url"
                  type="url"
                  placeholder="https://www.saferpay.com/Fields/lib/1/"
                  value={fieldJsUrl}
                  onChange={(e) => setField('fieldJsUrl', e.target.value)}
                  disabled={!settings.hasBusinessLicense}
                />
                <a href="https://docs.saferpay.com/home/integration-guide/licences-and-interfaces/saferpay-fields#javascript-library-url" target="_blank" rel="noopener noreferrer" className="sp-text-xs sp-text-[#294e57] sp-underline hover:sp-no-underline">
                  {t('findLibraryUrlHere')}
                </a>
              </div>
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
