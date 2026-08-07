@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">الرئيسية</h1>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> نجاح!</h5>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> خطأ!</h5>
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-exclamation-triangle"></i> تنبيه!</h5>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Small boxes (Stat box) -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $summary['total_orders'] }}</h3>
                    <p>إجمالي الطلبات</p>
                </div>
                <div class="icon"><i class="fas fa-shopping-bag"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($summary['total_revenue'], 2) }}<sup style="font-size: 20px"> ج.م</sup></h3>
                    <p>إجمالي الإيرادات</p>
                </div>
                <div class="icon"><i class="fas fa-chart-pie"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $summary['total_users'] }}</h3>
                    <p>المستخدمين المسجلين</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $summary['total_categories'] }}</h3>
                    <p>عدد الفئات</p>
                </div>
                <div class="icon"><i class="fas fa-tags"></i></div>
            </div>
        </div>
    </div>

    <!-- Main row -->
    <div class="row">
        <!-- Categories Table -->
        <section class="col-lg-8 connectedSortable" id="categories">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-1"></i> الفئات الحالية
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>المنتجات</th>
                                <th>الحالة</th>
                                <th>الترتيب</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td>{{ $category->name }}</td>
                                    <td><span class="badge badge-info">{{ $category->meals_count }}</span></td>
                                    <td>
                                        @if($category->is_active)
                                            <span class="badge badge-success">نشط</span>
                                        @else
                                            <span class="badge badge-danger">معطّل</span>
                                        @endif
                                    </td>
                                    <td>{{ $category->sort_order }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="openEditCategory({
                                            id: {{ $category->id }},
                                            name: '{{ addslashes($category->name) }}',
                                            description: '{{ addslashes($category->description) }}',
                                            image: '{{ addslashes($category->image) }}',
                                            sortOrder: '{{ $category->sort_order }}',
                                            isActive: '{{ $category->is_active ? 1 : 0 }}'
                                        })"><i class="fas fa-edit"></i> تعديل</button>

                                        <form method="POST" action="{{ route('dashboard.categories.destroy', ['category' => $category->id]) }}" class="d-inline" onsubmit="return confirm('هل تريد حذف هذه الفئة؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">لا توجد فئات حتى الآن.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Category Form -->
        <section class="col-lg-4 connectedSortable">
            <div class="card card-primary" id="add-category-card">
                <div class="card-header">
                    <h3 class="card-title">أضف فئة جديدة</h3>
                </div>
                <form method="POST" action="{{ route('dashboard.categories.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label>اسم الفئة</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="أدخل اسم الفئة" required>
                        </div>
                        <div class="form-group">
                            <label>الوصف</label>
                            <textarea name="description" class="form-control" placeholder="وصف الفئة">{{ old('description') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>رابط صورة</label>
                            <input type="url" name="image" class="form-control" value="{{ old('image') }}" placeholder="https://...">
                        </div>
                        <div class="form-group">
                            <label>ترتيب العرض</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">نشط</label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">حفظ الفئة</button>
                    </div>
                </form>
            </div>

            <div class="card card-warning d-none" id="edit-category-panel">
                <div class="card-header">
                    <h3 class="card-title">تعديل الفئة</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" onclick="closeEditPanel()"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <form id="edit-category-form" method="POST" action="{{ route('dashboard.categories.update', ['category' => 0]) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_category_id" id="edit-category-id" value="">
                    <div class="card-body">
                        <div class="form-group">
                            <label>اسم الفئة</label>
                            <input type="text" name="name" id="edit-category-name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>الوصف</label>
                            <textarea name="description" id="edit-category-description" class="form-control"></textarea>
                        </div>
                        <div class="form-group">
                            <label>رابط صورة</label>
                            <input type="text" name="image" id="edit-category-image" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>ترتيب العرض</label>
                            <input type="number" name="sort_order" id="edit-category-sort-order" class="form-control" min="0">
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" id="edit-category-active" class="form-check-input" value="1">
                            <label class="form-check-label" for="edit-category-active">نشط</label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">تحديث الفئة</button>
                        <button type="button" class="btn btn-default float-right" onclick="closeEditPanel()">إلغاء</button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <!-- Orders and Users row -->
    <div class="row">
        <section class="col-lg-6 connectedSortable" id="orders">
            <div class="card">
                <div class="card-header border-transparent">
                    <h3 class="card-title">أحدث الطلبات</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table m-0">
                            <thead>
                                <tr>
                                    <th>رقم الطلب</th>
                                    <th>المستخدم</th>
                                    <th>الحالة</th>
                                    <th>الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td>{{ $order->order_number }}</td>
                                        <td>{{ optional($order->user)->name ?? 'ضيف' }}</td>
                                        <td>
                                            @if($order->status === 'delivered')
                                                <span class="badge badge-success">{{ $order->status_description }}</span>
                                            @elseif($order->status === 'cancelled')
                                                <span class="badge badge-danger">{{ $order->status_description }}</span>
                                            @else
                                                <span class="badge badge-warning">{{ $order->status_description }}</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($order->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">لا توجد طلبات حديثة.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <section class="col-lg-6 connectedSortable">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">أحدث المستخدمين</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="users-list clearfix">
                        @forelse($recentUsers as $user)
                            <li>
                                <img src="{{ $user->profile_image_url ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=random' }}" alt="User Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                <a class="users-list-name" href="#">{{ $user->name }}</a>
                                <span class="users-list-date">{{ $user->created_at->diffForHumans() }}</span>
                            </li>
                        @empty
                            <li class="w-100 text-center py-3">لا يوجد مستخدمين.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </section>
    </div>

    <!-- Top Meals -->
    <div class="row" id="meals">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">أهم المنتجات (الأكثر مبيعاً)</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($topMeals as $meal)
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <img src="{{ $meal->image_url ?? 'https://via.placeholder.com/240x160?text=No+Image' }}" class="card-img-top" alt="{{ $meal->title }}" style="height: 150px; object-fit: cover;">
                                    <div class="card-body">
                                        <h5 class="card-title w-100 mb-2 font-weight-bold">{{ $meal->title }}</h5>
                                        <p class="card-text text-muted text-sm">{{ Str::limit($meal->description, 60) }}</p>
                                    </div>
                                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                                        <span class="badge badge-info">{{ $meal->category?->name ?? 'بدون فئة' }}</span>
                                        <strong class="text-primary">{{ number_format($meal->final_price, 2) }} ج.م</strong>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center">لا توجد منتجات متاحة للعرض.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@stack('scripts')
<script>
    function openEditCategory(category) {
        const panel = document.getElementById('edit-category-panel');
        const addCard = document.getElementById('add-category-card');
        const form = document.getElementById('edit-category-form');
        const action = form.getAttribute('action');
        const updateRoute = action.replace('/0', '/' + category.id);

        panel.classList.remove('d-none');
        addCard.classList.add('d-none');
        
        form.setAttribute('action', updateRoute);
        document.getElementById('edit-category-id').value = category.id;
        document.getElementById('edit-category-name').value = category.name;
        document.getElementById('edit-category-description').value = category.description;
        document.getElementById('edit-category-image').value = category.image;
        document.getElementById('edit-category-sort-order').value = category.sortOrder;
        document.getElementById('edit-category-active').checked = category.isActive === '1';
        
        window.scrollTo({ top: panel.offsetTop - 80, behavior: 'smooth' });
    }

    function closeEditPanel() {
        document.getElementById('edit-category-panel').classList.add('d-none');
        document.getElementById('add-category-card').classList.remove('d-none');
    }
</script>
