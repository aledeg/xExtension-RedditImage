<?php

declare(strict_types=1);

namespace RedditImage\Client;

use RedditImage\Exception\ClientException;

class Client {
    private string $userAgent;

    public function __construct(string $userAgent) {
        $this->userAgent = $userAgent;
    }

    public function jsonGet(string $url, array $headers = []): array {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        $jsonString = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            throw new ClientException(curl_error($ch));
        }
        curl_close($ch);

        return json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
    }

    public function isAccessible(string $url, array $headers = []): bool {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_NOBODY => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return 200 === $httpCode;
    }
}
