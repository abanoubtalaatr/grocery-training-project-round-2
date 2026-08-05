<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>فاتورة</title>
    <style>
        body { font-family: 'Tajawal', sans-serif; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .invoice-header { text-align: center; border-bottom: 2px solid #4F46E5; padding-bottom: 20px; }
        .invoice-details { margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #4F46E5; color: white; }
        .total { text-align: right; font-size: 18px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="invoice-header">
            <h1>فاتورة</h1>
            <p>رقم الفاتورة: <?php echo e($invoiceData['invoice_number'] ?? 'N/A'); ?></p>
            <p>التاريخ: <?php echo e($invoiceData['date'] ?? now()->format('Y-m-d')); ?></p>
        </div>

        <div class="invoice-details">
            <h3>معلومات العميل</h3>
            <p><strong>الاسم:</strong> <?php echo e($user->name ?? 'N/A'); ?></p>
            <p><strong>البريد الإلكتروني:</strong> <?php echo e($user->email ?? 'N/A'); ?></p>
        </div>

        <h3>تفاصيل الفاتورة</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>المنتج</th>
                    <th>الكمية</th>
                    <th>السعر</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $invoiceData['items'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($index + 1); ?></td>
                    <td><?php echo e($item['name'] ?? 'N/A'); ?></td>
                    <td><?php echo e($item['quantity'] ?? 0); ?></td>
                    <td><?php echo e($item['price'] ?? 0); ?> ₪</td>
                    <td><?php echo e(($item['quantity'] ?? 0) * ($item['price'] ?? 0)); ?> ₪</td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>

        <div class="total">
            <p><strong>المبلغ الإجمالي: <?php echo e($invoiceData['total'] ?? 0); ?> ₪</strong></p>
        </div>

        <div style="text-align: center; margin-top: 30px; color: #666; border-top: 1px solid #eee; padding-top: 20px;">
            <p>شكراً لثقتكم بنا</p>
        </div>
    </div>
</body>
</html>
<?php /**PATH /home/mohammed-ouais/project-Huma-volve-Laravel/grocery-training-project-round-2/resources/views/pdfs/invoice.blade.php ENDPATH**/ ?>