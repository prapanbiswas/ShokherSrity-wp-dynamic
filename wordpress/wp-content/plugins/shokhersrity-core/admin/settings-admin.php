<?php defined('ABSPATH') || exit;
$nonce = wp_create_nonce('ss_nonce');
$s     = ss_get_settings();
?>
<div class="wrap ss-admin">
    <div class="ss-header">
        <div class="ss-header-logo">⚙</div>
        <div>
            <h1>Site Settings</h1>
            <p>Contact info, social links, about section, and CTAs</p>
        </div>
        <div style="margin-left:auto;">
            <button class="ss-btn ss-btn-primary" onclick="saveSettings()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Settings
            </button>
        </div>
    </div>

    <div id="save-notice" style="display:none;" class="ss-notice ss-notice-success">✓ Settings saved!</div>

    <?php
    $text_field = function($id, $label, $val, $placeholder='') {
        echo '<div class="ss-form-group"><label>' . esc_html($label) . '</label>';
        echo '<input type="text" id="' . esc_attr($id) . '" value="' . esc_attr($val) . '" placeholder="' . esc_attr($placeholder) . '"></div>';
    };
    $textarea_field = function($id, $label, $val, $rows=3) {
        echo '<div class="ss-form-group"><label>' . esc_html($label) . '</label>';
        echo '<textarea id="' . esc_attr($id) . '" rows="' . (int)$rows . '">' . esc_textarea($val) . '</textarea></div>';
    };
    ?>

    <!-- Contact Info -->
    <div class="ss-card">
        <h2>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
            Contact Information
        </h2>
        <div class="ss-form-row">
            <?php $text_field('phone1', 'Phone 1 (Primary)', $s['phone1'], '+8801XXXXXXXXX'); ?>
            <?php $text_field('phone1_name', 'Phone 1 Person Name', $s['phone1_name'], 'Kowsik'); ?>
        </div>
        <div class="ss-form-row">
            <?php $text_field('phone2', 'Phone 2 (Secondary)', $s['phone2'], '+8801XXXXXXXXX'); ?>
            <?php $text_field('phone2_name', 'Phone 2 Person Name', $s['phone2_name'], 'Dip'); ?>
        </div>
        <div class="ss-form-row">
            <?php $text_field('email', 'Email Address', $s['email'], 'studio@example.com'); ?>
            <?php $text_field('address', 'Studio Address', $s['address'], 'City, District, Bangladesh'); ?>
        </div>
        <?php $textarea_field('map_embed_url', 'Google Maps Embed URL (the src="" value from the iframe embed code)', $s['map_embed_url'], 2); ?>
    </div>

    <!-- Social Links -->
    <div class="ss-card">
        <h2>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            Social Media & Contact Links
        </h2>
        <div class="ss-form-row">
            <?php $text_field('whatsapp', 'WhatsApp Number (digits only)', $s['whatsapp'], '8801XXXXXXXXX'); ?>
            <?php $text_field('facebook', 'Facebook URL', $s['facebook']); ?>
        </div>
        <div class="ss-form-row">
            <?php $text_field('instagram', 'Instagram URL', $s['instagram']); ?>
            <?php $text_field('youtube', 'YouTube URL', $s['youtube']); ?>
        </div>
        <?php $text_field('tiktok', 'TikTok URL', $s['tiktok']); ?>
    </div>

    <!-- About Section -->
    <div class="ss-card">
        <h2>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><circle cx="12" cy="7" r="4"/><path d="M20 21a8 8 0 10-16 0"/></svg>
            About Section
        </h2>
        <?php $textarea_field('about_p1', 'About Paragraph 1', $s['about_p1'], 3); ?>
        <?php $textarea_field('about_p2', 'About Paragraph 2', $s['about_p2'], 3); ?>
        <?php $text_field('about_signature', 'Signature Quote', $s['about_signature']); ?>

        <h3 style="font-size:0.95rem;margin:1.5rem 0 1rem;color:#555;">Stats</h3>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;">
            <div class="ss-card" style="margin:0;">
                <h4 style="font-size:0.85rem;margin:0 0 0.75rem;">Stat 1</h4>
                <?php $text_field('stat1_count', 'Count', $s['stat1_count']); ?>
                <?php $text_field('stat1_suffix', 'Suffix (e.g. +)', $s['stat1_suffix']); ?>
                <?php $text_field('stat1_label', 'Label', $s['stat1_label']); ?>
            </div>
            <div class="ss-card" style="margin:0;">
                <h4 style="font-size:0.85rem;margin:0 0 0.75rem;">Stat 2</h4>
                <?php $text_field('stat2_count', 'Count', $s['stat2_count']); ?>
                <?php $text_field('stat2_suffix', 'Suffix', $s['stat2_suffix']); ?>
                <?php $text_field('stat2_label', 'Label', $s['stat2_label']); ?>
            </div>
            <div class="ss-card" style="margin:0;">
                <h4 style="font-size:0.85rem;margin:0 0 0.75rem;">Stat 3</h4>
                <?php $text_field('stat3_count', 'Count', $s['stat3_count']); ?>
                <?php $text_field('stat3_suffix', 'Suffix', $s['stat3_suffix']); ?>
                <?php $text_field('stat3_label', 'Label', $s['stat3_label']); ?>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="ss-card">
        <h2>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Call to Action Section
        </h2>
        <div class="ss-form-group">
            <label>CTA Title (HTML allowed: <code>&lt;span class="text-gradient"&gt;...&lt;/span&gt;</code>)</label>
            <input type="text" id="cta_title" value="<?php echo esc_attr($s['cta_title']); ?>">
        </div>
        <?php $textarea_field('cta_text', 'CTA Sub-text', $s['cta_text'], 2); ?>
    </div>

    <!-- Site Identity -->
    <div class="ss-card">
        <h2>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Site Identity
        </h2>
        <div class="ss-form-row">
            <?php $text_field('site_name', 'Studio Name', $s['site_name']); ?>
            <?php $text_field('tagline', 'Hero Tagline', $s['tagline']); ?>
        </div>
        <div class="ss-form-row">
            <?php $text_field('geo_lat', 'GPS Latitude', $s['geo_lat']); ?>
            <?php $text_field('geo_lng', 'GPS Longitude', $s['geo_lng']); ?>
        </div>
    </div>
