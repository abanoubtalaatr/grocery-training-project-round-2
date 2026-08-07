<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="bg-body-tertiary">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <div class="p-2 rounded-3 bg-dark text-white">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <div>
                                <h1 class="h4 mb-0">Admin Portal</h1>
                                <p class="text-muted mb-0">Sign in to continue</p>
                            </div>
                        </div>

                        @include('admin.partials.flash-messages')

                        <form method="POST" action="{{ route('admin.login.post') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="identifier" class="form-label">Username or email</label>
                                <input type="text" class="form-control rounded-3 @error('identifier') is-invalid @enderror" id="identifier" name="identifier" value="{{ old('identifier') }}" required>
                                @error('identifier')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control rounded-3 @error('password') is-invalid @enderror" id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <button type="submit" class="btn btn-dark w-100 rounded-3">Sign in</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
