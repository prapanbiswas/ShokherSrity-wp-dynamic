<?php
/**
 * Plugin Name: ShokherSrity Core
 * Description: Core functionality for ShokherSrity — image catalog, video management, packages & settings.
 * Version: 3.0.0
 * Author: ShokherSrity
 */

defined('ABSPATH') || exit;

define('SS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SS_UPLOADS',    WP_CONTENT_DIR . '/uploads');
define('SS_UPLOADS_URL', content_url('uploads'));

// ─── Activation Hook ──────────────────────────────────────────
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
    $videos_dir    = SS_UPLOADS . '/videos/';
    $default_videos = [
        ['file' => 'reel1.mp4', 'title' => 'A Timeless Love Story',       'description' => 'Captured in every frame — a day you will never forget.'],
        ['file' => 'reel2.mp4', 'title' => 'Cinematic Wedding Highlights', 'description' => 'An elegant dance of love through our lens.'],
        ['file' => 'reel3.mp4', 'title' => 'Moments That Last Forever',   'description' => 'Every smile, every tear, every precious moment.'],
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

// ─── 1. Login → land directly on SS dashboard ─────────────────
add_filter('login_redirect', 'ss_login_redirect', 10, 3);
function ss_login_redirect($redirect_to, $requested_redirect_to, $user) {
    if (is_wp_error($user)) return $redirect_to;
    if (is_a($user, 'WP_User') && user_can($user, 'manage_options')) {
        return admin_url('admin.php?page=shokhersrity');
    }
    return $redirect_to;
}

// ─── 2. Redirect ALL standard WP admin pages to SS dashboard ──
add_action('admin_init', 'ss_admin_redirect_to_dashboard', 1);
function ss_admin_redirect_to_dashboard() {
    if (!current_user_can('manage_options')) return;
    global $pagenow;
    $redirect_pages = [
        'index.php', 'plugins.php', 'plugin-install.php', 'plugin-editor.php',
        'theme-editor.php', 'themes.php', 'edit.php', 'upload.php',
        'edit-comments.php', 'tools.php', 'options-general.php',
        'options-writing.php', 'options-reading.php', 'options-discussion.php',
        'options-media.php', 'options-permalink.php', 'options-privacy.php',
        'site-editor.php', 'customize.php', 'nav-menus.php', 'widgets.php',
        'post-new.php', 'post.php', 'media-new.php', 'user-new.php',
    ];
    // Allow SS admin pages through
    $page = sanitize_key($_GET['page'] ?? '');
    if (strpos($page, 'shokhersrity') === 0 || strpos($page, 'ss-') === 0) return;
    if (in_array($pagenow, $redirect_pages)) {
        wp_redirect(admin_url('admin.php?page=shokhersrity'));
        exit;
    }
}

// ─── 3. Admin Menu ─────────────────────────────────────────────
add_action('admin_menu', 'ss_register_admin_menu', 999);
function ss_register_admin_menu() {
    $remove = [
        'index.php', 'edit.php', 'upload.php', 'edit.php?post_type=page',
        'edit-comments.php', 'themes.php', 'plugins.php', 'users.php',
        'tools.php', 'options-general.php', 'link-manager.php',
        'edit.php?post_type=wp_block', 'site-editor.php',
    ];
    foreach ($remove as $slug) remove_menu_page($slug);

    // Camera SVG icon as base64 data URI (replaces broken 'none')
    $svg  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.75)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>';
    $icon = 'data:image/svg+xml;base64,' . base64_encode($svg);

    add_menu_page(
        'ShokherSrity Studio', 'ShokherSrity', 'manage_options',
        'shokhersrity', 'ss_admin_dashboard', $icon, 2
    );
    add_submenu_page('shokhersrity', 'Dashboard',       'Dashboard',  'manage_options', 'shokhersrity',  'ss_admin_dashboard');
    add_submenu_page('shokhersrity', 'Gallery Manager', 'Gallery',    'manage_options', 'ss-gallery',    'ss_admin_gallery');
    add_submenu_page('shokhersrity', 'Reels / Videos',  'Reels',      'manage_options', 'ss-videos',     'ss_admin_videos');
    add_submenu_page('shokhersrity', 'Packages',        'Packages',   'manage_options', 'ss-packages',   'ss_admin_packages');
    add_submenu_page('shokhersrity', 'Settings',        'Settings',   'manage_options', 'ss-settings',   'ss_admin_settings');
    add_submenu_page('shokhersrity', 'Users',           'Users',      'manage_options', 'ss-users',      'ss_admin_users');
    add_submenu_page('shokhersrity', 'System & Updates','System',     'manage_options', 'ss-updates',    'ss_admin_updates');
}

// ─── 4. Enqueue media on SS pages ─────────────────────────────
add_action('admin_enqueue_scripts', 'ss_enqueue_media_on_ss_pages');
function ss_enqueue_media_on_ss_pages($hook) {
    if (strpos($hook, 'shokhersrity') !== false || strpos($hook, 'ss-') !== false) {
        wp_enqueue_media();
    }
}

// ─── 5. Global admin CSS ───────────────────────────────────────
add_action('admin_head', 'ss_global_admin_css', 1);
function ss_global_admin_css() {
    $logo_url = get_option('ss_logo_url', '');
    echo '<style id="ss-admin-chrome">' . ss_get_admin_css() . '</style>';
    // Dynamic logo in sidebar when logo uploaded
    if ($logo_url) {
        echo '<style>
#adminmenu>li:first-child>.wp-menu-image{
  background-image:url("' . esc_url($logo_url) . '")!important;
  background-size:contain!important;background-repeat:no-repeat!important;
  background-position:center!important;
}
#adminmenu>li:first-child>.wp-menu-image::before{display:none!important;}
</style>';
    }
}

function ss_get_admin_css() {
    return '
/* ══ ShokherSrity Studio Admin v3 ══ */
html{background:#0a0805!important}
body.wp-admin{background:#f0ece4!important;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif!important}
#wpcontent,#wpbody{background:#f0ece4!important}
#wpfooter{display:none!important}

/* ── Admin Bar ── */
#wpadminbar{background:#0a0805!important;border-bottom:1px solid rgba(212,175,55,.1)!important}
#wpadminbar *{box-shadow:none!important}
#wpadminbar .ab-item,#wpadminbar a.ab-item{color:rgba(255,255,255,.55)!important}
#wpadminbar .ab-item:hover{color:#D4AF37!important;background:transparent!important}
#wp-admin-bar-wp-logo,#wp-admin-bar-site-editor,#wp-admin-bar-customize,
#wp-admin-bar-updates,#wp-admin-bar-comments,#wp-admin-bar-new-content,
#wp-admin-bar-wpseo-menu,#wp-admin-bar-debug-bar{display:none!important}
#wp-admin-bar-site-name a{color:#D4AF37!important;font-weight:600!important}
#wp-admin-bar-my-account .ab-item{color:rgba(255,255,255,.5)!important;font-size:.8rem!important}

/* ── Sidebar ── */
#adminmenuback,#adminmenuwrap{
  background:#0a0805!important;
  border-right:1px solid rgba(212,175,55,.08)!important;
  box-shadow:2px 0 20px rgba(0,0,0,.25)!important;
}

/* Brand top item */
#adminmenu>li:first-child>a.menu-top{
  background:linear-gradient(135deg,rgba(212,175,55,.14),rgba(212,175,55,.05))!important;
  border-bottom:1px solid rgba(212,175,55,.15)!important;
  padding:.85rem!important;margin-bottom:.35rem!important;
  border-radius:0!important;
}
#adminmenu>li:first-child>a.menu-top .wp-menu-name{
  color:#D4AF37!important;font-size:.88rem!important;font-weight:700!important;
  letter-spacing:.04em!important;
}

