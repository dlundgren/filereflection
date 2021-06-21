<?php

namespace SyberIsle\FileReflection;

use Other\World\Baz;
use Other\World\Nib;
use PHPUnit\Framework\TestCase;
use SyberIsle\FileReflection\Fixture\MockCache;

class ReflectionFileTest
	extends TestCase
{
	public function provideFilesWithNamespace()
	{
		return [
			[__DIR__ . '/Fixture/TestA.php', null],
			[__DIR__ . '/Fixture/TestB.php', 'Hello'],
			[__DIR__ . '/Fixture/TestC.php', 'Hello\World']
		];
	}

	/**
	 * @dataProvider provideFilesWithNamespace
	 */
	public function testCanReadNamespaces($testFile, $expectedNamespace)
	{
		self::assertEquals(
			$expectedNamespace,
			(new ReflectionFile($testFile))->getNamespaceName()
		);
	}

	public function testCanReadClasses()
	{
		self::assertEquals(
			['Hello\World\Foo', 'Hello\World\Bar'],
			array_map(
				function (\ReflectionClass $c) {
					return $c->getName();
				},
				(new ReflectionFile(__DIR__ . '/Fixture/TestC.php'))->getClasses()
			)
		);
	}

	public function provideTestCNameResolutions()
	{
		$file = (new ReflectionFile(__DIR__ . '/Fixture/TestC.php'));

		return [
			[$file, 'Foo', '\Hello\World\Foo'],
			[$file, 'Bar', '\Hello\World\Bar'],
			[$file, 'Bat\Wing', '\Hello\World\Bat\Wing'],
			[$file, '\Hello\World\Foo', '\Hello\World\Foo'],
			[$file, '\Hello\World\Bar', '\Hello\World\Bar'],

			// unqualified local name via use clause
			[$file, 'Baz', '\Other\World\Baz'],
			[$file, 'Fud', '\Other\World\Nib'],

			// resolves fully-qualified name
			[$file, '\Other\World\Baz', '\Other\World\Baz'],
			[$file, '\Other\World\Nib', '\Other\World\Nib'],
			[$file, '\Other\World\Fud', '\Other\World\Fud'],
		];
	}

	/**
	 * @dataProvider provideTestCNameResolutions
	 */
	public function testCanResolveTypeNames(ReflectionFile $file, $name, $expected)
	{
		self::assertEquals($expected, $file->resolveName($name));
	}

	public function testCanResolveTypeNameViaClass()
	{
		$file = new ReflectionFile(__DIR__ . '/Fixture/TestC.php');
		self::assertEquals(
			[
				Baz::class,
				Nib::class
			],
			[
				$file->getClass('Baz')->getName(),
				$file->getClass('Fud')->getName()
			]
		);
	}

	public function testCanResolveBuiltInType()
	{
		self::assertEquals(
			'string',
			(new ReflectionFile(__DIR__ . '/Fixture/TestC.php'))->resolveName('string')
		);
	}

	public function testThrowsInvalidArgumentOnGetClassWithPseudoType()
	{
		self::expectException(\InvalidArgumentException::class);
		(new ReflectionFile(__DIR__ . '/Fixture/TestB.php'))->getClass('string');
	}

	public function testThrowsInvalidArgumentOnGetClassWithUndefined()
	{
		self::expectException(\InvalidArgumentException::class);
		(new ReflectionFile(__DIR__ . '/Fixture/TestB.php'))->getClass('Blah');
	}

	public function provideCacheScenarios()
	{
		return [
			[
				new MockCache(),
				[]
			]
		];
	}

	public function testCache()
	{
		$cache = new MockCache();
		new ReflectionFile(__DIR__ . '/Fixture/TestB.php', $cache);
		self::assertEquals(1, $cache->missed);
		self::assertEquals(0, $cache->hit);

		return $cache;
	}

	/**
	 * @depends testCache
	 */
	public function testWarmCache($cache)
	{
		$cache->hit = 0;
		$cache->missed = 0;

		new ReflectionFile(__DIR__ . '/Fixture/TestB.php', $cache);
		self::assertEquals(1, $cache->hit);
		self::assertEquals(0, $cache->missed);
	}

	public function testGetPath()
	{
		self::assertEquals(
			$path = __DIR__ . '/Fixture/TestB.php',
			(new ReflectionFile($path))->getPath()
		);
	}
}