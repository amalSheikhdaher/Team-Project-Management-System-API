<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        // Inject the AuthService dependency
        $this->authService = $authService;
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Handle user login.
     * 
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $result = $this->authService->login($credentials);

        if ($result['status'] === 'error') {
            return response()->json([
                'status' => $result['status'],
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'status' => $result['status'],
            'user' => $result['user'],
            'authorisation' => [
                'token' => $result['token'],
                'type' => $result['type'],
            ]
        ]);
    }

    /**
     * Handle user logout.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $result = $this->authService->logout();

        return response()->json($result);
    }

    /**
     * Refresh the JWT token.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $result = $this->authService->refresh();

        return response()->json([
            'status' => $result['status'],
            'user' => $result['user'],
            'authorisation' => [
                'token' => $result['token'],
                'type' => $result['type'],
            ]
        ]);
    }
}
