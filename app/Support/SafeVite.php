<?php

namespace App\Support;

use Illuminate\Foundation\Vite;
use Illuminate\Support\HtmlString;

class SafeVite
{
    public function tags(array $assets): HtmlString
    {
        $vite = app(Vite::class);

        if ($this->shouldFallbackToBuildAssets(public_path('hot'))) {
            return $vite->useHotFile(public_path('hot.disabled'))($assets);
        }

        return $vite($assets);
    }

    private function shouldFallbackToBuildAssets(string $hotFile): bool
    {
        if (! is_file($hotFile)) {
            return false;
        }

        $devServerUrl = trim((string) file_get_contents($hotFile));

        if ($devServerUrl === '') {
            return true;
        }

        $parts = parse_url($devServerUrl);

        if (! is_array($parts) || empty($parts['host'])) {
            return true;
        }

        $scheme = $parts['scheme'] ?? 'http';
        $host = $parts['host'];
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
        $socketHost = str_contains($host, ':') ? '['.$host.']' : $host;
        $socket = @stream_socket_client(
            sprintf('tcp://%s:%d', $socketHost, $port),
            $errorCode,
            $errorMessage,
            0.25
        );

        if (! is_resource($socket)) {
            return true;
        }

        fclose($socket);

        return false;
    }
}
