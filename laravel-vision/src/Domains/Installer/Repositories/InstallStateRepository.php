<?php

namespace Installer\Repositories;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Artisan;
use Installer\Enums\InstallStage;
use Installer\Repositories\Interfaces\InstallStateRepositoryInterface;
use Installer\Services\Interfaces\EnvWriterServiceInterface;

/**
 * Installer state — flag stored in `.env` as APP_INSTALLED, and the temporary wizard state
 * (stage + step payloads) in the Redis cache. After finalize the cache is cleared
 * because the only source of truth left is APP_INSTALLED=true in .env.
 */
class InstallStateRepository implements InstallStateRepositoryInterface
{
    /**
     * Cache key prefix — shared by stage and payloads, both live only during the wizard.
     */
    private const CACHE_PREFIX = 'install:';

    /**
     * Wizard TTL — 24h is enough for installation, after that the cache expires on its own.
     */
    private const CACHE_TTL_SECONDS = 86400;

    /**
     * @param CacheRepository $cache Redis-backed cache (stores stage and payloads).
     * @param EnvWriterServiceInterface $envWriter Writes APP_INSTALLED to .env on finalize.
     */
    public function __construct(
        private readonly CacheRepository $cache,
        private readonly EnvWriterServiceInterface $envWriter,
    ) {}

    /**
     * @inheritDoc
     */
    public function isInstalled(): bool
    {
        return (bool) config('app.installed', false);
    }

    /**
     * @inheritDoc
     */
    public function getStage(): InstallStage
    {
        if ($this->isInstalled()) {
            return InstallStage::Finalized;
        }
        $value = (string) $this->cache->get(self::CACHE_PREFIX . 'stage', InstallStage::Fresh->value);
        return InstallStage::tryFrom($value) ?? InstallStage::Fresh;
    }

    /**
     * @inheritDoc
     */
    public function getPayload(string $key): ?array
    {
        $value = $this->cache->get(self::CACHE_PREFIX . 'payload:' . $key);
        return is_array($value) ? $value : null;
    }

    /**
     * @inheritDoc
     */
    public function putPayload(string $key, array $value): void
    {
        $this->cache->put(self::CACHE_PREFIX . 'payload:' . $key, $value, self::CACHE_TTL_SECONDS);
    }

    /**
     * @inheritDoc
     */
    public function markStage(InstallStage $stage): void
    {
        $current = $this->getStage();
        if ($stage->isAfter($current)) {
            $this->cache->put(self::CACHE_PREFIX . 'stage', $stage->value, self::CACHE_TTL_SECONDS);
        }
    }

    /**
     * @inheritDoc
     */
    public function finalize(): void
    {
        $this->envWriter->update(['APP_INSTALLED' => 'true']);
        // Reload config so that runtime isInstalled() returns true immediately after this action —
        // without this, config('app.installed') would still hold the value from process boot.
        Artisan::call('config:clear');
        $this->forgetWizardCache();
    }

    /**
     * @inheritDoc
     */
    public function reset(): void
    {
        $this->envWriter->update(['APP_INSTALLED' => 'false']);
        Artisan::call('config:clear');
        $this->forgetWizardCache();
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return [
            'installed' => $this->isInstalled(),
            'stage' => $this->getStage()->value,
        ];
    }

    /**
     * Removes all temporary wizard cache keys.
     */
    private function forgetWizardCache(): void
    {
        $this->cache->forget(self::CACHE_PREFIX . 'stage');
        // Named payloads used by InstallerService (admin/object/camera/company).
        foreach (['admin', 'object', 'camera', 'company'] as $key) {
            $this->cache->forget(self::CACHE_PREFIX . 'payload:' . $key);
        }
    }
}
