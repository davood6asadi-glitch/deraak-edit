import { NextRequest, NextResponse } from 'next/server'
import {
  sendAdminConsultationViaKavenegar,
  verifyOtpToken,
} from '@/lib/kavenegar'
import {
  clean,
  normalizeIranMobile,
  validateConsultationInput,
} from '@/lib/request'

export async function POST(req: NextRequest) {
  try {
    const body = await req.json().catch(() => null)
    if (!body || typeof body !== 'object') {
      return NextResponse.json(
        { ok: false, error: 'اطلاعات ارسالی نامعتبر است' },
        { status: 400 },
      )
    }

    const name = clean(String(body.name ?? ''), 80)
    const company = clean(String(body.company ?? ''), 120)
    const phone = String(body.phone ?? '')
    const part = clean(String(body.part ?? ''), 60)
    const message = clean(String(body.message ?? ''), 600)
    const otpCode = String(body.otpCode ?? '')
    const otpToken = String(body.otpToken ?? '')

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
    const verification = verifyOtpToken(normalizedPhone, otpCode, otpToken)

    if (!verification.valid) {
      return NextResponse.json(
        { ok: false, error: verification.reason || 'کد تایید معتبر نیست' },
        { status: 400 },
      )
    }

    const trackingCode = `DRK-${Date.now().toString().slice(-6)}`

    const adminSmsResult = await sendAdminConsultationViaKavenegar({
      name,
      company,
      phone: normalizedPhone,
      part,
      message,
      trackingCode,
    })

    if (!adminSmsResult.ok) {
      return NextResponse.json(
        {
          ok: false,
          error:
            adminSmsResult.message ||
            'شماره شما تایید شد اما در ارسال پیامک به مدیر سایت خطایی رخ داد',
        },
        { status: 502 },
      )
    }

    return NextResponse.json({
      ok: true,
      trackingCode,
      sandbox: adminSmsResult.sandbox,
      message: 'درخواست مشاوره شما پس از تایید شماره موبایل، با موفقیت برای مدیر سایت پیامک شد.',
    })
  } catch {
    return NextResponse.json(
      { ok: false, error: 'خطای داخلی سرور در تایید کد و ارسال درخواست مشاوره' },
      { status: 500 },
    )
  }
}
