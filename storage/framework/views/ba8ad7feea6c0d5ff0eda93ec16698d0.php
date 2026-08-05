<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>فاتورة الطلب</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            text-align: right; 
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        th { background-color: #f2f2f2; }
        .text-left { text-align: left; }
    </style>
</head>
<body>
    <h2>فاتورة للطلب رقم #<?php echo e($order->order_number); ?></h2>
    <p>التاريخ: <?php echo e($order->created_at->format('Y-m-d H:i:s')); ?></p>
    <p>العميل: <?php echo e(optional($order->user)->name); ?></p>

    <table>
        <thead>
            <tr>
                <th>المنتج</th>
                <th>الكمية</th>
                <th>السعر</th>
                <th>المجموع</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e(optional($item->meal)->title); ?></td>
                <td><?php echo e($item->quantity); ?></td>
                <td>$<?php echo e(number_format($item->unit_price, 2)); ?></td>
                <td>$<?php echo e(number_format($item->subtotal, 2)); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-left"><strong>المجموع الفرعي</strong></td>
                <td>$<?php echo e(number_format($order->subtotal, 2)); ?></td>
            </tr>
            <tr>
                <td colspan="3" class="text-left"><strong>الضريبة</strong></td>
                <td>$<?php echo e(number_format($order->tax, 2)); ?></td>
            </tr>
            <tr>
                <td colspan="3" class="text-left"><strong>التوصيل</strong></td>
                <td>$<?php echo e(number_format($order->shipping_fee, 2)); ?></td>
            </tr>
            <tr>
                <td colspan="3" class="text-left"><strong>الإجمالي</strong></td>
                <td><strong>$<?php echo e(number_format($order->total, 2)); ?></strong></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
<?php /**PATH D:\Laravel-Training\grocery-training-project-round-2\resources\views/pdf/invoice.blade.php ENDPATH**/ ?>