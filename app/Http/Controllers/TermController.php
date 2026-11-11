<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Helpers\JalaliHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TermController extends Controller
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
            abort(403, 'ادمین باید از پنل مدرسه این کار را انجام دهد.');
        }

        return $schoolId;
    }

    public function index()
    {
        $schoolId = $this->getSchoolId();

        $terms = Term::when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('starts_at', 'desc')
            ->get();

        return view('manager.terms.index', compact('terms'));
    }

    public function create()
    {
        $this->ensureSchool();
        return view('manager.terms.create');
    }

    public function store(Request $request)
    {
        $schoolId = $this->ensureSchool();

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'starts_at'  => 'required|string',
            'ends_at'    => 'required|string',
            'is_active'  => 'nullable|boolean',
        ], [
            'name.required'      => 'نام ترم را وارد کنید',
            'starts_at.required' => 'تاریخ شروع را وارد کنید',
            'ends_at.required'   => 'تاریخ پایان را وارد کنید',
        ]);

        $startsAt = JalaliHelper::toGregorian($validated['starts_at']);
        $endsAt   = JalaliHelper::toGregorian($validated['ends_at']);

        if (!$startsAt) {
            return back()->withErrors(['starts_at' => 'فرمت تاریخ شروع نامعتبر است'])->withInput();
        }
        if (!$endsAt) {
            return back()->withErrors(['ends_at' => 'فرمت تاریخ پایان نامعتبر است'])->withInput();
        }
        if ($endsAt->lt($startsAt)) {
            return back()->withErrors(['ends_at' => 'تاریخ پایان باید بعد از شروع باشد'])->withInput();
        }

        $isActive = $request->boolean('is_active');

        // اگه قراره فعال باشه، بقیه رو غیرفعال کن
        if ($isActive) {
            Term::where('school_id', $schoolId)->update(['is_active' => false]);
        }

        Term::create([
            'school_id' => $schoolId,
            'name'      => $validated['name'],
            'starts_at' => $startsAt->toDateString(),
            'ends_at'   => $endsAt->toDateString(),
            'is_active' => $isActive,
        ]);

        return redirect()->route('manager.terms')->with('success', 'ترم با موفقیت ساخته شد');
    }

    public function edit(Term $term)
    {
        $this->authorizeTerm($term);
        return view('manager.terms.edit', compact('term'));
    }

    public function update(Request $request, Term $term)
    {
        $this->authorizeTerm($term);

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'starts_at' => 'required|string',
            'ends_at'   => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $startsAt = JalaliHelper::toGregorian($validated['starts_at']);
        $endsAt   = JalaliHelper::toGregorian($validated['ends_at']);

        if (!$startsAt || !$endsAt) {
            return back()->withErrors(['starts_at' => 'تاریخ نامعتبر است'])->withInput();
        }

        $isActive = $request->boolean('is_active');

        // اگه این ترم قراره فعال بشه، بقیه رو غیرفعال کن
        if ($isActive && !$term->is_active) {
            Term::where('school_id', $term->school_id)
                ->where('id', '!=', $term->id)
                ->update(['is_active' => false]);
        }

        $term->update([
            'name'      => $validated['name'],
            'starts_at' => $startsAt->toDateString(),
            'ends_at'   => $endsAt->toDateString(),
            'is_active' => $isActive,
        ]);

        return redirect()->route('manager.terms')->with('success', 'ترم ویرایش شد');
    }

    public function activate(Term $term)
    {
        $this->authorizeTerm($term);

        Term::where('school_id', $term->school_id)->update(['is_active' => false]);
        $term->update(['is_active' => true]);

        return back()->with('success', "ترم «{$term->name}» فعال شد");
    }

    public function destroy(Term $term)
    {
        $this->authorizeTerm($term);

        // اگه کلاسی توش هست، اجازه حذف نده
        if ($term->scheduleSlots()->count() > 0) {
            return back()->withErrors(['error' => 'این ترم کلاس داره، نمی‌تونی حذفش کنی']);
        }

        $term->delete();

        return back()->with('success', 'ترم حذف شد');
    }

    protected function authorizeTerm(Term $term): void
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $term->school_id !== $user->school_id) {
            abort(403);
        }
    }
}
