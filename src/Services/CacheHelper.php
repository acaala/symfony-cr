<?php

namespace App\Services;

use Symfony\Contracts\Cache\CacheInterface;

class CacheHelper {

    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }


    public function cache(string $source, string $nestedUrl = null): string 
    {   
        if($nestedUrl != null) {
            $url = 'https://development.coinrivet.com/'.$source . '/' . $nestedUrl;
        } else {
            $url = 'https://development.coinrivet.com/'.$source;
        }
        return $this->cache->get('page_'.md5($source), function() use ($url) {
            return file_get_contents($url);
        });
    }


}