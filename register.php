<?php
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/includes/functions.php';

if (current_user()) {
    redirect('/Sistem-Pakar-Potensi-Banjir/konsultasi/index.php');
}

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if ($name === '') {
        $errors[] = 'Nama lengkap wajib diisi.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }

    if ($password !== $passwordConfirm) {
        $errors[] = 'Konfirmasi password tidak sama.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = 'Email sudah terdaftar. Silakan login.';
        } else {
            $roleStmt = $pdo->prepare('SELECT id FROM roles WHERE name = ? LIMIT 1');
            $roleStmt->execute(['user']);
            $roleId = $roleStmt->fetchColumn();

            if (!$roleId) {
                $errors[] = 'Role pengguna belum tersedia.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insert = $pdo->prepare('INSERT INTO users(role_id, name, email, password, is_active) VALUES(?, ?, ?, ?, 1)');
                $insert->execute([$roleId, $name, $email, $hashedPassword]);

                $userStmt = $pdo->prepare('SELECT u.*, r.name role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?');
                $userStmt->execute([$pdo->lastInsertId()]);
                $_SESSION['user'] = $userStmt->fetch();

                log_activity($pdo, 'register', 'Membuat akun baru');
                redirect('/Sistem-Pakar-Potensi-Banjir/konsultasi/index.php');
            }
        }
    }
}

require_once __DIR__.'/includes/header.php';
?>
<div class="container px-3 px-lg-4 auth-shell"><div class="row justify-content-center"><div class="col-lg-7"><div class="card p-4 p-lg-5"><span class="badge text-bg-primary rounded-pill align-self-start mb-3">Akun Pengguna</span><h3 class="section-title mb-2">Buat Akun</h3><p class="text-muted">Daftar untuk mulai konsultasi dan menyimpan riwayat hasil diagnosa.</p><?php if($errors): ?><div class="alert alert-danger rounded-4"><ul class="mb-0"><?php foreach($errors as $error): ?><li><?=e($error)?></li><?php endforeach; ?></ul></div><?php endif; ?><form method="post"><div class="mb-3"><label class="form-label fw-semibold">Nama Lengkap</label><input class="form-control" name="name" value="<?=e($name)?>" required></div><div class="mb-3"><label class="form-label fw-semibold">Email</label><input class="form-control" name="email" type="email" value="<?=e($email)?>" required></div><div class="row g-3"><div class="col-md-6"><label class="form-label fw-semibold">Password</label><input class="form-control" name="password" type="password" minlength="6" required></div><div class="col-md-6"><label class="form-label fw-semibold">Konfirmasi Password</label><input class="form-control" name="password_confirm" type="password" minlength="6" required></div></div><button class="btn btn-primary w-100 py-2 mt-4">Daftar</button></form><p class="text-center mt-3 mb-0">Sudah punya akun? <a href="/Sistem-Pakar-Potensi-Banjir/login.php">Login</a>.</p></div></div></div></div>
<?php require_once __DIR__.'/includes/footer.php'; ?>
