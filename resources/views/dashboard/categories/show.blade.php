@extends('dashboard.layouts.app')

@section('title', $category->name)
@section('page-title', $category->name)
@section('page-subtitle', $category->slug)

@section('page-actions')
    <a class="btn" href="{{ route('dashboard.categories.index') }}">رجوع</a>
    <a class="btn primary" href="{{ route('dashboard.categories.edit', $category) }}">تعديل</a>
@endsection

@section('content')
    <section class="panel">
        <img class="hero-image" src="{{ $category->image_url }}" alt="{{ $category->name }}">

        <div class="grid">
            <div class="metric">
                <span class="muted">المنتجات</span>
                <strong>{{ $category->meals_count }}</strong>
            </div>
            <div class="metric">
                <span class="muted">التصنيفات الفرعية</span>
                <strong>{{ $category->subcategories_count }}</strong>
            </div>
            <div class="metric">
                <span class="muted">الحالة</span>
                <strong>{{ $category->is_active ? 'نشط' : 'غير نشط' }}</strong>
            </div>
        </div>

        <div style="margin-top: 20px;">
            <h2>الوصف</h2>
            <p class="muted">{{ $category->description ?: 'لا يوجد وصف.' }}</p>
        </div>

        <div style="margin-top: 20px;">
            <h2>بيانات التصنيف</h2>
            <table>
                <tbody>
                    <tr>
                        <th>المعرف</th>
                        <td>{{ $category->id }}</td>
                    </tr>
                    <tr>
                        <th>ترتيب الظهور</th>
                        <td>{{ $category->sort_order }}</td>
                    </tr>
                    <tr>
                        <th>تاريخ الإنشاء</th>
                        <td>{{ $category->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                    <tr>
                        <th>آخر تحديث</th>
                        <td>{{ $category->updated_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if ($category->subcategories->isNotEmpty())
            <div style="margin-top: 20px;">
                <h2>التصنيفات الفرعية</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>الرابط</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($category->subcategories as $subcategory)
                                <tr>
                                    <td>{{ $subcategory->name }}</td>
                                    <td class="muted">{{ $subcategory->slug }}</td>
                                    <td>{{ $subcategory->is_active ? 'نشط' : 'غير نشط' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>
@endsection
