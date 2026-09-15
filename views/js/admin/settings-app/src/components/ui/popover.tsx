import * as React from 'react'
import * as PopoverPrimitive from '@radix-ui/react-popover'

import { cn } from '@/lib/utils'

const Popover = PopoverPrimitive.Root

const PopoverTrigger = PopoverPrimitive.Trigger

const PopoverContent = React.forwardRef<
  React.ElementRef<typeof PopoverPrimitive.Content>,
  React.ComponentPropsWithoutRef<typeof PopoverPrimitive.Content>
>(({ className, align = 'center', sideOffset = 4, ...props }, ref) => (
  <PopoverPrimitive.Portal container={document.querySelector<HTMLElement>('.sp-saferpay-root')}>
    <PopoverPrimitive.Content
      ref={ref}
      align={align}
      sideOffset={sideOffset}
      className={cn(
        'sp-z-50 sp-w-72 sp-rounded-md sp-border sp-bg-popover sp-p-4 sp-text-popover-foreground sp-shadow-md sp-outline-none data-[state=open]:sp-animate-in data-[state=closed]:sp-animate-out data-[state=closed]:sp-fade-out-0 data-[state=open]:sp-fade-in-0 data-[state=closed]:sp-zoom-out-95 data-[state=open]:sp-zoom-in-95 data-[side=bottom]:sp-slide-in-from-top-2 data-[side=left]:sp-slide-in-from-right-2 data-[side=right]:sp-slide-in-from-left-2 data-[side=top]:sp-slide-in-from-bottom-2',
        className,
      )}
      {...props}
    />
  </PopoverPrimitive.Portal>
))
PopoverContent.displayName = PopoverPrimitive.Content.displayName

export { Popover, PopoverTrigger, PopoverContent }
