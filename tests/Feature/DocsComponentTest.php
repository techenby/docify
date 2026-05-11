<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Livewire\Livewire;

beforeEach(function (): void {
    config()->set('app.key', 'base64:' . base64_encode(str_repeat('a', 32)));
    config()->set('docify.folder', './docs-test');

    File::deleteDirectory(base_path('docs-test'));
    File::ensureDirectoryExists(base_path('docs-test/guides'));
});

afterEach(function (): void {
    File::deleteDirectory(base_path('docs-test'));
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
