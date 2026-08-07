<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Grocery Admin') }} | لوحة التحكم</title>

    <!-- Google Font: Cairo (Better for Arabic) -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Cairo:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Theme style (AdminLTE 3 RTL if possible, or standard) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <style>
        body, h1, h2, h3, h4, h5, h6 { font-family: 'Cairo', sans-serif; }
        /* RTL Adjustments since AdminLTE standard is LTR */
        .sidebar { direction: ltr; }
        .sidebar .nav-sidebar .nav-link p { direction: rtl; text-align: right; }
        .content-wrapper { direction: rtl; text-align: right; }
        .main-header { direction: rtl; }
        th { text-align: right; }
        .nav-sidebar .nav-link>p>.right { right: auto; left: 1rem; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('dashboard.index') }}" class="nav-link">الرئيسية</a>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{ route('dashboard.index') }}" class="brand-link" style="text-align: center;">
            <span class="brand-text font-weight-light">{{ config('app.name', 'Grocery') }}</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="{{ route('dashboard.index') }}" class="nav-link active">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>لوحة التحكم</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#categories" class="nav-link">
                            <i class="nav-icon fas fa-tags"></i>
                            <p>الفئات</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#meals" class="nav-link">
                            <i class="nav-icon fas fa-hamburger"></i>
                            <p>المنتجات</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#orders" class="nav-link">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>الطلبات</p>
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Main content -->
        <section class="content pt-3">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer" style="direction: rtl; text-align: left;">
        <strong>Copyright &copy; {{ date('Y') }} <a href="#">Grocery Admin</a>.</strong>
        All rights reserved.
    </footer>

</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

@stack('scripts')
</body>
</html>
