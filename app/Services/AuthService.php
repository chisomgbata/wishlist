<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\Registered;

class AuthService
{

    public function createUser(mixed $validatedData)
    {
        // Hash the password before saving
        $validatedData['password'] = bcrypt($validatedData['password']);

        // Create the user
        $user = User::create($validatedData);

        event(new Registered($user));
        
        return $user;
    }
}
