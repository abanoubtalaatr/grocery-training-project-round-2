<!DOCTYPE html>
<html>
<body>
    <h2>Hello, <?php echo e($order->user->name); ?>!</h2>
    <p>Thank you for your purchase. Please find your invoice for Order <strong>#<?php echo e($order->order_number); ?></strong> attached to this email.</p>
    <p>Total Paid: <strong>$<?php echo e(number_format($order->total_amount, 2)); ?></strong></p>
</body>
</html><?php /**PATH H:\laravel\grocery-training-project-round-2\resources\views/emails/invoice-notification.blade.php ENDPATH**/ ?>