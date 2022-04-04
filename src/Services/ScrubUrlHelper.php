<?php

namespace App\Services;

class ScrubUrlHelper {

    public function scrubUrls(string $stringToScrub, $urls): string {
        $keys = [];
        foreach ($urls as $key => $value) {
            $keys[] = $_ENV['SITE_URL'] . 'assets/' . $key;
        }
        $assetScrubbedString = str_replace(array_values($urls), $keys, $stringToScrub);
        return str_replace('https://development.coinrivet.com/', $_ENV['SITE_URL'], $assetScrubbedString);
    }
}