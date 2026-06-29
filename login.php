<?php
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = $pdo->prepare('SELECT u.*, r.name role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE email = ? AND u.is_active = 1');
    $stmt->execute([$_POST['email'] ?? '']);
    $u = $stmt->fetch();

    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = $u;
        log_activity($pdo, 'login', 'Masuk aplikasi');
        redirect($u['role_name'] === 'admin' ? app_url('admin/dashboard/index.php') : app_url('konsultasi/index.php'));
    }

    $error = 'Email atau password salah.';
}

require_once __DIR__.'/includes/header.php';
?>
<div class="container px-3 px-lg-4 auth-shell">
 <div class="row g-4 align-items-stretch">
  <div class="col-lg-6 d-none d-lg-block"><div class="auth-art h-100 p-5 panel"><span class="badge text-bg-primary rounded-pill mb-3">Akses Sistem</span><h2 class="section-title">Masuk untuk konsultasi dan melihat riwayat diagnosis.</h2><p class="text-muted">Panel ini mempertahankan proses login backend yang sama, hanya ditata ulang agar lebih rapi dan profesional.</p><div class="display-1 text-primary mt-5"><i class="bi bi-water"></i></div></div></div>
  <div class="col-lg-6"><div class="card p-4 p-lg-5"><h3 class="section-title mb-2">Login</h3><p class="text-muted">Gunakan akun pengguna atau admin yang sudah terdaftar.</p>
  <?php if($error): ?><div class="alert alert-danger rounded-4"><i class="bi bi-exclamation-triangle me-2"></i><?=e($error)?></div><?php endif; ?>
  <form method="post"><?=csrf_field()?><div class="mb-3"><label class="form-label fw-semibold">Email</label><input class="form-control" name="email" type="email" placeholder="nama@email.com" required></div><div class="mb-3"><label class="form-label fw-semibold">Password</label><input class="form-control" name="password" type="password" placeholder="••••••••" required></div><div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember"><label class="form-check-label">Remember Me</label></div><button class="btn btn-primary w-100 py-2"><i class="bi bi-box-arrow-in-right me-2"></i>Login</button></form><p class="text-center mt-3 mb-0">Belum punya akun? <a href="<?=app_url('register.php')?>">Buat akun</a>.</p></div></div>
 </div>
</div>
<?php require_once __DIR__.'/includes/footer.php'; ?>
