import { BrandsAndProcess } from '@/components/derak/brands-process'
import { Catalog } from '@/components/derak/catalog'
import { Contact } from '@/components/derak/contact'
import { Hero } from '@/components/derak/hero'
import { InquiryDrawer } from '@/components/derak/inquiry-drawer'
import { InquiryProvider } from '@/components/derak/inquiry-provider'
import { SiteHeader } from '@/components/derak/site-header'
import { Toast } from '@/components/derak/toast'

export default function Page() {
  return (
    <InquiryProvider>
      <SiteHeader />
      <main>
        <Hero />
        <Catalog />
        <BrandsAndProcess />
        <Contact />
      </main>
      <InquiryDrawer />
      <Toast />
    </InquiryProvider>
  )
}
