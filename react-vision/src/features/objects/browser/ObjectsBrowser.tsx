import {MCardGrid} from '@banzamel/mineralui-pro/cards'
import {MButton, MButtonGroup} from '@banzamel/mineralui-pro/controls'
import {MSkeleton} from '@banzamel/mineralui-pro/feedback'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {
    MArrowLeftIcon,
    MCameraIcon,
    MEditIcon,
    MFolderPlusIcon,
} from '@banzamel/mineralui-pro/icons'
import {MInline, MStack} from '@banzamel/mineralui-pro/layout'
import {MHeading, MText} from '@banzamel/mineralui-pro/typography'

import {AlbumCard, AlbumGallery, useAlbumPhotos} from '../../albums'
import type {AlbumSearchResult} from '../../albums'
import {Camera, CameraTile} from '../../cameras'
import pl from '../../../i18n/pl.json'
import {interpolate} from '../../../i18n/interpolate'
import {canHaveChildren} from '../helpers'
import type {VisionObject} from '../types'
import {ObjectIcon} from '../ObjectIcon'
import {ObjectTile} from '../ObjectTile'
import type {ObjectsBrowserCrud} from './useObjectsBrowserCrud'

interface ObjectsBrowserProps {
    query: string
    searchResults: AlbumSearchResult[]
    activeObject: VisionObject | null
    activeCamera: Camera | null
    activeAlbum: AlbumSearchResult | undefined
    childObjects: VisionObject[]
    camerasOfActive: Camera[]
    albumsOfCamera: AlbumSearchResult[]
    rootObjects: VisionObject[]
    crud: ObjectsBrowserCrud
    openObject: (id: number) => void
    openCamera: (id: number) => void
    openAlbum: (id: number) => void
    clearAlbum: () => void
    clearCamera: () => void
    goHome: () => void
}

// Responsywne kolumny mobile-first: 2 na mobile, 3 od ≥768px (tablet), 4 od ≥1024px (desktop).
// MCardGrid kaskada CSS — base → sm → md → lg → xl → xxl, brakujące breakpointy dziedziczą
// z poprzedniego mniejszego. Tu sm dziedziczy z base (2), xl/xxl z lg (4).
const RESPONSIVE_COLUMNS = {base: 2, md: 3, lg: 4} as const

