<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - {{ config('app.name', 'Grocery Shop') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;850&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="login-header">
            <h1>{{ config('app.name', 'Grocery Shop') }}</h1>
            <p>Admin Control Panel</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">
                <span>{{ session('error') }}</span>
                <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                <span>{{ session('success') }}</span>
                <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="login" class="form-label">Email or Username</label>
                <input type="text" name="login" id="login" class="form-control" value="{{ old('login') }}" required autofocus placeholder="e.g. admin or admin@example.com">
                @error('login')
                    <span class="form-feedback-invalid">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required placeholder="••••••••">
                @error('password')
                    <span class="form-feedback-invalid">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group" style="flex-direction: row; align-items: center; gap: 8px; margin-top: 10px;">
                <input type="checkbox" name="remember" id="remember" style="width: auto;">
                <label for="remember" class="form-label" style="margin-bottom: 0; cursor: pointer;">Remember Me</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px; justify-content: center; padding: 12px;">
                <i class="fa-solid fa-right-to-bracket"></i> Login to Dashboard
            </button>
        </form>
    </div>
</body>
</html>
