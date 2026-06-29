<?php require_once __DIR__.'/functions.php'; ?>
<?php
$basePath = '/Sistem-Pakar-Potensi-Banjir';
$user = current_user();
$isAdmin = ($user['role_name'] ?? '') === 'admin';
$currentPath = $_SERVER['PHP_SELF'] ?? '';
function nav_active($needle){ return str_contains($_SERVER['PHP_SELF'] ?? '', $needle) ? 'active' : ''; }
?>
<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sistem Pakar Peringatan Potensi Banjir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= $basePath ?>/assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg app-navbar sticky-top">
    <div class="container-fluid px-3 px-lg-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $basePath ?>/index.php">
            <span class="brand-mark"><i class="bi bi-cloud-rain-heavy-fill"></i></span>
            <span>Pakar Banjir</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div id="nav" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link <?=nav_active('/konsultasi/')?>" href="<?= $basePath ?>/konsultasi/index.php">Konsultasi</a></li>
                <?php if($user): ?>
                    <li class="nav-item"><a class="nav-link <?=nav_active('/laporan/')?>" href="<?= $basePath ?>/laporan/index.php">Riwayat</a></li>
                    <?php if($isAdmin): ?><li class="nav-item"><a class="nav-link <?=nav_active('/admin/')?>" href="<?= $basePath ?>/admin/dashboard/index.php">Admin</a></li><?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle user-pill" href="#" role="button" data-bs-toggle="dropdown"><i class="bi bi-person-circle"></i> <?=e($user['name'] ?? 'User')?></a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><span class="dropdown-item-text small text-muted"><?=e($user['role_name'] ?? 'user')?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= $basePath ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>/login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-light btn-sm rounded-pill px-3" href="<?= $basePath ?>/register.php">Buat Akun</a></li>
                <?php endif; ?>
                <li class="nav-item"><button class="btn btn-icon" type="button" data-theme-toggle aria-label="Toggle dark mode"><i class="bi bi-moon-stars"></i></button></li>
            </ul>
        </div>
    </div>
</nav>
<main class="app-main">
