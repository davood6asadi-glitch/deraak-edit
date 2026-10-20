'use client'

import { useState } from 'react'
import Image from 'next/image'
import { Copy, Mail, Minus, Plus, Trash2 } from 'lucide-react'
import { buildMailto, clean, requesterLines, validateRequester, type RequesterInfo } from '@/lib/request'
import { cn } from '@/lib/utils'
import { MAX_NOTE, MAX_QTY, useInquiry } from './inquiry-provider'
import { Modal } from './modal'

const fieldClass =
  'h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15'

export function InquiryDrawer() {
  const { items, isOpen, setOpen, setQuantity, remove, setNotes, toggleUrgent, notify } = useInquiry()
  const [info, setInfo] = useState<RequesterInfo>({ name: '', company: '', phone: '' })
  const [error, setError] = useState<string | null>(null)

  const lines = () => [
    ...requesterLines(info),
    '',
    ...items.map(
      (i, n) =>
        `${n + 1}. ${i.product.model} (${i.product.brand}) × ${i.quantity}${i.urgent ? ' [فوری]' : ''}${
          i.notes ? ` | ${clean(i.notes, MAX_NOTE)}` : ''
        }`,
    ),
  ]

  const validate = () => {
    const message = validateRequester(info)
    setError(message)
    return message === null
  }

  const sendEmail = () => {
    if (!validate()) return
    window.location.href = buildMailto('درخواست استعلام قیمت', lines())
  }

  const copy = async () => {
    if (!validate()) return
    try {
      await navigator.clipboard.writeText(lines().join('\n'))
      notify('متن استعلام کپی شد')
    } catch {
      setError('کپی انجام نشد؛ از ارسال ایمیل استفاده کنید')
    }
  }

  return (
    <Modal open={isOpen} onClose={() => setOpen(false)} title={`لیست استعلام (${items.length})`} variant="side">
      {items.length === 0 ? (
        <div className="flex flex-col items-center gap-3 px-6 py-20 text-center">
          <p className="font-bold">لیست استعلام خالی است</p>
          <p className="text-sm text-muted-foreground">تجهیزات مورد نیاز را از کاتالوگ اضافه کنید.</p>
          <a
            href="#catalog"
            onClick={() => setOpen(false)}
            className="mt-2 inline-flex h-10 items-center rounded-md bg-primary px-5 text-sm font-medium text-primary-foreground"
          >
            رفتن به کاتالوگ
          </a>
        </div>
      ) : (
        <div className="flex flex-col">
          <ul className="flex flex-col divide-y">
            {items.map((item) => (
              <li key={item.product.id} className="flex flex-col gap-3 p-4">
                <div className="flex gap-3">
                  <div className="relative size-16 shrink-0 overflow-hidden rounded-md bg-muted">
                    <Image src={item.product.image} alt="" fill sizes="64px" className="object-cover" />
                  </div>
                  <div className="flex min-w-0 flex-1 flex-col">
                    <span className="font-mono text-sm font-semibold text-primary" dir="ltr">
                      <span className="block truncate text-right">{item.product.model}</span>
                    </span>
                    <span className="truncate text-sm">{item.product.title}</span>
                    <span className="font-mono text-xs text-muted-foreground" dir="ltr">
                      <span className="block text-right">{item.product.brand}</span>
                    </span>
                  </div>
                  <button
                    type="button"
                    onClick={() => remove(item.product.id)}
                    className="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition hover:bg-destructive/10 hover:text-destructive"
                  >
                    <Trash2 className="size-4" aria-hidden />
                    <span className="sr-only">حذف {item.product.model}</span>
                  </button>
                </div>

                <div className="flex items-center justify-between gap-3">
                  <div className="flex items-center rounded-md border" dir="ltr">
                    <button
                      type="button"
                      disabled={item.quantity <= 1}
                      onClick={() => setQuantity(item.product.id, item.quantity - 1)}
                      className="inline-flex size-9 items-center justify-center disabled:opacity-40"
                    >
                      <Minus className="size-3.5" aria-hidden />
                      <span className="sr-only">کاهش</span>
                    </button>
                    <input
                      type="number"
                      inputMode="numeric"
                      min={1}
                      max={MAX_QTY}
                      value={item.quantity}
                      onChange={(e) => setQuantity(item.product.id, Number(e.target.value))}
                      aria-label={`تعداد ${item.product.model}`}
                      className="h-9 w-12 border-x bg-transparent text-center font-mono text-sm outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none"
                    />
                    <button
                      type="button"
                      disabled={item.quantity >= MAX_QTY}
                      onClick={() => setQuantity(item.product.id, item.quantity + 1)}
                      className="inline-flex size-9 items-center justify-center disabled:opacity-40"
                    >
                      <Plus className="size-3.5" aria-hidden />
                      <span className="sr-only">افزایش</span>
                    </button>
                  </div>
                  <button
                    type="button"
                    aria-pressed={item.urgent}
                    onClick={() => toggleUrgent(item.product.id)}
                    className={cn(
                      'h-9 rounded-md border px-3 text-xs font-medium transition',
                      item.urgent ? 'border-accent bg-accent text-accent-foreground' : 'text-muted-foreground hover:bg-muted',
                    )}
                  >
                    {item.urgent ? 'فوری' : 'علامت فوری'}
                  </button>
                </div>

                <label className="flex flex-col gap-1">
                  <span className="sr-only">توضیحات فنی {item.product.model}</span>
                  <textarea
                    value={item.notes}
                    maxLength={MAX_NOTE}
                    onChange={(e) => setNotes(item.product.id, e.target.value)}
                    rows={2}
                    placeholder="توضیحات فنی (متریال، اتصال فرآیندی، گواهی...)"
                    className="w-full resize-none rounded-md border border-input bg-background px-3 py-2 text-sm outline-none transition focus:border-primary"
                  />
                </label>
              </li>
            ))}
          </ul>

          <form
            className="flex flex-col gap-3 border-t bg-muted/50 p-4"
            onSubmit={(e) => {
              e.preventDefault()
              sendEmail()
            }}
            noValidate
          >
            <h3 className="text-sm font-bold">اطلاعات درخواست‌دهنده</h3>
            <input
              className={fieldClass}
              placeholder="نام و نام خانوادگی"
              aria-label="نام و نام خانوادگی"
              autoComplete="name"
              maxLength={80}
              value={info.name}
              onChange={(e) => setInfo({ ...info, name: e.target.value })}
            />
            <input
              className={fieldClass}
              placeholder="نام شرکت (اختیاری)"
              aria-label="نام شرکت"
              autoComplete="organization"
              maxLength={120}
              value={info.company}
              onChange={(e) => setInfo({ ...info, company: e.target.value })}
            />
            <input
              className={cn(fieldClass, 'text-left font-mono')}
              placeholder="09xx xxx xxxx"
              aria-label="شماره تماس"
              type="tel"
              dir="ltr"
              autoComplete="tel"
              maxLength={20}
              value={info.phone}
              onChange={(e) => setInfo({ ...info, phone: e.target.value })}
            />
            {error && (
              <p role="alert" className="text-sm text-destructive">
                {error}
              </p>
            )}
            <div className="flex gap-2">
              <button
                type="submit"
                className="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-md bg-primary font-bold text-primary-foreground transition hover:bg-primary/90"
              >
                <Mail className="size-4" aria-hidden />
                ارسال با ایمیل
              </button>
              <button
                type="button"
                onClick={copy}
                className="inline-flex h-11 items-center justify-center gap-2 rounded-md border bg-card px-4 text-sm font-medium transition hover:bg-muted"
              >
                <Copy className="size-4" aria-hidden />
                کپی
              </button>
            </div>
            <p className="text-xs leading-5 text-muted-foreground">
              درخواست در برنامه ایمیل شما آماده می‌شود و پس از ارسال، کارشناسان با شما تماس می‌گیرند.
            </p>
          </form>
        </div>
      )}
    </Modal>
  )
}
