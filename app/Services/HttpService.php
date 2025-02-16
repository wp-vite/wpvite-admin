<?php
namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;

class HttpService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'verify' => Config::get('app.env') !== 'local',
        ]);
    }

    /**
     * Send a GET request.
     *
     * @param string $url
     * @param array $data
     * @param array $headers
     * @return array
     * @throws \Exception
     */
    public function get(string $url, array $data = [], array $headers = []): array
    {
        return $this->sendRequest('get', $url, $data, $headers);
    }

    /**
     * Send a POST request.
     *
     * @param string $url
     * @param array $data
     * @param array $headers
     * @return array
     * @throws \Exception
     */
    public function post(string $url, array $data = [], array $headers = []): array
    {
        return $this->sendRequest('post', $url, $data, $headers);
    }

    /**
     * Send HTTP request
     *
     * @param string $method
     * @param string $url
     * @param array $data
     * @param array $headers
     *
     * @return array
     */
    public function sendRequest(string $method, string $url, array $data = [], array $headers = []): array
    {
        $headers = array_merge([
            'Accept' => 'application/json',
        ], $headers);

        $options = [
            'headers' => $headers,
        ];

        if (isset($data['form_params'])) {
            $options['form_params'] = $data['form_params'];
            unset($headers['Content-Type']); // Adjust Content-Type for form data
        } elseif (isset($data['body'])) {
            $options['body'] = json_encode($data['body']);
        } elseif (isset($data['multipart'])) {
            $options['multipart'] = $data['multipart'];
            unset($headers['Content-Type']); // Adjust Content-Type for multipart
        }

        $this->client = new Client([
            'verify' => false
        ]);

        try {
            $response = $this->client->request(strtoupper($method), $url, $options);

            $responseBody = $response->getBody()->getContents();
            $decodedData = json_decode($responseBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON response: " . json_last_error_msg());
            }

            return [
                'status' => true,
                'http_status' => $response->getStatusCode(),
                'response_data' => $decodedData,
                'message' => null,
            ];
        } catch (RequestException $e) {
            $message = $e->hasResponse()
                ? $e->getResponse()->getBody()->getContents()
                : $e->getMessage();

            return [
                'status' => false,
                'http_status' => $e->getCode(),
                'response_data' => null,
                'message' => $message,
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'http_status' => 500, // Generic server error
                'response_data' => null,
                'message' => $e->getMessage(),
            ];
        }
    }
}
