<?php
/**
 * Plugin Name: ShokherSrity Core
 * Description: Core functionality for ShokherSrity — image catalog, video management, packages & settings.
 * Version: 2.0.0
 * Author: ShokherSrity
 */

defined('ABSPATH') || exit;

define('SS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SS_UPLOADS',    WP_CONTENT_DIR . '/uploads');
define('SS_UPLOADS_URL', content_url('uploads'));

// ─── Activation Hook ─────────────────────────────────────────
register_activation_hook(__FILE__, 'ss_plugin_activate');
function ss_plugin_activate() {
    ss_import_image_catalog();
    ss_import_default_videos();
}

function ss_import_image_catalog() {
    if (get_option('ss_catalog_imported')) return;
    $json_file = get_template_directory() . '/image_catalog.json';
    if (!file_exists($json_file)) return;
    $raw = json_decode(file_get_contents($json_file), true);
    if (!is_array($raw)) return;
    $catalog = [];
    foreach ($raw as $item) {
        $src = $item['src'] ?? '';
        $src = preg_replace('#^attached_assets/#', '/wp-content/uploads/', $src);
        $catalog[] = [
            'id'       => uniqid('img_'),
            'src'      => $src,
            'category' => $item['category'] ?? 'wedding',
            'label'    => $item['label'] ?? 'Wedding',
            'width'    => (int)($item['width'] ?? 0),
            'height'   => (int)($item['height'] ?? 0),
        ];
    }
    update_option('ss_image_catalog', $catalog, false);
    update_option('ss_catalog_imported', true);
}

function ss_import_default_videos() {
    if (get_option('ss_videos_imported')) return;
    $videos_dir = SS_UPLOADS . '/videos/';
    $default_videos = [
        ['file' => 'reel1.mp4', 'title' => 'A Timeless Love Story',        'description' => "Captured in every frame — a day you'll never forget ✨"],
        ['file' => 'reel2.mp4', 'title' => 'Cinematic Wedding Highlights',  'description' => 'An elegant dance of love through our lens 💍'],
        ['file' => 'reel3.mp4', 'title' => 'Moments That Last Forever',     'description' => 'Every smile, every tear, every precious moment 🌸'],
    ];
    $videos = [];
    foreach ($default_videos as $v) {
        $path = $videos_dir . $v['file'];
        if (!file_exists($path)) continue;
        $videos[] = [
            'id'          => uniqid('vid_'),
            'src'         => '/wp-content/uploads/videos/' . $v['file'],
            'title'       => $v['title'],
            'description' => $v['description'],
            'uploaded_at' => date('Y-m-d'),
        ];
    }
    if ($videos) {
        update_option('ss_videos', $videos, false);
        update_option('ss_videos_imported', true);
    }
}

// ═══════════════════════════════════════════════════════════════
// CUSTOM ADMIN EXPERIENCE
// ═══════════════════════════════════════════════════════════════

// ─── 1. Login → land directly on SS dashboard ────────────────
add_filter('login_redirect', 'ss_login_redirect', 10, 3);
function ss_login_redirect($redirect_to, $requested_redirect_to, $user) {
    if (is_wp_error($user)) return $redirect_to;
    if (is_a($user, 'WP_User') && user_can($user, 'manage_options')) {
        return admin_url('admin.php?page=shokhersrity');
    }
    return $redirect_to;
}

// ─── 2. Redirect ALL standard WP admin pages to SS dashboard ─
add_action('admin_init', 'ss_admin_redirect_to_dashboard', 1);
function ss_admin_redirect_to_dashboard() {
    if (!current_user_can('manage_options')) return;
    global $pagenow;
    $redirect_pages = [
        'index.php', 'plugins.php', 'plugin-install.php', 'plugin-editor.php',
        'theme-editor.php', 'themes.php', 'edit.php', 'upload.php',
        'edit-comments.php', 'users.php', 'tools.php', 'options-general.php',
        'options-writing.php', 'options-reading.php', 'options-discussion.php',
        'options-media.php', 'options-permalink.php', 'options-privacy.php',
        'site-editor.php', 'customize.php', 'nav-menus.php', 'widgets.php',
        'post-new.php', 'post.php', 'media-new.php', 'user-new.php',
    ];
    if (in_array($pagenow, $redirect_pages)) {
        wp_redirect(admin_url('admin.php?page=shokhersrity'));
        exit;
    }
}

