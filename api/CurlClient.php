<?php
class CurlClient {
    private $baseUrl;
    public function __construct(string $baseUrl) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function get(string $path) {
        $url = $this->baseUrl . '/' . ltrim($path, '/');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        // optional: set user-agent
        curl_setopt($ch, CURLOPT_USERAGENT, 'My-Fipe-Client/1.0');
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($resp === false) {
            throw new Exception("Curl error: $err");
        }
        if ($code < 200 || $code >= 300) {
            throw new Exception("Remote returned HTTP $code for $url");
        }
        return json_decode($resp, true);
    }
}
