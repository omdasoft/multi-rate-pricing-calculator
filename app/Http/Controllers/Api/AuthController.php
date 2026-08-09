<?php

namespace App\Http\Controllers\Api;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\DTOs\UserLoginData;
use App\DTOs\UserRegisterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $user = $action->execute(UserRegisterData::fromArray($request->validated()));

        $token = $user->createToken('api')->plainTextToken; 

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        [$user, $token] = $action->execute(UserLoginData::fromArray($request->validated()));

        return response()->json(['user' => $user, 'token' => $token]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(status: 204);
    }
}
