@extends('dashboard.layouts.app')

@section('title', 'إضافة تصنيف')
@section('page-title', 'إضافة تصنيف')
@section('page-subtitle', 'إنشاء تصنيف جديد للمنتجات')

@section('content')
    <section class="panel">
        <form method="POST" action="{{ route('dashboard.categories.store') }}" enctype="multipart/form-data">
            @include('dashboard.categories._form', [
                'category' => $category,
                'submitLabel' => 'حفظ التصنيف',
            ])
        </form>
    </section>
@endsection
