# CacheTags - Laravel blade partial caching

This package is intended to allow you to cache portions of a web page by marking them using blade directives.

You can specify how long the partial will be cached for as well as naming it (to allow you to invalidate it if needed).

## Installation
Install it as a composer package (not on packagist for now)

Add `Perturbatio\CacheTags\CacheTagsProvider::class` to your config/app.php providers array
Add `"CacheTags" => Perturbatio\CacheTags\CacheTags::class to your aliases

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

## Status
There is work to be done on this for now, however the main caching mechanism is now functional

