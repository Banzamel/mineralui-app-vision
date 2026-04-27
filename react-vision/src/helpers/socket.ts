type EventHandler = (payload: unknown) => void

interface ChannelHandle {
    name: string
    listen: (event: string, handler: EventHandler) => ChannelHandle
    stopListening: (event: string) => ChannelHandle
    leave: () => void
}

interface SocketDriver {
    private(channel: string): ChannelHandle
    presence(channel: string): ChannelHandle
    disconnect(): void
}

function createStubChannel(name: string): ChannelHandle {
    const handle: ChannelHandle = {
        name,
        listen: () => handle,
        stopListening: () => handle,
        leave: () => undefined,
    }
    return handle
}

const stubDriver: SocketDriver = {
    private: createStubChannel,
    presence: createStubChannel,
    disconnect: () => undefined,
}

let driver: SocketDriver = stubDriver

export function configureSocket(custom: SocketDriver) {
    driver = custom
}

export const socket = {
    private: (channel: string) => driver.private(channel),
    presence: (channel: string) => driver.presence(channel),
    disconnect: () => driver.disconnect(),
}

export const socketConfig = {
    // Stub driver is used until configureSocket() is called from RealtimeContext with a live Echo instance.
}
