<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/validators.php';

/* ============================================================
   Session, cookie, autentikasi, otorisasi, dan keamanan dasar.
   ============================================================ */

function send_security_headers(): void
{
    if (PHP_SAPI === 'cli' || headers_sent()) {
        return;
    }

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    // Aplikasi masih memakai beberapa handler inline (onclick), sehingga
    // 'unsafe-inline' diperlukan. Skrip pihak ketiga tetap dilarang.
    header(
        "Content-Security-Policy: default-src 'self'; "
        . "img-src 'self' data:; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
        . "script-src 'self' 'unsafe-inline'; "
        . "connect-src 'self'; font-src 'self' data: https://fonts.gstatic.com; "
        . "frame-ancestors 'self'; base-uri 'self'; form-action 'self'"
    );

    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

function session_cookie_path(): string
{
    return BASE_URL !== '' ? BASE_URL . '/' : '/';
}

/**
 * Opsi untuk session_set_cookie_params(): memakai `lifetime`, bukan `expires`.
 */
function session_cookie_options(): array
{
    return [
        'lifetime' => 0,
        'path'     => session_cookie_path(),
        'domain'   => '',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

/**
 * Opsi untuk setcookie(): memakai `expires` (timestamp absolut).
 */
function cookie_options(int $expires = 0): array
{
    return [
        'expires'  => $expires,
        'path'     => session_cookie_path(),
        'domain'   => '',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function clear_auth_session(string $message = ''): void
{
    $_SESSION = [];

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    if ($message !== '') {
        $_SESSION['flash'] = ['type' => 'error', 'message' => $message];
    }
}

function start_secure_session(): void
{
    if (PHP_SAPI === 'cli' || session_status() !== PHP_SESSION_NONE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', (string) SESSION_ABSOLUTE_TIMEOUT);

    session_name(SESSION_NAME);
    session_set_cookie_params(session_cookie_options());
    session_start();

    $now = time();

    // Batas waktu idle dan batas waktu absolut.
    if (!empty($_SESSION['user_id'])) {
        $idleSince = (int) ($_SESSION['last_activity'] ?? $now);
        $loginAt   = (int) ($_SESSION['login_time'] ?? $now);

        if (($now - $idleSince) > SESSION_IDLE_TIMEOUT) {
            clear_auth_session('Sesi berakhir karena tidak ada aktivitas. Silakan masuk kembali.');
        } elseif (($now - $loginAt) > SESSION_ABSOLUTE_TIMEOUT) {
            clear_auth_session('Sesi berakhir. Silakan masuk kembali.');
        } else {
            // Deteksi perubahan user-agent (indikasi session hijacking).
            $uaHash = hash('sha256', (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
            if (!empty($_SESSION['ua_hash']) && !hash_equals($_SESSION['ua_hash'], $uaHash)) {
                clear_auth_session('Sesi tidak valid. Silakan masuk kembali.');
            }
        }
    }

    if (!empty($_SESSION['user_id'])) {
        $_SESSION['last_activity'] = $now;

        $lastRegenerate = (int) ($_SESSION['last_regenerate'] ?? $now);
        if (($now - $lastRegenerate) > SESSION_REGENERATE_INTERVAL) {
            session_regenerate_id(true);
            $_SESSION['last_regenerate'] = $now;
        }

        // Perbarui cookie sesi agar masa aktif mengikuti aktivitas.
        if (!headers_sent()) {
            setcookie(session_name(), session_id(), cookie_options());
        }
    }

    // Login otomatis lewat cookie "ingat saya".
    if (empty($_SESSION['user_id']) && !empty($_COOKIE[REMEMBER_COOKIE])) {
        attempt_remember_login();
    }
}

/* ---------- identitas pengguna ---------- */

function current_user(bool $refresh = false): ?array
{
    static $cached = null;
    static $loaded = false;

    if ($refresh) {
        $cached = null;
        $loaded = false;
    }

    if ($loaded) {
        return $cached;
    }

    $loaded = true;

    $userId = (int) ($_SESSION['user_id'] ?? 0);
    if ($userId <= 0) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT id, username, nama, email, no_wa, password, role, aktif, last_login_at, created_at
         FROM users WHERE id = ? LIMIT 1'
    );
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || (int) $user['aktif'] !== 1) {
        clear_auth_session('Akun tidak aktif atau tidak ditemukan.');
        return null;
    }

    // Sinkronkan peran terbaru dari database.
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['nama'];

    $cached = $user;

    return $cached;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_staff(): bool
{
    $user = current_user();

    return $user !== null && is_staff_role((string) $user['role']);
}

function is_admin(): bool
{
    $user = current_user();

    return $user !== null && $user['role'] === 'admin';
}

function user_can(string $permission): bool
{
    $user = current_user();

    return $user !== null && role_can((string) $user['role'], $permission);
}

function user_name(): string
{
    return current_user()['nama'] ?? 'Tamu';
}

function user_role(): string
{
    return current_user()['role'] ?? 'tamu';
}

/* ---------- guard halaman ---------- */

function forbidden_page(string $message =
    'Anda tidak memiliki izin untuk membuka halaman ini.'): never
{
    http_response_code(403);

    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }

    $isStaffArea = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/');
    $backUrl = $isStaffArea ? url('admin/index.php') : url('index.php');

    exit(
        '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>403 - Akses Ditolak</title>'
        . '<style>body{font-family:system-ui,sans-serif;background:#f6f6f2;color:#26332c;'
        . 'display:grid;place-items:center;min-height:100vh;margin:0}'
        . '.box{background:#fff;padding:40px 44px;border-radius:16px;max-width:520px;'
        . 'box-shadow:0 18px 40px rgba(0,0,0,.08);text-align:center}'
        . 'h1{margin:0 0 10px;font-size:3rem;color:#0f6b4f}'
        . 'a{display:inline-block;margin-top:16px;background:#0f6b4f;color:#fff;'
        . 'padding:10px 18px;border-radius:8px;text-decoration:none}</style></head><body>'
        . '<div class="box"><h1>403</h1><p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') .
            '</p>'
        . '<a href="' . htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') . '">Kembali</a></div>'
        . '</body></html>'
    );
}

function remember_intended_url(): void
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if ($uri !== '' && !str_contains($uri, "\n") && !str_contains($uri, "\r")) {
        $_SESSION['intended_url'] = $uri;
    }
}

function take_intended_url(string $fallback): string
{
    $intended = (string) ($_SESSION['intended_url'] ?? '');
    unset($_SESSION['intended_url']);

    if ($intended === '' || !str_starts_with($intended, '/') || str_starts_with($intended, '//')) {
        return $fallback;
    }

    // Hanya izinkan path di dalam folder aplikasi.
    $base = BASE_URL === '' ? '/' : BASE_URL . '/';
    if (!str_starts_with($intended, $base)) {
        return $fallback;
    }

    return $intended;
}

function require_login(): void
{
    if (!is_logged_in()) {
        remember_intended_url();
        set_flash('error', 'Silakan masuk terlebih dahulu.');
        redirect(url('masuk.php'));
    }
}

function require_permission(string $permission): void
{
    $isStaffArea = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/');

    if (!is_logged_in()) {
        remember_intended_url();
        set_flash('error', 'Silakan masuk terlebih dahulu.');
        redirect(url($isStaffArea ? 'admin/login.php' : 'masuk.php'));
    }

    if (!user_can($permission)) {
        log_activity((int) current_user()['id'], 'akses.ditolak', $permission . ' @ ' .
            ($_SERVER['REQUEST_URI'] ?? ''));
        forbidden_page();
    }
}

function require_staff(): void
{
    if (!is_logged_in()) {
        remember_intended_url();
        set_flash('error', 'Silakan masuk ke panel admin.');
        redirect(url('admin/login.php'));
    }

    if (!is_staff()) {
        forbidden_page('Halaman ini hanya untuk staf toko.');
    }
}

function require_admin(): void
{
    if (!is_logged_in()) {
        remember_intended_url();
        set_flash('error', 'Silakan masuk ke panel admin.');
        redirect(url('admin/login.php'));
    }

    if (!is_admin()) {
        log_activity((int) current_user()['id'], 'akses.ditolak', 'admin-only @ ' .
            ($_SERVER['REQUEST_URI'] ?? ''));
        forbidden_page('Halaman ini hanya untuk administrator.');
    }
}

/* ---------- CSRF ---------- */

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
    $token = (string) ($_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));

    if (!csrf_token_matches((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
        http_response_code(419);
        exit('Sesi kedaluwarsa atau token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
    }
}

/* ---------- cookie "ingat saya" ---------- */

function clear_remember_cookie(): void
{
    if (!headers_sent()) {
        setcookie(REMEMBER_COOKIE, '', cookie_options(time() - 3600));
    }
    unset($_COOKIE[REMEMBER_COOKIE]);
}

function issue_remember_token(int $userId): void
{
    $token = make_remember_token();
    $expires = time() + (REMEMBER_DAYS * 86400);

    $pdo = db();
    // Bersihkan token kedaluwarsa dan batasi jumlah token per pengguna.
    $pdo->prepare('DELETE FROM remember_tokens WHERE expires_at < NOW()')->execute();
    $pdo->prepare(
        'DELETE FROM remember_tokens WHERE user_id = ? AND id NOT IN (
            SELECT id FROM (
                SELECT id FROM remember_tokens WHERE user_id = ? ORDER BY created_at DESC LIMIT 4
            ) AS keep
        )'
    )->execute([$userId, $userId]);

    $stmt = $pdo->prepare(
        'INSERT INTO remember_tokens (selector, validator_hash, user_id, expires_at, user_agent)
         VALUES (?, ?, ?, FROM_UNIXTIME(?), ?)'
    );
    $stmt->execute([
        $token['selector'],
        $token['hash'],
        $userId,
        $expires,
        mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
    ]);

    if (!headers_sent()) {
        setcookie(REMEMBER_COOKIE, $token['cookie'], cookie_options($expires));
    }
    $_COOKIE[REMEMBER_COOKIE] = $token['cookie'];
}

function revoke_remember_cookie(): void
{
    $value = (string) ($_COOKIE[REMEMBER_COOKIE] ?? '');
    $parsed = parse_remember_cookie($value);

    if ($parsed !== null) {
        try {
            db()->prepare('DELETE FROM remember_tokens WHERE selector = ?')->execute([$parsed['selector']]);
        } catch (Throwable $e) {
            error_log('[remember] ' . $e->getMessage());
        }
    }

    clear_remember_cookie();
}

function attempt_remember_login(): void
{
    $parsed = parse_remember_cookie((string) ($_COOKIE[REMEMBER_COOKIE] ?? ''));
    if ($parsed === null) {
        clear_remember_cookie();
        return;
    }

    try {
        $stmt = db()->prepare(
            'SELECT rt.id AS token_id, rt.user_id, rt.validator_hash, rt.expires_at,
                    u.id, u.username, u.nama, u.email, u.role, u.aktif
             FROM remember_tokens rt
             JOIN users u ON u.id = rt.user_id
             WHERE rt.selector = ? LIMIT 1'
        );
        $stmt->execute([$parsed['selector']]);
        $row = $stmt->fetch();
    } catch (Throwable $e) {
        error_log('[remember] ' . $e->getMessage());
        return;
    }

    if (!$row) {
        clear_remember_cookie();
        return;
    }

    if (strtotime((string) $row['expires_at']) < time() || (int) $row['aktif'] !== 1) {
        db()->prepare('DELETE FROM remember_tokens WHERE id = ?')->execute([(int)
            $row['token_id']]);
        clear_remember_cookie();
        return;
    }

    if (!verify_remember_validator($parsed['validator'], (string) $row['validator_hash'])) {
        // Token tidak cocok: kemungkinan dicuri. Hapus semua token pengguna.
        db()->prepare('DELETE FROM remember_tokens WHERE user_id = ?')->execute([(int)
            $row['user_id']]);
        clear_remember_cookie();
        log_activity((int) $row['user_id'], 'cookie.tidak_valid', 'Token ingat saya tidak cocok.');
        return;
    }

    db()->prepare('DELETE FROM remember_tokens WHERE id = ?')->execute([(int) $row['token_id']]);
    login_user($row, true, false);
    log_activity((int) $row['user_id'], 'login.ingat_saya', 'Masuk otomatis lewat cookie.');
}

/* ---------- rate limit login ---------- */

function client_ip(): string
{
    $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

    // Cloudflare Quick Tunnel / reverse proxy: klien terlihat sebagai 127.0.0.1.
    // Gunakan header dari proxy bila koneksi berasal dari localhost.
    $ip = '';
    if ($remote === '127.0.0.1' || $remote === '::1') {
        $ip = trim((string) ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''));
        if ($ip === '' && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = trim(explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }
    }

    if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
        $ip = $remote;
    }

    return mb_substr($ip, 0, 45);
}

function login_rate_limited(string $username): bool
{
    $cutoff = date('Y-m-d H:i:s', time() - (LOGIN_WINDOW_MINUTES * 60));

    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM login_attempts
         WHERE sukses = 0
           AND created_at > ?
           AND (username = ? OR ip = ?)'
    );
    $stmt->execute([$cutoff, $username, client_ip()]);

    return (int) $stmt->fetchColumn() >= LOGIN_MAX_ATTEMPTS;
}

function record_login_attempt(string $username, bool $success): void
{
    try {
        db()->prepare('INSERT INTO login_attempts (username, ip, sukses) VALUES (?, ?, ?)')
            ->execute([mb_substr($username, 0, 60), client_ip(), $success ? 1 : 0]);

        // Pembersihan berkala (1% permintaan).
        if (random_int(1, 100) === 1) {
            db()->exec('DELETE FROM login_attempts WHERE created_at < (NOW() - INTERVAL 7 DAY)');
        }
    } catch (Throwable $e) {
        error_log('[login_attempts] ' . $e->getMessage());
    }
}

/**
 * @return array{ok:bool,error:?string,user:?array}
 */
function attempt_login(string $username, string $password, bool $staffOnly = false): array
{
    $username = trim($username);

    if ($username === '' || $password === '') {
        return ['ok' => false, 'error' => 'Username dan password wajib diisi.', 'user' => null];
    }

    if (login_rate_limited($username)) {
        return [
            'ok'    => false,
            'error' => 'Terlalu banyak percobaan gagal. Coba lagi dalam ' . LOGIN_WINDOW_MINUTES
                . ' menit.',
            'user'  => null,
        ];
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE username = ? AND aktif = 1 LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    $valid = $user && password_verify($password, (string) $user['password']);
    record_login_attempt($username, (bool) $valid);

    if (!$valid) {
        usleep(250_000);
        log_activity(null, 'login.gagal', 'Username: ' . mb_substr($username, 0, 60) . ' / IP: '
            . client_ip());

        return ['ok' => false, 'error' => 'Username atau password salah.', 'user' => null];
    }

    if ($staffOnly && !is_staff_role((string) $user['role'])) {
        log_activity((int) $user['id'], 'login.ditolak',
            'Akun pelanggan mencoba masuk panel admin.');

        return ['ok' => false, 'error' => 'Username atau password salah.', 'user' => null];
    }

    if (password_needs_rehash((string) $user['password'], PASSWORD_DEFAULT)) {
        db()->prepare('UPDATE users SET password = ? WHERE id = ?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), (int) $user['id']]);
    }

    return ['ok' => true, 'error' => null, 'user' => $user];
}

/* ---------- sesi login ---------- */

function login_user(array $user, bool $remember = false, bool $log = true): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    $_SESSION['user_id']         = (int) $user['id'];
    $_SESSION['user_name']       = $user['nama'];
    $_SESSION['user_role']       = $user['role'];
    $_SESSION['login_time']      = time();
    $_SESSION['last_activity']   = time();
    $_SESSION['last_regenerate'] = time();
    $_SESSION['ua_hash']         = hash('sha256', (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    unset($_SESSION['csrf_token']);

    db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([(int)
        $user['id']]);

    if ($remember) {
        issue_remember_token((int) $user['id']);
    }

    if ($log) {
        log_activity((int) $user['id'], 'login', 'Masuk sebagai ' . role_label((string)
            $user['role']));
    }

    if ($user['role'] !== 'pelanggan') {
        unset($_SESSION['intended_url']);
    }
}

function logout_user(): void
{
    $user = current_user();
    if ($user !== null) {
        log_activity((int) $user['id'], 'logout', 'Keluar dari aplikasi.');
    }

    revoke_remember_cookie();

    $_SESSION = [];

    // Buang seluruh data sesi, lalu pakai ID sesi baru agar ID lama tidak valid.
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/* ---------- audit log ---------- */

function log_activity(?int $userId, string $aksi, string $detail = ''): void
{
    try {
        db()->prepare(
            'INSERT INTO activity_log (user_id, aksi, detail, ip, user_agent)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([
            $userId,
            mb_substr($aksi, 0, 60),
            mb_substr($detail, 0, 500),
            client_ip(),
            mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);
    } catch (Throwable $e) {
        error_log('[activity_log] ' . $e->getMessage());
    }
}

/* ---------- cookie preferensi & persetujuan ---------- */

function theme_preference(): string
{
    $theme = (string) ($_COOKIE[THEME_COOKIE] ?? '');

    return $theme === 'dark' ? 'dark' : 'light';
}

function has_cookie_consent(): bool
{
    return ($_COOKIE[CONSENT_COOKIE] ?? '') === '1';
}

/* ---------- bootstrap ---------- */

if (PHP_SAPI !== 'cli') {
    send_security_headers();
    start_secure_session();
}
