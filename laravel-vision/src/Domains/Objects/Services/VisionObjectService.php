<?php

namespace Objects\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Objects\Dtos\CreateObjectDto;
use Objects\Dtos\UpdateObjectDto;
use Objects\Events\ObjectCreatedEvent;
use Objects\Events\ObjectDeletedEvent;
use Objects\Events\ObjectUpdatedEvent;
use Objects\Models\VisionObject;
use Objects\Repositories\Interfaces\VisionObjectRepositoryInterface;
use Objects\Services\Interfaces\VisionObjectServiceInterface;

/**
 * Business logic service for Vision tree objects.
 * Enforces unique slug within the company, computes depth and emits events.
 */
class VisionObjectService implements VisionObjectServiceInterface
{
    /**
     * @param VisionObjectRepositoryInterface $repository Objects repository.
     */
    public function __construct(
        protected VisionObjectRepositoryInterface $repository,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function tree(): Collection
    {
        $all = $this->repository->allOrderedWithCameras();

        $byParent = $all->groupBy('parent_id');
        $all->each(function (VisionObject $node) use ($byParent) {
            $node->setRelation('children', $byParent->get($node->id, collect()));
        });

        return $all->whereNull('parent_id')->values();
    }

    /**
     * @inheritDoc
     */
    public function list(): Collection
    {
        return $this->repository->all();
    }

    /**
     * @inheritDoc
     */
    public function find(int $id): VisionObject
    {
        return $this->repository->findOrFail($id);
    }

    /**
     * @inheritDoc
     */
    public function create(CreateObjectDto $dto): VisionObject
    {
        return DB::transaction(function () use ($dto) {
            $depth = $this->calculateDepth($dto->parentId);

            $data = $dto->toArray();
            $data['slug'] = $this->uniqueSlug($dto->name);
            $data['depth'] = $depth;

            $object = $this->repository->create($data);
            event(new ObjectCreatedEvent($object));
            return $object;
        });
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, UpdateObjectDto $dto): VisionObject
    {
        return DB::transaction(function () use ($id, $dto) {
            $object = $this->repository->findOrFail($id);
            $data = $dto->toArray();

            if (array_key_exists('parent_id', $data)) {
                $data['depth'] = $this->calculateDepth($data['parent_id']);
            }

            $updated = $this->repository->update($object, $data);
            event(new ObjectUpdatedEvent($updated));
            return $updated;
        });
    }

    /**
     * @inheritDoc
     */
    public function updateMainPhoto(int $id, UploadedFile $file): VisionObject
    {
        $object = $this->repository->findOrFail($id);

        // Drop the previous photo from disk so we don't leak storage on repeated edits.
        if ($object->main_photo_path) {
            Storage::disk('public')->delete($object->main_photo_path);
        }

        $path = $file->storePublicly("object-photos/{$object->company_id}/{$object->id}", 'public');
        $updated = $this->repository->update($object, ['main_photo_path' => $path]);
        event(new ObjectUpdatedEvent($updated));

        return $updated;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $object = $this->repository->findOrFail($id);
            $this->repository->delete($object);
            event(new ObjectDeletedEvent($object));
        });
    }

    /**
     * @inheritDoc
     */
    public function buildingsForScopePicker(): array
    {
        $all = $this->repository->allOrderedWithCameras(['id', 'object_id', 'name']);

        $byParent = $all->groupBy('parent_id');

        /**
         * Collects all cameras from the subtree rooted at the given object id.
         *
         * @param int $rootId
         * @return array<int, array{id:int, name:string}>
         */
        $gatherCameras = function (int $rootId) use (&$gatherCameras, $byParent, $all): array {
            $node = $all->firstWhere('id', $rootId);
            $cameras = $node ? $node->cameras->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->all() : [];
            foreach ($byParent->get($rootId, collect()) as $child) {
                $cameras = array_merge($cameras, $gatherCameras($child->id));
            }
            return $cameras;
        };

        $buildings = [];
        foreach ($all->whereNull('parent_id') as $root) {
            $addresses = [];
            foreach ($byParent->get($root->id, collect()) as $child) {
                $addresses[] = [
                    'id' => $child->id,
                    'name' => $child->name,
                    'cameras' => $gatherCameras($child->id),
                ];
            }
            $buildings[] = [
                'id' => $root->id,
                'name' => $root->name,
                // Cameras attached DIRECTLY under the root object (flat hierarchy with no sub-objects).
                // Without this the scope picker skipped cameras like "Havira -> ahead/behind".
                'cameras' => $root->cameras->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->all(),
                'addresses' => $addresses,
            ];
        }
        return $buildings;
    }

    /**
     * Computes depth from the parent (root = 0).
     *
     * @param int|null $parentId parent id or null for root
     * @return int depth of the new node
     */
    protected function calculateDepth(?int $parentId): int
    {
        if ($parentId === null) {
            return 0;
        }

        $parentDepth = $this->repository->depthOf($parentId);
        return $parentDepth !== null ? $parentDepth + 1 : 0;
    }

    /**
     * Generates a unique slug within the company (via the repository's slugExists check).
     *
     * @param string $name object name to base the slug on
     * @return string slug guaranteed to be free
     */
    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'object';
        $slug = $base;
        $i = 1;
        while ($this->repository->slugExists($slug)) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }
}
