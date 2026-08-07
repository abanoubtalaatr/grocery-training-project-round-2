<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($pageTitle) ? $pageTitle.' - ' : '' }}Admin Dashboard</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="bg-body-tertiary text-dark">
    <div class="d-flex min-vh-100">
        @include('admin.partials.sidebar')

        <div class="flex-grow-1 d-flex flex-column">
            @include('admin.partials.navbar')

            <main class="flex-grow-1 p-4 p-lg-5">
                @include('admin.partials.breadcrumbs')
                @include('admin.partials.flash-messages')
                {{ $slot }}
            </main>

            @include('admin.partials.footer')
        </div>
    </div>
</body>
</html>
