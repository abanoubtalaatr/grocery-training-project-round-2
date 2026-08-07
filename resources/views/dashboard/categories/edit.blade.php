@extends('dashboard.layouts.app')

@section('title', 'تعديل تصنيف')
@section('page-title', 'تعديل تصنيف')
@section('page-subtitle', $category->name)

@section('content')
    <section class="panel">
        <form method="POST" action="{{ route('dashboard.categories.update', $category) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('dashboard.categories._form', [
                'category' => $category,
                'submitLabel' => 'تحديث التصنيف',
            ])
        </form>
    </section>
@endsection
