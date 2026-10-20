'use client'

import { ClipboardList, Phone } from 'lucide-react'
import { CONTACT } from '@/lib/products'
import { useInquiry } from './inquiry-provider'

const NAV = [
  { href: '#catalog', label: 'کاتالوگ' },
  { href: '#brands', label: 'برندها' },
  { href: '#process', label: 'روند استعلام' },
  { href: '#contact', label: 'تماس' },
]

export function SiteHeader() {
  const { items, setOpen } = useInquiry()

  return (
    <header className="sticky top-0 z-40 border-b border-white/10 bg-ink/95 text-ink-foreground backdrop-blur">
      <div className="mx-auto flex h-16 max-w-7xl items-center gap-6 px-4 md:px-8">
        <a href="#top" className="flex items-center gap-2.5">
          <span className="flex size-9 items-center justify-center rounded-md bg-accent font-mono text-sm font-bold text-accent-foreground">
            DR
          </span>
          <span className="flex flex-col leading-tight">
            <span className="text-base font-extrabold">دراک</span>
            <span className="font-mono text-[10px] tracking-widest text-ink-foreground/60" dir="ltr">
              PROCESS · CONTROL
            </span>
          </span>
        </a>

        <nav aria-label="ناوبری اصلی" className="hidden flex-1 md:block">
          <ul className="flex items-center gap-1">
            {NAV.map((item) => (
              <li key={item.href}>
                <a
                  href={item.href}
                  className="rounded-md px-3 py-2 text-sm text-ink-foreground/75 transition hover:bg-white/5 hover:text-ink-foreground"
                >
                  {item.label}
                </a>
              </li>
            ))}
          </ul>
        </nav>

        <div className="mr-auto flex items-center gap-2 md:mr-0">
          <a
            href={`tel:${CONTACT.phone}`}
            className="hidden items-center gap-2 rounded-md px-3 py-2 font-mono text-sm text-ink-foreground/80 transition hover:text-ink-foreground lg:flex"
            dir="ltr"
          >
            <Phone className="size-4" aria-hidden />
            {CONTACT.phoneLabel}
          </a>
          <button
            type="button"
            onClick={() => setOpen(true)}
            className="relative inline-flex h-10 items-center gap-2 rounded-md bg-accent px-4 text-sm font-bold text-accent-foreground transition hover:brightness-105"
          >
            <ClipboardList className="size-4" aria-hidden />
            لیست استعلام
            <span
              className="flex min-w-6 items-center justify-center rounded bg-accent-foreground px-1.5 font-mono text-xs text-accent"
              aria-label={`${items.length} قلم`}
            >
              {items.length}
            </span>
          </button>
        </div>
      </div>
    </header>
  )
}
