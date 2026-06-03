<?php
/**
 * Plugin Name: ShokherSrity Core
 * Description: Core functionality for ShokherSrity — image catalog, video management, packages & settings.
 * Version: 1.0.0
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
        // Convert "attached_assets/X" → "/wp-content/uploads/X"
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
    $videos_url = SS_UPLOADS_URL . '/videos/';
    $default_videos = [
        ['file' => 'reel1.mp4', 'title' => 'A Timeless Love Story', 'description' => 'Captured in every frame — a day you\'ll never forget ✨'],
        ['file' => 'reel2.mp4', 'title' => 'Cinematic Wedding Highlights', 'description' => 'An elegant dance of love through our lens 💍'],
        ['file' => 'reel3.mp4', 'title' => 'Moments That Last Forever', 'description' => 'Every smile, every tear, every precious moment 🌸'],
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

// ─── Admin Menu ──────────────────────────────────────────────
add_action('admin_menu', 'ss_register_admin_menu');
function ss_register_admin_menu() {
    // Remove noisy default menus
    remove_menu_page('edit.php');            // Posts
    remove_menu_page('edit-comments.php');   // Comments
    remove_menu_page('edit.php?post_type=page'); // Pages (we handle pages in setup)
    remove_menu_page('themes.php');          // Appearance
    remove_menu_page('plugins.php');         // Plugins
    remove_menu_page('tools.php');           // Tools
    remove_menu_page('link-manager.php');    // Links

    // Add ShokherSrity top-level menu
    add_menu_page(
        'ShokherSrity Studio',
        '✦ ShokherSrity',
        'manage_options',
        'shokhersrity',
        'ss_admin_dashboard',
        'none',
        2
    );
    add_submenu_page('shokhersrity', 'Dashboard',      'Dashboard',      'manage_options', 'shokhersrity',   'ss_admin_dashboard');
    add_submenu_page('shokhersrity', 'Gallery Manager','Gallery',        'manage_options', 'ss-gallery',     'ss_admin_gallery');
    add_submenu_page('shokhersrity', 'Videos',         'Videos',         'manage_options', 'ss-videos',      'ss_admin_videos');
    add_submenu_page('shokhersrity', 'Packages',       'Packages',       'manage_options', 'ss-packages',    'ss_admin_packages');
    add_submenu_page('shokhersrity', 'Settings',       'Settings',       'manage_options', 'ss-settings',    'ss_admin_settings');
}

// ─── Admin Asset Enqueue ─────────────────────────────────────
add_action('admin_enqueue_scripts', 'ss_admin_assets');
function ss_admin_assets($hook) {
    if (strpos($hook, 'shokhersrity') === false && strpos($hook, 'ss-') === false) return;
    wp_enqueue_media();
    ?>
    <style>
    #adminmenuwrap { background: #0a0805 !important; }
    #adminmenu, #adminmenu .wp-submenu { background: #0a0805 !important; }
    #adminmenu li a, #adminmenu .wp-submenu a { color: rgba(255,255,255,0.7) !important; }
    #adminmenu li.current a, #adminmenu li:hover a { color: #D4AF37 !important; }
    #adminmenu li.menu-top:hover, #adminmenu li.opensub .wp-submenu li:hover { background: rgba(212,175,55,0.08) !important; }
    #wpcontent, #wpbody { background: #f8f6f0; }
    .ss-admin { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    .ss-header { background: linear-gradient(135deg, #0a0805 0%, #1a120a 100%); color: white; padding: 2rem 2.5rem; border-radius: 12px; margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem; }
    .ss-header h1 { font-size: 1.6rem; margin: 0; font-weight: 600; background: linear-gradient(135deg, #D4AF37, #F5D67B); -webkit-background-clip: text; background-clip: text; color: transparent; }
    .ss-header p { margin: 0.25rem 0 0; font-size: 0.9rem; color: rgba(255,255,255,0.6); }
    .ss-header-logo { font-size: 2rem; }
    .ss-card { background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 1.5rem; margin-bottom: 1.5rem; border: 1px solid rgba(212,175,55,0.12); }
    .ss-card h2 { font-size: 1.1rem; color: #1a120a; margin: 0 0 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(212,175,55,0.15); display: flex; align-items: center; gap: 0.5rem; }
    .ss-btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.6rem 1.25rem; border-radius: 8px; font-size: 0.88rem; font-weight: 500; cursor: pointer; border: none; text-decoration: none; transition: all 0.2s; }
    .ss-btn-primary { background: linear-gradient(135deg, #D4AF37, #c49d2e); color: #0a0805; }
    .ss-btn-primary:hover { background: linear-gradient(135deg, #e6c347, #D4AF37); color: #0a0805; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(212,175,55,0.35); }
    .ss-btn-danger { background: #fff0f0; color: #c0392b; border: 1px solid #ffcdd2; }
    .ss-btn-danger:hover { background: #c0392b; color: white; }
    .ss-btn-secondary { background: #f0f0f0; color: #333; }
    .ss-btn-secondary:hover { background: #e0e0e0; color: #111; }
    .ss-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1rem; }
    .ss-image-card { position: relative; aspect-ratio: 1; border-radius: 8px; overflow: hidden; cursor: pointer; border: 2px solid transparent; transition: border-color 0.2s; }
    .ss-image-card img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .ss-image-card:hover { border-color: #D4AF37; }
    .ss-image-card .ss-image-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.6); opacity: 0; transition: opacity 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
    .ss-image-card:hover .ss-image-overlay { opacity: 1; }
    .ss-image-card .ss-image-meta { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); padding: 0.5rem; font-size: 0.7rem; color: white; }
    .ss-notice { padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; }
    .ss-notice-success { background: #f0fff4; color: #2e7d32; border: 1px solid #a5d6a7; }
    .ss-notice-error { background: #fff0f0; color: #c62828; border: 1px solid #ef9a9a; }
    .ss-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
    .ss-form-group { margin-bottom: 1rem; }
    .ss-form-group label { display: block; font-size: 0.85rem; font-weight: 500; color: #555; margin-bottom: 0.35rem; }
    .ss-form-group input, .ss-form-group textarea, .ss-form-group select { width: 100%; padding: 0.6rem 0.9rem; border: 1px solid #ddd; border-radius: 8px; font-size: 0.9rem; color: #333; box-sizing: border-box; transition: border-color 0.2s; }
    .ss-form-group input:focus, .ss-form-group textarea:focus { outline: none; border-color: #D4AF37; box-shadow: 0 0 0 3px rgba(212,175,55,0.12); }
    .ss-form-group textarea { resize: vertical; min-height: 100px; }
    .ss-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
    .ss-table th { text-align: left; padding: 0.65rem 1rem; background: #f8f6f0; color: #555; font-weight: 600; border-bottom: 2px solid rgba(212,175,55,0.15); }
    .ss-table td { padding: 0.75rem 1rem; border-bottom: 1px solid #f0ece0; vertical-align: middle; }
    .ss-table tr:hover td { background: rgba(212,175,55,0.04); }
    .ss-upload-zone { border: 2px dashed rgba(212,175,55,0.4); border-radius: 12px; padding: 3rem; text-align: center; cursor: pointer; transition: all 0.2s; background: rgba(212,175,55,0.03); }
    .ss-upload-zone:hover, .ss-upload-zone.drag-over { border-color: #D4AF37; background: rgba(212,175,55,0.08); }
    .ss-upload-zone svg { color: #D4AF37; margin-bottom: 1rem; }
    .ss-upload-zone p { color: #888; margin: 0.25rem 0; font-size: 0.9rem; }
    .ss-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    .ss-badge-gold { background: rgba(212,175,55,0.15); color: #8a6d00; }
    .ss-badge-dark { background: #1a120a; color: #D4AF37; }
    .ss-badge-blue { background: #e3f2fd; color: #1565c0; }
    .ss-tabs { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 2px solid rgba(212,175,55,0.12); padding-bottom: 0; }
    .ss-tab { padding: 0.65rem 1.25rem; border-radius: 8px 8px 0 0; cursor: pointer; font-size: 0.9rem; font-weight: 500; color: #888; background: none; border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s; }
    .ss-tab.active { color: #1a120a; border-bottom-color: #D4AF37; background: rgba(212,175,55,0.05); }
    .ss-tab:hover { color: #1a120a; }
    .ss-tab-content { display: none; }
    .ss-tab-content.active { display: block; }
    .hero-pair-preview { display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-top: 1rem; }
    .hero-pair-preview .hero-preview-slot { border-radius: 10px; overflow: hidden; border: 2px solid rgba(212,175,55,0.2); min-height: 160px; position: relative; background: #111; }
    .hero-pair-preview .hero-preview-slot img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .hero-pair-preview .hero-preview-slot .slot-label { position: absolute; top: 0.5rem; left: 0.5rem; background: rgba(0,0,0,0.75); color: #D4AF37; font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; }
    .spinner { display: inline-block; width: 18px; height: 18px; border: 2px solid rgba(212,175,55,0.3); border-top-color: #D4AF37; border-radius: 50%; animation: spin 0.7s linear infinite; vertical-align: middle; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .ss-video-list { display: grid; gap: 1rem; }
    .ss-video-card { display: grid; grid-template-columns: 200px 1fr auto; gap: 1rem; align-items: center; padding: 1rem; background: #fafaf8; border-radius: 10px; border: 1px solid rgba(212,175,55,0.1); }
    .ss-video-card video { width: 200px; height: 112px; object-fit: cover; border-radius: 8px; background: #111; }
    .ss-video-actions { display: flex; flex-direction: column; gap: 0.5rem; }
    .pkg-row { display: grid; grid-template-columns: 90px 1fr 1fr 1fr auto; gap: 0.75rem; align-items: start; padding: 0.75rem; border-bottom: 1px solid #f0ece0; }
    .pkg-row:last-child { border-bottom: none; }
    .ss-stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
    .ss-stat-card { background: linear-gradient(135deg, #0a0805, #1a120a); color: white; border-radius: 10px; padding: 1.25rem; text-align: center; }
    .ss-stat-card .stat-val { font-size: 2rem; font-weight: 700; background: linear-gradient(135deg, #D4AF37, #F5D67B); -webkit-background-clip: text; background-clip: text; color: transparent; }
    .ss-stat-card .stat-lbl { font-size: 0.8rem; color: rgba(255,255,255,0.6); margin-top: 0.25rem; }
    </style>
    <?php
}

// ─── Admin Page Callbacks ─────────────────────────────────────
function ss_admin_dashboard() { include SS_PLUGIN_DIR . 'admin/dashboard.php'; }
function ss_admin_gallery()   { include SS_PLUGIN_DIR . 'admin/gallery-admin.php'; }
function ss_admin_videos()    { include SS_PLUGIN_DIR . 'admin/videos-admin.php'; }
function ss_admin_packages()  { include SS_PLUGIN_DIR . 'admin/packages-admin.php'; }
function ss_admin_settings()  { include SS_PLUGIN_DIR . 'admin/settings-admin.php'; }

// ─── AJAX: Upload Image ───────────────────────────────────────
add_action('wp_ajax_ss_upload_image', 'ss_ajax_upload_image');
function ss_ajax_upload_image() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    if (empty($_FILES['image'])) wp_send_json_error('No file received');

    $file     = $_FILES['image'];
    $category = sanitize_text_field($_POST['category'] ?? 'wedding');
    $label    = sanitize_text_field($_POST['label'] ?? 'Wedding');

    // Validate type
    $allowed = ['image/webp', 'image/jpeg', 'image/png'];
    if (!in_array($file['type'], $allowed)) {
        wp_send_json_error('Only WebP, JPEG, PNG allowed');
    }

    // Map category to folder name
    $folders = [
        'wedding'    => 'Wedding Photoshooot',
        'bride'      => 'Bride Photoshoot',
        'reception'  => 'Reception',
        'engagement' => 'Engegment Photoshoot',
        'babyshower' => 'Baby Shower',
        'baby'       => 'Baby Photoshoot',
        'gallery'    => 'gallery',
    ];
    $folder = $folders[$category] ?? 'gallery';
    $dest_dir = SS_UPLOADS . '/' . $folder . '/';
    wp_mkdir_p($dest_dir);

    // Build safe filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
    $filename = uniqid('img_') . '.' . $ext;
    $dest_path = $dest_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
        wp_send_json_error('Failed to save file');
    }

    // Auto-detect dimensions
    $info = @getimagesize($dest_path);
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

    $catalog = (array) get_option('ss_image_catalog', []);
    $catalog[] = $entry;
    update_option('ss_image_catalog', $catalog, false);

    wp_send_json_success($entry);
}

// ─── AJAX: Delete Image ───────────────────────────────────────
add_action('wp_ajax_ss_delete_image', 'ss_ajax_delete_image');
function ss_ajax_delete_image() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    $id = sanitize_text_field($_POST['id'] ?? '');
    if (!$id) wp_send_json_error('No ID');

    $catalog = (array) get_option('ss_image_catalog', []);
    $new_catalog = [];
    $deleted_src = '';

    foreach ($catalog as $item) {
        if ($item['id'] === $id) {
            $deleted_src = $item['src'];
        } else {
            $new_catalog[] = $item;
        }
    }

    // Delete physical file
    if ($deleted_src) {
        $file_path = ABSPATH . ltrim($deleted_src, '/');
        if (file_exists($file_path)) @unlink($file_path);
    }

    update_option('ss_image_catalog', $new_catalog, false);
    wp_send_json_success(['deleted' => $id]);
}

// ─── AJAX: Update Hero Images ─────────────────────────────────
add_action('wp_ajax_ss_update_hero', 'ss_ajax_update_hero');
function ss_ajax_update_hero() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    $desktop = sanitize_text_field($_POST['desktop'] ?? '');
    $mobile  = sanitize_text_field($_POST['mobile']  ?? '');

    // Handle new uploads if any
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
    $file = $_FILES[$file_key];
    $allowed = ['image/webp', 'image/jpeg', 'image/png'];
    if (!in_array($file['type'], $allowed)) return false;

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'webp';
    $dest = SS_UPLOADS . '/hero/' . $dest_name . '.' . $ext;
    wp_mkdir_p(SS_UPLOADS . '/hero/');
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return '/wp-content/uploads/hero/' . $dest_name . '.' . $ext;
    }
    return false;
}

// ─── AJAX: Upload Video ───────────────────────────────────────
add_action('wp_ajax_ss_upload_video', 'ss_ajax_upload_video');
function ss_ajax_upload_video() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    if (empty($_FILES['video'])) wp_send_json_error('No file received');
    $file = $_FILES['video'];
    if ($file['type'] !== 'video/mp4') wp_send_json_error('Only MP4 allowed');

    $title = sanitize_text_field($_POST['title'] ?? 'Wedding Reel');
    $desc  = sanitize_text_field($_POST['description'] ?? '');
    $dest_dir = SS_UPLOADS . '/videos/';
    wp_mkdir_p($dest_dir);

    $tmp_name = $dest_dir . 'tmp_' . uniqid() . '.mp4';
    if (!move_uploaded_file($file['tmp_name'], $tmp_name)) {
        wp_send_json_error('Failed to save upload');
    }

    // FFmpeg faststart optimization
    $final_name = uniqid('vid_') . '.mp4';
    $final_path = $dest_dir . $final_name;
    $ffmpeg = trim(shell_exec('which ffmpeg 2>/dev/null') ?: '/run/current-system/sw/bin/ffmpeg');

    exec(escapeshellarg($ffmpeg) . ' -i ' . escapeshellarg($tmp_name) . ' -c copy -movflags +faststart ' . escapeshellarg($final_path) . ' -y 2>&1', $out, $code);
    @unlink($tmp_name);

    if ($code !== 0 || !file_exists($final_path)) {
        // Fallback: use the file without FFmpeg optimization
        rename($tmp_name, $final_path);
    }

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

// ─── AJAX: Update Video Metadata ──────────────────────────────
add_action('wp_ajax_ss_update_video', 'ss_ajax_update_video');
function ss_ajax_update_video() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    $id    = sanitize_text_field($_POST['id'] ?? '');
    $title = sanitize_text_field($_POST['title'] ?? '');
    $desc  = sanitize_text_field($_POST['description'] ?? '');

    $videos = (array) get_option('ss_videos', []);
    foreach ($videos as &$v) {
        if ($v['id'] === $id) {
            $v['title']       = $title;
            $v['description'] = $desc;
            break;
        }
    }
    update_option('ss_videos', $videos, false);
    wp_send_json_success(['id' => $id]);
}

// ─── AJAX: Delete Video ───────────────────────────────────────
add_action('wp_ajax_ss_delete_video', 'ss_ajax_delete_video');
function ss_ajax_delete_video() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    $id = sanitize_text_field($_POST['id'] ?? '');
    $videos = (array) get_option('ss_videos', []);
    $new = [];
    foreach ($videos as $v) {
        if ($v['id'] === $id) {
            $path = ABSPATH . ltrim($v['src'], '/');
            if (file_exists($path)) @unlink($path);
        } else {
            $new[] = $v;
        }
    }
    update_option('ss_videos', $new, false);
    wp_send_json_success(['deleted' => $id]);
}

// ─── AJAX: Reorder Videos ────────────────────────────────────
add_action('wp_ajax_ss_reorder_videos', 'ss_ajax_reorder_videos');
function ss_ajax_reorder_videos() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    $order  = array_map('sanitize_text_field', (array)($_POST['order'] ?? []));
    $videos = (array) get_option('ss_videos', []);
    $indexed = [];
    foreach ($videos as $v) $indexed[$v['id']] = $v;

    $new = [];
    foreach ($order as $id) {
        if (isset($indexed[$id])) $new[] = $indexed[$id];
    }
    // Append any not in order array
    foreach ($videos as $v) {
        if (!in_array($v['id'], $order)) $new[] = $v;
    }
    update_option('ss_videos', $new, false);
    wp_send_json_success(['count' => count($new)]);
}

// ─── AJAX: Save Packages ─────────────────────────────────────
add_action('wp_ajax_ss_save_packages', 'ss_ajax_save_packages');
function ss_ajax_save_packages() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    $raw = stripslashes($_POST['packages'] ?? '{}');
    $data = json_decode($raw, true);
    if (!$data) wp_send_json_error('Invalid JSON');

    // Sanitize
    $clean = ['tiers' => [], 'packages' => []];
    if (isset($data['tiers'])) {
        foreach ((array)$data['tiers'] as $t) {
            $clean['tiers'][] = [
                'id'      => sanitize_key($t['id'] ?? ''),
                'label'   => sanitize_text_field($t['label'] ?? ''),
                'subtitle'=> sanitize_text_field($t['subtitle'] ?? ''),
                'columns' => (int)($t['columns'] ?? 3),
            ];
        }
    }
    if (isset($data['packages'])) {
        foreach ((array)$data['packages'] as $p) {
            $feats = [];
            foreach ((array)($p['features'] ?? []) as $f) {
                $feats[] = sanitize_text_field($f);
            }
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
    }

    update_option('ss_packages', $clean);
    wp_send_json_success(['saved' => count($clean['packages'])]);
}

// ─── AJAX: Save Settings ─────────────────────────────────────
add_action('wp_ajax_ss_save_settings', 'ss_ajax_save_settings');
function ss_ajax_save_settings() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    $fields_text = ['site_name','tagline','phone1','phone1_name','phone2','phone2_name','email','address','map_embed_url','facebook','instagram','whatsapp','youtube','tiktok','about_p1','about_p2','about_signature','cta_title','cta_text','geo_lat','geo_lng'];
    $fields_int  = ['stat1_count','stat2_count','stat3_count'];
    $fields_str  = ['stat1_suffix','stat2_suffix','stat3_suffix','stat1_label','stat2_label','stat3_label'];

    $s = [];
    foreach ($fields_text as $f) $s[$f] = sanitize_text_field($_POST[$f] ?? '');
    foreach ($fields_int  as $f) $s[$f] = (int)($_POST[$f] ?? 0);
    foreach ($fields_str  as $f) $s[$f] = sanitize_text_field($_POST[$f] ?? '');

    // CTA title allows span tag
    $s['cta_title'] = wp_kses($_POST['cta_title'] ?? '', ['span' => ['class' => []]]);

    update_option('ss_settings', $s);
    wp_send_json_success(['saved' => true]);
}

// ─── AJAX: Reorder Catalog ────────────────────────────────────
add_action('wp_ajax_ss_reorder_catalog', 'ss_ajax_reorder_catalog');
function ss_ajax_reorder_catalog() {
    check_ajax_referer('ss_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    $order   = array_map('sanitize_text_field', (array)($_POST['order'] ?? []));
    $catalog = (array) get_option('ss_image_catalog', []);
    $indexed = [];
    foreach ($catalog as $item) $indexed[$item['id']] = $item;

    $new = [];
    foreach ($order as $id) {
        if (isset($indexed[$id])) $new[] = $indexed[$id];
    }
    foreach ($catalog as $item) {
        if (!in_array($item['id'], $order)) $new[] = $item;
    }
    update_option('ss_image_catalog', $new, false);
    wp_send_json_success(['count' => count($new)]);
}

// ─── Remove Plugin Install Buttons from Admin ─────────────────
add_filter('plugin_action_links', '__return_empty_array');
add_filter('install_plugins_tabs', '__return_empty_array');
// Redirect Plugins page to our dashboard
add_action('admin_init', function() {
    global $pagenow;
    if ($pagenow === 'plugins.php' || $pagenow === 'plugin-install.php') {
        wp_redirect(admin_url('admin.php?page=shokhersrity'));
        exit;
    }
});
