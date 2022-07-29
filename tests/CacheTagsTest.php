<?php

namespace Perturbatio\CacheTags\Tests;

require_once dirname(__FILE__) . '/../vendor/autoload.php';

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Compilers\BladeCompiler;
use Perturbatio\CacheTags\CacheTags;
use Perturbatio\CacheTags\CacheTagsProvider;

class CacheTagsTest extends \Orchestra\Testbench\TestCase {
	/**
	 * @var
	 */
	public $cacheTags;

	protected function getPackageProviders($app)
	{
		return [
			CacheTagsProvider::class,
		];
	}

	public function setUp(): void {
		parent::setUp();
		$this->cacheTags = new CacheTags(cache());
		$this->cacheTags->addCacheMacros();
	}

	public function testIfCacheHasSupportsTagsMacro() {
		$this->assertTrue($this->cacheTags->getCache()->hasMacro('supportsTags'), 'supportsTags method has not been added to the cache');
	}

	public function testMainCacheClassIsWorking() {
		Cache::put(__METHOD__, 'yes', 1);
		$this->assertEquals(Cache::get(__METHOD__), 'yes', 'Cache::get failed');
	}

	public function testCacheKeyExistsInContexts() {
		$cacheKey = __METHOD__;
		$this->cacheTags->start($cacheKey, 1);
		$contexts = $this->cacheTags->getCacheContexts();
		$this->assertArrayHasKey($cacheKey, $contexts, "Key testCacheStart does not exist");
		$this->cacheTags->end();
	}

	public function testIfCachedDataCanBeRetrieved() {
		$testValue = __METHOD__ . ':value';
		$cacheKey  = __METHOD__;
		$this->cacheTags->start($cacheKey, 1);
		echo $testValue;
		$this->cacheTags->end();
		$this->assertEquals($testValue, $this->cacheTags->get($cacheKey), " data was not cached");
	}

	public function testIfCachedDataCanBeTagged() {
		$testValue = __METHOD__ . ':value';
		$cacheKey  = __METHOD__;
		$this->cacheTags->start($cacheKey, 1, 'testTag');
		echo $testValue;
		$this->cacheTags->end();
		$this->assertEquals($testValue, $this->cacheTags->get($cacheKey, 'testTag'), " data was not cached (with the correct tag)");
		$this->assertNull($this->cacheTags->get($cacheKey, 'wrongTag'), " data was not tagged correctly");
	}

	public function testIfCachedDataCanBeTaggedUsingCallback() {
		$testValue = __METHOD__ . ':value';
		$cacheKey  = __METHOD__;

		$cacheTags = ['tagA', 'tagB'];

		$this->cacheTags->start($cacheKey, 1, function () use ($cacheTags) {
			return $cacheTags;
		});
		echo $testValue;
		$this->cacheTags->end();
		$this->assertEquals($testValue, $this->cacheTags->get($cacheKey, $cacheTags), " data was not cached (with the correct tag)");
		$this->assertNull($this->cacheTags->get($cacheKey, 'wrongTag'), " data was not tagged correctly");
	}

	public function testIfCacheItemCanBeCleared() {
		$testValue = __METHOD__ . ':value';
		$cacheKey  = __METHOD__;
		$this->cacheTags->start($cacheKey, 1);
		echo $testValue;
		$this->cacheTags->end();
		$this->cacheTags->clear($cacheKey);
		$this->assertNotEquals($testValue, $this->cacheTags->get($cacheKey), " data was not cleared from cache");
	}

	public function testIfWeCanCacheForever() {
		$cacheKey = __METHOD__;

		$this->cacheTags->start($cacheKey, 'forever');
		echo 'forever young';
		$this->cacheTags->end();

		$this->assertStringStartsWith('forever young', $this->cacheTags->get($cacheKey), "Cache forever failed, can't find test string");
	}

	/**
	 * tests if a complex expression can be used to determine the cache key in blade context
	 */
	public function testExpressionInBladeDirective() {
		App::getFacadeApplication()->bind('CacheTags', function () {
			return $this->cacheTags;
		});

		$testValue = __METHOD__ . ':value';

		BladeCompiler::render(<<<'VIEW'
			@cachetagStart($formatString ?? 'fallbackString' . 1, 1){{
				$testValue
			}}@cachetagEnd()
		VIEW, compact('testValue'), true);

		$this->assertEquals($testValue, $this->cacheTags->get('fallbackString1'));
	}
}
