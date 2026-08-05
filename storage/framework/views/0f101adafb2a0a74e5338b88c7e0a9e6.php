<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h2 { margin-bottom: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        .totals { margin-top: 15px; width: 300px; margin-left: auto; }
        .totals td { border: none; padding: 4px 8px; }
        .totals .grand-total { font-weight: bold; font-size: 14px; border-top: 2px solid #333; }
    </style>
</head>
<body>
    <h2>Invoice <?php echo e($receipt['invoice_number']); ?></h2>
    <p>Date: <?php echo e(\Carbon\Carbon::parse($receipt['date'])->format('d M Y, H:i')); ?></p>

    <p>
        <strong>Customer:</strong> <?php echo e($receipt['customer']['name']); ?><br>
        <strong>Email:</strong> <?php echo e($receipt['customer']['email']); ?><br>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($receipt['customer']['phone']): ?>
            <strong>Phone:</strong> <?php echo e($receipt['customer']['phone']); ?><br>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </p>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($receipt['delivery_address']): ?>
        <p>
            <strong>Delivery Address:</strong><br>
            <?php echo e($receipt['delivery_address']['full_address'] ?? $receipt['delivery_address']['street_address']); ?>

        </p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $receipt['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($item['meal']['title']); ?></td>
                    <td><?php echo e($item['quantity']); ?></td>
                    <td><?php echo e(number_format($item['unit_price'], 2)); ?></td>
                    <td><?php echo e(number_format($item['subtotal'], 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td><?php echo e(number_format($receipt['pricing']['subtotal'], 2)); ?></td>
        </tr>
        <tr>
            <td>Tax</td>
            <td><?php echo e(number_format($receipt['pricing']['tax'], 2)); ?></td>
        </tr>
        <tr>
            <td>Discount</td>
            <td>-<?php echo e(number_format($receipt['pricing']['discount'], 2)); ?></td>
        </tr>
        <tr class="grand-total">
            <td>Total</td>
            <td><?php echo e(number_format($receipt['pricing']['total'], 2)); ?></td>
        </tr>
    </table>
</body>
</html><?php /**PATH D:\grocery-training-project-round-2\resources\views/pdf/invoice.blade.php ENDPATH**/ ?>