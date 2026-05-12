<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TechEnby\Docify\Http\Middleware\EnsureDocifyCanBeViewed;

beforeEach(function (): void {
    config()->set('app.key', 'base64:' . base64_encode(str_repeat('a', 32)));
    config()->set('docify.folder', './docs-test');
    config()->set('docify.environments', ['testing']);

    File::deleteDirectory(base_path('docs-test'));
    File::ensureDirectoryExists(base_path('docs-test/guides'));
});

afterEach(function (): void {
    File::deleteDirectory(base_path('docs-test'));
    File::deleteDirectory(base_path('docs-test-private'));
});

test('renders the requested markdown page', function (): void {
    File::put(base_path('docs-test/index.md'), '# Welcome');
    File::put(base_path('docs-test/guides/setup.md'), <<<'MARKDOWN'
---
title: Setup Guide
---

# Install Docify

Run `composer require techenby/docify`.
MARKDOWN);

    Livewire::test('docify::docs', ['page' => 'guides/setup'])
        ->assertSet('page', 'guides/setup')
        ->assertSet('path', realpath(base_path('docs-test/guides/setup.md')))
        ->assertSeeHtml('<h1 id="install-docify">Install Docify</h1>')
        ->assertSee('composer require techenby/docify');
});

test('defaults to the index page', function (): void {
    File::put(base_path('docs-test/index.md'), '# Package Docs');

    Livewire::test('docify::docs')
        ->assertSet('page', 'index')
        ->assertSeeHtml('<h1 id="package-docs">Package Docs</h1>');
});

test('builds the sidebar from markdown front matter', function (): void {
    File::put(base_path('docs-test/index.md'), <<<'MARKDOWN'
---
title: Welcome
order: 2
---

# Welcome
MARKDOWN);

    File::put(base_path('docs-test/api.md'), <<<'MARKDOWN'
---
title: API Reference
order: 1
---

# API
MARKDOWN);

    File::put(base_path('docs-test/guides/setup.md'), <<<'MARKDOWN'
---
title: Setup Guide
order: 1
---

# Setup
MARKDOWN);

    File::put(base_path('docs-test/guides/advanced.md'), <<<'MARKDOWN'
---
title: Advanced Usage
order: 2
---

# Advanced
MARKDOWN);

    $sidebar = Livewire::test('docify::docs')->get('sidebar');

    expect($sidebar)->toHaveKeys(['', 'Guides'])
        ->and(array_column($sidebar[''], 'label'))->toBe(['Api Reference', 'Welcome'])
        ->and(array_column($sidebar['Guides'], 'label'))->toBe(['Setup Guide', 'Advanced Usage'])
        ->and($sidebar['Guides'][0]['href'])->toBe(route('docs', ['page' => 'guides/setup']));
});

test('returns a not found response for missing pages', function (): void {
    File::put(base_path('docs-test/index.md'), '# Welcome');

    $this->get('/docs/missing')->assertNotFound();
});

test('renders docs in configured non local environments without an edit link', function (): void {
    $this->app['env'] = 'staging';
    config()->set('docify.environments', ['local', 'staging']);

    File::put(base_path('docs-test/index.md'), '# Package Docs');

    Livewire::test('docify::docs')
        ->assertSee('Package Docs')
        ->assertDontSee('Edit');
});

test('uses the configured docify editor for local edit links', function (): void {
    $this->app['env'] = 'local';
    config()->set('docify.editor', 'cursor');

    File::put(base_path('docs-test/index.md'), '# Package Docs');

    expect(Livewire::test('docify::docs')->get('editUrl'))
        ->toBe('cursor://file/' . realpath(base_path('docs-test/index.md')));
});

test('falls back to vscode for unknown local editors', function (): void {
    $this->app['env'] = 'local';
    config()->set('docify.editor', 'unknown');

    File::put(base_path('docs-test/index.md'), '# Package Docs');

    expect(Livewire::test('docify::docs')->get('editUrl'))
        ->toBe('vscode://file/' . realpath(base_path('docs-test/index.md')));
});

test('does not allow traversing outside the configured docs folder', function (): void {
    File::ensureDirectoryExists(base_path('docs-test-private'));
    File::put(base_path('docs-test/index.md'), '# Public Docs');
    File::put(base_path('docs-test-private/secret.md'), '# Secret Docs');

    $this->get('/docs/%2E%2E%2Fdocs-test-private%2Fsecret')
        ->assertNotFound();
});

test('registers the environment guard on the docs route', function (): void {
    expect(Route::getRoutes()->getByName('docs')->gatherMiddleware())
        ->toContain(EnsureDocifyCanBeViewed::class);
});

test('only allows configured environments to view docs', function (): void {
    $middleware = new EnsureDocifyCanBeViewed;
    $request = request();
    $next = fn () => response('allowed');

    $this->app['env'] = 'local';
    config()->set('docify.environments', ['local']);

    expect($middleware->handle($request, $next)->getContent())->toBe('allowed');

    $this->app['env'] = 'production';

    $middleware->handle($request, $next);
})->throws(NotFoundHttpException::class);

test('allows users to configure additional docs environments', function (): void {
    $middleware = new EnsureDocifyCanBeViewed;

    $this->app['env'] = 'staging';
    config()->set('docify.environments', ['local', 'staging']);

    expect($middleware->handle(request(), fn () => response('allowed'))->getContent())->toBe('allowed');
});
