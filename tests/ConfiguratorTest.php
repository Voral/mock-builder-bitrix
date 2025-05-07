<?php

declare(strict_types=1);

namespace Vasoft\MockBuilderBitrix\Tests;

use PHPUnit\Framework\TestCase;
use Vasoft\MockBuilder\Visitor\AddMockToolsVisitor;
use Vasoft\MockBuilder\Visitor\PublicAndConstFilter;
use Vasoft\MockBuilder\Visitor\RemoveFinalVisitor;
use Vasoft\MockBuilder\Visitor\SetReturnTypes;
use Vasoft\MockBuilderBitrix\Configurator;

/**
 * @internal
 *
 * @coversDefaultClass \Vasoft\MockBuilderBitrix\Configurator
 */
final class ConfiguratorTest extends TestCase
{
    private array $reflectionCache = [];
    private static string $fakePath = __DIR__ . '/fake/';

    private static function getPaths(): array
    {
        return [
            self::$fakePath,
            self::$fakePath . 'iblock/',
            self::$fakePath . 'iblock/lib',
            self::$fakePath . 'main/',
            self::$fakePath . 'main/interface',
            self::$fakePath . 'main/interfaces',
            self::$fakePath . 'main/lib',
            self::$fakePath . 'main/classes',
            self::$fakePath . 'sale/',
            self::$fakePath . 'sale/user',
            self::$fakePath . 'store/',
            self::$fakePath . 'store/classes',
            self::$fakePath . 'vendor.module/',
            self::$fakePath . 'vendor.module/lib',
        ];
    }

    public static function setUpBeforeClass(): void
    {
        array_map('mkdir', self::getPaths());
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        array_map('rmdir', array_reverse(self::getPaths()));
        parent::tearDownAfterClass();
    }

    public function testGetBitrixMockBuilderSettings(): void
    {
        $expected = [
            self::$fakePath . 'iblock/lib/',
            self::$fakePath . 'main/lib/',
            self::$fakePath . 'main/classes/',
            self::$fakePath . 'main/interface/',
            self::$fakePath . 'store/classes/',
        ];

        $configurator = new Configurator(self::$fakePath);
        $config = $configurator->getBitrixMockBuilderSettings('/example');
        self::assertIsArray($config);

        self::assertCount(3, $config);
        self::assertArrayHasKey('targetPath', $config);
        self::assertSame('/example', $config['targetPath']);
        self::assertArrayHasKey('basePath', $config);
        self::assertEqualsCanonicalizing($expected, $config['basePath']);
        self::assertArrayHasKey('visitors', $config);
        self::assertCount(4, $config['visitors']);
    }

    /**
     * @dataProvider provideGetBitrixVisitorsSomeVersionCases
     */
    public function testGetBitrixMockBuilderSettingsSomeVersion(string $version): void
    {
        $configurator = new Configurator(self::$fakePath);
        $config = $configurator->getBitrixMockBuilderSettings('/example', targetPhpVersion: $version);
        self::assertIsArray($config);

        self::assertArrayHasKey('visitors', $config);
        self::assertIsArray($config['visitors']);
        self::assertCount(4, $config['visitors']);
        self::assertInstanceOf(SetReturnTypes::class, $config['visitors'][1]);
        self::assertSame($version, $this->getObjectProperty($config['visitors'][1], 'targetPhpVersion'));
    }

    public function testGetBitrixMockBuilderSettingsIncludes(): void
    {
        $expected = [
            self::$fakePath . 'iblock/lib/',
            self::$fakePath . 'main/lib/',
            self::$fakePath . 'main/classes/',
            self::$fakePath . 'main/interface/',
        ];

        $configurator = new Configurator(self::$fakePath);
        $config = $configurator->getBitrixMockBuilderSettings('/example', ['iblock', 'main']);
        self::assertIsArray($config);
        self::assertArrayHasKey('basePath', $config);
        self::assertEqualsCanonicalizing($expected, $config['basePath']);
    }

    public function testGetBitrixMockBuilderSettingsExclude(): void
    {
        $expected = [
            self::$fakePath . 'main/lib/',
            self::$fakePath . 'main/classes/',
            self::$fakePath . 'main/interface/',
            self::$fakePath . 'store/classes/',
        ];

        $configurator = new Configurator(self::$fakePath);
        $config = $configurator->getBitrixMockBuilderSettings('/example', excludeModules: ['iblock']);
        self::assertIsArray($config);
        self::assertArrayHasKey('basePath', $config);
        self::assertEqualsCanonicalizing($expected, $config['basePath']);
    }

