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

  const toggle = useCallback(
    (value: number) => {
      onChange(
        selected.includes(value)
          ? selected.filter((s) => s !== value)
          : [...selected, value],
      )
    },
    [selected, onChange],
  )

  return (
    <Popover>
      <PopoverTrigger asChild>
        <button
          type="button"
          className="sp-flex sp-min-h-[36px] sp-w-full sp-items-center sp-justify-between sp-rounded-md sp-border sp-border-border sp-bg-card sp-px-3 sp-py-1.5 sp-text-left sp-text-sm sp-transition-colors hover:sp-bg-secondary/50 focus-visible:sp-outline-none focus-visible:sp-ring-2 focus-visible:sp-ring-ring"
          aria-label={label}
        >
          {selected.length === 0 ? (
            <span className="sp-text-muted-foreground">{placeholder}</span>
          ) : (
            <span className="sp-flex sp-flex-wrap sp-gap-1">
              {selected.length <= 2 ? (
                selected.map((s) => {
                  const opt = options.find((o) => o.id === s)
                  return (
                    <Badge key={s} variant="secondary" className="sp-text-xs sp-font-normal">
                      {opt?.name || s}
                    </Badge>
                  )
                })
              ) : (
                <Badge variant="secondary" className="sp-text-xs sp-font-normal">
                  {selected.length} selected
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
              placeholder="Search..."
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
                checked={selected.includes(option.id)}
                onCheckedChange={() => toggle(option.id)}
              />
              <span className="sp-text-sm">{option.name}</span>
            </label>
          ))}
          {filteredOptions.length === 0 && (
            <p className="sp-px-2 sp-py-4 sp-text-center sp-text-xs sp-text-muted-foreground">
              No results found.
            </p>
          )}
        </div>
        {selected.length > 0 && (
          <div className="sp-border-t sp-p-2">
            <button
              type="button"
              onClick={() => onChange([])}
              className="sp-w-full sp-rounded-sm sp-px-2 sp-py-1 sp-text-xs sp-text-muted-foreground hover:sp-text-foreground sp-transition-colors"
            >
              Clear all
            </button>
          </div>
        )}
      </PopoverContent>
    </Popover>
  )
}

