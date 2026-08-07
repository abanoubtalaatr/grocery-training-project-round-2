<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title', 'لوحة التحكم'); ?></title>
    <style>
        :root {
            --bg: #f6f7f9;
            --panel: #ffffff;
            --text: #172026;
            --muted: #697680;
            --border: #dfe5ea;
            --primary: #0f766e;
            --primary-dark: #115e59;
            --danger: #b42318;
            --danger-bg: #fee4e2;
            --success-bg: #d1fadf;
            --success-text: #067647;
            --warning-bg: #fef0c7;
            --warning-text: #b54708;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Tahoma, Arial, sans-serif;
            color: var(--text);
            background: var(--bg);
            line-height: 1.6;
        }

        a { color: inherit; text-decoration: none; }

        .shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 260px 1fr;
        }

        .sidebar {
            background: #111827;
            color: #f9fafb;
            padding: 24px 18px;
        }

        .brand {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 28px;
        }

        .nav-link {
            display: block;
            padding: 11px 12px;
            border-radius: 8px;
            color: #d1d5db;
            margin-bottom: 6px;
        }

        .nav-link.active,
        .nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, .1);
        }

        .main {
            padding: 28px;
        }

        .topbar,
        .panel {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        .topbar {
            padding: 18px 20px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        h1, h2, h3, p { margin-top: 0; }
        h1 { font-size: 24px; margin-bottom: 4px; }
        h2 { font-size: 18px; }
        .muted { color: var(--muted); }

        .panel { padding: 20px; }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 8px 13px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text);
            cursor: pointer;
            font: inherit;
        }

        .btn.primary { background: var(--primary); border-color: var(--primary); color: #fff; }
        .btn.primary:hover { background: var(--primary-dark); }
        .btn.danger { background: var(--danger-bg); border-color: #fda29b; color: var(--danger); }

        .filters {
            display: grid;
            grid-template-columns: 1fr 180px auto;
            gap: 10px;
            margin-bottom: 16px;
        }

        input, textarea, select {
            width: 100%;
            min-height: 40px;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 9px 11px;
            background: #fff;
            color: var(--text);
            font: inherit;
        }

        textarea { min-height: 120px; resize: vertical; }
        label { display: block; font-weight: 700; margin-bottom: 7px; }
        .field { margin-bottom: 16px; }
        .error { color: var(--danger); font-size: 13px; margin-top: 5px; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid var(--border);
            text-align: right;
            vertical-align: middle;
        }

        th { color: var(--muted); font-weight: 700; background: #fbfcfd; }

        .thumb {
            width: 54px;
            height: 54px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid var(--border);
            background: #f3f4f6;
        }

        .hero-image {
            width: 100%;
            max-height: 320px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--border);
            margin-bottom: 16px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 3px 9px;
            font-size: 13px;
            font-weight: 700;
        }

        .badge.active { color: var(--success-text); background: var(--success-bg); }
        .badge.inactive { color: var(--warning-text); background: var(--warning-bg); }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .metric {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px;
            background: #fbfcfd;
        }

        .metric strong { display: block; font-size: 24px; }

        .alert {
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 16px;
            background: var(--success-bg);
            color: var(--success-text);
        }

        .pagination { margin-top: 16px; }
        .pagination nav > div:first-child { display: none; }
        .pagination svg { width: 18px; }

        @media (max-width: 860px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar { position: static; }
            .topbar, .filters { grid-template-columns: 1fr; display: grid; }
            .grid { grid-template-columns: 1fr; }
            .main { padding: 18px; }
            table { min-width: 760px; }
            .table-wrap { overflow-x: auto; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">Grocery Dashboard</div>
            <nav>
                <a class="nav-link <?php echo e(request()->routeIs('dashboard.categories.*') ? 'active' : ''); ?>" href="<?php echo e(route('dashboard.categories.index')); ?>">التصنيفات</a>
            </nav>
        </aside>

        <main class="main">
            <header class="topbar">
                <div>
                    <h1><?php echo $__env->yieldContent('page-title', 'لوحة التحكم'); ?></h1>
                    <p class="muted"><?php echo $__env->yieldContent('page-subtitle'); ?></p>
                </div>
                <div class="actions"><?php echo $__env->yieldContent('page-actions'); ?></div>
            </header>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                <div class="alert"><?php echo e(session('success')); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>
</body>
</html>
<?php /**PATH E:\Traning\projects\grocery-training-project-round-2\resources\views/dashboard/layouts/app.blade.php ENDPATH**/ ?>