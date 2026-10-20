'use client'

import { useState } from 'react'
import { Mail, Phone, Send, Smartphone } from 'lucide-react'
import { CONTACT } from '@/lib/products'
import { buildMailto, clean, requesterLines, validateRequester } from '@/lib/request'

const CHANNELS = [
  { icon: Phone, label: 'تلفن دفتر', value: CONTACT.phoneLabel, href: `tel:${CONTACT.phone}` },
  { icon: Smartphone, label: 'موبایل فروش', value: CONTACT.mobileLabel, href: `tel:${CONTACT.mobile}` },
  { icon: Send, label: 'تلگرام فروش', value: `@${CONTACT.telegramSales}`, href: `https://t.me/${CONTACT.telegramSales}` },
  { icon: Send, label: 'پشتیبانی فنی', value: `@${CONTACT.telegramProcess}`, href: `https://t.me/${CONTACT.telegramProcess}` },
  { icon: Mail, label: 'ایمیل', value: CONTACT.email, href: `mailto:${CONTACT.email}` },
]

const fieldClass =
  'h-11 w-full rounded-md border border-white/15 bg-white/5 px-3 text-sm text-ink-foreground outline-none transition placeholder:text-ink-foreground/40 focus:border-accent'

export function Contact() {
  const [form, setForm] = useState({ name: '', company: '', phone: '', part: '', message: '' })
  const [error, setError] = useState<string | null>(null)

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    const message = validateRequester(form) ?? (clean(form.message, 600).length < 5 ? 'شرح نیاز فنی را وارد کنید' : null)
    setError(message)
    if (message) return
    window.location.href = buildMailto('درخواست مشاوره فنی', [
      ...requesterLines(form),
      `پارت‌نامبر: ${clean(form.part, 60) || '-'}`,
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

        <form onSubmit={submit} noValidate className="flex flex-col gap-3 rounded-lg border border-white/10 bg-white/[0.03] p-5 md:p-6">
          <h3 className="text-lg font-bold">فرم درخواست مشاوره</h3>
          <div className="grid gap-3 sm:grid-cols-2">
            <input className={fieldClass} placeholder="نام و نام خانوادگی" aria-label="نام و نام خانوادگی" autoComplete="name" maxLength={80} value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
            <input className={fieldClass} placeholder="نام شرکت" aria-label="نام شرکت" autoComplete="organization" maxLength={120} value={form.company} onChange={(e) => setForm({ ...form, company: e.target.value })} />
            <input className={`${fieldClass} text-left font-mono`} placeholder="09xx xxx xxxx" aria-label="شماره تماس" type="tel" dir="ltr" autoComplete="tel" maxLength={20} value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
            <input className={`${fieldClass} text-left font-mono`} placeholder="Part number" aria-label="پارت‌نامبر" dir="ltr" maxLength={60} value={form.part} onChange={(e) => setForm({ ...form, part: e.target.value })} />
          </div>
          <textarea
            className={`${fieldClass} h-32 resize-none py-2.5`}
            placeholder="شرح نیاز فنی: سیال، دما، فشار، اتصال فرآیندی، گواهی مورد نیاز..."
            aria-label="شرح نیاز فنی"
            maxLength={600}
            value={form.message}
            onChange={(e) => setForm({ ...form, message: e.target.value })}
          />
          {error && (
            <p role="alert" className="text-sm text-accent">
              {error}
            </p>
          )}
          <button type="submit" className="inline-flex h-12 items-center justify-center gap-2 rounded-md bg-accent font-bold text-accent-foreground transition hover:brightness-105">
            <Mail className="size-4" aria-hidden />
            آماده‌سازی ایمیل درخواست
          </button>
        </form>
      </div>
      <footer className="border-t border-white/10">
        <div className="mx-auto flex max-w-7xl flex-col justify-between gap-2 px-4 py-6 text-xs text-ink-foreground/50 md:flex-row md:px-8">
          <span>دراک — تأمین ابزار دقیق و اتوماسیون صنعتی</span>
          <span className="font-mono" dir="ltr">
            Tehran · Asaluyeh
          </span>
        </div>
      </footer>
    </section>
  )
}