</div>

<script>
const SS_NONCE = '<?php echo esc_js($nonce); ?>';
const SS_AJAX  = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';

async function saveSettings() {
    const fields = ['phone1','phone1_name','phone2','phone2_name','email','address','map_embed_url',
        'facebook','instagram','whatsapp','youtube','tiktok',
        'about_p1','about_p2','about_signature',
        'stat1_count','stat1_suffix','stat1_label',
        'stat2_count','stat2_suffix','stat2_label',
        'stat3_count','stat3_suffix','stat3_label',
        'cta_title','cta_text','site_name','tagline','geo_lat','geo_lng'];
    const fd = new FormData();
    fd.append('action', 'ss_save_settings');
    fd.append('nonce', SS_NONCE);
    fields.forEach(f => {
        const el = document.getElementById(f);
        if (el) fd.append(f, el.value);
    });
    const r    = await fetch(SS_AJAX, { method: 'POST', body: fd });
    const data = await r.json();
    const notice = document.getElementById('save-notice');
    if (data.success) {
        notice.className = 'ss-notice ss-notice-success';
        notice.textContent = '✓ Settings saved successfully!';
    } else {
        notice.className = 'ss-notice ss-notice-error';
        notice.textContent = '✗ Error: ' + (data.data || 'unknown');
    }
    notice.style.display = 'block';
    window.scrollTo(0, 0);
    setTimeout(() => notice.style.display = 'none', 4000);
}
</script>
