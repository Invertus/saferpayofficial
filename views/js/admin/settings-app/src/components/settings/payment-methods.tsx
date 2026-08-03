import { useState, useCallback, useMemo, useEffect } from 'react'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Switch } from '@/components/ui/switch'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { Checkbox } from '@/components/ui/checkbox'
import { Wallet, ChevronDown, Search, Loader2 } from 'lucide-react'
import { Input } from '@/components/ui/input'
import { useSettings } from '@/context/settings-context'
import { toast } from '@/hooks/use-toast'
import { t } from '@/utils/translations'

// Module-level so tab switches (which remount the component) don't re-toast.
let fetchFailedToastShown = false

function MultiSelect({
  options,
  selected,
  onChange,
  placeholder,
  label,
}: {
  options: Array<{ id: number; name: string }>
  selected: number[]
  onChange: (values: number[]) => void
  placeholder: string
  label: string
}) {
  const [search, setSearch] = useState('')

  const filteredOptions = useMemo(
    () => options.filter((opt) => opt.name.toLowerCase().includes(search.toLowerCase())),
    [options, search],
  )

  const ALL_VALUE = 0

  const validSelected = useMemo(
    () => selected.filter((s) => s !== ALL_VALUE && options.some((o) => o.id === s)),
    [selected, options],
  )

  const isAll = selected.includes(ALL_VALUE) || validSelected.length === 0

  const toggle = useCallback(
    (value: number) => {
      if (value === ALL_VALUE) {
        onChange([ALL_VALUE])
        return
      }
      const base = isAll ? [] : validSelected
      const next = base.includes(value)
        ? base.filter((s) => s !== value)
        : [...base, value]
      onChange(next.length === 0 ? [ALL_VALUE] : next)
    },
    [validSelected, isAll, onChange],
  )

  return (
    <Popover>
      <PopoverTrigger asChild>
        <button
          type="button"
          className="sp-flex sp-min-h-[36px] sp-w-full sp-items-center sp-justify-between sp-rounded-md sp-border sp-border-border sp-bg-card sp-px-3 sp-py-1.5 sp-text-left sp-text-sm sp-transition-colors hover:sp-bg-secondary/50 focus-visible:sp-outline-none focus-visible:sp-ring-2 focus-visible:sp-ring-ring"
          aria-label={isAll ? `${label}: ${placeholder.replace(/^Select\s+/i, 'All ')}` : validSelected.length === 0 ? `${label}: ${placeholder}` : `${label}: ${validSelected.length} ${t('selected')}`}
        >
          {isAll ? (
            <Badge variant="secondary" className="sp-text-xs sp-font-normal">
              {placeholder.replace(/^Select\s+/i, 'All ')}
            </Badge>
          ) : validSelected.length === 0 ? (
            <span className="sp-text-muted-foreground">{placeholder}</span>
          ) : (
            <span className="sp-flex sp-flex-wrap sp-gap-1">
              {validSelected.length <= 2 ? (
                validSelected.map((s) => {
                  const opt = options.find((o) => o.id === s)
                  return (
                    <Badge key={s} variant="secondary" className="sp-text-xs sp-font-normal">
                      {opt?.name || s}
                    </Badge>
                  )
                })
              ) : (
                <Badge variant="secondary" className="sp-text-xs sp-font-normal">
                  {validSelected.length} {t('selected')}
                </Badge>
              )}
            </span>
          )}
          <ChevronDown className="sp-ml-2 sp-h-3.5 sp-w-3.5 sp-shrink-0 sp-text-muted-foreground" />
        </button>
      </PopoverTrigger>
      <PopoverContent className="sp-w-[200px] sp-p-0" align="start">
        <div className="sp-border-b sp-p-2">
          <div className="sp-relative">
            <Search className="sp-absolute sp-left-2 sp-top-1/2 sp-h-3.5 sp-w-3.5 sp--translate-y-1/2 sp-text-muted-foreground" />
            <Input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder={t('search')}
              className="sp-h-8 sp-pl-7 sp-text-xs"
            />
          </div>
        </div>
        <div className="sp-max-h-[200px] sp-overflow-y-auto sp-p-1">
          {filteredOptions.map((option) => (
            <label
              key={option.id}
              className="sp-flex sp-cursor-pointer sp-items-center sp-gap-2 sp-rounded-sm sp-px-2 sp-py-1.5 sp-text-sm hover:sp-bg-accent"
            >
              <Checkbox
                checked={option.id === ALL_VALUE ? isAll : validSelected.includes(option.id)}
                onCheckedChange={() => toggle(option.id)}
              />
              <span className="sp-text-sm">{option.name}</span>
            </label>
          ))}
          {filteredOptions.length === 0 && (
            <p className="sp-px-2 sp-py-4 sp-text-center sp-text-xs sp-text-muted-foreground">
              {t('noResultsFound')}
            </p>
          )}
        </div>
        {validSelected.length > 0 && (
          <div className="sp-border-t sp-p-2">
            <button
              type="button"
              onClick={() => onChange([ALL_VALUE])}
              className="sp-w-full sp-rounded-sm sp-px-2 sp-py-1 sp-text-xs sp-text-muted-foreground hover:sp-text-foreground sp-transition-colors"
            >
              {t('clearAll')}
            </button>
          </div>
        )}
      </PopoverContent>
    </Popover>
  )
}

