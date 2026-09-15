import * as React from 'react'
import * as CheckboxPrimitive from '@radix-ui/react-checkbox'
import { Check } from 'lucide-react'

import { cn } from '@/lib/utils'

const Checkbox = React.forwardRef<
  React.ElementRef<typeof CheckboxPrimitive.Root>,
  React.ComponentPropsWithoutRef<typeof CheckboxPrimitive.Root>
>(({ className, ...props }, ref) => (
  <CheckboxPrimitive.Root
    ref={ref}
    className={cn(
      'sp-peer sp-h-4 sp-w-4 sp-shrink-0 sp-rounded-sm sp-border sp-border-primary sp-ring-offset-background focus-visible:sp-outline-none focus-visible:sp-ring-2 focus-visible:sp-ring-ring focus-visible:sp-ring-offset-2 disabled:sp-cursor-not-allowed disabled:sp-opacity-50 data-[state=checked]:sp-bg-primary data-[state=checked]:sp-text-primary-foreground',
      className,
    )}
    {...props}
  >
    <CheckboxPrimitive.Indicator
      className={cn('sp-flex sp-items-center sp-justify-center sp-text-current')}
    >
      <Check className="sp-h-4 sp-w-4" />
    </CheckboxPrimitive.Indicator>
  </CheckboxPrimitive.Root>
))
Checkbox.displayName = CheckboxPrimitive.Root.displayName

export { Checkbox }
