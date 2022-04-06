<?php

namespace App\Services;

class ScrubUrlHelper {

    public function scrubUrls(string $stringToScrub, array $urls): string {
        $keys = [];
        foreach ($urls as $key => $value) {
            $keys[] = $_ENV['SITE_URL'] . 'assets/' . $key;
        }
        $assetScrubbedString = str_replace(array_values($urls), $keys, $stringToScrub);
        return str_replace($_ENV['TARGET_URL'], $_ENV['SITE_URL'], $assetScrubbedString);
    }
}