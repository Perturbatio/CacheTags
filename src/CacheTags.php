<?php
/**
 * Created by Kris with PhpStorm.
 * Date: 13/09/2016
 * Time: 21:21
 */

namespace Perturbatio\CacheTags;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class CacheTags {
	protected static $cacheContexts = [];

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
		return Cache::has( $key );
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
			if ( Cache::supportsTags() ){
				Cache::tags( $lastCacheItem[ 'tag' ] )->put( $key, $result, $lastCacheItem[ 'minutes' ] );
			} else {
				Cache::put( $key, $result, $lastCacheItem[ 'minutes' ] );
			}


			return $result;
		} else {
			throw new \Exception( 'cachetagEnd encountered without opening cachetagStart' );
		}
	}

	/**
	 *
	 */
	static public function addCacheMacros() {

		Cache::macro( 'supportsTags', function () {
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


	public function get( $key, $tag = '' ) {
		if ( Cache::supportsTags() ){
			$tag     = $tag | config( 'cachetags.default_tag', static::class );
			$content = Cache::tags( $tag )->get( $key );
		} else {
			$content = Cache::get( $key );
		}
		return $content;
	}

	public function clear( $key, $tag = ''  ) {
		if ( Cache::supportsTags() ){
			$tag     = $tag | config( 'cachetags.default_tag', static::class );
			Cache::tags( $tag )->flush( $key );
		} else {
			Cache::forget( $key );
		}
	}
}