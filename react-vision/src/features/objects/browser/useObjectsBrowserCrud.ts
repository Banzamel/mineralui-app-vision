import {useState} from 'react'

import {albumsApi} from '../../albums'
import type {Camera} from '../../cameras'
import {camerasApi} from '../../cameras'
import {objectsApi} from '../api'
import type {VisionObject} from '../types'

export interface ObjectsBrowserCrudDeps {
    activeObjectId: number | null
    activeCameraId: number | null
    activeAlbumId: number | null
    setActiveObjectId: (id: number | null) => void
    setActiveCameraId: (id: number | null) => void
    setActiveAlbumId: (id: number | null) => void
    reloadData: () => void
    reloadAlbums: () => void
}

export interface ObjectsBrowserCrud {
    objectModal: {
        open: boolean
        object: VisionObject | null
        defaultParentId: number | null
        close: () => void
    }
    cameraModal: {
        open: boolean
        camera: Camera | null
        defaultObjectId: number | null
        close: () => void
    }
    openNewObject: (parentId: number | null) => void
    openEditObject: (obj: VisionObject) => void
    deleteObject: (obj: VisionObject) => Promise<void>
    openNewCamera: (objectId: number | null) => void
    openEditCamera: (cam: Camera) => void
    deleteCamera: (cam: Camera) => Promise<void>
    deleteAlbum: (id: number) => Promise<void>
    /** IDs currently being deleted — ObjectsBrowser renders an MSkeleton in their place. */
    deletingObjectIds: ReadonlySet<number>
    deletingCameraIds: ReadonlySet<number>
    deletingAlbumIds: ReadonlySet<number>
}

export function useObjectsBrowserCrud(deps: ObjectsBrowserCrudDeps): ObjectsBrowserCrud {
    const {
        activeObjectId,
        activeCameraId,
        activeAlbumId,
        setActiveObjectId,
        setActiveCameraId,
        setActiveAlbumId,
        reloadData,
        reloadAlbums,
    } = deps

    const [objectModalOpen, setObjectModalOpen] = useState(false)
    const [editingObject, setEditingObject] = useState<VisionObject | null>(null)
    const [objectParentDefault, setObjectParentDefault] = useState<number | null>(null)
    const [cameraModalOpen, setCameraModalOpen] = useState(false)
    const [editingCamera, setEditingCamera] = useState<Camera | null>(null)
    const [cameraObjectDefault, setCameraObjectDefault] = useState<number | null>(null)
    const [deletingObjectIds, setDeletingObjectIds] = useState<ReadonlySet<number>>(() => new Set())
    const [deletingCameraIds, setDeletingCameraIds] = useState<ReadonlySet<number>>(() => new Set())
    const [deletingAlbumIds, setDeletingAlbumIds] = useState<ReadonlySet<number>>(() => new Set())

    function withId(set: ReadonlySet<number>, id: number, add: boolean): ReadonlySet<number> {
        const next = new Set(set)
        if (add) next.add(id)
        else next.delete(id)
        return next
    }

    function openNewObject(parentId: number | null) {
        setEditingObject(null)
        setObjectParentDefault(parentId)
        setObjectModalOpen(true)
    }
    function openEditObject(obj: VisionObject) {
        setEditingObject(obj)
        setObjectParentDefault(null)
        setObjectModalOpen(true)
    }
    async function deleteObject(obj: VisionObject) {
        setDeletingObjectIds((s) => withId(s, obj.id, true))
        try {
            await objectsApi.delete(obj.id)
            if (activeObjectId === obj.id) {
                setActiveObjectId(null)
                setActiveCameraId(null)
                setActiveAlbumId(null)
            }
            reloadData()
        } finally {
            setDeletingObjectIds((s) => withId(s, obj.id, false))
        }
    }
    function openNewCamera(objectId: number | null) {
        setEditingCamera(null)
        setCameraObjectDefault(objectId)
        setCameraModalOpen(true)
    }
    function openEditCamera(cam: Camera) {
        setEditingCamera(cam)
        setCameraObjectDefault(null)
        setCameraModalOpen(true)
    }
    async function deleteCamera(cam: Camera) {
        setDeletingCameraIds((s) => withId(s, cam.id, true))
        try {
            await camerasApi.delete(cam.id)
            if (activeCameraId === cam.id) setActiveCameraId(null)
            reloadData()
        } finally {
            setDeletingCameraIds((s) => withId(s, cam.id, false))
        }
    }
    async function deleteAlbum(id: number) {
        setDeletingAlbumIds((s) => withId(s, id, true))
        try {
            await albumsApi.delete(id)
            if (activeAlbumId === id) setActiveAlbumId(null)
            reloadAlbums()
        } finally {
            setDeletingAlbumIds((s) => withId(s, id, false))
        }
    }

    return {
        objectModal: {
            open: objectModalOpen,
            object: editingObject,
            defaultParentId: objectParentDefault,
            close: () => setObjectModalOpen(false),
        },
        cameraModal: {
            open: cameraModalOpen,
            camera: editingCamera,
            defaultObjectId: cameraObjectDefault,
            close: () => setCameraModalOpen(false),
        },
        openNewObject,
        openEditObject,
        deleteObject,
        openNewCamera,
        openEditCamera,
        deleteCamera,
        deleteAlbum,
        deletingObjectIds,
        deletingCameraIds,
        deletingAlbumIds,
    }
}
