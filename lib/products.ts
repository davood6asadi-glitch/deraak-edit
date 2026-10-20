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
}

export const CATEGORIES: { id: 'all' | Category; label: string }[] = [
  { id: 'all', label: 'همه تجهیزات' },
  { id: 'instrumentation', label: 'ابزار دقیق' },
  { id: 'automation', label: 'اتوماسیون' },
]

export const PRODUCTS: Product[] = [
  {
    id: 'pr-4116',
    model: 'PR 4116',
    title: 'ترانسمیتر دما یونیورسال',
    brand: 'PR electronics',
    category: 'instrumentation',
    status: 'موجود',
    tag: 'DIN RAIL',
    image: '/products/pr-4116.png',
    params: [
      { label: 'سنسور', value: 'RTD, TC, mV' },
      { label: 'خروجی', value: '4-20 mA' },
    ],
    leadTime: 'موجود در انبار تهران - تحویل فوری',
    specs: [
      { label: 'پروتکل', value: 'mA / VDC / Relay Out' },
      { label: 'تغذیه', value: '21.6...253 VAC / 19.2...300 VDC' },
      { label: 'دقت', value: 'بهتر از 0.05% بازه' },
      { label: 'گواهی', value: 'SIL 2 / UL / DNV Marine' },
      { label: 'دمای کاری', value: '-20°C … +60°C' },
    ],
    description:
      'مبدل یونیورسال سیگنال برای انواع ترموکوپل‌ها و سنسورهای RTD (Pt100, Ni100). مجهز به ایزولاسیون گالوانیک 2.3 kVAC چهارطرفه و پشتیبانی از ماژول نمایشگر جداشونده ۴۵۰۱ برای تنظیم سریع در سایت.',
  },
  {
    id: 'ifm-si5010',
    model: 'SI5010',
    title: 'فلو سوئیچ پایش جریان',
    brand: 'ifm electronic',
    category: 'instrumentation',
    status: 'انبار تهران',
    tag: 'FLOW',
    image: '/products/ifm-si5010.png',
    params: [
      { label: 'محدوده', value: '3..300 cm/s' },
      { label: 'بدنه', value: '316L SS' },
    ],
    leadTime: 'تحویل ۲۴ ساعته در تهران',
    specs: [
      { label: 'خروجی', value: 'PNP / NPN NO/NC' },
      { label: 'تغذیه', value: '19...36 VDC' },
      { label: 'فشار', value: 'تا 30 bar' },
      { label: 'گواهی', value: 'IP67 / CE / cULus' },
      { label: 'دمای کاری', value: '-25°C … +80°C' },
    ],
    description:
      'سنسور نظارت بر جریان مایعات و گازها بر اساس کالریمتری حرارتی، با بدنه یکپارچه استیل 316L و نشانگر LED چندرنگ برای نمایش وضعیت جریان.',
  },
  {
    id: 'eh-fmr53',
    model: 'FMR53 Micropilot',
    title: 'ترانسمیتر سطح راداری',
    brand: 'Endress+Hauser',
    category: 'instrumentation',
    status: 'تحویل ۴۸ ساعته',
    tag: 'RADAR',
    image: '/products/eh-fmr53.png',
    params: [
      { label: 'فرکانس', value: '26 GHz' },
      { label: 'برد', value: '40 m' },
    ],
    leadTime: 'موجود در انبار تهران، ارسال ۴۸ ساعته به عسلویه',
    specs: [
      { label: 'پروتکل', value: '4...20 mA HART / Profibus PA' },
      { label: 'تغذیه', value: 'Loop powered 2-wire 24 VDC' },
      { label: 'دقت', value: '±6 mm' },
      { label: 'گواهی', value: 'ATEX Ex ia / IECEx / SIL 2' },
      { label: 'دمای کاری', value: '-40°C … +200°C' },
      { label: 'فشار', value: 'Vac...40 bar' },
    ],
    description:
      'ترانسمیتر سطح راداری غیرتماسی برای مایعات و جامدات فله در مخازن، راکتورها و سیلوها با آنتن هورن و الگوریتم Multi-echo Tracking.',
  },
  {
    id: 'eh-cls13',
    model: 'CLS13 Condumax',
    title: 'سنسور رسانایی‌سنجی',
    brand: 'Endress+Hauser',
    category: 'instrumentation',
    status: 'موجود',
    tag: 'ANALYTICAL',
    image: '/products/eh-cls13.png',
    params: [
      { label: 'ویژگی', value: 'Autoclavable' },
      { label: 'الکترود', value: 'Platinum' },
    ],
    leadTime: 'موجود در انبار لاله‌زار',
    specs: [
      { label: 'خروجی', value: 'Memosens / Analog' },
      { label: 'سل ثابت', value: 'k = 0.01 cm⁻¹ / ±1%' },
      { label: 'گواهی', value: 'EHEDG / FDA / USP Class VI' },
      { label: 'فشار', value: '12 bar @ 150 °C' },
    ],
    description:
      'سل رسانایی دو الکترودی بهداشتی برای آب خالص، WFI و صنایع داروسازی با الکترودهای پلاتین و اتصال تری‌کلمپ استیل 316L.',
  },
  {
    id: 'pr-9106b',
    model: 'PR 9106B',
    title: 'ریپیتر و ایزولاتور HART',
    brand: 'PR electronics',
    category: 'automation',
    status: 'سفارشی',
    tag: 'SIL 2 / SIL 3',
    image: '/products/pr-9106b.png',
    params: [
      { label: 'ایزولاسیون', value: '2.6 kVAC' },
      { label: 'استاندارد', value: 'ATEX / IECEx' },
    ],
    leadTime: 'تحویل سفارشی ۱۵ روز کاری',
    specs: [
      { label: 'پروتکل', value: 'HART Transparent 2-Way' },
      { label: 'تغذیه', value: '19.2...31.2 VDC' },
      { label: 'گواهی', value: 'SIL 2 / SIL 3 / ATEX Zone 0/1' },
    ],
    description:
      'بریر ایزولاتور دوکاناله ایمنی ذاتی برای تغذیه و انتقال سیگنال 4-20 mA ترانسمیترهای دوسیمه در مناطق پرخطر Zone 0 و Zone 1.',
  },
  {
    id: 'pr-5116b',
    model: 'PR 5116B',
    title: 'فرستنده قابل برنامه‌ریزی',
    brand: 'PR electronics',
    category: 'automation',
    status: 'موجود',
    tag: 'CONVERTER',
    image: '/products/pr-5116b.png',
    params: [
      { label: 'ورودی', value: 'RTD / Ω' },
      { label: 'تغذیه', value: '24 VDC' },
    ],
    leadTime: 'موجود در انبار تهران',
    specs: [
      { label: 'خروجی', value: 'V/mA / Relay' },
      { label: 'تغذیه', value: '24 VDC ±20%' },
      { label: 'گواهی', value: 'UL / CSA / CE' },
    ],
    description:
      'محاسبه‌گر و مبدل سیگنال برنامه‌پذیر برای جمع، تفریق، ضرب و رادیکال سیگنال‌ها و خطی‌سازی مخازن استوانه‌ای افقی.',
  },
  {
    id: 'eh-fdu95',
    model: 'Prosonic FDU95',
    title: 'سنسور سطح اولتراسونیک',
    brand: 'Endress+Hauser',
    category: 'instrumentation',
    status: 'سفارش ویژه',
    tag: 'ULTRASONIC',
    image: '/products/eh-fdu95.png',
    params: [
      { label: 'برد', value: '45 m' },
      { label: 'ضدانفجار', value: 'Ex-proof' },
    ],
    leadTime: 'سفارش مهندسی، همراه مدارک تست بازرسی',
    specs: [
      { label: 'سازگاری', value: 'FMU90 / FMU95' },
      { label: 'جبران‌سازی', value: 'سنسور دمای داخلی' },
      { label: 'گواهی', value: 'ATEX Ex ta/tb IIIC / IECEx' },
    ],
    description:
      'سنسور اولتراسونیک برای سیلوهای بلند پودر، سیمان، گرانول و غلات تا ۴۵ متر با گرم‌کن خودکار ضد یخ‌زدگی.',
  },
  {
    id: 'eh-fmp54',
    model: 'FMP54 Levelflex',
    title: 'لول ترانسمیتر موج هدایت‌شده',
    brand: 'Endress+Hauser',
    category: 'instrumentation',
    status: 'انبار تهران',
    tag: 'GWR',
    image: '/products/eh-fmp54.png',
    params: [
      { label: 'فشار', value: '400 bar' },
      { label: 'دما', value: '450 °C' },
    ],
    leadTime: 'موجود در انبار تهران',
    specs: [
      { label: 'پروتکل', value: '4...20 mA HART / FF' },
      { label: 'تغذیه', value: '2-Wire Loop Powered' },
      { label: 'دقت', value: '±2 mm' },
      { label: 'گواهی', value: 'ATEX / IECEx / SIL 2 / SIL 3' },
      { label: 'دمای کاری', value: '-196°C … +450°C' },
    ],
    description:
      'لول ترانسمیتر رادار هدایت‌شده برای شرایط فرآیندی حاد در پتروشیمی، بویلرهای فشار بالا و ستون‌های تقطیر.',
  },
]

export const BRANDS = [
  { name: 'Endress+Hauser', desc: 'اندازه‌گیری سطح، فشار و دبی', count: 18 },
  { name: 'PR electronics', desc: 'سیگنال کاندیشنر و بریر ایمنی ذاتی', count: 11 },
  { name: 'ifm electronic', desc: 'سنسورهای صنعتی و پایش وضعیت', count: 9 },
  { name: 'WIKA', desc: 'تجهیزات اندازه‌گیری فشار و دما', count: 5 },
  { name: 'Siemens', desc: 'اتوماسیون فرآیند و درایو', count: 2 },
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
