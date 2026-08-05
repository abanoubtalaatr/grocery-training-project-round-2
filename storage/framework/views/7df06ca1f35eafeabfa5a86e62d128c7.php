<!DOCTYPE html>
<html>
<head>
    <title><?php echo e($subject ?? 'Your Document'); ?></title>
</head>
<body>
    <h2>Hello,</h2>
    <p>Please find the requested document attached to this email.</p>
    <br>
    <p>Best regards,<br><?php echo e(config('app.name')); ?></p>
</body>
</html>
<?php /**PATH D:\Laravel-Training\grocery-training-project-round-2\resources\views/emails/invoice_generic.blade.php ENDPATH**/ ?>