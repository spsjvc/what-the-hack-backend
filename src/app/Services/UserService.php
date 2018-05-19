<?php

namespace App\Http\Services;

use App\User;

class UserService
{

    /**
     * Register User and get a JWT token.
     *
     * @param  array  $userData
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(array $userData)
    {
        $user = new User;
        $user->email = array_get($userData, 'email');
        $user->name = array_get($userData, 'name');
        $user->password = \Hash::make(array_get($userData, 'password'));

        if ($user->save()) {
            return $this->login($userData);
        }

        return response()->json(['error' => 'Something terrible happened'], 500);
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  array  $userData
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(array $userData)
    {
        if ($token = $this->guard()->attempt($userData)) {
            return $this->respondWithToken($token);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer'
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return \Auth::guard();
    }
}
