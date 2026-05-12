<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use League\CommonMark\Extension\FrontMatter\Data\SymfonyYamlFrontMatterParser;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterParser;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use PomoDocs\CommonMark\Alert\AlertExtension;
use Tempest\Highlight\CommonMark\HighlightExtension;

new #[Layout('docify::docs-layout')] class extends Component
{
    #[Locked]
    public string $page = 'index';

    #[Locked]
    public string $path;

    public function mount(?string $page = null): void
    {
        $this->page = trim($page ?: 'index', '/') ?: 'index';

        $docsPath = realpath(base_path(trim(config('docify.folder'), './')));
        $resolvedPath = $docsPath
            ? realpath(sprintf('%s/%s.md', $docsPath, $this->page))
            : false;

        abort_unless(
            $docsPath
            && $resolvedPath
            && str_starts_with($resolvedPath, $docsPath . DIRECTORY_SEPARATOR),
            404
        );

        $this->path = $resolvedPath;
    }

    #[Computed]
    public function editUrl(): ?string
    {
        if (! App::isLocal()) {
            return null;
        }

        $editor = config()->string('docify.editor', 'vscode');

        return match ($editor) {
            'cursor' => 'cursor://file/' . $this->path,
            'phpstorm' => 'phpstorm://open?file=' . $this->path,
            'sublime' => 'subl://open?url=file://' . $this->path,
            'atom' => 'atom://open?url=file://' . $this->path,
            'zed' => 'zed://open?path=' . $this->path,
            default => 'vscode://file/' . $this->path,
        };
    }

    /** @return array<string, list<array{path: string, href: string, label: string, directory: string|null, order: int}>> */
    #[Computed]
    public function sidebar(): array
    {
        /** @var list<array{path: string, href: string, label: string, directory: string|null, order: int}> $pages */
        $pages = [];
        $basePath = base_path(trim(config('docify.folder'), './'));
        $frontMatterParser = new FrontMatterParser(new SymfonyYamlFrontMatterParser);

        foreach (File::allFiles($basePath) as $file) {
            if ($file->getExtension() !== 'md') {
                continue;
            }

            $relativePath = Str::of($file->getRelativePathname())
                ->replace('.md', '')
                ->replace(DIRECTORY_SEPARATOR, '/');

            $frontMatter = $frontMatterParser->parse(File::get($file->getRealPath()))->getFrontMatter();

            $label = (string) Str::of($frontMatter['title'] ?? $relativePath->afterLast('/'))
                ->replace('-', ' ')
                ->title();

            $directory = Str::of($relativePath)->contains('/')
                ? (string) Str::of($relativePath)->beforeLast('/')->title()
                : null;

            $pages[] = [
                'path' => (string) $relativePath,
                'href' => route(config('docify.route_name'), ['page' => (string) $relativePath]),
                'label' => $label,
                'directory' => $directory,
                'order' => (int) ($frontMatter['order'] ?? PHP_INT_MAX),
            ];
        }

        /** @var array<string, list<array{path: string, href: string, label: string, directory: string|null, order: int}>> */
        return collect($pages)
            ->sortBy(fn (array $page): array => [$page['order'], $page['label']])
            ->groupBy('directory')
            ->sortKeys()
            ->toArray();
    }

    #[Computed]
    public function content(): string
    {
        return Str::markdown(File::get($this->path), options: [
            'alert' => [
                'icons' => ['active' => true, 'use_svg' => true],
            ],
            'heading_permalink' => [
                'insert' => 'none',
                'id_prefix' => '',
                'fragment_prefix' => '',
                'apply_id_to_heading' => true,
            ],
        ], extensions: [
            new AlertExtension,
            new FrontMatterExtension,
            new HeadingPermalinkExtension,
            new HighlightExtension,
        ]);
    }
};
?>

<x-slot:sidebar>
    <flux:sidebar.nav>
        @foreach ($this->sidebar as $directory => $pages)
            @if ($directory)
                <flux:sidebar.group expandable :heading="$directory" class="grid">
                    @foreach ($pages as $sidebarPage)
                        <flux:sidebar.item
                            wire:navigate
                            :href="$sidebarPage['href']"
                            :current="$sidebarPage['path'] === $this->page"
                        >
                            {{ $sidebarPage['label'] }}
                        </flux:sidebar.item>
                    @endforeach
                </flux:sidebar.group>
            @else
                @foreach ($pages as $sidebarPage)
                    <flux:sidebar.item
                        wire:navigate
                        :href="$sidebarPage['href']"
                        :current="$sidebarPage['path'] === $this->page"
                    >
                        {{ $sidebarPage['label'] }}
                    </flux:sidebar.item>
                @endforeach
            @endif
        @endforeach
    </flux:sidebar.nav>
</x-slot:sidebar>

<div class="relative w-full">
    @if ($this->editUrl)
        <flux:button icon="pencil" size="sm" class="!absolute top-0 right-0" :href="$this->editUrl" target="_window">Edit</flux:button>
    @endif

    <article class="prose prose-zinc max-w-none dark:prose-invert">
        {!! $this->content !!}
    </article>
</div>
