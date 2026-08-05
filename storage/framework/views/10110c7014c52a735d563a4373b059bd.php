<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #dcdcdc;
        }

        th {
            background: #f3f3f3;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        .section {
            margin-bottom: 20px;
        }

        .text-right {
            text-align: right;
        }

        .total {
            font-weight: bold;
            font-size: 15px;
        }
    </style>

</head>

<body>

    <h1>Invoice</h1>

    <div class="section">

        <strong>Invoice Number:</strong>
        <?php echo e($receipt['invoice_number']); ?>


        <br>

        <strong>Receipt Number:</strong>
        <?php echo e($receipt['receipt_number']); ?>


        <br>

        <strong>Date:</strong>
        <?php echo e($receipt['date']); ?>


        <br>

        <strong>Status:</strong>
        <?php echo e($receipt['status_description']); ?>


    </div>

    <div class="section">

        <h3>Customer Information</h3>

        <p>
            <strong>Name:</strong>
            <?php echo e($receipt['customer']['name']); ?>

        </p>

        <p>
            <strong>Email:</strong>
            <?php echo e($receipt['customer']['email']); ?>

        </p>

        <p>
            <strong>Phone:</strong>
            <?php echo e($receipt['customer']['phone']); ?>

        </p>

    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($receipt['delivery_address']): ?>

        <div class="section">

            <h3>Delivery Address</h3>

            <p>

                <?php echo e($receipt['delivery_address']['full_address']); ?>


            </p>

        </div>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="section">

        <h3>Order Items</h3>

        <table>

            <thead>

                <tr>

                    <th>Meal</th>

                    <th>Quantity</th>

                    <th>Unit Price</th>

                    <th>Subtotal</th>

                </tr>

            </thead>

            <tbody>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $receipt['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                    <tr>

                        <td>
                            <?php echo e($item['meal']['title']); ?>

                        </td>

                        <td>
                            <?php echo e($item['quantity']); ?>

                        </td>

                        <td>
                            $<?php echo e(number_format($item['unit_price'], 2)); ?>

                        </td>

                        <td>
                            $<?php echo e(number_format($item['subtotal'], 2)); ?>

                        </td>

                    </tr>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            </tbody>

        </table>

    </div>

    <div class="section">

        <table>

            <tr>

                <td>Subtotal</td>

                <td class="text-right">
                    $<?php echo e(number_format($receipt['pricing']['subtotal'],2)); ?>

                </td>

            </tr>

            <tr>

                <td>Tax</td>

                <td class="text-right">
                    $<?php echo e(number_format($receipt['pricing']['tax'],2)); ?>

                </td>

            </tr>

            <tr>

                <td>Discount</td>

                <td class="text-right">
                    $<?php echo e(number_format($receipt['pricing']['discount'],2)); ?>

                </td>

            </tr>

            <tr class="total">

                <td>Total</td>

                <td class="text-right">
                    $<?php echo e(number_format($receipt['pricing']['total'],2)); ?>

                </td>

            </tr>

        </table>

    </div>

    <div class="section">

        <strong>Payment Method:</strong>

        <?php echo e($receipt['payment']['method_display']); ?>


    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($receipt['notes']): ?>

        <div class="section">

            <strong>Notes:</strong>

            <?php echo e($receipt['notes']); ?>


        </div>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</body>

</html><?php /**PATH D:\huma volve\clone\grocery-training-project-round-2\resources\views/pdf/invoice.blade.php ENDPATH**/ ?>