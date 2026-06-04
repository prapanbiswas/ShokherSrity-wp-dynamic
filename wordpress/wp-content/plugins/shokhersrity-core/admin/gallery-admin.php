<?php defined('ABSPATH') || exit;
$nonce   = wp_create_nonce('ss_nonce');
$catalog = (array) get_option('ss_image_catalog', []);
$hero    = ss_get_hero();
$tab     = sanitize_text_field($_GET['tab'] ?? 'images');

$categories = [
    'wedding'    => 'Wedding',
    'bride'      => 'Bride',
    'reception'  => 'Reception',
    'engagement' => 'Engagement',
    'babyshower' => 'Baby Shower',
    'baby'       => 'Baby Photoshoot',
];

// Filter by category
$filter_cat = sanitize_key($_GET['cat'] ?? 'all');
if ($filter_cat !== 'all') {
    $display = array_filter($catalog, fn($i) => ($i['category'] ?? '') === $filter_cat);
} else {
    $display = $catalog;
}
?>
<div class="wrap ss-admin">
    <div class="ss-header">
        <div class="ss-header-logo">🖼</div>
        <div>
            <h1>Gallery Manager</h1>
            <p><?php echo count($catalog); ?> images in catalog</p>
        </div>
        <div style="margin-left:auto;">
            <button class="ss-btn ss-btn-primary" id="open-upload-modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Upload Images
            </button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="ss-tabs">
        <button class="ss-tab <?php echo $tab !== 'hero' ? 'active' : ''; ?>" onclick="switchTab('images')">Gallery Images</button>
        <button class="ss-tab <?php echo $tab === 'hero' ? 'active' : ''; ?>" onclick="switchTab('hero')">Hero Images</button>
    </div>

    <!-- Gallery Images Tab -->
    <div class="ss-tab-content <?php echo $tab !== 'hero' ? 'active' : ''; ?>" id="tab-images">
        <!-- Category Filter -->
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1.25rem;">
            <?php
            $filters = ['all' => 'All (' . count($catalog) . ')'];
            foreach ($categories as $slug => $name) {
                $cnt = count(array_filter($catalog, fn($i) => ($i['category'] ?? '') === $slug));
                if ($cnt) $filters[$slug] = $name . ' (' . $cnt . ')';
            }
            foreach ($filters as $slug => $label): ?>
            <a href="<?php echo admin_url('admin.php?page=ss-gallery&tab=images&cat=' . $slug); ?>"
               class="ss-btn <?php echo $filter_cat === $slug ? 'ss-btn-primary' : 'ss-btn-secondary'; ?>">
                <?php echo esc_html($label); ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Image Grid -->
        <div class="ss-grid" id="image-grid">
            <?php foreach ($display as $img): ?>
            <div class="ss-image-card" data-id="<?php echo esc_attr($img['id']); ?>">
                <img src="<?php echo esc_url(home_url($img['src'])); ?>" alt="" loading="lazy">
                <div class="ss-image-meta"><?php echo esc_html(ucfirst($img['category'] ?? '')); ?> · <?php echo $img['width']; ?>×<?php echo $img['height']; ?></div>
                <div class="ss-image-overlay">
                    <button class="ss-btn ss-btn-secondary" style="padding:0.4rem 0.7rem;font-size:0.75rem;" onclick="setHeroFromGallery('<?php echo esc_js($img['src']); ?>', <?php echo (int)$img['width']; ?>, <?php echo (int)$img['height']; ?>)">
                        <?php echo ((int)$img['width'] >= (int)$img['height']) ? '🖥 Set Desktop' : '📱 Set Mobile'; ?>
                    </button>
                    <button class="ss-btn ss-btn-danger" style="padding:0.4rem 0.7rem;font-size:0.75rem;" onclick="deleteImage('<?php echo esc_js($img['id']); ?>', this)">Delete</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (empty($display)): ?>
        <div style="text-align:center;padding:3rem;color:#888;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.5" style="display:block;margin:0 auto 1rem;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            <p>No images found. <a href="#" onclick="document.getElementById('open-upload-modal').click();return false;">Upload some?</a></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Hero Images Tab -->
    <div class="ss-tab-content <?php echo $tab === 'hero' ? 'active' : ''; ?>" id="tab-hero">
        <div class="ss-card">
            <h2>Hero Image Pair</h2>
            <p style="color:#888;font-size:0.88rem;margin-bottom:1.5rem;">The homepage hero shows the <strong>desktop</strong> image on screens ≥769px and the <strong>mobile</strong> image on smaller screens. Ideally desktop is landscape (wide) and mobile is portrait (tall).</p>

            <div class="hero-pair-preview" id="hero-preview-wrapper">
                <div class="hero-preview-slot" id="slot-desktop">
                    <span class="slot-label">Desktop (landscape)</span>
                    <img src="<?php echo esc_url($hero['desktop']); ?>?v=<?php echo time(); ?>" alt="Desktop" id="preview-desktop">
                    <div style="position:absolute;bottom:0;left:0;right:0;padding:0.5rem;display:flex;gap:0.5rem;">
                        <button class="ss-btn ss-btn-secondary" style="flex:1;font-size:0.75rem;" onclick="openHeroPicker('desktop')">Choose from Gallery</button>
                        <label class="ss-btn ss-btn-secondary" style="flex:1;font-size:0.75rem;cursor:pointer;">Upload New<input type="file" accept="image/*" style="display:none" onchange="heroFileUpload(this,'desktop')"></label>
                    </div>
                </div>
                <div class="hero-preview-slot" id="slot-mobile">
                    <span class="slot-label">Mobile (portrait)</span>
                    <img src="<?php echo esc_url($hero['mobile']); ?>?v=<?php echo time(); ?>" alt="Mobile" id="preview-mobile">
                    <div style="position:absolute;bottom:0;left:0;right:0;padding:0.5rem;display:flex;gap:0.5rem;">
                        <button class="ss-btn ss-btn-secondary" style="flex:1;font-size:0.75rem;" onclick="openHeroPicker('mobile')">Choose from Gallery</button>
                        <label class="ss-btn ss-btn-secondary" style="flex:1;font-size:0.75rem;cursor:pointer;">Upload New<input type="file" accept="image/*" style="display:none" onchange="heroFileUpload(this,'mobile')"></label>
                    </div>
                </div>
            </div>

            <div style="margin-top:1rem;display:flex;gap:1rem;align-items:center;">
                <button class="ss-btn ss-btn-primary" id="save-hero-btn" onclick="saveHero()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Hero Pair
                </button>
                <span id="hero-save-msg" style="font-size:0.85rem;color:green;display:none;">✓ Saved!</span>
                <span id="pair-hint" style="font-size:0.85rem;color:#D4AF37;display:none;"></span>
            </div>
        </div>

        <!-- Smart Pairing Guide -->
        <div class="ss-card">
            <h2>Smart Pairing Guide</h2>
            <p style="color:#888;font-size:0.88rem;">When you click "Set Desktop" or "Set Mobile" on a gallery image, the system suggests the correct slot based on image orientation:</p>
            <ul style="color:#555;font-size:0.88rem;line-height:1.8;">
                <li><strong>Landscape images (width > height)</strong> → shown as Desktop option → set as desktop hero</li>
                <li><strong>Portrait images (height > width)</strong> → shown as Mobile option → set as mobile hero</li>
                <li>You can override any image into either slot manually using "Choose from Gallery" or "Upload New"</li>
            </ul>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="upload-modal" style="display:flex;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)closeUploadModal()">
    <div style="background:white;border-radius:16px;padding:2rem;width:520px;max-width:95vw;max-height:85vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
            <h3 style="margin:0;font-size:1.1rem;">Upload Images</h3>
            <button onclick="closeUploadModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#888;">&times;</button>
        </div>

        <div class="ss-form-group">
            <label>Category</label>
            <select id="upload-category">
                <?php foreach ($categories as $slug => $name): ?>
                <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="ss-upload-zone" id="upload-zone" onclick="document.getElementById('file-input').click()">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.5" style="display:block;margin:0 auto;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <p style="margin:1rem 0 0.25rem;font-size:1rem;color:#333;">Click to browse or drag & drop</p>
            <p>WebP, JPEG, PNG — multiple files supported</p>
            <input type="file" id="file-input" accept="image/webp,image/jpeg,image/png" multiple style="display:none" onchange="handleFileSelect(this.files)">
        </div>

        <div id="upload-queue" style="margin-top:1rem;"></div>
        <div style="margin-top:1rem;display:flex;justify-content:flex-end;gap:0.75rem;">
            <button class="ss-btn ss-btn-secondary" onclick="closeUploadModal()">Close</button>
            <button class="ss-btn ss-btn-primary" id="upload-all-btn" onclick="uploadAll()" style="display:none;">Upload All</button>
        </div>
    </div>
