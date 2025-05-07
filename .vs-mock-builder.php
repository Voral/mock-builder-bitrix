<?php

declare(strict_types=1);

use Vasoft\MockBuilderBitrix\Configurator;

$bitrixModulesPaths = '';
$local = __DIR__ . '/.vs-mock-builder.local.php';
if (file_exists($local)) {
    $bitrixModulesPaths = require_once $local;
}

$configurator = new Configurator($bitrixModulesPaths);

return $configurator->getBitrixMockBuilderSettings(__DIR__ . '/bx/', ['main', 'blog'], targetPhpVersion: '8.2');
