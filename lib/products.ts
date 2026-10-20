export type Category = 'instrumentation' | 'automation'

export interface Product {
  id: string
  model: string
  title: string
  brand: string
  category: Category
  status: string
  tag: string
  image: string
  params: { label: string; value: string }[]
  leadTime: string
  specs: { label: string; value: string }[]
  description: string
  destinationUrl: string
  isDemo?: boolean
}

export const CATEGORIES: { id: 'all' | Category; label: string }[] = [
  { id: 'all', label: 'همه تجهیزات' },
  { id: 'instrumentation', label: 'ابزار دقیق' },
  { id: 'automation', label: 'اتوماسیون' },
]

/**
 * داده‌های نمونه آزمایشی (Demonstration Data)
 * در محیط عملیاتی وردپرس، این اقلام از پایگاه داده ووکامرس فراخوانی می‌شوند.
 */
export const PRODUCTS: Product[] = [
  {
    id: 'pr-4116',
    model: 'PR 4116',
    title: 'ترانسمیتر دما یونیورسال',
    brand: 'PR electronics',
    category: 'instrumentation',
    status: 'نمونه کاتالوگ',
    tag: 'DIN RAIL',
    image: '/products/pr-4116.png',
    params: [
      { label: 'ورودی', value: 'RTD, TC, mV' },
      { label: 'خروجی', value: '4-20 mA' },
    ],
    leadTime: 'داده نمونه آزمایشی — استعلام وضعیت از واحد فروش',
    specs: [
      { label: 'نوع سیگنال خروجی', value: 'mA / VDC / Relay' },
      { label: 'محدوده تغذیه', value: '21.6...253 VAC / 19.2...300 VDC' },
      { label: 'نصب', value: 'ریل استاندارد DIN' },
    ],
    description:
      '[رکورد نمونه آزمایشی جهت نمایش ساختار کاتالوگ] مبدل سیگنال یونیورسال برای سنسورهای دما و مقاومت با قابلیت نصب روی ریل تابلویی.',
    destinationUrl: 'https://deraak.com/?s=PR+4116',
    isDemo: true,
  },
  {
    id: 'ifm-si5010',
    model: 'SI5010',
    title: 'فلو سوئیچ پایش جریان',
    brand: 'ifm electronic',
    category: 'instrumentation',
    status: 'نمونه کاتالوگ',
    tag: 'FLOW',
    image: '/products/ifm-si5010.png',
    params: [
      { label: 'بازه سنجش', value: '3..300 cm/s' },
      { label: 'متریال بدنه', value: '316L SS' },
    ],
    leadTime: 'داده نمونه آزمایشی — استعلام وضعیت از واحد فروش',
    specs: [
      { label: 'نوع خروجی', value: 'PNP / NPN' },
      { label: 'ولتاژ تغذیه', value: '19...36 VDC' },
      { label: 'نشانگر', value: 'آرایه LED وضعیت جریان' },
    ],
    description:
      '[رکورد نمونه آزمایشی جهت نمایش ساختار کاتالوگ] سنسور پایش جریان سیالات بر پایه اصل کالریمتری حرارتی با بدنه استنلس استیل.',
    destinationUrl: 'https://deraak.com/?s=SI5010',
    isDemo: true,
  },
  {
    id: 'eh-fmr53',
    model: 'FMR53 Micropilot',
    title: 'ترانسمیتر سطح راداری',
    brand: 'Endress+Hauser',
    category: 'instrumentation',
    status: 'نمونه کاتالوگ',
    tag: 'RADAR',
    image: '/products/eh-fmr53.png',
    params: [
      { label: 'فرکانس کاری', value: 'K-Band' },
      { label: 'کاربری', value: 'سنجش غیرتماسی سطح' },
    ],
    leadTime: 'داده نمونه آزمایشی — استعلام وضعیت از واحد فروش',
    specs: [
      { label: 'پروتکل ارتباطی', value: '4...20 mA HART' },
      { label: 'تغذیه', value: '2-Wire Loop Powered' },
      { label: 'نوع تجهیز', value: 'رادار فضای آزاد (Free-space)' },
    ],
    description:
      '[رکورد نمونه آزمایشی جهت نمایش ساختار کاتالوگ] ترانسمیتر سطح راداری غیرتماسی ویژه اندازه‌گیری پیوسته سطح مایعات در مخازن فرآیندی.',
    destinationUrl: 'https://deraak.com/?s=FMR53',
    isDemo: true,
  },
  {
    id: 'eh-cls13',
    model: 'CLS13 Condumax',
    title: 'سنسور رسانایی‌سنجی',
    brand: 'Endress+Hauser',
    category: 'instrumentation',
    status: 'نمونه کاتالوگ',
    tag: 'ANALYTICAL',
    image: '/products/eh-cls13.png',
    params: [
      { label: 'نوع سنسور', value: 'Conductivity' },
      { label: 'کاربرد', value: 'آب خالص و فرآیند' },
    ],
    leadTime: 'داده نمونه آزمایشی — استعلام وضعیت از واحد فروش',
    specs: [
      { label: 'فناوری سیگنال', value: 'Memosens / Analog' },
      { label: 'ساختار الکترود', value: 'دو الکترودی هم‌محور' },
    ],
    description:
      '[رکورد نمونه آزمایشی جهت نمایش ساختار کاتالوگ] سل رسانایی‌سنجی جهت پایش هدایت الکتریکی آب خالص و سیالات صنعتی.',
    destinationUrl: 'https://deraak.com/?s=CLS13',
    isDemo: true,
  },
  {
    id: 'pr-9106b',
    model: 'PR 9106B',
    title: 'ریپیتر و ایزولاتور HART',
    brand: 'PR electronics',
    category: 'automation',
    status: 'نمونه کاتالوگ',
    tag: 'ISOLATOR',
    image: '/products/pr-9106b.png',
    params: [
      { label: 'سیگنال', value: '4-20 mA / HART' },
      { label: 'نوع تجهیز', value: 'Barrier / Isolator' },
    ],
    leadTime: 'داده نمونه آزمایشی — استعلام وضعیت از واحد فروش',
    specs: [
      { label: 'پروتکل عبوری', value: 'HART Transparent' },
      { label: 'تغذیه نامی', value: '24 VDC' },
    ],
    description:
      '[رکورد نمونه آزمایشی جهت نمایش ساختار کاتالوگ] ایزولاتور و بریر انتقال سیگنال ترانسمیترهای دو سیمه در تابلوهای کنترل صنعتی.',
    destinationUrl: 'https://deraak.com/?s=PR+9106B',
    isDemo: true,
  },
  {
    id: 'pr-5116b',
    model: 'PR 5116B',
    title: 'فرستنده قابل برنامه‌ریزی',
    brand: 'PR electronics',
    category: 'automation',
    status: 'نمونه کاتالوگ',
    tag: 'CONVERTER',
    image: '/products/pr-5116b.png',
    params: [
      { label: 'ورودی', value: 'RTD / TC / mA / V' },
      { label: 'تغذیه', value: '24 VDC' },
    ],
    leadTime: 'داده نمونه آزمایشی — استعلام وضعیت از واحد فروش',
    specs: [
      { label: 'خروجی', value: 'Current / Voltage / Relay' },
      { label: 'نصب', value: 'DIN Rail' },
    ],
    description:
      '[رکورد نمونه آزمایشی جهت نمایش ساختار کاتالوگ] مبدل سیگنال قابل برنامه‌ریزی جهت ایزولاسیون و تبدیل سیگنال‌های آنالوگ صنعتی.',
    destinationUrl: 'https://deraak.com/?s=PR+5116B',
    isDemo: true,
  },
  {
    id: 'eh-fdu95',
    model: 'Prosonic FDU95',
    title: 'سنسور سطح اولتراسونیک',
    brand: 'Endress+Hauser',
    category: 'instrumentation',
    status: 'نمونه کاتالوگ',
    tag: 'ULTRASONIC',
    image: '/products/eh-fdu95.png',
    params: [
      { label: 'فناوری', value: 'Ultrasonic' },
      { label: 'کاربرد', value: 'سیلو و مواد فله' },
    ],
    leadTime: 'داده نمونه آزمایشی — استعلام وضعیت از واحد فروش',
    specs: [
      { label: 'ترانسمیتر سازگار', value: 'Prosonic FMU90' },
      { label: 'سنسور دما', value: 'داخلی جهت تصحیح زمان پرواز موج' },
    ],
    description:
      '[رکورد نمونه آزمایشی جهت نمایش ساختار کاتالوگ] سنسور سطح اولتراسونیک غیرتماسی برای اندازه‌گیری سطح مواد جامد فله، پودر و مایعات.',
    destinationUrl: 'https://deraak.com/?s=FDU95',
    isDemo: true,
  },
  {
    id: 'eh-fmp54',
    model: 'FMP54 Levelflex',
    title: 'لول ترانسمیتر موج هدایت‌شده',
    brand: 'Endress+Hauser',
    category: 'instrumentation',
    status: 'نمونه کاتالوگ',
    tag: 'GWR',
    image: '/products/eh-fmp54.png',
    params: [
      { label: 'فناوری', value: 'Guided Radar' },
      { label: 'پروتکل', value: 'HART / Fieldbus' },
    ],
    leadTime: 'داده نمونه آزمایشی — استعلام وضعیت از واحد فروش',
    specs: [
      { label: 'خروجی', value: '4...20 mA HART' },
      { label: 'تغذیه', value: '2-Wire Loop Powered' },
    ],
    description:
      '[رکورد نمونه آزمایشی جهت نمایش ساختار کاتالوگ] لول ترانسمیتر رادار موج هدایت‌شده (GWR) جهت اندازه‌گیری سطح مایعات در شرایط فرآیندی.',
    destinationUrl: 'https://deraak.com/?s=FMP54',
    isDemo: true,
  },
]

export const BRANDS = [
  { name: 'Endress+Hauser', desc: 'تجهیزات اندازه‌گیری سطح، فشار، دبی و آنالیتیکال', count: 4 },
  { name: 'PR electronics', desc: 'سیگنال کاندیشنر، مبدل دما و ایزولاتورهای تابلویی', count: 3 },
  { name: 'ifm electronic', desc: 'سنسورهای صنعتی پایش جریان، فشار و موقعیت', count: 1 },
  { name: 'WIKA', desc: 'تجهیزات اندازه‌گیری فشار و دمای فرآیندی', count: 0 },
  { name: 'Siemens', desc: 'اتوماسیون فرآیند و ابزار دقیق صنعتی', count: 0 },
]

export const CONTACT = {
  phone: '02191693022',
  phoneLabel: '021 - 9169 3022',
  mobile: '09398058002',
  mobileLabel: '0939 805 8002',
  email: 'info@deraak-process.com',
  telegramSales: 'deraak_sales',
  telegramProcess: 'deraak_process',
}
