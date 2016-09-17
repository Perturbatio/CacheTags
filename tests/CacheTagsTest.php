<?php
namespace Perturbatio\CacheTags\Tests;

require_once dirname(__FILE__) . '/../vendor/autoload.php';

use Illuminate\Support\Facades\Cache;
use Perturbatio\CacheTags\CacheTags;

class CacheTagsTest extends \Orchestra\Testbench\TestCase {
    /**
     * @var
     */
    public $cacheTags;

    public function setUp() {
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

    public function testIfCacheItemCanBeCleared() {
        $testValue = __METHOD__ . ':value';
        $cacheKey  = __METHOD__;
        $this->cacheTags->start($cacheKey, 1);
        echo $testValue;
        $this->cacheTags->end();
        $this->cacheTags->clear($cacheKey);
        $this->assertNotEquals($testValue, $this->cacheTags->get($cacheKey), " data was not cleared from cache");
    }
}
