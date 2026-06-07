<?php defined('ABSPATH') || exit;
$nonce    = wp_create_nonce('ss_nonce');
$s        = ss_get_settings();
$logo_url = get_option('ss_logo_url', '');
$fav_url  = get_option('ss_favicon_url', '');
$tab      = sanitize_key($_GET['stab'] ?? 'contact');
?>
<div class="wrap ss-admin">

    <div class="ss-page-header">
        <div class="ss-page-header-left">
            <div class="ss-page-icon-wrap">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
            </div>
            <div>
                <h1 class="ss-page-title">Site Settings</h1>
                <p class="ss-page-subtitle">Identity, contact, social, and content</p>
            </div>
        </div>
        <div class="ss-page-header-right">
            <button class="ss-btn ss-btn-primary" id="save-settings-btn" onclick="saveSettings()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Settings
            </button>
        </div>
    </div>

    <div id="settings-toast" class="ss-toast" style="display:none;"></div>

    <!-- Tab nav -->
    <div class="ss-tab-nav">
        <button class="ss-tab-btn <?php echo $tab==='identity'?'active':''; ?>" onclick="switchSettingsTab('identity')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            Brand &amp; Identity
        </button>
        <button class="ss-tab-btn <?php echo $tab==='contact'||$tab===''?'active':''; ?>" onclick="switchSettingsTab('contact')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
            Contact
        </button>
        <button class="ss-tab-btn <?php echo $tab==='social'?'active':''; ?>" onclick="switchSettingsTab('social')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            Social
        </button>
        <button class="ss-tab-btn <?php echo $tab==='about'?'active':''; ?>" onclick="switchSettingsTab('about')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 10-16 0"/></svg>
            About
        </button>
        <button class="ss-tab-btn <?php echo $tab==='cta'?'active':''; ?>" onclick="switchSettingsTab('cta')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            CTA
        </button>
    </div>

    <?php
    $field = function($id, $label, $val, $placeholder='', $type='text') {
        echo '<div class="ss-form-group">';
        echo '<label for="' . esc_attr($id) . '">' . esc_html($label) . '</label>';
        echo '<input type="' . esc_attr($type) . '" id="' . esc_attr($id) . '" value="' . esc_attr($val) . '" placeholder="' . esc_attr($placeholder) . '">';
        echo '</div>';
    };
    $textarea = function($id, $label, $val, $rows=3) {
        echo '<div class="ss-form-group">';
        echo '<label for="' . esc_attr($id) . '">' . esc_html($label) . '</label>';
        echo '<textarea id="' . esc_attr($id) . '" rows="' . (int)$rows . '">' . esc_textarea($val) . '</textarea>';
        echo '</div>';
    };
    ?>

    <!-- Brand & Identity tab -->
    <div class="ss-settings-tab <?php echo $tab==='identity'?'active':''; ?>" id="tab-identity">

        <!-- Logo upload -->
        <div class="ss-card">
            <div class="ss-card-header">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/></svg>
                Studio Logo
            </div>
            <p class="ss-help-text" style="margin-bottom:1rem;">Uploading a logo here will apply it site-wide: navigation header, footer, admin sidebar, browser tab favicon, and social share image.</p>
            <div class="ss-logo-area">
                <div class="ss-logo-preview" id="logo-preview-wrap">
                    <?php if ($logo_url): ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="Logo" id="logo-preview-img" style="max-height:80px;max-width:220px;object-fit:contain;">
                    <div class="ss-logo-preview-label">Current logo</div>
                    <?php else: ?>
                    <div id="logo-preview-empty">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ddd" stroke-width="1.5"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        <p style="color:#aaa;font-size:.8rem;margin:.5rem 0 0;">No logo set</p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="ss-logo-upload-area">
                    <label class="ss-upload-label" for="logo-file-input">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg>
                        Choose Logo File
                    </label>
                    <input type="file" id="logo-file-input" accept="image/png,image/jpeg,image/webp,image/svg+xml" style="display:none;" onchange="previewLogo(this)">
                    <p class="ss-field-hint">PNG, WebP, SVG or JPEG · Recommended: 300×100px or square. Will be auto-resized.</p>
                    <div id="logo-upload-progress" style="display:none;">
                        <div class="ss-progress-bar"><div class="ss-progress-fill" id="logo-progress-fill"></div></div>
                        <p class="ss-field-hint" style="margin-top:.35rem;">Uploading &amp; optimizing…</p>
                    </div>
                    <button class="ss-btn ss-btn-primary" id="logo-upload-btn" onclick="uploadLogo()" style="display:none;margin-top:.75rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/></svg>
                        Upload Logo
                    </button>
                    <?php if ($logo_url): ?>
                    <button class="ss-btn ss-btn-danger" onclick="removeLogo()" style="margin-top:.5rem;">Remove Logo</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Site Identity -->
        <div class="ss-card">
            <div class="ss-card-header">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                Site Details
            </div>
            <div class="ss-form-row">
                <?php $field('site_name', 'Studio Name', $s['site_name']); ?>
                <?php $field('tagline', 'Hero Tagline / Slogan', $s['tagline']); ?>
            </div>
            <div class="ss-form-row">
                <?php $field('geo_lat', 'GPS Latitude', $s['geo_lat'], '23.8103'); ?>
                <?php $field('geo_lng', 'GPS Longitude', $s['geo_lng'], '90.4125'); ?>
            </div>
        </div>
    </div>

    <!-- Contact tab -->
    <div class="ss-settings-tab <?php echo ($tab==='contact'||$tab==='')?'active':''; ?>" id="tab-contact">
        <div class="ss-card">
            <div class="ss-card-header">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                Contact Information
            </div>
            <div class="ss-form-row">
                <?php $field('phone1', 'Primary Phone / WhatsApp', $s['phone1'], '+8801XXXXXXXXX'); ?>
                <?php $field('phone1_name', 'Primary Contact Name', $s['phone1_name'], 'e.g. Kowsik'); ?>
            </div>
            <div class="ss-form-row">
                <?php $field('phone2', 'Secondary Phone', $s['phone2'], '+8801XXXXXXXXX'); ?>
                <?php $field('phone2_name', 'Secondary Contact Name', $s['phone2_name'], 'e.g. Dip'); ?>
            </div>
            <div class="ss-form-row">
                <?php $field('email', 'Email Address', $s['email'], 'studio@example.com', 'email'); ?>
                <?php $field('address', 'Studio Address', $s['address'], 'City, District, Bangladesh'); ?>
            </div>
            <?php $textarea('map_embed_url', 'Google Maps Embed URL (paste only the src="" value from the Google Maps embed iframe code)', $s['map_embed_url'], 2); ?>
        </div>
    </div>

    <!-- Social tab -->
    <div class="ss-settings-tab <?php echo $tab==='social'?'active':''; ?>" id="tab-social">
        <div class="ss-card">
            <div class="ss-card-header">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                Social Media &amp; Messaging
            </div>
            <div class="ss-form-row">
                <?php $field('whatsapp', 'WhatsApp Number (digits only, no +)', $s['whatsapp'], '8801XXXXXXXXX'); ?>
                <?php $field('facebook', 'Facebook Page URL', $s['facebook']); ?>
            </div>
            <div class="ss-form-row">
                <?php $field('instagram', 'Instagram URL', $s['instagram']); ?>
                <?php $field('youtube', 'YouTube Channel URL', $s['youtube']); ?>
            </div>
            <?php $field('tiktok', 'TikTok URL', $s['tiktok']); ?>
        </div>
    </div>

    <!-- About tab -->
    <div class="ss-settings-tab <?php echo $tab==='about'?'active':''; ?>" id="tab-about">
        <div class="ss-card">
            <div class="ss-card-header">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 10-16 0"/></svg>
                About Section Content
            </div>
            <?php $textarea('about_p1', 'About Paragraph 1', $s['about_p1'], 4); ?>
            <?php $textarea('about_p2', 'About Paragraph 2', $s['about_p2'], 4); ?>
            <?php $field('about_signature', 'Signature / Quote', $s['about_signature']); ?>
        </div>
        <div class="ss-card">
            <div class="ss-card-header">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Achievement Stats
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;">
                <?php foreach ([1,2,3] as $n): ?>
                <div class="ss-stat-editor">
                    <div class="ss-stat-editor-label">Stat <?php echo $n; ?></div>
                    <?php $field("stat{$n}_count", 'Number', $s["stat{$n}_count"]); ?>
                    <?php $field("stat{$n}_suffix", 'Suffix (e.g. +)', $s["stat{$n}_suffix"]); ?>
                    <?php $field("stat{$n}_label", 'Label', $s["stat{$n}_label"]); ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- CTA tab -->
    <div class="ss-settings-tab <?php echo $tab==='cta'?'active':''; ?>" id="tab-cta">
        <div class="ss-card">
            <div class="ss-card-header">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Call-to-Action Section
            </div>
            <div class="ss-form-group">
                <label>CTA Title
                    <span class="ss-field-hint" style="font-weight:400;">&nbsp;— HTML allowed: <code>&lt;span class="text-gradient"&gt;...&lt;/span&gt;</code></span>
                </label>
                <input type="text" id="cta_title" value="<?php echo esc_attr($s['cta_title']); ?>">
            </div>
            <?php $textarea('cta_text', 'CTA Sub-text', $s['cta_text'], 3); ?>
        </div>
    </div>

