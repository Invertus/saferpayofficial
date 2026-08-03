import * as React from 'react'
import { cva, type VariantProps } from 'class-variance-authority'

import { cn } from '@/lib/utils'

const alertVariants = cva(
  'sp-relative sp-w-full sp-rounded-lg sp-border sp-p-4 [&>svg~*]:sp-pl-7 [&>svg+div]:sp-translate-y-[-3px] [&>svg]:sp-absolute [&>svg]:sp-left-4 [&>svg]:sp-top-4 [&>svg]:sp-text-foreground',
  {
    variants: {
      variant: {
        default: 'sp-bg-background sp-text-foreground',
        destructive:
          'sp-border-destructive/50 sp-text-destructive [&>svg]:sp-text-destructive',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
)

const Alert = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement> & VariantProps<typeof alertVariants>
>(({ className, variant, ...props }, ref) => (
  <div
    ref={ref}
    role="alert"
    className={cn(alertVariants({ variant }), className)}
    {...props}
  />
))
Alert.displayName = 'Alert'

const AlertTitle = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLHeadingElement>
>(({ className, ...props }, ref) => (
  <h5
    ref={ref}
    className={cn('sp-mb-1 sp-font-medium sp-leading-none sp-tracking-tight', className)}
    {...props}
  />
))
AlertTitle.displayName = 'AlertTitle'

const AlertDescription = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLParagraphElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn('sp-text-sm [&_p]:sp-leading-relaxed', className)}
    {...props}
  />
))
AlertDescription.displayName = 'AlertDescription'

export { Alert, AlertTitle, AlertDescription }
