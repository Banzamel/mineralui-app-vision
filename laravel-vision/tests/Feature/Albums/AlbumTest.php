<?php

namespace Tests\Feature\Albums;

use Albums\Models\Album;
use Objects\Models\Camera;
use Objects\Models\VisionObject;
use Tests\Feature\ApiTestCase;

class AlbumTest extends ApiTestCase
{
    public function test_admin_can_list_albums(): void
    {
        $this->actingAsAdmin();
        $object = VisionObject::factory()->create(['company_id' => $this->company->id]);
        $camera = Camera::factory()->create([
            'company_id' => $this->company->id,
            'object_id' => $object->id,
        ]);
        Album::factory()->create([
            'company_id' => $this->company->id,
            'camera_id' => $camera->id,
        ]);

        $response = $this->getJson('/api/vision/albums');

        $response->assertOk()
            ->assertJsonCount(1);
    }

    public function test_admin_can_filter_albums_by_camera(): void
    {
        $this->actingAsAdmin();
        $object = VisionObject::factory()->create(['company_id' => $this->company->id]);
        $cameraA = Camera::factory()->create([
            'company_id' => $this->company->id,
            'object_id' => $object->id,
        ]);
        $cameraB = Camera::factory()->create([
            'company_id' => $this->company->id,
            'object_id' => $object->id,
        ]);
        Album::factory()->create(['company_id' => $this->company->id, 'camera_id' => $cameraA->id]);
        Album::factory()->create(['company_id' => $this->company->id, 'camera_id' => $cameraB->id]);

        $response = $this->getJson("/api/vision/albums?camera_id={$cameraA->id}");

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.camera_id', $cameraA->id);
    }

    public function test_admin_can_show_album(): void
    {
        $this->actingAsAdmin();
        $object = VisionObject::factory()->create(['company_id' => $this->company->id]);
        $camera = Camera::factory()->create([
            'company_id' => $this->company->id,
            'object_id' => $object->id,
        ]);
        $album = Album::factory()->create([
            'company_id' => $this->company->id,
            'camera_id' => $camera->id,
        ]);

        $response = $this->getJson("/api/vision/albums/{$album->id}");

        $response->assertOk()
            ->assertJsonPath('id', $album->id);
    }

    public function test_unauthenticated_cannot_list_albums(): void
    {
        $response = $this->getJson('/api/vision/albums');

        $response->assertUnauthorized();
    }
}
