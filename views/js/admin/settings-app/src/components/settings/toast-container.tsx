import { useToast } from '@/hooks/use-toast'

export function ToastContainer() {
  const { toasts } = useToast()

  return (
    <div
      className="sp-fixed sp-top-4 sp-right-4 sp-z-[9999] sp-flex sp-flex-col sp-gap-2 sp-max-w-[420px]"
      role="status"
      aria-live="polite"
    >
      {toasts.map((t) => (
        <div
          key={t.id}
          className={`sp-rounded-lg sp-border sp-px-4 sp-py-3 sp-shadow-lg sp-text-sm sp-animate-in sp-slide-in-from-bottom-2 ${
            t.variant === 'destructive'
              ? 'sp-bg-destructive sp-text-destructive-foreground sp-border-destructive'
              : t.variant === 'warning'
                ? 'sp-bg-amber-500 sp-text-white sp-border-amber-600'
                : 'sp-bg-emerald-600 sp-text-white sp-border-emerald-700'
          }`}
        >
          {t.title && <div className="sp-font-medium">{t.title}</div>}
          {t.description && <div className="sp-text-xs sp-mt-1 sp-opacity-80">{t.description}</div>}
        </div>
      ))}
    </div>
  )
}
