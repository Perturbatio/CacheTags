<?php
/**
 * Created by Kris with PhpStorm.
 * Date: 13/09/2016
 * Time: 21:21
 */

namespace Perturbatio\CacheTags;


use Illuminate\Cache\CacheManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class CacheTags {

	protected static $cacheContexts = [];
	/**
	 * @var CacheManager
	 */
	protected $cache;

	/**
	 * CacheTags constructor.
	 */
	public function __construct( CacheManager $cache ) {
		$this->cache = $cache;
		$this->addCacheMacros();
	}

	/**
	 * Get a copy of the internal $cacheContexts array
	 *
	 * @return array
	 */
	public static function getCacheContexts() {
		return self::$cacheContexts;
	}

	/**
	 * Start caching the output that follows this call
	 *
	 * @param        $key
	 * @param null   $time
	 * @param string $tags
	 */
	public function start( $key, $time = null, $tags = '' ) {
		if ($time !== 'forever') {
			$time = $time || config('cachetags.timeout', 1);
		}
		$tags = static::splitTags($tags | config('cachetags.default_tag', static::class));

		if ($time === null) {
			$time = 15;
		}
		if (empty($key)) {
			throw new InvalidArgumentException("Cache key cannot be empty");
		}
		if (isset(static::$cacheContexts[ $key ])) {
			throw new InvalidArgumentException("Cache key '{$key}' is already in use");
		}
		static::$cacheContexts[ $key ] = compact('key', 'time', 'tags');

		ob_start();
	}

	/**
	 * Determine if the cache has the specified key
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public function has( $key ) {
		return $this->cache->has($key);
	}

	/**
	 * Close the output buffer and store the result in the latest cache key
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function end() {
		//
		$cacheContexts = new Collection(static::$cacheContexts);
		$lastCacheItem = $cacheContexts->last(function ( $value, $key ) {
			return $value !== false;
		});

		if ( !empty($lastCacheItem['key'])) {
			$key                           = $lastCacheItem['key'];
			static::$cacheContexts[ $key ] = false;

			$result = ob_get_clean();

			if ($this->cache->supportsTags()) {
				$cache = $this->cache->tags($lastCacheItem['tag']);
			} else {
				$cache = $this->cache;
			}

			if ($lastCacheItem['time'] !== 'forever') {
				$cache->put($key, $result, $lastCacheItem['time']);
			} else {
				$cache->forever($key, $result);
			}

			return $result;
		} else {
			throw new \Exception('cachetagEnd encountered without opening cachetagStart');
		}
	}

	/**
	 *
	 */
	public function addCacheMacros() {

		$macro = function () {
			return method_exists(app('cache'), 'tags');
		};

		$this->cache->macro('supportsTags', $macro);

		if (class_exists('Cache')) {
			Cache::macro('supportsTags', $macro);
		}
	}

	/**
	 * Set up the @cachetagStart and @cachetagEnd directives
	 */
	static public function registerBladeDirectives() {

		Blade::directive('cachetagStart', function ( $params ) {
			$params = array_map('trim', explode(',', $params), [' "\'']);
			if (count($params) < 1) {
				throw new InvalidArgumentException(("cachetagStart requires the cache key as a parameter"));
			}
			$key  = $params[0];
			$time = isset($params[1]) ? $params[1] : config('cachetags.timeout', 15);
			$tag  = isset($params[2]) ? $params[2] : 'cachetags';

			return "<?php 
			if ( cachetagHas(\"{$key}\") ){
				echo cachetagGet(\"{$key}\");
			} else {
				cachetagStart(\"{$key}\", $time, \"{$tag}\");
			?>";
		});

		Blade::directive('cachetagEnd', function () {
			return "<?php 
				echo cachetagEnd(); 
			} ?>";
		});
		Blade::directive('cachetagClear', function () {
			return "<?php 
				echo cachetagClear(); 
			} ?>";
		});
	}

	/**
	 * Retrieve a cached item
	 *
	 * @param        $key
	 * @param string $tags
	 *
	 * @return mixed
	 */
	public function get( $key, $tags = '' ) {
		if ($this->cache->supportsTags()) {
			$tags    = static::splitTags($tags | config('cachetags.default_tag', static::class));
			$content = $this->cache->tags($tags)->get($key);
		} else {
			$content = $this->cache->get($key);
		}

		return $content;
	}

	/**
	 * Clear a cached item
	 *
	 * @param        $key
	 * @param string $tags
	 */
	public function clear( $key, $tags = '' ) {
		if ($this->cache->supportsTags()) {
			$tags = static::splitTags($tags | config('cachetags.default_tag', static::class));
			$this->cache->tags($tags)->flush($key);
		} else {
			$this->cache->forget($key);
		}
	}

	/**
	 * get a tag array from the supplied string
	 *
	 * @param $tags
	 *
	 * @return array
	 */
	static public function splitTags( $tags ) {
		$result = $tags;
		if ( !is_array($tags)) {
			$result = explode(',', (string) $tags);
		}

		return $result;
	}

	/**
	 * @return CacheManager
	 */
	public function getCache() {
		return $this->cache;
	}
}
