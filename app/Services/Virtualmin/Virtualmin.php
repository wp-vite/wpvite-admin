<?php

namespace App\Services\Virtualmin;

use App\Models\HostingServer;
use App\Services\AWS\ParameterStore;
use App\Services\HttpService;
use Exception;
use Illuminate\Support\Facades\Log;

class Virtualmin
{
    private $apiKey;
    private $authType;
    protected $baseUrl;
    protected $httpService;

    public function __construct(HttpService $httpService)
    {
        $this->httpService  = $httpService;
    }

    /**
     * Get server with credential
     * @param \App\Models\HostingServer $hostingServer
     * @return static
     */
    public function server(HostingServer $hostingServer)
    {
        $this->baseUrl  = rtrim($hostingServer->virtualmin_url, '/') .'/';

        $authorization  = $hostingServer->authorization;
        $this->authType = ucfirst(strtolower($authorization->auth_type));

        $authSource = $authorization->auth_source;
        if($authSource == 'aws_parameter_store') {
            $parameterName  = 'hosting_servers/'. strtolower($hostingServer->server_uid);
            $auth   = (new ParameterStore)->getParameter($parameterName);
            $this->apiKey   = base64_encode($auth);
        }

        return $this;
    }

    /**
     * Make an API request to Virtualmin.
     *
     * @param string $command The API endpoint URL.
     * @param array $params Parameters to send with the request.
     * @return array
     * @throws Exception
     */
    protected function makeApiRequest(string $command, array $params): array
    {
        $url    = $this->baseUrl.'virtual-server/remote.cgi?json=1&program='.$command;

        $response = $this->httpService->post($url, ['form_params' => $params], [
            'Authorization' => "{$this->authType} {$this->apiKey}",
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ]);

        if (!$response['status']) {
            Log::error("Virtualmin API error: {$response['message']}");
        }

        return $response;
    }
}
