'use client'

import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react'
import type { Product } from '@/lib/products'

export const MAX_QTY = 999
export const MAX_NOTE = 300

export interface InquiryItem {
  product: Product
  quantity: number
  notes: string
  urgent: boolean
}

interface InquiryContextValue {
  items: InquiryItem[]
  add: (product: Product) => void
  setQuantity: (id: string, quantity: number) => void
  remove: (id: string) => void
  setNotes: (id: string, notes: string) => void
  toggleUrgent: (id: string) => void
  clear: () => void
  isOpen: boolean
  setOpen: (open: boolean) => void
  toast: string | null
  notify: (message: string) => void
}

const InquiryContext = createContext<InquiryContextValue | null>(null)

const clampQty = (n: number) => (Number.isFinite(n) ? Math.min(MAX_QTY, Math.max(1, Math.trunc(n))) : 1)

export function InquiryProvider({ children }: { children: React.ReactNode }) {
  const [items, setItems] = useState<InquiryItem[]>([])
  const [isOpen, setOpen] = useState(false)
  const [toast, setToast] = useState<string | null>(null)

  useEffect(() => {
    if (!toast) return
    const timer = setTimeout(() => setToast(null), 3000)
    return () => clearTimeout(timer)
  }, [toast])

  const notify = useCallback((message: string) => setToast(message), [])

  const add = useCallback(
    (product: Product) => {
      setItems((prev) => {
        const existing = prev.find((i) => i.product.id === product.id)
        if (existing) {
          return prev.map((i) =>
            i.product.id === product.id ? { ...i, quantity: clampQty(i.quantity + 1) } : i,
          )
        }
        return [...prev, { product, quantity: 1, notes: '', urgent: false }]
      })
      notify(`${product.model} به لیست استعلام اضافه شد`)
    },
    [notify],
  )

  const value = useMemo<InquiryContextValue>(
    () => ({
      items,
      add,
      setQuantity: (id, quantity) =>
        setItems((prev) => prev.map((i) => (i.product.id === id ? { ...i, quantity: clampQty(quantity) } : i))),
      remove: (id) => setItems((prev) => prev.filter((i) => i.product.id !== id)),
      setNotes: (id, notes) =>
        setItems((prev) =>
          prev.map((i) => (i.product.id === id ? { ...i, notes: notes.slice(0, MAX_NOTE) } : i)),
        ),
      toggleUrgent: (id) =>
        setItems((prev) => prev.map((i) => (i.product.id === id ? { ...i, urgent: !i.urgent } : i))),
      clear: () => setItems([]),
      isOpen,
      setOpen,
      toast,
      notify,
    }),
    [items, add, isOpen, toast, notify],
  )

  return <InquiryContext.Provider value={value}>{children}</InquiryContext.Provider>
}

export function useInquiry() {
  const ctx = useContext(InquiryContext)
  if (!ctx) throw new Error('useInquiry must be used inside InquiryProvider')
  return ctx
}
