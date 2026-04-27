export function interpolate(template: string, params: Record<string, string | number>): string {
    return template.replace(/\{(\w+)\}/g, (match, key) => {
        const value = params[key]
        return value === undefined ? match : String(value)
    })
}
