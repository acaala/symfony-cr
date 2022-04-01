<?php

namespace App\Services;

use Symfony\Contracts\Cache\CacheInterface;

class CacheHelper {
    private CacheInterface $cache;
    private string $baseURL;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
        $this->baseURL = 'https://development.coinrivet.com/';
    }

    public function cache(string $source, string $nestedUrl = null): array
    {   
        $t = microtime(true);
        if($nestedUrl != null) {
            $url = $this->baseURL.$source . '/' . $nestedUrl;
        } else {
            $url = $this->baseURL.$source;
        }
        $html = $this->cache->get('page_'.md5($url), function() use ($url) {
            $contents = file_get_contents($url);
            $contents = str_replace('<a href="https://development.coinrivet.com/', '<a href="https://localhost:8000/', $contents);
            $contents = str_replace('src="https://development.coinrivet.com/wp-content/themes/coinrivet/assets/scripts/main.js?v=1.0.80"', 'src="https://localhost:8000/scripts/main.js"', $contents);
            return $contents;
        });
        $time = microtime(true) - $t;
        return ['html' => $html, 'time' => $time];
    }

    public function cacheClear(string $source, string $nestedUrl = null): string
    {
        if($nestedUrl != null) {
            $url = $this->baseURL.$source . '/' . $nestedUrl;
        } else {
            $url = $this->baseURL.$source;
        }
        return $this->cache->delete('page_'.md5($url));
    }

    public function cacheScripts(string $source): string
    {
        $v = '?v=1.0.80';
        $url = $this->baseURL.'wp-content/themes/coinrivet/assets/scripts/'.$source.$v;
        $script = $this->cache->get('script_'.md5($url), function() use ($url) {
            return file_get_contents($url);
        });
        return $script;
    }

    public function cacheInfo(string $source, string $nestedUrl = null): array
    {
        $t = microtime(true);
        if($source == 'home') $source = '/';
        if($nestedUrl != null) {
            $url = $this->baseURL.$source . '/' . $nestedUrl;
        } else {
            $url = $this->baseURL.$source;
        }
        $size = $this->cache->get('page_'.md5($url), function() use ($url) {
            return file_get_contents($url);
        });

        $size = mb_strlen($size, '8bit');
        $time = (microtime(true) - $t) * 1000;
        
        return ['size' => $size, 'time' => $time];
    }
}