/* Menu links */
#adminmenu a,#adminmenu .wp-submenu a{
  color:rgba(255,255,255,.48)!important;font-size:.83rem!important;
  transition:color .15s,background .15s!important;
}
#adminmenu li a:hover{color:rgba(255,255,255,.9)!important;background:rgba(255,255,255,.04)!important}
#adminmenu .wp-menu-arrow{display:none!important}
#adminmenu .wp-menu-image{opacity:.5}

/* Active */
#adminmenu li.wp-has-current-submenu>a,
#adminmenu li.current>a{color:#D4AF37!important;background:rgba(212,175,55,.08)!important}
#adminmenu .wp-submenu li.current a{color:#D4AF37!important}
#adminmenu li.wp-has-current-submenu .wp-menu-image,
#adminmenu li.current .wp-menu-image{opacity:1!important}

/* Submenu */
#adminmenu .wp-submenu{
  background:rgba(0,0,0,.32)!important;
  border-left:2px solid rgba(212,175,55,.1)!important;
  margin-left:4px!important;border-radius:0 0 6px 6px!important;
}
#adminmenu .wp-submenu a{
  color:rgba(255,255,255,.42)!important;
  padding:.45rem 1rem .45rem 1.5rem!important;font-size:.8rem!important;
}
#adminmenu .wp-submenu a:hover{color:#D4AF37!important;background:rgba(212,175,55,.05)!important}
#adminmenu .wp-submenu li.current a{color:#D4AF37!important}

/* No separators */
#adminmenu .separator,#adminmenu .wp-menu-separator{display:none!important}

/* WP notices cleanup */
.notice:not(.notice-error):not(.ss-notice),
.update-nag,.updated:not(.ss-notice),.notice-warning:not(.ss-notice){display:none!important}
#screen-meta,#screen-options-link-wrap,#contextual-help-link-wrap{display:none!important}
.wrap>h1.wp-heading-inline,.wrap>h1:first-child{display:none!important}

/* Content area */
#wpcontent{padding-left:12px!important}
#wpbody-content{padding-bottom:2rem!important}
#wpbody-content .wrap{margin-right:10px!important}

/* ── SS Design Tokens ── */
.ss-admin{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;}

