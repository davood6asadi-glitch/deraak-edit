'use client'

import { CheckCircle2 } from 'lucide-react'
import { useInquiry } from './inquiry-provider'

export function Toast() {
  const { toast, setOpen, items } = useInquiry()

  return (
    <div aria-live="polite" className="pointer-events-none fixed inset-x-0 bottom-5 z-50 flex justify-center px-4">
      {toast && (
        <div className="pointer-events-auto flex items-center gap-3 rounded-lg bg-ink py-2.5 pr-4 pl-2.5 text-sm text-ink-foreground shadow-xl animate-in fade-in slide-in-from-bottom-2">
          <CheckCircle2 className="size-4 text-accent" aria-hidden />
          {toast}
          {items.length > 0 && (
            <button
              type="button"
              onClick={() => setOpen(true)}
              className="rounded-md bg-white/10 px-3 py-1.5 text-xs font-medium transition hover:bg-white/15"
            >
              مشاهده لیست
            </button>
          )}
        </div>
      )}
    </div>
  )
}
