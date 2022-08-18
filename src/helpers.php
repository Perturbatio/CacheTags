<?php
/**
 *
 */

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Perturbatio\CacheTags\CacheTags;

if ( !function_exists('cachetagStart')) {
	/**
	 * @param                      $key
	 * @param                      $time
	 * @param array|string|Closure $tag
	 */
	function cachetagStart( $key, $time = null, $tag = '' ) {
		app('CacheTags')->start($key, $time, $tag);
	}
}

if ( !function_exists('cachetagEnd')) {
	/**
	 * @return mixed
	 */
	function cachetagEnd() {
		return app('CacheTags')->end();
	}
}

if ( !function_exists('cachetagHas')) {
	/**
	 * @param                      $key
	 * @param array|string|Closure $tag
	 *
	 * @return mixed
	 */
	function cachetagHas( $key, $tag = '' ) {
		return app('CacheTags')->has($key, $tag);
	}
}
if ( !function_exists('cachetagClear')) {
	/**
	 * @param                      $key
	 * @param array|string|Closure $tag
	 *
	 * @return mixed
	 */
	function cachetagClear( $key, $tag = '' ) {
		return app('CacheTags')->clear($key, $tag);
	}
}

if ( !function_exists('cachetagGet')) {
	/**
	 * @param                      $key
	 * @param array|string|Closure $tag
	 *
	 * @return mixed
	 */
	function cachetagGet( $key, $tag = '' ) {
		return app('CacheTags')->get($key, $tag);
	}
}
