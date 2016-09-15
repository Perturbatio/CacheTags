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
	 * @var Cache
	 */
	protected $cache;

	/**
	 * CacheTags constructor.
	 */
	public function __construct(CacheManager $cache) {
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
	 * @param        $key
	 * @param null   $minutes
	 * @param string $tags
	 */
	public function start( $key, $minutes = null, $tag = '' ) {
		$minutes = $minutes | config( 'cachetags.timeout', 1 );
		$tag     = $tag | config( 'cachetags.default_tag', static::class );

		if ( $minutes === null ){
			$minutes = 15;
		}
		if ( empty( $key ) ){
			throw new InvalidArgumentException( "Cache key cannot be empty" );
		}
		if ( isset( static::$cacheContexts[ $key ] ) ){
			throw new InvalidArgumentException( "Cache key '{$key}' is already in use" );
		}
		static::$cacheContexts[ $key ] = compact( 'key', 'minutes', 'tag' );

		ob_start();
	}

	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	public function has( $key ) {
		return $this->cache->has( $key );
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	public function end() {
		//
		$cacheContexts = new Collection( static::$cacheContexts );
		$lastCacheItem = $cacheContexts->last( function ( $value, $key ) {
			return $value !== false;
		} );

		if ( !empty( $lastCacheItem[ 'key' ] ) ){
			$key                           = $lastCacheItem[ 'key' ];
			static::$cacheContexts[ $key ] = false;

			$result = ob_get_clean();
			if ( $this->cache->supportsTags() ){
				$this->cache->tags( $lastCacheItem[ 'tag' ] )->put( $key, $result, $lastCacheItem[ 'minutes' ] );
			} else {
				$this->cache->put( $key, $result, $lastCacheItem[ 'minutes' ] );
			}


			return $result;
		} else {
			throw new \Exception( 'cachetagEnd encountered without opening cachetagStart' );
		}
	}

	/**
	 *
	 */
	public function addCacheMacros() {
		$this->cache->macro( 'supportsTags', function () {
			return method_exists( app( 'cache' ), 'tags' );
		} );
	}

	/**
	 * Set up the @cachetagStart and @cachetagEnd directives
	 */
	static public function registerBladeDirectives() {

		Blade::directive( 'cachetagStart', function ( $params ) {
			$params = array_map( 'trim', explode( ',', $params ), [ ' "\'' ] );
			if ( count( $params ) < 1 ){
				throw new InvalidArgumentException( ( "cachetagStart requires the cache key as a parameter" ) );
			}
			$key     = $params[ 0 ];
			$minutes = isset( $params[ 1 ] ) ? $params[ 1 ] : null;
			$tag     = isset( $params[ 2 ] ) ? $params[ 2 ] : 'cachetags';

			return "<?php 
			if ( cachetagHas(\"{$key}\") ){
				echo cachetagGet(\"{$key}\");
			} else {
				cachetagStart(\"{$key}\", $minutes, \"{$tag}\");
			?>";
		} );

		Blade::directive( 'cachetagEnd', function () {
			return "<?php 
				echo cachetagEnd(); 
			} ?>";
		} );
		Blade::directive( 'cachetagClear', function () {
			return "<?php 
				echo cachetagClear(); 
			} ?>";
		} );
	}

	/**
	 *
	 *
	 * @param        $key
	 * @param string $tag
	 *
	 * @return mixed
	 */
	public function get( $key, $tag = '' ) {
		if ( $this->cache->supportsTags() ){
			$tag     = $tag | config( 'cachetags.default_tag', static::class );
			$content = $this->cache->tags( $tag )->get( $key );
		} else {
			$content = $this->cache->get( $key );
		}
		return $content;
	}

	/**
	 * @param        $key
	 * @param string $tag
	 */
	public function clear( $key, $tag = ''  ) {
		if ( $this->cache->supportsTags() ){
			$tag     = $tag | config( 'cachetags.default_tag', static::class );
			$this->cache->tags( $tag )->flush( $key );
		} else {
			$this->cache->forget( $key );
		}
	}

	/**
	 * @return Cache
	 */
	public function getCache() {
		return $this->cache;
	}
}