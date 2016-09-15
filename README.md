# CacheTags - Laravel blade partial caching

https://api.travis-ci.org/Perturbatio/CacheTags.svg?branch=master

This package is intended to allow you to cache portions of a web page by marking them using blade directives.

You can specify how long the partial will be cached for as well as naming it (to allow you to invalidate it if needed).

## Installation
Install it as a composer package (not on packagist for now)

Add `Perturbatio\CacheTags\CacheTagsProvider::class` to your config/app.php providers array

Add `'CacheTags' => Perturbatio\CacheTags\CacheTags::class` to your aliases

usage:
```Blade
@cachetagStart('menu', 15) <!-- menu cached for 15 minutes -->
<?=superCoolMenuThatTakesTooLongToGenerate();?>
@cachetagEnd()
```

usage outside of blade:
```PHP
if ( cachetagHas('menu') ){
  echo cachetagGet('menu');
} else {
  cachetagStart('menu', 15);
  echo superCoolMenuThatTakesTooLongToGenerate();
  echo cachetagEnd();
}
```


