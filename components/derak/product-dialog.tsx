'use client'

import Image from 'next/image'
import { ExternalLink, Info, Plus } from 'lucide-react'
import type { Product } from '@/lib/products'
import { useInquiry } from './inquiry-provider'
import { Modal } from './modal'

export function ProductDialog({ product, onClose }: { product: Product | null; onClose: () => void }) {
  const { add } = useInquiry()

  return (
    <Modal open={product !== null} onClose={onClose} title={product ? `مشخصات فنی ${product.model}` : 'مشخصات فنی'}>
      {product && (
        <div className="grid md:grid-cols-[1fr_1.2fr]">
          <div className="relative aspect-square bg-muted md:aspect-auto">
            <Image
              src={product.image}
              alt={product.title}
              fill
              sizes="(min-width: 768px) 420px, 100vw"
              className="object-cover"
              referrerPolicy="no-referrer"
            />
          </div>
          <div className="flex flex-col gap-5 p-5 md:p-6">
            <div className="flex flex-col gap-1">
              <span className="text-xs text-muted-foreground" dir="ltr">
                {product.brand} · {product.tag}
              </span>
              <h3 className="text-xl font-extrabold">{product.title}</h3>
              <span className="text-lg font-semibold text-primary" dir="ltr">
                {product.model}
              </span>
            </div>
            <p className="text-pretty text-sm leading-7 text-muted-foreground">{product.description}</p>

            <table className="w-full text-sm">
              <caption className="sr-only">مشخصات فنی</caption>
              <tbody>
                {product.specs.map((spec) => (
                  <tr key={spec.label} className="border-b last:border-0">
                    <th scope="row" className="w-36 py-2.5 text-right font-normal text-muted-foreground">
                      {spec.label}
                    </th>
                    <td className="py-2.5 text-[13px] font-medium" dir="ltr">
                      <span className="block text-right">{spec.value}</span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>

            <p className="flex items-start gap-2 rounded-md bg-muted px-3 py-2.5 text-xs text-muted-foreground">
              <Info className="mt-0.5 size-4 shrink-0 text-primary" aria-hidden />
              {product.leadTime}
            </p>

            <div className="flex flex-col gap-2.5 sm:flex-row">
              <button
                type="button"
                onClick={() => {
                  add(product)
                  onClose()
                }}
                className="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-md bg-primary px-4 font-bold text-primary-foreground transition hover:bg-primary/90"
              >
                <Plus className="size-4" aria-hidden />
                افزودن به لیست استعلام
              </button>

              {product.destinationUrl && (
                <a
                  href={product.destinationUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-flex h-11 items-center justify-center gap-2 rounded-md border border-primary/30 bg-card px-4 text-xs font-bold text-primary transition hover:bg-primary/5"
                >
                  <span>مشاهده در deraak.com</span>
                  <ExternalLink className="size-3.5" aria-hidden />
                </a>
              )}
            </div>
          </div>
        </div>
      )}
    </Modal>
  )
}
