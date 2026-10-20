'use client'

import { useMemo, useState } from 'react'
import { Search, X } from 'lucide-react'
import { BRANDS, CATEGORIES, PRODUCTS, type Category, type Product } from '@/lib/products'
import { cn } from '@/lib/utils'
import { ProductCard } from './product-card'
import { ProductDialog } from './product-dialog'

export function Catalog() {
  const [query, setQuery] = useState('')
  const [category, setCategory] = useState<'all' | Category>('all')
  const [brands, setBrands] = useState<string[]>([])
  const [active, setActive] = useState<Product | null>(null)

  const results = useMemo(() => {
    const q = query.trim().toLowerCase()
    return PRODUCTS.filter((p) => {
      if (category !== 'all' && p.category !== category) return false
      if (brands.length && !brands.includes(p.brand)) return false
      if (!q) return true
      return [p.title, p.model, p.brand, p.tag].some((field) => field.toLowerCase().includes(q))
    })
  }, [query, category, brands])

  const toggleBrand = (name: string) =>
    setBrands((prev) => (prev.includes(name) ? prev.filter((b) => b !== name) : [...prev, name]))

  const reset = () => {
    setQuery('')
    setCategory('all')
    setBrands([])
  }

  return (
    <section id="catalog" className="mx-auto max-w-7xl scroll-mt-20 px-4 py-16 md:px-8 md:py-24">
      <div className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div className="flex flex-col gap-2">
          <span className="font-mono text-xs tracking-widest text-primary" dir="ltr">
            01 / CATALOG
          </span>
          <h2 className="text-3xl font-extrabold md:text-4xl">کاتالوگ تجهیزات</h2>
          <p className="text-muted-foreground">جست‌وجو بر اساس نام، شماره مدل یا برند</p>
        </div>
        <p className="font-mono text-sm text-muted-foreground" aria-live="polite">
          {results.length} / {PRODUCTS.length}
        </p>
      </div>

      <div className="mt-8 flex flex-col gap-4 rounded-lg border bg-card p-4">
        <label className="relative block">
          <span className="sr-only">جست‌وجو در کاتالوگ</span>
          <Search className="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" aria-hidden />
          <input
            type="search"
            value={query}
            maxLength={80}
            onChange={(e) => setQuery(e.target.value)}
            placeholder="مثلاً FMR53 یا ترانسمیتر دما"
            className="h-12 w-full rounded-md border border-input bg-background pr-10 pl-4 outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15"
          />
        </label>

        <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
          <div className="flex flex-wrap gap-2" role="group" aria-label="دسته‌بندی">
            {CATEGORIES.map((c) => (
              <button
                key={c.id}
                type="button"
                aria-pressed={category === c.id}
                onClick={() => setCategory(c.id)}
                className={cn(
                  'h-9 rounded-md px-4 text-sm font-medium transition',
                  category === c.id ? 'bg-ink text-ink-foreground' : 'bg-muted text-foreground hover:bg-secondary',
                )}
              >
                {c.label}
              </button>
            ))}
          </div>
          <div className="flex flex-wrap gap-2" role="group" aria-label="برند">
            {BRANDS.slice(0, 3).map((b) => (
              <button
                key={b.name}
                type="button"
                aria-pressed={brands.includes(b.name)}
                onClick={() => toggleBrand(b.name)}
                className={cn(
                  'h-9 rounded-md border px-3 font-mono text-xs transition',
                  brands.includes(b.name)
                    ? 'border-primary bg-primary/10 text-primary'
                    : 'text-muted-foreground hover:border-primary/40 hover:text-foreground',
                )}
                dir="ltr"
              >
                {b.name}
              </button>
            ))}
          </div>
        </div>
      </div>

      {results.length > 0 ? (
        <div className="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
          {results.map((p) => (
            <ProductCard key={p.id} product={p} onOpen={setActive} />
          ))}
        </div>
      ) : (
        <div className="mt-8 flex flex-col items-center gap-3 rounded-lg border border-dashed py-16 text-center">
          <p className="font-bold">تجهیزی با این مشخصات پیدا نشد</p>
          <p className="text-sm text-muted-foreground">پارت‌نامبر را برای استعلام مستقیم به کارشناسان ارسال کنید.</p>
          <button
            type="button"
            onClick={reset}
            className="mt-2 inline-flex h-9 items-center gap-1.5 rounded-md border px-4 text-sm transition hover:bg-muted"
          >
            <X className="size-4" aria-hidden />
            حذف فیلترها
          </button>
        </div>
      )}

      <ProductDialog product={active} onClose={() => setActive(null)} />
    </section>
  )
}
