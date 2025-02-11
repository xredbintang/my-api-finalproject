<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $request){
        $data = $request->validated();
    
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']) 
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'Successfully created your account',
            'data' => $user
        ], 201);
    }
    

    public function logout(Request $request)
{
    try {
        auth('api')->logout();
        auth('api')->invalidate(true);        
        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
            'data' => null
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to logout',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function requestReset(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Email tidak ditemukan',
            'errors' => $validator->errors()
        ], 400);
    }

    $token = Str::random(32);

    DB::table('password_reset_tokens')->updateOrInsert(
        ['email' => $request->email],
        ['token' => $token, 'created_at' => now()]
    );

    Mail::raw("Gunakan token berikut untuk mereset password Anda : $token", function ($message) use ($request) {
        $message->to($request->email)
                ->subject('Reset Password Anda');
    });

    return response()->json([
        'success' => true,
        'message' => 'Token reset berhasil dikirim ke email',
    ], 200);
}

public function resetPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'token' => 'required',
        'email' => 'required|email|exists:users,email',
        'password' => 'required|min:6|confirmed'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => $validator->errors()
        ], 400);
    }

    $resetData = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->where('token', $request->token)
        ->first();

    if (!$resetData) {
        return response()->json([
            'success' => false,
            'message' => 'Token tidak valid atau sudah kedaluwarsa'
        ], 400);
    }

    $user = User::where('email', $request->email)->first();
    $user->password = Hash::make($request->password);
    $user->save();

    DB::table('password_reset_tokens')->where('email', $request->email)->delete();

    return response()->json([
        'success' => true,
        'message' => 'Password berhasil direset, silakan login kembali'
    ], 200);
}


protected function storeLatestAccessToken($userId, $token)
{

    //Cache gak berfgunsi, DB solusinya
    Log::info("Menyimpan token baru untuk user ID: " . $userId);
    Log::info(message: "Token: " . $token);

    DB::table('users')->where('id', $userId)->update([
        'latest_access_token' => $token
    ]);
}

public function login(LoginRequest $request) 
    {
        $credentials = $request->validated(); 

    if (!$token = auth()->guard('api')->attempt($credentials)) {
        return response()->json([
            'success' => false,
            'message' => 'Wrong username or password'
        ], 401);
    }

        $user = auth('api')->user();
        $user->latest_access_token = $token;
        $user->save();

        $refreshToken = auth()->guard('api')
            ->setTTL(10080) // 7 days in minutes
            ->claims([
                'refresh' => true,
                'user_id' => $user->id,
            ])
            ->fromUser($user);

        $this->storeLatestAccessToken($user->id, $token);

        return response()->json([
            'success' => true,
            'message' => 'Successfully Login',
            'data' => $user,
            'access_token' => $token,
            'refresh_token' => $refreshToken
        ], 200);
    }

    public function refresh(Request $request) 
{
    try {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token not provided'
            ], 401);
        }

        $token = str_replace('Bearer ', '', $token);

        try {
            $payload = auth()->guard('api')->setToken($token)->getPayload();

            if ($payload->get('refresh') !== true) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only refresh token is allowed'
                ], 403);
            }

            $user = auth()->guard('api')->setToken($token)->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid refresh token'
                ], 401);
            }

            $newAccessToken = auth()->guard('api')
                ->setTTL(1440)
                ->claims(['refresh' => false])
                ->fromUser($user);

            $user->latest_access_token = $newAccessToken;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Access token successfully generated',
                'access_token' => $newAccessToken
            ], 200);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token has expired'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid refresh token'
            ], 401);
        }

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to refresh token',
            'error' => $e->getMessage()
        ], 500);
    }
}




    


}
