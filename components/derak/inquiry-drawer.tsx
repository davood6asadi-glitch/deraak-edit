'use client'

import { useEffect, useState } from 'react'
import Image from 'next/image'
import { CheckCircle2, Copy, Loader2, Mail, Minus, Plus, Send, Trash2, WifiOff } from 'lucide-react'
import { buildMailto, clean, requesterLines, toEnglishDigits, validateRequester, type RequesterInfo } from '@/lib/request'
import { cn } from '@/lib/utils'
import { MAX_NOTE, MAX_QTY, useInquiry } from './inquiry-provider'
import { Modal } from './modal'

const fieldClass =
  'h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15'

export function InquiryDrawer() {
  const { items, isOpen, setOpen, setQuantity, remove, setNotes, toggleUrgent, clear, notify } = useInquiry()
  const [info, setInfo] = useState<RequesterInfo>({ name: '', company: '', phone: '' })
  const [honeypot, setHoneypot] = useState('')
  const [error, setError] = useState<string | null>(null)
  const [submitting, setSubmitting] = useState(false)
  const [submittedTracking, setSubmittedTracking] = useState<string | null>(null)
  const [isOnline, setIsOnline] = useState(true)

  useEffect(() => {
    setIsOnline(typeof navigator !== 'undefined' ? navigator.onLine : true)
    const onOnline = () => setIsOnline(true)
    const onOffline = () => setIsOnline(false)
    window.addEventListener('online', onOnline)
    window.addEventListener('offline', onOffline)
    return () => {
      window.removeEventListener('online', onOnline)
      window.removeEventListener('offline', onOffline)
    }
  }, [])

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

  const submitServerInquiry = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!validate()) return

    if (!isOnline) {
      setError('شما در حالت آفلاین هستید. ثبت نهایی استعلام در سرور نیازمند اتصال اینترنت است.')
      return
    }

    setSubmitting(true)
    setError(null)
    try {
      const res = await fetch('/api/inquiry/submit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          ...info,
          website_hp: honeypot,
          items: items.map((i) => ({
            id: i.product.id,
            model: i.product.model,
            title: i.product.title,
            quantity: i.quantity,
            notes: i.notes,
            urgent: i.urgent,
          })),
        }),
      })
      const data = await res.json()
      if (!res.ok || !data.ok) {
        setError(data.error || 'خطا در ثبت استعلام در سرور')
        return
      }
      setSubmittedTracking(data.trackingCode)
      notify(`استعلام با کد ${data.trackingCode} ثبت شد`)
    } catch {
      setError('خطا در ارتباط با سرور. برای ثبت نهایی استعلام، اتصال اینترنت خود را بررسی کنید.')
    } finally {
      setSubmitting(false)
    }
  }

  const openMailClient = () => {
    if (!validate()) return
    window.location.href = buildMailto('پیش‌نویس لیست استعلام قیمت', lines())
  }

  const copy = async () => {
    if (!validate()) return
    try {
      await navigator.clipboard.writeText(lines().join('\n'))
      notify('متن استعلام در حافظه کپی شد')
    } catch {
      setError('کپی انجام نشد')
    }
  }

  return (
    <Modal open={isOpen} onClose={() => setOpen(false)} title={`لیست استعلام (${items.length})`} variant="side">
      {submittedTracking ? (
        <div className="flex flex-col gap-4 p-6">
          <div className="flex items-center gap-3 rounded-lg border border-primary/20 bg-primary/5 p-4">
            <CheckCircle2 className="size-6 shrink-0 text-primary" aria-hidden />
            <div>
              <h3 className="font-bold text-foreground">درخواست استعلام ثبت شد</h3>
              <p className="mt-1 text-xs text-muted-foreground">
                کد رهگیری سرور:{' '}
                <strong className="font-bold text-primary" dir="ltr">
                  {submittedTracking}
                </strong>
              </p>
            </div>
          </div>
          <p className="text-xs leading-6 text-muted-foreground">
            اطلاعات استعلام شما اعتبارسنجی و ثبت گردید. در صورت تمایل می‌توانید یک نسخه متنی را نیز کپی یا در برنامه ایمیل خود باز کنید.
          </p>
          <div className="flex gap-2">
            <button
              type="button"
              onClick={() => {
                setSubmittedTracking(null)
                clear()
                setOpen(false)
              }}
              className="inline-flex h-10 flex-1 items-center justify-center rounded-md bg-primary px-4 text-xs font-bold text-primary-foreground"
            >
              بستن و پاکسازی لیست
            </button>
            <button
              type="button"
              onClick={copy}
              className="inline-flex h-10 items-center justify-center gap-1.5 rounded-md border px-3 text-xs font-medium"
            >
              <Copy className="size-3.5" aria-hidden />
              کپی متن
            </button>
          </div>
        </div>
      ) : items.length === 0 ? (
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
          {!isOnline && (
            <div className="flex items-center gap-2 bg-destructive px-4 py-2.5 text-xs font-medium text-white">
              <WifiOff className="size-4 shrink-0" aria-hidden />
              <span>حالت آفلاین: می‌توانید لیست را ویرایش یا کپی کنید، اما ارسال نهایی به سرور نیازمند اتصال اینترنت است.</span>
            </div>
          )}

          <ul className="flex flex-col divide-y">
            {items.map((item) => (
              <li key={item.product.id} className="flex flex-col gap-3 p-4">
                <div className="flex gap-3">
                  <div className="relative size-16 shrink-0 overflow-hidden rounded-md bg-muted">
                    <Image
                      src={item.product.image}
                      alt={item.product.title}
                      fill
                      sizes="64px"
                      className="object-cover"
                      referrerPolicy="no-referrer"
                    />
                  </div>
                  <div className="flex min-w-0 flex-1 flex-col">
                    <span className="text-sm font-semibold text-primary" dir="ltr">
                      <span className="block truncate text-right">{item.product.model}</span>
                    </span>
                    <span className="truncate text-sm">{item.product.title}</span>
                    <span className="text-xs text-muted-foreground" dir="ltr">
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
                      className="h-9 w-12 border-x bg-transparent text-center text-sm tabular-nums outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none"
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
            onSubmit={submitServerInquiry}
            noValidate
          >
            <h3 className="text-sm font-bold">اطلاعات درخواست‌دهنده</h3>
            <input
              type="text"
              name="website_hp"
              tabIndex={-1}
              autoComplete="off"
              value={honeypot}
              onChange={(e) => setHoneypot(e.target.value)}
              className="hidden"
              aria-hidden="true"
            />
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
              className={cn(fieldClass, 'text-left tabular-nums')}
              placeholder="09xx xxx xxxx"
              aria-label="شماره تماس"
              type="tel"
              dir="ltr"
              autoComplete="tel"
              maxLength={20}
              value={info.phone}
              onChange={(e) => setInfo({ ...info, phone: toEnglishDigits(e.target.value) })}
            />
            {error && (
              <p role="alert" className="text-sm text-destructive">
                {error}
              </p>
            )}
            <button
              type="submit"
              disabled={submitting || !isOnline}
              className="inline-flex h-11 w-full items-center justify-center gap-2 rounded-md bg-primary font-bold text-primary-foreground transition hover:bg-primary/90 disabled:opacity-50"
            >
              {submitting ? (
                <>
                  <Loader2 className="size-4 animate-spin" aria-hidden />
                  در حال ثبت در سرور...
                </>
              ) : (
                <>
                  <Send className="size-4" aria-hidden />
                  ثبت نهایی استعلام در سامانه
                </>
              )}
            </button>
            <div className="flex gap-2">
              <button
                type="button"
                onClick={openMailClient}
                className="inline-flex h-9 flex-1 items-center justify-center gap-1.5 rounded-md border bg-card px-3 text-xs font-medium transition hover:bg-muted"
              >
                <Mail className="size-3.5" aria-hidden />
                باز کردن پیش‌نویس در برنامه ایمیل
              </button>
              <button
                type="button"
                onClick={copy}
                className="inline-flex h-9 items-center justify-center gap-1.5 rounded-md border bg-card px-3 text-xs font-medium transition hover:bg-muted"
              >
                <Copy className="size-3.5" aria-hidden />
                کپی متن
              </button>
            </div>
          </form>
        </div>
      )}
    </Modal>
  )
}
