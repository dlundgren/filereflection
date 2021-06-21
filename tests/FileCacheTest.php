<?php

namespace SyberIsle\FileReflection;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;

class FileCacheTest
	extends TestCase
{
	/**
	 * @var \org\bovigo\vfs\vfsStreamDirectory
	 */
	protected $vfs;

	/**
	 * @var FileCache
	 */
	protected $cache;

	public function setUp(): void
	{
		$this->vfs   = vfsStream::setup(
			'cache',
			null,
			[
				sha1('kakaw') . '.php' => '<?php return "test";'
			]
		);
		$this->cache = new FileCache($this->vfs->url());
	}

	public function testReadWithExistingFile()
	{
		self::assertEquals(
			'test',
			$this->cache->read(
				'kakaw',
				0,
				function () {
					throw new \RuntimeException("Must exist");
				}
			)
		);
	}

	public function testReadWritesOnNonExistingFile()
	{
		self::assertEquals(
			'written',
			$this->cache->read(
				'writeme',
				0,
				function () {
					return 'written';
				}
			)
		);

		self::assertFileExists($this->vfs->url() . '/' . sha1('writeme') . '.php');
	}

	public function testWriteThrowsExceptionWhenUnableToWrite()
	{
		$cachePath = $this->vfs->url() . '/tmp';
		$path      = "{$cachePath}/" . sha1('kakaw') . '.php';

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage("unable to write cache file: {$path}");

		$cache = new FileCache($cachePath);
		$cache->read(
			'kakaw',
			0,
			function () {
				return 'test';
			}
		);
	}

	public function testWriteThrowsExceptionWhenUnableToChangeMode()
	{
		$file      = sha1('kakaw') . '.php';
		$cachePath = $this->vfs->url();
		$path      = "{$cachePath}/{$file}";

		$fs = new class($file) extends vfsStreamFile {
			public function isOwnedByUser($user)
			{
				return false;
			}
		};
		$this->vfs->addChild($fs);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage("unable to set cache file mode: {$path}");

		$cache = new FileCache($cachePath);
		$cache->read(
			'kakaw',
			time() + 1000,
			function () {
				return 'test';
			}
		);
	}
}