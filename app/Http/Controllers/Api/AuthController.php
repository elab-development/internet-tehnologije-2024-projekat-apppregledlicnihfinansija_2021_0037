<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;           
use Illuminate\Support\Facades\Hash;               
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Auth\Events\PasswordReset;         
use Illuminate\Support\Str;                        
use App\Models\User;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    // POST /api/v1/auth/register
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'], // + password_confirmation
        ]);

        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'email_verified_at' => now(), // po potrebi; ili izbaci ako želiš verifikaciju e-pošte
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user'  => ['id'=>$user->id,'name'=>$user->name,'email'=>$user->email],
            'token' => $token,
        ], 201);
    }

    // POST /api/v1/auth/login
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user'  => ['id'=>$user->id,'name'=>$user->name,'email'=>$user->email],
            'token' => $token,
        ]);
    }

    // POST /api/v1/auth/logout  (zaht. Bearer token)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required','email'],
        ]);

        
        $status = Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => __($status),
        ], $status === Password::RESET_LINK_SENT ? 200 : 400);
    }

    // POST /api/v1/auth/reset-password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required','email'],
            'password'              => ['required','confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email','password','password_confirmation','token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                //izloguj sanctum tokene
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)], 200);
        }

        return response()->json(['message' => __($status)], 400);
    }

}
