import { NextRequest, NextResponse } from 'next/server'
import {
  createOtpToken,
  generateOtpCode,
  OTP_TTL_SECONDS,
  sendUserOtpViaKavenegar,
} from '@/lib/kavenegar'
import { normalizeIranMobile, validateConsultationInput } from '@/lib/request'

export async function POST(req: NextRequest) {
  try {
    const body = await req.json().catch(() => null)
    if (!body || typeof body !== 'object') {
      return NextResponse.json(
        { ok: false, error: 'اطلاعات ارسالی نامعتبر است' },
        { status: 400 },
      )
    }

    const name = String(body.name ?? '')
    const company = String(body.company ?? '')
    const phone = String(body.phone ?? '')
    const part = String(body.part ?? '')
    const message = String(body.message ?? '')

    const validationError = validateConsultationInput({
      name,
      company,
      phone,
      part,
      message,
    })

    if (validationError) {
      return NextResponse.json({ ok: false, error: validationError }, { status: 400 })
    }

    const normalizedPhone = normalizeIranMobile(phone)!
    const otpCode = generateOtpCode()
    const { token, expiresAt } = createOtpToken(normalizedPhone, otpCode)

    const smsResult = await sendUserOtpViaKavenegar(normalizedPhone, otpCode)
    if (!smsResult.ok) {
      return NextResponse.json(
        {
          ok: false,
          error: smsResult.message || 'خطا در ارسال پیامک کد تایید از طریق کاوه‌نگار',
        },
        { status: 502 },
      )
    }

    return NextResponse.json({
      ok: true,
      phone: normalizedPhone,
      otpToken: token,
      expiresIn: OTP_TTL_SECONDS,
      expiresAt,
      sandbox: smsResult.sandbox,
      // در صورتی که کلید KAVENEGAR_API_KEY هنوز در محیط تنظیم نشده باشد، کد آزمایشی برگردانده می‌شود تا فلو کامل تست شود
      sandboxCode: smsResult.sandbox ? otpCode : undefined,
    })
  } catch {
    return NextResponse.json(
      { ok: false, error: 'خطای داخلی سرور هنگام ارسال کد تایید' },
      { status: 500 },
    )
  }
}
