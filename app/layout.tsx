import { Analytics } from '@vercel/analytics/next'
import type { Metadata, Viewport } from 'next'
import { Vazirmatn, JetBrains_Mono } from 'next/font/google'
import './globals.css'

const vazirmatn = Vazirmatn({ subsets: ['arabic', 'latin'], variable: '--font-vazirmatn' })
const jetbrains = JetBrains_Mono({ subsets: ['latin'], variable: '--font-jetbrains' })

export const metadata: Metadata = {
  title: 'دراک | ابزار دقیق و اتوماسیون صنعتی',
  description:
    'تأمین ابزار دقیق، سیگنال کاندیشنر و تجهیزات اتوماسیون صنعتی از برندهای Endress+Hauser، PR electronics و ifm با مشاوره فنی و استعلام سریع.',
  generator: 'v0.app',
  icons: {
    icon: [
      { url: '/icon-light-32x32.png', media: '(prefers-color-scheme: light)' },
      { url: '/icon-dark-32x32.png', media: '(prefers-color-scheme: dark)' },
      { url: '/icon.svg', type: 'image/svg+xml' },
    ],
    apple: '/apple-icon.png',
  },
}

export const viewport: Viewport = {
  colorScheme: 'light',
  themeColor: '#081631',
}

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode
}>) {
  return (
    <html lang="fa" dir="rtl" className={`${vazirmatn.variable} ${jetbrains.variable}`}>
      <body className="antialiased">
        {children}
        {process.env.NODE_ENV === 'production' && <Analytics />}
      </body>
    </html>
  )
}
