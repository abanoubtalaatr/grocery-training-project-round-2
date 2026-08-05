<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورتك</title>
    <style>
        body { font-family: 'Tajawal', sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4F46E5; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .footer { text-align: center; padding: 20px; color: #666; }
        .invoice-details { background: white; padding: 15px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📄 فاتورتك جاهزة</h1>
        </div>
        
        <div class="content">
            <h2>مرحباً {{ $userName }}،</h2>
            <p>نشكرك على استخدام خدماتنا. نرفق لك فاتورتك في المرفقات.</p>
            
            <div class="invoice-details">
                <h3>تفاصيل الفاتورة:</h3>
                <ul>
                    <li><strong>رقم الفاتورة:</strong> {{ $invoiceData['invoice_number'] ?? 'N/A' }}</li>
                    <li><strong>التاريخ:</strong> {{ $invoiceData['date'] ?? now()->format('Y-m-d') }}</li>
                    <li><strong>المبلغ الإجمالي:</strong> {{ $invoiceData['total'] ?? '0.00' }} ₪</li>
                </ul>
            </div>
            
            <p>يمكنك الاطلاع على الفاتورة من خلال المرفق المرفق مع هذا البريد.</p>
            <p>مع خالص الشكر،<br>فريق الدعم</p>
        </div>
        
        <div class="footer">
            <p>هذا بريد إلكتروني تلقائي، يرجى عدم الرد عليه.</p>
            <p>© {{ date('Y') }} جميع الحقوق محفوظة</p>
        </div>
    </div>
</body>
</html>
