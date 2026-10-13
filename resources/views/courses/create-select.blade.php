@extends('layouts.app')

@section('title', 'ساخت کلاس جدید')

@section('content')
<div class="text-center mb-10">
    <h2 class="text-2xl font-bold mb-2">چه نوع کلاسی می‌خواید بسازید؟</h2>
    <p class="text-gray-500">یکی از گزینه‌های زیر رو انتخاب کنید</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <a href="{{ route('courses.create.regular') }}"
       class="block bg-white rounded-2xl shadow-sm border hover:border-indigo-400 hover:shadow-md transition p-8 text-center">
        <div class="text-4xl mb-4">📚</div>
        <h3 class="text-lg font-semibold mb-2">کلاس درسی</h3>
        <p class="text-gray-500 text-sm">کلاس رسمی با برنامه‌ی هفتگی ثابت و لیست دانش‌آموز/دانشجوی مشخص</p>
    </a>

    <a href="{{ route('courses.create.extra') }}"
       class="block bg-white rounded-2xl shadow-sm border hover:border-indigo-400 hover:shadow-md transition p-8 text-center">
        <div class="text-4xl mb-4">🎨</div>
        <h3 class="text-lg font-semibold mb-2">کلاس فوق‌برنامه</h3>
        <p class="text-gray-500 text-sm">کلاس آزاد بدون محدودیت زمان‌بندی ثابت، مناسب کارگاه و فعالیت جانبی</p>
    </a>
</div>
@endsection
