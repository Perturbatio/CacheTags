<?php
/**
 *
 */

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Perturbatio\CacheTags\CacheTags;

if ( ! function_exists('cachetagStart')) {
    /**
     * @param        $key
     * @param        $minutes
     * @param string $tag
     */
    function cachetagStart( $key, $minutes = null, $tag = '' ) {
        /**
         * @var $cacheTags CacheTags
         */
        $cacheTags = app('CacheTags');
        $cacheTags->start($key, $minutes, $tag);
    }
}

if ( ! function_exists('cachetagEnd')) {
    /**
     * @return mixed
     */
    function cachetagEnd() {
        /**
         * @var $cacheTags CacheTags
         */
        $cacheTags = app('CacheTags');

        return $cacheTags->end();
    }
}

if ( ! function_exists('cachetagHas')) {
    /**
     * @param $key
     *
     * @return mixed
     */
    function cachetagHas( $key ) {
        /**
         * @var $cacheTags CacheTags
         */
        $cacheTags = app('CacheTags');

        return $cacheTags->has($key);
    }
}
if ( ! function_exists('cachetagClear')) {
    /**
     * @param $key
     *
     * @return mixed
     */
    function cachetagClear( $key, $tag = '' ) {
        /**
         * @var $cacheTags CacheTags
         */
        $cacheTags = app('CacheTags');

        return $cacheTags->clear($key, $tag);
    }
}

if ( ! function_exists('cachetagGet')) {
    /**
     * @param $key
     *
     * @return mixed
     */
    function cachetagGet( $key, $tag = '' ) {
        /**
         * @var $cacheTags CacheTags
         */
        $cacheTags = app('CacheTags');

        return $cacheTags->get($key, $tag);
    }
}
