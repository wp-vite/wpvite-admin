<?php

namespace App\Services\Virtualmin;

use App\Helpers\CustomHelper;
use Exception;

class VirtualminSiteManager extends Virtualmin
{
    protected $apiUrl;
    protected $apiKey;

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
        $command = "create-domain";

        // Default parameters for domain creation
        $params = array_merge([
            'domain' => $domain,
            'plan' => $options['plan'] ?? 'WPVite-Template',
            'user' => strtolower($options['user'] ?? CustomHelper::generateRandomUsername(12, 'templ')),
            'pass' => $options['pass'] ?? CustomHelper::generateRandomPassword(),
            'features-from-plan' => '',
            'web' => '',
        ], $options);

        $response   = $this->makeApiRequest($command, $params);

        if($response['status'] && ($response['response_data']['status'] ?? '' == 'success')) {
            return ['status' => true, 'data' => $response['response_data']['output']];
        }

        return $response;
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
        $command = "delete-domain";
        $params = ['domain' => $domain];

        $response  = $this->makeApiRequest($command, $params);

        if($response['status'] && ($response['response_data']['status'] ?? '' == 'success')) {
            return ['status' => true, 'data' => $response['response_data']['output']];
        }

        return $response;
    }

    /**
     * Get details about an existing domain.
     *
     * @param string $domain The domain name to query.
     * @return array
     * @throws Exception
     */
    public function domainDetails(string $domain): array
    {
        $command = "list-domains";
        $params = [
            'domain' => $domain,
            'multiline' => ''
        ];

        $response  = $this->makeApiRequest($command, $params);

        if($response['status'] && isset($response['response_data']['data'][0]['values'])) {
            $domainData = $response['response_data']['data'][0]['values'];
            // $root_directory = $domainData['html_directory'][0] ?? null;
            return ['status' => true, 'data' => $domainData];
        }

        return $response;
    }

    /**
     * Disable a domain.
     *
     * @param string $domain The domain name to disable.
     * @return array
     * @throws Exception
     */
    public function disableDomain(string $domain): array
    {
        $command = "disable-domain";
        $params = ['domain' => $domain];

        $response  = $this->makeApiRequest($command, $params);

        if($response['status'] && ($response['response_data']['status'] ?? '' == 'success')) {
            return ['status' => true, 'data' => $response['response_data']['output']];
        }

        return $response;
    }

    /**
     * Enable a domain.
     *
     * @param string $domain The domain name to enable.
     * @return array
     * @throws Exception
     */
    public function enableDomain(string $domain): array
    {
        $command = "enable-domain";
        $params = ['domain' => $domain];

        $response  = $this->makeApiRequest($command, $params);

        if($response['status'] && ($response['response_data']['status'] ?? '' == 'success')) {
            return ['status' => true, 'data' => $response['response_data']['output']];
        }

        return $response;
    }
}
