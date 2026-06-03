<?php
/**
 * Template Name: Packages
 */
$data = ss_get_packages();
$tiers = $data['tiers'] ?? [];
$pkgs  = $data['packages'] ?? [];

// Group packages by tier
$by_tier = [];
foreach ($pkgs as $p) {
    $by_tier[$p['tier']][] = $p;
}

get_header();

function ss_render_package_card($p) {
    $style = $p['style'] ?? 'standard';
    $has_badge = !empty($p['badge']);
    $features = $p['features'] ?? [];
    $wa_link = 'https://wa.me/' . esc_attr(ss_get_settings()['whatsapp']) . '?text=' . rawurlencode($p['whatsapp_message'] ?? '');

    // CSS class for card
    $card_classes = 'package-card';
    if ($style === 'featured') $card_classes .= ' featured';
    if ($style === 'liquid-glass') $card_classes .= ' liquid-glass';
    if ($style === 'exclusive') $card_classes .= ' liquid-glass exclusive';

    // Complementary features (contain "[COMPLEMENTARY]")
    // Icon for each style
    if ($style === 'standard') {
        $icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
    } elseif ($style === 'featured') {
        $icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>';
    } elseif ($style === 'liquid-glass') {
        $icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
    } else {
        $icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>';
    }

    ob_start(); ?>
    <?php if ($has_badge): ?>
    <div class="package-card-wrapper">
        <div class="package-badge"><?php echo esc_html($p['badge']); ?></div>
        <div class="<?php echo esc_attr($card_classes); ?>">
    <?php else: ?>
    <div class="<?php echo esc_attr($card_classes); ?>">
    <?php endif; ?>
            <div class="package-icon"><?php echo $icon; ?></div>
            <h3 class="package-name"><?php echo esc_html($p['name']); ?></h3>
            <div class="package-price"><?php echo esc_html($p['price']); ?><span><?php echo esc_html($p['period']); ?></span></div>
            <p class="package-price-note"><?php echo esc_html($p['note']); ?></p>
            <div class="package-divider"></div>
            <ul class="package-features">
                <?php foreach ($features as $feat):
                    $is_comp = strpos($feat, '[COMPLEMENTARY]') !== false;
                    $feat_text = str_replace('[COMPLEMENTARY]', '', $feat);
                    $feat_label = trim($feat_text);
                ?>
                <li <?php echo $is_comp ? 'class="feature-highlighted"' . ($style === 'exclusive' || $style === 'liquid-glass' ? ' style="border-bottom-color:rgba(255,255,255,0.06);"' : '') : ''; ?>>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <?php echo esc_html($feat_label); ?>
                    <?php if ($is_comp): ?><span class="complementary-badge">Complementary</span><?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php if (!empty($p['complementary_note'])): ?>
            <p class="complementary-note"><?php echo esc_html($p['complementary_note']); ?></p>
            <?php endif; ?>
            <a href="<?php echo esc_url($wa_link); ?>" target="_blank" class="package-btn" id="btn-<?php echo esc_attr($p['id']); ?>">Book Now</a>
    <?php if ($has_badge): ?>
        </div>
    </div>
    <?php else: ?>
    </div>
    <?php endif; ?>
    <?php return ob_get_clean();
}
?>
<main class="packages-page">
    <div class="packages-hero" data-aos="fade-down">
        <span class="section-label" style="color:var(--color-gold);">Investment</span>
        <h1>Wedding Photography <span class="text-gradient">Packages</span></h1>
        <p>Transparent pricing, extraordinary results. Choose the package that fits your vision and let's create something timeless together.</p>
    </div>

    <?php foreach ($tiers as $tier):
        $tier_pkgs = $by_tier[$tier['id']] ?? [];
        if (empty($tier_pkgs)) continue;
        $cols = $tier['columns'] ?? count($tier_pkgs);
    ?>

    <div class="section-header" data-aos="fade-up" <?php echo $tier !== $tiers[0] ? 'style="margin-top:4rem;"' : ''; ?>>
        <span class="section-label" style="color:var(--color-gold);"><?php echo esc_html($tier['subtitle']); ?></span>
        <h2 class="section-title" style="color:var(--color-charcoal);"><?php
            $parts = explode(' ', $tier['label'], -1);
            $last = end(explode(' ', $tier['label']));
            $first = trim(str_replace($last, '', $tier['label']));
            echo esc_html($first); ?> <span class="text-gradient"><?php echo esc_html($last); ?></span></h2>
    </div>

    <div class="packages-container" style="grid-template-columns: repeat(<?php echo $cols; ?>, 1fr);" id="packages-<?php echo esc_attr($tier['id']); ?>">
        <?php foreach ($tier_pkgs as $p): ?>
        <?php echo ss_render_package_card($p); ?>
        <?php endforeach; ?>
    </div>

    <?php endforeach; ?>

    <!-- Additional Info Bar -->
    <div class="packages-container" style="margin-top:2rem;">
        <div style="background:var(--color-white);border-radius:24px;padding:3rem;grid-column:1 / -1;text-align:center;box-shadow:0 10px 40px rgba(212,175,55,0.08);border:1px solid rgba(212,175,55,0.12);">
            <div style="display:flex;justify-content:center;gap:3rem;flex-wrap:wrap;">
                <?php
                $perks = [
                    ['icon'=>'<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>','title'=>'Quick Delivery','desc'=>'Edited photos within 2 weeks'],
                    ['icon'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>','title'=>'Secure Backup','desc'=>'All photos backed up securely'],
                    ['icon'=>'<path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"></path>','title'=>'24/7 Support','desc'=>'Always here to help you'],
                ];
                foreach ($perks as $perk): ?>
                <div style="display:flex;align-items:center;gap:1rem;">
                    <div style="width:50px;height:50px;background:rgba(212,175,55,0.12);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><?php echo $perk['icon']; ?></svg>
                    </div>
                    <div style="text-align:left;">
                        <h4 style="font-size:1rem;margin-bottom:0.25rem;"><?php echo esc_html($perk['title']); ?></h4>
                        <p style="font-size:0.85rem;color:#666;"><?php echo esc_html($perk['desc']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Custom Calculator (locked / coming soon) -->
    <div class="section-header" style="margin-top:4rem;" data-aos="fade-up">
        <span class="section-label" style="color:var(--color-gold);">Build Your Dream Package</span>
        <h2 class="section-title" style="color:var(--color-charcoal);">Instant <span class="text-gradient">Customization</span></h2>
    </div>
    <div class="calculator-section" data-aos="fade-up">
        <div class="calculator-card" id="package-calculator" style="filter:grayscale(100%);opacity:0.6;cursor:not-allowed;" onclick="openComingSoonModal()">
            <div style="pointer-events:none;">
                <div class="calculator-header">
                    <h3>Design Your <span style="background:var(--gradient-gold-liquid);-webkit-background-clip:text;background-clip:text;color:transparent;">Dream Package</span></h3>
                    <p>Select the services you need and see your price update instantly</p>
                </div>
                <div class="calc-options-grid">
                    <div class="calc-option-group">
                        <h4><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>Photography</h4>
                        <?php foreach ([['8000','1 Photographer'],['14000','2 Photographers'],['20000','3 Senior Photographers']] as $o): ?>
                        <div class="calc-option" data-price="<?php echo $o[0]; ?>"><div class="calc-option-label"><div class="calc-checkbox"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="20 6 9 17 4 12"></polyline></svg></div><?php echo esc_html($o[1]); ?></div><span class="calc-option-price" style="display:none;">৳<?php echo number_format((int)$o[0]); ?></span></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="calc-option-group">
                        <h4><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>Videography</h4>
                        <?php foreach ([['10000','1 Cinematographer'],['18000','2 Cinematographers'],['7000','Cinematic Promo Reel']] as $o): ?>
                        <div class="calc-option" data-price="<?php echo $o[0]; ?>"><div class="calc-option-label"><div class="calc-checkbox"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="20 6 9 17 4 12"></polyline></svg></div><?php echo esc_html($o[1]); ?></div><span class="calc-option-price" style="display:none;">৳<?php echo number_format((int)$o[0]); ?></span></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="calculator-footer">
                    <div class="calc-total"><div class="calc-total-label">Estimated Total</div><div class="calc-total-amount"><span id="calc-price">৳0</span><span class="calc-total-note">/ day</span></div></div>
                    <button class="calc-cta" disabled>Coming Soon</button>
                </div>
            </div>
        </div>
    </div>

</main>

<!-- Coming Soon Modal -->
<div id="coming-soon-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content glass-effect">
        <button class="modal-close" onclick="closeComingSoonModal()">&times;</button>
        <div class="modal-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg></div>
        <h3>Coming Soon</h3>
        <p>Our custom package calculator is under development. Meanwhile, reach out directly and we'll craft a custom quote for you!</p>
        <a href="https://wa.me/<?php echo esc_attr(ss_get_settings()['whatsapp']); ?>?text=Hello! I would like a custom package quote." target="_blank" class="btn btn-primary">Chat on WhatsApp</a>
    </div>
</div>
<script>
function openComingSoonModal(){document.getElementById('coming-soon-modal').style.display='flex';}
function closeComingSoonModal(){document.getElementById('coming-soon-modal').style.display='none';}
document.getElementById('coming-soon-modal').addEventListener('click',function(e){if(e.target===this)closeComingSoonModal();});
</script>

<?php get_footer(); ?>
