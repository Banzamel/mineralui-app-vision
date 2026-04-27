export function formatDateTime(value: string | null | undefined, fallback = ''): string {
    if (!value) return fallback
    const parsed = new Date(value)
    if (Number.isNaN(parsed.getTime())) return fallback || value
    const date = parsed.toLocaleDateString('pl-PL')
    const time = parsed.toLocaleTimeString('pl-PL', {hour: '2-digit', minute: '2-digit'})
    return `${date} ${time}`
}

export function fileToDataUrl(file: File): Promise<string> {
    return new Promise((resolve, reject) => {
        const reader = new FileReader()
        reader.onload = () => resolve(String(reader.result))
        reader.onerror = () => reject(reader.error)
        reader.readAsDataURL(file)
    })
}
