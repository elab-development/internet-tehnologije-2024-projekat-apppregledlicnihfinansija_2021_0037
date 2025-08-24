<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    // GET /api/v1/user (ako već nemaš svoj me endpoint)
    public function me(Request $request)
    {
        return response()->json(['data' => $request->user()]);
    }

    // POST /api/v1/account/upgrade
    public function upgrade(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'premium') {
            return response()->json(['message' => 'Već si premium 😊', 'data' => $user]);
        }

        $user->forceFill(['role' => 'premium'])->save();

        return response()->json([
            'message' => 'Uspešno si unapređen/a na premium!',
            'data'    => $user,
        ]);
    }
}
