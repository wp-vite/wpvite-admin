<?php

namespace App\Services\Cloudflare;

use App\Services\HttpService;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class CloudflareDnsManager
{
    protected $apiBaseUrl = 'https://api.cloudflare.com/client/v4';
    protected $apiToken;
    protected $httpService;

    public function __construct(HttpService $httpService)
    {
        $this->apiToken = Config::get('services.cloudflare.api_token');
        $this->httpService  = $httpService;
    }

    /**
     * Add an A record to Cloudflare DNS.
     *
     * @param string $domain
     * @param string $ipAddress
     * @return array
     * @throws Exception
     */
    public function addARecord(string $domain, string $ipAddress): array
    {
        // Step 1: Get the Zone ID for the domain
        $zoneId = $this->getZoneId($domain);

        if (!$zoneId) {
            Log::error("Zone ID not found for domain: {$domain}");
            throw new Exception("Zone ID not found for domain: {$domain}");
        }

        // Step 2: Create the DNS record
        $params = [
            'type' => 'A',
            'name' => $domain,
            'content' => $ipAddress,
            // 'ttl' => 3600,
            'proxied' => true,
        ];

        $path    = "/zones/{$zoneId}/dns_records";
        return $this->makeApiRequest('POST', $path, $params);
    }

    /**
     * Get the Zone ID for a domain.
     *
     * @param string $domain
     * @return string|null
     * @throws Exception
     */
    public function getZoneId(string $domain):?string
    {
        $zoneId = config('services.cloudflare.zone_id');
        if($zoneId) {
            return $zoneId;
        }

        $response = $this->makeApiRequest('GET', 'zones', [
            'name' => $this->extractBaseDomain($domain),
        ]);

        $zones = $response['data']['result'] ?? [];
        return $zones[0]['id'] ?? null;
    }

    /**
     * Extract the base domain from a full domain name.
     *
     * @param string $domain
     * @return string
     */
    protected function extractBaseDomain(string $domain): string
    {
        $parts = explode('.', $domain);
        $count = count($parts);

        return $count > 2 ? "{$parts[$count - 2]}.{$parts[$count - 1]}" : $domain;
    }

    /**
     * Make an API request to CloudFlare DNS.
     *
     * @param string $command The API endpoint URL.
     * @param array $params Parameters to send with the request.
     * @return array
     * @throws Exception
     */
    protected function makeApiRequest(string $method, string $path, array $params): array
    {
        $url    = $this->apiBaseUrl.'/'.$path;

        $response = $this->httpService->sendRequest($method, $url, ['body' => $params], [
            'Authorization' => "Bearer {$this->apiToken}",
            'Content-Type' => 'application/json',
        ]);

        if (!$response['status']) {
            Log::error("CloudFlare DNS API error: {$response['message']}");
        }

        return $response;
    }
}
