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
function flood_level_index($diagnosis){
    return ['Rendah'=>0,'Sedang'=>1,'Tinggi'=>2,'Sangat Tinggi'=>3][$diagnosis] ?? 0;
}
function flood_level_name($index){
    $levels=['Rendah','Sedang','Tinggi','Sangat Tinggi'];
    return $levels[max(0,min(3,(int)$index))];
}
function symptom_level($code){
    return (int)preg_replace('/\D+/', '', (string)$code);
}
function forward_chaining($pdo, array $selectedSymptomIds){
    $wm = array_values(array_unique(array_map('intval', $selectedSymptomIds)));
    sort($wm);

    $placeholders = $wm ? implode(',', array_fill(0, count($wm), '?')) : '0';
    $stmt = $pdo->prepare("SELECT s.id, s.code, s.name, v.code variable_code FROM symptoms s JOIN variables v ON v.id=s.variable_id WHERE s.id IN ($placeholders)");
    $stmt->execute($wm);
    $facts = [];
    foreach($stmt->fetchAll() as $fact){
        $facts[$fact['variable_code']] = $fact;
    }

    $rules=$pdo->query('SELECT * FROM rules WHERE is_active=1 ORDER BY priority ASC,id ASC')->fetchAll();
    $conditions=[];
    if($rules){
        $ids=array_column($rules,'id');
        $in=implode(',', array_fill(0, count($ids), '?'));
        $stmt=$pdo->prepare("SELECT rc.rule_id,s.id symptom_id,s.code,s.name,v.code variable_code FROM rule_conditions rc JOIN symptoms s ON s.id=rc.symptom_id JOIN variables v ON v.id=s.variable_id WHERE rc.rule_id IN ($in) ORDER BY rc.id");
        $stmt->execute($ids);
        foreach($stmt->fetchAll() as $cond){ $conditions[$cond['rule_id']][]=$cond; }
    }

    $active=[]; $failed=[]; $trace=[]; $baseRule=null; $usedRules=[];
    $coreScore = 0;
    foreach(['CH','DH','KD'] as $code){
        $coreScore += isset($facts[$code]) ? symptom_level($facts[$code]['code']) : 1;
    }

    foreach($rules as $rule){
        if(($rule['rule_type'] ?? 'base') !== 'base') continue;
        $min = $rule['min_score'] === null ? null : (int)$rule['min_score'];
        $max = $rule['max_score'] === null ? null : (int)$rule['max_score'];
        $ok = $min !== null && $max !== null && $coreScore >= $min && $coreScore <= $max;
        $trace[]=['rule'=>$rule,'conditions'=>$conditions[$rule['id']]??[],'matched'=>$ok,'missing'=>[],'memory'=>$wm,'note'=>'Evaluasi rule dasar CH×DH×KD dengan skor inti '.$coreScore];
        if($ok){ $active[]=$rule; if(!$baseRule){ $baseRule=$rule; $usedRules[]=$rule; } }
        else { $failed[]=$rule; }
    }

    if(!$baseRule){
        $baseDiagnosis = $coreScore <= 4 ? 'Rendah' : ($coreScore <= 7 ? 'Sedang' : ($coreScore <= 9 ? 'Tinggi' : 'Sangat Tinggi'));
        $baseRule=['id'=>null,'code'=>'R-DEFAULT','diagnosis'=>$baseDiagnosis,'explanation'=>'Tidak ada rule dasar spesifik terpenuhi; sistem menggunakan pemetaan konservatif skor CH, DH, dan KD.','recommendation'=>'Pantau informasi cuaca dan kondisi sekitar.'];
    }

    $level = flood_level_index($baseRule['diagnosis']);
    foreach($rules as $rule){
        if(($rule['rule_type'] ?? 'base') !== 'modifier') continue;
        $conds=$conditions[$rule['id']]??[];
        $condIds=array_map(fn($c)=>(int)$c['symptom_id'],$conds);
        $missing=array_values(array_diff($condIds,$wm));
        $ok=empty($missing);
        $before=flood_level_name($level);
        if($ok){
            $active[]=$rule; $usedRules[]=$rule;
            $level=max(0,min(3,$level+(int)$rule['adjustment']));
        } else { $failed[]=$rule; }
        $trace[]=['rule'=>$rule,'conditions'=>$conds,'matched'=>$ok,'missing'=>$missing,'memory'=>$wm,'note'=>'Rule modifikasi '.$before.' → '.flood_level_name($level).' (floor RENDAH, ceiling SANGAT TINGGI)'];
    }

    $finalDiagnosis=flood_level_name($level);
    [$color,$defaultRecommendation]=diagnosis_meta($finalDiagnosis);
    $explanation='Forward chaining memilih '.$baseRule['code'].' sebagai rule dasar, lalu menerapkan '.(count($usedRules)-1) .' rule modifikasi aktif dengan mekanisme floor-ceiling.';
    $recommendation=$baseRule['recommendation'] ?? $defaultRecommendation;
    if($finalDiagnosis !== ($baseRule['diagnosis'] ?? $finalDiagnosis)) $recommendation=$defaultRecommendation;
    $result=['id'=>$baseRule['id'],'code'=>implode(' + ', array_map(fn($r)=>$r['code'],$usedRules)),'diagnosis'=>$finalDiagnosis,'explanation'=>$explanation,'recommendation'=>$recommendation,'base_rule'=>$baseRule,'used_rules'=>$usedRules];
    return ['working_memory'=>$wm,'facts'=>$facts,'active_rules'=>$active,'failed_rules'=>$failed,'trace'=>$trace,'result'=>$result];
}
