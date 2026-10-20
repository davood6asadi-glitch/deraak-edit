import { CONTACT } from './products'

const MAX_MAILTO_BODY = 1800

const clean = (value: string, max: number) =>
  value.replace(/[\u0000-\u0008\u000B\u000C\u000E-\u001F\u007F]/g, '').trim().slice(0, max)

export function toEnglishDigits(input: string): string {
  return input
    .replace(/[۰-۹]/g, (d) => String(d.charCodeAt(0) - 1776))
    .replace(/[٠-٩]/g, (d) => String(d.charCodeAt(0) - 1632))
}

export function normalizeIranMobile(rawPhone: string): string | null {
  const digits = toEnglishDigits(rawPhone).replace(/[\s\-()]/g, '')
  if (/^09\d{9}$/.test(digits)) return digits
  if (/^\+989\d{9}$/.test(digits)) return `0${digits.slice(3)}`
  if (/^00989\d{9}$/.test(digits)) return `0${digits.slice(4)}`
  if (/^989\d{9}$/.test(digits)) return `0${digits.slice(2)}`
  if (/^9\d{9}$/.test(digits)) return `0${digits}`
  return null
}

export interface RequesterInfo {
  name: string
  company: string
  phone: string
}

export interface ConsultationFormInput extends RequesterInfo {
  part: string
  message: string
}

export function validateRequester(info: RequesterInfo): string | null {
  if (clean(info.name, 80).length < 2) return 'نام و نام خانوادگی را وارد کنید'
  const phone = toEnglishDigits(info.phone).replace(/[\s-]/g, '')
  if (!/^(\+98|0)?9\d{9}$|^0\d{10}$/.test(phone)) return 'شماره تماس معتبر نیست'
  return null
}

export function validateConsultationInput(info: ConsultationFormInput): string | null {
  if (clean(info.name, 80).length < 2) return 'نام و نام خانوادگی را وارد کنید'
  if (!normalizeIranMobile(info.phone)) return 'شماره موبایل معتبر وارد کنید (مثال: 09123456789)'
  if (clean(info.message, 600).length < 5) return 'شرح نیاز فنی را وارد کنید (حداقل ۵ کاراکتر)'
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
    `تماس: ${clean(toEnglishDigits(info.phone), 20)}`,
  ]
}

export { clean }