</div>

<!-- Hero Gallery Picker -->
<div id="hero-picker-modal" style="display:flex;position:fixed;inset:0;background:rgba(0,0,0,0.8);z-index:9998;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:16px;padding:2rem;width:800px;max-width:95vw;max-height:85vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h3 style="margin:0;" id="picker-title">Choose Desktop Hero</h3>
            <button onclick="closeHeroPicker()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
        </div>
        <div class="ss-grid" id="picker-grid">
            <?php foreach ($catalog as $img): ?>
            <div class="ss-image-card" onclick="heroPickFromGallery('<?php echo esc_js($img['src']); ?>')" style="cursor:pointer;" title="<?php echo esc_attr($img['width']); ?>×<?php echo esc_attr($img['height']); ?>">
                <img src="<?php echo esc_url(home_url($img['src'])); ?>" alt="" loading="lazy">
                <div class="ss-image-meta"><?php echo $img['width']; ?>×<?php echo $img['height']; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
const SS_NONCE = '<?php echo esc_js($nonce); ?>';
const SS_AJAX = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
let pendingFiles = [];
let heroPickerTarget = 'desktop';
let heroDesktopSrc = '<?php echo esc_js($hero['desktop']); ?>';
let heroMobileSrc  = '<?php echo esc_js($hero['mobile']); ?>';

