<?php

namespace App\Services;

use Symfony\Contracts\Cache\CacheInterface;

class CacheHelper {
    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function cache(string $source, string $nestedUrl = null): array 
    {   
        $t = microtime(true);
        if($nestedUrl != null) {
            $url = 'https://development.coinrivet.com/'.$source . '/' . $nestedUrl;
        } else {
            $url = 'https://development.coinrivet.com/'.$source;
        }
        $html = $this->cache->get('page_'.md5($url), function() use ($url) {
            return file_get_contents($url);
        });
        $time = microtime(true) - $t;
        return ['html' => $html, 'time' => $time];
    }

    public function cacheClear(string $source, string $nestedUrl = null): string
    {
        if($nestedUrl != null) {
            $url = 'https://development.coinrivet.com/'.$source . '/' . $nestedUrl;
        } else {
            $url = 'https://development.coinrivet.com/'.$source;
        }
        return $this->cache->delete('page_'.md5($url));
    }

    public function cacheInfo(string $source, string $nestedUrl = null): array
    {
        $t = microtime(true);
        if($source == 'home') $source = '/';
        if($nestedUrl != null) {
            $url = 'https://development.coinrivet.com/'.$source . '/' . $nestedUrl;
        } else {
            $url = 'https://development.coinrivet.com/'.$source;
        }
        $size = $this->cache->get('page_'.md5($url), function() use ($url) {
            return file_get_contents($url);
        });

        $size = mb_strlen($size, '8bit');
        $time = (microtime(true) - $t) * 1000;
        
        return ['size' => $size, 'time' => $time];
    }
}