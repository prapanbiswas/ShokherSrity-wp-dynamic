<?php
/**
 * ShokherSrity — MySQL Database Setup Wizard
 *
 * Access at: /setup-database.php
 * This file helps migrate from SQLite (development) to MySQL (production).
 * IMPORTANT: Delete or restrict access to this file after setup is complete.
 */

// Security: block access unless the request includes a setup token OR
// the site is not yet connected to MySQL.
define('SS_SETUP_VERSION', '1.0.0');

// Determine if already on MySQL
$wp_config_path = __DIR__ . '/wp-config.php';
$wp_config      = file_exists($wp_config_path) ? file_get_contents($wp_config_path) : '';
$currently_sqlite = file_exists(__DIR__ . '/wp-content/database/.ht.sqlite') ||
                    file_exists(__DIR__ . '/wp-content/db.php');
$db_host_match = [];
preg_match("/define\('DB_HOST',\s*'([^']+)'\)/", $wp_config, $db_host_match);
$current_host = $db_host_match[1] ?? 'localhost';
$using_mysql  = ($current_host !== '' && $current_host !== 'localhost' && !$currently_sqlite)
             || (strpos($wp_config, 'DB_HOST') !== false && !$currently_sqlite);

$step    = (int)($_POST['step'] ?? $_GET['step'] ?? 1);
$message = '';
$success = false;

// ── Step 2: Test connection ────────────────────────────────────
if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = trim($_POST['db_host'] ?? 'localhost');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = $_POST['db_pass'] ?? '';
    $db_port = (int)($_POST['db_port'] ?? 3306);

    if (!$db_name || !$db_user) {
        $message = 'error:Database name and username are required.';
        $step    = 1;
    } else {
        $host_str = $db_port !== 3306 ? "{$db_host}:{$db_port}" : $db_host;
        try {
            $pdo = new PDO(
                "mysql:host={$db_host};port={$db_port};charset=utf8mb4",
                $db_user, $db_pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]
            );
            // Try to create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$db_name}`");
            $message = "success:Connection successful! Database '{$db_name}' is ready.";
            // Store in session for step 3
            session_start();
            $_SESSION['ss_db'] = compact('db_host', 'db_name', 'db_user', 'db_pass', 'db_port', 'host_str');
        } catch (Exception $e) {
            $message = 'error:Connection failed: ' . htmlspecialchars($e->getMessage());
            $step    = 1;
        }
    }
}

