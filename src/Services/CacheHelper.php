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

    public function __construct(CacheInterface $cache, ScrubUrlHelper $scrubUrlHelper, LocationHelper $locationHelper)
    {
        $this->locationHelper = $locationHelper;
        $this->cache = $cache;
        $this->baseURL = $_ENV['TARGET_URL'];
        $this->v = $_ENV['ASSET_V'];
        $this->scrubHelper = $scrubUrlHelper;
        $this->assetUrls = [
            'scripts/runtime.js' => $this->baseURL.'wp-includes/js/dist/vendor/regenerator-runtime.min.js',
            'scripts/main.js' => $this->baseURL.'wp-content/themes/coinrivet/assets/scripts/main.js'.$this->v,
            'scripts/landing.js' => $this->baseURL.'wp-content/themes/coinrivet/assets/scripts/landing.js'.$this->v,
            'scripts/prices.js' => $this->baseURL.'wp-content/themes/coinrivet/assets/scripts/prices.js'.$this->v,
            'scripts/guides.js' => $this->baseURL.'wp-content/themes/coinrivet/assets/scripts/guides.js'.$this->v,
            'scripts/dictionary.js' => $this->baseURL.'wp-content/themes/coinrivet/assets/scripts/dictionary.js'.$this->v,
            'scripts/nft-calendar.js' => $this->baseURL.'wp-content/themes/coinrivet/assets/scripts/nft-calendar.js'.$this->v,
            'scripts/support.js' => $this->baseURL.'wp-content/themes/coinrivet/assets/scripts/support.js'.$this->v,
            'scripts/events.js' => $this->baseURL.'wp-content/themes/coinrivet/assets/scripts/events.js'.$this->v,
            'scripts/polyfill.js' => $this->baseURL.'wp-includes/js/dist/vendor/wp-polyfill.min.js',
            'scripts/cr7.js' => $this->baseURL.'wp-content/plugins/contact-form-7/includes/js/index.js',
            'scripts/emailSubscribers.js' => $this->baseURL.'wp-content/plugins/email-subscribers/lite/public/js/email-subscribers-public.js',
            'scripts/recaptcha.js' => $this->baseURL.'wp-content/plugins/contact-form-7/modules/recaptcha/index.js',
            'css/main.css' => $this->baseURL.'wp-content/themes/coinrivet/assets/styles/main.css'.$this->v,
            'css/style.min.css' => $this->baseURL.'wp-includes/css/dist/block-library/style.min.css',
            'css/cr7.css' => $this->baseURL.'wp-content/plugins/contact-form-7/includes/css/styles.css',
            'css/emailSubscribers.css' => $this->baseURL.'wp-content/plugins/email-subscribers/lite/public/css/email-subscribers-public.css',
            'icon/favicon.ico' => $this->baseURL.'wp-content/themes/coinrivet/favicon/favicon.ico',
            'icon/apple-touch-icon.png' => $this->baseURL.'wp-content/themes/coinrivet/favicon/apple-touch-icon.png',
            'icon/android-chrome-192x192.png' => $this->baseURL.'wp-content/themes/coinrivet/favicon/android-chrome-192x192.png',
            'icon/android-chrome-512x512.png' => $this->baseURL.'wp-content/themes/coinrivet/favicon/android-chrome-512x512.png',
            'icon/favicon-16x16.png' => $this->baseURL.'wp-content/themes/coinrivet/favicon/favicon-16x16.png',
            'icon/favicon-32x32.png' => $this->baseURL.'wp-content/themes/coinrivet/favicon/favicon-32x32.png',
//            'icon/landing-bitcoin-1.svg' => $this->baseURL.'wp-content/themes/coinrivet/assets/images/landing-bitcoin.svg',
            'manifest/manifest.json' => $this->baseURL.'wp-content/themes/coinrivet/favicon/manifest.json'
        ];
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

    public function recacheSlug(string $source): void
    {
        $this->cache->delete('page_'.md5($source));
        foreach ($this->restrictedCountries as $cc) {
            $this->cache->delete('page_'.md5($source).md5($cc));
        }
        $this->getHtml(null, $source, null);
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
