<?php
defined('ABSPATH') || exit;
if (!current_user_can('manage_options')) wp_die('Access denied');

$nonce = wp_create_nonce('ss_nonce');

// Only root admin (user ID 1) can run updates
$is_root = (get_current_user_id() === 1);

// Get WP version info
global $wp_version;
$core_updates = get_site_transient('update_core');

// Check for available WP update
$available_update = null;
if (!empty($core_updates->updates)) {
    foreach ($core_updates->updates as $u) {
        if ($u->response === 'upgrade') {
            $available_update = $u;
            break;
        }
    }
}

// Check for theme/plugin updates
$plugin_updates = get_site_transient('update_plugins');
$theme_updates  = get_site_transient('update_themes');
$plugin_count   = !empty($plugin_updates->response) ? count($plugin_updates->response) : 0;
$theme_count    = !empty($theme_updates->response)  ? count($theme_updates->response)  : 0;

// Backups list
$backup_dir = WP_CONTENT_DIR . '/ss-backups/';
$backups    = [];
if (is_dir($backup_dir)) {
    $files = glob($backup_dir . '*.json') ?: [];
    rsort($files);
    foreach (array_slice($files, 0, 5) as $f) {
        $meta = json_decode(file_get_contents($f), true);
        if ($meta) $backups[] = $meta;
    }
}
?>
<div class="wrap ss-admin">

    <div class="ss-page-header">
        <div class="ss-page-header-left">
            <div class="ss-page-icon-wrap">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
            </div>
            <div>
                <h1 class="ss-page-title">System &amp; Updates</h1>
                <p class="ss-page-subtitle">WordPress <?php echo esc_html($wp_version); ?> &middot; PHP <?php echo PHP_VERSION; ?></p>
            </div>
        </div>
        <div class="ss-page-header-right">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('ss_check_updates_action', '_check_nonce'); ?>
                <input type="hidden" name="action" value="ss_check_updates_action">
                <button type="submit" class="ss-btn ss-btn-outline">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
                    Check for Updates
                </button>
            </form>
        </div>
    </div>

    <?php if (!$is_root): ?>
    <div class="ss-notice ss-notice-error" style="margin-bottom:1rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:.35rem;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        Update actions are restricted to the root administrator account only.
    </div>
    <?php endif; ?>

    <!-- Status cards -->
    <div class="ss-update-status-grid">

        <!-- WordPress core -->
        <div class="ss-card ss-update-card <?php echo $available_update ? 'ss-update-available' : 'ss-update-ok'; ?>">
            <div class="ss-update-card-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
            </div>
            <div class="ss-update-card-body">
                <div class="ss-update-card-title">WordPress Core</div>
                <div class="ss-update-card-ver">Current: v<?php echo esc_html($wp_version); ?></div>
                <?php if ($available_update): ?>
                <div class="ss-update-badge ss-badge-warn">Update available: v<?php echo esc_html($available_update->version); ?></div>
                <?php else: ?>
                <div class="ss-update-badge ss-badge-ok">Up to date</div>
                <?php endif; ?>
            </div>
            <?php if ($available_update && $is_root): ?>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" onsubmit="return confirmUpdate();">
                <?php wp_nonce_field('ss_run_update_action', '_upd_nonce'); ?>
                <input type="hidden" name="action" value="ss_run_update_action">
                <input type="hidden" name="update_type" value="core">
                <button type="submit" class="ss-btn ss-btn-primary ss-update-btn">Update Now</button>
            </form>
            <?php endif; ?>
        </div>

        <!-- Plugins -->
        <div class="ss-card ss-update-card <?php echo $plugin_count ? 'ss-update-available' : 'ss-update-ok'; ?>">
            <div class="ss-update-card-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.24 12.24a6 6 0 00-8.49-8.49L5 10.5V19h8.5z"/><line x1="16" y1="8" x2="2" y2="22"/><line x1="17.5" y1="15" x2="9" y2="15"/></svg>
            </div>
            <div class="ss-update-card-body">
                <div class="ss-update-card-title">Plugins</div>
                <div class="ss-update-card-ver">1 plugin active</div>
                <?php if ($plugin_count): ?>
                <div class="ss-update-badge ss-badge-warn"><?php echo $plugin_count; ?> update<?php echo $plugin_count>1?'s':''; ?> available</div>
                <?php else: ?>
                <div class="ss-update-badge ss-badge-ok">All up to date</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- PHP -->
        <div class="ss-card ss-update-card ss-update-ok">
            <div class="ss-update-card-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
            </div>
            <div class="ss-update-card-body">
                <div class="ss-update-card-title">PHP Runtime</div>
                <div class="ss-update-card-ver">Version <?php echo PHP_VERSION; ?></div>
                <div class="ss-update-badge ss-badge-ok">Optimal</div>
            </div>
        </div>

        <!-- Database -->
        <div class="ss-card ss-update-card ss-update-ok">
            <div class="ss-update-card-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
            </div>
            <div class="ss-update-card-body">
                <div class="ss-update-card-title">Database</div>
                <div class="ss-update-card-ver"><?php echo defined('SQLITE_DB_DROPIN_VERSION') ? 'SQLite (dev)' : 'MySQL'; ?></div>
                <div class="ss-update-badge ss-badge-ok">Active</div>
            </div>
            <a href="<?php echo esc_url(home_url('/ss-db-setup.php')); ?>" class="ss-btn ss-btn-sm ss-btn-outline" target="_blank">DB Setup</a>
        </div>
    </div>

    <!-- Backup & Rollback -->
    <div class="ss-card" style="margin-top:1.25rem;">
        <div class="ss-card-header">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><polyline points="20 9 20 16.5 16 16.5 16 12 8 12 8 16.5 4 16.5 4 9 12 3 20 9"/><rect x="8" y="12" width="8" height="9"/></svg>
            Backup &amp; Rollback
        </div>
        <?php if ($is_root): ?>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="margin-bottom:1.25rem;">
            <?php wp_nonce_field('ss_create_backup_action', '_bkp_nonce'); ?>
            <input type="hidden" name="action" value="ss_create_backup_action">
            <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                <div>
                    <p style="font-size:.85rem;color:#4a4040;margin:0 0 .35rem;">Create a snapshot of all site data (settings, catalog, videos, packages) before any update.</p>
                    <p class="ss-field-hint" style="margin:0;">Backups are stored in <code>wp-content/ss-backups/</code></p>
                </div>
                <button type="submit" class="ss-btn ss-btn-outline" style="flex-shrink:0;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Create Backup Now
                </button>
            </div>
        </form>

        <?php if ($backups): ?>
        <div class="ss-section-divider"></div>
        <h4 class="ss-section-subtitle">Recent Backups (rollback available)</h4>
        <table class="ss-table">
            <thead><tr><th>Date &amp; Time</th><th>WP Version</th><th>Images</th><th>Videos</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($backups as $bkp): ?>
            <tr>
                <td style="font-size:.82rem;"><?php echo esc_html(date('M j, Y H:i', strtotime($bkp['created_at'] ?? ''))); ?></td>
                <td><code style="font-size:.78rem;"><?php echo esc_html($bkp['wp_version'] ?? '?'); ?></code></td>
                <td><?php echo (int)($bkp['image_count'] ?? 0); ?></td>
                <td><?php echo (int)($bkp['video_count'] ?? 0); ?></td>
                <td>
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" onsubmit="return confirm('Restore this backup? Current data will be overwritten.');">
                        <?php wp_nonce_field('ss_restore_backup_action', '_rst_nonce'); ?>
                        <input type="hidden" name="action" value="ss_restore_backup_action">
                        <input type="hidden" name="backup_id" value="<?php echo esc_attr($bkp['id'] ?? ''); ?>">
                        <button type="submit" class="ss-btn ss-btn-sm ss-btn-danger">Restore</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="font-size:.85rem;color:#9b9490;text-align:center;padding:1rem 0;">No backups yet. Create one before running updates.</p>
        <?php endif; ?>
        <?php else: ?>
        <p style="font-size:.85rem;color:#9b9490;">Backup and rollback controls are available to the root administrator only.</p>
        <?php endif; ?>
    </div>

    <!-- Security overview -->
    <div class="ss-card" style="margin-top:1.25rem;">
        <div class="ss-card-header">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Security Checklist
        </div>
        <div class="ss-security-list">
            <?php
            $checks = [
                ['File editing disabled',      defined('DISALLOW_FILE_EDIT')  && DISALLOW_FILE_EDIT,  true],
                ['File modifications disabled',defined('DISALLOW_FILE_MODS')  && DISALLOW_FILE_MODS,  true],
                ['Debug mode off',             !WP_DEBUG,                                             true],
                ['HTTPS active',               is_ssl(),                                              false],
                ['WP version hidden',          true,                                                  true],
                ['XML-RPC blocked',            !apply_filters('xmlrpc_enabled', true),                false],
            ];
            foreach ($checks as [$label, $pass, $required]):
            ?>
            <div class="ss-security-item">
                <?php if ($pass): ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#38a169" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span style="color:#38a169;"><?php echo esc_html($label); ?></span>
                <?php elseif ($required): ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#e53e3e" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <span style="color:#e53e3e;"><?php echo esc_html($label); ?></span>
                <?php else: ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span style="color:#6b6460;"><?php echo esc_html($label); ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<style>
