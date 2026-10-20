import { ClipboardCheck, FileSpreadsheet, PackageCheck } from 'lucide-react'
import { BRANDS } from '@/lib/products'

const STEPS = [
  { icon: FileSpreadsheet, title: 'ارسال لیست تجهیزات', text: 'اقلام را از کاتالوگ انتخاب کنید یا پارت‌نامبرها را مستقیم بفرستید.' },
  { icon: ClipboardCheck, title: 'بررسی فنی', text: 'مهندسین فرآیند سازگاری، گواهی‌ها و شرایط کاری را تأیید می‌کنند.' },
  { icon: PackageCheck, title: 'پیش‌فاکتور و تحویل', text: 'پیش‌فاکتور رسمی با زمان تحویل دقیق و مدارک کالیبراسیون صادر می‌شود.' },
]

export function BrandsAndProcess() {
  return (
    <>
      <section id="brands" className="scroll-mt-20 border-y bg-card">
        <div className="mx-auto max-w-7xl px-4 py-16 md:px-8 md:py-20">
          <span className="font-mono text-xs tracking-widest text-primary" dir="ltr">
            02 / BRANDS
          </span>
          <h2 className="mt-2 text-3xl font-extrabold md:text-4xl">برندهای تحت پوشش</h2>
          <ul className="mt-10 grid gap-px overflow-hidden rounded-lg border bg-border sm:grid-cols-2 lg:grid-cols-5">
            {BRANDS.map((brand) => (
              <li key={brand.name} className="flex flex-col gap-3 bg-card p-5">
                <span className="font-mono text-base font-bold" dir="ltr">
                  {brand.name}
                </span>
                <span className="text-sm leading-6 text-muted-foreground">{brand.desc}</span>
                <span className="mt-auto font-mono text-xs text-primary">{brand.count} مدل</span>
              </li>
            ))}
          </ul>
        </div>
      </section>

      <section id="process" className="mx-auto max-w-7xl scroll-mt-20 px-4 py-16 md:px-8 md:py-24">
        <span className="font-mono text-xs tracking-widest text-primary" dir="ltr">
          03 / PROCESS
        </span>
        <h2 className="mt-2 text-3xl font-extrabold md:text-4xl">روند استعلام قیمت</h2>
        <ol className="mt-10 grid gap-5 md:grid-cols-3">
          {STEPS.map((step, i) => (
            <li key={step.title} className="flex flex-col gap-4 rounded-lg border bg-card p-6">
              <div className="flex items-center justify-between">
                <span className="flex size-11 items-center justify-center rounded-md bg-primary/10 text-primary">
                  <step.icon className="size-5" aria-hidden />
                </span>
                <span className="font-mono text-3xl font-bold text-border" aria-hidden>
                  0{i + 1}
                </span>
              </div>
              <h3 className="text-lg font-bold">{step.title}</h3>
              <p className="leading-7 text-muted-foreground">{step.text}</p>
            </li>
          ))}
        </ol>
      </section>
    </>
  )
}
