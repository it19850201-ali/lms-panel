@extends('layouts.app')

@section('title', 'منابع ' . $klass->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-1">منابع {{ $klass->academicLevel->name }} — کلاس {{ $klass->name }}</h2>
        <a href="{{ route('materials') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← بازگشت</a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 rounded-lg p-4 mb-6 text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 text-red-600 rounded-lg p-4 mb-6 text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- فرم آپلود --}}
    @if ($canUpload)
        <div class="bg-white rounded-2xl border p-6 mb-6">
            <h3 class="font-bold text-lg mb-4">📤 آپلود فایل جدید</h3>

            <form method="POST" action="{{ route('materials.store', $klass) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">درس</label>
                        <select name="subject" required
                                class="w-full border rounded-lg px-4 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">— انتخاب —</option>
                            @foreach ($subjects as $s)
                                <option value="{{ $s }}" {{ old('subject') === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">دسته‌بندی</label>
                        <select name="category" required
                                class="w-full border rounded-lg px-4 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach (\App\Models\CourseMaterial::$categories as $cat)
                                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">عنوان</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           placeholder="مثلاً فصل ۳ - معادلات"
                           class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">توضیحات (اختیاری)</label>
                    <textarea name="description" rows="2"
                              class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">فایل</label>
                    <input type="file" name="file" required
                           class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">
                        فرمت‌های مجاز: PDF، Word، Excel، تصویر، ویدیو، zip — حداکثر ۲۰ مگابایت
                    </p>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-indigo-700">
                        آپلود
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- لیست فایل‌ها --}}
    @if ($materials->isEmpty())
        <div class="bg-white rounded-2xl border p-12 text-center text-gray-500">
            هنوز فایلی آپلود نشده است.
        </div>
    @else
        <div class="space-y-6">
            @foreach ($materials as $subject => $items)
                <div>
                    <h3 class="text-sm font-bold text-gray-700 mb-2">📖 {{ $subject }}</h3>
                    <div class="bg-white rounded-2xl border overflow-hidden">
                        @foreach ($items as $m)
                            <div class="flex justify-between items-center px-5 py-4 border-b last:border-b-0">
                                <div class="flex items-center gap-3 flex-1">
                                    <span class="text-2xl">{{ $m->icon }}</span>
                                    <div class="flex-1">
                                        <p class="font-bold text-sm">{{ $m->title }}</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <span class="bg-gray-100 px-2 py-0.5 rounded">{{ $m->category }}</span>
                                            · {{ $m->file_size_human }}
                                            · {{ $m->teacher->name }}
                                            · {{ \App\Helpers\JalaliHelper::diffForHumans($m->created_at) }}
                                        </p>
                                        @if ($m->description)
                                            <p class="text-xs text-gray-500 mt-1">{{ $m->description }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('materials.download', $m) }}"
                                       class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-indigo-700">
                                        دانلود
                                    </a>
                                    @if (auth()->user()->isAdmin() || auth()->user()->isManager() || $m->teacher_id === auth()->id())
                                        <form method="POST" action="{{ route('materials.destroy', $m) }}"
                                              onsubmit="return confirm('این فایل حذف شود؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="border border-red-200 text-red-600 px-3 py-1.5 rounded-lg text-xs hover:bg-red-50">
                                                حذف
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