.ss-update-status-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;}
.ss-update-card{display:flex;flex-direction:column;gap:.75rem;padding:1.25rem!important;}
.ss-update-available{border-color:rgba(212,175,55,.35)!important;box-shadow:0 0 0 1px rgba(212,175,55,.15)!important;}
.ss-update-ok{border-color:#ede8e0!important;}
.ss-update-card-icon{width:44px;height:44px;border-radius:10px;background:#f4f1eb;display:flex;align-items:center;justify-content:center;color:#6b6460;}
.ss-update-available .ss-update-card-icon{background:rgba(212,175,55,.1);color:#D4AF37;}
.ss-update-card-title{font-size:.85rem;font-weight:700;color:#1a120a;}
.ss-update-card-ver{font-size:.78rem;color:#9b9490;margin-top:.15rem;}
.ss-update-badge{display:inline-block;padding:.2rem .6rem;border-radius:20px;font-size:.72rem;font-weight:600;margin-top:.35rem;}
.ss-badge-ok{background:#f0fff4;color:#276749;}
.ss-badge-warn{background:rgba(212,175,55,.12);color:#7a5c00;}
.ss-update-btn{margin-top:.25rem;font-size:.8rem!important;padding:.45rem .9rem!important;}
.ss-section-divider{height:1px;background:#ede8e0;margin:1rem 0;}
.ss-section-subtitle{font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9b9490;margin:0 0 .75rem;}
.ss-security-list{display:grid;grid-template-columns:repeat(2,1fr);gap:.5rem;}
.ss-security-item{display:flex;align-items:center;gap:.5rem;padding:.5rem .65rem;background:#f9f7f3;border-radius:7px;font-size:.82rem;}
.ss-field-hint{font-size:.76rem;color:#9b9490;}
@media(max-width:900px){.ss-update-status-grid{grid-template-columns:1fr 1fr;}}
@media(max-width:600px){.ss-update-status-grid{grid-template-columns:1fr;}.ss-security-list{grid-template-columns:1fr;}}
</style>
<script>
function confirmUpdate() {
    return confirm('This will update WordPress to the latest version.\n\nIMPORTANT: Create a backup first!\n\nProceed with update?');
}
</script>
