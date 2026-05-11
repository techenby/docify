<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

test('creates the configured docs folder with the starter index page', function (): void {
    $docsPath = base_path('docs');

    File::deleteDirectory($docsPath);

    $this->artisan('docify:install')
        ->assertSuccessful();

    expect($docsPath)->toBeDirectory()
        ->and($docsPath.'/index.md')->toBeFile()
        ->and(File::get($docsPath.'/index.md'))->toBe(File::get(__DIR__.'/../../resources/docs/docify.md'));
});

test('uses the configured docs folder', function (): void {
    config()->set('docify.folder', './documentation');

    $docsPath = base_path('documentation');

    File::deleteDirectory($docsPath);

    $this->artisan('docify:install')
        ->assertSuccessful();

    expect($docsPath)->toBeDirectory()
        ->and($docsPath.'/index.md')->toBeFile();
});

test('does not overwrite an existing index page unless forced', function (): void {
    $docsPath = base_path('docs');

    File::ensureDirectoryExists($docsPath);
    File::put($docsPath.'/index.md', '# Existing docs');

    $this->artisan('docify:install')
        ->assertSuccessful();

    expect(File::get($docsPath.'/index.md'))->toBe('# Existing docs');

    $this->artisan('docify:install --force')
        ->assertSuccessful();

    expect(File::get($docsPath.'/index.md'))->toBe(File::get(__DIR__.'/../../resources/docs/docify.md'));
});
