<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        return view('profile.show', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => [
                'required',
                'digits:11',
                Rule::unique('users', 'mobile')->ignore($user->id),
            ],
        ], [
            'mobile.unique' => 'این شماره موبایل قبلاً در سیستم ثبت شده',
            'mobile.digits' => 'شماره موبایل باید ۱۱ رقم باشد',
        ]);

        $user->update([
            'name'   => $validated['name'],
            'mobile' => $validated['mobile'],
        ]);

        return redirect()->route('profile.show')->with('success', 'اطلاعات با موفقیت به‌روزرسانی شد');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'رمز فعلی را وارد کنید',
            'password.required' => 'رمز جدید را وارد کنید',
            'password.min' => 'رمز جدید باید حداقل ۶ کاراکتر باشد',
            'password.confirmed' => 'تکرار رمز جدید مطابقت ندارد',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'رمز فعلی اشتباه است'])->withInput();
        }

        $user->update([
            'password' => Hash::make($validated['password']),
            'password_changed_at' => now(),
        ]);

        return redirect()->route('profile.show')->with('success', 'رمز عبور با موفقیت تغییر یافت');
    }
}
