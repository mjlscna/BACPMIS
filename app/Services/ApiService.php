<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;

class ApiService
{
    protected $client;
    protected $tokenTTL = 300; // token validity in seconds (5 minutes)
    protected $refreshThreshold = 240; // refresh at 4 minutes

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'http://192.168.100.162:8081/',
            'timeout' => 10.0,
        ]);
    }

    public function login($credentials)
    {
        try {
            $response = $this->client->post('auth/login', [
                'json' => $credentials,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!empty($data['token'])) {
                Session::put('jwt_token', $data['token']);
                Session::put('token_created_at', time());
                Session::put('login_credentials', $credentials); // only store if safe
            }

            return $data;
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    public function request($method, $uri, $data = [])
    {
        // Ensure token is valid before making request
        $this->ensureTokenIsFresh();

        try {
            $response = $this->client->request($method, $uri, [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token'),
                ],
                'json' => $data,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    public function ensureTokenIsFresh()
    {
        $token = Session::get('jwt_token');
        $createdAt = Session::get('token_created_at', 0);

        // If token is missing or older than refresh threshold
        if (!$token || (time() - $createdAt) >= $this->refreshThreshold) {
            $credentials = Session::get('login_credentials');

            if ($credentials) {
                $this->login($credentials);
            }
        }
    }
}
