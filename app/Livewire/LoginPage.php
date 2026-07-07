<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Services\ApiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

#[Title('Login | PMIS')]
class LoginPage extends Component
{
    #[Layout('components.layouts.login')]

    public $email;
    public $password;
    public $errorMessage;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];

    public function authenticate(ApiService $apiService)
    {
        $this->validate();

        $this->errorMessage = null;

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        // Check if this is a local database user (bypass API)
        $localUser = User::where('email', $this->email)
            ->whereNotNull('password')
            ->first();

        if ($localUser && Hash::check($this->password, $localUser->password)) {
            return $this->handleLocalLogin($localUser, $credentials);
        }

        // Continue with API authentication
        $response = $apiService->login($credentials);

        if (!isset($response['statusCode']) || $response['statusCode'] != 200) {
            $this->errorMessage = $response['message'] ?? 'Invalid credentials';
            return;
        }

        // Get API employee id
        $apiEmployeeId = $response['employee']['id'] ?? null;
        $user = null;

        if ($apiEmployeeId) {
            // Find Laravel user by hris_id
            $user = User::where('hris_id', $apiEmployeeId)->first();

            // If the user does not exist, create them with a default role
            if (!$user) {
                $employeeData = $response['employee'];

                // Create user WITHOUT triggering audit events (no authenticated user yet)
                $user = User::withoutEvents(function () use ($apiEmployeeId, $employeeData) {
                    return User::create([
                        'hris_id' => $apiEmployeeId,
                        'name' => $employeeData['firstName'] . ' ' . $employeeData['lastName'],
                        'email' => $employeeData['email'] ?? $this->email,
                        'password' => bcrypt(str()->random(32)), // Random password since using API auth
                    ]);
                });

                // Assign the default 'User' role
                $user->assignRole('User');
            }
        }

        // If we have a user (either found or newly created), log them in
        if ($user) {
            Auth::login($user);
        } else {
            $this->errorMessage = 'Could not verify your employee ID from the API.';
            return;
        }

        // Store JWT for API requests
        session([
            'jwt_token' => $response['token'] ?? null,
            'roleName' => $response['roleName'] ?? null,
            'user' => $response['employee'] ?? null,
            'token_created_at' => time(),
            'login_credentials' => $credentials,
        ]);

        // After successful login, handle the photo
        $photoUrl = $response['employee']['photoUrl'] ?? null;
        $employeeId = $response['employee']['id'] ?? null;

        // Clear any previously cached copies so we always get the latest
        if ($employeeId) {
            Storage::disk('public')->delete([
                'employees/' . $employeeId . '.jpg',
                'photos/' . $employeeId,
            ]);
        }

        if ($photoUrl && $employeeId) {
            try {
                $res = Http::withHeaders([
                    'Authorization' => 'Bearer ' . ($response['token'] ?? ''),
                ])->get($photoUrl);

                if ($res->successful()) {
                    $contents = $res->body();
                    $filename = 'employees/' . $employeeId . '.jpg';

                    Storage::disk('public')->put($filename, $contents);
                    session(['user_photo' => asset('storage/' . $filename)]);
                } else {
                    session(['user_photo' => asset('storage/employees/default.png')]);
                }
            } catch (\Exception $e) {
                session(['user_photo' => asset('storage/employees/default.png')]);
            }
        } else {
            session(['user_photo' => asset('storage/employees/default.png')]);
        }

        return redirect()->route('dashboard');
    }

    /**
     * Handle login for local database users (no API check)
     */
    protected function handleLocalLogin($user, $credentials)
    {
        Auth::login($user);

        // Set mock session data for local users
        session([
            'jwt_token' => 'local_token_' . time(),
            'roleName' => $user->roles->first()?->name ?? 'User',
            'user' => [
                'id' => $user->hris_id ?? $user->id,
                'firstName' => explode(' ', $user->name)[0] ?? 'User',
                'lastName' => explode(' ', $user->name)[1] ?? '',
                'email' => $user->email,
            ],
            'token_created_at' => time(),
            'login_credentials' => $credentials,
            'user_photo' => asset('storage/employees/default.png'),
        ]);

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.login-page');
    }
}
