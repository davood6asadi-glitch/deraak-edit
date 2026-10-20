import Image from 'next/image'
import { ArrowLeft, Headset } from 'lucide-react'

const STATS = [
  { value: '+45', label: 'تجهیز فعال در کاتالوگ' },
  { value: '24h', label: 'تحویل اقلام موجود در تهران' },
  { value: 'SIL 3', label: 'تجهیزات دارای گواهی ایمنی' },
  { value: 'ATEX', label: 'مناسب مناطق پرخطر' },
]

export function Hero() {
  return (
    <section id="top" className="relative overflow-hidden bg-ink text-ink-foreground">
      <Image
        src="/hero-plant.png"
        alt=""
        fill
        priority
        sizes="100vw"
        className="object-cover opacity-35"
      />
      <div className="bg-grid absolute inset-0" aria-hidden />
      <div className="absolute inset-0 bg-gradient-to-l from-ink via-ink/85 to-ink/40" aria-hidden />

      <div className="relative mx-auto flex h-[600px] w-[1150px] max-w-7xl flex-col gap-12 px-4 pb-14 pt-16 md:px-8 md:pb-20 md:pt-24">
        <div className="flex max-w-2xl flex-col gap-6">
          <span className="inline-flex w-fit items-center gap-2 rounded-full border border-accent/40 bg-accent/10 px-3 py-1 font-mono text-xs text-accent" dir="ltr">
            <span className="size-1.5 rounded-full bg-accent" aria-hidden />
            INSTRUMENTATION · AUTOMATION · SAFETY
          </span>
          <h1 className="text-balance text-4xl font-extrabold leading-[1.25] md:text-6xl md:leading-[1.2]">
            ابزار دقیق صنعتی،
            <br />
            <span className="text-accent">از دیتاشیت تا تحویل در سایت</span>
          </h1>
          <p className="max-w-xl text-pretty leading-8 text-ink-foreground/75 md:text-lg">
            تأمین ترانسمیتر، سنسور و سیگنال کاندیشنر از برندهای معتبر اروپایی برای پالایشگاه، پتروشیمی، نیروگاه و صنایع
            دارویی، همراه با انتخاب فنی توسط مهندسین فرآیند.
          </p>
          <div className="flex flex-wrap gap-3">
            <a
              href="#catalog"
              className="inline-flex h-12 items-center gap-2 rounded-md bg-accent px-6 font-bold text-accent-foreground transition hover:brightness-105"
            >
              مشاهده کاتالوگ
              <ArrowLeft className="size-4" aria-hidden />
            </a>
            <a
              href="#contact"
              className="inline-flex h-12 items-center gap-2 rounded-md border border-white/20 px-6 font-medium transition hover:bg-white/5"
            >
              <Headset className="size-4" aria-hidden />
              مشاوره فنی
            </a>
          </div>
        </div>

        <dl className="grid grid-cols-2 overflow-hidden rounded-lg border border-white/10 bg-white/[0.03] md:grid-cols-4">
          {STATS.map((stat) => (
            <div key={stat.label} className="flex flex-col gap-1 border-white/10 p-5 [&:not(:last-child)]:border-l max-md:[&:nth-child(2)]:border-l-0 max-md:[&:nth-child(-n+2)]:border-b">
              <dt className="order-2 text-sm text-ink-foreground/65">{stat.label}</dt>
              <dd className="order-1 font-mono text-2xl font-bold text-ink-foreground" dir="ltr">
                {stat.value}
              </dd>
            </div>
          ))}
        </dl>
      </div>
    </section>
  )
}
