<?php

namespace App\Services;

use JetBrains\PhpStorm\ArrayShape;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CacheHelper {
    private CacheInterface $cache;
    private string $baseURL;
    private ScrubUrlHelper $scrubHelper;
    private array $assetUrls;
    private string $v;
    private LocationHelper $locationHelper;
    private array $restricted;
    private array $restrictedCountries;

    public function __construct(CacheInterface $cache, ScrubUrlHelper $scrubUrlHelper, LocationHelper $locationHelper, array $assetUrls)
    {
        $this->locationHelper = $locationHelper;
        $this->cache = $cache;
        $this->baseURL = $_ENV['TARGET_URL'];
        $this->v = $_ENV['ASSET_V'];
        $this->scrubHelper = $scrubUrlHelper;
        $this->assetUrls = $assetUrls;
        $this->restricted = ['/', 'faqs'];
        $this->restrictedCountries = ['GB', 'DK', 'LT', 'US'];
    }

    #[ArrayShape(['html' => "mixed", 'time' => ""])]
    public function cache(string $source, string $nestedUrl = null, string $article = null): array
    {
        $t = microtime(true);
        $html = $this->getHtml($nestedUrl, $source, $article);
        $time = microtime(true) - $t;
        return ['html' => $html, 'time' => $time];
    }

    public function cacheClear(string $source, ?string $nestedUrl = null): string
    {
        if($nestedUrl != null) {
            $url = $this->baseURL.$source . '/' . $nestedUrl;
        } else {
            $url = $this->baseURL.$source;
        }
        return $this->cache->delete('page_'.md5($url));
    }

    public function cacheAsset(string $source): string
    {
        return $this->cache->get('asset_'.md5($source), function() use ($source) {
            return file_get_contents($this->assetUrls[$source]);
        });
    }

    public function cacheAllAssets(): void
    {
        foreach ($this->assetUrls as $key => $value) {
             $this->cache->get('asset_'.md5($key), function() use ($value) {
                return file_get_contents($value);
            });
        };
    }

    public function getCachedAssets(): array
    {
        return $this->assetUrls;
    }

//    Caching info helpers
    #[ArrayShape(['size' => "false|int", 'time' => "float|int"])]
    public function cacheScriptsInfo(string $source): array
    {
        $t = microtime(true);
        $url = $this->baseURL.'wp-content/themes/coinrivet/assets/scripts/'.$source.$this->v;
        $size = $this->cache->get('script_'.md5($url), function() use ($url) {
            return file_get_contents($url);
        });
        $size = mb_strlen($size, '8bit');
        $time = (microtime(true) - $t) * 1000;

        return ['size' => $size, 'time' => $time];
    }

    #[ArrayShape(['size' => "false|int", 'time' => "float|int"])]
    public function cacheInfo(string $source, ?string $nestedUrl = null, ?string $article = null): array
    {
        $t = microtime(true);
        if($source == 'home') $source = '/';
        $size = $this->getHtml($nestedUrl, $source, $article);

        $size = mb_strlen($size, '8bit');
        $time = (microtime(true) - $t) * 1000;
        
        return ['size' => $size, 'time' => $time];
    }

    public function deleteSlugCache(string $source): string
    {
        $this->cache->delete('page_'.md5($this->baseURL.$source));
        foreach ($this->restrictedCountries as $cc) {
            $this->cache->delete('page_'.md5($this->baseURL.$source).md5($cc));
        }
        return 'deleted';
    }

    public function getHtml(?string $nestedSlug, string $source, ?string $article): string
    {
        if ($nestedSlug && $article) {
            $url = $this->baseURL . $source . '/' . $nestedSlug . '/' . $article;
        } elseif ($nestedSlug) {
            $url = $this->baseURL . $source . '/' . $nestedSlug;
        } else {
            $url = $this->baseURL . $source;
        }

        if(in_array($source, $this->restricted) || in_array($this->locationHelper->getCountryCode(), $this->restrictedCountries)) {
            $cacheKey = 'page_' . md5($url).md5($this->locationHelper->getCountryCode());
        } else {
            $cacheKey = 'page_' . md5($url);
        };

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($url) {
//            expire after 1 day.
            $item->expiresAfter(86400);
            $opts = [
                "http" => [
                    "method" => "POST",
                    'header' =>
                        "Content-Type: application/x-www-form-urlencoded\r\n",
                    'content' => $this->locationHelper->getCountryCode(),

                ]
            ];
            $context = stream_context_create($opts);
            $htmlString = file_get_contents($url, false, $context);
            return $this->scrubHelper->scrubUrls($htmlString, $this->assetUrls);
        });
    }
}
