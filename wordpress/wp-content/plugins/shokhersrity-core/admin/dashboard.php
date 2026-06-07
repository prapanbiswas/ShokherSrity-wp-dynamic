<?php defined('ABSPATH') || exit;
$catalog  = (array) get_option('ss_image_catalog', []);
$videos   = (array) get_option('ss_videos', []);
$pkgs     = ss_get_packages();
$pkg_count = count($pkgs['packages'] ?? []);
$settings  = ss_get_settings();
$logo_url  = get_option('ss_logo_url', '');

$cats = [];
foreach ($catalog as $img) {
    $c = $img['category'] ?? 'other';
    $cats[$c] = ($cats[$c] ?? 0) + 1;
}
arsort($cats);

$cat_labels = [
    'wedding'    => 'Wedding',
    'bride'      => 'Bride',
    'reception'  => 'Reception',
    'engagement' => 'Engagement',
    'babyshower' => 'Baby Shower',
    'baby'       => 'Baby Photoshoot',
    'gallery'    => 'Gallery',
];
?>
<div class="wrap ss-admin">

    <!-- Page header -->
    <div class="ss-page-header">
        <div class="ss-page-header-left">
            <?php if ($logo_url): ?>
            <img src="<?php echo esc_url($logo_url); ?>" alt="Logo" style="height:36px;width:auto;object-fit:contain;">
            <?php else: ?>
            <div class="ss-brand-mark">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.8"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/></svg>
            </div>
            <?php endif; ?>
            <div>
                <h1 class="ss-page-title">Studio Dashboard</h1>
                <p class="ss-page-subtitle"><?php echo esc_html($settings['site_name'] ?? 'ShokherSrity'); ?> &middot; <?php echo esc_html($settings['address'] ?? ''); ?></p>
            </div>
        </div>
        <div class="ss-page-header-right">
            <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" class="ss-btn ss-btn-outline">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                View Website
            </a>
        </div>
    </div>

    <!-- Stats row -->
    <div class="ss-stats-row">
        <div class="ss-stat-card-pro">
            <div class="ss-stat-icon-wrap">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            </div>
            <div class="ss-stat-body">
                <div class="ss-stat-value"><?php echo count($catalog); ?></div>
                <div class="ss-stat-label">Gallery Images</div>
            </div>
            <a href="<?php echo admin_url('admin.php?page=ss-gallery'); ?>" class="ss-stat-link">Manage</a>
        </div>
        <div class="ss-stat-card-pro">
            <div class="ss-stat-icon-wrap">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
            </div>
            <div class="ss-stat-body">
                <div class="ss-stat-value"><?php echo count($videos); ?></div>
                <div class="ss-stat-label">Reels</div>
            </div>
            <a href="<?php echo admin_url('admin.php?page=ss-videos'); ?>" class="ss-stat-link">Manage</a>
        </div>
        <div class="ss-stat-card-pro">
            <div class="ss-stat-icon-wrap">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            <div class="ss-stat-body">
                <div class="ss-stat-value"><?php echo $pkg_count; ?></div>
                <div class="ss-stat-label">Packages</div>
            </div>
            <a href="<?php echo admin_url('admin.php?page=ss-packages'); ?>" class="ss-stat-link">Manage</a>
        </div>
        <div class="ss-stat-card-pro">
            <div class="ss-stat-icon-wrap">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
            </div>
            <div class="ss-stat-body">
                <div class="ss-stat-value"><?php echo count($cats); ?></div>
                <div class="ss-stat-label">Categories</div>
            </div>
            <span class="ss-stat-link" style="cursor:default;">Active</span>
        </div>
    </div>

    <!-- Two column layout -->
    <div class="ss-two-col">

        <!-- Quick Actions -->
        <div class="ss-card">
            <div class="ss-card-header">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                Quick Actions
            </div>
            <div class="ss-action-grid">
                <a href="<?php echo admin_url('admin.php?page=ss-gallery'); ?>" class="ss-action-tile ss-action-primary">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                    <span>Gallery</span>
                </a>
                <a href="<?php echo admin_url('admin.php?page=ss-videos'); ?>" class="ss-action-tile ss-action-primary">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                    <span>Reels</span>
                </a>
                <a href="<?php echo admin_url('admin.php?page=ss-packages'); ?>" class="ss-action-tile ss-action-secondary">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    <span>Packages</span>
                </a>
                <a href="<?php echo admin_url('admin.php?page=ss-settings'); ?>" class="ss-action-tile ss-action-secondary">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                    <span>Settings</span>
                </a>
            </div>
        </div>

        <!-- Gallery breakdown -->
        <div class="ss-card">
            <div class="ss-card-header">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Gallery by Category
            </div>
            <?php if (empty($cats)): ?>
            <p style="color:#9b9490;font-size:.85rem;text-align:center;padding:1.5rem 0;">No images yet. Upload images in Gallery.</p>
            <?php else: ?>
            <div class="ss-category-list">
                <?php foreach ($cats as $cat => $count):
                    $pct = count($catalog) ? round($count / count($catalog) * 100) : 0;
                    $label = $cat_labels[$cat] ?? ucfirst($cat);
                ?>
                <div class="ss-cat-row">
                    <span class="ss-cat-name"><?php echo esc_html($label); ?></span>
                    <div class="ss-cat-bar-wrap">
                        <div class="ss-cat-bar" style="width:<?php echo $pct; ?>%;"></div>
                    </div>
                    <span class="ss-cat-count"><?php echo $count; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Hero preview -->
    <?php $hero = ss_get_hero(); if ($hero['desktop'] || $hero['mobile']): ?>
    <div class="ss-card" style="margin-top:1.25rem;">
        <div class="ss-card-header">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Hero Images
            <a href="<?php echo admin_url('admin.php?page=ss-gallery&tab=hero'); ?>" class="ss-btn ss-btn-sm ss-btn-outline" style="margin-left:auto;">Change</a>
        </div>
        <div class="hero-pair-preview">
            <div class="hero-preview-slot">
                <span class="slot-label">Desktop</span>
                <?php if ($hero['desktop']): ?>
                <img src="<?php echo esc_url($hero['desktop']); ?>" alt="Desktop hero" loading="lazy">
                <?php endif; ?>
            </div>
            <div class="hero-preview-slot">
                <span class="slot-label">Mobile</span>
                <?php if ($hero['mobile']): ?>
                <img src="<?php echo esc_url($hero['mobile']); ?>" alt="Mobile hero" loading="lazy">
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Reels preview -->
    <?php if ($videos): ?>
    <div class="ss-card" style="margin-top:1.25rem;">
        <div class="ss-card-header">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
            Reels (<?php echo count($videos); ?>)
            <a href="<?php echo admin_url('admin.php?page=ss-videos'); ?>" class="ss-btn ss-btn-sm ss-btn-outline" style="margin-left:auto;">Manage</a>
        </div>
        <div class="ss-reel-thumbs">
            <?php foreach ($videos as $v): ?>
            <div class="ss-reel-thumb">
                <video src="<?php echo esc_url(home_url($v['src'])); ?>" preload="metadata" muted></video>
                <p><?php echo esc_html($v['title']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<style>
.ss-page-header{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;}
.ss-page-header-left{display:flex;align-items:center;gap:.85rem;}
.ss-brand-mark{width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#0a0805,#1a120a);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.ss-page-title{font-size:1.3rem;font-weight:700;color:#1a120a;margin:0;line-height:1.2;}
.ss-page-subtitle{font-size:.8rem;color:#9b9490;margin:.15rem 0 0;}
.ss-page-header-right{display:flex;gap:.5rem;}

.ss-stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.25rem;}
.ss-stat-card-pro{background:#fff;border:1px solid #ede8e0;border-radius:12px;padding:1rem 1.1rem;display:flex;align-items:center;gap:.85rem;box-shadow:0 1px 3px rgba(0,0,0,.04);}
.ss-stat-icon-wrap{width:42px;height:42px;border-radius:10px;background:rgba(212,175,55,.08);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.ss-stat-body{flex:1;min-width:0;}
.ss-stat-value{font-size:1.55rem;font-weight:700;color:#1a120a;line-height:1;}
.ss-stat-label{font-size:.75rem;color:#9b9490;margin-top:.15rem;}
.ss-stat-link{font-size:.75rem;color:#D4AF37;text-decoration:none;font-weight:600;white-space:nowrap;}
.ss-stat-link:hover{color:#b8960c;}

.ss-two-col{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;}
.ss-card-header{display:flex;align-items:center;gap:.5rem;font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b6460;margin-bottom:1rem;}

.ss-action-grid{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;}
.ss-action-tile{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.5rem;padding:1.1rem .75rem;border-radius:10px;text-decoration:none;font-size:.8rem;font-weight:500;transition:all .18s;border:1px solid transparent;}
.ss-action-primary{background:linear-gradient(135deg,rgba(212,175,55,.12),rgba(212,175,55,.06));color:#1a120a;border-color:rgba(212,175,55,.2);}
.ss-action-primary:hover{background:linear-gradient(135deg,rgba(212,175,55,.2),rgba(212,175,55,.1));border-color:#D4AF37;color:#0a0805;}
.ss-action-secondary{background:#f8f5ef;color:#4a4040;border-color:#ede8e0;}
.ss-action-secondary:hover{background:#f0ece4;color:#1a120a;}

.ss-category-list{display:flex;flex-direction:column;gap:.55rem;}
.ss-cat-row{display:flex;align-items:center;gap:.75rem;}
.ss-cat-name{font-size:.82rem;color:#4a4040;width:90px;flex-shrink:0;}
.ss-cat-bar-wrap{flex:1;height:6px;background:#f0ece4;border-radius:3px;overflow:hidden;}
.ss-cat-bar{height:100%;background:linear-gradient(90deg,#D4AF37,#F5D67B);border-radius:3px;transition:width .4s;}
.ss-cat-count{font-size:.78rem;color:#9b9490;width:30px;text-align:right;flex-shrink:0;}

.ss-reel-thumbs{display:flex;gap:.85rem;flex-wrap:wrap;}
.ss-reel-thumb{text-align:center;}
.ss-reel-thumb video{width:140px;height:79px;object-fit:cover;border-radius:8px;background:#111;display:block;}
.ss-reel-thumb p{font-size:.72rem;color:#6b6460;margin:.3rem 0 0;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}

@media (max-width:900px){
.ss-stats-row{grid-template-columns:1fr 1fr;}
.ss-two-col{grid-template-columns:1fr;}
}
@media (max-width:600px){
.ss-stats-row{grid-template-columns:1fr 1fr;}
.ss-stat-card-pro{padding:.75rem;}
.ss-page-header{flex-direction:column;align-items:flex-start;}
}
</style>
