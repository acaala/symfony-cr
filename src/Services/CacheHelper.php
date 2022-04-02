<?php

namespace App\Services;

use JetBrains\PhpStorm\ArrayShape;
use Symfony\Contracts\Cache\CacheInterface;

class CacheHelper {
    private CacheInterface $cache;
    private string $baseURL;
    private ScrubUrlHelper $scrubHelper;
    private array $assetUrls;

    public function __construct(CacheInterface $cache, ScrubUrlHelper $scrubUrlHelper)
    {
        $this->cache = $cache;
        $this->baseURL = 'https://development.coinrivet.com/';
        $this->scrubHelper = $scrubUrlHelper;
        $this->assetUrls = [
            'scripts/runtime.js' => 'https://development.coinrivet.com/wp-includes/js/dist/vendor/regenerator-runtime.min.js',
            'scripts/main.js' => 'https://development.coinrivet.com/wp-content/themes/coinrivet/assets/scripts/main.js?v=1.0.80',
            'scripts/landing.js' => 'https://development.coinrivet.com/wp-content/themes/coinrivet/assets/scripts/landing.js?v=1.0.80',
            'scripts/prices.js' => 'https://development.coinrivet.com/wp-content/themes/coinrivet/assets/scripts/prices.js?v=1.0.80',
            'scripts/polyfill.js' => 'https://development.coinrivet.com/wp-includes/js/dist/vendor/wp-polyfill.min.js',
            'scripts/cr7.js' => 'https://development.coinrivet.com/wp-content/plugins/contact-form-7/includes/js/index.js',
            'scripts/emailSubscribers.js' => 'https://development.coinrivet.com/wp-content/plugins/email-subscribers/lite/public/js/email-subscribers-public.js',
            'scripts/recaptcha.js' => 'https://development.coinrivet.com/wp-content/plugins/contact-form-7/modules/recaptcha/index.js',
            'css/main.css' => 'https://development.coinrivet.com/wp-content/themes/coinrivet/assets/styles/main.css?v=1.0.80',
            'css/style.min.css' => 'https://development.coinrivet.com/wp-includes/css/dist/block-library/style.min.css',
            'css/cr7.css' => 'https://development.coinrivet.com/wp-content/plugins/contact-form-7/includes/css/styles.css',
            'css/emailSubscribers.css' => 'https://development.coinrivet.com/wp-content/plugins/email-subscribers/lite/public/css/email-subscribers-public.css',
            'icon/favicon.ico' => 'https://development.coinrivet.com/wp-content/themes/coinrivet/favicon/favicon.ico',
            'icon/apple-touch-icon.png' => 'https://development.coinrivet.com/wp-content/themes/coinrivet/favicon/apple-touch-icon.png',
            'icon/android-chrome-192x192.png' => 'https://development.coinrivet.com/wp-content/themes/coinrivet/favicon/android-chrome-192x192.png',
            'icon/android-chrome-512x512.png' => 'https://development.coinrivet.com/wp-content/themes/coinrivet/favicon/android-chrome-512x512.png',
            'icon/favicon-16x16.png' => 'https://development.coinrivet.com/wp-content/themes/coinrivet/favicon/favicon-16x16.png',
            'icon/favicon-32x32.png' => 'https://development.coinrivet.com/wp-content/themes/coinrivet/favicon/favicon-32x32.png',
//            'en-gb' => 'https://development.coinrivet.com/',
            'lang/fr' => 'https://development.coinrivet.com/fr/',
            'lang/de' => 'https://development.coinrivet.com/de/',
            'lang/it' => 'https://development.coinrivet.com/it/',
            'lang/ru' => 'https://development.coinrivet.com/ru/',
            'lang/pt' => 'https://development.coinrivet.com/pt/',
            'lang/es' => 'https://development.coinrivet.com/es/',
            'lang/ja' => 'https://development.coinrivet.com/ja/',
            'manifest/manifest.json' => 'https://development.coinrivet.com/wp-content/themes/coinrivet/favicon/manifest.json'
        ];
    }

    #[ArrayShape(['html' => "mixed", 'time' => ""])]
    public function cache(string $source, string $nestedUrl = null, ): array
    {
        $t = microtime(true);
        $html = $this->getHtml($nestedUrl, $source);
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

//    Caching info helpers
    #[ArrayShape(['size' => "false|int", 'time' => "float|int"])]
    public function cacheScriptsInfo(string $source): array
    {
        $t = microtime(true);
        $v = '?v=1.0.80';
        $url = $this->baseURL.'wp-content/themes/coinrivet/assets/scripts/'.$source.$v;
        $size = $this->cache->get('script_'.md5($url), function() use ($url) {
            return file_get_contents($url);
        });
        $size = mb_strlen($size, '8bit');
        $time = (microtime(true) - $t) * 1000;

        return ['size' => $size, 'time' => $time];
    }

    #[ArrayShape(['size' => "false|int", 'time' => "float|int"])]
    public function cacheInfo(string $source, string $nestedUrl = null): array
    {
        $t = microtime(true);
        if($source == 'home') $source = '/';
        $size = $this->getHtml($nestedUrl, $source);

        $size = mb_strlen($size, '8bit');
        $time = (microtime(true) - $t) * 1000;
        
        return ['size' => $size, 'time' => $time];
    }

    /**
     * @param string|null $nestedUrl
     * @param string $source
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getHtml(?string $nestedUrl, string $source): mixed
    {
        if ($nestedUrl != null) {
            $url = $this->baseURL . $source . '/' . $nestedUrl;
        } else {
            $url = $this->baseURL . $source;
        }
        return $this->cache->get('page_' . md5($url), function () use ($url) {
            $htmlString = file_get_contents($url);
            return $this->scrubHelper->scrubUrls($htmlString, $this->assetUrls);
        });
    }
}
