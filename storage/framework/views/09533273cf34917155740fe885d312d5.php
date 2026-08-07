<?php $__env->startSection('title', 'تعديل تصنيف'); ?>
<?php $__env->startSection('page-title', 'تعديل تصنيف'); ?>
<?php $__env->startSection('page-subtitle', $category->name); ?>

<?php $__env->startSection('content'); ?>
    <section class="panel">
        <form method="POST" action="<?php echo e(route('dashboard.categories.update', $category)); ?>" enctype="multipart/form-data">
            <?php echo method_field('PUT'); ?>
            <?php echo $__env->make('dashboard.categories._form', [
                'category' => $category,
                'submitLabel' => 'تحديث التصنيف',
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </form>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('dashboard.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\Traning\projects\grocery-training-project-round-2\resources\views/dashboard/categories/edit.blade.php ENDPATH**/ ?>