export function PaymentMethods() {
  const { paymentMethods, updatePaymentMethod, savePaymentMethods, savingSections, settings, refreshPaymentMethods } = useSettings()
  const saving = savingSections.has('paymentMethods')

  useEffect(() => {
    if (paymentMethods.length === 0) {
      refreshPaymentMethods()
    }
  }, [paymentMethods.length, refreshPaymentMethods])

  // The account check runs while the page bootstraps, so a failure arrives via
  // the initial settings data rather than a refresh response.
  useEffect(() => {
    // Clearing the latch keeps a later, genuine failure from being swallowed.
    if (!settings.paymentMethodsFetchFailed) {
      fetchFailedToastShown = false

      return
    }

    if (!fetchFailedToastShown) {
      fetchFailedToastShown = true
      toast({ title: t('paymentMethodsUnreachable'), variant: 'warning' })
    }
  }, [settings.paymentMethodsFetchFailed])

  const enabledCount = paymentMethods.filter((m) => m.enabled).length

  return (
    <div className="sp-flex sp-flex-col sp-gap-6">
      <Card>
        <CardHeader>
          <div className="sp-flex sp-items-center sp-justify-between">
            <div className="sp-flex sp-items-center sp-gap-2">
              <Wallet className="sp-h-5 sp-w-5 sp-text-muted-foreground" />
              <div className="sp-flex sp-flex-col sp-gap-1.5">
                <CardTitle className="sp-text-base sp-font-semibold">{t('paymentMethods')}</CardTitle>
                <CardDescription>
                  {t('paymentMethodsDescription')}
                </CardDescription>
              </div>
            </div>
            {enabledCount > 0 && (
              <Badge variant="secondary" className="sp-bg-primary/10 sp-text-primary sp-border-0">
                {enabledCount} {t('active')}
              </Badge>
            )}
          </div>
        </CardHeader>
        <CardContent>
          {/* Header row */}
          <div className="sp-mb-3 sp-hidden sp-items-center sp-gap-3 sp-rounded-lg sp-bg-muted sp-px-4 sp-py-2.5 sp-text-xs sp-font-medium sp-text-muted-foreground md:sp-grid md:sp-grid-cols-[1fr_80px_80px_100px_1fr_1fr]">
            <span>{t('paymentMethod')}</span>
            <span className="sp-text-center">{t('enabled')}</span>
            <span className="sp-text-center">{t('logos')}</span>
            <span className="sp-text-center">{t('customForm')}</span>
            <span>{t('countries')}</span>
            <span>{t('currencies')}</span>
          </div>

          {/* Payment method rows */}
          <div className="sp-flex sp-flex-col sp-gap-2">
            {paymentMethods.map((method) => (
              <div
                key={method.name}
                className={`sp-group sp-rounded-lg sp-border sp-px-4 sp-py-3 sp-transition-colors ${
                  method.enabled
                    ? 'sp-bg-card sp-border-border'
                    : 'sp-bg-card sp-border-border sp-opacity-75 hover:sp-opacity-100'
                }`}
              >
                {/* Desktop layout */}
                <div className="sp-hidden sp-items-center sp-gap-3 md:sp-grid md:sp-grid-cols-[1fr_80px_80px_100px_1fr_1fr]">
                  <div className="sp-flex sp-items-center sp-gap-3">
                    <span className="sp-text-sm sp-font-medium">{method.displayName}</span>
                  </div>

                  <div className="sp-flex sp-justify-center">
                    <Switch
                      checked={method.enabled}
                      onCheckedChange={(checked) => updatePaymentMethod(method.name, { enabled: checked })}
                      aria-label={`${t('enable')} ${method.displayName}`}
                    />
                  </div>

                  <div className="sp-flex sp-justify-center">
                    <Switch
                      checked={method.showLogos}
                      onCheckedChange={(checked) => updatePaymentMethod(method.name, { showLogos: checked })}
                      aria-label={`${t('logos')} ${method.displayName}`}
                    />
                  </div>

                  <div className="sp-flex sp-justify-center">
                    {method.hasCustomForm ? (
                      <Switch
                        checked={method.showCustomForm}
                        onCheckedChange={(checked) => updatePaymentMethod(method.name, { showCustomForm: checked })}
                        aria-label={`${t('customForm')} ${method.displayName}`}
                      />
                    ) : (
                      <span className="sp-text-xs sp-text-muted-foreground">--</span>
                    )}
                  </div>

                  <MultiSelect
                    options={settings.countries}
                    selected={method.countries}
                    onChange={(countries) => updatePaymentMethod(method.name, { countries })}
                    placeholder={t('selectCountries')}
                    label={`${t('countries')} ${method.displayName}`}
                  />

                  <MultiSelect
                    options={settings.currencies.map((c) => ({ id: c.id, name: c.iso_code }))}
                    selected={method.currencies}
                    onChange={(currencies) => updatePaymentMethod(method.name, { currencies })}
                    placeholder={t('selectCurrencies')}
                    label={`${t('currencies')} ${method.displayName}`}
                  />
                </div>

                {/* Mobile layout */}
                <div className="sp-flex sp-flex-col sp-gap-3 md:sp-hidden">
                  <div className="sp-flex sp-items-center sp-justify-between">
                    <span className="sp-text-sm sp-font-medium">{method.displayName}</span>
                    <Switch
                      checked={method.enabled}
                      onCheckedChange={(checked) => updatePaymentMethod(method.name, { enabled: checked })}
                      aria-label={`${t('enable')} ${method.displayName}`}
                    />
                  </div>

                  <div className="sp-grid sp-grid-cols-2 sp-gap-2">
                    <div className="sp-flex sp-items-center sp-justify-between sp-rounded-md sp-border sp-bg-secondary/50 sp-px-3 sp-py-2">
                      <Label className="sp-text-xs sp-text-muted-foreground">{t('logos')}</Label>
                      <Switch
                        checked={method.showLogos}
                        onCheckedChange={(checked) => updatePaymentMethod(method.name, { showLogos: checked })}
                        aria-label={`${t('logos')} ${method.displayName}`}
                      />
                    </div>
                    {method.hasCustomForm && (
                      <div className="sp-flex sp-items-center sp-justify-between sp-rounded-md sp-border sp-bg-secondary/50 sp-px-3 sp-py-2">
                        <Label className="sp-text-xs sp-text-muted-foreground">{t('customForm')}</Label>
                        <Switch
                          checked={method.showCustomForm}
                          onCheckedChange={(checked) => updatePaymentMethod(method.name, { showCustomForm: checked })}
                          aria-label={`${t('customForm')} ${method.displayName}`}
                        />
                      </div>
                    )}
                  </div>

                  <div className="sp-grid sp-grid-cols-2 sp-gap-2">
                    <div className="sp-flex sp-flex-col sp-gap-1">
                      <Label className="sp-text-xs sp-text-muted-foreground">{t('countries')}</Label>
                      <MultiSelect
                        options={settings.countries}
                        selected={method.countries}
                        onChange={(countries) => updatePaymentMethod(method.name, { countries })}
                        placeholder={t('select')}
                        label={`${t('countries')} ${method.displayName}`}
                      />
                    </div>
                    <div className="sp-flex sp-flex-col sp-gap-1">
                      <Label className="sp-text-xs sp-text-muted-foreground">{t('currencies')}</Label>
                      <MultiSelect
                        options={settings.currencies.map((c) => ({ id: c.id, name: c.iso_code }))}
                        selected={method.currencies}
                        onChange={(currencies) => updatePaymentMethod(method.name, { currencies })}
                        placeholder={t('select')}
                        label={`${t('currencies')} ${method.displayName}`}
                      />
                    </div>
                  </div>
                </div>
              </div>
            ))}

            {paymentMethods.length === 0 && (
              <div className="sp-rounded-lg sp-border sp-border-dashed sp-p-8 sp-text-center sp-text-sm sp-text-muted-foreground">
                {t('noPaymentMethods')}
              </div>
            )}
          </div>
        </CardContent>
      </Card>

      {paymentMethods.length > 0 && (
        <div className="sp-flex sp-justify-end">
          <Button className="sp-min-w-[120px]" onClick={savePaymentMethods} disabled={saving} aria-label={saving ? t('saving') : undefined}>
            {saving ? <Loader2 className="sp-h-4 sp-w-4 sp-animate-spin" /> : t('saveChanges')}
          </Button>
        </div>
      )}
    </div>
  )
}
