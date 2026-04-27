<?php

namespace Installer\Services;

use Installer\Services\Interfaces\EnvWriterServiceInterface;
use RuntimeException;

/**
 * Service that writes changes to the .env file - updates existing keys or appends new ones at the end.
 */
class EnvWriterService implements EnvWriterServiceInterface
{
    /**
     * @inheritDoc
     */
    public function update(array $values): void
    {
        $path = base_path('.env');

        if (! is_file($path)) {
            file_put_contents($path, '');
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException('Unable to read .env file.');
        }

        foreach ($values as $key => $value) {
            $escaped = $this->escape((string) $value);
            $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

            if (preg_match($pattern, $contents)) {
                $contents = preg_replace($pattern, $key . '=' . $escaped, $contents);
            } else {
                $contents = rtrim($contents, "\n") . "\n" . $key . '=' . $escaped . "\n";
            }
        }

        $tmp = $path . '.tmp';
        if (file_put_contents($tmp, $contents) === false) {
            throw new RuntimeException('Unable to write .env.tmp file.');
        }

        if (! rename($tmp, $path)) {
            @unlink($tmp);
            throw new RuntimeException('Unable to replace .env file atomically.');
        }
    }

    /**
     * Escapes a value for the .env file - wraps in quotes when it contains spaces, quotes or special characters.
     *
     * @param string $value Value to escape.
     * @return string Value ready to be written to .env.
     */
    private function escape(string $value): string
    {
        if ($value === '' || preg_match('/\s|"|\'|#|=/', $value)) {
            return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
        }

        return $value;
    }
}
