<?php

namespace App\Services\AWS;

use Aws\Sdk;

class AwsBase
{
    protected $accessKey;
    protected $secretKey;
    protected $region;
    protected $sdk;

    /**
     * Constructor to initialize AWS SDK and configuration.
     */
    public function __construct()
    {
        $this->accessKey = config('aws.access_key');
        $this->secretKey = config('aws.secret_key');
        $this->region    = config('aws.region');

        // Initialize AWS SDK
        $this->sdk = new Sdk([
            'credentials' => [
                'key'    => $this->accessKey,
                'secret' => $this->secretKey,
            ],
            'region'  => $this->region,
            'version' => 'latest',
        ]);
    }

    /**
     * Get an AWS client for a specific service.
     *
     * @param string $serviceName
     * @return mixed
     */
    protected function getClient(string $serviceName)
    {
        return $this->sdk->createClient($serviceName);
    }
}
