<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class ApiService
{
    protected $client;
    protected $tokenTTL = 300;         // token validity (5 minutes)
    protected $refreshThreshold = 240; // refresh after 4 minutes

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
                'http_errors' => false,
            ]);
            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['statusCode']) && $data['statusCode'] == 200) {
                Session::put('jwt_token', $data['token'] ?? null);
                Session::put('token_created_at', time());
                Session::put('login_credentials', $credentials);
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

    protected function ensureTokenIsFresh()
    {
        $token = Session::get('jwt_token');
        $createdAt = Session::get('token_created_at', 0);

        if (!$token || (time() - $createdAt) >= $this->refreshThreshold) {
            $credentials = Session::get('login_credentials');
            if ($credentials) {
                $this->login($credentials);
            }
        }
    }

    /**
     * Fetches and stores a user's photo locally
     *
     * @param array $user
     * @return string|null URL to stored image or null if failed
     */
    public function fetchUserPhoto(array $user): ?string
    {
        $this->ensureTokenIsFresh();

        if (!isset($user['photoUrl'])) {
            return null;
        }

        try {
            $response = $this->client->get('employee/image/' . $user['photoUrl'], [
                'headers' => [
                    'Authorization' => 'Bearer ' . Session::get('jwt_token'),
                ],
                'http_errors' => false,
            ]);

            if ($response->getStatusCode() === 200) {
                $imageContent = $response->getBody()->getContents();
                $imagePath = 'photos/' . $user['photoUrl'];

                Storage::disk('public')->put($imagePath, $imageContent);

                $imageUrl = asset('storage/' . $imagePath);
                Session::put('userImage', $imageUrl);

                return $imageUrl;
            } else {
                Session::flash('error', 'Failed to fetch user photo: ' . $response->getBody()->getContents());
                return null;
            }
        } catch (\Exception $e) {
            Session::flash('error', 'An error occurred while fetching photo: ' . $e->getMessage());
            return null;
        }
    }
}