// Tab switching
function switchTab(tab) {
    document.querySelectorAll('.ss-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.ss-tab-content').forEach(c => c.classList.remove('active'));
    event.currentTarget.classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
}

// Upload Modal
document.getElementById('open-upload-modal').addEventListener('click', () => {
    document.getElementById('upload-modal').style.display = 'flex';
});
function closeUploadModal() {
    document.getElementById('upload-modal').style.display = 'none';
    pendingFiles = [];
    document.getElementById('upload-queue').innerHTML = '';
    document.getElementById('upload-all-btn').style.display = 'none';
}

function handleFileSelect(files) {
    pendingFiles = Array.from(files);
    const q = document.getElementById('upload-queue');
    q.innerHTML = '';
    pendingFiles.forEach((f, i) => {
        const div = document.createElement('div');
        div.id = 'file-row-' + i;
        div.style.cssText = 'display:flex;align-items:center;gap:0.75rem;padding:0.5rem;border-bottom:1px solid #f0ece0;font-size:0.85rem;';
        div.innerHTML = '<span style="flex:1;">' + f.name + '</span><span style="color:#888;">' + (f.size / 1024).toFixed(0) + 'KB</span><span id="status-' + i + '" style="width:80px;text-align:right;color:#888;">Pending</span>';
        q.appendChild(div);
    });
    if (pendingFiles.length) document.getElementById('upload-all-btn').style.display = 'inline-flex';
}

async function uploadAll() {
    const cat = document.getElementById('upload-category').value;
    const catLabels = {wedding:'Wedding',bride:'Bride',reception:'Reception',engagement:'Engagement',babyshower:'Baby Shower',baby:'Baby Photoshoot',gallery:'Gallery'};
    for (let i = 0; i < pendingFiles.length; i++) {
        const statusEl = document.getElementById('status-' + i);
        statusEl.textContent = 'Uploading…';
        statusEl.style.color = '#D4AF37';
        const fd = new FormData();
        fd.append('action', 'ss_upload_image');
        fd.append('nonce', SS_NONCE);
        fd.append('image', pendingFiles[i]);
        fd.append('category', cat);
        fd.append('label', catLabels[cat] || cat);
        try {
            const r = await fetch(SS_AJAX, { method: 'POST', body: fd });
            const data = await r.json();
            if (data.success) {
                statusEl.textContent = '✓ Done';
                statusEl.style.color = 'green';
                addImageToGrid(data.data);
            } else {
                statusEl.textContent = '✗ Error';
                statusEl.style.color = 'red';
            }
        } catch(e) {
            statusEl.textContent = '✗ Failed';
            statusEl.style.color = 'red';
        }
    }
}

function addImageToGrid(img) {
    const grid = document.getElementById('image-grid');
    const isLandscape = img.width >= img.height;
    const div = document.createElement('div');
    div.className = 'ss-image-card';
    div.dataset.id = img.id;
    div.innerHTML = `<img src="${img.src}" alt="" loading="lazy">
        <div class="ss-image-meta">${img.category} · ${img.width}×${img.height}</div>
        <div class="ss-image-overlay">
            <button class="ss-btn ss-btn-secondary" style="padding:0.4rem 0.7rem;font-size:0.75rem;" onclick="setHeroFromGallery('${img.src}',${img.width},${img.height})">${isLandscape ? '🖥 Set Desktop' : '📱 Set Mobile'}</button>
            <button class="ss-btn ss-btn-danger" style="padding:0.4rem 0.7rem;font-size:0.75rem;" onclick="deleteImage('${img.id}',this)">Delete</button>
        </div>`;
    grid.prepend(div);
}