    public function testGetPathsDefault(): void
    {
        $expected = [
            self::$fakePath . 'iblock/lib/',
            self::$fakePath . 'main/lib/',
            self::$fakePath . 'main/classes/',
            self::$fakePath . 'main/interface/',
            self::$fakePath . 'store/classes/',
        ];
        $configurator = new Configurator(self::$fakePath);
        self::assertEqualsCanonicalizing($expected, $configurator->getPaths());
    }

    public function testGetPathsIncludes(): void
    {
        $expected = [
            self::$fakePath . 'iblock/lib/',
            self::$fakePath . 'store/classes/',
        ];
        $configurator = new Configurator(self::$fakePath);
        self::assertEqualsCanonicalizing($expected, $configurator->getPaths(['iblock', 'store']));
    }

    public function testGetPathsExclude(): void
    {
        $expected = [
            self::$fakePath . 'iblock/lib/',
            self::$fakePath . 'store/classes/',
        ];
        $configurator = new Configurator(self::$fakePath);
        self::assertEqualsCanonicalizing($expected, $configurator->getPaths(excludeModules: ['main']));
    }

    public function testGetPathsAll(): void
    {
        $expected = [
            self::$fakePath . 'vendor.module/lib/',
            self::$fakePath . 'iblock/lib/',
            self::$fakePath . 'main/lib/',
            self::$fakePath . 'main/classes/',
            self::$fakePath . 'main/interface/',
            self::$fakePath . 'store/classes/',
        ];
        $configurator = new Configurator(self::$fakePath);
        self::assertEqualsCanonicalizing($expected, $configurator->getPaths(bitrixOnly: false));
    }

    public function testGetBitrixVisitors(): void
    {
        $configurator = new Configurator(self::$fakePath);
        $visitors = $configurator->getBitrixVisitors();
        self::assertIsArray($visitors);

        self::assertCount(4, $visitors);

        self::assertInstanceOf(PublicAndConstFilter::class, $visitors[0]);
        self::assertTrue($this->getObjectProperty($visitors[0], 'skipThrowable'));

        self::assertInstanceOf(SetReturnTypes::class, $visitors[1]);
        self::assertSame('8.1', $this->getObjectProperty($visitors[1], 'targetPhpVersion'));
        self::assertTrue($this->getObjectProperty($visitors[1], 'skipThrowable'));

        self::assertInstanceOf(AddMockToolsVisitor::class, $visitors[2]);
        self::assertSame('Bitrix', $this->getObjectProperty($visitors[2], 'baseNamespace'));
        self::assertTrue($this->getObjectProperty($visitors[2], 'skipThrowable'));
        self::assertFalse($this->getObjectProperty($visitors[2], 'copyDefinition'));
        self::assertFalse($this->getObjectProperty($visitors[2], 'copyFunction'));

        self::assertInstanceOf(RemoveFinalVisitor::class, $visitors[3]);
        self::assertFalse($this->getObjectProperty($visitors[3], 'skipThrowable'));
    }

    /**
     * @dataProvider provideGetBitrixVisitorsSomeVersionCases
     */
    public function testGetBitrixVisitorsSomeVersion(string $version): void
    {
        $configurator = new Configurator(self::$fakePath);
        $visitors = $configurator->getBitrixVisitors($version);
        self::assertIsArray($visitors);
        self::assertCount(4, $visitors);
        self::assertInstanceOf(SetReturnTypes::class, $visitors[1]);
        self::assertSame($version, $this->getObjectProperty($visitors[1], 'targetPhpVersion'));
    }

    public static function provideGetBitrixVisitorsSomeVersionCases(): iterable
    {
        yield ['7.4'];
        yield ['8.3'];
    }

    private function getObjectProperty(object $object, string $property)
    {
        $reflection = $this->getReflectionObject($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    private function getReflectionObject($object): \ReflectionObject
    {
        $key = spl_object_hash($object);
        if (!isset($this->reflectionCache[$key])) {
            $this->reflectionCache[$key] = new \ReflectionObject($object);
        }

        return $this->reflectionCache[$key];
    }
}
