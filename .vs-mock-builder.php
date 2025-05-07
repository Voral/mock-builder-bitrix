<?php

declare(strict_types=1);

use Vasoft\MockBuilder\Visitor\AddMockToolsVisitor;
use Vasoft\MockBuilder\Visitor\PublicAndConstFilter;
use Vasoft\MockBuilder\Visitor\RemoveFinalVisitor;
use Vasoft\MockBuilder\Visitor\SetReturnTypes;

$basePaths = '';
$local = __DIR__ . '/.vs-mock-builder.local.php';
if (file_exists($local)) {
    $basePaths = require_once $local;
}

return [
    'basePath' => $basePaths,
    'targetPath' => __DIR__ . '/bx/',
    'visitors' => [
        new PublicAndConstFilter(true),
        new SetReturnTypes('8.1', true),
        new AddMockToolsVisitor('Bitrix', true),
        new RemoveFinalVisitor()
    ],
];
