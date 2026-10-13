<?php

namespace App\Http\Controllers;

use App\Models\Bell;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BellController extends Controller
{
    protected function getSchoolId(): ?int
    {
        $user = auth()->user();
        return $user->isAdmin() ? null : $user->school_id;
    }

    protected function ensureSchool(): int
    {
        $schoolId = $this->getSchoolId();

        if (!$schoolId) {
            abort(403, 'ادمین باید از پنل مدرسه‌ی مورد نظر این کار را انجام دهد.');
        }

        return $schoolId;
    }

    public function index()
    {
        $schoolId = $this->getSchoolId();

        $bells = Bell::when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('order')
            ->orderBy('start_time')
            ->get();

        return view('manager.bells.index', compact('bells'));
    }

    public function create()
    {
        $this->ensureSchool();

        return view('manager.bells.create');
    }

    public function store(Request $request)
    {
        $schoolId = $this->ensureSchool();

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'start_time' => 'required|string',
            'end_time'   => 'required|string',
            'order'      => 'nullable|integer|min:0',
            'is_break'   => 'nullable|boolean',
        ]);

        // نرمال‌سازی
        $start = $this->normalizeTime($validated['start_time']);
        $end   = $this->normalizeTime($validated['end_time']);

        // اعتبارسنجی دقیق
        if (!$start) {
            return back()->withErrors(['start_time' => 'ساعت شروع نامعتبر. فرمت صحیح: 07:30'])->withInput();
        }
        if (!$end) {
            return back()->withErrors(['end_time' => 'ساعت پایان نامعتبر. فرمت صحیح: 08:15'])->withInput();
        }
        if ($start >= $end) {
            return back()->withErrors(['end_time' => 'ساعت پایان باید بعد از ساعت شروع باشد'])->withInput();
        }

        // چک تداخل با زنگ‌های دیگه
        $conflict = $this->findConflict($schoolId, $start, $end);
        if ($conflict) {
            return back()->withErrors([
                'start_time' => "این بازه با «{$conflict->name}» ({$conflict->start_time_short} - {$conflict->end_time_short}) تداخل دارد",
            ])->withInput();
        }

        Bell::create([
            'school_id'  => $schoolId,
            'name'       => $validated['name'],
            'start_time' => $start,
            'end_time'   => $end,
            'order'      => $validated['order'] ?? 0,
            'is_break'   => $request->boolean('is_break'),
        ]);

        return redirect()->route('manager.bells')->with('success', 'زنگ با موفقیت اضافه شد');
    }

    public function edit(Bell $bell)
    {
        $this->authorizeBell($bell);

        return view('manager.bells.edit', compact('bell'));
    }

    public function update(Request $request, Bell $bell)
    {
        $this->authorizeBell($bell);

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'start_time' => 'required|string',
            'end_time'   => 'required|string',
            'order'      => 'nullable|integer|min:0',
            'is_break'   => 'nullable|boolean',
        ]);

        $start = $this->normalizeTime($validated['start_time']);
        $end   = $this->normalizeTime($validated['end_time']);

        if (!$start) {
            return back()->withErrors(['start_time' => 'ساعت شروع نامعتبر'])->withInput();
        }
        if (!$end) {
            return back()->withErrors(['end_time' => 'ساعت پایان نامعتبر'])->withInput();
        }
        if ($start >= $end) {
            return back()->withErrors(['end_time' => 'ساعت پایان باید بعد از ساعت شروع باشد'])->withInput();
        }

        $conflict = $this->findConflict($bell->school_id, $start, $end, $bell->id);
        if ($conflict) {
            return back()->withErrors([
                'start_time' => "این بازه با «{$conflict->name}» ({$conflict->start_time_short} - {$conflict->end_time_short}) تداخل دارد",
            ])->withInput();
        }

        $bell->update([
            'name'       => $validated['name'],
            'start_time' => $start,
            'end_time'   => $end,
            'order'      => $validated['order'] ?? 0,
            'is_break'   => $request->boolean('is_break'),
        ]);

        return redirect()->route('manager.bells')->with('success', 'زنگ با موفقیت ویرایش شد');
    }

    public function destroy(Bell $bell)
    {
        $this->authorizeBell($bell);

        $bell->delete();

        return back()->with('success', 'زنگ حذف شد');
    }

    protected function authorizeBell(Bell $bell): void
    {
        $user = auth()->user();

        if (!$user->isAdmin() && $bell->school_id !== $user->school_id) {
            abort(403);
        }
    }

    /**
     * نرمال‌سازی زمان: "۷:۳۰" یا "7:30" → "07:30:00"
     * اگه نامعتبر بود، null برمی‌گردونه
     */
    protected function normalizeTime(string $time): ?string
    {
        // اعداد فارسی به انگلیسی
        $time = str_replace(
            ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹',  '٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
            ['0','1','2','3','4','5','6','7','8','9',  '0','1','2','3','4','5','6','7','8','9'],
            trim($time)
        );

        // فرمت: H:MM یا HH:MM
        if (!preg_match('/^(\d{1,2}):(\d{2})$/', $time, $m)) {
            return null;
        }

        $h = (int) $m[1];
        $i = (int) $m[2];

        // محدوده‌ی معتبر
        if ($h < 0 || $h > 23) return null;
        if ($i < 0 || $i > 59) return null;

        return sprintf('%02d:%02d:00', $h, $i);
    }

    /**
     * پیدا کردن زنگ تداخل‌دار
     * قاعده: A.start < B.end AND A.end > B.start
     */
    protected function findConflict(int $schoolId, string $start, string $end, ?int $exceptId = null): ?Bell
    {
        return Bell::where('school_id', $schoolId)
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->first();
    }
}
