import * as React from "react";
import { Slot } from "@radix-ui/react-slot";
import { cva, type VariantProps } from "class-variance-authority";

import { cn } from "@/lib/utils";

const buttonVariants = cva(
  "sp-inline-flex sp-items-center sp-justify-center sp-gap-2 sp-whitespace-nowrap sp-rounded-md sp-text-sm sp-font-medium sp-ring-offset-background sp-transition-colors focus-visible:sp-outline-none focus-visible:sp-ring-2 focus-visible:sp-ring-ring focus-visible:sp-ring-offset-2 disabled:sp-pointer-events-none disabled:sp-opacity-50 [&_svg]:sp-pointer-events-none [&_svg]:sp-size-4 [&_svg]:sp-shrink-0",
  {
    variants: {
      variant: {
        default:
          "sp-bg-primary sp-text-primary-foreground hover:sp-bg-primary/90",
        destructive:
          "sp-bg-destructive sp-text-destructive-foreground hover:sp-bg-destructive/90",
        outline:
          "sp-border sp-border-input sp-bg-background hover:sp-bg-accent hover:sp-text-accent-foreground",
        secondary:
          "sp-bg-secondary sp-text-secondary-foreground hover:sp-bg-secondary/80",
        ghost: "hover:sp-bg-accent hover:sp-text-accent-foreground",
        link: "sp-text-primary sp-underline-offset-4 hover:sp-underline",
      },
      size: {
        default: "sp-h-10 sp-px-4 sp-py-2",
        sm: "sp-h-9 sp-rounded-md sp-px-3",
        lg: "sp-h-11 sp-rounded-md sp-px-8",
        icon: "sp-h-10 sp-w-10",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  },
);

export interface ButtonProps
  extends
    React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  asChild?: boolean;
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant, size, asChild = false, ...props }, ref) => {
    const Comp = asChild ? Slot : "button";
    return (
      <Comp
        className={cn(buttonVariants({ variant, size, className }))}
        ref={ref}
        {...props}
      />
    );
  },
);
Button.displayName = "Button";

export { Button, buttonVariants };
