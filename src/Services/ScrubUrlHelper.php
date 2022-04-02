<?php

namespace App\Services;

class ScrubUrlHelper {

    public function scrubUrls(string $stringToScrub, $urls): string {
        $keys = [];
        foreach ($urls as $key => $value) {
            $keys[] = getenv('SITE_URL') . 'assets/' . $key;
        }
        return str_replace(array_values($urls), $keys, $stringToScrub);
    }
}