<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister()
    {
        $schools = School::where('is_active', true)->orderBy('name')->get();
        return view('auth.register', compact('schools'));
    }
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'       => 'required|string|max:255',
                'code_melli' => 'required|digits:10|unique:users,code_melli',
                'mobile'     => 'required|digits:11|unique:users,mobile',
                'password'   => 'required|string|min:6|confirmed',
                'school_id'  => 'required|exists:schools,id',
            ], [
                'code_melli.unique'   => 'این کد ملی قبلاً ثبت شده است',
                'mobile.unique'       => 'این شماره موبایل قبلاً ثبت شده است',
                'password.min'        => 'رمز عبور باید حداقل ۶ کاراکتر باشد',
                'password.confirmed'  => 'تکرار رمز عبور مطابقت ندارد',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('home')
                ->withErrors($e->errors())
                ->withInput();
        }

        User::create([
            'name'                => $validated['name'],
            'code_melli'          => $validated['code_melli'],
            'mobile'              => $validated['mobile'],
            'email'               => $validated['code_melli'] . '@placeholder.local',
            'password'            => Hash::make($validated['password']),
            'role'                => 'teacher',
            'school_id'           => $validated['school_id'],
            'status'              => 'pending',
            'password_changed_at' => now(),
        ]);

        return redirect()->route('register.pending');
    }


    public function showPending()
    {
        return view('auth.pending');
    }

    public function showLogin()
    {
        // اگه کاربر لاگین هست، به پنلش بره
        if (auth()->check()) {
            return redirect()->route('home');
        }

        // به لندینگ برو با پارامتر show_login
        return redirect()->route('home')->with('show_login', true);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'code_melli' => 'required|digits:10',
            'password'   => 'required|string',
        ], [
            'code_melli.required' => 'کد ملی را وارد کنید',
            'password.required'   => 'رمز عبور را وارد کنید',
        ]);

        if (Auth::attempt(['code_melli' => $validated['code_melli'], 'password' => $validated['password']])) {
            $user = Auth::user();

            if ($user->status === 'pending') {
                Auth::logout();
                return back()->withErrors([
                    'code_melli' => 'حساب شما هنوز توسط مدیر مدرسه تایید نشده است.',
                ])->withInput();
            }

            if ($user->status === 'rejected') {
                Auth::logout();
                return back()->withErrors([
                    'code_melli' => 'متأسفانه درخواست ثبت‌نام شما رد شده است.',
                ])->withInput();
            }

            $request->session()->regenerate();
	    Auth::login($user);
            // ریدایرکت بر اساس نقش
            // ریدایرکت بر اساس نقش
            return match ($user->role) {
                'admin'   => redirect()->route('admin.dashboard'),
                'manager' => redirect()->route('manager.dashboard'),
                'student' => redirect()->route('student.home'),
                'teacher' => redirect()->route('my-schedule'),
                default   => redirect()->route('home'),
            };
            return redirect()->route('home');
        }

        return back()->withErrors([
            'code_melli' => 'کد ملی یا رمز عبور اشتباه است',
        ])->withInput();
    }
    public function logout(Request $request)
    {
        Auth::logout();

        // فقط session رو flush کن، نه invalidate
        $request->session()->flush();
        $request->session()->regenerate();

        return redirect()->route('home');
    }

}
