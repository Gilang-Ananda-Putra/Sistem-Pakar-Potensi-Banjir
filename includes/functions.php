<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

const APP_BASE_PATH = '/Sistem-Pakar-Potensi-Banjir';
const FLOOD_LEVELS = ['Rendah', 'Sedang', 'Tinggi', 'Sangat Tinggi'];

function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function app_url(string $path = ''): string
{
    return APP_BASE_PATH . '/' . ltrim($path, '/');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        redirect(app_url('login.php'));
    }
}

function require_admin(): void
{
    require_login();
    if ((current_user()['role_name'] ?? '') !== 'admin') {
        redirect(app_url('konsultasi/index.php'));
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Sesi form tidak valid. Silakan muat ulang halaman.');
    }
}

function ensure_owner_or_admin(array $row, string $userIdColumn = 'user_id'): void
{
    $user = current_user();
    if (($user['role_name'] ?? '') === 'admin') {
        return;
    }
    if ((int) ($row[$userIdColumn] ?? 0) !== (int) ($user['id'] ?? 0)) {
        http_response_code(403);
        exit('Anda tidak memiliki akses ke data ini.');
    }
}

function log_activity(PDO $pdo, string $action, string $desc = ''): void
{
    if (!current_user()) {
        return;
    }
    $stmt = $pdo->prepare('INSERT INTO activity_logs(user_id,action,description,created_at) VALUES(?,?,?,NOW())');
    $stmt->execute([current_user()['id'], $action, $desc]);
}

function diagnosis_meta(string $status): array
{
    return [
        'Rendah' => ['success', 'Potensi banjir rendah. Tetap pantau lingkungan dan informasi cuaca.'],
        'Sedang' => ['warning', 'Potensi banjir sedang. Bersihkan drainase dan siapkan rencana evakuasi.'],
        'Tinggi' => ['orange', 'Potensi banjir tinggi. Amankan dokumen, pantau sungai, dan waspada evakuasi.'],
        'Sangat Tinggi' => ['danger', 'Potensi banjir sangat tinggi. Segera koordinasi dengan petugas dan bersiap evakuasi.'],
    ][$status] ?? ['secondary', 'Tidak ada rekomendasi.'];
}

function flood_level_index(string $diagnosis): int
{
    $index = array_search($diagnosis, FLOOD_LEVELS, true);
    return $index === false ? 0 : (int) $index;
}

function flood_level_name(int $index): string
{
    return FLOOD_LEVELS[max(0, min(3, $index))];
}

function symptom_level(string $code): int
{
    return (int) preg_replace('/\D+/', '', $code);
}

function placeholders(int $count): string
{
    return implode(',', array_fill(0, $count, '?'));
}

