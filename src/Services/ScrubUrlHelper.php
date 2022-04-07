<?php

namespace App\Services;

class ScrubUrlHelper {

    public function scrubUrls(string $stringToScrub, array $urls): string {
        $keys = [];
        foreach ($urls as $key => $value) {
            $keys[] = $_ENV['SITE_URL'] . 'assets/' . $key;
        }
        return str_replace(array_values($urls), $keys, $stringToScrub);
    }
}