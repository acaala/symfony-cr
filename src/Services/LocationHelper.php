<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;

class LocationHelper {
    private Request $request;

    public function __construct()
    {
        $this->request = Request::createFromGlobals();;
    }

    public function getCountryCode(): string
    {
        $ip = $_ENV['APP_ENV'] == 'prod' ? $this->request->getClientIp() : '89.238.150.174';
        $json = file_get_contents('https://www.iplocate.io/api/lookup/' . $ip);
        $ipInfo = json_decode($json);
        return $ipInfo->country_code;
    }
}