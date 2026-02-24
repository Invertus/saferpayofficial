import { useState, useEffect } from 'react'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Switch } from '@/components/ui/switch'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { AlertCircle, Eye, EyeOff, Key, Shield, Loader2, Info } from 'lucide-react'
import { useSettings } from '@/context/settings-context'
import type { TerminalOption } from '@/types'

export function ApiCredentials() {
  const { settings, updateSettings, saveCredentials, fetchTerminals, saving } = useSettings()
  const [showApiPassword, setShowApiPassword] = useState(false)
  const [isLoadingTerminals, setIsLoadingTerminals] = useState(false)
  const [terminals, setTerminals] = useState<TerminalOption[]>([])

  const environment = settings.testMode ? 'test' : 'live'
  const isTest = settings.testMode

  const username = isTest ? settings.testUsername : settings.liveUsername
  const password = isTest ? settings.testPassword : settings.livePassword
  const customerId = isTest ? settings.testCustomerId : settings.liveCustomerId
  const terminalId = isTest ? settings.testTerminalId : settings.liveTerminalId
  const merchantEmails = isTest ? settings.testMerchantEmails : settings.liveMerchantEmails
  const fieldAccessToken = isTest ? settings.testFieldAccessToken : settings.liveFieldAccessToken
  const fieldJsUrl = isTest ? settings.testFieldJsUrl : settings.liveFieldJsUrl
  const hasBusinessLicense = isTest ? settings.testBusinessLicense : settings.liveBusinessLicense

  const prefix = isTest ? 'test' : 'live'
  const setField = (field: string, value: string | boolean) => {
    updateSettings({ [`${prefix}${field.charAt(0).toUpperCase() + field.slice(1)}`]: value } as Record<string, string | boolean>)
  }

  const envLabel = isTest ? 'Test' : 'Live'
  const hasCredentials = username.length > 0 && password.length > 0

  const handleFetchTerminals = async () => {
    if (!hasCredentials) return
    setIsLoadingTerminals(true)
    try {
      const result = await fetchTerminals(environment, username, password, customerId)
      setTerminals(result)
    } finally {
      setIsLoadingTerminals(false)
    }
  }

  useEffect(() => {
    if (hasCredentials && terminals.length === 0) {
      handleFetchTerminals()
    }
  }, [environment])

  return (
    <div className="sp-flex sp-flex-col sp-gap-6">
      {/* Environment Selector */}
      <Card>
        <CardHeader>
          <div className="sp-flex sp-items-center sp-justify-between">
            <div className="sp-flex sp-flex-col sp-gap-1.5">
              <CardTitle className="sp-text-base sp-font-semibold">Environment</CardTitle>
              <CardDescription>
                Select your active environment. Credentials are stored separately for each.
              </CardDescription>
            </div>
            <Select
              value={environment}
              onValueChange={(val: string) => updateSettings({ testMode: val === 'test' })}
            >
              <SelectTrigger className="sp-w-[180px]" aria-label="Select environment">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="test">
                  <span className="sp-flex sp-items-center sp-gap-2">
                    <span className="sp-h-2 sp-w-2 sp-rounded-full sp-bg-amber-500" />
                    Test Environment
                  </span>
                </SelectItem>
                <SelectItem value="live">
                  <span className="sp-flex sp-items-center sp-gap-2">
                    <span className="sp-h-2 sp-w-2 sp-rounded-full sp-bg-emerald-500" />
                    Live Environment
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
                ? 'You are currently in test mode. No real transactions will be processed.'
                : 'You are in live mode. Real transactions will be processed.'}
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
                {envLabel} API Credentials
              </CardTitle>
              <CardDescription>
                Enter your Saferpay {envLabel.toLowerCase()} environment API credentials.
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="sp-grid sp-gap-5">
            <div className="sp-grid sp-gap-5 md:sp-grid-cols-2">
              <div className="sp-flex sp-flex-col sp-gap-2">
                <Label htmlFor="api-username">JSON API Username</Label>
                <Input
                  id="api-username"
                  type="text"
                  placeholder={`Enter ${envLabel.toLowerCase()} API username`}
                  value={username}
                  onChange={(e) => setField('username', e.target.value)}
                />
              </div>
              <div className="sp-flex sp-flex-col sp-gap-2">
                <Label htmlFor="api-password">JSON API Password</Label>
                <div className="sp-relative">
                  <Input
                    id="api-password"
                    type={showApiPassword ? 'text' : 'password'}
                    placeholder={`Enter ${envLabel.toLowerCase()} API password`}
                    value={password}
                    onChange={(e) => setField('password', e.target.value)}
                    className="sp-pr-10"
                  />
                  <button
                    type="button"
                    onClick={() => setShowApiPassword(!showApiPassword)}
                    className="sp-absolute sp-right-3 sp-top-1/2 sp--translate-y-1/2 sp-text-muted-foreground hover:sp-text-foreground sp-transition-colors"
                    aria-label={showApiPassword ? 'Hide password' : 'Show password'}
                  >
                    {showApiPassword ? <EyeOff className="sp-h-4 sp-w-4" /> : <Eye className="sp-h-4 sp-w-4" />}
                  </button>
                </div>
              </div>
            </div>

            <div className="sp-grid sp-gap-5 md:sp-grid-cols-2">
              <div className="sp-flex sp-flex-col sp-gap-2">
                <Label htmlFor="customer-id">Customer ID</Label>
                <Input
                  id="customer-id"
                  type="text"
                  placeholder="Enter customer ID"
                  value={customerId}
                  onChange={(e) => setField('customerId', e.target.value)}
                />
              </div>
              <div className="sp-flex sp-flex-col sp-gap-2">
                <Label htmlFor="terminal-id">Terminal ID</Label>
                <div className="sp-flex sp-gap-2">
                  <Select
                    value={terminalId}
                    onValueChange={(val) => setField('terminalId', val)}
                    disabled={!hasCredentials && terminals.length === 0}
                  >
                    <SelectTrigger id="terminal-id" className="sp-flex-1">
                      <SelectValue placeholder={hasCredentials ? 'Select a terminal' : 'Enter credentials first'} />
                    </SelectTrigger>
                    <SelectContent>
                      {terminals.map((t) => (
                        <SelectItem key={t.id} value={t.id}>{t.name}</SelectItem>
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
                    disabled={!hasCredentials || isLoadingTerminals}
                    aria-label="Refresh terminals"
                    title="Fetch terminals from API"
                  >
                    {isLoadingTerminals ? (
                      <Loader2 className="sp-h-4 sp-w-4 sp-animate-spin" />
                    ) : (
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
                    )}
                  </Button>
                </div>
                {!hasCredentials && (
                  <p className="sp-text-xs sp-text-muted-foreground">
                    Enter your API username and password first to load available terminals.
                  </p>
                )}
              </div>
            </div>

            <div className="sp-flex sp-flex-col sp-gap-2">
              <Label htmlFor="merchant-emails">Merchant Emails</Label>
              <Input
                id="merchant-emails"
                type="text"
                placeholder="Enter merchant email addresses (comma-separated)"
                value={merchantEmails}
                onChange={(e) => setField('merchantEmails', e.target.value)}
              />
              <p className="sp-text-xs sp-text-muted-foreground">
                Separate multiple email addresses with commas.
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Saferpay Fields Configuration */}
      <Card>
        <CardHeader>
          <CardTitle className="sp-text-base sp-font-semibold">Saferpay Fields</CardTitle>
          <CardDescription>
            Configure Saferpay Fields for inline payment form integration.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="sp-grid sp-gap-5">
            <div className="sp-flex sp-items-center sp-gap-3 sp-rounded-lg sp-bg-[#294e57]/5 sp-border sp-border-[#294e57]/30 sp-border-l-[3px] sp-border-l-[#294e57] sp-px-4 sp-py-3 sp-text-sm sp-text-foreground">
              <Info className="sp-h-4 sp-w-4 sp-shrink-0 sp-text-[#294e57]" />
              <p className="sp-mb-0">
                Saferpay Field Access Token can be found in Saferpay Backoffice, navigate to{' '}
                <span className="sp-font-medium">{'Settings > Saferpay Fields Access Tokens'}</span>.
              </p>
            </div>

            <div className="sp-grid sp-gap-5 md:sp-grid-cols-2">
              <div className="sp-flex sp-flex-col sp-gap-2">
                <Label htmlFor="field-access-token">Field Access Token</Label>
                <Input
                  id="field-access-token"
                  type="text"
                  placeholder="Enter field access token"
                  value={fieldAccessToken}
                  onChange={(e) => setField('fieldAccessToken', e.target.value)}
                />
              </div>
              <div className="sp-flex sp-flex-col sp-gap-2">
                <Label htmlFor="field-js-url">Field Javascript Library URL</Label>
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
                  I have Business license
                </Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  Enable if you have a Saferpay Business license for advanced features.
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
          {saving ? <Loader2 className="sp-h-4 sp-w-4 sp-animate-spin" /> : 'Save Changes'}
        </Button>
      </div>
    </div>
  )
}
