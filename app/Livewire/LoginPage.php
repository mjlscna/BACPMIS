<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class LoginPage extends Component
{
    public $email = '';
    public $password = '';
    public $errorMessage = '';

    public function authenticate()
    {
        \Log::debug('Attempting login: ' . $this->email);

        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();
            \Log::debug('✅ Auth successful: ' . $this->email);
            return redirect()->route('dashboard.page'); // make sure this exists!
        }

        \Log::debug('❌ Auth failed: ' . $this->email);
        $this->errorMessage = 'Invalid credentials. Please try again.';
    }



    public function render()
    {
        return view('livewire.login-page')->layout('components.layouts.login');
    }
}