async function deleteImage(id, btn) {
    if (!confirm('Delete this image? This cannot be undone.')) return;
    const fd = new FormData();
    fd.append('action', 'ss_delete_image');
    fd.append('nonce', SS_NONCE);
    fd.append('id', id);
    const r = await fetch(SS_AJAX, { method: 'POST', body: fd });
    const data = await r.json();
    if (data.success) {
        const card = document.querySelector(`.ss-image-card[data-id="${id}"]`);
        if (card) card.remove();
    }
}

// Hero picker
function openHeroPicker(target) {
    heroPickerTarget = target;
    document.getElementById('picker-title').textContent = 'Choose ' + (target === 'desktop' ? 'Desktop Hero' : 'Mobile Hero');
    document.getElementById('hero-picker-modal').style.display = 'flex';
}
function closeHeroPicker() {
    document.getElementById('hero-picker-modal').style.display = 'none';
}
function heroPickFromGallery(src) {
    if (heroPickerTarget === 'desktop') {
        heroDesktopSrc = src;
        document.getElementById('preview-desktop').src = src;
    } else {
        heroMobileSrc = src;
        document.getElementById('preview-mobile').src = src;
    }
    closeHeroPicker();
}
function setHeroFromGallery(src, w, h) {
    const isLandscape = (w >= h);
    const target = isLandscape ? 'desktop' : 'mobile';
    if (target === 'desktop') {
        heroDesktopSrc = src;
        const el = document.getElementById('preview-desktop');
        if (el) el.src = src;
    } else {
        heroMobileSrc = src;
        const el = document.getElementById('preview-mobile');
        if (el) el.src = src;
    }
    // Switch to hero tab
    document.querySelectorAll('.ss-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.ss-tab-content').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.ss-tab')[1].classList.add('active');
    document.getElementById('tab-hero').classList.add('active');
    // Smart pairing: auto-prompt for companion slot
    const companionTarget = isLandscape ? 'mobile' : 'desktop';
    const companionLabel  = isLandscape ? 'portrait (mobile)' : 'landscape (desktop)';
    const hint = document.getElementById('pair-hint');
    if (hint) {
        hint.textContent = '✓ ' + (isLandscape ? 'Desktop' : 'Mobile') + ' hero set! Now pick a ' + companionLabel + ' image.';
        hint.style.display = 'inline';
        setTimeout(() => { openHeroPicker(companionTarget); }, 700);
        setTimeout(() => { hint.style.display = 'none'; }, 6000);
    }
}

async function heroFileUpload(input, target) {
    if (!input.files[0]) return;
    const fd = new FormData();
    fd.append('action', 'ss_update_hero');
    fd.append('nonce', SS_NONCE);
    fd.append(target + '_file', input.files[0]);
    fd.append('desktop', heroDesktopSrc);
    fd.append('mobile', heroMobileSrc);
    const btn = document.getElementById('save-hero-btn');
    btn.disabled = true;
    const r = await fetch(SS_AJAX, { method: 'POST', body: fd });
    const data = await r.json();
    if (data.success) {
        heroDesktopSrc = data.data.desktop;
        heroMobileSrc  = data.data.mobile;
        document.getElementById('preview-desktop').src = heroDesktopSrc + '?v=' + Date.now();
        document.getElementById('preview-mobile').src  = heroMobileSrc  + '?v=' + Date.now();
        document.getElementById('hero-save-msg').style.display = 'inline';
        setTimeout(() => document.getElementById('hero-save-msg').style.display = 'none', 2000);
    }
    btn.disabled = false;
}

async function saveHero() {
    const fd = new FormData();
    fd.append('action', 'ss_update_hero');
    fd.append('nonce', SS_NONCE);
    fd.append('desktop', heroDesktopSrc);
    fd.append('mobile', heroMobileSrc);
    const btn = document.getElementById('save-hero-btn');
    btn.disabled = true;
    const r = await fetch(SS_AJAX, { method: 'POST', body: fd });
    const data = await r.json();
    if (data.success) {
        const msg = document.getElementById('hero-save-msg');
        msg.style.display = 'inline';
        setTimeout(() => msg.style.display = 'none', 2500);
    }
    btn.disabled = false;
}

// Drag & drop upload zone
const zone = document.getElementById('upload-zone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
zone.addEventListener('drop', e => {
    e.preventDefault(); zone.classList.remove('drag-over');
    handleFileSelect(e.dataTransfer.files);
});

// Initially hide modal (inline style was display:flex for CSS centering)
document.getElementById('upload-modal').style.display = 'none';
document.getElementById('hero-picker-modal').style.display = 'none';
</script>
