import { useToast } from '@/hooks/use-toast'

export function ToastContainer() {
  const { toasts } = useToast()

  if (toasts.length === 0) return null

  return (
    <div className="sp-fixed sp-bottom-4 sp-right-4 sp-z-[9999] sp-flex sp-flex-col sp-gap-2">
      {toasts.map((t) => (
        <div
          key={t.id}
          className={`sp-rounded-lg sp-border sp-px-4 sp-py-3 sp-shadow-lg sp-text-sm sp-animate-in sp-slide-in-from-bottom-2 ${
            t.variant === 'destructive'
              ? 'sp-bg-destructive sp-text-destructive-foreground sp-border-destructive'
              : 'sp-bg-card sp-text-card-foreground sp-border-border'
          }`}
        >
          {t.title && <div className="sp-font-medium">{t.title}</div>}
          {t.description && <div className="sp-text-xs sp-mt-1 sp-opacity-80">{t.description}</div>}
        </div>
      ))}
    </div>
  )
}