/* Page header */
.ss-page-header{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;}
.ss-page-header-left{display:flex;align-items:center;gap:.85rem;}
.ss-page-icon-wrap{width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#0a0805,#1a120a);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,.2);}
.ss-page-title{font-size:1.25rem;font-weight:700;color:#1a120a;margin:0;line-height:1.2;}
.ss-page-subtitle{font-size:.78rem;color:#9b9490;margin:.15rem 0 0;}
.ss-page-header-right{display:flex;gap:.5rem;flex-wrap:wrap;}

/* Cards */
.ss-card{background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.04),0 4px 14px rgba(0,0,0,.05);padding:1.25rem 1.5rem;margin-bottom:1.25rem;border:1px solid #ede8e0;}
.ss-card h2{font-size:1rem;color:#1a120a;margin:0 0 1.1rem;padding-bottom:.65rem;border-bottom:1px solid #f0ece4;display:flex;align-items:center;gap:.5rem;}
.ss-card-header{display:flex;align-items:center;gap:.5rem;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#6b6460;margin-bottom:1rem;}

/* Buttons */
.ss-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.1rem;border-radius:8px;font-size:.84rem;font-weight:500;cursor:pointer;border:none;text-decoration:none;transition:all .18s cubic-bezier(.4,0,.2,1);line-height:1.4;white-space:nowrap;}
.ss-btn-primary{background:linear-gradient(135deg,#D4AF37,#c49d2e);color:#0a0805;}
.ss-btn-primary:hover{background:linear-gradient(135deg,#e6c347,#D4AF37);box-shadow:0 4px 14px rgba(212,175,55,.35);transform:translateY(-1px);color:#0a0805;}
.ss-btn-outline{background:#fff;color:#4a4040;border:1px solid #ddd;box-shadow:0 1px 3px rgba(0,0,0,.05);}
.ss-btn-outline:hover{border-color:#D4AF37;color:#1a120a;background:#fafaf5;}
.ss-btn-danger{background:#fff8f8;color:#c0392b;border:1px solid #f5c6c6;}
.ss-btn-danger:hover{background:#c0392b;color:#fff;border-color:#c0392b;}
.ss-btn-secondary{background:#f4f1eb;color:#4a4040;border:1px solid #ede8e0;}
.ss-btn-secondary:hover{background:#ede8e0;color:#1a120a;}
.ss-btn-sm{padding:.32rem .75rem!important;font-size:.76rem!important;}

/* Stats */
.ss-stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.25rem;}
.ss-stat-card-pro{background:#fff;border:1px solid #ede8e0;border-radius:12px;padding:1rem 1.1rem;display:flex;align-items:center;gap:.85rem;box-shadow:0 1px 3px rgba(0,0,0,.04);}
.ss-stat-icon-wrap{width:42px;height:42px;border-radius:10px;background:rgba(212,175,55,.08);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.ss-stat-body{flex:1;min-width:0;}
.ss-stat-value{font-size:1.55rem;font-weight:700;color:#1a120a;line-height:1;}
.ss-stat-label{font-size:.73rem;color:#9b9490;margin-top:.15rem;}
.ss-stat-link{font-size:.74rem;color:#D4AF37;text-decoration:none;font-weight:600;white-space:nowrap;}
.ss-stat-link:hover{color:#b8960c;}

/* Old stat grid (compat) */
.ss-stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;}
.ss-stat-card{background:linear-gradient(135deg,#0a0805,#1a120a);color:#fff;border-radius:10px;padding:1.2rem;text-align:center;}
.ss-stat-card .stat-val{font-size:1.9rem;font-weight:700;background:linear-gradient(135deg,#D4AF37,#F5D67B);-webkit-background-clip:text;background-clip:text;color:transparent;}
.ss-stat-card .stat-lbl{font-size:.78rem;color:rgba(255,255,255,.55);margin-top:.2rem;}

/* Layout */
.ss-two-col{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;}

/* Forms */
.ss-form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:.75rem;}
.ss-form-group{margin-bottom:.85rem;}
.ss-form-group label{display:block;font-size:.8rem;font-weight:600;color:#4a4040;margin-bottom:.3rem;}
.ss-form-group input,.ss-form-group textarea,.ss-form-group select{
  width:100%;padding:.52rem .8rem;border:1.5px solid #e0dbd2;border-radius:8px;
  font-size:.85rem;color:#1a120a;box-sizing:border-box;
  transition:border-color .2s,box-shadow .2s;background:#fff;
}
.ss-form-group input:focus,.ss-form-group textarea:focus,.ss-form-group select:focus{
  outline:none;border-color:#D4AF37;box-shadow:0 0 0 3px rgba(212,175,55,.12);
}
.ss-form-group textarea{resize:vertical;min-height:80px;}
.ss-field-hint{font-size:.74rem;color:#9b9490;line-height:1.4;}

/* Notices */
.ss-notice{padding:.7rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.86rem;}
.ss-notice-success{background:#f0fff4;color:#276749;border:1px solid #9ae6b4;}
.ss-notice-error{background:#fff5f5;color:#c53030;border:1px solid #feb2b2;}

/* Tables */
.ss-table{width:100%;border-collapse:collapse;font-size:.84rem;}
.ss-table th{text-align:left;padding:.55rem .9rem;background:#f8f5ef;color:#6b6460;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;border-bottom:2px solid #ede8e0;}
.ss-table td{padding:.65rem .9rem;border-bottom:1px solid #f0ece4;vertical-align:middle;}
.ss-table tr:hover td{background:rgba(212,175,55,.03);}
.ss-table tr:last-child td{border-bottom:none;}

/* Badges */
.ss-badge{display:inline-block;padding:.18rem .55rem;border-radius:20px;font-size:.72rem;font-weight:600;}
.ss-badge-gold{background:rgba(212,175,55,.13);color:#7a5c00;}
.ss-badge-dark{background:#1a120a;color:#D4AF37;}
.ss-badge-blue{background:#e3f2fd;color:#1565c0;}

/* Tab nav */
.ss-tab-nav{display:flex;gap:.25rem;background:#fff;border:1px solid #ede8e0;border-radius:10px;padding:.3rem;flex-wrap:wrap;}
.ss-tab-btn{display:flex;align-items:center;gap:.35rem;padding:.48rem .95rem;border:none;background:none;border-radius:7px;font-size:.81rem;font-weight:500;color:#6b6460;cursor:pointer;transition:all .15s;white-space:nowrap;}
.ss-tab-btn.active{background:linear-gradient(135deg,#D4AF37,#c49d2e);color:#0a0805;font-weight:600;}
.ss-tab-btn:hover:not(.active){background:#f4f1eb;color:#1a120a;}
.ss-settings-tab{display:none;}
.ss-settings-tab.active{display:block;}

/* Old tabs (compat) */
.ss-tabs{display:flex;gap:.4rem;margin-bottom:1.5rem;border-bottom:2px solid rgba(212,175,55,.1);padding-bottom:0;}
.ss-tab{padding:.6rem 1.1rem;border-radius:8px 8px 0 0;cursor:pointer;font-size:.87rem;font-weight:500;color:#888;background:none;border:none;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .18s;}
.ss-tab.active{color:#1a120a;border-bottom-color:#D4AF37;background:rgba(212,175,55,.05);}
.ss-tab:hover{color:#1a120a;}
.ss-tab-content{display:none;}
.ss-tab-content.active{display:block;}

/* Modals */
.ss-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:99998;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);}
.ss-modal{background:#fff;border-radius:16px;width:520px;max-width:95vw;max-height:90vh;overflow-y:auto;box-shadow:0 24px 64px rgba(0,0,0,.25);}
.ss-modal-header{display:flex;justify-content:space-between;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid #ede8e0;}
.ss-modal-title{font-size:1rem;font-weight:700;color:#1a120a;margin:0;}
.ss-modal-close{background:none;border:none;font-size:1.4rem;cursor:pointer;color:#9b9490;width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:6px;}
.ss-modal-close:hover{background:#f4f1eb;color:#1a120a;}
.ss-modal-body{padding:1.25rem 1.5rem;}
.ss-modal-footer{display:flex;justify-content:flex-end;gap:.6rem;padding:1rem 1.5rem;border-top:1px solid #ede8e0;background:#f9f7f3;border-radius:0 0 16px 16px;}

/* Upload areas */
.ss-upload-dropzone{border:2px dashed rgba(212,175,55,.35);border-radius:12px;padding:2rem;text-align:center;cursor:pointer;transition:all .2s;background:rgba(212,175,55,.025);}
.ss-upload-dropzone:hover,.ss-upload-dropzone.drag-over{border-color:#D4AF37;background:rgba(212,175,55,.07);}
.ss-upload-label{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border-radius:8px;background:#f4f1eb;border:1px solid #ede8e0;color:#4a4040;font-size:.82rem;font-weight:500;cursor:pointer;transition:all .15s;}
.ss-upload-label:hover{background:#ede8e0;border-color:#D4AF37;}

/* Progress bar */
.ss-progress-bar{height:4px;background:#f0ece4;border-radius:2px;overflow:hidden;}
.ss-progress-fill{height:100%;background:linear-gradient(90deg,#D4AF37,#F5D67B);border-radius:2px;width:0%;transition:width .3s;}

/* Logo area */
.ss-logo-area{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;}
.ss-logo-preview{border:2px dashed #ede8e0;border-radius:10px;padding:1.5rem;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:120px;background:#fafaf8;}
.ss-logo-preview-label{font-size:.72rem;color:#9b9490;margin-top:.5rem;}
.ss-logo-upload-area{display:flex;flex-direction:column;gap:.5rem;}

/* Hero */
.hero-pair-preview{display:grid;grid-template-columns:2fr 1fr;gap:1rem;margin-top:1rem;}
.hero-preview-slot{border-radius:10px;overflow:hidden;border:2px solid rgba(212,175,55,.18);min-height:140px;position:relative;background:#111;}
.hero-preview-slot img{width:100%;height:100%;object-fit:cover;display:block;}
.hero-preview-slot .slot-label{position:absolute;top:.4rem;left:.4rem;background:rgba(0,0,0,.7);color:#D4AF37;font-size:.65rem;padding:.18rem .45rem;border-radius:4px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;}

/* Filter chips */
.ss-filter-chips{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1rem;}
.ss-chip{padding:.32rem .85rem;border-radius:20px;font-size:.78rem;font-weight:500;text-decoration:none;background:#f4f1eb;color:#4a4040;border:1px solid #ede8e0;transition:all .15s;}
.ss-chip:hover,.ss-chip-active{background:linear-gradient(135deg,#D4AF37,#c49d2e);color:#0a0805;border-color:transparent;}

/* Gallery */
.ss-gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.75rem;}
.ss-image-card{position:relative;aspect-ratio:1;border-radius:10px;overflow:hidden;background:#111;border:2px solid transparent;transition:border-color .18s;}
.ss-image-card img{width:100%;height:100%;object-fit:cover;display:block;}
.ss-image-card:hover{border-color:#D4AF37;}
.ss-image-meta{position:absolute;bottom:0;left:0;right:0;background:linear-gradient(transparent,rgba(0,0,0,.75));padding:.4rem .5rem;font-size:.65rem;color:rgba(255,255,255,.85);}
.ss-image-overlay{position:absolute;inset:0;background:rgba(0,0,0,.65);opacity:0;transition:opacity .2s;display:flex;align-items:center;justify-content:center;gap:.4rem;flex-wrap:wrap;padding:.5rem;}
.ss-image-card:hover .ss-image-overlay{opacity:1;}
.ss-img-action-btn{display:inline-flex;align-items:center;gap:.3rem;padding:.35rem .65rem;border:none;border-radius:6px;background:rgba(255,255,255,.15);color:#fff;font-size:.72rem;cursor:pointer;backdrop-filter:blur(4px);transition:background .15s;}
.ss-img-action-btn:hover{background:rgba(212,175,55,.6);color:#0a0805;}
.ss-img-delete-btn:hover{background:rgba(220,38,38,.6)!important;color:#fff!important;}

/* Video list */
.ss-video-list{display:flex;flex-direction:column;gap:.75rem;}
.ss-video-row{display:grid;grid-template-columns:160px 1fr auto;gap:1rem;align-items:center;padding:.9rem;background:#f9f7f3;border-radius:10px;border:1px solid #ede8e0;transition:border-color .18s;}
.ss-video-row:hover{border-color:rgba(212,175,55,.3);}
.ss-video-thumb-wrap{position:relative;}
.ss-video-thumb{width:160px;height:90px;object-fit:cover;border-radius:8px;background:#111;display:block;}
.ss-video-num{position:absolute;top:.35rem;left:.35rem;background:rgba(0,0,0,.7);color:#D4AF37;font-size:.68rem;font-weight:700;padding:.12rem .4rem;border-radius:4px;}
.ss-video-title{font-weight:600;font-size:.9rem;color:#1a120a;margin-bottom:.2rem;}
.ss-video-desc{font-size:.82rem;color:#6b6460;margin-bottom:.4rem;}
.ss-video-meta{font-size:.72rem;color:#aaa;}
.ss-video-actions{display:flex;flex-direction:column;gap:.35rem;align-items:stretch;min-width:80px;}
.ss-move-btn{justify-content:center!important;padding:.3rem!important;}
.ss-code-small{font-size:.7rem;background:#f4f1eb;padding:.1rem .35rem;border-radius:4px;font-family:monospace;}
.ss-video-edit-form .ss-form-group label{font-size:.75rem;}
.ss-video-edit-form .ss-form-group{margin-bottom:.5rem;}

/* Old video compat */
.ss-video-card{display:grid;grid-template-columns:180px 1fr auto;gap:1rem;align-items:center;padding:1rem;background:#fafaf8;border-radius:10px;border:1px solid rgba(212,175,55,.08);}
.ss-video-card video{width:180px;height:102px;object-fit:cover;border-radius:8px;background:#111;}
.ss-video-actions-old{display:flex;flex-direction:column;gap:.45rem;}

/* Packages */
.ss-pkg-card{background:#f9f7f3;border:1px solid #ede8e0;border-radius:10px;padding:1rem;margin-bottom:.75rem;}
.ss-pkg-card:last-child{margin-bottom:0;}
.ss-pkg-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.75rem;}
.ss-pkg-header-left{flex:1;}
.ss-pkg-name{font-weight:700;font-size:.95rem;color:#1a120a;}
.ss-pkg-price{font-size:.82rem;color:#D4AF37;font-weight:600;margin-top:.1rem;}
.ss-pkg-features-preview{display:flex;flex-wrap:wrap;gap:.3rem;}
.ss-feat-chip{display:inline-block;padding:.18rem .55rem;background:#fff;border:1px solid #ede8e0;border-radius:5px;font-size:.72rem;color:#4a4040;}

/* Empty state */
.ss-empty-state{text-align:center;padding:3rem 1rem;background:#fff;border-radius:12px;border:1px solid #ede8e0;}
.ss-empty-state p{color:#9b9490;font-size:.88rem;margin:.75rem 0 0;}

/* Toast */
.ss-toast{position:fixed;top:80px;right:20px;z-index:999999;padding:.75rem 1.25rem;border-radius:10px;font-size:.88rem;font-weight:500;box-shadow:0 8px 24px rgba(0,0,0,.2);animation:ss-slide-in .25s ease;}
.ss-toast-success{background:#0a0805;color:#D4AF37;border:1px solid rgba(212,175,55,.3);}
.ss-toast-error{background:#fff5f5;color:#c53030;border:1px solid #feb2b2;}
@keyframes ss-slide-in{from{transform:translateX(20px);opacity:0;}to{transform:translateX(0);opacity:1;}}

/* Spinner */
.spinner{display:inline-block;width:15px;height:15px;border:2px solid rgba(212,175,55,.3);border-top-color:#D4AF37;border-radius:50%;animation:ss-spin .7s linear infinite;vertical-align:middle;}
@keyframes ss-spin{to{transform:rotate(360deg)}}

/* Stat editor */
.ss-stat-editor{background:#f9f7f3;border:1px solid #ede8e0;border-radius:8px;padding:1rem;}
.ss-stat-editor-label{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9b9490;margin-bottom:.75rem;}

/* Checkbox */
.ss-checkbox-label{display:flex;align-items:center;gap:.4rem;font-size:.83rem;cursor:pointer;}

/* Update status grid */
.ss-update-status-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;}
.ss-update-card{display:flex;flex-direction:column;gap:.75rem;padding:1.25rem!important;}
.ss-update-available{border-color:rgba(212,175,55,.35)!important;}
.ss-update-ok{border-color:#ede8e0!important;}
.ss-update-card-icon{width:44px;height:44px;border-radius:10px;background:#f4f1eb;display:flex;align-items:center;justify-content:center;color:#6b6460;}
.ss-update-available .ss-update-card-icon{background:rgba(212,175,55,.1);color:#D4AF37;}
.ss-update-card-title{font-size:.85rem;font-weight:700;color:#1a120a;}
.ss-update-card-ver{font-size:.78rem;color:#9b9490;margin-top:.15rem;}
.ss-update-badge{display:inline-block;padding:.2rem .6rem;border-radius:20px;font-size:.72rem;font-weight:600;margin-top:.35rem;}
.ss-badge-ok{background:#f0fff4;color:#276749;}
.ss-badge-warn{background:rgba(212,175,55,.12);color:#7a5c00;}
.ss-update-btn{margin-top:.25rem;font-size:.8rem!important;padding:.45rem .9rem!important;}

/* Security list */
.ss-section-divider{height:1px;background:#ede8e0;margin:1rem 0;}
.ss-section-subtitle{font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9b9490;margin:0 0 .75rem;}
.ss-security-list{display:grid;grid-template-columns:repeat(2,1fr);gap:.4rem;}
.ss-security-item{display:flex;align-items:center;gap:.5rem;padding:.45rem .6rem;background:#f9f7f3;border-radius:7px;font-size:.8rem;}

/* User avatar */
.ss-avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;font-weight:700;flex-shrink:0;opacity:.85;}

/* Action grid */
.ss-action-grid{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;}
.ss-action-tile{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.5rem;padding:1.1rem .75rem;border-radius:10px;text-decoration:none;font-size:.8rem;font-weight:500;transition:all .18s;border:1px solid transparent;}
.ss-action-primary{background:linear-gradient(135deg,rgba(212,175,55,.12),rgba(212,175,55,.06));color:#1a120a;border-color:rgba(212,175,55,.2);}
.ss-action-primary:hover{background:linear-gradient(135deg,rgba(212,175,55,.22),rgba(212,175,55,.12));border-color:#D4AF37;color:#0a0805;}
.ss-action-secondary{background:#f8f5ef;color:#4a4040;border-color:#ede8e0;}
.ss-action-secondary:hover{background:#f0ece4;color:#1a120a;}

/* Category list */
.ss-category-list{display:flex;flex-direction:column;gap:.5rem;}
.ss-cat-row{display:flex;align-items:center;gap:.75rem;}
.ss-cat-name{font-size:.82rem;color:#4a4040;width:90px;flex-shrink:0;}
.ss-cat-bar-wrap{flex:1;height:6px;background:#f0ece4;border-radius:3px;overflow:hidden;}
.ss-cat-bar{height:100%;background:linear-gradient(90deg,#D4AF37,#F5D67B);border-radius:3px;}
.ss-cat-count{font-size:.75rem;color:#9b9490;width:30px;text-align:right;flex-shrink:0;}

/* Reel thumbnails */
.ss-reel-thumbs{display:flex;gap:.85rem;flex-wrap:wrap;}
.ss-reel-thumb{text-align:center;}
.ss-reel-thumb video{width:140px;height:79px;object-fit:cover;border-radius:8px;background:#111;display:block;}
.ss-reel-thumb p{font-size:.7rem;color:#6b6460;margin:.25rem 0 0;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}

/* Hidden util */
.ss-hidden{display:none!important;}

/* ── Responsive ── */
@media (max-width:900px){
  .ss-stats-row{grid-template-columns:1fr 1fr;}
  .ss-two-col{grid-template-columns:1fr;}
  .ss-update-status-grid{grid-template-columns:1fr 1fr;}
  .ss-security-list{grid-template-columns:1fr;}
  .ss-form-row{grid-template-columns:1fr;}
  .ss-logo-area{grid-template-columns:1fr;}
  .ss-video-row{grid-template-columns:120px 1fr;}.ss-video-thumb{width:120px;height:68px;}
  .ss-gallery-grid{grid-template-columns:repeat(auto-fill,minmax(110px,1fr));}
}
@media (max-width:600px){
  .ss-stats-row{grid-template-columns:1fr;}
  .ss-page-header{flex-direction:column;align-items:flex-start;}
  .ss-update-status-grid{grid-template-columns:1fr;}
  .ss-tab-btn span{display:none;}
  .ss-video-row{grid-template-columns:1fr;}.ss-video-thumb{width:100%;height:auto;}
}
';
}

// ─── 6. Disable Gutenberg ──────────────────────────────────────
add_filter('use_block_editor_for_post',      '__return_false');
add_filter('use_block_editor_for_post_type', '__return_false');

// ─── 7. Hide WP admin bar on frontend ─────────────────────────
add_filter('show_admin_bar', '__return_false');

// ─── 8. Suppress update nag notices ───────────────────────────
add_action('admin_head', function() {
    remove_action('admin_notices', 'update_nag');
    remove_action('admin_notices', 'maintenance_nag');
}, 1);

// ─── 9. Branded login page ─────────────────────────────────────
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

// ─── 10. Remove plugin install links ──────────────────────────
add_filter('plugin_action_links',  '__return_empty_array');
add_filter('install_plugins_tabs', '__return_empty_array');

// ─── 11. Security hardening hooks ─────────────────────────────
// Disable XML-RPC
add_filter('xmlrpc_enabled', '__return_false');
add_filter('wp_headers', 'ss_add_security_headers');
function ss_add_security_headers($headers) {
    $headers['X-Content-Type-Options']    = 'nosniff';
    $headers['X-Frame-Options']           = 'SAMEORIGIN';
    $headers['X-XSS-Protection']          = '1; mode=block';
    $headers['Referrer-Policy']           = 'strict-origin-when-cross-origin';
    $headers['Permissions-Policy']        = 'camera=(), microphone=(), geolocation=()';
    return $headers;
}
// Hide WP version from frontend
remove_action('wp_head', 'wp_generator');
add_filter('the_generator', '__return_empty_string');
// Disable author enumeration
add_action('template_redirect', function() {
    if (is_author()) { wp_redirect(home_url('/'), 301); exit; }
});

// ─── 12. Dynamic favicon from ss_logo_url ─────────────────────
add_action('wp_head', 'ss_dynamic_favicon', 1);
add_action('admin_head', 'ss_dynamic_favicon', 1);
function ss_dynamic_favicon() {
    $fav = get_option('ss_favicon_url', '');
    $logo = get_option('ss_logo_url', '');
    $url  = $fav ?: $logo;
    if ($url) {
        echo '<link rel="icon" href="' . esc_url($url) . '" type="image/png">' . PHP_EOL;
        echo '<link rel="apple-touch-icon" href="' . esc_url($url) . '">' . PHP_EOL;
    }
}

// ─── 13. Sitemap ───────────────────────────────────────────────
add_action('init', 'ss_register_sitemap_rewrite');
function ss_register_sitemap_rewrite() {
    add_rewrite_rule('^sitemap\.xml$', 'index.php?ss_sitemap=1', 'top');
    add_rewrite_tag('%ss_sitemap%', '1');
}
add_action('template_redirect', 'ss_serve_sitemap');
function ss_serve_sitemap() {
    if (!get_query_var('ss_sitemap')) return;
    $home = home_url('/');
    $pages = [
        ['loc' => $home,                        'priority' => '1.0', 'freq' => 'weekly'],
        ['loc' => $home . 'gallery/',           'priority' => '0.9', 'freq' => 'weekly'],
        ['loc' => $home . 'reels/',             'priority' => '0.8', 'freq' => 'weekly'],
        ['loc' => $home . 'packages/',          'priority' => '0.9', 'freq' => 'monthly'],
        ['loc' => $home . 'contact/',           'priority' => '0.7', 'freq' => 'yearly'],
    ];
    // Add any WP pages
    $wp_pages = get_pages(['post_status' => 'publish']);
    $slugs_done = ['gallery', 'reels', 'packages', 'contact'];
    foreach ($wp_pages as $p) {
        if (!in_array($p->post_name, $slugs_done)) {
            $pages[] = ['loc' => get_permalink($p), 'priority' => '0.6', 'freq' => 'monthly'];
        }
    }
    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
    $now = date('Y-m-d');
    foreach ($pages as $p) {
        echo "  <url>\n";
        echo "    <loc>" . esc_url($p['loc']) . "</loc>\n";
        echo "    <lastmod>{$now}</lastmod>\n";
        echo "    <changefreq>{$p['freq']}</changefreq>\n";
        echo "    <priority>{$p['priority']}</priority>\n";
        echo "  </url>\n";
    }
    echo '</urlset>';
    exit;
}

// ─── Admin Page Callbacks ──────────────────────────────────────
function ss_admin_dashboard() { include SS_PLUGIN_DIR . 'admin/dashboard.php'; }
function ss_admin_gallery()   { include SS_PLUGIN_DIR . 'admin/gallery-admin.php'; }
function ss_admin_videos()    { include SS_PLUGIN_DIR . 'admin/videos-admin.php'; }
function ss_admin_packages()  { include SS_PLUGIN_DIR . 'admin/packages-admin.php'; }
function ss_admin_settings()  { include SS_PLUGIN_DIR . 'admin/settings-admin.php'; }
function ss_admin_users()     { include SS_PLUGIN_DIR . 'admin/users-admin.php'; }
function ss_admin_updates()   { include SS_PLUGIN_DIR . 'admin/updates-admin.php'; }

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
    $folder    = $folders[$category] ?? 'gallery';
    $dest_dir  = SS_UPLOADS . '/' . $folder . '/';
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
            $ow = imagesx($img); $oh = imagesy($img); $max = 2000;
            if ($ow > $max || $oh > $max) {
                $ratio   = $ow > $oh ? $max / $ow : $max / $oh;
                $nw      = (int)round($ow * $ratio);
                $nh      = (int)round($oh * $ratio);
                $resized = imagecreatetruecolor($nw, $nh);
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $ow, $oh);
                imagedestroy($img); $img = $resized;
            }
            if (function_exists('imagewebp')) {
                $new_path = preg_replace('/\.(?:jpe?g|png|webp)$/i', '.webp', $dest_path);
                imagewebp($img, $new_path, 85);
                if ($new_path !== $dest_path) { @unlink($dest_path); $dest_path = $new_path; $filename = basename($dest_path); }
            } else {
                imagejpeg($img, $dest_path, 88);
            }
            imagedestroy($img);
        }
    }

    $info = @getimagesize($dest_path);
    $entry = [
        'id'       => uniqid('img_'),
        'src'      => '/wp-content/uploads/' . $folder . '/' . $filename,
        'category' => $category,
        'label'    => $label,
        'width'    => $info[0] ?? 0,
        'height'   => $info[1] ?? 0,
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
    $id = sanitize_text_field($_POST['id'] ?? '');
    if (!$id) wp_send_json_error('No ID');
    $catalog = (array) get_option('ss_image_catalog', []);
    $new = []; $del_src = '';
    foreach ($catalog as $item) {
        if ($item['id'] === $id) { $del_src = $item['src']; } else { $new[] = $item; }
    }
    if ($del_src) { $fp = ABSPATH . ltrim($del_src, '/'); if (file_exists($fp)) @unlink($fp); }
    update_option('ss_image_catalog', $new, false);
    wp_send_json_success(['deleted' => $id]);
}

// ─── Update Hero Images ───────────────────────────────────────
add_action('wp_ajax_ss_update_hero', 'ss_ajax_update_hero');
function ss_ajax_update_hero() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');
    $desktop = sanitize_text_field($_POST['desktop'] ?? '');
    $mobile  = sanitize_text_field($_POST['mobile']  ?? '');
    if (!empty($_FILES['desktop_file'])) { $p = ss_save_hero_upload('desktop_file', 'hero-desktop'); if ($p) $desktop = $p; }
    if (!empty($_FILES['mobile_file']))  { $p = ss_save_hero_upload('mobile_file',  'hero-mobile');  if ($p) $mobile  = $p; }
    $hero = ['desktop' => $desktop, 'mobile' => $mobile];
    update_option('ss_hero_images', $hero);
    wp_send_json_success($hero);
}
function ss_save_hero_upload($file_key, $dest_name) {
    $file = $_FILES[$file_key];
    $allowed = ['image/webp', 'image/jpeg', 'image/png'];
    if (!in_array($file['type'], $allowed)) return false;
    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'webp';
    wp_mkdir_p(SS_UPLOADS . '/hero/');
    $dest = SS_UPLOADS . '/hero/' . $dest_name . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return '/wp-content/uploads/hero/' . $dest_name . '.' . $ext;
    }
    return false;
}

// ─── Upload Logo ──────────────────────────────────────────────
add_action('wp_ajax_ss_upload_logo', 'ss_ajax_upload_logo');
function ss_ajax_upload_logo() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    // Remove logo
    if (!empty($_POST['remove'])) {
        $old = get_option('ss_logo_url', '');
        if ($old) { $fp = ABSPATH . ltrim(str_replace(home_url('/'), '', $old), '/'); if (file_exists($fp)) @unlink($fp); }
        delete_option('ss_logo_url');
        delete_option('ss_favicon_url');
        wp_send_json_success(['removed' => true]);
    }

    if (empty($_FILES['logo'])) wp_send_json_error('No file received');
    $file = $_FILES['logo'];
    $allowed_types  = ['image/png','image/jpeg','image/webp','image/svg+xml'];
    $allowed_exts   = ['png','jpg','jpeg','webp','svg'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'png');
    if (!in_array($file['type'], $allowed_types) || !in_array($ext, $allowed_exts)) {
        wp_send_json_error('Only PNG, JPEG, WebP, SVG allowed');
    }

    $logo_dir = SS_UPLOADS . '/logo/';
    wp_mkdir_p($logo_dir);

    $logo_filename = 'logo-' . time() . '.' . $ext;
    $logo_path     = $logo_dir . $logo_filename;

    if (!move_uploaded_file($file['tmp_name'], $logo_path)) {
        wp_send_json_error('Failed to save logo');
    }

    // Optimize non-SVG logo
    if ($ext !== 'svg' && function_exists('imagecreatefromstring')) {
        $orig = @file_get_contents($logo_path);
        $img  = $orig ? @imagecreatefromstring($orig) : false;
        if ($img) {
            $ow = imagesx($img); $oh = imagesy($img); $max = 600;
            if ($ow > $max || $oh > $max) {
                $ratio = $ow > $oh ? $max/$ow : $max/$oh;
                $nw = (int)round($ow*$ratio); $nh = (int)round($oh*$ratio);
                $r  = imagecreatetruecolor($nw, $nh);
                imagealphablending($r, false); imagesavealpha($r, true);
                $trans = imagecolorallocatealpha($r, 0, 0, 0, 127);
                imagefilledrectangle($r, 0, 0, $nw, $nh, $trans);
                imagecopyresampled($r, $img, 0, 0, 0, 0, $nw, $nh, $ow, $oh);
                imagedestroy($img); $img = $r;
            }
            // Save as PNG (preserves transparency)
            $new_path = $logo_dir . 'logo-' . time() . '.png';
            imagepng($img, $new_path, 6);
            @unlink($logo_path);
            $logo_path = $new_path;
            $logo_filename = basename($logo_path);
            imagedestroy($img);
        }
    }

    $logo_url = content_url('uploads/logo/' . $logo_filename);
    update_option('ss_logo_url', $logo_url);

    // Generate 32x32 favicon PNG
    if ($ext !== 'svg' && function_exists('imagecreatefromstring')) {
        $src = @imagecreatefromstring(file_get_contents($logo_path));
        if ($src) {
            $fav = imagecreatetruecolor(64, 64);
            imagealphablending($fav, false); imagesavealpha($fav, true);
            $trans = imagecolorallocatealpha($fav, 0, 0, 0, 127);
            imagefilledrectangle($fav, 0, 0, 64, 64, $trans);
            imagecopyresampled($fav, $src, 0, 0, 0, 0, 64, 64, imagesx($src), imagesy($src));
            $fav_path = $logo_dir . 'favicon.png';
            imagepng($fav, $fav_path);
            imagedestroy($src); imagedestroy($fav);
            update_option('ss_favicon_url', content_url('uploads/logo/favicon.png'));
        }
    } elseif ($ext === 'svg') {
        update_option('ss_favicon_url', $logo_url);
    }

    wp_send_json_success(['url' => $logo_url]);
}

// ─── Upload Video ─────────────────────────────────────────────
add_action('wp_ajax_ss_upload_video', 'ss_ajax_upload_video');
function ss_ajax_upload_video() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');
    if (empty($_FILES['video'])) wp_send_json_error('No file received');
    $file = $_FILES['video'];
    if ($file['type'] !== 'video/mp4') wp_send_json_error('Only MP4 allowed');
    $title    = sanitize_text_field($_POST['title']       ?? 'Wedding Reel');
    $desc     = sanitize_text_field($_POST['description'] ?? '');
    $dest_dir = SS_UPLOADS . '/videos/';
    wp_mkdir_p($dest_dir);
    $tmp_name = $dest_dir . 'tmp_' . uniqid() . '.mp4';
    if (!move_uploaded_file($file['tmp_name'], $tmp_name)) wp_send_json_error('Failed to save upload');
    $final_name = uniqid('vid_') . '.mp4';
    $final_path = $dest_dir . $final_name;
    $ffmpeg = trim(shell_exec('which ffmpeg 2>/dev/null') ?: '/run/current-system/sw/bin/ffmpeg');
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
    $videos = (array) get_option('ss_videos', []);
    $videos[] = $entry;
    update_option('ss_videos', $videos, false);
    wp_send_json_success($entry);
}

// ─── Update / Delete / Reorder Video ─────────────────────────
add_action('wp_ajax_ss_update_video', 'ss_ajax_update_video');
function ss_ajax_update_video() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');
    $id = sanitize_text_field($_POST['id'] ?? '');
    $videos = (array) get_option('ss_videos', []);
    foreach ($videos as &$v) {
        if ($v['id'] === $id) {
            $v['title']       = sanitize_text_field($_POST['title']       ?? $v['title']);
            $v['description'] = sanitize_text_field($_POST['description'] ?? $v['description']);
            break;
        }
    }
    update_option('ss_videos', $videos, false);
    wp_send_json_success(['id' => $id]);
}

add_action('wp_ajax_ss_delete_video', 'ss_ajax_delete_video');
function ss_ajax_delete_video() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');
    $id = sanitize_text_field($_POST['id'] ?? '');
    $videos = (array) get_option('ss_videos', []);
    $new = [];
    foreach ($videos as $v) {
        if ($v['id'] === $id) { $p = ABSPATH . ltrim($v['src'], '/'); if (file_exists($p)) @unlink($p); }
        else $new[] = $v;
    }
    update_option('ss_videos', $new, false);
    wp_send_json_success(['deleted' => $id]);
}

add_action('wp_ajax_ss_reorder_videos', 'ss_ajax_reorder_videos');
function ss_ajax_reorder_videos() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');
    $order   = array_map('sanitize_text_field', (array)($_POST['order'] ?? []));
    $videos  = (array) get_option('ss_videos', []);
    $indexed = []; foreach ($videos as $v) $indexed[$v['id']] = $v;
    $new = []; foreach ($order as $id) { if (isset($indexed[$id])) $new[] = $indexed[$id]; }
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
            'id'      => sanitize_key($t['id']    ?? ''),
            'label'   => sanitize_text_field($t['label']    ?? ''),
            'subtitle'=> sanitize_text_field($t['subtitle'] ?? ''),
            'columns' => (int)($t['columns'] ?? 3),
        ];
    }
    foreach ((array)($data['packages'] ?? []) as $p) {
        $feats = [];
        foreach ((array)($p['features'] ?? []) as $f) $feats[] = sanitize_text_field($f);
        $clean['packages'][] = [
            'id'                 => sanitize_key($p['id']    ?? ''),
            'tier'               => sanitize_key($p['tier']  ?? ''),
            'name'               => sanitize_text_field($p['name']     ?? ''),
            'price'              => sanitize_text_field($p['price']    ?? ''),
            'period'             => sanitize_text_field($p['period']   ?? '/ day'),
            'note'               => sanitize_text_field($p['note']     ?? ''),
            'badge'              => sanitize_text_field($p['badge']    ?? ''),
            'is_popular'         => (bool)($p['is_popular'] ?? false),
            'style'              => sanitize_key($p['style'] ?? 'standard'),
            'features'           => $feats,
            'complementary_note' => sanitize_text_field($p['complementary_note'] ?? ''),
            'whatsapp_message'   => sanitize_text_field($p['whatsapp_message']   ?? ''),
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
    $indexed = []; foreach ($catalog as $item) $indexed[$item['id']] = $item;
    $new = []; foreach ($order as $id) { if (isset($indexed[$id])) $new[] = $indexed[$id]; }
    foreach ($catalog as $item) { if (!in_array($item['id'], $order)) $new[] = $item; }
    update_option('ss_image_catalog', $new, false);
    wp_send_json_success(['count' => count($new)]);
}

// ═══════════════════════════════════════════════════════════════
// ADMIN-POST HANDLERS (form submissions)
// ═══════════════════════════════════════════════════════════════

// ─── Create User ──────────────────────────────────────────────
add_action('admin_post_ss_create_user_action', 'ss_handle_create_user');
function ss_handle_create_user() {
    if (!current_user_can('manage_options')) wp_die('Unauthorized');
    check_admin_referer('ss_create_user', '_create_nonce');
    $username = sanitize_user($_POST['username'] ?? '');
    $email    = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = sanitize_key($_POST['role'] ?? 'subscriber');
    $display  = sanitize_text_field($_POST['display_name'] ?? $username);
    if (!$username || !$email || strlen($password) < 8) {
        wp_redirect(admin_url('admin.php?page=ss-users&err=' . urlencode('Invalid input or password too short')));
        exit;
    }
    $uid = wp_create_user($username, $password, $email);
    if (is_wp_error($uid)) {
        wp_redirect(admin_url('admin.php?page=ss-users&err=' . urlencode($uid->get_error_message())));
        exit;
    }
    wp_update_user(['ID' => $uid, 'display_name' => $display, 'role' => $role]);
    wp_redirect(admin_url('admin.php?page=ss-users&created=1'));
    exit;
}

// ─── Edit User ────────────────────────────────────────────────
add_action('admin_post_ss_edit_user_action', 'ss_handle_edit_user');
function ss_handle_edit_user() {
    if (!current_user_can('manage_options')) wp_die('Unauthorized');
    check_admin_referer('ss_edit_user', '_edit_nonce');
    $uid      = (int)($_POST['uid'] ?? 0);
    $email    = sanitize_email($_POST['email'] ?? '');
    $role     = sanitize_key($_POST['role'] ?? '');
    $display  = sanitize_text_field($_POST['display_name'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$uid || !get_user_by('id', $uid)) {
        wp_redirect(admin_url('admin.php?page=ss-users&err=' . urlencode('User not found')));
        exit;
    }
    $data = ['ID' => $uid];
    if ($email)    $data['user_email']   = $email;
    if ($display)  $data['display_name'] = $display;
    if ($role)     $data['role']         = $role;
    if (strlen($password) >= 8) $data['user_pass'] = $password;
    wp_update_user($data);
    wp_redirect(admin_url('admin.php?page=ss-users&updated=1'));
    exit;
}

// ─── Delete User ──────────────────────────────────────────────
add_action('admin_post_ss_delete_user_action', 'ss_handle_delete_user');
function ss_handle_delete_user() {
    if (!current_user_can('manage_options')) wp_die('Unauthorized');
    $uid = (int)($_POST['uid'] ?? 0);
    check_admin_referer('ss_delete_user_' . $uid, '_del_nonce');
    if ($uid === get_current_user_id()) {
        wp_redirect(admin_url('admin.php?page=ss-users&err=' . urlencode('Cannot delete your own account')));
        exit;
    }
    require_once ABSPATH . 'wp-admin/includes/user.php';
    wp_delete_user($uid, 1);
    wp_redirect(admin_url('admin.php?page=ss-users&deleted=1'));
    exit;
}

// ─── Check for Updates ────────────────────────────────────────
add_action('admin_post_ss_check_updates_action', 'ss_handle_check_updates');
function ss_handle_check_updates() {
    if (!current_user_can('manage_options')) wp_die('Unauthorized');
    check_admin_referer('ss_check_updates_action', '_check_nonce');
    delete_site_transient('update_core');
    delete_site_transient('update_plugins');
    delete_site_transient('update_themes');
    wp_version_check();
    wp_update_plugins();
    wp_update_themes();
    wp_redirect(admin_url('admin.php?page=ss-updates'));
    exit;
}

// ─── Create Backup ────────────────────────────────────────────
add_action('admin_post_ss_create_backup_action', 'ss_handle_create_backup');
function ss_handle_create_backup() {
    if (!current_user_can('manage_options') || get_current_user_id() !== 1) wp_die('Unauthorized');
    check_admin_referer('ss_create_backup_action', '_bkp_nonce');
    global $wp_version;
    $backup_dir = WP_CONTENT_DIR . '/ss-backups/';
    wp_mkdir_p($backup_dir);
    $id = 'backup_' . date('Ymd_His');
    $data = [
        'id'          => $id,
        'created_at'  => date('Y-m-d H:i:s'),
        'wp_version'  => $wp_version,
        'image_count' => count((array)get_option('ss_image_catalog', [])),
        'video_count' => count((array)get_option('ss_videos', [])),
        'catalog'     => get_option('ss_image_catalog', []),
        'videos'      => get_option('ss_videos', []),
        'packages'    => get_option('ss_packages', []),
        'settings'    => get_option('ss_settings', []),
        'hero'        => get_option('ss_hero_images', []),
        'logo_url'    => get_option('ss_logo_url', ''),
    ];
    file_put_contents($backup_dir . $id . '.json', json_encode($data, JSON_PRETTY_PRINT));
    wp_redirect(admin_url('admin.php?page=ss-updates'));
    exit;
}

// ─── Restore Backup ───────────────────────────────────────────
add_action('admin_post_ss_restore_backup_action', 'ss_handle_restore_backup');
function ss_handle_restore_backup() {
    if (!current_user_can('manage_options') || get_current_user_id() !== 1) wp_die('Unauthorized');
    check_admin_referer('ss_restore_backup_action', '_rst_nonce');
    $id   = sanitize_key($_POST['backup_id'] ?? '');
    $file = WP_CONTENT_DIR . '/ss-backups/' . $id . '.json';
    if (!file_exists($file)) wp_die('Backup file not found');
    $data = json_decode(file_get_contents($file), true);
    if (!$data) wp_die('Invalid backup file');
    if (!empty($data['catalog']))  update_option('ss_image_catalog', $data['catalog'],  false);
    if (!empty($data['videos']))   update_option('ss_videos',        $data['videos'],   false);
    if (!empty($data['packages'])) update_option('ss_packages',      $data['packages']);
    if (!empty($data['settings'])) update_option('ss_settings',      $data['settings']);
    if (!empty($data['hero']))     update_option('ss_hero_images',   $data['hero']);
    if (!empty($data['logo_url'])) update_option('ss_logo_url',      $data['logo_url']);
    wp_redirect(admin_url('admin.php?page=ss-updates'));
    exit;
}

// ─── Run WP Core Update ───────────────────────────────────────
add_action('admin_post_ss_run_update_action', 'ss_handle_run_update');
function ss_handle_run_update() {
    if (!current_user_can('manage_options') || get_current_user_id() !== 1) wp_die('Unauthorized');
    check_admin_referer('ss_run_update_action', '_upd_nonce');
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    require_once ABSPATH . 'wp-admin/includes/update.php';
    $updates = get_site_transient('update_core');
    if (empty($updates->updates)) { wp_redirect(admin_url('admin.php?page=ss-updates')); exit; }
    foreach ($updates->updates as $update) {
        if ($update->response === 'upgrade') {
            $upgrader = new Core_Upgrader();
            $upgrader->upgrade($update);
            break;
        }
    }
    wp_redirect(admin_url('admin.php?page=ss-updates'));
    exit;
}
