<?php

declare(strict_types=1);

return [
    'route' => '/docs',
    'route_name' => 'docs',
    'prefix' => 'docify',
    'folder' => './docs',
    'environments' => ['local'],
    'editor' => env('DOCIFY_EDITOR') ?: env('DEBUGBAR_EDITOR') ?: env('IGNITION_EDITOR', 'vscode'),
];