// ─── 3. Admin Menu: only ShokherSrity items ──────────────────
add_action('admin_menu', 'ss_register_admin_menu', 999);
function ss_register_admin_menu() {
    $remove = [
        'index.php', 'edit.php', 'upload.php', 'edit.php?post_type=page',
        'edit-comments.php', 'themes.php', 'plugins.php', 'users.php',
        'tools.php', 'options-general.php', 'link-manager.php',
        'edit.php?post_type=wp_block', 'site-editor.php',
    ];
    foreach ($remove as $slug) remove_menu_page($slug);

    add_menu_page(
        'ShokherSrity Studio', 'ShokherSrity', 'manage_options',
        'shokhersrity', 'ss_admin_dashboard', 'none', 2
    );
    add_submenu_page('shokhersrity', 'Dashboard',       '📊  Dashboard',  'manage_options', 'shokhersrity', 'ss_admin_dashboard');
    add_submenu_page('shokhersrity', 'Gallery Manager', '🖼  Gallery',    'manage_options', 'ss-gallery',   'ss_admin_gallery');
    add_submenu_page('shokhersrity', 'Reels / Videos',  '🎬  Reels',      'manage_options', 'ss-videos',    'ss_admin_videos');
    add_submenu_page('shokhersrity', 'Packages',        '💎  Packages',   'manage_options', 'ss-packages',  'ss_admin_packages');
    add_submenu_page('shokhersrity', 'Settings',        '⚙  Settings',    'manage_options', 'ss-settings',  'ss_admin_settings');
}

// ─── 4. Media enqueue only on SS pages ───────────────────────
add_action('admin_enqueue_scripts', 'ss_enqueue_media_on_ss_pages');
function ss_enqueue_media_on_ss_pages($hook) {
    if (strpos($hook, 'shokhersrity') !== false || strpos($hook, 'ss-') !== false) {
        wp_enqueue_media();
    }
}

// ─── 5. Global admin CSS — every admin page ──────────────────
add_action('admin_head', 'ss_global_admin_css', 1);
function ss_global_admin_css() {
    echo '<style id="ss-admin-chrome">' . ss_get_admin_css() . '</style>';
}

