<?php
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('SELECT u.*, r.name role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE email = ? AND u.is_active = 1');
    $stmt->execute([$_POST['email'] ?? '']);
    $u = $stmt->fetch();

    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        $_SESSION['user'] = $u;
        log_activity($pdo, 'login', 'Masuk aplikasi');
        redirect($u['role_name'] === 'admin' ? '/Sistem-Pakar-Potensi-Banjir/admin/dashboard/index.php' : '/Sistem-Pakar-Potensi-Banjir/konsultasi/index.php');
    }

    $error = 'Email atau password salah.';
}

require_once __DIR__.'/includes/header.php';
?>
<div class="container col-md-5">
    <div class="card p-4">
        <h3>Login</h3>
        <?php if($error): ?>
            <div class="alert alert-danger"><?=e($error)?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label>Email</label>
                <input class="form-control" name="email" type="email" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input class="form-control" name="password" type="password" required>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember">
                <label class="form-check-label">Remember Me</label>
            </div>
            <button class="btn btn-primary w-100">Login</button>
        </form>
        <p class="text-center mt-3 mb-0">Belum punya akun? <a href="/Sistem-Pakar-Potensi-Banjir/register.php">Buat akun di sini</a>.</p>
    </div>
</div>
<?php require_once __DIR__.'/includes/footer.php'; ?>
