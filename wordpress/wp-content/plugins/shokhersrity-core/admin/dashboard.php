<?php defined('ABSPATH') || exit;
$catalog  = (array) get_option('ss_image_catalog', []);
$videos   = (array) get_option('ss_videos', []);
$pkgs     = ss_get_packages();
$pkg_count = count($pkgs['packages'] ?? []);
$settings  = ss_get_settings();

// Count by category
$cats = [];
foreach ($catalog as $img) {
    $c = $img['category'] ?? 'other';
    $cats[$c] = ($cats[$c] ?? 0) + 1;
}
arsort($cats);
?>
<div class="wrap ss-admin">
    <div class="ss-header">
        <div class="ss-header-logo">✦</div>
        <div>
            <h1>ShokherSrity Studio Admin</h1>
            <p>Premium Wedding Photography — <?php echo esc_html($settings['address'] ?? ''); ?></p>
        </div>
        <div style="margin-left:auto;display:flex;gap:0.75rem;align-items:center;">
            <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" class="ss-btn ss-btn-secondary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3"/></svg>
                View Site
            </a>
        </div>
    </div>

    <div class="ss-stat-grid" style="margin-bottom:1.5rem;">
        <div class="ss-stat-card">
            <div class="stat-val"><?php echo count($catalog); ?></div>
            <div class="stat-lbl">Gallery Images</div>
        </div>
        <div class="ss-stat-card">
            <div class="stat-val"><?php echo count($videos); ?></div>
            <div class="stat-lbl">Reels / Videos</div>
        </div>
        <div class="ss-stat-card">
            <div class="stat-val"><?php echo $pkg_count; ?></div>
            <div class="stat-lbl">Packages</div>
        </div>
        <div class="ss-stat-card">
            <div class="stat-val">3</div>
            <div class="stat-lbl">Tiers (Standard/Premium/Exclusive)</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
        <!-- Quick Links -->
        <div class="ss-card">
            <h2>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                Quick Actions
            </h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                <a href="<?php echo admin_url('admin.php?page=ss-gallery'); ?>" class="ss-btn ss-btn-primary" style="justify-content:center;padding:1rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                    Manage Gallery
                </a>
                <a href="<?php echo admin_url('admin.php?page=ss-videos'); ?>" class="ss-btn ss-btn-primary" style="justify-content:center;padding:1rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                    Manage Videos
                </a>
                <a href="<?php echo admin_url('admin.php?page=ss-packages'); ?>" class="ss-btn ss-btn-secondary" style="justify-content:center;padding:1rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                    Edit Packages
                </a>
                <a href="<?php echo admin_url('admin.php?page=ss-settings'); ?>" class="ss-btn ss-btn-secondary" style="justify-content:center;padding:1rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                    Site Settings
                </a>
            </div>
        </div>

        <!-- Gallery Breakdown -->
        <div class="ss-card">
            <h2>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                Gallery by Category
            </h2>
            <table class="ss-table" style="font-size:0.85rem;">
                <thead><tr><th>Category</th><th>Count</th><th>% of total</th></tr></thead>
                <tbody>
                <?php foreach ($cats as $cat => $count):
                    $pct = count($catalog) ? round($count / count($catalog) * 100) : 0;
                ?>
                <tr>
                    <td><?php echo esc_html(ucfirst($cat)); ?></td>
                    <td><span class="ss-badge ss-badge-gold"><?php echo $count; ?></span></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <div style="flex:1;height:6px;background:#f0ece0;border-radius:3px;overflow:hidden;">
                                <div style="width:<?php echo $pct; ?>%;height:100%;background:linear-gradient(90deg,#D4AF37,#F5D67B);border-radius:3px;"></div>
                            </div>
                            <span style="color:#888;font-size:0.78rem;"><?php echo $pct; ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Hero Image Preview -->
    <div class="ss-card" style="margin-top:1.5rem;">
        <h2>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Current Hero Images
            <a href="<?php echo admin_url('admin.php?page=ss-gallery&tab=hero'); ?>" class="ss-btn ss-btn-secondary" style="margin-left:auto;font-size:0.8rem;">Change</a>
        </h2>
        <?php $hero = ss_get_hero(); ?>
        <div class="hero-pair-preview">
            <div class="hero-preview-slot">
                <span class="slot-label">Desktop (landscape)</span>
                <?php if ($hero['desktop']): ?>
                <img src="<?php echo esc_url($hero['desktop']); ?>?v=<?php echo time(); ?>" alt="Desktop hero">
                <?php else: ?>
                <div style="display:flex;align-items:center;justify-content:center;height:160px;color:rgba(255,255,255,0.4);font-size:0.85rem;">No image set</div>
                <?php endif; ?>
            </div>
            <div class="hero-preview-slot">
                <span class="slot-label">Mobile (portrait)</span>
                <?php if ($hero['mobile']): ?>
                <img src="<?php echo esc_url($hero['mobile']); ?>?v=<?php echo time(); ?>" alt="Mobile hero">
                <?php else: ?>
                <div style="display:flex;align-items:center;justify-content:center;height:160px;color:rgba(255,255,255,0.4);font-size:0.85rem;">No image set</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Videos Preview -->
    <?php if ($videos): ?>
    <div class="ss-card" style="margin-top:1.5rem;">
        <h2>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
            Videos (<?php echo count($videos); ?>)
            <a href="<?php echo admin_url('admin.php?page=ss-videos'); ?>" class="ss-btn ss-btn-secondary" style="margin-left:auto;font-size:0.8rem;">Manage</a>
        </h2>
        <div style="display:flex;gap:1rem;flex-wrap:wrap;">
            <?php foreach ($videos as $v): ?>
            <div style="text-align:center;">
                <video src="<?php echo esc_url(home_url($v['src'])); ?>" style="width:160px;height:90px;object-fit:cover;border-radius:8px;background:#111;" preload="metadata"></video>
                <p style="font-size:0.8rem;color:#555;margin:0.25rem 0 0;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo esc_html($v['title']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
