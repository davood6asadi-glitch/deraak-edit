import { NextRequest, NextResponse } from 'next/server'
import { clean, normalizeIranMobile, validateRequester } from '@/lib/request'

export async function POST(req: NextRequest) {
  try {
    const rawBody = await req.text()
    if (rawBody.length > 32768) {
      return NextResponse.json(
        { ok: false, error: 'حجم درخواست بیش از حد مجاز است' },
        { status: 413 },
      )
    }

    const body = JSON.parse(rawBody)
    if (!body || typeof body !== 'object') {
      return NextResponse.json(
        { ok: false, error: 'فرمت درخواست نامعتبر است' },
        { status: 400 },
      )
    }

    // بررسی فیلد ضد اسپم (Honeypot)
    if (body.website_hp) {
      return NextResponse.json(
        { ok: false, error: 'درخواست نامعتبر تشخیص داده شد' },
        { status: 400 },
      )
    }

    const info = {
      name: clean(String(body.name ?? ''), 80),
      company: clean(String(body.company ?? ''), 120),
      phone: clean(String(body.phone ?? ''), 20),
    }

    const requesterErr = validateRequester(info)
    if (requesterErr) {
      return NextResponse.json({ ok: false, error: requesterErr }, { status: 400 })
    }

    const rawItems = Array.isArray(body.items) ? body.items : []
    if (rawItems.length === 0 || rawItems.length > 50) {
      return NextResponse.json(
        { ok: false, error: 'لیست اقلام استعلام خالی یا خارج از محدوده مجاز است' },
        { status: 400 },
      )
    }

    const sanitizedItems = rawItems.map((item: Record<string, unknown>) => ({
      id: clean(String(item.id ?? ''), 60),
      model: clean(String(item.model ?? ''), 80),
      title: clean(String(item.title ?? ''), 160),
      quantity: Math.max(1, Math.min(999, Number(item.quantity) || 1)),
      notes: clean(String(item.notes ?? ''), 300),
      urgent: Boolean(item.urgent),
    }))

    const trackingCode = `DRK-RFQ-${Date.now().toString().slice(-6)}`

    return NextResponse.json({
      ok: true,
      trackingCode,
      receivedAt: new Date().toISOString(),
      requester: {
        name: info.name,
        company: info.company,
        phone: normalizeIranMobile(info.phone) || info.phone,
      },
      itemCount: sanitizedItems.length,
      message: 'لیست استعلام شما در سرور اعتبارسنجی و با کد رهگیری ثبت گردید.',
    })
  } catch {
    return NextResponse.json(
      { ok: false, error: 'خطا در پردازش درخواست استعلام در سمت سرور' },
      { status: 500 },
    )
  }
}
