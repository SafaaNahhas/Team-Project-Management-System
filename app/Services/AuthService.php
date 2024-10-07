<?php
namespace App\Services;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Class AuthService
 *
 * This service class handles the core authentication logic for user login, registration, logout, and token refresh operations.
 *
 * @package App\Services
 */
class AuthService
{
    /**
     * Attempt to log in the user with the provided credentials.
     *
     * @param array $credentials The user's email and password.
     * @return array The response containing the authentication token or an error message with the status code.
     */
 
    public function login($credentials)
    {
        if (!$token = Auth::attempt($credentials)) {
            throw new Exception('Unauthorized');
        }

        return $this->respondWithToken($token);
    }

    /**
     * Log out the currently authenticated user.
     *
     * @return array The response confirming the user has logged out with the status code.
     */
    public function logout()
    {
        Auth::logout();
        return ['message' => 'Successfully logged out', 'status' => 200];
    }


    /**
     * Refresh the authentication token for the currently authenticated user.
     *
     * @return array The response containing the new authentication token and related information.
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * Format the response with the authentication token and user details.
     *
     * @param string $token The JWT authentication token.
     * @return array The formatted response including the token, token type, expiration time, and user details.
     */
    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'user' => Auth::user(),
        ];
    }
}
