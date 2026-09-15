import * as React from 'react'

import { cn } from '@/lib/utils'

const Input = React.forwardRef<HTMLInputElement, React.ComponentProps<'input'>>(
  ({ className, type, ...props }, ref) => {
    return (
      <input
        type={type}
        className={cn(
          'sp-flex sp-h-10 sp-w-full sp-rounded-md sp-border sp-border-input sp-bg-background sp-px-3 sp-py-2 sp-text-sm sp-ring-offset-background file:sp-border-0 file:sp-bg-transparent file:sp-text-sm file:sp-font-medium file:sp-text-foreground placeholder:sp-text-muted-foreground focus-visible:sp-outline-none focus-visible:sp-ring-2 focus-visible:sp-ring-ring focus-visible:sp-ring-offset-2 disabled:sp-cursor-not-allowed disabled:sp-opacity-50',
          className,
        )}
        ref={ref}
        {...props}
      />
    )
  },
)
Input.displayName = 'Input'

export { Input }
