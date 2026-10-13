<?php

namespace App\Http\Controllers;

use App\Models\ScheduleSlot;
use App\Services\MoodleClient;
use Illuminate\Support\Facades\Log;

class MoodleSsoController extends Controller
{
    /**
     * ریدایرکت به Moodle با SSO
     */
    public function redirectToCourse(ScheduleSlot $slot)
	{
	    $user = auth()->user();

	    if ($user->isTeacher() && $slot->teacher_id !== $user->id) {
	        abort(403, 'این کلاس مال شما نیست');
	    }

	    if (empty($user->email)) {
	        return back()->withErrors(['error' => 'ایمیل شما تنظیم نشده است']);
	    }

	    $klass = $slot->klass;
	    if (!$klass || !$klass->moodle_course_id) {
	        return back()->withErrors(['error' => 'دوره‌ی Moodle برای این کلاس ساخته نشده است']);
	    }

    // ذخیره courseId توی session
	    session(['moodle_redirect_course' => $klass->moodle_course_id]);

	    $moodle = new MoodleClient();
	    $ssoUrl = $moodle->generateSsoLoginUrl($user->email);

	    if (!$ssoUrl) {
	        return back()->withErrors(['error' => 'خطا در ساخت لینک SSO']);
	    }

	    return redirect($ssoUrl);
	}
}
