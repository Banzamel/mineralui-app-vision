<?php

namespace Tests\Unit\Services\Installer;

use Installer\Services\EnvWriterService;
use ReflectionClass;
use Tests\TestCase;

class EnvWriterServiceTest extends TestCase
{
    private EnvWriterService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EnvWriterService();
    }

    public function test_escape_leaves_simple_values_unquoted(): void
    {
        $this->assertSame('local', $this->callEscape('local'));
        $this->assertSame('1234', $this->callEscape('1234'));
        $this->assertSame('mysql', $this->callEscape('mysql'));
    }

    public function test_escape_wraps_empty_string(): void
    {
        $this->assertSame('""', $this->callEscape(''));
    }

    public function test_escape_wraps_values_with_whitespace(): void
    {
        $this->assertSame('"hello world"', $this->callEscape('hello world'));
        // Real newline character should still trigger quoting (PCRE \s matches it).
        $expected = "\"line\nbreak\"";
        $this->assertSame($expected, $this->callEscape("line\nbreak"));
    }

    public function test_escape_wraps_values_with_equals_or_hash_or_quotes(): void
    {
        $this->assertSame('"key=value"', $this->callEscape('key=value'));
        $this->assertSame('"comment#here"', $this->callEscape('comment#here'));
        $this->assertSame('"with\\"quote"', $this->callEscape('with"quote'));
    }

    public function test_update_writes_new_keys_and_replaces_existing_ones(): void
    {
        // Use a real temp file as the .env target — skip the base_path() shim entirely by
        // pointing $tmp directly. We do this by writing to a temp dir and overriding base_path.
        $tmpDir = sys_get_temp_dir() . '/vision-env-' . uniqid();
        mkdir($tmpDir);
        file_put_contents($tmpDir . '/.env', "APP_NAME=Vision\nFOO=bar\n");

        $this->app->setBasePath($tmpDir);

        $this->service->update([
            'APP_NAME' => 'Vision Updated',
            'NEW_KEY' => 'new value',
        ]);

        $contents = file_get_contents($tmpDir . '/.env');
        $this->assertStringContainsString('APP_NAME="Vision Updated"', $contents);
        $this->assertStringContainsString('FOO=bar', $contents);
        $this->assertStringContainsString('NEW_KEY="new value"', $contents);

        // Cleanup.
        unlink($tmpDir . '/.env');
        rmdir($tmpDir);
    }

    private function callEscape(string $value): string
    {
        $ref = new ReflectionClass($this->service);
        $m = $ref->getMethod('escape');
        $m->setAccessible(true);
        return $m->invoke($this->service, $value);
    }
}
