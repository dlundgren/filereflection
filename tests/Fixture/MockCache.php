<?php

namespace SyberIsle\FileReflection\Fixture;

use SyberIsle\FileReflection\CacheProvider;

class MockCache
	implements CacheProvider
{
	public $cache = array();

	public $time = array();

	/**
	 * @var int
	 */
	public $hit = 0;

	/**
	 * @var int
	 */
	public $missed = 0;

	/**
	 * @inheritdoc
	 */
	public function read($key, $timestamp, $refresh)
	{
		if (isset($this->cache[$key]) && $this->time[$key] >= $timestamp) {
			$this->hit += 1;

			return $this->cache[$key];
		}

		$this->missed += 1;

		$data = call_user_func($refresh);

		$this->cache[$key] = $data;
		$this->time[$key]  = $timestamp;

		return $data;
	}
}