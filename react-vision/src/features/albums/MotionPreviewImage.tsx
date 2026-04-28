import {useEffect, useRef, useState} from 'react'

interface MotionFrame {
    url: string
    thumbnail_url: string
}

interface MotionPreviewImageProps {
    /** Frames to cycle through, chronological order. Must contain ≥ 2 entries. */
    frames: MotionFrame[]
    /** Frames per second for the loop. Default 5 (200 ms / frame). */
    fps?: number
    /** When true (touch / mobile), animate continuously regardless of hover. */
    autoplay?: boolean
    alt?: string
    radius?: number
}

/**
 * Cycles through a list of thumbnail frames to fake a motion clip without server-side video.
 *
 * Desktop UX: idle = lead frame, hover → loop, leave → reset to lead frame.
 * Mobile UX: `autoplay` runs the loop continuously (no hover available); tap stays as the
 * default browser behaviour (does not toggle play, does not conflict with link click).
 *
 * Frame preload runs on the first activation only — that way scrolling past dozens of bursts
 * does not pull every thumbnail at once. After the first cycle the browser cache makes the
 * `<img>` `src` swaps instantaneous.
 */
export function MotionPreviewImage({
    frames,
    fps = 5,
    autoplay = false,
    alt = '',
    radius = 8,
}: MotionPreviewImageProps) {
    const [hover, setHover] = useState(false)
    const [index, setIndex] = useState(0)
    const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null)
    const preloadedRef = useRef(false)

    const playing = autoplay || hover

    useEffect(() => {
        if (!playing) {
            if (intervalRef.current) clearInterval(intervalRef.current)
            intervalRef.current = null
            setIndex(0)
            return
        }
        if (!preloadedRef.current) {
            preloadedRef.current = true
            for (const f of frames) {
                const img = new Image()
                img.src = f.thumbnail_url
            }
        }
        const periodMs = Math.max(60, Math.round(1000 / fps))
        intervalRef.current = setInterval(() => {
            setIndex((i) => (i + 1) % frames.length)
        }, periodMs)
        return () => {
            if (intervalRef.current) clearInterval(intervalRef.current)
        }
    }, [playing, frames, fps])

    const src = frames[index]?.thumbnail_url ?? frames[0]!.thumbnail_url

    return (
        <div
            onMouseEnter={() => setHover(true)}
            onMouseLeave={() => setHover(false)}
            style={{
                position: 'relative',
                overflow: 'hidden',
                borderRadius: radius,
                aspectRatio: '4 / 3',
                background: 'var(--m-color-neutral-200, #eee)',
            }}
        >
            <img
                src={src}
                alt={alt}
                loading={'lazy'}
                style={{width: '100%', height: '100%', objectFit: 'cover', display: 'block'}}
            />
            <span
                aria-hidden={'true'}
                style={{
                    position: 'absolute',
                    top: 6,
                    right: 6,
                    padding: '2px 6px',
                    fontSize: 11,
                    lineHeight: 1.2,
                    fontWeight: 600,
                    color: '#fff',
                    background: 'rgba(0, 0, 0, 0.55)',
                    borderRadius: 999,
                    pointerEvents: 'none',
                }}
            >
                ▶ {frames.length}
            </span>
        </div>
    )
}
