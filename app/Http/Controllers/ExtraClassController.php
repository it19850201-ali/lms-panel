<?php

namespace App\Http\Controllers;

use App\Helpers\JalaliHelper;
use App\Models\Klass;
use App\Models\ScheduleSlot;
use App\Models\User;
use App\Services\BBBClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExtraClassController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $schoolId = $user->isAdmin() ? null : $user->school_id;

        $extras = ScheduleSlot::with(['teacher', 'klass.academicLevel'])
            ->where('is_extra', true)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('starts_at', 'desc')
            ->get();

        return view('manager.extra-class.index', compact('extras'));
    }
    public function create()
    {
        $user = auth()->user();
        $schoolId = $user->isAdmin() ? null : $user->school_id;

        if (!$schoolId) {
            abort(403, 'ادمین باید از پنل مدرسه این کار را انجام دهد');
        }

        $teachers = User::where('role', 'teacher')
            ->where('school_id', $schoolId)
            ->where('status', 'approved')
            ->orderBy('name')
            ->get();

        $klasses = Klass::with('academicLevel')
            ->where('school_id', $schoolId)
            ->orderBy('academic_level_id')
            ->orderBy('name')
            ->get();

        return view('manager.extra-class.create', compact('teachers', 'klasses'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $schoolId = $user->isAdmin() ? null : $user->school_id;

        if (!$schoolId) {
            abort(403);
        }

        $school = \App\Models\School::find($schoolId);
        if (!$school) abort(404);

        $activeTerm = $school->activeTerm();
        if (!$activeTerm) {
            return back()->withErrors(['error' => 'هیچ ترم فعالی وجود ندارد. ابتدا یک ترم فعال بسازید.'])->withInput();
        }

        $rules = [
            'subject'     => 'required|string|max:255',
            'teacher_id'  => 'required|exists:users,id',
            'scope'       => 'required|in:single,all',
            'klass_id'    => 'required_if:scope,single|nullable|exists:klasses,id',
            'date'        => 'required|string',
            'start_time'  => 'required|string',
            'end_time'    => 'required|string',
            'description' => 'nullable|string|max:1000',
        ];

        // اگر دانشگاه بود، فیلدهای تکرار رو هم چک کن
        if ($school->isUniversity()) {
            $rules['is_recurring'] = 'nullable|boolean';
            $rules['recurrence_count'] = 'required_if:is_recurring,1|nullable|integer|min:1|max:32';
        }

        $validated = $request->validate($rules, [
            'subject.required'      => 'عنوان جلسه را وارد کنید',
            'teacher_id.required'   => 'استاد را انتخاب کنید',
            'klass_id.required_if'  => 'کلاس را انتخاب کنید',
            'date.required'         => 'تاریخ برگزاری را وارد کنید',
            'start_time.required'   => 'ساعت شروع را وارد کنید',
            'end_time.required'     => 'ساعت پایان را وارد کنید',
            'recurrence_count.required_if' => 'تعداد جلسات را وارد کنید',
        ]);

        $baseStartDateTime = $this->buildDateTime($validated['date'], $validated['start_time']);
        $baseEndDateTime = $this->buildDateTime($validated['date'], $validated['end_time']);

        if (!$baseStartDateTime || !$baseEndDateTime) {
            return back()->withErrors(['date' => 'فرمت تاریخ یا ساعت نامعتبر است'])->withInput();
        }
        if ($baseStartDateTime->gte($baseEndDateTime)) {
            return back()->withErrors(['end_time' => 'ساعت پایان باید بعد از شروع باشد'])->withInput();
        }

        $klassId = $validated['scope'] === 'single' ? $validated['klass_id'] : null;

        $teacher = \App\Models\User::where('id', $validated['teacher_id'])
            ->where('school_id', $schoolId)
            ->where('role', 'teacher')
            ->firstOrFail();

        $isRecurring = $school->isUniversity() && $request->boolean('is_recurring');
        $recurrenceCount = $isRecurring ? (int)$validated['recurrence_count'] : 1;
        $recurrenceGroupId = $isRecurring ? uniqid('rec-') : null;

        $bbb = new BBBClient();
        $createdCount = 0;

        DB::beginTransaction();
        try {
            for ($i = 0; $i < $recurrenceCount; $i++) {
                $currentStart = $baseStartDateTime->copy()->addWeeks($i);
                $currentEnd = $baseEndDateTime->copy()->addWeeks($i);

                // چک تداخل زمانی (اختیاری - خیلی ساده شده)
                // $conflict = ScheduleSlot::where('teacher_id', $teacher->id)->where('starts_at', $currentStart)->exists();

                $meetingId = 'extra-' . $schoolId . '-' . time() . '-' . rand(1000, 9999) . '-' . $i;
                $meetingName = $validated['subject'];

                $result = $bbb->createMeeting($meetingId, $meetingName);
                if (($result['returncode'] ?? '') !== 'SUCCESS') {
                    throw new \Exception('خطا در ساخت جلسه BBB: ' . ($result['message'] ?? 'نامشخص'));
                }

                ScheduleSlot::create([
                    'school_id'        => $schoolId,
                    'term_id'          => $activeTerm->id,
                    'klass_id'         => $klassId,
                    'teacher_id'       => $teacher->id,
                    'bell_id'          => null,
                    'day_of_week'      => null,
                    'subject'          => $validated['subject'],
                    'room'             => null,
                    'bbb_meeting_id'   => $meetingId,
                    'bbb_created_at'   => now(),
                    'is_extra'         => true,
                    'starts_at'        => $currentStart,
                    'ends_at'          => $currentEnd,
                    'extra_scope'      => $validated['scope'],
                    'is_recurring'     => $isRecurring,
                    'recurrence_group_id' => $recurrenceGroupId,
                    'recurrence_index' => $isRecurring ? ($i + 1) : null,
                    'recurrence_total' => $isRecurring ? $recurrenceCount : null,
                ]);

                $createdCount++;
            }

            DB::commit();
            return redirect()->route('manager.extra-class.index')
                ->with('success', "تعداد {$createdCount} جلسه با موفقیت ساخته شد");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Extra BBB creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'خطا در ساخت جلسات: ' . $e->getMessage()])->withInput();
        }
    }
    public function destroy(ScheduleSlot $slot)
    {
        $user = auth()->user();

        if (!$slot->is_extra) {
            abort(403);
        }

        if (!$user->isAdmin() && $slot->school_id !== $user->school_id) {
            abort(403);
        }

        // پایان BBB
        if ($slot->bbb_meeting_id) {
            try {
                (new BBBClient())->endMeeting($slot->bbb_meeting_id);
            } catch (\Throwable $e) {
                Log::warning('End BBB failed: ' . $e->getMessage());
            }
        }

        $slot->delete();

        return back()->with('success', 'فوق‌برنامه حذف شد');
    }

    protected function buildDateTime(string $date, string $time): ?\Carbon\Carbon
    {
        // تبدیل اعداد فارسی
        $date = str_replace(['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], ['0','1','2','3','4','5','6','7','8','9'], $date);
        $time = str_replace(['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], ['0','1','2','3','4','5','6','7','8','9'], $time);

        try {
            $gregorian = JalaliHelper::toGregorian($date . ' ' . $time);
            return $gregorian;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
