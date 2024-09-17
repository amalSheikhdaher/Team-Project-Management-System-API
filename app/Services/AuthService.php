<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class AuthService
{
    /**
     * Attempt to log in the user with given credentials.
     * 
     * @param array $credentials
     * @return array
     */
    public function login(array $credentials)
    {
        $token = Auth::attempt($credentials);
        if (!$token) {
            return [
                'status' => 'error',
                'message' => 'Unauthorized',
                'code' => 401
            ];
        }

        return [
            'status' => 'success',
            'user' => Auth::user(),
            'token' => $token,
            'type' => 'bearer'
        ];
    }

    /**
     * Logout the user and invalidate the token.
     * 
     * @return array
     */
    public function logout()
    {
        Auth::logout();

        return [
            'status' => 'success',
            'message' => 'Successfully logged out',
        ];
    }

    /**
     * Refresh the JWT token.
     * 
     * @return array
     */
    public function refresh()
    {
        return [
            'status' => 'success',
            'user' => Auth::user(),
            'token' => Auth::refresh(),
            'type' => 'bearer'
        ];
    }
}
