<?php

declare(strict_types=1);

namespace TechEnby\Docify\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    protected $signature = 'docify:install {--force : Overwrite an existing index.md file}';

    protected $description = 'Install Docify documentation files';

    public function handle(Filesystem $files): int
    {
        $docsPath = $this->docsPath();
        $indexPath = $docsPath.DIRECTORY_SEPARATOR.'index.md';

        $files->ensureDirectoryExists($docsPath);

        if ($files->exists($indexPath) && ! $this->option('force')) {
            $this->components->warn('Docify documentation already exists.');
            $this->line('Use --force to overwrite '.$indexPath.'.');

            return self::SUCCESS;
        }

        $files->copy(__DIR__.'/../../resources/docs/docify.md', $indexPath);

        $this->components->info('Docify installed successfully.');
        $this->line('Documentation created at '.$indexPath.'.');

        return self::SUCCESS;
    }

    private function docsPath(): string
    {
        $configuredPath = trim((string) config('docify.folder', './docs'));

        if ($configuredPath === '') {
            $configuredPath = './docs';
        }

        if ($this->isAbsolutePath($configuredPath)) {
            return rtrim($configuredPath, '/\\');
        }

        if (str_starts_with($configuredPath, './') || str_starts_with($configuredPath, '.\\')) {
            $configuredPath = substr($configuredPath, 2);
        }

        return base_path($configuredPath);
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR)
            || (bool) preg_match('/^[A-Za-z]:[\/\\\\]/', $path);
    }
}
