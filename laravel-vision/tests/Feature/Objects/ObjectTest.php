<?php

namespace Tests\Feature\Objects;

use Objects\Models\VisionObject;
use Tests\Feature\ApiTestCase;

class ObjectTest extends ApiTestCase
{
    // ── LIST / TREE ──

    public function test_admin_can_list_objects(): void
    {
        $this->actingAsAdmin();
        VisionObject::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/vision/objects');

        $response->assertOk();
    }

    public function test_admin_can_get_object_tree(): void
    {
        $this->actingAsAdmin();
        VisionObject::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/vision/objects/tree');

        $response->assertOk();
    }

    public function test_operator_cannot_list_objects(): void
    {
        $this->actingAsOperator();

        $response = $this->getJson('/api/vision/objects');

        $response->assertForbidden();
    }

    public function test_unauthenticated_cannot_list_objects(): void
    {
        $response = $this->getJson('/api/vision/objects');

        $response->assertUnauthorized();
    }

    // ── CREATE ──

    public function test_admin_can_create_object(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/vision/objects', [
            'name' => 'Budynek A',
            'type' => 'block',
            'address' => 'ul. Przykładowa 1',
        ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'Budynek A')
            ->assertJsonPath('type', 'block');
    }

    public function test_create_object_fails_without_name(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/vision/objects', [
            'type' => 'block',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_object_fails_with_invalid_type(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/vision/objects', [
            'name' => 'Test',
            'type' => 'skyscraper',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    // ── UPDATE ──

    public function test_admin_can_update_object(): void
    {
        $this->actingAsAdmin();
        $object = VisionObject::factory()->create(['company_id' => $this->company->id]);

        $response = $this->patchJson("/api/vision/objects/{$object->id}", [
            'name' => 'Nowa Nazwa',
        ]);

        $response->assertOk()
            ->assertJsonPath('name', 'Nowa Nazwa');
    }

    // ── DELETE ──

    public function test_admin_can_delete_object(): void
    {
        $this->actingAsAdmin();
        $object = VisionObject::factory()->create(['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/vision/objects/{$object->id}");

        $response->assertOk();
        $this->assertSoftDeleted('vision_objects', ['id' => $object->id]);
    }
}
