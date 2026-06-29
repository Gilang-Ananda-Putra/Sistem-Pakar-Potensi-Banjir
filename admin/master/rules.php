<?php require_once __DIR__.'/../../config/database.php'; require_once __DIR__.'/../../includes/functions.php'; require_admin();
function rule_if_text($r){
    $conds = trim((string)($r['conds'] ?? ''));
    if(($r['rule_type'] ?? 'base') === 'base'){
        $min = $r['min_score']; $max = $r['max_score'];
        if($min !== null && $max !== null) return 'Skor inti CH + DH + KD '.($min == $max ? '= '.$min : $min.'-'.$max);
    }
    return $conds ?: '-';
}
if($_SERVER['REQUEST_METHOD']==='POST'){ verify_csrf();
    if(isset($_POST['delete'])){
        $pdo->prepare('DELETE FROM rules WHERE id=?')->execute([$_POST['id']]);
    } else {
        $ruleType = $_POST['rule_type'] === 'modifier' ? 'modifier' : 'base';
        $minScore = $ruleType === 'base' && $_POST['min_score'] !== '' ? (int)$_POST['min_score'] : null;
        $maxScore = $ruleType === 'base' && $_POST['max_score'] !== '' ? (int)$_POST['max_score'] : null;
        $adjustment = $ruleType === 'modifier' ? (int)($_POST['adjustment'] ?? 0) : 0;
        $pdo->prepare('INSERT INTO rules(code,diagnosis,priority,rule_type,min_score,max_score,adjustment,explanation,recommendation,reference_id) VALUES(?,?,?,?,?,?,?,?,?,1)')->execute([$_POST['code'],$_POST['diagnosis'],$_POST['priority'],$ruleType,$minScore,$maxScore,$adjustment,$_POST['explanation'],$_POST['recommendation']]);
        $rid=$pdo->lastInsertId();
        foreach($_POST['symptoms']??[] as $sid)$pdo->prepare('INSERT INTO rule_conditions(rule_id,symptom_id) VALUES(?,?)')->execute([$rid,$sid]);
    }
}
$sym=$pdo->query('SELECT * FROM symptoms ORDER BY code')->fetchAll();
$rows=$pdo->query('SELECT r.*,GROUP_CONCAT(s.code ORDER BY s.code SEPARATOR " AND ") conds FROM rules r LEFT JOIN rule_conditions rc ON rc.rule_id=r.id LEFT JOIN symptoms s ON s.id=rc.symptom_id GROUP BY r.id ORDER BY priority')->fetchAll(); require_once __DIR__.'/../../includes/header.php'; ?><div class="container-fluid px-3 px-lg-4"><div class="row g-3"><?php include __DIR__.'/../../includes/admin_sidebar.php'; ?><section class="col-lg-9 col-xl-10"><h2 class="section-title mb-3">Master Rule Base</h2><form class="card p-3 mb-3" method="post"><?=csrf_field()?><div class="row g-2"><div class="col"><input name="code" class="form-control" placeholder="Rxx"></div><div class="col"><select name="diagnosis" class="form-select"><option>Rendah</option><option>Sedang</option><option>Tinggi</option><option>Sangat Tinggi</option></select></div><div class="col"><input name="priority" type="number" class="form-control" placeholder="Prioritas"></div></div><div class="row g-2 mt-2"><div class="col"><select name="rule_type" class="form-select"><option value="base">base</option><option value="modifier">modifier</option></select></div><div class="col"><input name="min_score" type="number" class="form-control" placeholder="Min skor inti"></div><div class="col"><input name="max_score" type="number" class="form-control" placeholder="Max skor inti"></div><div class="col"><input name="adjustment" type="number" class="form-control" placeholder="Adjustment"></div></div><div class="mt-2"><label>Rule Builder IF</label><select multiple name="symptoms[]" class="form-select"><?php foreach($sym as $s): ?><option value="<?=$s['id']?>"><?=$s['code']?> - <?=$s['name']?></option><?php endforeach; ?></select></div><input class="form-control mt-2" name="explanation" placeholder="Alasan"><input class="form-control mt-2" name="recommendation" placeholder="Rekomendasi"><button class="btn btn-primary mt-2">Simpan</button></form><div class="table-wrap"><table class="table table-hover"><tr><th>Rule</th><th>Jenis</th><th>IF</th><th>THEN</th><th>Prioritas</th><th>Adjustment</th><th>Referensi</th><th>Aksi</th></tr><?php foreach($rows as $r): ?><tr><td><?=e($r['code'])?></td><td><?=e($r['rule_type'])?></td><td><?=e(rule_if_text($r))?></td><td><?=e($r['diagnosis'])?></td><td><?=$r['priority']?></td><td><?=e($r['adjustment'])?></td><td>Laporan Akademik</td><td><form method="post"><?=csrf_field()?><input type="hidden" name="id" value="<?=$r['id']?>"><button name="delete" class="btn btn-sm btn-danger">Hapus</button></form></td></tr><?php endforeach; ?></table></div></section></div></div><?php require_once __DIR__.'/../../includes/footer.php'; ?>
