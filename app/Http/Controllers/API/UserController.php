<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use function PHPSTORM_META\map;
use App\Helpers\ResponseFormatter;
use Laravel\Fortify\Rules\Password;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;




class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'email' => ['required', 'string', 'max:255', 'email', 'unique:users'],
                'phone' => ['nullable', 'string', 'max:255'],
                'password' => ['required', 'string', new Password]
            ]);

            User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password)
            ]);

            $user = User::where('email', $request->email)->first();

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success(
                [
                    'access_token' => $tokenResult,
                    'token_type' => 'Bearer',
                    'user' => $user
                ],
                'User Registered'
            );
        } catch (\Throwable $th) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something went wrong',
                    'error' => $th,
                    'data' => $request
                ],
                'User Unregistered',
                500
            );
        }
    }

    public function  login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $credential = request(['email', 'password']);

            if (!Auth::attempt($credential)) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Unauthorized'
                    ],
                    'Authentication Failed',
                    500
                );
            }

            $user = User::where('email', $request->email)->first();


            if (!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');
        } catch (\Throwable $th) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something went wrong',
                    'error' => $th
                ],
                'Authentication Failed',
                500
            );
        }
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success($request->user(), 'Data Profile User Berhasil Diambil');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users'],
            'email' => ['nullable', 'string', 'max:255', 'email', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', new Password]
        ]);

        $data = $request->all();
        $user = Auth::user();
        $user->update($data);

        return ResponseFormatter::success($user, 'user updated');
    }
}