export function ObjectsBrowser(props: ObjectsBrowserProps) {
    const {t} = useMI18n<typeof pl>()
    const {
        query,
        searchResults,
        activeObject,
        activeCamera,
        activeAlbum,
        childObjects,
        camerasOfActive,
        albumsOfCamera,
        rootObjects,
        crud,
        openObject,
        openCamera,
        openAlbum,
        clearAlbum,
        clearCamera,
        goHome,
    } = props

    const albumPhotosFeed = useAlbumPhotos(activeAlbum?.id ?? null)

    const objectMenu = (obj: VisionObject) => [
        {label: t('objects_dashboard.edit'), onClick: () => crud.openEditObject(obj)},
        {label: t('objects_dashboard.delete'), danger: true, onClick: () => crud.deleteObject(obj)},
    ]
    const cameraMenu = (cam: Camera) => [
        {label: t('objects_dashboard.edit'), onClick: () => crud.openEditCamera(cam)},
        {label: t('objects_dashboard.delete'), danger: true, onClick: () => crud.deleteCamera(cam)},
    ]
    const albumMenu = (album: AlbumSearchResult) => [
        {label: t('objects_dashboard.delete'), danger: true, onClick: () => crud.deleteAlbum(album.id)},
    ]

    if (query.trim().length > 0) {
        return (
            <MStack>
                <MText tone={'muted'}>
                    {interpolate(t('objects_dashboard.search_results'), {count: searchResults.length})}
                </MText>
                <MCardGrid<AlbumSearchResult>
                    items={searchResults}
                    columns={RESPONSIVE_COLUMNS}
                    pagination
                    pageSize={12}
                    emptyMessage={t('objects_dashboard.empty_search')}
                    renderCard={(album) =>
                        crud.deletingAlbumIds.has(album.id) ? (
                            <DeletingTile />
                        ) : (
                            <AlbumCard
                                album={album}
                                cameraName={`${album.object_path.join(' / ')} · ${album.camera_name}`}
                                onOpen={openAlbum}
                                menuItems={albumMenu(album)}
                            />
                        )
                    }
                />
            </MStack>
        )
    }

    if (activeAlbum) {
        return (
            <MStack>
                <MInline justify={'between'} wrap={'wrap'} align={'center'}>
                    <MHeading level={3}>
                        {activeCamera?.name} · {activeAlbum.date}
                    </MHeading>
                    <MButtonGroup size={'sm'}>
                        <MButton
                            variant={'outlined'}
                            iconOnly
                            startIcon={<MArrowLeftIcon />}
                            aria-label={t('objects_dashboard.back_to_albums')}
                            title={t('objects_dashboard.back_to_albums')}
                            onClick={clearAlbum}
                        />
                    </MButtonGroup>
                </MInline>
                <AlbumGallery
                    photos={albumPhotosFeed.photos}
                    hasMore={albumPhotosFeed.hasMore}
                    loading={albumPhotosFeed.loading}
                    onLoadMore={albumPhotosFeed.loadMore}
                />
            </MStack>
        )
    }

    if (activeCamera) {
        return (
            <MStack>
                <MInline justify={'between'} wrap={'wrap'} align={'center'}>
                    <MStack>
                        <MHeading level={3}>{activeCamera.name}</MHeading>
                        <MText tone={'muted'}>{activeCamera.address}</MText>
                    </MStack>
                    <MButtonGroup size={'sm'}>
                        <MButton
                            variant={'outlined'}
                            iconOnly
                            startIcon={<MArrowLeftIcon />}
                            aria-label={t('objects_dashboard.back_to_object')}
                            title={t('objects_dashboard.back_to_object')}
                            onClick={clearCamera}
                        />
                        <MButton
                            variant={'outlined'}
                            iconOnly
                            startIcon={<MEditIcon />}
                            aria-label={t('objects_dashboard.edit_camera')}
                            title={t('objects_dashboard.edit_camera')}
                            onClick={() => crud.openEditCamera(activeCamera)}
                        />
                    </MButtonGroup>
                </MInline>
                <MCardGrid<AlbumSearchResult>
                    items={albumsOfCamera}
                    columns={RESPONSIVE_COLUMNS}
                    pagination
                    pageSize={12}
                    emptyMessage={t('objects_dashboard.empty_albums')}
                    renderCard={(album) =>
                        crud.deletingAlbumIds.has(album.id) ? (
                            <DeletingTile />
                        ) : (
                            <AlbumCard album={album} onOpen={openAlbum} menuItems={albumMenu(album)} />
                        )
                    }
                />
            </MStack>
        )
    }

    if (activeObject) {
        const canAddChild = canHaveChildren(activeObject)
        const meta =
            (activeObject.address ?? '').trim() ||
            (activeObject.description ?? '').trim() ||
            t('objects_dashboard.no_meta')
        return (
            <MStack>
                <MInline justify={'between'} wrap={'wrap'} align={'center'}>
                    <MStack>
                        <MHeading level={3}>{activeObject.name}</MHeading>
                        <MInline align={'center'} wrap={'wrap'}>
                            <ObjectIcon type={activeObject.type} size={16} />
                            <MText tone={'muted'} size={'sm'}>
                                {t(`object_type.${activeObject.type}`)} · {meta}
                            </MText>
                        </MInline>
                    </MStack>
                    <MButtonGroup size={'sm'}>
                        <MButton
                            variant={'outlined'}
                            iconOnly
                            startIcon={<MArrowLeftIcon />}
                            aria-label={t('objects_dashboard.back')}
                            title={t('objects_dashboard.back')}
                            onClick={() =>
                                activeObject.parent_id != null
                                    ? openObject(activeObject.parent_id)
                                    : goHome()
                            }
                        />
                        <MButton
                            variant={'outlined'}
                            iconOnly
                            startIcon={<MEditIcon />}
                            aria-label={t('objects_dashboard.edit_object')}
                            title={t('objects_dashboard.edit_object')}
                            onClick={() => crud.openEditObject(activeObject)}
                        />
                        {canAddChild && (
                            <MButton
                                variant={'filled'}
                                color={'primary'}
                                iconOnly
                                startIcon={<MFolderPlusIcon />}
                                aria-label={t('objects_dashboard.add_sub_object')}
                                title={t('objects_dashboard.add_sub_object')}
                                onClick={() => crud.openNewObject(activeObject.id)}
                            />
                        )}
                        <MButton
                            variant={'filled'}
                            color={'primary'}
                            iconOnly
                            startIcon={<MCameraIcon />}
                            aria-label={t('objects_dashboard.add_camera')}
                            title={t('objects_dashboard.add_camera')}
                            onClick={() => crud.openNewCamera(activeObject.id)}
                        />
                    </MButtonGroup>
                </MInline>
                {childObjects.length > 0 && (
                    <MStack spacing={'xs'}>
                        <MHeading level={5}>{t('objects_dashboard.sub_objects')}</MHeading>
                        <MCardGrid<VisionObject>
                            items={childObjects}
                            columns={RESPONSIVE_COLUMNS}
                            emptyMessage={''}
                            renderCard={(item) =>
                                crud.deletingObjectIds.has(item.id) ? (
                                    <DeletingTile />
                                ) : (
                                    <ObjectTile object={item} onOpen={openObject} menuItems={objectMenu(item)} />
                                )
                            }
                        />
                    </MStack>
                )}
                {camerasOfActive.length > 0 && (
                    <MStack>
                        <MHeading level={5}>{t('objects_dashboard.cameras')}</MHeading>
                        <MCardGrid<Camera>
                            items={camerasOfActive}
                            columns={RESPONSIVE_COLUMNS}
                            emptyMessage={''}
                            renderCard={(cam) =>
                                crud.deletingCameraIds.has(cam.id) ? (
                                    <DeletingTile />
                                ) : (
                                    <CameraTile camera={cam} onOpen={openCamera} menuItems={cameraMenu(cam)} />
                                )
                            }
                        />
                    </MStack>
                )}
                {childObjects.length === 0 && camerasOfActive.length === 0 && (
                    <MText tone={'muted'}>{t('objects_dashboard.empty_object')}</MText>
                )}
            </MStack>
        )
    }

    return (
        <MStack>
            <MInline justify={'between'} wrap={'wrap'} align={'center'}>
                <MHeading level={3}>{t('objects_dashboard.all_objects')}</MHeading>
                <MButtonGroup size={'sm'}>
                    <MButton
                        variant={'filled'}
                        color={'primary'}
                        iconOnly
                        startIcon={<MFolderPlusIcon />}
                        aria-label={t('objects_dashboard.add_object')}
                        title={t('objects_dashboard.add_object')}
                        onClick={() => crud.openNewObject(null)}
                    />
                </MButtonGroup>
            </MInline>
            <MCardGrid<VisionObject>
                items={rootObjects}
                columns={RESPONSIVE_COLUMNS}
                searchable
                searchKeys={['name', 'address']}
                pagination
                pageSize={12}
                emptyMessage={t('objects_dashboard.empty_objects')}
                renderCard={(item) =>
                    crud.deletingObjectIds.has(item.id) ? (
                        <DeletingTile />
                    ) : (
                        <ObjectTile object={item} onOpen={openObject} menuItems={objectMenu(item)} />
                    )
                }
            />
        </MStack>
    )
}

/**
 * Tile-sized shimmer placeholder rendered in place of an item that is currently being deleted.
 * Matches the typical card height so the grid does not jump while the API call is in flight.
 */
function DeletingTile() {
    return <MSkeleton variant={'rectangle'} width={'100%'} height={220} radius={12} animate={'shimmer'} />
}