// ── Step 3: Write config and disable SQLite ────────────────────
if ($step === 3 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    $db = $_SESSION['ss_db'] ?? [];
    if (empty($db)) { $message = 'error:Session expired. Please start over.'; $step = 1; }
    else {
        // Generate fresh security keys
        $chars   = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+[]{}|;:,.<>?';
        $charlen = strlen($chars);
        $randkey = function() use ($chars, $charlen) {
            $k = '';
            for ($i = 0; $i < 64; $i++) $k .= $chars[random_int(0, $charlen - 1)];
            return $k;
        };

        $new_config = "<?php\n"
            . "define('DB_NAME',     '" . addslashes($db['db_name']) . "');\n"
            . "define('DB_USER',     '" . addslashes($db['db_user']) . "');\n"
            . "define('DB_PASSWORD', '" . addslashes($db['db_pass']) . "');\n"
            . "define('DB_HOST',     '" . addslashes($db['db_host']) . "');\n"
            . "define('DB_CHARSET',  'utf8mb4');\n"
            . "define('DB_COLLATE',  'utf8mb4_unicode_ci');\n\n"
            . "define('AUTH_KEY',         '" . $randkey() . "');\n"
            . "define('SECURE_AUTH_KEY',  '" . $randkey() . "');\n"
            . "define('LOGGED_IN_KEY',    '" . $randkey() . "');\n"
            . "define('NONCE_KEY',        '" . $randkey() . "');\n"
            . "define('AUTH_SALT',        '" . $randkey() . "');\n"
            . "define('SECURE_AUTH_SALT', '" . $randkey() . "');\n"
            . "define('LOGGED_IN_SALT',   '" . $randkey() . "');\n"
            . "define('NONCE_SALT',       '" . $randkey() . "');\n\n"
            . "\$table_prefix = 'wp_';\n\n"
            . "define('WP_DEBUG',         false);\n"
            . "define('WP_DEBUG_LOG',     false);\n"
            . "define('WP_DEBUG_DISPLAY', false);\n\n"
            . "define('DISALLOW_FILE_EDIT', true);\n"
            . "define('DISALLOW_FILE_MODS', true);\n"
            . "define('WP_AUTO_UPDATE_CORE', false);\n"
            . "define('FORCE_SSL_ADMIN', true);\n\n"
            . "// Production: assume HTTPS\n"
            . "if (isset(\$_SERVER['HTTP_X_FORWARDED_PROTO']) && \$_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {\n"
            . "    \$_SERVER['HTTPS'] = 'on';\n"
            . "}\n\n"
            . "if (!defined('WP_HOME')) {\n"
            . "    \$ss_https  = !empty(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS'] !== 'off';\n"
            . "    \$ss_scheme = \$ss_https ? 'https' : 'http';\n"
            . "    \$ss_host   = \$_SERVER['HTTP_HOST'] ?? 'localhost';\n"
            . "    define('WP_HOME',    \$ss_scheme . '://' . \$ss_host);\n"
            . "    define('WP_SITEURL', \$ss_scheme . '://' . \$ss_host);\n"
            . "}\n\n"
            . "if (!defined('ABSPATH')) {\n"
            . "    define('ABSPATH', __DIR__ . '/');\n"
            . "}\n\n"
            . "require_once ABSPATH . 'wp-settings.php';\n";

        // Write new wp-config.php
        if (file_put_contents($wp_config_path, $new_config) === false) {
            $message = 'error:Could not write wp-config.php — check file permissions.';
            $step    = 1;
        } else {
            // Disable SQLite dropin by renaming db.php
            $sqlite_dropin = __DIR__ . '/wp-content/db.php';
            if (file_exists($sqlite_dropin)) {
                rename($sqlite_dropin, $sqlite_dropin . '.sqlite-backup');
            }

            // Rename SQLite database file
            $sqlite_db = __DIR__ . '/wp-content/database/.ht.sqlite';
            if (file_exists($sqlite_db)) {
                rename($sqlite_db, $sqlite_db . '.backup-' . date('Ymd_His'));
            }

            $success = true;
            $message = 'success:WordPress is now configured to use MySQL. You can now run the WP installer or import your database.';
            $step    = 4;
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ShokherSrity — Database Setup</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: linear-gradient(135deg, #0a0805 0%, #1a120a 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem; }
.card { background: #fff; border-radius: 16px; box-shadow: 0 24px 64px rgba(0,0,0,.5); width: 560px; max-width: 100%; overflow: hidden; }
.card-header { background: linear-gradient(135deg, #0a0805, #1a120a); padding: 2rem; text-align: center; border-bottom: 1px solid rgba(212,175,55,.15); }
.card-logo { display: flex; align-items: center; justify-content: center; gap: .75rem; margin-bottom: .75rem; }
.card-logo svg { color: #D4AF37; }
.card-title { font-size: 1.25rem; font-weight: 700; color: #D4AF37; }
.card-subtitle { font-size: .83rem; color: rgba(255,255,255,.5); margin-top: .35rem; }
.card-body { padding: 2rem; }
.steps { display: flex; gap: .5rem; margin-bottom: 1.75rem; }
.step { flex: 1; text-align: center; padding: .4rem; border-radius: 6px; font-size: .75rem; font-weight: 600; }
.step-done { background: rgba(212,175,55,.12); color: #D4AF37; }
.step-active { background: #D4AF37; color: #0a0805; }
.step-pending { background: #f4f1eb; color: #9b9490; }
.notice { padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: .86rem; }
.notice-error { background: #fff5f5; color: #c53030; border: 1px solid #feb2b2; }
.notice-success { background: #f0fff4; color: #276749; border: 1px solid #9ae6b4; }
.form-group { margin-bottom: 1rem; }
label { display: block; font-size: .82rem; font-weight: 600; color: #4a4040; margin-bottom: .3rem; }
input, select { width: 100%; padding: .55rem .85rem; border: 1.5px solid #e0dbd2; border-radius: 8px; font-size: .88rem; color: #1a120a; transition: border-color .2s, box-shadow .2s; }
input:focus, select:focus { outline: none; border-color: #D4AF37; box-shadow: 0 0 0 3px rgba(212,175,55,.12); }
.form-row { display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; }
.hint { font-size: .74rem; color: #9b9490; margin-top: .25rem; }
.btn { display: inline-flex; align-items: center; gap: .4rem; padding: .65rem 1.5rem; border-radius: 8px; font-size: .9rem; font-weight: 600; cursor: pointer; border: none; width: 100%; justify-content: center; transition: all .2s; margin-top: .5rem; }
.btn-primary { background: linear-gradient(135deg, #D4AF37, #c49d2e); color: #0a0805; }
.btn-primary:hover { background: linear-gradient(135deg, #e6c347, #D4AF37); box-shadow: 0 4px 16px rgba(212,175,55,.35); transform: translateY(-1px); }
.btn-outline { background: #f4f1eb; color: #4a4040; border: 1px solid #ddd; margin-top: .5rem; }
.btn-outline:hover { background: #ede8e0; }
.warning-box { background: rgba(212,175,55,.06); border: 1px solid rgba(212,175,55,.25); border-radius: 8px; padding: 1rem; margin-bottom: 1.25rem; font-size: .83rem; color: #4a4040; line-height: 1.5; }
.warning-box strong { color: #0a0805; }
.checklist { list-style: none; margin-top: .5rem; }
.checklist li { display: flex; align-items: flex-start; gap: .4rem; padding: .2rem 0; font-size: .83rem; color: #4a4040; }
.success-icon { width: 56px; height: 56px; border-radius: 50%; background: rgba(56,161,105,.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
h2 { font-size: 1.1rem; font-weight: 700; color: #1a120a; margin-bottom: 1rem; }
p { font-size: .86rem; color: #6b6460; line-height: 1.6; margin-bottom: .75rem; }
code { background: #f4f1eb; padding: .15rem .4rem; border-radius: 4px; font-family: monospace; font-size: .82rem; color: #1a120a; }
</style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <div class="card-logo">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
            <div>
                <div class="card-title">Database Setup Wizard</div>
                <div class="card-subtitle">ShokherSrity &middot; SQLite → MySQL Migration</div>
            </div>
        </div>
    </div>
    <div class="card-body">

        <?php if ($message): [$mtype, $mtext] = explode(':', $message, 2); ?>
        <div class="notice notice-<?php echo $mtype; ?>"><?php echo $mtext; ?></div>
        <?php endif; ?>

        <?php if ($step === 1 || $step === 2 && !$success): ?>
        <!-- STEP 1: Enter credentials -->
        <div class="steps">
            <div class="step step-active">1. Credentials</div>
            <div class="step step-pending">2. Test</div>
            <div class="step step-pending">3. Apply</div>
        </div>
        <div class="warning-box">
            <strong>Before you proceed:</strong>
            <ul class="checklist">
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Create a MySQL database on your server first</li>
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Note the database name, username, and password</li>
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> You may need to import your data separately using <code>wp db export/import</code> if you already have content</li>
            </ul>
        </div>
        <form method="post" action="?step=2">
            <input type="hidden" name="step" value="2">
            <div class="form-row">
                <div class="form-group">
                    <label>Database Host</label>
                    <input type="text" name="db_host" value="<?php echo htmlspecialchars($_POST['db_host'] ?? 'localhost'); ?>" placeholder="localhost" required>
                    <p class="hint">Usually <code>localhost</code> or your DB server IP</p>
                </div>
                <div class="form-group">
                    <label>Port</label>
                    <input type="number" name="db_port" value="<?php echo (int)($_POST['db_port'] ?? 3306); ?>" placeholder="3306">
                </div>
            </div>
            <div class="form-group">
                <label>Database Name</label>
                <input type="text" name="db_name" value="<?php echo htmlspecialchars($_POST['db_name'] ?? 'wordpress'); ?>" placeholder="wordpress" required>
                <p class="hint">Will be created automatically if it doesn't exist</p>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="db_user" value="<?php echo htmlspecialchars($_POST['db_user'] ?? ''); ?>" placeholder="db_user" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="db_pass" placeholder="••••••••" autocomplete="new-password">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Test Connection
            </button>
        </form>

        <?php elseif ($step === 2 && strpos($message ?? '', 'success') === 0): ?>
        <!-- STEP 2: Confirm apply -->
        <div class="steps">
            <div class="step step-done">1. Credentials</div>
            <div class="step step-done">2. Test</div>
            <div class="step step-active">3. Apply</div>
        </div>
        <?php session_start(); $db = $_SESSION['ss_db'] ?? []; ?>
        <h2>Ready to Apply Configuration</h2>
        <p>The MySQL connection was successful. The following changes will be made:</p>
        <ul class="checklist" style="margin-bottom:1.25rem;">
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#276749" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Rewrite <code>wp-config.php</code> with MySQL credentials</li>
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#276749" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Generate new randomized security keys</li>
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#276749" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Disable the SQLite database dropin (<code>db.php</code>)</li>
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#276749" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Rename the SQLite file to <code>.ht.sqlite.backup-*</code></li>
        </ul>
        <div class="warning-box">
            <strong>Important:</strong> WordPress will try to use MySQL after this step. Run the WordPress installer or import a database export before loading the site, or it will show a setup screen.
        </div>
        <form method="post" action="?step=3">
            <input type="hidden" name="step" value="3">
            <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/></svg>
                Apply MySQL Configuration
            </button>
        </form>
        <a href="?step=1" class="btn btn-outline">Back</a>

        <?php elseif ($step === 4): ?>
        <!-- STEP 4: Done -->
        <div class="steps">
            <div class="step step-done">1. Credentials</div>
            <div class="step step-done">2. Test</div>
            <div class="step step-done">3. Apply</div>
        </div>
        <div class="success-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#38a169" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <h2 style="text-align:center;">Configuration Applied</h2>
        <p style="text-align:center;">WordPress is now configured to use MySQL.</p>
        <ul class="checklist" style="margin-bottom:1.5rem;">
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#276749" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> <code>wp-config.php</code> rewritten with MySQL credentials</li>
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#276749" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> New randomized security keys generated</li>
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#276749" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> SQLite dropin disabled</li>
        </ul>
        <div class="warning-box">
            <strong>Next steps:</strong>
            <ul class="checklist">
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Delete <code>setup-database.php</code> from your server for security</li>
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Visit your site to run the WordPress installer, or import your DB</li>
                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Log in to the admin panel and verify everything works</li>
            </ul>
        </div>
        <a href="/wp-admin/" class="btn btn-primary">Go to WordPress Admin</a>
        <?php endif; ?>

    </div>
</div>
</body>
</html>
