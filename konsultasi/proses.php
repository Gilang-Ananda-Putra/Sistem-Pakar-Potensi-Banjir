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

if ($location === '' || mb_strlen($location) > 150) {
    exit('Lokasi wajib diisi dan maksimal 150 karakter.');
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

    $pdo->prepare('INSERT INTO consultations(user_id,location,notes) VALUES(?,?,?)')->execute([current_user()['id'], $location, $notes]);
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
        json_encode($fc['working_memory'], JSON_UNESCAPED_UNICODE),
        json_encode(array_column($fc['active_rules'], 'code'), JSON_UNESCAPED_UNICODE),
        json_encode(array_column($fc['failed_rules'], 'code'), JSON_UNESCAPED_UNICODE),
        json_encode($fc['trace'], JSON_UNESCAPED_UNICODE),
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
    http_response_code(500);
    exit('Konsultasi gagal disimpan. Silakan coba lagi.');
}
