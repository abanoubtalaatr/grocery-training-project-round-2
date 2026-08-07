<?php $__env->startSection('title', $category->name); ?>
<?php $__env->startSection('page-title', $category->name); ?>
<?php $__env->startSection('page-subtitle', $category->slug); ?>

<?php $__env->startSection('page-actions'); ?>
    <a class="btn" href="<?php echo e(route('dashboard.categories.index')); ?>">رجوع</a>
    <a class="btn primary" href="<?php echo e(route('dashboard.categories.edit', $category)); ?>">تعديل</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <section class="panel">
        <img class="hero-image" src="<?php echo e($category->image_url); ?>" alt="<?php echo e($category->name); ?>">

        <div class="grid">
            <div class="metric">
                <span class="muted">المنتجات</span>
                <strong><?php echo e($category->meals_count); ?></strong>
            </div>
            <div class="metric">
                <span class="muted">التصنيفات الفرعية</span>
                <strong><?php echo e($category->subcategories_count); ?></strong>
            </div>
            <div class="metric">
                <span class="muted">الحالة</span>
                <strong><?php echo e($category->is_active ? 'نشط' : 'غير نشط'); ?></strong>
            </div>
        </div>

        <div style="margin-top: 20px;">
            <h2>الوصف</h2>
            <p class="muted"><?php echo e($category->description ?: 'لا يوجد وصف.'); ?></p>
        </div>

        <div style="margin-top: 20px;">
            <h2>بيانات التصنيف</h2>
            <table>
                <tbody>
                    <tr>
                        <th>المعرف</th>
                        <td><?php echo e($category->id); ?></td>
                    </tr>
                    <tr>
                        <th>ترتيب الظهور</th>
                        <td><?php echo e($category->sort_order); ?></td>
                    </tr>
                    <tr>
                        <th>تاريخ الإنشاء</th>
                        <td><?php echo e($category->created_at?->format('Y-m-d H:i')); ?></td>
                    </tr>
                    <tr>
                        <th>آخر تحديث</th>
                        <td><?php echo e($category->updated_at?->format('Y-m-d H:i')); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($category->subcategories->isNotEmpty()): ?>
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
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $category->subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subcategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($subcategory->name); ?></td>
                                    <td class="muted"><?php echo e($subcategory->slug); ?></td>
                                    <td><?php echo e($subcategory->is_active ? 'نشط' : 'غير نشط'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('dashboard.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\Traning\projects\grocery-training-project-round-2\resources\views/dashboard/categories/show.blade.php ENDPATH**/ ?>