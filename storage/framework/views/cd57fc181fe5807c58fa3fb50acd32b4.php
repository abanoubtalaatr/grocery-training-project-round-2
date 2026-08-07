<?php $__env->startSection('title', 'إضافة تصنيف'); ?>
<?php $__env->startSection('page-title', 'إضافة تصنيف'); ?>
<?php $__env->startSection('page-subtitle', 'إنشاء تصنيف جديد للمنتجات'); ?>

<?php $__env->startSection('content'); ?>
    <section class="panel">
        <form method="POST" action="<?php echo e(route('dashboard.categories.store')); ?>" enctype="multipart/form-data">
            <?php echo $__env->make('dashboard.categories._form', [
                'category' => $category,
                'submitLabel' => 'حفظ التصنيف',
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </form>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('dashboard.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\Traning\projects\grocery-training-project-round-2\resources\views/dashboard/categories/create.blade.php ENDPATH**/ ?>