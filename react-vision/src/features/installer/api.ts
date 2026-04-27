import {api} from '../../helpers/api'
import type {
    InstallAdmin,
    InstallDatabase,
    InstallFirstCamera,
    InstallFirstObject,
    InstallResponse,
    InstallStatus,
} from './types'

export const installerApi = {
    status: () => api.get<InstallStatus>('/install/status'),
    testDatabase: (payload: InstallDatabase) => api.post<InstallResponse>('/install/test-database', payload),
    saveDatabase: (payload: InstallDatabase) => api.post<InstallResponse>('/install/database', payload),
    createAdmin: (payload: InstallAdmin) => api.post<InstallResponse>('/install/admin', payload),
    createFirstObject: (payload: InstallFirstObject) =>
        api.post<InstallResponse>('/install/first-object', payload),
    createFirstCamera: (payload: InstallFirstCamera) =>
        api.post<InstallResponse>('/install/first-camera', payload),
    finalize: () => api.post<InstallResponse>('/install/finalize'),
}
