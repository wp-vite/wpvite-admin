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

        return $this->makeApiRequest($command, $params);
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

        return $this->makeApiRequest($command, $params);
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

        return $this->makeApiRequest($command, $params);
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
        $command = "suspend-domain";
        $params = ['domain' => $domain];

        return $this->makeApiRequest($command, $params);
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
        $command = "unsuspend-domain";
        $params = ['domain' => $domain];

        return $this->makeApiRequest($command, $params);
    }
}