function forward_chaining(PDO $pdo, array $selectedSymptomIds): array
{
    $wm = array_values(array_unique(array_map('intval', $selectedSymptomIds)));
    sort($wm);

    $facts = [];
    if ($wm) {
        $stmt = $pdo->prepare('SELECT s.id, s.code, s.name, v.code variable_code FROM symptoms s JOIN variables v ON v.id=s.variable_id WHERE s.id IN (' . placeholders(count($wm)) . ')');
        $stmt->execute($wm);
        foreach ($stmt->fetchAll() as $fact) {
            $facts[$fact['variable_code']] = $fact;
        }
    }

    $rules = $pdo->query('SELECT * FROM rules WHERE is_active=1 ORDER BY priority ASC,id ASC')->fetchAll();
    $conditions = [];
    if ($rules) {
        $ids = array_column($rules, 'id');
        $stmt = $pdo->prepare('SELECT rc.rule_id,s.id symptom_id,s.code,s.name,v.code variable_code FROM rule_conditions rc JOIN symptoms s ON s.id=rc.symptom_id JOIN variables v ON v.id=s.variable_id WHERE rc.rule_id IN (' . placeholders(count($ids)) . ') ORDER BY rc.id');
        $stmt->execute($ids);
        foreach ($stmt->fetchAll() as $cond) {
            $conditions[$cond['rule_id']][] = $cond;
        }
    }

    $active = [];
    $failed = [];
    $trace = [];
    $baseRule = null;
    $usedRules = [];
    $coreScore = 0;

    foreach (['CH', 'DH', 'KD'] as $code) {
        $coreScore += isset($facts[$code]) ? symptom_level($facts[$code]['code']) : 1;
    }

    foreach ($rules as $rule) {
        if (($rule['rule_type'] ?? 'base') !== 'base') {
            continue;
        }
        $min = $rule['min_score'] === null ? null : (int) $rule['min_score'];
        $max = $rule['max_score'] === null ? null : (int) $rule['max_score'];
        $ok = $min !== null && $max !== null && $coreScore >= $min && $coreScore <= $max;
        $trace[] = ['rule' => $rule, 'conditions' => $conditions[$rule['id']] ?? [], 'matched' => $ok, 'missing' => [], 'memory' => $wm, 'note' => 'Evaluasi rule dasar CH×DH×KD dengan skor inti ' . $coreScore];
        if ($ok) {
            $active[] = $rule;
            if (!$baseRule) {
                $baseRule = $rule;
                $usedRules[] = $rule;
            }
        } else {
            $failed[] = $rule;
        }
    }

    if (!$baseRule) {
        $baseDiagnosis = $coreScore <= 4 ? 'Rendah' : ($coreScore <= 7 ? 'Sedang' : ($coreScore <= 9 ? 'Tinggi' : 'Sangat Tinggi'));
        $baseRule = ['id' => null, 'code' => 'R-DEFAULT', 'diagnosis' => $baseDiagnosis, 'explanation' => 'Tidak ada rule dasar spesifik terpenuhi; sistem menggunakan pemetaan konservatif skor CH, DH, dan KD.', 'recommendation' => 'Pantau informasi cuaca dan kondisi sekitar.'];
    }

    $level = flood_level_index($baseRule['diagnosis']);
    foreach ($rules as $rule) {
        if (($rule['rule_type'] ?? 'base') !== 'modifier') {
            continue;
        }
        $conds = $conditions[$rule['id']] ?? [];
        $condIds = array_map(fn ($c) => (int) $c['symptom_id'], $conds);
        $missing = array_values(array_diff($condIds, $wm));
        $ok = empty($missing);
        $before = flood_level_name($level);
        if ($ok) {
            $active[] = $rule;
            $usedRules[] = $rule;
            $level = max(0, min(3, $level + (int) $rule['adjustment']));
        } else {
            $failed[] = $rule;
        }
        $trace[] = ['rule' => $rule, 'conditions' => $conds, 'matched' => $ok, 'missing' => $missing, 'memory' => $wm, 'note' => 'Rule modifikasi ' . $before . ' → ' . flood_level_name($level) . ' (floor RENDAH, ceiling SANGAT TINGGI)'];
    }

    $finalDiagnosis = flood_level_name($level);
    [, $defaultRecommendation] = diagnosis_meta($finalDiagnosis);
    $modifierCount = max(0, count($usedRules) - 1);
    $explanation = 'Forward chaining memilih ' . $baseRule['code'] . ' sebagai rule dasar, lalu menerapkan ' . $modifierCount . ' rule modifikasi aktif dengan mekanisme floor-ceiling.';
    $recommendation = $finalDiagnosis !== ($baseRule['diagnosis'] ?? $finalDiagnosis) ? $defaultRecommendation : ($baseRule['recommendation'] ?? $defaultRecommendation);

    $result = ['id' => $baseRule['id'], 'code' => implode(' + ', array_map(fn ($r) => $r['code'], $usedRules)), 'diagnosis' => $finalDiagnosis, 'explanation' => $explanation, 'recommendation' => $recommendation, 'base_rule' => $baseRule, 'used_rules' => $usedRules];
    return ['working_memory' => $wm, 'facts' => $facts, 'active_rules' => $active, 'failed_rules' => $failed, 'trace' => $trace, 'result' => $result];
}
