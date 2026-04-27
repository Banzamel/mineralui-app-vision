import {useEffect, useMemo, useState} from 'react'
import {useSearchParams} from 'react-router-dom'

import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MHomeIcon} from '@banzamel/mineralui-pro/icons'
import {MInputSearch} from '@banzamel/mineralui-pro/inputs'
import {MBreadcrumb, MContainer, MGrid, MSection, MStack} from '@banzamel/mineralui-pro/layout'
import type {MBreadcrumbItem} from '@banzamel/mineralui-pro/layout'
import {MHeading} from '@banzamel/mineralui-pro/typography'

import {Loading} from '../components/Loading'
import {albumsApi, enrichAlbums, searchAlbums} from '../features/albums'
import {CameraFormModal, camerasApi} from '../features/cameras'
import type {Camera} from '../features/cameras'
import {
    ObjectsBrowser,
    ObjectFormModal,
    ObjectsTree,
    buildObjectTree,
    getObjectPath,
    objectsApi,
    useObjectsBrowserCrud,
} from '../features/objects'
import type {VisionObject} from '../features/objects'
import {useAsync} from '../helpers'
import pl from '../i18n/pl.json'

interface DashboardData {
    objects: VisionObject[]
    cameras: Camera[]
}

export function ObjectsPage() {
    const {t} = useMI18n<typeof pl>()
    const [searchParams, setSearchParams] = useSearchParams()
    const [activeObjectId, setActiveObjectId] = useState<number | null>(null)
    const [activeCameraId, setActiveCameraId] = useState<number | null>(null)
    const [activeAlbumId, setActiveAlbumId] = useState<number | null>(null)
    const [query, setQuery] = useState('')

    const {data, loading, error, reload} = useAsync<DashboardData>(async () => {
        const [objectsRes, camerasRes] = await Promise.all([objectsApi.list(), camerasApi.list()])
        return {objects: objectsRes.data, cameras: camerasRes.data}
    }, [])

    const {
        data: albumsData,
        loading: albumsLoading,
        reload: reloadAlbums,
    } = useAsync(() => albumsApi.list().then((res) => res.data), [])

    const albums = albumsData ?? []
    const objects = data?.objects ?? []
    const cameras = data?.cameras ?? []

    const tree = useMemo(() => buildObjectTree(objects), [objects])
    const objectById = useMemo(() => new Map(objects.map((o) => [o.id, o] as const)), [objects])
    const cameraById = useMemo(() => new Map(cameras.map((c) => [c.id, c] as const)), [cameras])
    const enriched = useMemo(() => enrichAlbums(albums, cameras, objects), [albums, cameras, objects])
    const searchResults = useMemo(() => searchAlbums(enriched, query), [enriched, query])

    const activeObject = activeObjectId != null ? objectById.get(activeObjectId) ?? null : null
    const activeCamera = activeCameraId != null ? cameraById.get(activeCameraId) ?? null : null
    const activeAlbum = activeAlbumId != null ? enriched.find((a) => a.id === activeAlbumId) : undefined

    const rootObjects = useMemo(() => objects.filter((o) => o.parent_id == null), [objects])
    const childObjects = useMemo(
        () => (activeObjectId != null ? objects.filter((o) => o.parent_id === activeObjectId) : []),
        [objects, activeObjectId],
    )
    const camerasOfActive = useMemo(
        () => (activeObjectId != null ? cameras.filter((c) => c.object_id === activeObjectId) : []),
        [cameras, activeObjectId],
    )
    const albumsOfCamera = useMemo(
        () => (activeCameraId != null ? enriched.filter((a) => a.camera_id === activeCameraId) : []),
        [enriched, activeCameraId],
    )

    useEffect(() => {
        if (loading || !enriched.length) return
        const albumParam = searchParams.get('album')
        const cameraParam = searchParams.get('camera')
        const objectParam = searchParams.get('object')
        if (albumParam) {
            const album = enriched.find((a) => a.id === Number(albumParam))
            if (album) {
                setActiveObjectId(album.object_id)
                setActiveCameraId(album.camera_id)
                setActiveAlbumId(album.id)
            }
        } else if (cameraParam) {
            const camera = cameraById.get(Number(cameraParam))
            if (camera) {
                setActiveObjectId(camera.object_id)
                setActiveCameraId(camera.id)
                setActiveAlbumId(null)
            }
        } else if (objectParam) {
            if (objectById.has(Number(objectParam))) {
                setActiveObjectId(Number(objectParam))
                setActiveCameraId(null)
                setActiveAlbumId(null)
            }
        } else {
            return
        }
        const next = new URLSearchParams(searchParams)
        next.delete('album')
        next.delete('camera')
        next.delete('object')
        setSearchParams(next, {replace: true})
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [loading, enriched, cameraById, objectById])

    const goHome = () => {
        setActiveObjectId(null)
        setActiveCameraId(null)
        setActiveAlbumId(null)
    }
    const openObject = (id: number) => {
        setActiveObjectId(id)
        setActiveCameraId(null)
        setActiveAlbumId(null)
    }
    const openCamera = (id: number) => {
        setActiveCameraId(id)
        setActiveAlbumId(null)
    }
    const openAlbum = (id: number) => {
        const album = enriched.find((a) => a.id === id)
        if (album) {
            setActiveObjectId(album.object_id)
            setActiveCameraId(album.camera_id)
            setActiveAlbumId(id)
            setQuery('')
        }
    }

    const crud = useObjectsBrowserCrud({
        activeObjectId,
        activeCameraId,
        activeAlbumId,
        setActiveObjectId,
        setActiveCameraId,
        setActiveAlbumId,
        reloadData: reload,
        reloadAlbums,
    })

    const breadcrumbItems = useMemo<MBreadcrumbItem[]>(() => {
        const items: MBreadcrumbItem[] = [{label: <MHomeIcon size={14} />, onClick: goHome}]
        if (activeObjectId != null) {
            for (const node of getObjectPath(objects, activeObjectId)) {
                items.push({label: node.name, onClick: () => openObject(node.id)})
            }
        }
        if (activeCamera) {
            items.push({label: activeCamera.name, onClick: () => openCamera(activeCamera.id)})
        }
        if (activeAlbum) {
            items.push({label: activeAlbum.date})
        }
        return items
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [activeObjectId, activeCameraId, activeAlbumId, objects, cameras])

    return (
        <MSection as={'main'}>
            <MContainer size={'wide'}>
                <MStack>
                    <MInputSearch
                        placeholder={t('objects_dashboard.search_placeholder')}
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        debounceMs={150}
                        fullWidth
                        loading={albumsLoading}
                    />

                    <Loading loading={loading} error={error} onRetry={reload} minHeight={400}>
                        <MGrid type={'row'}>
                            <MGrid type={'col'} sm={12} md={12} lg={3} xl={3}>
                                <MStack>
                                    <MHeading level={5}>{t('objects_dashboard.tree')}</MHeading>
                                    <ObjectsTree
                                        roots={tree}
                                        selectedId={activeObjectId}
                                        onSelect={openObject}
                                    />
                                </MStack>
                            </MGrid>
                            <MGrid type={'col'} sm={12} md={12} lg={9} xl={9}>
                                <MStack>
                                    <MBreadcrumb items={breadcrumbItems} />
                                    <ObjectsBrowser
                                        query={query}
                                        searchResults={searchResults}
                                        activeObject={activeObject}
                                        activeCamera={activeCamera}
                                        activeAlbum={activeAlbum}
                                        childObjects={childObjects}
                                        camerasOfActive={camerasOfActive}
                                        albumsOfCamera={albumsOfCamera}
                                        rootObjects={rootObjects}
                                        crud={crud}
                                        openObject={openObject}
                                        openCamera={openCamera}
                                        openAlbum={openAlbum}
                                        clearAlbum={() => setActiveAlbumId(null)}
                                        clearCamera={() => setActiveCameraId(null)}
                                        goHome={goHome}
                                    />
                                </MStack>
                            </MGrid>
                        </MGrid>
                    </Loading>
                </MStack>
            </MContainer>

            <ObjectFormModal
                open={crud.objectModal.open}
                object={crud.objectModal.object}
                allObjects={objects}
                defaultParentId={crud.objectModal.defaultParentId}
                onClose={crud.objectModal.close}
                onSaved={reload}
            />
            <CameraFormModal
                open={crud.cameraModal.open}
                camera={crud.cameraModal.camera}
                allObjects={objects}
                defaultObjectId={crud.cameraModal.defaultObjectId}
                onClose={crud.cameraModal.close}
                onSaved={reload}
            />
        </MSection>
    )
}
