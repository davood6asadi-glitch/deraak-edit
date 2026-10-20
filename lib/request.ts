import { CONTACT } from './products'

const MAX_MAILTO_BODY = 1800

const clean = (value: string, max: number) =>
  value.replace(/[\u0000-\u0008\u000B\u000C\u000E-\u001F\u007F]/g, '').trim().slice(0, max)

export interface RequesterInfo {
  name: string
  company: string
  phone: string
}

export function validateRequester(info: RequesterInfo): string | null {
  if (clean(info.name, 80).length < 2) return 'نام و نام خانوادگی را وارد کنید'
  const phone = info.phone.replace(/[\s-]/g, '')
  if (!/^(\+98|0)?9\d{9}$|^0\d{10}$/.test(phone)) return 'شماره تماس معتبر نیست'
  return null
}

export function buildMailto(subject: string, lines: string[]) {
  const body = lines.join('\n').slice(0, MAX_MAILTO_BODY)
  return `mailto:${CONTACT.email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`
}

export function requesterLines(info: RequesterInfo) {
  return [
    `نام: ${clean(info.name, 80)}`,
    `شرکت: ${clean(info.company, 120) || '-'}`,
    `تماس: ${clean(info.phone, 20)}`,
  ]
}

export { clean }
