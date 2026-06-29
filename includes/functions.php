<?php
session_start();
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function redirect($url){ header("Location: $url"); exit; }
function current_user(){ return $_SESSION['user'] ?? null; }
function require_login(){ if(!current_user()) redirect('/Sistem-Pakar-Potensi-Banjir/login.php'); }
function require_admin(){ require_login(); if((current_user()['role_name'] ?? '') !== 'admin') redirect('/Sistem-Pakar-Potensi-Banjir/konsultasi/index.php'); }
function log_activity($pdo,$action,$desc=''){
    if(!current_user()) return;
    $stmt=$pdo->prepare('INSERT INTO activity_logs(user_id,action,description,created_at) VALUES(?,?,?,NOW())');
    $stmt->execute([current_user()['id'],$action,$desc]);
}
function diagnosis_meta($status){
    return [
        'Rendah'=>['success','Potensi banjir rendah. Tetap pantau lingkungan dan informasi cuaca.'],
        'Sedang'=>['warning','Potensi banjir sedang. Bersihkan drainase dan siapkan rencana evakuasi.'],
        'Tinggi'=>['orange','Potensi banjir tinggi. Amankan dokumen, pantau sungai, dan waspada evakuasi.'],
        'Sangat Tinggi'=>['danger','Potensi banjir sangat tinggi. Segera koordinasi dengan petugas dan bersiap evakuasi.'],
    ][$status] ?? ['secondary','Tidak ada rekomendasi.'];
}
function forward_chaining($pdo, array $selectedSymptomIds){
    $wm = array_map('intval', $selectedSymptomIds);
    sort($wm);
    $rules=$pdo->query('SELECT * FROM rules WHERE is_active=1 ORDER BY priority ASC,id ASC')->fetchAll();
    $active=[]; $failed=[]; $trace=[]; $fired=null;
    foreach($rules as $rule){
        $stmt=$pdo->prepare('SELECT symptom_id FROM rule_conditions WHERE rule_id=? ORDER BY id');
        $stmt->execute([$rule['id']]);
        $conds=array_map('intval', array_column($stmt->fetchAll(),'symptom_id'));
        $missing=array_values(array_diff($conds,$wm));
        $ok=empty($missing);
        $trace[]=['rule'=>$rule,'conditions'=>$conds,'matched'=>$ok,'missing'=>$missing,'memory'=>$wm];
        if($ok){ $active[]=$rule; if(!$fired) $fired=$rule; }
        else { $failed[]=$rule; }
    }
    if(!$fired){
        $fired=['id'=>null,'code'=>'R-DEFAULT','diagnosis'=>'Rendah','explanation'=>'Tidak ada rule spesifik terpenuhi; sistem mengembalikan potensi terendah.','recommendation'=>'Pantau informasi cuaca dan kondisi sekitar.'];
    }
    return ['working_memory'=>$wm,'active_rules'=>$active,'failed_rules'=>$failed,'trace'=>$trace,'result'=>$fired];
}
