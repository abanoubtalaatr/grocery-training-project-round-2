@extends('dashboard.layouts.app')

@section('title', 'التصنيفات')
@section('page-title', 'التصنيفات')
@section('page-subtitle', 'إدارة تصنيفات المنتجات المعروضة في التطبيق')

@section('page-actions')
    <a class="btn primary" href="{{ route('dashboard.categories.create') }}">تصنيف جديد</a>
@endsection

@section('content')
    <section class="panel">
        <form class="filters" method="GET" action="{{ route('dashboard.categories.index') }}">
            <input name="search" value="{{ request('search') }}" placeholder="بحث بالاسم أو الرابط أو الوصف">
            <select name="status">
                <option value="">كل الحالات</option>
                <option value="active" @selected(request('status') === 'active')>نشط</option>
                <option value="inactive" @selected(request('status') === 'inactive')>غير نشط</option>
            </select>
            <button class="btn" type="submit">بحث</button>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>الصورة</th>
                        <th>الاسم</th>
                        <th>الرابط</th>
                        <th>المنتجات</th>
                        <th>التصنيفات الفرعية</th>
                        <th>الترتيب</th>
                        <th>الحالة</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td><img class="thumb" src="{{ $category->image_url }}" alt="{{ $category->name }}"></td>
                            <td><strong>{{ $category->name }}</strong></td>
                            <td class="muted">{{ $category->slug }}</td>
                            <td>{{ $category->meals_count }}</td>
                            <td>{{ $category->subcategories_count }}</td>
                            <td>{{ $category->sort_order }}</td>
                            <td>
                                <span class="badge {{ $category->is_active ? 'active' : 'inactive' }}">
                                    {{ $category->is_active ? 'نشط' : 'غير نشط' }}
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="btn" href="{{ route('dashboard.categories.show', $category) }}">عرض</a>
                                    <a class="btn" href="{{ route('dashboard.categories.edit', $category) }}">تعديل</a>
                                    <form method="POST" action="{{ route('dashboard.categories.destroy', $category) }}" onsubmit="return confirm('هل تريد حذف هذا التصنيف؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn danger" type="submit">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="muted">لا توجد تصنيفات مطابقة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $categories->links() }}</div>
    </section>
@endsection
