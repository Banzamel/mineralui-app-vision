<?php

namespace Tests\Feature\FileManager;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\ApiTestCase;

class FileManagerTest extends ApiTestCase
{
    // ── LIST ──

    public function test_admin_can_list_files(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/files');

        $response->assertOk();
    }

    public function test_operator_can_list_files(): void
    {
        $this->actingAsOperator();

        $response = $this->getJson('/api/files');

        $response->assertOk();
    }

    public function test_unauthenticated_cannot_list_files(): void
    {
        $response = $this->getJson('/api/files');

        $response->assertUnauthorized();
    }

    // ── CREATE DIRECTORY ──

    public function test_admin_can_create_directory(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/files/directory', [
            'name' => 'Test Directory',
        ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'Test Directory');
    }

    public function test_admin_can_create_subdirectory(): void
    {
        $this->actingAsAdmin();

        $parent = $this->postJson('/api/files/directory', [
            'name' => 'Parent',
        ]);

        $response = $this->postJson('/api/files/directory', [
            'name' => 'Child',
            'parent_id' => $parent->json('id'),
        ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'Child');
    }

    public function test_create_directory_fails_without_name(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/files/directory', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_operator_cannot_create_directory(): void
    {
        $this->actingAsOperator();

        $response = $this->postJson('/api/files/directory', [
            'name' => 'Should Fail',
        ]);

        $response->assertForbidden();
    }

    // ── UPLOAD FILE ──

    public function test_admin_can_upload_file(): void
    {
        Storage::fake('local');
        $this->actingAsAdmin();

        $dir = $this->postJson('/api/files/directory', [
            'name' => 'Upload Dir',
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/files/upload', [
            'file' => $file,
            'path' => '/',
            'type' => 'file',
            'parent_id' => $dir->json('id'),
        ]);

        $response->assertCreated();
    }

    public function test_upload_fails_without_file(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/files/upload', [
            'path' => '/',
            'type' => 'file',
        ]);

        $response->assertStatus(422);
    }

    // ── SHOW ──

    public function test_admin_can_get_file_details(): void
    {
        $this->actingAsAdmin();

        $dir = $this->postJson('/api/files/directory', [
            'name' => 'Details Dir',
        ]);

        $response = $this->getJson("/api/files/{$dir->json('id')}");

        $response->assertOk()
            ->assertJsonPath('name', 'Details Dir');
    }

    // ── UPDATE (RENAME) ──

    public function test_admin_can_rename_item(): void
    {
        $this->actingAsAdmin();

        $dir = $this->postJson('/api/files/directory', [
            'name' => 'Original Name',
        ]);

        $response = $this->putJson("/api/files/{$dir->json('id')}", [
            'name' => 'Renamed',
        ]);

        $response->assertOk();
    }

    // ── DELETE ──

    public function test_admin_can_delete_item(): void
    {
        $this->actingAsAdmin();

        $dir = $this->postJson('/api/files/directory', [
            'name' => 'Deletable',
        ]);

        $response = $this->deleteJson("/api/files/{$dir->json('id')}");

        $response->assertNoContent();
    }

    public function test_operator_cannot_delete_item(): void
    {
        $this->actingAsAdmin();
        $dir = $this->postJson('/api/files/directory', [
            'name' => 'Protected',
        ]);

        $this->actingAsOperator();

        $response = $this->deleteJson("/api/files/{$dir->json('id')}");

        $response->assertForbidden();
    }
}
