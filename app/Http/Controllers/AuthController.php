<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordLinkMail;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            Log::info('[LOGIN] User berhasil login', [
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
            ]);

            if ($user->role === 'admin') {
                $admin = $user->admin;
                if (!$admin || $admin->status !== 1 || !$admin->is_active) {
                    Auth::logout();

                    $errorMsg = match (true) {
                        !$admin => 'Akun admin tidak ditemukan.',
                        $admin->status === 2 => 'Akun anda ditolak.',
                        $admin->status === 0 => 'Akun anda belum disetujui.',
                        !$admin->is_active => 'Akun anda dinonaktifkan.',
                    };

                    return back()->withErrors(['username' => $errorMsg]);
                }

                return redirect()->route('dashboard.index');
            }

            if ($user->role === 'superadmin') {
                return redirect()->route('dashboard.index');
            }

            return redirect()->route('landing');
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ]);
    }


    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
        ]);


        Auth::login($user);
        return redirect()->route('dashboard.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function handleForgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan dalam.']);
        }

        $token = Str::random(64);
        $user->update(['reset_token' => $token]);

        $link = route('password.reset.form', ['token' => $token]);

        Mail::to($user->email)->send(new ResetPasswordLinkMail($user->first_name, $link));

        return redirect()->route('login')->with('success', 'Link reset password telah dikirim ke email Anda.');
    }

    public function showResetPasswordForm($token)
    {
        $user = User::where('reset_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')->withErrors(['token' => 'Token tidak valid atau sudah kadaluarsa. Silahkan lakukan reset password kembali!']);
        }

        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('reset_token', $request->token)->first();

        if (!$user) {
            return redirect()->route('login')->withErrors(['token' => 'Token tidak valid.']);
        }

        $user->update([
            'password' => bcrypt($request->password),
            'reset_token' => null,
        ]);

        return redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login.');
    }

    // Admin
    public function showAdminRegisterForm()
    {
        return view('auth.register_admin');
    }

    public function registerAdmin(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'jabatan' => 'required|string|max:100',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'admin',
        ]);

        Admin::create([
            'user_id' => $user->id,
            'jabatan' => $data['jabatan'],
            'status' => 0, // 0: menunggu, 1: disetujui, 2: ditolak
            'is_active' => true,
        ]);

        return redirect()->route('login')->with('status', 'Registrasi berhasil. Tunggu persetujuan dari superadmin.');
    }
}
