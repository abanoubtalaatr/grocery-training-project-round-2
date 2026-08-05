<!DOCTYPE html>
<html>
<head>
    <title>{{ $subject ?? 'Your Document' }}</title>
</head>
<body>
    <h2>Hello,</h2>
    <p>Please find the requested document attached to this email.</p>
    <br>
    <p>Best regards,<br>{{ config('app.name') }}</p>
</body>
</html>
