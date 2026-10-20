'use client'

import { useEffect, useRef } from 'react'
import { X } from 'lucide-react'
import { cn } from '@/lib/utils'

interface ModalProps {
  open: boolean
  onClose: () => void
  title: string
  variant?: 'center' | 'side'
  className?: string
  children: React.ReactNode
}

export function Modal({ open, onClose, title, variant = 'center', className, children }: ModalProps) {
  const ref = useRef<HTMLDialogElement>(null)

  useEffect(() => {
    const dialog = ref.current
    if (!dialog) return
    if (open && !dialog.open) dialog.showModal()
    if (!open && dialog.open) dialog.close()
  }, [open])

  return (
    <dialog
      ref={ref}
      aria-label={title}
      onClose={onClose}
      onClick={(e) => {
        if (e.target === e.currentTarget) onClose()
      }}
      className={cn(
        'm-0 max-h-none max-w-none bg-transparent p-0 text-foreground',
        variant === 'side'
          ? 'fixed inset-y-0 left-0 h-dvh w-full sm:w-[440px]'
          : 'fixed inset-0 m-auto h-fit max-h-[92dvh] w-[min(920px,94vw)]',
      )}
    >
      <div
        className={cn(
          'flex flex-col overflow-hidden bg-card shadow-2xl',
          variant === 'side' ? 'h-full' : 'max-h-[92dvh] rounded-xl',
          className,
        )}
      >
        <div className="flex items-center justify-between border-b px-5 py-4">
          <h2 className="text-base font-bold">{title}</h2>
          <button
            type="button"
            onClick={onClose}
            className="inline-flex size-9 items-center justify-center rounded-md text-muted-foreground transition hover:bg-muted hover:text-foreground"
          >
            <X className="size-5" aria-hidden />
            <span className="sr-only">بستن</span>
          </button>
        </div>
        <div className="min-h-0 flex-1 overflow-y-auto">{children}</div>
      </div>
    </dialog>
  )
}
