<?php

declare(strict_types=1);

namespace Vasoft\MockBuilderBitrix\Tests;

use Vasoft\MockBuilderBitrix\Autoloader;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass \Vasoft\MockBuilderBitrix\Autoloader
 */
final class AutoloaderTest extends TestCase
{
    private static string $fakePath = __DIR__ . '/fake/';
    private static string $fakeFunctionsPath = '';
    private static string $fakeFunctionsPath2 = '';
    private static string $fakeClassesPath = '';
    private static string $fakeClassesPathExtends = '';

    private static function getPaths(): array
    {
        self::$fakeFunctionsPath = self::$fakePath . 'functions/';
        self::$fakeFunctionsPath2 = self::$fakePath . 'functions2/';
        self::$fakeClassesPath = self::$fakePath . 'classes/';
        self::$fakeClassesPathExtends = self::$fakePath . 'extends/';

        return [
            self::$fakePath,
            self::$fakeFunctionsPath,
            self::$fakeFunctionsPath2,
            self::$fakeClassesPath,
            self::$fakeClassesPath . 'FakeVendor/',
            self::$fakeClassesPathExtends,
        ];
    }

    public static function setUpBeforeClass(): void
    {
        array_map('mkdir', self::getPaths());
        file_put_contents(
            self::$fakeFunctionsPath . 'functions.php',
            '<?php function TestExampleFake1(): void{echo "Hello World";}',
        );
        file_put_contents(
            self::$fakeFunctionsPath . 'functions2.php',
            '<?php function TestExampleFake2(): void{echo "Hello World";}',
        );
        file_put_contents(
            self::$fakeFunctionsPath . 'functions3.md',
            '<?php function TestExampleFake3(): void{echo "Hello World";}',
        );
        file_put_contents(
            self::$fakeFunctionsPath2 . 'functions4.php',
            '<?php function TestExampleFake4(): void{echo "Hello World";}',
        );
        file_put_contents(
            self::$fakeClassesPath . 'FakeVendor/ExampleClass1.php',
            '<?php namespace FakeVendor; class ExampleClass1{}',
        );
        file_put_contents(
            self::$fakeClassesPath . 'FakeVendor/ExampleClass2.php',
            '<?php namespace FakeVendor; class ExampleClass2{}',
        );
        file_put_contents(
            self::$fakeClassesPathExtends . 'ExampleClass2.php',
            '<?php namespace FakeVendor; class ExampleClass2{ public function test(){}}',
        );
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        unlink(self::$fakeFunctionsPath . 'functions.php');
        unlink(self::$fakeFunctionsPath . 'functions2.php');
        unlink(self::$fakeFunctionsPath . 'functions3.md');
        unlink(self::$fakeFunctionsPath2 . 'functions4.php');
        unlink(self::$fakeClassesPath . 'FakeVendor/ExampleClass1.php');
        unlink(self::$fakeClassesPath . 'FakeVendor/ExampleClass2.php');
        unlink(self::$fakeClassesPathExtends . 'ExampleClass2.php');
        array_map('rmdir', array_reverse(self::getPaths()));
        parent::tearDownAfterClass();
    }

    private static array $registeredFunctions = [
        'BeginNote',
        'EndNote',
        'GetMessage',
    ];

    public function testIncludes(): void
    {
        foreach (self::$registeredFunctions as $registeredFunction) {
            self::assertFalse(
                function_exists($registeredFunction),
                "Function {$registeredFunction} is already registered",
            );
        }
        $autoloader = new Autoloader(__DIR__);
        $autoloader->includes();
        foreach (self::$registeredFunctions as $registeredFunction) {
            self::assertTrue(function_exists($registeredFunction), "Function {$registeredFunction} not registered");
        }
    }

    /**
     * @depends testIncludes
     */
    public function testIncludesExtended(): void
    {
        $autoloader = new Autoloader(__DIR__, includes: [self::$fakeFunctionsPath, self::$fakeFunctionsPath2]);
        $autoloader->includes();
        self::assertTrue(function_exists('TestExampleFake1'), 'Function TestExampleFake1 not registered');
        self::assertTrue(function_exists('TestExampleFake2'), 'Function TestExampleFake2 not registered');
        self::assertFalse(function_exists('TestExampleFake3'), 'Function TestExampleFake3 not registered');
        self::assertTrue(function_exists('TestExampleFake4'), 'Function TestExampleFake4 not registered');
    }

    public function testAutoload(): void
    {
        self::assertFalse(class_exists('FakeVendor\ExampleClass1'), 'Class FakeVendor\ExampleClass1 registered');
        self::assertFalse(class_exists('FakeVendor\ExampleClass2'), 'Class FakeVendor\ExampleClass2 registered');
        $autoloader = new Autoloader(
            self::$fakeClassesPath,
            ['FakeVendor\ExampleClass2' => self::$fakeClassesPathExtends . 'ExampleClass2.php'],
        );
        $autoloader->register();
        self::assertTrue(class_exists('FakeVendor\ExampleClass1'), 'Class FakeVendor\ExampleClass1 not registered');
        self::assertTrue(class_exists('FakeVendor\ExampleClass2'), 'Class FakeVendor\ExampleClass2 not registered');
        self::assertTrue(
            method_exists('FakeVendor\ExampleClass2', 'test'),
            'Class FakeVendor\ExampleClass2 registered but not from extended path',
        );
    }

    public function testAutoloadAll(): void
    {
        /** @var Autoloader $mocked */
        $mocked = self::getMockBuilder(Autoloader::class)
            ->onlyMethods(['register', 'includes'])
            ->setConstructorArgs([self::$fakeClassesPath])
            ->getMock();
        $mocked->expects(self::once())->method('register');
        $mocked->expects(self::once())->method('includes');
        $mocked->registerAll();
    }
}
