<?php

namespace App\Actions\Auth;

use App\DTOs\UserLoginData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class LoginUserAction
{
    public function execute(UserLoginData $data): array
    {
        $user = User::where('email', $data->email)->first();
        
        $password = $data->password;

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return [$user, $token];
    }
}