function ss_get_admin_css() {
    return '
/* ══ ShokherSrity Studio — Full Admin Chrome ══ */
html{background:#0a0805!important}
body.wp-admin{background:#f0ece4!important}
#wpcontent,#wpbody{background:#f0ece4!important}
#wpfooter{display:none!important}

/* Admin bar */
#wpadminbar{background:#0a0805!important;border-bottom:1px solid rgba(212,175,55,.12)!important}
#wpadminbar *{box-shadow:none!important}
#wpadminbar .ab-item,#wpadminbar a.ab-item{color:rgba(255,255,255,.6)!important}
#wpadminbar .ab-item:hover{color:#D4AF37!important;background:transparent!important}
#wp-admin-bar-wp-logo,#wp-admin-bar-site-editor,#wp-admin-bar-customize,
#wp-admin-bar-updates,#wp-admin-bar-comments,#wp-admin-bar-new-content,
#wp-admin-bar-wpseo-menu,#wp-admin-bar-debug-bar{display:none!important}
#wp-admin-bar-site-name a{color:#D4AF37!important;font-weight:600!important}
#wp-admin-bar-my-account .ab-item{color:rgba(255,255,255,.5)!important;font-size:.8rem!important}

/* Sidebar */
#adminmenuback,#adminmenuwrap{background:#0a0805!important;border-right:1px solid rgba(212,175,55,.08)!important}

/* Brand header: first SS menu item */
#adminmenu>li:first-child>a.menu-top{
  background:linear-gradient(135deg,rgba(212,175,55,.14),rgba(212,175,55,.06))!important;
  border-bottom:1px solid rgba(212,175,55,.18)!important;
  padding:1rem .85rem!important;margin-bottom:.4rem!important
}
#adminmenu>li:first-child>a.menu-top .wp-menu-name{
  color:#D4AF37!important;font-size:.9rem!important;font-weight:600!important
}

/* All menu links */
#adminmenu a,#adminmenu .wp-submenu a{color:rgba(255,255,255,.5)!important;font-size:.84rem!important}
#adminmenu li a:hover{color:rgba(255,255,255,.9)!important;background:rgba(255,255,255,.035)!important}
#adminmenu .wp-menu-arrow{display:none!important}
#adminmenu .wp-menu-image{opacity:.45}

/* Active item */
#adminmenu li.wp-has-current-submenu>a,
#adminmenu li.current>a,
#adminmenu .wp-submenu li.current a{color:#D4AF37!important;background:rgba(212,175,55,.08)!important}
#adminmenu li.wp-has-current-submenu .wp-menu-image,
#adminmenu li.current .wp-menu-image{opacity:1!important}

/* Submenu */
#adminmenu .wp-submenu{background:rgba(0,0,0,.3)!important;border-left:2px solid rgba(212,175,55,.12)!important;margin-left:4px!important}
#adminmenu .wp-submenu a{color:rgba(255,255,255,.45)!important;padding:.5rem 1rem .5rem 1.6rem!important;font-size:.81rem!important}
#adminmenu .wp-submenu a:hover{color:#D4AF37!important;background:rgba(212,175,55,.06)!important}
#adminmenu .wp-submenu li.current a{color:#D4AF37!important}

/* Remove separators */
#adminmenu .separator,#adminmenu .wp-menu-separator{display:none!important}

/* WP notices cleanup */
.notice:not(.notice-error):not(.ss-notice),
.update-nag,.updated:not(.ss-notice),.notice-warning:not(.ss-notice){display:none!important}
#screen-meta,#screen-options-link-wrap,#contextual-help-link-wrap{display:none!important}

/* Hide WP default page title */
.wrap>h1.wp-heading-inline,.wrap>h1:first-child{display:none!important}

/* Main content */
#wpcontent{padding-left:12px!important}
#wpbody-content{padding-bottom:2rem!important}
#wpbody-content .wrap{margin-right:10px!important}

/* ── SS Component styles ── */
.ss-admin{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
.ss-header{background:linear-gradient(135deg,#0a0805 0%,#1a120a 100%);color:#fff;padding:1.75rem 2rem;border-radius:12px;margin-bottom:1.75rem;display:flex;align-items:center;gap:1rem}
.ss-header h1{font-size:1.5rem;margin:0;font-weight:600;background:linear-gradient(135deg,#D4AF37,#F5D67B);-webkit-background-clip:text;background-clip:text;color:transparent}
.ss-header p{margin:.25rem 0 0;font-size:.88rem;color:rgba(255,255,255,.55)}
.ss-header-logo{font-size:1.8rem}
.ss-card{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06);padding:1.5rem;margin-bottom:1.5rem;border:1px solid rgba(212,175,55,.1)}
.ss-card h2{font-size:1.05rem;color:#1a120a;margin:0 0 1.25rem;padding-bottom:.75rem;border-bottom:1px solid rgba(212,175,55,.12);display:flex;align-items:center;gap:.5rem}
.ss-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.2rem;border-radius:8px;font-size:.86rem;font-weight:500;cursor:pointer;border:none;text-decoration:none;transition:all .18s;line-height:1.4}
.ss-btn-primary{background:linear-gradient(135deg,#D4AF37,#c49d2e);color:#0a0805}
.ss-btn-primary:hover{background:linear-gradient(135deg,#e6c347,#D4AF37);color:#0a0805;transform:translateY(-1px);box-shadow:0 4px 12px rgba(212,175,55,.3)}
.ss-btn-danger{background:#fff0f0;color:#c0392b;border:1px solid #ffcdd2}
.ss-btn-danger:hover{background:#c0392b;color:#fff}
.ss-btn-secondary{background:#f0ede6;color:#333;border:1px solid rgba(0,0,0,.08)}
.ss-btn-secondary:hover{background:#e6e0d5;color:#111}
.ss-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:.9rem}
.ss-image-card{position:relative;aspect-ratio:1;border-radius:8px;overflow:hidden;cursor:pointer;border:2px solid transparent;transition:border-color .2s;background:#111}
.ss-image-card img{width:100%;height:100%;object-fit:cover;display:block}
.ss-image-card:hover{border-color:#D4AF37}
.ss-image-card .ss-image-overlay{position:absolute;inset:0;background:rgba(0,0,0,.62);opacity:0;transition:opacity .2s;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.4rem;padding:.5rem}
.ss-image-card:hover .ss-image-overlay{opacity:1}
.ss-image-card .ss-image-meta{position:absolute;bottom:0;left:0;right:0;background:linear-gradient(transparent,rgba(0,0,0,.8));padding:.5rem;font-size:.68rem;color:#fff}
.ss-notice{padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.9rem}
.ss-notice-success{background:#f0fff4;color:#2e7d32;border:1px solid #a5d6a7}
.ss-notice-error{background:#fff0f0;color:#c62828;border:1px solid #ef9a9a}
.ss-form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem}
.ss-form-group{margin-bottom:1rem}
.ss-form-group label{display:block;font-size:.83rem;font-weight:500;color:#555;margin-bottom:.3rem}
.ss-form-group input,.ss-form-group textarea,.ss-form-group select{width:100%;padding:.55rem .85rem;border:1px solid #ddd;border-radius:8px;font-size:.88rem;color:#333;box-sizing:border-box;transition:border-color .2s;background:#fff}
.ss-form-group input:focus,.ss-form-group textarea:focus,.ss-form-group select:focus{outline:none;border-color:#D4AF37;box-shadow:0 0 0 3px rgba(212,175,55,.1)}
.ss-form-group textarea{resize:vertical;min-height:90px}
.ss-table{width:100%;border-collapse:collapse;font-size:.86rem}
.ss-table th{text-align:left;padding:.6rem 1rem;background:#f8f5ef;color:#555;font-weight:600;border-bottom:2px solid rgba(212,175,55,.12)}
.ss-table td{padding:.7rem 1rem;border-bottom:1px solid #f0ece0;vertical-align:middle}
.ss-table tr:hover td{background:rgba(212,175,55,.04)}
.ss-upload-zone{border:2px dashed rgba(212,175,55,.38);border-radius:12px;padding:2.5rem;text-align:center;cursor:pointer;transition:all .2s;background:rgba(212,175,55,.025)}
.ss-upload-zone:hover,.ss-upload-zone.drag-over{border-color:#D4AF37;background:rgba(212,175,55,.07)}
.ss-upload-zone p{color:#888;margin:.25rem 0;font-size:.88rem}
.ss-badge{display:inline-block;padding:.2rem .6rem;border-radius:20px;font-size:.73rem;font-weight:600}
.ss-badge-gold{background:rgba(212,175,55,.13);color:#7a5c00}
.ss-badge-dark{background:#1a120a;color:#D4AF37}
.ss-badge-blue{background:#e3f2fd;color:#1565c0}
.ss-tabs{display:flex;gap:.4rem;margin-bottom:1.5rem;border-bottom:2px solid rgba(212,175,55,.1);padding-bottom:0}
.ss-tab{padding:.6rem 1.1rem;border-radius:8px 8px 0 0;cursor:pointer;font-size:.87rem;font-weight:500;color:#888;background:none;border:none;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .18s}
.ss-tab.active{color:#1a120a;border-bottom-color:#D4AF37;background:rgba(212,175,55,.05)}
.ss-tab:hover{color:#1a120a}
.ss-tab-content{display:none}
.ss-tab-content.active{display:block}
.hero-pair-preview{display:grid;grid-template-columns:2fr 1fr;gap:1rem;margin-top:1rem}
.hero-preview-slot{border-radius:10px;overflow:hidden;border:2px solid rgba(212,175,55,.18);min-height:150px;position:relative;background:#111}
.hero-preview-slot img{width:100%;height:100%;object-fit:cover;display:block}
.hero-preview-slot .slot-label{position:absolute;top:.5rem;left:.5rem;background:rgba(0,0,0,.75);color:#D4AF37;font-size:.68rem;padding:.2rem .5rem;border-radius:4px;font-weight:600;letter-spacing:.06em;text-transform:uppercase}
.spinner{display:inline-block;width:16px;height:16px;border:2px solid rgba(212,175,55,.3);border-top-color:#D4AF37;border-radius:50%;animation:ss-spin .7s linear infinite;vertical-align:middle}
@keyframes ss-spin{to{transform:rotate(360deg)}}
.ss-video-list{display:grid;gap:.9rem}
.ss-video-card{display:grid;grid-template-columns:180px 1fr auto;gap:1rem;align-items:center;padding:1rem;background:#fafaf8;border-radius:10px;border:1px solid rgba(212,175,55,.08)}
.ss-video-card video{width:180px;height:102px;object-fit:cover;border-radius:8px;background:#111}
.ss-video-actions{display:flex;flex-direction:column;gap:.45rem}
.pkg-row{display:grid;grid-template-columns:90px 1fr 1fr 1fr auto;gap:.75rem;align-items:start;padding:.75rem;border-bottom:1px solid #f0ece0}
.pkg-row:last-child{border-bottom:none}
.ss-stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem}
.ss-stat-card{background:linear-gradient(135deg,#0a0805,#1a120a);color:#fff;border-radius:10px;padding:1.2rem;text-align:center}
.ss-stat-card .stat-val{font-size:1.9rem;font-weight:700;background:linear-gradient(135deg,#D4AF37,#F5D67B);-webkit-background-clip:text;background-clip:text;color:transparent}
.ss-stat-card .stat-lbl{font-size:.78rem;color:rgba(255,255,255,.55);margin-top:.2rem}
';
}

// ─── 6. Disable Gutenberg / Block Editor everywhere ──────────
add_filter('use_block_editor_for_post',      '__return_false');
add_filter('use_block_editor_for_post_type', '__return_false');

// ─── 7. Hide WP admin bar on the frontend ────────────────────
add_filter('show_admin_bar', '__return_false');

// ─── 8. Suppress update/maintenance nag notices ──────────────
add_action('admin_head', function() {
    remove_action('admin_notices', 'update_nag');
    remove_action('admin_notices', 'maintenance_nag');
}, 1);

// ─── 9. Branded login page ────────────────────────────────────
add_action('login_head', 'ss_custom_login_css');
function ss_custom_login_css() {
    echo '<style>
body.login{background:linear-gradient(160deg,#0a0805 0%,#1a120a 50%,#0d0a06 100%)!important;background-attachment:fixed!important}
body.login::before{content:"";position:fixed;inset:0;background:radial-gradient(ellipse 80% 60% at 50% 0%,rgba(212,175,55,.07),transparent);pointer-events:none}
#login{max-width:380px;width:100%}
#login h1 a{background-image:none!important;width:auto!important;height:auto!important;text-indent:0!important;overflow:visible!important;display:block;text-align:center;font-family:Georgia,"Times New Roman",serif;font-size:1.5rem;letter-spacing:.1em;color:#D4AF37!important;padding:.5rem 0 1.5rem;text-decoration:none;text-shadow:0 0 30px rgba(212,175,55,.4)}
#loginform{border-radius:14px!important;box-shadow:0 24px 60px rgba(0,0,0,.6),0 0 0 1px rgba(212,175,55,.15)!important;background:rgba(20,14,8,.95)!important;border:none!important;padding:2rem!important}
#loginform label{color:rgba(255,255,255,.6)!important;font-size:.82rem!important}
#user_login,#user_pass{background:rgba(255,255,255,.05)!important;border:1px solid rgba(212,175,55,.2)!important;color:rgba(255,255,255,.9)!important;border-radius:8px!important;height:42px!important;padding:0 .9rem!important;font-size:.9rem!important}
#user_login:focus,#user_pass:focus{border-color:#D4AF37!important;box-shadow:0 0 0 2px rgba(212,175,55,.18)!important;outline:none!important}
.wp-pwd .button.wp-hide-pw{background:transparent!important;border:none!important;color:rgba(255,255,255,.4)!important;box-shadow:none!important}
#wp-submit{background:linear-gradient(135deg,#D4AF37,#c49d2e)!important;border:none!important;color:#0a0805!important;font-weight:700!important;font-size:.9rem!important;border-radius:8px!important;height:42px!important;width:100%!important;cursor:pointer!important;letter-spacing:.04em!important;transition:all .2s!important}
#wp-submit:hover{background:linear-gradient(135deg,#e6c347,#D4AF37)!important;box-shadow:0 4px 16px rgba(212,175,55,.4)!important;transform:translateY(-1px)!important}
#rememberme+label{color:rgba(255,255,255,.45)!important;font-size:.8rem!important}
#nav,#backtoblog{display:none!important}
.login #login_error,.login .message{border-radius:8px!important;border-left:none!important;background:rgba(212,175,55,.08)!important;color:#D4AF37!important;border:1px solid rgba(212,175,55,.2)!important}
</style>';
}
add_filter('login_headertext', function() { return 'ShokherSrity Studio'; });
add_filter('login_headerurl',  function() { return home_url(); });

// ─── 10. Remove plugin action/install links ───────────────────
add_filter('plugin_action_links',  '__return_empty_array');
add_filter('install_plugins_tabs', '__return_empty_array');

// ─── Admin Page Callbacks ─────────────────────────────────────
function ss_admin_dashboard() { include SS_PLUGIN_DIR . 'admin/dashboard.php'; }
function ss_admin_gallery()   { include SS_PLUGIN_DIR . 'admin/gallery-admin.php'; }
function ss_admin_videos()    { include SS_PLUGIN_DIR . 'admin/videos-admin.php'; }
function ss_admin_packages()  { include SS_PLUGIN_DIR . 'admin/packages-admin.php'; }
function ss_admin_settings()  { include SS_PLUGIN_DIR . 'admin/settings-admin.php'; }

// ═══════════════════════════════════════════════════════════════
// AJAX HANDLERS
// ═══════════════════════════════════════════════════════════════

// ─── Upload Image ─────────────────────────────────────────────
add_action('wp_ajax_ss_upload_image', 'ss_ajax_upload_image');
function ss_ajax_upload_image() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');
    if (empty($_FILES['image'])) wp_send_json_error('No file received');

    $file     = $_FILES['image'];
    $category = sanitize_text_field($_POST['category'] ?? 'wedding');
    $label    = sanitize_text_field($_POST['label']    ?? 'Wedding');

    $allowed = ['image/webp', 'image/jpeg', 'image/png'];
    if (!in_array($file['type'], $allowed)) wp_send_json_error('Only WebP, JPEG, PNG allowed');

    $folders = [
        'wedding'    => 'Wedding Photoshooot',
        'bride'      => 'Bride Photoshoot',
        'reception'  => 'Reception',
        'engagement' => 'Engegment Photoshoot',
        'babyshower' => 'Baby Shower',
        'baby'       => 'Baby Photoshoot',
        'gallery'    => 'gallery',
    ];
    $folder   = $folders[$category] ?? 'gallery';
    $dest_dir = SS_UPLOADS . '/' . $folder . '/';
    wp_mkdir_p($dest_dir);

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
    $filename = uniqid('img_') . '.' . $ext;
    $dest_path = $dest_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
        wp_send_json_error('Failed to save file');
    }

    // GD optimization: resize to max 2000px, convert to WebP
    if (function_exists('imagecreatefromstring')) {
        $orig = @file_get_contents($dest_path);
        $img  = $orig ? @imagecreatefromstring($orig) : false;
        if ($img) {
            $ow = imagesx($img);
            $oh = imagesy($img);
            $max = 2000;
            if ($ow > $max || $oh > $max) {
                $ratio   = $ow > $oh ? $max / $ow : $max / $oh;
                $nw      = (int)round($ow * $ratio);
                $nh      = (int)round($oh * $ratio);
                $resized = imagecreatetruecolor($nw, $nh);
                imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $ow, $oh);
                imagedestroy($img);
                $img = $resized;
            }
            if (function_exists('imagewebp')) {
                $new_path = preg_replace('/\.(?:jpe?g|png|webp)$/i', '.webp', $dest_path);
                imagewebp($img, $new_path, 85);
                if ($new_path !== $dest_path) {
                    @unlink($dest_path);
                    $dest_path = $new_path;
                    $filename  = basename($dest_path);
                }
            } else {
                imagejpeg($img, $dest_path, 88);
            }
            imagedestroy($img);
        }
    }

    $info   = @getimagesize($dest_path);
    $width  = $info[0] ?? 0;
    $height = $info[1] ?? 0;

    $entry = [
        'id'       => uniqid('img_'),
        'src'      => '/wp-content/uploads/' . $folder . '/' . $filename,
        'category' => $category,
        'label'    => $label,
        'width'    => $width,
        'height'   => $height,
    ];
    $catalog   = (array) get_option('ss_image_catalog', []);
    $catalog[] = $entry;
    update_option('ss_image_catalog', $catalog, false);
    wp_send_json_success($entry);
}

// ─── Delete Image ─────────────────────────────────────────────
add_action('wp_ajax_ss_delete_image', 'ss_ajax_delete_image');
function ss_ajax_delete_image() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    $id      = sanitize_text_field($_POST['id'] ?? '');
    if (!$id) wp_send_json_error('No ID');

    $catalog     = (array) get_option('ss_image_catalog', []);
    $new_catalog = [];
    $deleted_src = '';
    foreach ($catalog as $item) {
        if ($item['id'] === $id) { $deleted_src = $item['src']; }
        else { $new_catalog[] = $item; }
    }
    if ($deleted_src) {
        $file_path = ABSPATH . ltrim($deleted_src, '/');
        if (file_exists($file_path)) @unlink($file_path);
    }
    update_option('ss_image_catalog', $new_catalog, false);
    wp_send_json_success(['deleted' => $id]);
}

// ─── Update Hero Images ───────────────────────────────────────
add_action('wp_ajax_ss_update_hero', 'ss_ajax_update_hero');
function ss_ajax_update_hero() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    $desktop = sanitize_text_field($_POST['desktop'] ?? '');
    $mobile  = sanitize_text_field($_POST['mobile']  ?? '');

    if (!empty($_FILES['desktop_file'])) {
        $path = ss_save_hero_upload('desktop_file', 'hero-desktop');
        if ($path) $desktop = $path;
    }
    if (!empty($_FILES['mobile_file'])) {
        $path = ss_save_hero_upload('mobile_file', 'hero-mobile');
        if ($path) $mobile = $path;
    }

    $hero = ['desktop' => $desktop, 'mobile' => $mobile];
    update_option('ss_hero_images', $hero);
    wp_send_json_success($hero);
}

function ss_save_hero_upload($file_key, $dest_name) {
    $file    = $_FILES[$file_key];
    $allowed = ['image/webp', 'image/jpeg', 'image/png'];
    if (!in_array($file['type'], $allowed)) return false;
    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'webp';
    $dest = SS_UPLOADS . '/hero/' . $dest_name . '.' . $ext;
    wp_mkdir_p(SS_UPLOADS . '/hero/');
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return '/wp-content/uploads/hero/' . $dest_name . '.' . $ext;
    }
    return false;
}

// ─── Upload Video ─────────────────────────────────────────────
add_action('wp_ajax_ss_upload_video', 'ss_ajax_upload_video');
function ss_ajax_upload_video() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');
    if (empty($_FILES['video'])) wp_send_json_error('No file received');

    $file  = $_FILES['video'];
    if ($file['type'] !== 'video/mp4') wp_send_json_error('Only MP4 allowed');

    $title    = sanitize_text_field($_POST['title']       ?? 'Wedding Reel');
    $desc     = sanitize_text_field($_POST['description'] ?? '');
    $dest_dir = SS_UPLOADS . '/videos/';
    wp_mkdir_p($dest_dir);

    $tmp_name = $dest_dir . 'tmp_' . uniqid() . '.mp4';
    if (!move_uploaded_file($file['tmp_name'], $tmp_name)) {
        wp_send_json_error('Failed to save upload');
    }

    $final_name = uniqid('vid_') . '.mp4';
    $final_path = $dest_dir . $final_name;
    $ffmpeg     = trim(shell_exec('which ffmpeg 2>/dev/null') ?: '/run/current-system/sw/bin/ffmpeg');
    exec(escapeshellarg($ffmpeg) . ' -i ' . escapeshellarg($tmp_name) . ' -c copy -movflags +faststart ' . escapeshellarg($final_path) . ' -y 2>&1', $out, $code);
    @unlink($tmp_name);
    if ($code !== 0 || !file_exists($final_path)) rename($tmp_name, $final_path);

    $entry = [
        'id'          => uniqid('vid_'),
        'src'         => '/wp-content/uploads/videos/' . $final_name,
        'title'       => $title,
        'description' => $desc,
        'uploaded_at' => date('Y-m-d'),
    ];
    $videos   = (array) get_option('ss_videos', []);
    $videos[] = $entry;
    update_option('ss_videos', $videos, false);
    wp_send_json_success($entry);
}

// ─── Update Video Metadata ────────────────────────────────────
add_action('wp_ajax_ss_update_video', 'ss_ajax_update_video');
function ss_ajax_update_video() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    $id    = sanitize_text_field($_POST['id']          ?? '');
    $title = sanitize_text_field($_POST['title']       ?? '');
    $desc  = sanitize_text_field($_POST['description'] ?? '');

    $videos = (array) get_option('ss_videos', []);
    foreach ($videos as &$v) {
        if ($v['id'] === $id) { $v['title'] = $title; $v['description'] = $desc; break; }
    }
    update_option('ss_videos', $videos, false);
    wp_send_json_success(['id' => $id]);
}

// ─── Delete Video ─────────────────────────────────────────────
add_action('wp_ajax_ss_delete_video', 'ss_ajax_delete_video');
function ss_ajax_delete_video() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    $id  = sanitize_text_field($_POST['id'] ?? '');
    $videos = (array) get_option('ss_videos', []);
    $new = [];
    foreach ($videos as $v) {
        if ($v['id'] === $id) {
            $path = ABSPATH . ltrim($v['src'], '/');
            if (file_exists($path)) @unlink($path);
        } else { $new[] = $v; }
    }
    update_option('ss_videos', $new, false);
    wp_send_json_success(['deleted' => $id]);
}

// ─── Reorder Videos ───────────────────────────────────────────
add_action('wp_ajax_ss_reorder_videos', 'ss_ajax_reorder_videos');
function ss_ajax_reorder_videos() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    $order   = array_map('sanitize_text_field', (array)($_POST['order'] ?? []));
    $videos  = (array) get_option('ss_videos', []);
    $indexed = [];
    foreach ($videos as $v) $indexed[$v['id']] = $v;
    $new = [];
    foreach ($order as $id) { if (isset($indexed[$id])) $new[] = $indexed[$id]; }
    foreach ($videos as $v) { if (!in_array($v['id'], $order)) $new[] = $v; }
    update_option('ss_videos', $new, false);
    wp_send_json_success(['count' => count($new)]);
}

// ─── Save Packages ────────────────────────────────────────────
add_action('wp_ajax_ss_save_packages', 'ss_ajax_save_packages');
function ss_ajax_save_packages() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    $raw  = stripslashes($_POST['packages'] ?? '{}');
    $data = json_decode($raw, true);
    if (!$data) wp_send_json_error('Invalid JSON');

    $clean = ['tiers' => [], 'packages' => []];
    foreach ((array)($data['tiers'] ?? []) as $t) {
        $clean['tiers'][] = [
            'id'      => sanitize_key($t['id'] ?? ''),
            'label'   => sanitize_text_field($t['label'] ?? ''),
            'subtitle'=> sanitize_text_field($t['subtitle'] ?? ''),
            'columns' => (int)($t['columns'] ?? 3),
        ];
    }
    foreach ((array)($data['packages'] ?? []) as $p) {
        $feats = [];
        foreach ((array)($p['features'] ?? []) as $f) $feats[] = sanitize_text_field($f);
        $clean['packages'][] = [
            'id'                 => sanitize_key($p['id'] ?? ''),
            'tier'               => sanitize_key($p['tier'] ?? ''),
            'name'               => sanitize_text_field($p['name'] ?? ''),
            'price'              => sanitize_text_field($p['price'] ?? ''),
            'period'             => sanitize_text_field($p['period'] ?? '/ day'),
            'note'               => sanitize_text_field($p['note'] ?? ''),
            'badge'              => sanitize_text_field($p['badge'] ?? ''),
            'is_popular'         => (bool)($p['is_popular'] ?? false),
            'style'              => sanitize_key($p['style'] ?? 'standard'),
            'features'           => $feats,
            'complementary_note' => sanitize_text_field($p['complementary_note'] ?? ''),
            'whatsapp_message'   => sanitize_text_field($p['whatsapp_message'] ?? ''),
        ];
    }
    update_option('ss_packages', $clean);
    wp_send_json_success(['saved' => count($clean['packages'])]);
}

// ─── Save Settings ────────────────────────────────────────────
add_action('wp_ajax_ss_save_settings', 'ss_ajax_save_settings');
function ss_ajax_save_settings() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    $fields_text = ['site_name','tagline','phone1','phone1_name','phone2','phone2_name','email','address','map_embed_url','facebook','instagram','whatsapp','youtube','tiktok','about_p1','about_p2','about_signature','cta_text','geo_lat','geo_lng'];
    $fields_int  = ['stat1_count','stat2_count','stat3_count'];
    $fields_str  = ['stat1_suffix','stat2_suffix','stat3_suffix','stat1_label','stat2_label','stat3_label'];

    $s = [];
    foreach ($fields_text as $f) $s[$f] = sanitize_text_field($_POST[$f] ?? '');
    foreach ($fields_int  as $f) $s[$f] = (int)($_POST[$f] ?? 0);
    foreach ($fields_str  as $f) $s[$f] = sanitize_text_field($_POST[$f] ?? '');
    $s['cta_title'] = wp_kses($_POST['cta_title'] ?? '', ['span' => ['class' => []]]);

    update_option('ss_settings', $s);
    wp_send_json_success(['saved' => true]);
}

// ─── Reorder Catalog ──────────────────────────────────────────
add_action('wp_ajax_ss_reorder_catalog', 'ss_ajax_reorder_catalog');
function ss_ajax_reorder_catalog() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    $order   = array_map('sanitize_text_field', (array)($_POST['order'] ?? []));
    $catalog = (array) get_option('ss_image_catalog', []);
    $indexed = [];
    foreach ($catalog as $item) $indexed[$item['id']] = $item;
    $new = [];
    foreach ($order as $id) { if (isset($indexed[$id])) $new[] = $indexed[$id]; }
    foreach ($catalog as $item) { if (!in_array($item['id'], $order)) $new[] = $item; }
    update_option('ss_image_catalog', $new, false);
    wp_send_json_success(['count' => count($new)]);
}
