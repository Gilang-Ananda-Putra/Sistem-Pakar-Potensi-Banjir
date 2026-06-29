<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(app_url('konsultasi/index.php'));
}
verify_csrf();

$selected = $_POST['symptom'] ?? [];
$location = trim((string) ($_POST['location'] ?? ''));
$notes = trim((string) ($_POST['notes'] ?? ''));
$user = current_user();
$userId = (int) ($user['id'] ?? 0);

if ($location === '' || mb_strlen($location) > 150) {
    exit('Lokasi wajib diisi dan maksimal 150 karakter.');
}

$userStmt = $pdo->prepare('SELECT id FROM users WHERE id=? AND is_active=1');
$userStmt->execute([$userId]);
if (!$userStmt->fetchColumn()) {
    session_destroy();
    http_response_code(403);
    exit('Sesi pengguna tidak valid atau akun sudah nonaktif. Silakan login ulang.');
}

$variableIds = $pdo->query('SELECT id FROM variables ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
$validPairs = [];
foreach ($variableIds as $variableId) {
    if (empty($selected[$variableId])) {
        exit('Seluruh variabel pada laporan wajib dipilih.');
    }
    $stmt = $pdo->prepare('SELECT id FROM symptoms WHERE id=? AND variable_id=?');
    $stmt->execute([(int) $selected[$variableId], (int) $variableId]);
    $symptomId = $stmt->fetchColumn();
    if (!$symptomId) {
        exit('Gejala tidak valid untuk variabel laporan.');
    }
    $validPairs[(int) $variableId] = (int) $symptomId;
}

try {
    $pdo->beginTransaction();
    $fc = forward_chaining($pdo, array_values($validPairs));

    $pdo->prepare('INSERT INTO consultations(user_id,location,notes) VALUES(?,?,?)')->execute([$userId, $location, $notes]);
    $cid = (int) $pdo->lastInsertId();

    $detailStmt = $pdo->prepare('INSERT INTO consultation_details(consultation_id,variable_id,symptom_id) VALUES(?,?,?)');
    foreach ($validPairs as $vid => $sid) {
        $detailStmt->execute([$cid, $vid, $sid]);
    }

    $res = $fc['result'];
    $pdo->prepare('INSERT INTO diagnosis_results(consultation_id,rule_id,diagnosis,working_memory,active_rules,failed_rules,inference_trace,explanation,recommendation) VALUES(?,?,?,?,?,?,?,?,?)')->execute([
        $cid,
        $res['id'],
        $res['diagnosis'],
        json_encode($fc['working_memory'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        json_encode(array_column($fc['active_rules'], 'code'), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        json_encode(array_column($fc['failed_rules'], 'code'), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        json_encode($fc['trace'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        $res['explanation'],
        $res['recommendation'],
    ]);

    log_activity($pdo, 'consultation', 'Konsultasi #' . $cid);
    $pdo->commit();
    redirect(app_url('hasil/index.php?id=' . $cid));
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $errorId = bin2hex(random_bytes(4));
    error_log(sprintf('[consultation:%s] %s in %s:%d', $errorId, $e->getMessage(), $e->getFile(), $e->getLine()));
    http_response_code(500);
    exit('Konsultasi gagal disimpan. Silakan coba lagi. Kode error: ' . $errorId);
}