</div>

<style>
.ss-page-icon-wrap{width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#0a0805,#1a120a);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.ss-tab-nav{display:flex;gap:.25rem;margin-bottom:1.25rem;background:#fff;border:1px solid #ede8e0;border-radius:10px;padding:.3rem;flex-wrap:wrap;}
.ss-tab-btn{display:flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border:none;background:none;border-radius:7px;font-size:.82rem;font-weight:500;color:#6b6460;cursor:pointer;transition:all .15s;white-space:nowrap;}
.ss-tab-btn.active{background:linear-gradient(135deg,#D4AF37,#c49d2e);color:#0a0805;font-weight:600;}
.ss-tab-btn:hover:not(.active){background:#f4f1eb;color:#1a120a;}
.ss-settings-tab{display:none;}
.ss-settings-tab.active{display:block;}
.ss-logo-area{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;}
.ss-logo-preview{border:2px dashed #ede8e0;border-radius:10px;padding:1.5rem;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:120px;background:#fafaf8;}
.ss-logo-preview-label{font-size:.72rem;color:#9b9490;margin-top:.5rem;}
.ss-logo-upload-area{display:flex;flex-direction:column;gap:.5rem;}
.ss-upload-label{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.1rem;border-radius:8px;background:#f4f1eb;border:1px solid #ede8e0;color:#4a4040;font-size:.85rem;font-weight:500;cursor:pointer;transition:all .18s;}
.ss-upload-label:hover{background:#ede8e0;border-color:#D4AF37;}
.ss-field-hint{font-size:.76rem;color:#9b9490;margin:.25rem 0 0;}
.ss-progress-bar{height:4px;background:#f0ece4;border-radius:2px;overflow:hidden;}
.ss-progress-fill{height:100%;background:linear-gradient(90deg,#D4AF37,#F5D67B);border-radius:2px;width:0%;transition:width .3s;}
.ss-stat-editor{background:#f9f7f3;border:1px solid #ede8e0;border-radius:8px;padding:1rem;}
.ss-stat-editor-label{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9b9490;margin-bottom:.75rem;}
.ss-help-text{font-size:.83rem;color:#6b6460;line-height:1.5;}
.ss-toast{position:fixed;top:80px;right:20px;z-index:99999;padding:.75rem 1.25rem;border-radius:10px;font-size:.88rem;font-weight:500;box-shadow:0 4px 20px rgba(0,0,0,.15);animation:ss-slide-in .25s ease;}
.ss-toast-success{background:#1a120a;color:#D4AF37;border:1px solid rgba(212,175,55,.3);}
.ss-toast-error{background:#fff0f0;color:#c0392b;border:1px solid #ffcdd2;}
@keyframes ss-slide-in{from{transform:translateX(20px);opacity:0;}to{transform:translateX(0);opacity:1;}}
@media(max-width:700px){.ss-logo-area{grid-template-columns:1fr;}.ss-tab-btn span{display:none;}}
</style>

<script>
const SS_NONCE = '<?php echo esc_js($nonce); ?>';
const SS_AJAX  = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';

function switchSettingsTab(tab) {
    document.querySelectorAll('.ss-settings-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.ss-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab)?.classList.add('active');
    event.currentTarget.classList.add('active');
}

function showToast(msg, type='success') {
    const t = document.getElementById('settings-toast');
    t.className = 'ss-toast ss-toast-' + type;
    t.textContent = msg;
    t.style.display = 'block';
    setTimeout(() => t.style.display = 'none', 3500);
}

function previewLogo(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const wrap = document.getElementById('logo-preview-wrap');
        let img = wrap.querySelector('#logo-preview-img');
        if (!img) {
            wrap.innerHTML = '';
            img = document.createElement('img');
            img.id = 'logo-preview-img';
            img.style.cssText = 'max-height:80px;max-width:220px;object-fit:contain;';
            wrap.appendChild(img);
            const lbl = document.createElement('div');
            lbl.className = 'ss-logo-preview-label';
            lbl.textContent = 'Preview';
            wrap.appendChild(lbl);
        }
        img.src = e.target.result;
        document.getElementById('logo-upload-btn').style.display = 'inline-flex';
    };
    reader.readAsDataURL(file);
}

async function uploadLogo() {
    const file = document.getElementById('logo-file-input').files[0];
    if (!file) return;
    const btn = document.getElementById('logo-upload-btn');
    const prog = document.getElementById('logo-upload-progress');
    btn.disabled = true;
    prog.style.display = 'block';
    const fill = document.getElementById('logo-progress-fill');
    let pct = 0;
    const ticker = setInterval(() => { pct = Math.min(pct + 10, 85); fill.style.width = pct + '%'; }, 200);

    const fd = new FormData();
    fd.append('action', 'ss_upload_logo');
    fd.append('nonce', SS_NONCE);
    fd.append('logo', file);

    const r = await fetch(SS_AJAX, { method: 'POST', body: fd });
    const data = await r.json();
    clearInterval(ticker);
    fill.style.width = '100%';
    setTimeout(() => { prog.style.display = 'none'; btn.disabled = false; }, 500);

    if (data.success) {
        showToast('Logo uploaded successfully');
        setTimeout(() => location.reload(), 800);
    } else {
        showToast('Upload failed: ' + (data.data || 'unknown error'), 'error');
    }
}

async function removeLogo() {
    if (!confirm('Remove the current logo?')) return;
    const fd = new FormData();
    fd.append('action', 'ss_upload_logo');
    fd.append('nonce', SS_NONCE);
    fd.append('remove', '1');
    const r = await fetch(SS_AJAX, { method: 'POST', body: fd });
    const d = await r.json();
    if (d.success) { showToast('Logo removed'); setTimeout(() => location.reload(), 800); }
}

async function saveSettings() {
    const btn = document.getElementById('save-settings-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Saving…';

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

    const r = await fetch(SS_AJAX, { method: 'POST', body: fd });
    const data = await r.json();

    btn.disabled = false;
    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Save Settings';

    if (data.success) {
        showToast('Settings saved successfully');
    } else {
        showToast('Error: ' + (data.data || 'unknown'), 'error');
    }
}
</script>
