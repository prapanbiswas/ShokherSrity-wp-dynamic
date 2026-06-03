<?php defined('ABSPATH') || exit;
$nonce   = wp_create_nonce('ss_nonce');
$data    = ss_get_packages();
$tiers   = $data['tiers']   ?? [];
$packages = $data['packages'] ?? [];
$json    = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
<div class="wrap ss-admin">
    <div class="ss-header">
        <div class="ss-header-logo">💎</div>
        <div>
            <h1>Packages Manager</h1>
            <p><?php echo count($packages); ?> packages across <?php echo count($tiers); ?> tiers</p>
        </div>
        <div style="margin-left:auto;display:flex;gap:0.75rem;">
            <button class="ss-btn ss-btn-primary" onclick="savePackages()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save All Changes
            </button>
        </div>
    </div>

    <div id="save-notice" style="display:none;" class="ss-notice ss-notice-success">✓ Packages saved successfully.</div>

    <?php foreach ($tiers as $tier):
        $tier_pkgs = array_filter($packages, fn($p) => $p['tier'] === $tier['id']);
        $tier_pkgs = array_values($tier_pkgs);
    ?>
    <div class="ss-card">
        <h2>
            <?php echo esc_html($tier['label']); ?>
            <span class="ss-badge ss-badge-gold" style="margin-left:0.5rem;"><?php echo count($tier_pkgs); ?> packages</span>
            <span style="margin-left:auto;font-size:0.8rem;color:#888;"><?php echo esc_html($tier['columns']); ?> columns on site</span>
        </h2>

        <?php foreach ($tier_pkgs as $pkg): ?>
        <div class="ss-card" style="border:1px solid rgba(212,175,55,0.15);background:#fdfcf8;margin-bottom:1rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <h3 style="margin:0;font-size:1rem;" id="pkg-name-display-<?php echo esc_attr($pkg['id']); ?>"><?php echo esc_html($pkg['name']); ?></h3>
                <div style="display:flex;gap:0.5rem;align-items:center;">
                    <?php if ($pkg['badge']): ?><span class="ss-badge ss-badge-dark"><?php echo esc_html($pkg['badge']); ?></span><?php endif; ?>
                    <?php if ($pkg['is_popular']): ?><span class="ss-badge" style="background:rgba(212,175,55,0.15);color:#8a6d00;">⭐ Popular</span><?php endif; ?>
                    <button class="ss-btn ss-btn-secondary" onclick="togglePkgEdit('<?php echo esc_js($pkg['id']); ?>')">Edit</button>
                </div>
            </div>

            <!-- View Mode -->
            <div id="pkg-view-<?php echo esc_attr($pkg['id']); ?>">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0.75rem;font-size:0.85rem;color:#555;">
                    <div><strong>Price:</strong> <?php echo esc_html($pkg['price'] . ' ' . $pkg['period']); ?></div>
                    <div><strong>Note:</strong> <?php echo esc_html($pkg['note']); ?></div>
                    <div><strong>Style:</strong> <code><?php echo esc_html($pkg['style']); ?></code></div>
                </div>
                <div style="margin-top:0.75rem;font-size:0.83rem;color:#777;">
                    <strong>Features:</strong> <?php echo esc_html(implode(' · ', array_slice($pkg['features'], 0, 4))); ?><?php echo count($pkg['features']) > 4 ? ' + ' . (count($pkg['features']) - 4) . ' more' : ''; ?>
                </div>
            </div>

            <!-- Edit Mode (hidden) -->
            <div id="pkg-edit-<?php echo esc_attr($pkg['id']); ?>" style="display:none;">
                <div class="ss-form-row">
                    <div class="ss-form-group"><label>Package Name</label><input type="text" data-field="name" value="<?php echo esc_attr($pkg['name']); ?>" onchange="updatePkgField('<?php echo esc_js($pkg['id']); ?>','name',this.value)"></div>
                    <div class="ss-form-group"><label>Price</label><input type="text" data-field="price" value="<?php echo esc_attr($pkg['price']); ?>" onchange="updatePkgField('<?php echo esc_js($pkg['id']); ?>','price',this.value)"></div>
                </div>
                <div class="ss-form-row">
                    <div class="ss-form-group"><label>Period (e.g., / day)</label><input type="text" data-field="period" value="<?php echo esc_attr($pkg['period']); ?>" onchange="updatePkgField('<?php echo esc_js($pkg['id']); ?>','period',this.value)"></div>
                    <div class="ss-form-group"><label>Subtitle / Note</label><input type="text" data-field="note" value="<?php echo esc_attr($pkg['note']); ?>" onchange="updatePkgField('<?php echo esc_js($pkg['id']); ?>','note',this.value)"></div>
                </div>
                <div class="ss-form-row">
                    <div class="ss-form-group"><label>Badge (leave blank for none)</label><input type="text" data-field="badge" value="<?php echo esc_attr($pkg['badge'] ?? ''); ?>" onchange="updatePkgField('<?php echo esc_js($pkg['id']); ?>','badge',this.value)"></div>
                    <div class="ss-form-group"><label>Card Style</label>
                        <select data-field="style" onchange="updatePkgField('<?php echo esc_js($pkg['id']); ?>','style',this.value)">
                            <?php foreach (['standard'=>'Standard','featured'=>'Featured (golden border)','liquid-glass'=>'Liquid Glass (dark)','exclusive'=>'Exclusive (dark + exclusive class)'] as $sv=>$sl): ?>
                            <option value="<?php echo esc_attr($sv); ?>" <?php selected($pkg['style'], $sv); ?>><?php echo esc_html($sl); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="ss-form-group">
                    <label>Features (one per line — add [COMPLEMENTARY] for complementary badge)</label>
                    <textarea data-field="features" rows="<?php echo count($pkg['features']); ?>" onchange="updatePkgFeatures('<?php echo esc_js($pkg['id']); ?>',this.value)"><?php echo esc_textarea(implode("\n", $pkg['features'])); ?></textarea>
                </div>
                <div class="ss-form-row">
                    <div class="ss-form-group"><label>Complementary Note</label><input type="text" data-field="complementary_note" value="<?php echo esc_attr($pkg['complementary_note'] ?? ''); ?>" onchange="updatePkgField('<?php echo esc_js($pkg['id']); ?>','complementary_note',this.value)"></div>
                    <div class="ss-form-group"><label>WhatsApp Message</label><input type="text" data-field="whatsapp_message" value="<?php echo esc_attr($pkg['whatsapp_message'] ?? ''); ?>" onchange="updatePkgField('<?php echo esc_js($pkg['id']); ?>','whatsapp_message',this.value)"></div>
                </div>
                <div class="ss-form-group">
                    <label><input type="checkbox" <?php checked($pkg['is_popular']); ?> onchange="updatePkgField('<?php echo esc_js($pkg['id']); ?>','is_popular',this.checked)"> Mark as Popular</label>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>

