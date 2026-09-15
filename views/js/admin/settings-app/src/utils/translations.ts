let translations: Record<string, string> = {}

export function initTranslations(t: Record<string, string>) {
  translations = t
}

export function t(key: string, ...args: (string | number)[]): string {
  let str = translations[key] || key
  args.forEach((arg) => {
    str = str.replace('%s', String(arg))
  })
  return str
}
