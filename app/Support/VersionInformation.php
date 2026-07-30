<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use RuntimeException;

class VersionInformation
{
    /**
     * Determine whether a newer version is available, even when a server
     * version contains additional text such as "Laravel Framework".
     */
    public function isUpgradeAvailable(string $usedVersion, string $latestVersion): bool
    {
        $used = $this->extractVersion($usedVersion);
        $latest = $this->extractVersion($latestVersion);

        return $used !== null && $latest !== null && version_compare($latest, $used, '>');
    }

    /**
     * Get current upstream releases. Results are cached for a day so the
     * footer does not make an external request on every page load.
     */
    public function latest(): array
    {
        try {
            $cached = Cache::get('latest-upstream-versions-v3');

            if (is_array($cached)) {
                return $cached;
            }

            $versions = $this->fetchLatest();

            if (!in_array('Unavailable', $versions, true)) {
                Cache::put('latest-upstream-versions-v3', $versions, now()->addDay());
            }

            return $versions;
        } catch (\Throwable) {
            // Continue without caching if the configured cache store is unavailable.
            return $this->fetchLatest();
        }
    }

    private function fetchLatest(): array
    {
        return [
            'laravel' => $this->safely(fn () => $this->latestLaravel()),
            'mysql' => $this->safely(fn () => $this->latestMySql()),
            'php' => $this->safely(fn () => $this->latestPhp()),
        ];
    }

    private function latestLaravel(): string
    {
        $release = json_decode($this->get('https://api.github.com/repos/laravel/framework/releases/latest'), true, flags: JSON_THROW_ON_ERROR);

        return $release['tag_name'] ?? throw new RuntimeException('Laravel version was not returned.');
    }

    private function latestMySql(): string
    {
        $downloadsPage = $this->get('https://dev.mysql.com/downloads/mysql/');

        preg_match('/MySQL Community Server\\s*([0-9]+(?:\\.[0-9]+){1,2})/i', $downloadsPage, $matches);

        return $matches[1] ?? 'Unavailable';
    }

    private function latestPhp(): string
    {
        $releases = json_decode($this->get('https://www.php.net/releases/?json'), true, flags: JSON_THROW_ON_ERROR);

        $versions = array_filter(array_column($releases, 'version'), fn ($version) => is_string($version));

        usort($versions, 'version_compare');

        return array_pop($versions) ?: 'Unavailable';
    }

    private function safely(callable $callback): string
    {
        try {
            return $callback();
        } catch (\Throwable) {
            return 'Unavailable';
        }
    }

    private function get(string $url): string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'header' => "Accept: application/json\r\nUser-Agent: curl/8.0",
            ],
        ]);

        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            throw new RuntimeException("Unable to retrieve {$url}");
        }

        return $response;
    }

    private function extractVersion(string $version): ?string
    {
        preg_match('/\d+(?:\.\d+){1,2}/', $version, $matches);

        return $matches[0] ?? null;
    }
}
