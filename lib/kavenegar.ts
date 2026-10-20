import crypto from 'crypto'
import { CONTACT } from './products'
import { clean, normalizeIranMobile, toEnglishDigits } from './request'

export const OTP_TTL_SECONDS = 120
export const OTP_LENGTH = 5

interface SignedOtpPayload {
  phone: string
  codeHash: string
  exp: number
}

interface KavenegarVerifyResponse {
  return?: {
    status: number
    message: string
  }
  entries?: Array<{
    messageid: number
    message: string
    status: number
    statustext: string
    sender: string
    receptor: string
    date: number
    cost: number
  }>
}

function getSecretKey(): string {
  return (
    process.env.OTP_SECRET ||
    process.env.KAVENEGAR_API_KEY ||
    'derak-industrial-b2b-otp-hmac-secret-2026'
  )
}

function hashOtpCode(phone: string, code: string, exp: number): string {
  return crypto
    .createHmac('sha256', getSecretKey())
    .update(`${phone}:${code}:${exp}`)
    .digest('hex')
}

export function generateOtpCode(): string {
  const min = 10000
  const max = 99999
  return String(crypto.randomInt(min, max + 1))
}

export function createOtpToken(phone: string, code: string): { token: string; expiresAt: number } {
  const normalizedPhone = normalizeIranMobile(phone)
  if (!normalizedPhone) {
    throw new Error('شماره موبایل نامعتبر است')
  }
  const exp = Date.now() + OTP_TTL_SECONDS * 1000
  const codeHash = hashOtpCode(normalizedPhone, code, exp)
  const payload: SignedOtpPayload = { phone: normalizedPhone, codeHash, exp }
  const encodedPayload = Buffer.from(JSON.stringify(payload), 'utf8').toString('base64url')
  const signature = crypto
    .createHmac('sha256', getSecretKey())
    .update(encodedPayload)
    .digest('base64url')

  return {
    token: `${encodedPayload}.${signature}`,
    expiresAt: exp,
  }
}

export function verifyOtpToken(
  phone: string,
  code: string,
  token: string,
): { valid: boolean; reason?: string } {
  const normalizedPhone = normalizeIranMobile(phone)
  const normalizedCode = toEnglishDigits(code).trim()

  if (!normalizedPhone) {
    return { valid: false, reason: 'شماره موبایل معتبر نیست' }
  }
  if (!/^\d{5}$/.test(normalizedCode)) {
    return { valid: false, reason: 'کد تایید باید ۵ رقم باشد' }
  }

  const parts = token.split('.')
  if (parts.length !== 2) {
    return { valid: false, reason: 'توکن اعتبارسنجی نامعتبر است؛ لطفاً مجدداً درخواست کد دهید' }
  }

  const [encodedPayload, providedSignature] = parts
  const expectedSignature = crypto
    .createHmac('sha256', getSecretKey())
    .update(encodedPayload)
    .digest('base64url')

  const sigBufferA = Buffer.from(providedSignature)
  const sigBufferB = Buffer.from(expectedSignature)
  if (sigBufferA.length !== sigBufferB.length || !crypto.timingSafeEqual(sigBufferA, sigBufferB)) {
    return { valid: false, reason: 'توکن اعتبارسنجی مخدوش است؛ لطفاً مجدداً کد دریافت کنید' }
  }

  let payload: SignedOtpPayload
  try {
    payload = JSON.parse(Buffer.from(encodedPayload, 'base64url').toString('utf8'))
  } catch {
    return { valid: false, reason: 'ساختار توکن نامعتبر است' }
  }

  if (Date.now() > payload.exp) {
    return { valid: false, reason: 'مهلت زمانی کد تایید به پایان رسیده است. لطفاً ارسال مجدد را بزنید' }
  }

  if (payload.phone !== normalizedPhone) {
    return { valid: false, reason: 'کد تایید با شماره موبایل وارد شده مطابقت ندارد' }
  }

  const expectedCodeHash = hashOtpCode(normalizedPhone, normalizedCode, payload.exp)
  const codeBufferA = Buffer.from(payload.codeHash)
  const codeBufferB = Buffer.from(expectedCodeHash)
  if (codeBufferA.length !== codeBufferB.length || !crypto.timingSafeEqual(codeBufferA, codeBufferB)) {
    return { valid: false, reason: 'کد تایید وارد شده اشتباه است' }
  }

  return { valid: true }
}

/**
 * پاک‌سازی و فرمت‌بندی مقادیر توکن برای متد Verify Lookup کاوه‌نگار
 * در کاوه‌نگار پارامترهای token، token2 و token3 نباید دارای فاصله (Space) باشند.
 */
export function sanitizeKavenegarToken(value: string, fallback = '-', maxLength = 30): string {
  const cleaned = clean(value, maxLength)
  if (!cleaned) return fallback
  return cleaned.replace(/\s+/g, '_')
}

