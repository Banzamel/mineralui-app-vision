<?php

namespace Tests\Feature\Cameras;

use Illuminate\Support\Facades\Crypt;
use Objects\Models\Camera;
use Objects\Models\VisionObject;
use Tests\Feature\ApiTestCase;

class CameraTest extends ApiTestCase
{
    /**
     * Przygotowuje obiekt nadrzędny dla kamer.
     */
    protected function freshObject(): VisionObject
    {
        return VisionObject::factory()->create(['company_id' => $this->company->id]);
    }

    // ── LIST ──

    public function test_admin_can_list_cameras(): void
    {
        $this->actingAsAdmin();
        $object = $this->freshObject();
        Camera::factory()->create([
            'company_id' => $this->company->id,
            'object_id' => $object->id,
        ]);

        $response = $this->getJson('/api/vision/cameras');

        $response->assertOk();
    }

    public function test_operator_can_list_cameras(): void
    {
        $this->actingAsOperator();

        $response = $this->getJson('/api/vision/cameras');

        $response->assertOk();
    }

    public function test_unauthenticated_cannot_list_cameras(): void
    {
        $response = $this->getJson('/api/vision/cameras');

        $response->assertUnauthorized();
    }

    // ── CREATE (password encryption) ──

    public function test_admin_can_create_camera_and_password_is_encrypted(): void
    {
        $this->actingAsAdmin();
        $object = $this->freshObject();

        $response = $this->postJson('/api/vision/cameras', [
            'object_id' => $object->id,
            'name' => 'cam-1',
            'stream_url' => 'rtsp://example.local/stream',
            'stream_login' => 'admin',
            'stream_password' => 'supersecret',
        ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'cam-1')
            ->assertJsonMissing(['stream_password_encrypted' => 'supersecret']);

        $camera = Camera::where('name', 'cam-1')->firstOrFail();
        $this->assertNotNull($camera->stream_password_encrypted);
        $this->assertSame('supersecret', Crypt::decryptString($camera->stream_password_encrypted));
    }

    public function test_create_camera_fails_without_required_fields(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/vision/cameras', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['object_id', 'name', 'stream_url']);
    }

    // ── UPDATE ──

    public function test_admin_can_update_camera(): void
    {
        $this->actingAsAdmin();
        $object = $this->freshObject();
        $camera = Camera::factory()->create([
            'company_id' => $this->company->id,
            'object_id' => $object->id,
        ]);

        $response = $this->patchJson("/api/vision/cameras/{$camera->id}", [
            'display_name' => 'Nowa Nazwa',
        ]);

        $response->assertOk()
            ->assertJsonPath('display_name', 'Nowa Nazwa');
    }

    // ── DELETE ──

    public function test_admin_can_delete_camera(): void
    {
        $this->actingAsAdmin();
        $object = $this->freshObject();
        $camera = Camera::factory()->create([
            'company_id' => $this->company->id,
            'object_id' => $object->id,
        ]);

        $response = $this->deleteJson("/api/vision/cameras/{$camera->id}");

        $response->assertOk();
        $this->assertSoftDeleted('vision_cameras', ['id' => $camera->id]);
    }
}
