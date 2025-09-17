<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Services\ApiService;
use Illuminate\Support\Facades\Auth;

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

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        $response = $apiService->login($credentials);

        if (!isset($response['statusCode']) || $response['statusCode'] != 200) {
            session()->flash('errorMessage', $response['message'] ?? 'Invalid credentials');
            return redirect()->route('login');
        }

        // Get API employee id
        $apiEmployeeId = $response['employee']['id'] ?? null;

        if ($apiEmployeeId) {
            // Find Laravel user by hris_id
            $user = User::where('hris_id', $apiEmployeeId)->first();
            if ($user) {
                Auth::login($user);
            } else {
                session()->flash('errorMessage', 'No corresponding user found in the system.');
                return redirect()->route('login');
            }
        }

        // Store JWT for API requests
        session([
            'jwt_token' => $response['token'] ?? null,
            'roleName' => $response['roleName'] ?? null,
            'user' => $response['employee'] ?? null,
            'token_created_at' => time(),
            'login_credentials' => $credentials,
        ]);


        // After successful login
        $photoUrl = $response['employee']['photoUrl'] ?? null;

        if ($photoUrl) {
            try {
                // Download the image from API
                $res = Http::withHeaders([
                    'Authorization' => 'Bearer ' . ($response['token'] ?? ''),
                ])->get($photoUrl);

                if ($res->successful()) {
                    $contents = $res->body();
                    $filename = 'employees/' . $response['employee']['id'] . '.jpg';

                    // Save to storage/app/public/employees/
                    Storage::disk('public')->put($filename, $contents);

                    // Update session to point to local copy
                    session(['user_photo' => asset('storage/' . $filename)]);
                } else {
                    // If download fails, fallback to default
                    session(['user_photo' => asset('storage/employees/default.png')]);
                }
            } catch (\Exception $e) {
                session(['user_photo' => asset('storage/employees/default.png')]);
            }
        } else {
            // No photo URL, use default
            session(['user_photo' => asset('storage/employees/default.png')]);
        }


        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.login-page');
    }
}
