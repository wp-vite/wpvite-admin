<?php

namespace App\Services\Virtualmin;

use Exception;
use Illuminate\Support\Facades\Http;

class SiteManagerService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('virtualmin.api_url'); // Virtualmin API URL
        $this->apiKey = config('virtualmin.api_key'); // Virtualmin API Key
    }

    /**
     * Create a new domain on Virtualmin.
     *
     * @param string $domain The domain name to create.
     * @param array $options Additional options for the domain.
     * @return array
     * @throws Exception
     */
    public function createDomain(string $domain, array $options = []): array
    {
        $url = "{$this->apiUrl}/create-domain";

        // Default parameters for domain creation
        $params = array_merge([
            'domain' => $domain,
            'plan' => $options['plan'] ?? 'default', // Hosting plan
            'admin_user' => $options['admin_user'] ?? $this->generateAdminUsername($domain),
            'admin_pass' => $options['admin_pass'] ?? $this->generateRandomPassword(),
        ], $options);

        return $this->makeApiRequest($url, $params);
    }

    /**
     * Delete an existing domain on Virtualmin.
     *
     * @param string $domain The domain name to delete.
     * @return array
     * @throws Exception
     */
    public function deleteDomain(string $domain): array
    {
        $url = "{$this->apiUrl}/delete-domain";
        $params = ['domain' => $domain];

        return $this->makeApiRequest($url, $params);
    }

    /**
     * Get details about an existing domain.
     *
     * @param string $domain The domain name to query.
     * @return array
     * @throws Exception
     */
    public function getDomainDetails(string $domain): array
    {
        $url = "{$this->apiUrl}/get-domain";
        $params = ['domain' => $domain];

        return $this->makeApiRequest($url, $params);
    }

    /**
     * Suspend a domain.
     *
     * @param string $domain The domain name to suspend.
     * @return array
     * @throws Exception
     */
    public function suspendDomain(string $domain): array
    {
        $url = "{$this->apiUrl}/suspend-domain";
        $params = ['domain' => $domain];

        return $this->makeApiRequest($url, $params);
    }

    /**
     * Unsuspend a domain.
     *
     * @param string $domain The domain name to unsuspend.
     * @return array
     * @throws Exception
     */
    public function unsuspendDomain(string $domain): array
    {
        $url = "{$this->apiUrl}/unsuspend-domain";
        $params = ['domain' => $domain];

        return $this->makeApiRequest($url, $params);
    }

    /**
     * Make an API request to Virtualmin.
     *
     * @param string $url The API endpoint URL.
     * @param array $params Parameters to send with the request.
     * @return array
     * @throws Exception
     */
    protected function makeApiRequest(string $url, array $params): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])->post($url, $params);

            if ($response->failed()) {
                throw new Exception("Virtualmin API error: {$response->body()}");
            }

            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Error communicating with Virtualmin API: {$e->getMessage()}");
        }
    }

    /**
     * Generate a random admin username based on the domain name.
     *
     * @param string $domain
     * @return string
     */
    protected function generateAdminUsername(string $domain): string
    {
        return substr(str_replace('.', '', $domain), 0, 8);
    }

    /**
     * Generate a random secure password.
     *
     * @return string
     */
    protected function generateRandomPassword(): string
    {
        return bin2hex(random_bytes(8)); // 16-character random password
    }
}
