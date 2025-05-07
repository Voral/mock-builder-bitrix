<?php

declare(strict_types=1);

namespace Vasoft\MockBuilderBitrix;

class Autoloader
{
    public function __construct(
        private readonly string $path,
        private readonly array $classes = [],
        private readonly array $includes = [],
    ) {}

    public function register(): void
    {
        spl_autoload_register([$this, 'autoload']);
    }

    public function autoload(string $className): void
    {
        if (array_key_exists($className, $this->classes)) {
            if (file_exists($this->classes[$className])) {
                require_once $this->classes[$className];
            }

            return;
        }
        $className = str_replace('\\', \DIRECTORY_SEPARATOR, $className);

        $filePath = $this->path . '/' . $className . '.php';
        if (file_exists($filePath)) {
            require_once $filePath;
        }
    }

    public function includes(): void
    {
        $this->includeFromPath(__DIR__ . '/functions/');
        foreach ($this->includes as $include) {
            $this->includeFromPath($include);
        }
    }

    private function includeFromPath(string $path): void
    {
        $result = [];
        $iterator = new \DirectoryIterator($path);
        foreach ($iterator as $file) {
            if ($file->isDot()) {
                continue;
            }
            if ($file->isFile() && 'php' === $file->getExtension()) {
                include_once $file->getRealPath();
            }
        }
    }

    public function registerAll(): void
    {
        $this->register();
        $this->includes();
    }
}
