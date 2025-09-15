<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
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
    public function authenticate()
    {
        // Clear previous error
        session()->forget('errorMessage');

        \Log::info('Authenticate method called');

        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();
            \Log::info('Login successful');
            return redirect()->route('dashboard');
        } else {
            \Log::info('Login failed - setting error message');
            session(['errorMessage' => 'Invalid Credentials']);
            \Log::info('Error message set in session');
        }
    }

    public function render()
    {
        \Log::info('Render method called. Session error: ' . session('errorMessage'));
        return view('livewire.login-page');
    }
}
