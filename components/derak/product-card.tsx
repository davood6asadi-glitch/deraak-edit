'use client'

import Image from 'next/image'
import { FileText, Plus } from 'lucide-react'
import type { Product } from '@/lib/products'
import { useInquiry } from './inquiry-provider'

export function ProductCard({ product, onOpen }: { product: Product; onOpen: (p: Product) => void }) {
  const { add } = useInquiry()

  return (
    <article className="group flex flex-col overflow-hidden rounded-lg border bg-card transition hover:border-primary/40 hover:shadow-lg hover:shadow-primary/5">
      <button
        type="button"
        onClick={() => onOpen(product)}
        className="relative aspect-[4/3] overflow-hidden bg-muted"
      >
        <Image
          src={product.image}
          alt={`${product.title} مدل ${product.model}`}
          fill
          sizes="(min-width: 1024px) 25vw, (min-width: 640px) 50vw, 100vw"
          className="object-cover transition duration-500 group-hover:scale-105"
        />
        <span className="absolute right-3 top-3 rounded bg-card/95 px-2 py-1 text-xs font-medium text-foreground shadow-sm">
          {product.status}
        </span>
        <span className="absolute bottom-3 left-3 rounded bg-ink/85 px-2 py-1 font-mono text-[10px] tracking-wider text-ink-foreground" dir="ltr">
          {product.tag}
        </span>
      </button>

      <div className="flex flex-1 flex-col gap-4 p-4">
        <div className="flex flex-col gap-1">
          <span className="font-mono text-xs text-muted-foreground" dir="ltr">
            {product.brand}
          </span>
          <h3 className="font-bold leading-7">{product.title}</h3>
          <span className="font-mono text-sm font-semibold text-primary" dir="ltr">
            {product.model}
          </span>
        </div>

        <dl className="grid grid-cols-2 overflow-hidden rounded-md border text-sm">
          {product.params.map((param, i) => (
            <div key={param.label} className={`flex flex-col gap-0.5 p-2.5 ${i === 0 ? 'border-l' : ''}`}>
              <dt className="text-xs text-muted-foreground">{param.label}</dt>
              <dd className="font-mono text-[13px] font-medium" dir="ltr">
                {param.value}
              </dd>
            </div>
          ))}
        </dl>

        <div className="mt-auto flex gap-2">
          <button
            type="button"
            onClick={() => onOpen(product)}
            className="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-md border text-sm font-medium transition hover:bg-muted"
          >
            <FileText className="size-4" aria-hidden />
            دیتاشیت
          </button>
          <button
            type="button"
            onClick={() => add(product)}
            className="inline-flex h-10 items-center justify-center gap-1.5 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
          >
            <Plus className="size-4" aria-hidden />
            استعلام
          </button>
        </div>
      </div>
    </article>
  )
}
