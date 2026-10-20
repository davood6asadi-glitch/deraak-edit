'use client'

import { useEffect, useRef, useState } from 'react'
import {
  ArrowRight,
  CheckCircle2,
  Loader2,
  Mail,
  MessageSquareMore,
  Phone,
  RefreshCw,
  Send,
  ShieldCheck,
  Smartphone,
} from 'lucide-react'
import { CONTACT } from '@/lib/products'
import {
  buildMailto,
  clean,
  normalizeIranMobile,
  requesterLines,
  toEnglishDigits,
  validateConsultationInput,
} from '@/lib/request'
import { useInquiry } from './inquiry-provider'

const CHANNELS = [
  { icon: Phone, label: 'تلفن دفتر', value: CONTACT.phoneLabel, href: `tel:${CONTACT.phone}` },
  { icon: Smartphone, label: 'موبایل فروش', value: CONTACT.mobileLabel, href: `tel:${CONTACT.mobile}` },
  { icon: Send, label: 'تلگرام فروش', value: `@${CONTACT.telegramSales}`, href: `https://t.me/${CONTACT.telegramSales}` },
  { icon: Send, label: 'پشتیبانی فنی', value: `@${CONTACT.telegramProcess}`, href: `https://t.me/${CONTACT.telegramProcess}` },
  { icon: Mail, label: 'ایمیل', value: CONTACT.email, href: `mailto:${CONTACT.email}` },
]

const fieldClass =
  'h-11 w-full rounded-md border border-white/15 bg-white/5 px-3 text-sm text-ink-foreground outline-none transition placeholder:text-ink-foreground/40 focus:border-accent disabled:opacity-50'

type FormStep = 'details' | 'otp' | 'success'

const OTP_LEN = 5

