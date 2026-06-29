<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../includes/functions.php';
require_login();

$selected = $_POST['symptom'] ?? [];
$variableIds = $pdo->query('SELECT id FROM variables ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
$validPairs = [];
foreach($variableIds as $variableId){
    if(empty($selected[$variableId])) die('Seluruh variabel pada laporan wajib dipilih.');
    $stmt = $pdo->prepare('SELECT id FROM symptoms WHERE id=? AND variable_id=?');
    $stmt->execute([(int)$selected[$variableId], (int)$variableId]);
    $symptomId = $stmt->fetchColumn();
    if(!$symptomId) die('Gejala tidak valid untuk variabel laporan.');
    $validPairs[(int)$variableId] = (int)$symptomId;
}

$fc = forward_chaining($pdo, array_values($validPairs));
$pdo->prepare('INSERT INTO consultations(user_id,location,notes) VALUES(?,?,?)')->execute([current_user()['id'],$_POST['location'],$_POST['notes']??'']);
$cid=$pdo->lastInsertId();
foreach($validPairs as $vid=>$sid)$pdo->prepare('INSERT INTO consultation_details(consultation_id,variable_id,symptom_id) VALUES(?,?,?)')->execute([$cid,$vid,$sid]);
$res=$fc['result'];
$pdo->prepare('INSERT INTO diagnosis_results(consultation_id,rule_id,diagnosis,working_memory,active_rules,failed_rules,inference_trace,explanation,recommendation) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$cid,$res['id'],$res['diagnosis'],json_encode($fc['working_memory']),json_encode(array_column($fc['active_rules'],'code')),json_encode(array_column($fc['failed_rules'],'code')),json_encode($fc['trace']),$res['explanation'],$res['recommendation']]);
log_activity($pdo,'consultation','Konsultasi #'.$cid);
redirect('/Sistem-Pakar-Potensi-Banjir/hasil/index.php?id='.$cid);
