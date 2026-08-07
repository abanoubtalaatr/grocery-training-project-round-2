<?php $__env->startSection('title', 'التصنيفات'); ?>
<?php $__env->startSection('page-title', 'التصنيفات'); ?>
<?php $__env->startSection('page-subtitle', 'إدارة تصنيفات المنتجات المعروضة في التطبيق'); ?>

<?php $__env->startSection('page-actions'); ?>
    <a class="btn primary" href="<?php echo e(route('dashboard.categories.create')); ?>">تصنيف جديد</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <section class="panel">
        <form class="filters" method="GET" action="<?php echo e(route('dashboard.categories.index')); ?>">
            <input name="search" value="<?php echo e(request('search')); ?>" placeholder="بحث بالاسم أو الرابط أو الوصف">
            <select name="status">
                <option value="">كل الحالات</option>
                <option value="active" <?php if(request('status') === 'active'): echo 'selected'; endif; ?>>نشط</option>
                <option value="inactive" <?php if(request('status') === 'inactive'): echo 'selected'; endif; ?>>غير نشط</option>
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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><img class="thumb" src="<?php echo e($category->image_url); ?>" alt="<?php echo e($category->name); ?>"></td>
                            <td><strong><?php echo e($category->name); ?></strong></td>
                            <td class="muted"><?php echo e($category->slug); ?></td>
                            <td><?php echo e($category->meals_count); ?></td>
                            <td><?php echo e($category->subcategories_count); ?></td>
                            <td><?php echo e($category->sort_order); ?></td>
                            <td>
                                <span class="badge <?php echo e($category->is_active ? 'active' : 'inactive'); ?>">
                                    <?php echo e($category->is_active ? 'نشط' : 'غير نشط'); ?>

                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="btn" href="<?php echo e(route('dashboard.categories.show', $category)); ?>">عرض</a>
                                    <a class="btn" href="<?php echo e(route('dashboard.categories.edit', $category)); ?>">تعديل</a>
                                    <form method="POST" action="<?php echo e(route('dashboard.categories.destroy', $category)); ?>" onsubmit="return confirm('هل تريد حذف هذا التصنيف؟')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="btn danger" type="submit">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="muted">لا توجد تصنيفات مطابقة.</td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination"><?php echo e($categories->links()); ?></div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('dashboard.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\Traning\projects\grocery-training-project-round-2\resources\views/dashboard/categories/index.blade.php ENDPATH**/ ?>