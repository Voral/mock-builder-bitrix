<?php

declare(strict_types=1);

namespace Vasoft\MockBuilderBitrix;

use Vasoft\MockBuilder\Visitor\AddMockToolsVisitor;
use Vasoft\MockBuilder\Visitor\PublicAndConstFilter;
use Vasoft\MockBuilder\Visitor\RemoveFinalVisitor;
use Vasoft\MockBuilder\Visitor\SetReturnTypes;

class Configurator
{
    public function __construct(
        public readonly string $bitrixModulesPath,
    ) {}

    public function getBitrixVisitors(string $targetPhpVersion = '8.1'): array
    {
        return [
            new PublicAndConstFilter(true),
            new SetReturnTypes($targetPhpVersion, true),
            new AddMockToolsVisitor('Bitrix', true),
            new RemoveFinalVisitor(),
        ];
    }

    public function getPaths(array $modules = [], array $excludeModules = [], bool $bitrixOnly = true): array
    {
        $result = [];
        $iterator = new \DirectoryIterator($this->bitrixModulesPath);
        foreach ($iterator as $file) {
            if ($file->isDot()) {
                continue;
            }
            if (
                $file->isDir()
                && (empty($modules) || in_array($file->getFilename(), $modules, true))
                && (empty($excludeModules) || !in_array($file->getFilename(), $excludeModules, true))
                && (!$bitrixOnly || !str_contains($file->getPathname(), '.'))
            ) {
                $this->processDirectory($file->getRealPath(), $result);
            }
        }

        return $result;
    }

    public function getBitrixMockBuilderSettings(
        string $destinationPath,
        array $modules = [],
        array $excludeModules = [],
        string $targetPhpVersion = '8.1',
    ): array {
        return [
            'targetPath' => $destinationPath,
            'basePath' => $this->getPaths($modules, $excludeModules),
            'visitors' => $this->getBitrixVisitors($targetPhpVersion),
        ];
    }

    private function processDirectory(string $directory, array &$result): void
    {
        if (file_exists($directory . '/lib')) {
            $result[] = $directory . '/lib/';
        }

        if (file_exists($directory . '/classes/')) {
            $result[] = $directory . '/classes/';
        }
        if (file_exists($directory . '/interface/')) {
            $result[] = $directory . '/interface/';
        }
    }
}
