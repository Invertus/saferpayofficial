import type { Config } from 'tailwindcss'

const config: Config = {
  prefix: 'sp-',
  important: '.sp-saferpay-root',
  darkMode: ['class'],
  content: ['./src/**/*.{ts,tsx}'],
  corePlugins: {
    preflight: false,
  },
  theme: {
    extend: {
      colors: {
        background: 'hsl(var(--sp-background))',
        foreground: 'hsl(var(--sp-foreground))',
        card: {
          DEFAULT: 'hsl(var(--sp-card))',
          foreground: 'hsl(var(--sp-card-foreground))',
        },
        popover: {
          DEFAULT: 'hsl(var(--sp-popover))',
          foreground: 'hsl(var(--sp-popover-foreground))',
        },
        primary: {
          DEFAULT: 'hsl(var(--sp-primary))',
          foreground: 'hsl(var(--sp-primary-foreground))',
        },
        secondary: {
          DEFAULT: 'hsl(var(--sp-secondary))',
          foreground: 'hsl(var(--sp-secondary-foreground))',
        },
        muted: {
          DEFAULT: 'hsl(var(--sp-muted))',
          foreground: 'hsl(var(--sp-muted-foreground))',
        },
        accent: {
          DEFAULT: 'hsl(var(--sp-accent))',
          foreground: 'hsl(var(--sp-accent-foreground))',
        },
        destructive: {
          DEFAULT: 'hsl(var(--sp-destructive))',
          foreground: 'hsl(var(--sp-destructive-foreground))',
        },
        border: 'hsl(var(--sp-border))',
        input: 'hsl(var(--sp-input))',
        ring: 'hsl(var(--sp-ring))',
      },
      borderRadius: {
        lg: 'var(--sp-radius)',
        md: 'calc(var(--sp-radius) - 2px)',
        sm: 'calc(var(--sp-radius) - 4px)',
      },
      keyframes: {
        'accordion-down': {
          from: { height: '0' },
          to: { height: 'var(--radix-accordion-content-height)' },
        },
        'accordion-up': {
          from: { height: 'var(--radix-accordion-content-height)' },
          to: { height: '0' },
        },
      },
      animation: {
        'accordion-down': 'accordion-down 0.2s ease-out',
        'accordion-up': 'accordion-up 0.2s ease-out',
      },
    },
  },
  plugins: [require('tailwindcss-animate')],
}
export default config