export async function callKavenegarVerifyLookup(params: {
  receptor: string
  template: string
  token: string
  token2?: string
  token3?: string
}): Promise<{ ok: boolean; sandbox: boolean; status?: number; message?: string }> {
  const apiKey = process.env.KAVENEGAR_API_KEY?.trim()

  // در صورتی که کلید کاوه‌نگار هنوز در محیط تنظیم نشده باشد، حالت شبیه‌ساز توسعه فعال می‌شود
  if (!apiKey) {
    return {
      ok: true,
      sandbox: true,
      status: 200,
      message: 'ارسال در حالت آزمایشی (Sandbox) انجام شد',
    }
  }

  const endpoint = `https://api.kavenegar.com/v1/${encodeURIComponent(apiKey)}/verify/lookup.json`
  const searchParams = new URLSearchParams()
  searchParams.set('receptor', params.receptor)
  searchParams.set('template', params.template)
  searchParams.set('token', params.token)
  if (params.token2) searchParams.set('token2', params.token2)
  if (params.token3) searchParams.set('token3', params.token3)

  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        Accept: 'application/json',
      },
      body: searchParams.toString(),
      cache: 'no-store',
    })

    const data = (await response.json().catch(() => null)) as KavenegarVerifyResponse | null
    const status = data?.return?.status ?? response.status

    if (response.ok && status === 200) {
      return {
        ok: true,
        sandbox: false,
        status: 200,
        message: data?.return?.message || 'پیامک با موفقیت ارسال شد',
      }
    }

    return {
      ok: false,
      sandbox: false,
      status,
      message: mapKavenegarError(status, data?.return?.message),
    }
  } catch {
    return {
      ok: false,
      sandbox: false,
      status: 503,
      message: 'خطا در برقراری ارتباط با وب‌سرویس کاوه‌نگار؛ لطفاً اتصال یا تنظیمات را بررسی کنید',
    }
  }
}

function mapKavenegarError(status: number, rawMessage?: string): string {
  switch (status) {
    case 401:
    case 403:
      return 'کلید API کاوه‌نگار نامعتبر یا غیرفعال است'
    case 411:
      return 'شماره گیرنده پیامک معتبر نیست'
    case 414:
      return 'حجم درخواست‌ها بیش از حد مجاز است؛ چند لحظه دیگر تلاش کنید'
    case 418:
      return 'اعتبار حساب کاوه‌نگار کافی نیست'
    case 422:
      return 'داده‌های ارسالی به الگوی کاوه‌نگار شامل کاراکتر غیرمجاز است'
    case 424:
      return 'الگوی اعتبارسنجی (Template) مورد نظر در پنل کاوه‌نگار یافت نشد یا هنوز تایید نشده است'
    case 426:
      return 'استفاده از متد اعتبارسنجی نیازمند ارتقاء سرویس در پنل کاوه‌نگار است'
    case 431:
      return 'ساختار پارامترهای توکن در الگوی کاوه‌نگار صحیح نیست (نباید فاصله داشته باشد)'
    case 432:
      return 'پارامترهای الزامی الگو در کاوه‌نگار ارسال نشده‌اند'
    default:
      return rawMessage || `خطای وب‌سرویس کاوه‌نگار (کد ${status})`
  }
}

export async function sendUserOtpViaKavenegar(phone: string, otpCode: string) {
  const template = process.env.KAVENEGAR_OTP_TEMPLATE?.trim() || 'derak-verify'
  return callKavenegarVerifyLookup({
    receptor: phone,
    template,
    token: otpCode,
  })
}

export async function sendAdminConsultationViaKavenegar(input: {
  name: string
  company: string
  phone: string
  part: string
  message: string
  trackingCode: string
}) {
  const adminPhone =
    normalizeIranMobile(process.env.KAVENEGAR_ADMIN_PHONE || CONTACT.mobile) || CONTACT.mobile
  const template = process.env.KAVENEGAR_ADMIN_TEMPLATE?.trim() || 'derak-admin-lead'

  // token: شماره موبایل مشتری
  // token2: نام مشتری و نام شرکت (بدون فاصله برای سازگاری کامل با قوانین Verify Lookup کاوه‌نگار)
  // token3: پارت‌نامبر یا خلاصه نیاز فنی + کد پیگیری
  const requesterLabel = input.company
    ? `${input.name}-${input.company}`
    : input.name
  const partOrNeed = input.part
    ? `${input.part}-${input.message.slice(0, 18)}`
    : input.message.slice(0, 25)

  return callKavenegarVerifyLookup({
    receptor: adminPhone,
    template,
    token: input.phone,
    token2: sanitizeKavenegarToken(requesterLabel, 'مشتری', 30),
    token3: sanitizeKavenegarToken(partOrNeed, input.trackingCode, 30),
  })
}
