import type { MetadataRoute } from 'next'

export default function manifest(): MetadataRoute.Manifest {
  return {
    id: '/',
    name: 'دراک — کاتالوگ ابزار دقیق و اتوماسیون صنعتی',
    short_name: 'دراک کاتالوگ',
    description:
      'کاتالوگ فنی تجهیزات ابزار دقیق، سیگنال کاندیشنر و اتوماسیون صنعتی با قابلیت مرور آفلاین و ثبت لیست استعلام.',
    start_url: '/',
    scope: '/',
    display: 'standalone',
    dir: 'rtl',
    lang: 'fa',
    background_color: '#00143B',
    theme_color: '#008DFF',
    icons: [
      {
        src: '/icon-192.png',
        sizes: '192x192',
        type: 'image/png',
        purpose: 'any',
      },
      {
        src: '/icon-512.png',
        sizes: '512x512',
        type: 'image/png',
        purpose: 'any',
      },
      {
        src: '/icon-maskable-512.png',
        sizes: '512x512',
        type: 'image/png',
        purpose: 'maskable',
      },
    ],
  }
}
