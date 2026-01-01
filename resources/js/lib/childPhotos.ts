export const CHILD_DEFAULT_PHOTOS = [
    '/enfant1.png',
    '/enfant2.png',
    '/enfant3.png',
    '/enfant4.png',
    '/enfant5.png',
    '/enfant6.png',
]

const hashSeed = (value: string) => {
    let hash = 0
    for (let i = 0; i < value.length; i += 1) {
        hash = (hash * 31 + value.charCodeAt(i)) % 2147483647
    }
    return Math.abs(hash)
}

export const getChildFallbackPhoto = (seedParts: Array<string | number | null | undefined>, index = 0) => {
    const seed = seedParts
        .map((part) => (part ?? '').toString().trim())
        .filter(Boolean)
        .join('|')
    const resolved = seed !== '' ? seed : String(index)
    const hash = hashSeed(resolved)
    const offset = hash % CHILD_DEFAULT_PHOTOS.length
    return CHILD_DEFAULT_PHOTOS[offset]
}

export const resolveChildPhoto = (
    photo: string | null | undefined,
    seedParts: Array<string | number | null | undefined>,
    index = 0,
) => {
    const trimmed = (photo ?? '').toString().trim()
    if (trimmed !== '') {
        return trimmed
    }
    return getChildFallbackPhoto(seedParts, index)
}
