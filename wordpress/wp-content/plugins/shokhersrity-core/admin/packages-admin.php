<?php defined('ABSPATH') || exit;
$nonce   = wp_create_nonce('ss_nonce');
$data    = ss_get_packages();
$tiers   = $data['tiers']    ?? [];
$packages = $data['packages'] ?? [];
$json    = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
<div class="wrap ss-admin">

    <div class="ss-page-header">
        <div class="ss-page-header-left">
            <div class="ss-page-icon-wrap">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            <div>
                <h1 class="ss-page-title">Packages Manager</h1>
                <p class="ss-page-subtitle"><?php echo count($packages); ?> packages across <?php echo count($tiers); ?> tiers</p>
            </div>
        </div>
        <div class="ss-page-header-right">
            <button class="ss-btn ss-btn-primary" id="save-pkgs-btn" onclick="savePackages()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save All Changes
            </button>
        </div>
    </div>

    <div id="pkg-toast" class="ss-toast" style="display:none;"></div>

    <?php foreach ($tiers as $tier):
        $tier_pkgs = array_values(array_filter($packages, fn($p) => $p['tier'] === $tier['id']));
    ?>
    <div class="ss-card" style="margin-bottom:1.25rem;">
        <div class="ss-card-header">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            <?php echo esc_html($tier['label']); ?>
            <span class="ss-badge ss-badge-gold" style="margin-left:.5rem;"><?php echo count($tier_pkgs); ?></span>
            <span class="ss-field-hint" style="margin-left:auto;"><?php echo (int)$tier['columns']; ?> columns on website</span>
        </div>

        <?php foreach ($tier_pkgs as $pkg): ?>
        <div class="ss-pkg-card" id="pkg-<?php echo esc_attr($pkg['id']); ?>">
            <div class="ss-pkg-header">
                <div class="ss-pkg-header-left">
                    <div class="ss-pkg-name" id="pkg-name-<?php echo esc_attr($pkg['id']); ?>"><?php echo esc_html($pkg['name']); ?></div>
                    <div class="ss-pkg-price"><?php echo esc_html($pkg['price'] . ' ' . $pkg['period']); ?></div>
                    <div style="display:flex;gap:.35rem;flex-wrap:wrap;margin-top:.25rem;">
                        <?php if (!empty($pkg['badge'])): ?>
                        <span class="ss-badge ss-badge-dark"><?php echo esc_html($pkg['badge']); ?></span>
                        <?php endif; ?>
                        <?php if ($pkg['is_popular']): ?>
                        <span class="ss-badge ss-badge-gold">Popular</span>
                        <?php endif; ?>
                        <span class="ss-badge" style="background:#f4f1eb;color:#6b6460;"><?php echo esc_html($pkg['style']); ?></span>
                    </div>
                </div>
                <button class="ss-btn ss-btn-sm ss-btn-outline" onclick="togglePkgEdit('<?php echo esc_js($pkg['id']); ?>')">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit
                </button>
            </div>

            <!-- View mode -->
            <div id="pkg-view-<?php echo esc_attr($pkg['id']); ?>">
                <div class="ss-pkg-features-preview">
                    <?php foreach (array_slice($pkg['features'], 0, 4) as $feat): ?>
                    <span class="ss-feat-chip"><?php echo esc_html($feat); ?></span>
                    <?php endforeach; ?>
                    <?php if (count($pkg['features']) > 4): ?>
                    <span class="ss-feat-chip" style="color:#9b9490;">+<?php echo count($pkg['features']) - 4; ?> more</span>
                    <?php endif; ?>
                </div>
                <?php if ($pkg['note']): ?><p class="ss-field-hint" style="margin-top:.5rem;"><?php echo esc_html($pkg['note']); ?></p><?php endif; ?>
            </div>

            <!-- Edit mode -->
            <div id="pkg-edit-<?php echo esc_attr($pkg['id']); ?>" style="display:none;border-top:1px solid #ede8e0;margin-top:.75rem;padding-top:.75rem;">
                <div class="ss-form-row">
                    <div class="ss-form-group"><label>Package Name</label><input type="text" data-field="name" value="<?php echo esc_attr($pkg['name']); ?>" onchange="updatePkgField('<?php echo esc_js($pkg['id']); ?>','name',this.value)"></div>
                    <div class="ss-form-group"><label>Price (with currency)</label><input type="text" data-field="price" value="<?php echo esc_attr($pkg['price']); ?>" onchange="updatePkgField('<?php echo esc_js($pkg['id']); ?>','price',this.value)"></div>
                </div>
                <div class="ss-form-row">
                    <div class="ss-form-group"><label>Period (e.g. / day)</label><input type="text" data-field="period" value="<?php echo esc_attr($pkg['period']); ?>" onchange="updatePkgField('<?php echo esc_js($pkg['id']); ?>','period',this.value)"></div>
                    <div class="ss-form-group"><label>Short Note / Subtitle</label><input type="text" data-field="note" value="<?php echo esc_attr($pkg['note']); ?>" onchange="updatePkgField('<?php echo esc_js($pkg['id']); ?>','note',this.value)"></div>
                </div>
                <div class="ss-form-row">
                    <div class="ss-form-group"><label>Badge Text (blank = none)</label><input type="text" data-field="badge" value="<?php echo esc_attr($pkg['badge'] ?? ''); ?>" onchange="updatePkgField('<?php echo esc_js($pkg['id']); ?>','badge',this.value)"></div>
                    <div class="ss-form-group"><label>Card Style</label>
                        <select data-field="style" onchange="updatePkgField('<?php echo esc_js($pkg['id']); ?>','style',this.value)">
                            <?php foreach (['standard'=>'Standard','featured'=>'Featured (gold border)','liquid-glass'=>'Liquid Glass (dark)','exclusive'=>'Exclusive (premium)'] as $sv=>$sl): ?>
                            <option value="<?php echo esc_attr($sv); ?>" <?php selected($pkg['style'],$sv); ?>><?php echo esc_html($sl); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="ss-form-group">
                    <label>Features <span class="ss-field-hint">(one per line — add [COMPLEMENTARY] for complementary badge)</span></label>
                    <textarea rows="<?php echo max(4, count($pkg['features'])); ?>" onchange="updatePkgFeatures('<?php echo esc_js($pkg['id']); ?>',this.value)"><?php echo esc_textarea(implode("\n", $pkg['features'])); ?></textarea>
                </div>
                <div class="ss-form-row">
                    <div class="ss-form-group"><label>Complementary Note</label><input type="text" value="<?php echo esc_attr($pkg['complementary_note'] ?? ''); ?>" onchange="updatePkgField('<?php echo esc_js($pkg['id']); ?>','complementary_note',this.value)"></div>
                    <div class="ss-form-group"><label>WhatsApp Pre-fill Message</label><input type="text" value="<?php echo esc_attr($pkg['whatsapp_message'] ?? ''); ?>" onchange="updatePkgField('<?php echo esc_js($pkg['id']); ?>','whatsapp_message',this.value)"></div>
                </div>
                <div class="ss-form-group">
                    <label class="ss-checkbox-label">
                        <input type="checkbox" <?php checked($pkg['is_popular']); ?> onchange="updatePkgField('<?php echo esc_js($pkg['id']); ?>','is_popular',this.checked)">
                        Mark as Popular choice
                    </label>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

