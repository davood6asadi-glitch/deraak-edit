import { Analytics } from '@vercel/analytics/next'
import type { Metadata, Viewport } from 'next'
import './globals.css'

export const metadata: Metadata = {
  title: 'دراک | ابزار دقیق و اتوماسیون صنعتی',
  description:
    'تأمین ابزار دقیق، سیگنال کاندیشنر و تجهیزات اتوماسیون صنعتی از برندهای Endress+Hauser، PR electronics و ifm با مشاوره فنی و استعلام سریع.',
  generator: 'deraak-b2b',
  appleWebApp: {
    capable: true,
    statusBarStyle: 'black-translucent',
    title: 'دراک کاتالوگ',
  },
  icons: {
    icon: [
      { url: '/icon-light-32x32.png', media: '(prefers-color-scheme: light)' },
      { url: '/icon-dark-32x32.png', media: '(prefers-color-scheme: dark)' },
      { url: '/icon.svg', type: 'image/svg+xml' },
    ],
    apple: '/apple-touch-icon.png',
  },
}

export const viewport: Viewport = {
  colorScheme: 'light dark',
  themeColor: '#00143B',
  width: 'device-width',
  initialScale: 1,
}

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode
}>) {
  return (
    <html lang="fa" dir="rtl">
      <body className="font-sans antialiased">
        {children}
        {process.env.NODE_ENV === 'production' && <Analytics />}
      </body>
    </html>
  )
}
