<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }
    
    public function register(Request $request)
    {
        // Validate
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'user_type' => 'nullable|in:customer,seller,rider',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
        ]);
        
        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'district' => $request->district,
            'province' => $request->province,
            'user_type' => $request->user_type ?? 'customer',
            'verification_status' => 'approved',
            'registration_completed' => true,
            'password_changed' => true,
        ]);
        
        // Create wallet
        Wallet::create([
            'user_id' => $user->id,
            'user_type' => $user->user_type,
            'balance' => 0,
        ]);
        
        // Login
        Auth::login($user);
        
        // Redirect
        if ($user->user_type === 'seller') {
            return redirect()->route('seller.dashboard');
        } elseif ($user->user_type === 'rider') {
            return redirect()->route('rider.dashboard');
        }
        
        return redirect()->route('client.dashboard');
    }
}