export function PaymentMethods() {
  const { paymentMethods, updatePaymentMethod, savePaymentMethods, saving, settings, refreshPaymentMethods } = useSettings()

  useEffect(() => {
    if (paymentMethods.length === 0) {
      refreshPaymentMethods()
    }
  }, [])

  const enabledCount = paymentMethods.filter((m) => m.enabled).length

  return (
    <div className="sp-flex sp-flex-col sp-gap-6">
      <Card>
        <CardHeader>
          <div className="sp-flex sp-items-center sp-justify-between">
            <div className="sp-flex sp-items-center sp-gap-2">
              <Wallet className="sp-h-5 sp-w-5 sp-text-muted-foreground" />
              <div className="sp-flex sp-flex-col sp-gap-1.5">
                <CardTitle className="sp-text-base sp-font-semibold">Payment Methods</CardTitle>
                <CardDescription>
                  Enable and configure available payment methods for your checkout.
                </CardDescription>
              </div>
            </div>
            {enabledCount > 0 && (
              <Badge variant="secondary" className="sp-bg-primary/10 sp-text-primary sp-border-0">
                {enabledCount} active
              </Badge>
            )}
          </div>
        </CardHeader>
        <CardContent>
          {/* Header row */}
          <div className="sp-mb-3 sp-hidden sp-items-center sp-gap-3 sp-rounded-lg sp-bg-muted sp-px-4 sp-py-2.5 sp-text-xs sp-font-medium sp-text-muted-foreground md:sp-grid md:sp-grid-cols-[1fr_80px_80px_100px_1fr_1fr]">
            <span>Payment method</span>
            <span className="sp-text-center">Enabled</span>
            <span className="sp-text-center">Logos</span>
            <span className="sp-text-center">Custom form</span>
            <span>Countries</span>
            <span>Currencies</span>
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
                      aria-label={`Enable ${method.displayName}`}
                    />
                  </div>

                  <div className="sp-flex sp-justify-center">
                    <Switch
                      checked={method.showLogos}
                      onCheckedChange={(checked) => updatePaymentMethod(method.name, { showLogos: checked })}
                      aria-label={`Show logos for ${method.displayName}`}
                    />
                  </div>

                  <div className="sp-flex sp-justify-center">
                    {method.hasCustomForm ? (
                      <Switch
                        checked={method.showCustomForm}
                        onCheckedChange={(checked) => updatePaymentMethod(method.name, { showCustomForm: checked })}
                        aria-label={`Show custom form for ${method.displayName}`}
                      />
                    ) : (
                      <span className="sp-text-xs sp-text-muted-foreground">--</span>
                    )}
                  </div>

                  <MultiSelect
                    options={settings.countries}
                    selected={method.countries}
                    onChange={(countries) => updatePaymentMethod(method.name, { countries })}
                    placeholder="Select countries"
                    label={`Countries for ${method.displayName}`}
                  />

                  <MultiSelect
                    options={settings.currencies.map((c) => ({ id: c.id, name: c.iso_code }))}
                    selected={method.currencies}
                    onChange={(currencies) => updatePaymentMethod(method.name, { currencies })}
                    placeholder="Select currencies"
                    label={`Currencies for ${method.displayName}`}
                  />
                </div>

                {/* Mobile layout */}
                <div className="sp-flex sp-flex-col sp-gap-3 md:sp-hidden">
                  <div className="sp-flex sp-items-center sp-justify-between">
                    <span className="sp-text-sm sp-font-medium">{method.displayName}</span>
                    <Switch
                      checked={method.enabled}
                      onCheckedChange={(checked) => updatePaymentMethod(method.name, { enabled: checked })}
                      aria-label={`Enable ${method.displayName}`}
                    />
                  </div>

                  <div className="sp-grid sp-grid-cols-2 sp-gap-2">
                    <div className="sp-flex sp-items-center sp-justify-between sp-rounded-md sp-border sp-bg-secondary/50 sp-px-3 sp-py-2">
                      <Label className="sp-text-xs sp-text-muted-foreground">Logos</Label>
                      <Switch
                        checked={method.showLogos}
                        onCheckedChange={(checked) => updatePaymentMethod(method.name, { showLogos: checked })}
                      />
                    </div>
                    {method.hasCustomForm && (
                      <div className="sp-flex sp-items-center sp-justify-between sp-rounded-md sp-border sp-bg-secondary/50 sp-px-3 sp-py-2">
                        <Label className="sp-text-xs sp-text-muted-foreground">Custom form</Label>
                        <Switch
                          checked={method.showCustomForm}
                          onCheckedChange={(checked) => updatePaymentMethod(method.name, { showCustomForm: checked })}
                        />
                      </div>
                    )}
                  </div>

                  <div className="sp-grid sp-grid-cols-2 sp-gap-2">
                    <div className="sp-flex sp-flex-col sp-gap-1">
                      <Label className="sp-text-xs sp-text-muted-foreground">Countries</Label>
                      <MultiSelect
                        options={settings.countries}
                        selected={method.countries}
                        onChange={(countries) => updatePaymentMethod(method.name, { countries })}
                        placeholder="Select"
                        label={`Countries for ${method.displayName}`}
                      />
                    </div>
                    <div className="sp-flex sp-flex-col sp-gap-1">
                      <Label className="sp-text-xs sp-text-muted-foreground">Currencies</Label>
                      <MultiSelect
                        options={settings.currencies.map((c) => ({ id: c.id, name: c.iso_code }))}
                        selected={method.currencies}
                        onChange={(currencies) => updatePaymentMethod(method.name, { currencies })}
                        placeholder="Select"
                        label={`Currencies for ${method.displayName}`}
                      />
                    </div>
                  </div>
                </div>
              </div>
            ))}

            {paymentMethods.length === 0 && (
              <div className="sp-rounded-lg sp-border sp-border-dashed sp-p-8 sp-text-center sp-text-sm sp-text-muted-foreground">
                No payment methods available. Please configure your API credentials first.
              </div>
            )}
          </div>
        </CardContent>
      </Card>

      <div className="sp-flex sp-justify-end">
        <Button className="sp-min-w-[120px]" onClick={savePaymentMethods} disabled={saving}>
          {saving ? <Loader2 className="sp-h-4 sp-w-4 sp-animate-spin" /> : 'Save Changes'}
        </Button>
      </div>
    </div>
  )
}