<script>
const SS_NONCE  = '<?php echo esc_js($nonce); ?>';
const SS_AJAX   = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
let pkgData = <?php echo $json; ?>;

function togglePkgEdit(id) {
    const view = document.getElementById('pkg-view-' + id);
    const edit = document.getElementById('pkg-edit-' + id);
    const isEditing = edit.style.display !== 'none';
    view.style.display = isEditing ? 'block' : 'none';
    edit.style.display = isEditing ? 'none' : 'block';
}

function updatePkgField(id, field, val) {
    pkgData.packages = pkgData.packages.map(p => {
        if (p.id !== id) return p;
        return { ...p, [field]: field === 'is_popular' ? !!val : val };
    });
}

function updatePkgFeatures(id, val) {
    const features = val.split('\n').map(s => s.trim()).filter(Boolean);
    pkgData.packages = pkgData.packages.map(p => p.id !== id ? p : { ...p, features });
}

async function savePackages() {
    const fd = new FormData();
    fd.append('action', 'ss_save_packages');
    fd.append('nonce', SS_NONCE);
    fd.append('packages', JSON.stringify(pkgData));
    const r = await fetch(SS_AJAX, { method: 'POST', body: fd });
    const data = await r.json();
    const notice = document.getElementById('save-notice');
    if (data.success) {
        notice.className = 'ss-notice ss-notice-success';
        notice.textContent = '✓ Packages saved successfully — ' + data.data.saved + ' packages.';
    } else {
        notice.className = 'ss-notice ss-notice-error';
        notice.textContent = '✗ Error: ' + (data.data || 'unknown');
    }
    notice.style.display = 'block';
    window.scrollTo(0, 0);
    setTimeout(() => notice.style.display = 'none', 4000);
}
</script>