</div>

<style>
.ss-pkg-card{background:#f9f7f3;border:1px solid #ede8e0;border-radius:10px;padding:1rem;margin-bottom:.75rem;}
.ss-pkg-card:last-child{margin-bottom:0;}
.ss-pkg-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.75rem;}
.ss-pkg-header-left{flex:1;}
.ss-pkg-name{font-weight:700;font-size:.95rem;color:#1a120a;}
.ss-pkg-price{font-size:.82rem;color:#D4AF37;font-weight:600;margin-top:.1rem;}
.ss-pkg-features-preview{display:flex;flex-wrap:wrap;gap:.3rem;}
.ss-feat-chip{display:inline-block;padding:.18rem .55rem;background:#fff;border:1px solid #ede8e0;border-radius:5px;font-size:.72rem;color:#4a4040;}
.ss-checkbox-label{display:flex;align-items:center;gap:.4rem;font-size:.83rem;cursor:pointer;}
.ss-toast{position:fixed;top:80px;right:20px;z-index:99999;padding:.75rem 1.25rem;border-radius:10px;font-size:.88rem;font-weight:500;box-shadow:0 4px 20px rgba(0,0,0,.15);}
.ss-toast-success{background:#1a120a;color:#D4AF37;border:1px solid rgba(212,175,55,.3);}
.ss-toast-error{background:#fff0f0;color:#c0392b;border:1px solid #ffcdd2;}
</style>

<script>
const SS_NONCE = '<?php echo esc_js($nonce); ?>';
const SS_AJAX  = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
let pkgData = <?php echo $json; ?>;

function togglePkgEdit(id) {
    const view = document.getElementById('pkg-view-' + id);
    const edit = document.getElementById('pkg-edit-' + id);
    const isEditing = edit.style.display !== 'none';
    view.style.display = isEditing ? 'block' : 'none';
    edit.style.display = isEditing ? 'none'  : 'block';
}

function updatePkgField(id, field, val) {
    pkgData.packages = pkgData.packages.map(p => {
        if (p.id !== id) return p;
        const upd = { ...p, [field]: field === 'is_popular' ? !!val : val };
        if (field === 'name') document.getElementById('pkg-name-' + id).textContent = val;
        return upd;
    });
}

function updatePkgFeatures(id, val) {
    const features = val.split('\n').map(s => s.trim()).filter(Boolean);
    pkgData.packages = pkgData.packages.map(p => p.id !== id ? p : { ...p, features });
}

function showToast(msg, type='success') {
    const t = document.getElementById('pkg-toast');
    t.className = 'ss-toast ss-toast-' + type;
    t.textContent = msg;
    t.style.display = 'block';
    setTimeout(() => t.style.display = 'none', 3500);
}

async function savePackages() {
    const btn = document.getElementById('save-pkgs-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Saving…';
    const fd = new FormData();
    fd.append('action', 'ss_save_packages');
    fd.append('nonce', SS_NONCE);
    fd.append('packages', JSON.stringify(pkgData));
    const r    = await fetch(SS_AJAX, { method: 'POST', body: fd });
    const data = await r.json();
    btn.disabled = false;
    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Save All Changes';
    if (data.success) showToast(data.data.saved + ' packages saved successfully');
    else showToast('Error: ' + (data.data || 'unknown'), 'error');
}
</script>
