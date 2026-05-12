<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->prependLaragonMysqlBinToPath();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    private function prependLaragonMysqlBinToPath(): void
    {
        if (DIRECTORY_SEPARATOR !== '\\') {
            return;
        }

        $currentPath = (string) getenv('PATH');
        $candidates = array_filter([
            env('MYSQL_BIN_PATH'),
            ...glob('C:/laragon/bin/mysql/*/bin') ?: [],
        ]);

        foreach ($candidates as $candidate) {
            $normalized = rtrim(str_replace('\\', '/', (string) $candidate), '/');

            if ($normalized === '' || ! is_dir($normalized)) {
                continue;
            }

            if (file_exists($normalized.'/mysql.exe') && file_exists($normalized.'/mysqldump.exe')) {
                if (! str_contains(str_replace('\\', '/', $currentPath), $normalized)) {
                    putenv('PATH='.$normalized.';'.$currentPath);
                    $_SERVER['PATH'] = $normalized.';'.$currentPath;
                    $_ENV['PATH'] = $normalized.';'.$currentPath;
                }

                return;
            }
        }
    }
}