export function Contact() {
  const { notify } = useInquiry()

  const [step, setStep] = useState<FormStep>('details')
  const [form, setForm] = useState({
    name: '',
    company: '',
    phone: '',
    part: '',
    message: '',
  })

  const [otpDigits, setOtpDigits] = useState<string[]>(Array(OTP_LEN).fill(''))
  const [otpToken, setOtpToken] = useState<string>('')
  const [verifiedPhone, setVerifiedPhone] = useState<string>('')
  const [countdown, setCountdown] = useState<number>(0)
  const [loading, setLoading] = useState<boolean>(false)
  const [error, setError] = useState<string | null>(null)
  const [sandboxCode, setSandboxCode] = useState<string | null>(null)
  const [trackingCode, setTrackingCode] = useState<string>('')

  const otpInputRefs = useRef<Array<HTMLInputElement | null>>([])

  // شمارش معکوس تایمر ارسال مجدد کد تایید
  useEffect(() => {
    if (step !== 'otp' || countdown <= 0) return
    const timer = window.setInterval(() => {
      setCountdown((prev) => (prev > 0 ? prev - 1 : 0))
    }, 1000)
    return () => window.clearInterval(timer)
  }, [step, countdown])

  // فوکوس خودکار روی اولین خانه کد تایید هنگام ورود به مرحله OTP
  useEffect(() => {
    if (step === 'otp') {
      const timeout = window.setTimeout(() => {
        otpInputRefs.current[0]?.focus()
      }, 60)
      return () => window.clearTimeout(timeout)
    }
  }, [step])

  const formatTimer = (seconds: number) => {
    const mins = Math.floor(seconds / 60)
    const secs = seconds % 60
    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
  }

  const requestOtp = async (e?: React.FormEvent) => {
    if (e) e.preventDefault()

    const validationError = validateConsultationInput(form)
    setError(validationError)
    if (validationError) return

    setLoading(true)
    setError(null)

    try {
      const response = await fetch('/api/contact/send-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      })

      const data = await response.json()
      if (!response.ok || !data.ok) {
        setError(data.error || 'خطا در ارسال کد تایید پیامکی')
        return
      }

      setVerifiedPhone(data.phone || normalizeIranMobile(form.phone) || form.phone)
      setOtpToken(data.otpToken)
      setOtpDigits(Array(OTP_LEN).fill(''))
      setCountdown(data.expiresIn || 120)
      setSandboxCode(data.sandboxCode || null)
      setStep('otp')
      notify(`کد تایید به شماره ${data.phone} ارسال شد`)
    } catch {
      setError('خطا در برقراری ارتباط با سرور؛ لطفاً مجدداً تلاش کنید')
    } finally {
      setLoading(false)
    }
  }

  const submitVerifyOtp = async (codeOverride?: string) => {
    const code = toEnglishDigits(codeOverride ?? otpDigits.join('')).trim()
    if (code.length !== OTP_LEN) {
      setError('لطفاً کد تایید ۵ رقمی پیامک شده را کامل وارد کنید')
      return
    }

    setLoading(true)
    setError(null)

    try {
      const response = await fetch('/api/contact/verify-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          ...form,
          phone: verifiedPhone || form.phone,
          otpCode: code,
          otpToken,
        }),
      })

      const data = await response.json()
      if (!response.ok || !data.ok) {
        setError(data.error || 'کد تایید وارد شده معتبر نیست')
        return
      }

      setTrackingCode(data.trackingCode || '')
      setStep('success')
      notify('درخواست مشاوره شما با موفقیت برای مدیر سایت پیامک شد')
    } catch {
      setError('خطا در ارتباط با سرور هنگام تایید کد')
    } finally {
      setLoading(false)
    }
  }

  const handleOtpChange = (index: number, rawValue: string) => {
    setError(null)
    const cleanVal = toEnglishDigits(rawValue).replace(/\D/g, '')

    if (!cleanVal) {
      const next = [...otpDigits]
      next[index] = ''
      setOtpDigits(next)
      return
    }

    // در صورت وارد کردن یا جایگذاری بیش از یک رقم
    if (cleanVal.length > 1) {
      const chars = cleanVal.slice(0, OTP_LEN).split('')
      const next = [...otpDigits]
      chars.forEach((ch, idx) => {
        if (index + idx < OTP_LEN) {
          next[index + idx] = ch
        }
      })
      setOtpDigits(next)
      const nextFocusIndex = Math.min(index + chars.length, OTP_LEN - 1)
      otpInputRefs.current[nextFocusIndex]?.focus()

      const joined = next.join('')
      if (joined.length === OTP_LEN && !next.includes('')) {
        void submitVerifyOtp(joined)
      }
      return
    }

    const digit = cleanVal.slice(-1)
    const next = [...otpDigits]
    next[index] = digit
    setOtpDigits(next)

    if (index < OTP_LEN - 1) {
      otpInputRefs.current[index + 1]?.focus()
    }

    const joined = next.join('')
    if (joined.length === OTP_LEN && !next.includes('')) {
      void submitVerifyOtp(joined)
    }
  }

  const handleOtpKeyDown = (index: number, e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Backspace') {
      if (otpDigits[index]) {
        const next = [...otpDigits]
        next[index] = ''
        setOtpDigits(next)
      } else if (index > 0) {
        const next = [...otpDigits]
        next[index - 1] = ''
        setOtpDigits(next)
        otpInputRefs.current[index - 1]?.focus()
      }
      e.preventDefault()
    } else if (e.key === 'ArrowLeft' && index > 0) {
      otpInputRefs.current[index - 1]?.focus()
    } else if (e.key === 'ArrowRight' && index < OTP_LEN - 1) {
      otpInputRefs.current[index + 1]?.focus()
    }
  }

  const handleOtpPaste = (e: React.ClipboardEvent<HTMLInputElement>) => {
    e.preventDefault()
    const pasted = toEnglishDigits(e.clipboardData.getData('text')).replace(/\D/g, '').slice(0, OTP_LEN)
    if (!pasted) return

    const next = Array(OTP_LEN).fill('')
    pasted.split('').forEach((ch, idx) => {
      next[idx] = ch
    })
    setOtpDigits(next)
    setError(null)

    const focusIdx = Math.min(pasted.length, OTP_LEN - 1)
    otpInputRefs.current[focusIdx]?.focus()

    if (pasted.length === OTP_LEN) {
      void submitVerifyOtp(pasted)
    }
  }

  const resetForm = () => {
    setForm({ name: '', company: '', phone: '', part: '', message: '' })
    setOtpDigits(Array(OTP_LEN).fill(''))
    setOtpToken('')
    setSandboxCode(null)
    setError(null)
    setStep('details')
  }

  const openBackupEmail = () => {
    window.location.href = buildMailto('درخواست مشاوره فنی', [
      ...requesterLines(form),
      `پارت‌نامبر: ${clean(form.part, 60) || '-'}`,
      `کد پیگیری پیامک: ${trackingCode || '-'}`,
      '',
      clean(form.message, 600),
    ])
  }

  return (
    <section id="contact" className="scroll-mt-16 bg-ink text-ink-foreground">
      <div className="mx-auto grid max-w-7xl gap-12 px-4 py-16 md:px-8 md:py-24 lg:grid-cols-2">
        <div className="flex flex-col gap-6">
          <span className="font-mono text-xs tracking-widest text-accent" dir="ltr">
            04 / CONTACT
          </span>
          <h2 className="text-3xl font-extrabold md:text-4xl">مشاوره فنی با مهندسین فرآیند</h2>
          <p className="max-w-md leading-8 text-ink-foreground/70">
            برای انتخاب تجهیز مناسب، جایگزینی مدل‌های قدیمی یا بررسی گواهی‌های ضدانفجار با ما در ارتباط باشید.
          </p>
          <ul className="flex flex-col divide-y divide-white/10 rounded-lg border border-white/10">
            {CHANNELS.map((c) => (
              <li key={c.label}>
                <a
                  href={c.href}
                  target={c.href.startsWith('http') ? '_blank' : undefined}
                  rel={c.href.startsWith('http') ? 'noopener noreferrer' : undefined}
                  className="flex items-center gap-4 px-4 py-3.5 transition hover:bg-white/5"
                >
                  <c.icon className="size-4 text-accent" aria-hidden />
                  <span className="text-sm text-ink-foreground/70">{c.label}</span>
                  <span className="mr-auto font-mono text-sm" dir="ltr">
                    {c.value}
                  </span>
                </a>
              </li>
            ))}
          </ul>
        </div>

        <div className="flex flex-col justify-between rounded-lg border border-white/10 bg-white/[0.03] p-5 md:p-6">
          {step === 'details' && (
            <form onSubmit={requestOtp} noValidate className="flex flex-col gap-3.5">
              <div className="flex items-center justify-between gap-2">
                <h3 className="text-lg font-bold">فرم درخواست مشاوره</h3>
                <span className="inline-flex items-center gap-1.5 text-xs text-ink-foreground/60">
                  <ShieldCheck className="size-3.5 text-accent" aria-hidden />
                  تایید پیامکی کاوه‌نگار
                </span>
              </div>

              <div className="grid gap-3 sm:grid-cols-2">
                <input
                  className={fieldClass}
                  placeholder="نام و نام خانوادگی"
                  aria-label="نام و نام خانوادگی"
                  autoComplete="name"
                  maxLength={80}
                  disabled={loading}
                  value={form.name}
                  onChange={(e) => setForm({ ...form, name: e.target.value })}
                />
                <input
                  className={fieldClass}
                  placeholder="نام شرکت"
                  aria-label="نام شرکت"
                  autoComplete="organization"
                  maxLength={120}
                  disabled={loading}
                  value={form.company}
                  onChange={(e) => setForm({ ...form, company: e.target.value })}
                />
                <input
                  className={`${fieldClass} text-left font-mono tabular-nums`}
                  placeholder="09xx xxx xxxx"
                  aria-label="شماره موبایل"
                  type="tel"
                  inputMode="tel"
                  dir="ltr"
                  autoComplete="tel"
                  maxLength={20}
                  disabled={loading}
                  value={form.phone}
                  onChange={(e) =>
                    setForm({ ...form, phone: toEnglishDigits(e.target.value) })
                  }
                />
                <input
                  className={`${fieldClass} text-left font-mono`}
                  placeholder="Part number"
                  aria-label="پارت‌نامبر"
                  dir="ltr"
                  maxLength={60}
                  disabled={loading}
                  value={form.part}
                  onChange={(e) => setForm({ ...form, part: e.target.value })}
                />
              </div>

              <textarea
                className={`${fieldClass} h-32 resize-none py-2.5`}
                placeholder="شرح نیاز فنی: سیال، دما، فشار، اتصال فرآیندی، گواهی مورد نیاز..."
                aria-label="شرح نیاز فنی"
                maxLength={600}
                disabled={loading}
                value={form.message}
                onChange={(e) => setForm({ ...form, message: e.target.value })}
              />

              {error && (
                <p role="alert" className="text-sm text-accent">
                  {error}
                </p>
              )}

              <button
                type="submit"
                disabled={loading}
                className="inline-flex h-12 items-center justify-center gap-2 rounded-md bg-accent font-bold text-accent-foreground transition hover:brightness-105 disabled:opacity-60"
              >
                {loading ? (
                  <>
                    <Loader2 className="size-4 animate-spin" aria-hidden />
                    در حال ارسال کد تایید...
                  </>
                ) : (
                  <>
                    <Smartphone className="size-4" aria-hidden />
                    دریافت کد تایید و ثبت درخواست
                  </>
                )}
              </button>

              <p className="text-xs leading-5 text-ink-foreground/50">
                پس از تایید شماره همراه شما با کد ۵ رقمی، خلاصه درخواست مشاوره مستقیماً برای مدیر فنی پیامک می‌شود.
              </p>
            </form>
          )}

          {step === 'otp' && (
            <form
              onSubmit={(e) => {
                e.preventDefault()
                void submitVerifyOtp()
              }}
              noValidate
              className="flex flex-col gap-4"
            >
              <div className="flex items-center justify-between gap-2 border-b border-white/10 pb-3">
                <div className="flex items-center gap-2">
                  <ShieldCheck className="size-5 text-accent" aria-hidden />
                  <h3 className="text-base font-bold">تایید شماره موبایل</h3>
                </div>
                <button
                  type="button"
                  disabled={loading}
                  onClick={() => {
                    setError(null)
                    setStep('details')
                  }}
                  className="inline-flex items-center gap-1 text-xs text-accent transition hover:underline disabled:opacity-50"
                >
                  <ArrowRight className="size-3.5" aria-hidden />
                  ویرایش شماره یا اطلاعات
                </button>
              </div>

              <div className="flex flex-col gap-1 text-sm text-ink-foreground/80">
                <p>
                  کد تایید ۵ رقمی به شماره{' '}
                  <span className="font-mono font-bold text-accent tabular-nums" dir="ltr">
                    {verifiedPhone}
                  </span>{' '}
                  پیامک شد.
                </p>
                <p className="text-xs text-ink-foreground/55">
                  لطفاً کد دریافت شده را جهت ارسال نهایی درخواست مشاوره به مدیر سایت وارد نمایید.
                </p>
              </div>

              {sandboxCode && (
                <div className="flex items-center justify-between rounded-md border border-accent/30 bg-accent/10 px-3 py-2 text-xs text-ink-foreground">
                  <span>حالت آزمایشی (بدون کلید کاوه‌نگار) — کد تایید شما:</span>
                  <button
                    type="button"
                    onClick={() => {
                      const digits = sandboxCode.split('').slice(0, OTP_LEN)
                      setOtpDigits(digits)
                      void submitVerifyOtp(sandboxCode)
                    }}
                    className="font-mono text-sm font-bold tracking-widest text-accent underline decoration-dotted underline-offset-4"
                    dir="ltr"
                    title="کلیک برای درج خودکار کد"
                  >
                    {sandboxCode}
                  </button>
                </div>
              )}

              <div className="my-1 flex justify-center gap-2.5 sm:gap-3" dir="ltr">
                {otpDigits.map((digit, index) => (
                  <input
                    key={index}
                    ref={(el) => {
                      otpInputRefs.current[index] = el
                    }}
                    type="text"
                    inputMode="numeric"
                    autoComplete={index === 0 ? 'one-time-code' : 'off'}
                    maxLength={OTP_LEN}
                    disabled={loading}
                    value={digit}
                    aria-label={`رقم ${index + 1} کد تایید`}
                    onChange={(e) => handleOtpChange(index, e.target.value)}
                    onKeyDown={(e) => handleOtpKeyDown(index, e)}
                    onPaste={handleOtpPaste}
                    className="h-12 w-11 rounded-md border border-white/20 bg-white/5 text-center font-mono text-lg font-bold text-ink-foreground tabular-nums outline-none transition focus:border-accent focus:bg-white/10 focus:ring-2 focus:ring-accent/25 sm:h-13 sm:w-12"
                  />
                ))}
              </div>

              {error && (
                <p role="alert" className="text-center text-sm text-accent">
                  {error}
                </p>
              )}

              <div className="flex items-center justify-between text-xs text-ink-foreground/60">
                {countdown > 0 ? (
                  <span>
                    ارسال مجدد کد تا{' '}
                    <strong className="font-mono text-ink-foreground tabular-nums" dir="ltr">
                      {formatTimer(countdown)}
                    </strong>{' '}
                    دیگر
                  </span>
                ) : (
                  <span>کد تایید را دریافت نکردید؟</span>
                )}

                <button
                  type="button"
                  disabled={countdown > 0 || loading}
                  onClick={() => void requestOtp()}
                  className="inline-flex items-center gap-1.5 font-medium text-accent transition hover:underline disabled:cursor-not-allowed disabled:text-ink-foreground/35 disabled:no-underline"
                >
                  <RefreshCw className="size-3.5" aria-hidden />
                  ارسال مجدد کد تایید
                </button>
              </div>

              <button
                type="submit"
                disabled={loading || otpDigits.join('').length !== OTP_LEN}
                className="mt-1 inline-flex h-12 items-center justify-center gap-2 rounded-md bg-accent font-bold text-accent-foreground transition hover:brightness-105 disabled:opacity-50"
              >
                {loading ? (
                  <>
                    <Loader2 className="size-4 animate-spin" aria-hidden />
                    در حال بررسی کد و ارسال پیامک به مدیر...
                  </>
                ) : (
                  <>
                    <MessageSquareMore className="size-4" aria-hidden />
                    تایید کد و ارسال پیامک درخواست مشاوره
                  </>
                )}
              </button>
            </form>
          )}

          {step === 'success' && (
            <div className="flex flex-col justify-between gap-5 py-2">
              <div className="flex items-start gap-3.5">
                <div className="flex size-10 shrink-0 items-center justify-center rounded-md bg-accent/15 text-accent">
                  <CheckCircle2 className="size-5" aria-hidden />
                </div>
                <div className="flex flex-col gap-1">
                  <h3 className="text-lg font-bold">درخواست مشاوره با موفقیت ثبت و پیامک شد</h3>
                  <p className="text-sm leading-6 text-ink-foreground/75">
                    شماره همراه شما تایید شد و جزئیات درخواست مشاوره فنی از طریق سامانه پیامکی کاوه‌نگار برای مدیر فروش و فنی دراک ارسال گردید.
                  </p>
                </div>
              </div>

              <div className="flex flex-col gap-2 rounded-md border border-white/10 bg-white/[0.02] p-4 text-xs">
                <div className="flex items-center justify-between border-b border-white/10 pb-2">
                  <span className="text-ink-foreground/60">کد پیگیری درخواست:</span>
                  <span className="font-mono font-bold text-accent tabular-nums" dir="ltr">
                    {trackingCode}
                  </span>
                </div>
                <div className="flex items-center justify-between">
                  <span className="text-ink-foreground/60">درخواست‌دهنده:</span>
                  <span>
                    {form.name} {form.company ? `(${form.company})` : ''}
                  </span>
                </div>
                <div className="flex items-center justify-between">
                  <span className="text-ink-foreground/60">شماره تایید شده:</span>
                  <span className="font-mono tabular-nums" dir="ltr">
                    {verifiedPhone}
                  </span>
                </div>
                {form.part && (
                  <div className="flex items-center justify-between">
                    <span className="text-ink-foreground/60">پارت‌نامبر:</span>
                    <span className="font-mono" dir="ltr">
                      {form.part}
                    </span>
                  </div>
                )}
              </div>

              <div className="flex flex-col gap-2.5 sm:flex-row">
                <button
                  type="button"
                  onClick={resetForm}
                  className="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-md bg-accent font-bold text-accent-foreground transition hover:brightness-105"
                >
                  ثبت درخواست مشاوره جدید
                </button>
                <button
                  type="button"
                  onClick={openBackupEmail}
                  className="inline-flex h-11 items-center justify-center gap-2 rounded-md border border-white/15 bg-white/5 px-4 text-xs font-medium text-ink-foreground transition hover:bg-white/10"
                >
                  <Mail className="size-4 text-accent" aria-hidden />
                  ارسال رونوشت با ایمیل
                </button>
              </div>
            </div>
          )}
        </div>
      </div>
      <footer className="border-t border-white/10">
        <div className="mx-auto flex max-w-7xl flex-col gap-5 px-4 py-6 md:px-8">
          <div className="flex flex-col items-start justify-between gap-4 rounded-xl border border-white/10 bg-white/[0.03] p-4 md:flex-row md:items-center">
            <div className="flex flex-col gap-1">
              <span className="text-xs font-bold text-accent">
                بسته نصبی کامل وردپرس و ووکامرس (آماده نصب روی هاست ایران)
              </span>
              <span className="text-xs text-ink-foreground/70">
                شامل پوسته اختصاصی با فونت محلی IBM Plex Sans Arabic، افزونه موتور B2B + کاوه‌نگار OTP و فایل CSV محصولات
              </span>
            </div>
            <div className="flex flex-wrap items-center gap-2">
              <a
                href="/wordpress-package/deraak-industrial.zip"
                download="deraak-industrial.zip"
                className="inline-flex h-9 items-center gap-1.5 rounded-md bg-accent px-3.5 text-xs font-bold text-accent-foreground transition hover:brightness-105"
              >
                دانلود قالب (deraak-industrial.zip)
              </a>
              <a
                href="/wordpress-package/deraak-b2b-engine.zip"
                download="deraak-b2b-engine.zip"
                className="inline-flex h-9 items-center gap-1.5 rounded-md border border-white/20 bg-white/10 px-3.5 text-xs font-semibold text-ink-foreground transition hover:bg-white/15"
              >
                دانلود افزونه (deraak-b2b-engine.zip)
              </a>
              <a
                href="/wordpress-package/sample-deraak-products.csv"
                download="sample-deraak-products.csv"
                className="inline-flex h-9 items-center gap-1.5 rounded-md border border-white/15 bg-white/5 px-3 text-xs font-medium text-ink-foreground/85 transition hover:bg-white/10"
              >
                فایل محصولات (CSV)
              </a>
            </div>
          </div>

          <div className="flex flex-col justify-between gap-2 text-xs text-ink-foreground/50 md:flex-row">
            <span>دراک — تأمین ابزار دقیق و اتوماسیون صنعتی (مجهز به تایپوگرافی یکپارچه IBM Plex Sans Arabic)</span>
            <span className="font-mono" dir="ltr">
              Tehran · Asaluyeh
            </span>
          </div>
        </div>
      </footer>
    </section>
  )
}
