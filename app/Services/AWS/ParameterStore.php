<?php

namespace App\Services\AWS;

use Aws\Ssm\SsmClient;
use Exception;

class ParameterStore extends AwsBase
{
    private SsmClient $client;
    private $kmsKeyId;
    private $parameterPrefix;

    /**
     * Constructor to initialize the SSM Client
     */
    public function __construct()
    {
        parent::__construct();
        $this->client   = $this->getClient('ssm');

        $this->kmsKeyId = config('aws.ssm.kms_key_id');
        $this->parameterPrefix  = config('aws.ssm.parameter_prefix');
    }

    /**
     * Create or update a parameter in the Parameter Store.
     *
     * @param string $name
     * @param string $value
     * @param bool $isSecureString
     * @return bool
     */
    public function putParameter(string $name, string $value, bool $isSecureString = true): bool
    {
        try {
            $this->client->putParameter([
                'Name'      => $this->parameterPrefix . $name,
                'Value'     => $value,
                'Type'      => $isSecureString ? 'SecureString' : 'String',
                'Overwrite' => true, // Allows overwriting existing parameters
            ]);
            return true;
        } catch (Exception $e) {
            logger()->error("Failed to store parameter {$name}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve a parameter from the Parameter Store.
     *
     * @param string $name
     * @param bool $decrypt
     * @return string|null
     */
    public function getParameter(string $name, bool $decrypt = true): ?string
    {
        $result = $this->client->getParameter([
            'Name'           => $this->parameterPrefix . $name,
            'WithDecryption' => $decrypt,
        ]);

        if(isset($result['Parameter']['Value'])) {
            return $result['Parameter']['Value'];
        }

        $errorMsg   = "Failed to retrieve parameter {$name}";
        if(isset($result['message'])) {
            $errorMsg .= ': '. $result['message'];
        }

        logger()->error($errorMsg);
        throw new Exception($errorMsg);
    }

    /**
     * Delete a parameter from the Parameter Store.
     *
     * @param string $name
     * @return bool
     */
    private function deleteParameter(string $name): bool
    {
        try {
            $this->client->deleteParameter([
                'Name' => $this->parameterPrefix . $name,
            ]);
            return true;
        } catch (Exception $e) {
            logger()->error("Failed to delete parameter {$name}: " . $e->getMessage());
            return false;
        }
    }
}
