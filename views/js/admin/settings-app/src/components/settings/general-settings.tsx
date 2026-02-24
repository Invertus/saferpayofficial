import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Switch } from '@/components/ui/switch'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Settings2, Paintbrush, ClipboardList, Loader2 } from 'lucide-react'
import { useSettings } from '@/context/settings-context'

export function GeneralSettings() {
  const { settings, updateSettings, saveGeneralSettings, saving } = useSettings()

  return (
    <div className="sp-flex sp-flex-col sp-gap-6">
      {/* Order State */}
      <Card>
        <CardHeader>
          <div className="sp-flex sp-items-center sp-gap-2">
            <ClipboardList className="sp-h-5 sp-w-5 sp-text-muted-foreground" />
            <div className="sp-flex sp-flex-col sp-gap-1.5">
              <CardTitle className="sp-text-base sp-font-semibold">Order State</CardTitle>
              <CardDescription>
                Define the default order status for Saferpay payments.
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="sp-flex sp-flex-col sp-gap-2">
            <Label htmlFor="order-status">Status for Saferpay payment awaiting</Label>
            <Select
              value={String(settings.orderStateAwaitingPayment)}
              onValueChange={(val) => updateSettings({ orderStateAwaitingPayment: Number(val) })}
            >
              <SelectTrigger id="order-status">
                <SelectValue placeholder="Select order status" />
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
              Default status on SaferPay order creation.
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
              <CardTitle className="sp-text-base sp-font-semibold">Styling</CardTitle>
              <CardDescription>
                Customize the appearance of the payment page.
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="sp-flex sp-flex-col sp-gap-2">
            <Label htmlFor="page-config-name">Payment Page configurations name</Label>
            <Input
              id="page-config-name"
              type="text"
              placeholder="Enter configuration name"
              value={settings.configurationName}
              onChange={(e) => updateSettings({ configurationName: e.target.value })}
            />
            <p className="sp-text-xs sp-text-muted-foreground">
              This name is visible in payment page and also in payment confirmation email.
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
              <CardTitle className="sp-text-base sp-font-semibold">Configuration</CardTitle>
              <CardDescription>
                General module configuration settings.
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="sp-grid sp-gap-5">
            <div className="sp-flex sp-flex-col sp-gap-2">
              <Label htmlFor="description">Description</Label>
              <Input
                id="description"
                type="text"
                placeholder="Enter description"
                value={settings.paymentDescription}
                onChange={(e) => updateSettings({ paymentDescription: e.target.value })}
              />
              <p className="sp-text-xs sp-text-muted-foreground">
                This description is visible in payment page also in payment confirmation email.
              </p>
            </div>

            <div className="sp-flex sp-items-center sp-justify-between sp-rounded-lg sp-border sp-bg-secondary/50 sp-px-4 sp-py-3">
              <div className="sp-flex sp-flex-col sp-gap-0.5">
                <Label htmlFor="debug-mode" className="sp-font-medium sp-cursor-pointer">
                  Debug mode
                </Label>
                <p className="sp-text-xs sp-text-muted-foreground">
                  Enable debug mode to see more information in logs.
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
          {saving ? <Loader2 className="sp-h-4 sp-w-4 sp-animate-spin" /> : 'Save Changes'}
        </Button>
      </div>
    </div>
  )
}
