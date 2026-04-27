import type {ObjectType} from '../objects/types'

export interface InstallDatabase {
    host: string
    port: number
    database: string
    username: string
    password: string
}

export interface InstallAdmin {
    name: string
    email: string
    password: string
    password_confirmation: string
}

export interface InstallFirstObject {
    name: string
    type: ObjectType
}

export interface InstallFirstCamera {
    name: string
    address: string
    ip: string
    stream_url: string
    stream_login: string
    stream_password: string
}

export interface InstallPayload {
    database: InstallDatabase
    admin: InstallAdmin
    first_object: InstallFirstObject
    first_camera: InstallFirstCamera
}

export interface InstallResponse {
    ok: boolean
    message?: string
    company_slug?: string
}

export interface InstallDatabaseDefaults {
    host: string
    port: number
    database: string
    username: string
    password: string
}

export interface InstallStatus {
    installed: boolean
    stage: string
    database_defaults: InstallDatabaseDefaults | null
}
