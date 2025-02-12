<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'admin_username' => 'required',
            'admin_password' => 'required'
        ]);

        $admin = Admin::where('admin_username', $request->admin_username)->first();

        if (!$admin || !Hash::check($request->admin_password, $admin->admin_password)) {
            return response()->json(['message' => 'Login gagal'], 401);
        }

        return response()->json([
            'message' => 'Login berhasil',
            'admin' => [
                'username' => $admin->admin_username
            ]
        ]);
    }

    public function logout(Request $request)
    {
        return response()->json(['message' => 'Logout berhasil']);
    }
}