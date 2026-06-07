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

$filter_cat = sanitize_key($_GET['cat'] ?? 'all');
$display    = ($filter_cat !== 'all')
    ? array_filter($catalog, fn($i) => ($i['category'] ?? '') === $filter_cat)
    : $catalog;
?>
<div class="wrap ss-admin">

    <div class="ss-page-header">
        <div class="ss-page-header-left">
            <div class="ss-page-icon-wrap">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            </div>
            <div>
                <h1 class="ss-page-title">Gallery Manager</h1>
                <p class="ss-page-subtitle"><?php echo count($catalog); ?> images in catalog</p>
            </div>
        </div>
        <div class="ss-page-header-right">
            <button class="ss-btn ss-btn-primary" id="open-upload-modal">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Upload Images
            </button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="ss-tab-nav" style="margin-bottom:1.25rem;">
        <button class="ss-tab-btn <?php echo $tab !== 'hero' ? 'active' : ''; ?>" onclick="switchGalleryTab('images', this)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            Gallery Images
        </button>
        <button class="ss-tab-btn <?php echo $tab === 'hero' ? 'active' : ''; ?>" onclick="switchGalleryTab('hero', this)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Hero Images
        </button>
    </div>

    <!-- Gallery Images Tab -->
    <div id="tab-panel-images" class="<?php echo $tab !== 'hero' ? '' : 'ss-hidden'; ?>">

        <!-- Category Filter chips -->
        <div class="ss-filter-chips">
            <?php
            $filters = ['all' => 'All (' . count($catalog) . ')'];
            foreach ($categories as $slug => $name) {
                $cnt = count(array_filter($catalog, fn($i) => ($i['category'] ?? '') === $slug));
                if ($cnt) $filters[$slug] = $name . ' (' . $cnt . ')';
            }
            foreach ($filters as $slug => $label): ?>
            <a href="<?php echo admin_url('admin.php?page=ss-gallery&tab=images&cat=' . $slug); ?>"
               class="ss-chip <?php echo $filter_cat === $slug ? 'ss-chip-active' : ''; ?>">
                <?php echo esc_html($label); ?>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($display)): ?>
        <div class="ss-empty-state">
            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#ddd" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            <p>No images in this category yet.</p>
        </div>
        <?php else: ?>
        <div class="ss-gallery-grid" id="image-grid">
            <?php foreach ($display as $img): ?>
            <div class="ss-image-card" data-id="<?php echo esc_attr($img['id']); ?>">
                <img src="<?php echo esc_url(home_url($img['src'])); ?>" alt="" loading="lazy">
                <div class="ss-image-meta">
                    <?php echo esc_html(ucfirst($img['category'] ?? '')); ?>
                    <?php if ($img['width'] && $img['height']): ?>&middot; <?php echo $img['width']; ?>&times;<?php echo $img['height']; ?><?php endif; ?>
                </div>
                <div class="ss-image-overlay">
                    <button class="ss-img-action-btn" onclick="setHeroFromGallery('<?php echo esc_js($img['src']); ?>', <?php echo (int)$img['width']; ?>, <?php echo (int)$img['height']; ?>)" title="<?php echo ((int)$img['width'] >= (int)$img['height']) ? 'Set as Desktop Hero' : 'Set as Mobile Hero'; ?>">
                        <?php if ((int)$img['width'] >= (int)$img['height']): ?>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        Desktop
                        <?php else: ?>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                        Mobile
                        <?php endif; ?>
                    </button>
                    <button class="ss-img-action-btn ss-img-delete-btn" onclick="deleteImage('<?php echo esc_js($img['id']); ?>', this)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                        Delete
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Hero Images Tab -->
    <div id="tab-panel-hero" class="<?php echo $tab === 'hero' ? '' : 'ss-hidden'; ?>">
        <div class="ss-card">
            <div class="ss-card-header">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/></svg>
                Desktop Hero Image
                <span class="ss-field-hint" style="font-weight:400;">&nbsp;(landscape, 1920&times;1080 recommended)</span>
            </div>
            <div class="hero-pair-preview" style="grid-template-columns:3fr 2fr;margin-bottom:1.25rem;">
                <div class="hero-preview-slot" id="desktop-preview">
                    <span class="slot-label">Desktop</span>
                    <?php if ($hero['desktop']): ?>
                    <img src="<?php echo esc_url($hero['desktop']); ?>" alt="Desktop hero" loading="lazy" id="desktop-preview-img">
                    <?php else: ?>
                    <div id="desktop-preview-empty" style="display:flex;align-items:center;justify-content:center;height:160px;color:rgba(255,255,255,.4);font-size:.85rem;">No image set</div>
                    <?php endif; ?>
                </div>
                <div class="hero-preview-slot" id="mobile-preview">
                    <span class="slot-label">Mobile</span>
                    <?php if ($hero['mobile']): ?>
                    <img src="<?php echo esc_url($hero['mobile']); ?>" alt="Mobile hero" loading="lazy" id="mobile-preview-img">
                    <?php else: ?>
                    <div id="mobile-preview-empty" style="display:flex;align-items:center;justify-content:center;height:160px;color:rgba(255,255,255,.4);font-size:.85rem;">No image set</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="ss-form-row">
                <div class="ss-form-group">
                    <label>Desktop Hero URL (or pick from gallery above)</label>
                    <input type="text" id="hero-desktop-url" value="<?php echo esc_attr($hero['desktop']); ?>" placeholder="/wp-content/uploads/...">
                </div>
                <div class="ss-form-group">
                    <label>Mobile Hero URL</label>
                    <input type="text" id="hero-mobile-url" value="<?php echo esc_attr($hero['mobile']); ?>" placeholder="/wp-content/uploads/...">
                </div>
            </div>

            <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-top:.25rem;">
                <div>
                    <label class="ss-upload-label" for="hero-desktop-file">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/></svg>
                        Upload Desktop Image
                    </label>
                    <input type="file" id="hero-desktop-file" accept="image/*" style="display:none;" onchange="previewHeroFile('desktop', this)">
                </div>
                <div>
                    <label class="ss-upload-label" for="hero-mobile-file">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/></svg>
                        Upload Mobile Image
                    </label>
                    <input type="file" id="hero-mobile-file" accept="image/*" style="display:none;" onchange="previewHeroFile('mobile', this)">
                </div>
                <button class="ss-btn ss-btn-primary" id="save-hero-btn" onclick="saveHero()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                    Save Hero Images
                </button>
            </div>
            <div id="hero-progress" style="display:none;margin-top:.75rem;">
                <div class="ss-progress-bar"><div class="ss-progress-fill" id="hero-progress-fill"></div></div>
                <p class="ss-field-hint" style="margin-top:.3rem;">Saving hero images…</p>
            </div>
            <div id="hero-notice" style="display:none;margin-top:.75rem;"></div>

            <p class="ss-field-hint" style="margin-top:1rem;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2" style="vertical-align:middle;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                You can also click any image in the gallery to quickly set it as the desktop or mobile hero.
            </p>
        </div>
    </div>

    <div id="gallery-notice" style="display:none;margin-top:1rem;"></div>

</div>

<!-- Upload Modal -->
<div class="ss-modal-overlay" id="upload-modal" style="display:none;" onclick="if(event.target===this)closeUploadModal()">
    <div class="ss-modal">
        <div class="ss-modal-header">
            <h3 class="ss-modal-title">Upload Images</h3>
            <button class="ss-modal-close" onclick="closeUploadModal()">&times;</button>
        </div>
        <div class="ss-modal-body">
            <div class="ss-form-group">
                <label>Category</label>
                <select id="upload-category">
                    <?php foreach ($categories as $slug => $name): ?>
                    <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ss-upload-dropzone" id="upload-dropzone">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.5"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg>
                <p style="font-weight:600;color:#4a4040;margin:.75rem 0 .25rem;">Drop images here or click to browse</p>
                <p class="ss-field-hint">WebP, JPEG, PNG · Max 10MB per file · Auto-optimized to 2000px &amp; WebP</p>
                <input type="file" id="upload-files" accept="image/webp,image/jpeg,image/png" multiple style="display:none;">
            </div>
            <div id="upload-queue" style="margin-top:1rem;display:none;">
                <div class="ss-card-header" style="margin-bottom:.5rem;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    Upload Queue
                </div>
                <div id="queue-list"></div>
            </div>
        </div>
        <div class="ss-modal-footer">
            <button class="ss-btn ss-btn-outline" onclick="closeUploadModal()">Close</button>
            <button class="ss-btn ss-btn-primary" id="start-upload-btn" onclick="startUpload()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/></svg>
                Upload All
            </button>
        </div>
    </div>
</div>

<style>
.ss-hidden{display:none!important;}
.ss-filter-chips{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1rem;}
.ss-chip{padding:.35rem .9rem;border-radius:20px;font-size:.8rem;font-weight:500;text-decoration:none;background:#f4f1eb;color:#4a4040;border:1px solid #ede8e0;transition:all .15s;}
.ss-chip:hover,.ss-chip-active{background:linear-gradient(135deg,#D4AF37,#c49d2e);color:#0a0805;border-color:transparent;}
.ss-gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.75rem;}
.ss-image-card{position:relative;aspect-ratio:1;border-radius:10px;overflow:hidden;background:#111;border:2px solid transparent;transition:border-color .18s;}
.ss-image-card img{width:100%;height:100%;object-fit:cover;display:block;}
.ss-image-card:hover{border-color:#D4AF37;}
.ss-image-meta{position:absolute;bottom:0;left:0;right:0;background:linear-gradient(transparent,rgba(0,0,0,.75));padding:.4rem .5rem;font-size:.65rem;color:rgba(255,255,255,.85);}
.ss-image-overlay{position:absolute;inset:0;background:rgba(0,0,0,.65);opacity:0;transition:opacity .2s;display:flex;align-items:center;justify-content:center;gap:.4rem;flex-wrap:wrap;padding:.5rem;}
.ss-image-card:hover .ss-image-overlay{opacity:1;}
.ss-img-action-btn{display:inline-flex;align-items:center;gap:.3rem;padding:.35rem .65rem;border:none;border-radius:6px;background:rgba(255,255,255,.15);color:#fff;font-size:.72rem;cursor:pointer;backdrop-filter:blur(4px);transition:background .15s;}
.ss-img-action-btn:hover{background:rgba(212,175,55,.6);color:#0a0805;}
.ss-img-delete-btn:hover{background:rgba(220,38,38,.6);color:#fff;}
.ss-empty-state{text-align:center;padding:3rem 1rem;background:#fff;border-radius:12px;border:1px solid #ede8e0;}
.ss-empty-state p{color:#9b9490;font-size:.88rem;margin:.75rem 0 0;}
.ss-upload-dropzone{border:2px dashed rgba(212,175,55,.35);border-radius:12px;padding:2rem;text-align:center;cursor:pointer;transition:all .2s;background:rgba(212,175,55,.025);}
.ss-upload-dropzone:hover,.ss-upload-dropzone.drag-over{border-color:#D4AF37;background:rgba(212,175,55,.07);}
.ss-field-hint{font-size:.76rem;color:#9b9490;}
.ss-upload-label{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border-radius:8px;background:#f4f1eb;border:1px solid #ede8e0;color:#4a4040;font-size:.82rem;font-weight:500;cursor:pointer;transition:all .15s;}
.ss-upload-label:hover{background:#ede8e0;border-color:#D4AF37;}
.ss-progress-bar{height:4px;background:#f0ece4;border-radius:2px;overflow:hidden;}
.ss-progress-fill{height:100%;background:linear-gradient(90deg,#D4AF37,#F5D67B);border-radius:2px;width:0%;transition:width .3s;}
.queue-item{display:flex;align-items:center;gap:.6rem;padding:.4rem .6rem;border-radius:7px;font-size:.8rem;background:#f9f7f3;margin-bottom:.3rem;}
.queue-item svg{flex-shrink:0;}
@media(max-width:600px){.ss-gallery-grid{grid-template-columns:repeat(auto-fill,minmax(110px,1fr));}}
</style>

<script>
const SS_NONCE = '<?php echo esc_js($nonce); ?>';
const SS_AJAX  = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
let pendingFiles = [];

// Tab switch
function switchGalleryTab(tab, btn) {
    document.getElementById('tab-panel-images').classList.toggle('ss-hidden', tab !== 'images');
    document.getElementById('tab-panel-hero').classList.toggle('ss-hidden', tab !== 'hero');
    document.querySelectorAll('.ss-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

// Upload modal
document.getElementById('open-upload-modal').addEventListener('click', () => {
    document.getElementById('upload-modal').style.display = 'flex';
});
function closeUploadModal() {
    document.getElementById('upload-modal').style.display = 'none';
    pendingFiles = [];
    updateQueue();
}

// Dropzone
const dz = document.getElementById('upload-dropzone');
const fi = document.getElementById('upload-files');
dz.addEventListener('click', () => fi.click());
dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('drag-over'); });
dz.addEventListener('dragleave', () => dz.classList.remove('drag-over'));
dz.addEventListener('drop', e => {
    e.preventDefault(); dz.classList.remove('drag-over');
    addFiles(e.dataTransfer.files);
});
fi.addEventListener('change', e => addFiles(e.target.files));

function addFiles(files) {
    for (const f of files) {
        if (['image/webp','image/jpeg','image/png'].includes(f.type)) pendingFiles.push(f);
    }
    updateQueue();
}

function updateQueue() {
    const q = document.getElementById('upload-queue');
    const list = document.getElementById('queue-list');
    q.style.display = pendingFiles.length ? 'block' : 'none';
    list.innerHTML = pendingFiles.map((f,i) =>
        `<div class="queue-item" id="qitem-${i}">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            <span style="flex:1;">${f.name}</span>
            <span style="color:#9b9490;">${(f.size/1024/1024).toFixed(1)}MB</span>
        </div>`
    ).join('');
}

async function startUpload() {
    if (!pendingFiles.length) return;
    const btn = document.getElementById('start-upload-btn');
    const cat = document.getElementById('upload-category').value;
    btn.disabled = true;
    let success = 0, fail = 0;
    for (let i = 0; i < pendingFiles.length; i++) {
        const item = document.getElementById('qitem-' + i);
        if (item) item.style.background = 'rgba(212,175,55,.1)';
        const fd = new FormData();
        fd.append('action', 'ss_upload_image');
        fd.append('nonce', SS_NONCE);
        fd.append('category', cat);
        fd.append('label', cat.charAt(0).toUpperCase() + cat.slice(1));
        fd.append('image', pendingFiles[i]);
        try {
            const r = await fetch(SS_AJAX, { method: 'POST', body: fd });
            const d = await r.json();
            if (d.success) {
                success++;
                if (item) { item.style.background = 'rgba(56,161,105,.1)'; item.querySelector('svg').setAttribute('stroke','#38a169'); }
            } else {
                fail++;
                if (item) item.style.background = 'rgba(220,38,38,.1)';
            }
        } catch { fail++; }
    }
    btn.disabled = false;
    pendingFiles = [];
    showGalleryNotice(success + ' image' + (success!==1?'s':'') + ' uploaded' + (fail ? ` (${fail} failed)` : '') + '.', fail ? 'error' : 'success');
    setTimeout(() => location.reload(), 1500);
}

function showGalleryNotice(msg, type='success') {
    const n = document.getElementById('gallery-notice');
    n.className = 'ss-notice ss-notice-' + type;
    n.textContent = msg;
    n.style.display = 'block';
    setTimeout(() => n.style.display = 'none', 4000);
}

// Delete image
async function deleteImage(id, btn) {
    if (!confirm('Delete this image? This cannot be undone.')) return;
    const card = btn.closest('.ss-image-card');
    card.style.opacity = '.4';
    const fd = new FormData();
    fd.append('action', 'ss_delete_image');
    fd.append('nonce', SS_NONCE);
    fd.append('id', id);
    const r = await fetch(SS_AJAX, { method: 'POST', body: fd });
    const d = await r.json();
    if (d.success) { card.remove(); }
    else { card.style.opacity = '1'; alert('Delete failed: ' + (d.data || 'error')); }
}

// Set hero from gallery
async function setHeroFromGallery(src, w, h) {
    const isDesktop = (w >= h);
    const fd = new FormData();
    fd.append('action', 'ss_update_hero');
    fd.append('nonce', SS_NONCE);
    const hero_d = document.getElementById('hero-desktop-url')?.value || '';
    const hero_m = document.getElementById('hero-mobile-url')?.value || '';
    fd.append('desktop', isDesktop ? src : hero_d);
    fd.append('mobile',  isDesktop ? hero_m : src);
    const r = await fetch(SS_AJAX, { method: 'POST', body: fd });
    const d = await r.json();
    if (d.success) {
        showGalleryNotice('Hero image updated! Reload to see the preview.');
    }
}

// Hero save
async function saveHero() {
    const btn = document.getElementById('save-hero-btn');
    const prog = document.getElementById('hero-progress');
    btn.disabled = true;
    prog.style.display = 'block';
    const fill = document.getElementById('hero-progress-fill');
    let p = 0;
    const t = setInterval(() => { p = Math.min(p+15,85); fill.style.width = p + '%'; }, 200);

    const fd = new FormData();
    fd.append('action', 'ss_update_hero');
    fd.append('nonce', SS_NONCE);
    fd.append('desktop', document.getElementById('hero-desktop-url').value);
    fd.append('mobile',  document.getElementById('hero-mobile-url').value);
    const df = document.getElementById('hero-desktop-file').files[0];
    const mf = document.getElementById('hero-mobile-file').files[0];
    if (df) fd.append('desktop_file', df);
    if (mf) fd.append('mobile_file', mf);

    const r = await fetch(SS_AJAX, { method: 'POST', body: fd });
    const d = await r.json();
    clearInterval(t); fill.style.width = '100%';
    setTimeout(() => { prog.style.display = 'none'; btn.disabled = false; }, 500);
    const n = document.getElementById('hero-notice');
    n.className = 'ss-notice ' + (d.success ? 'ss-notice-success' : 'ss-notice-error');
    n.textContent = d.success ? 'Hero images saved.' : ('Error: ' + (d.data || 'unknown'));
    n.style.display = 'block';
    if (d.success) setTimeout(() => location.reload(), 1200);
}

function previewHeroFile(type, input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const slot = document.getElementById(type + '-preview');
        let img = slot.querySelector('img');
        if (!img) {
            slot.innerHTML = '<span class="slot-label">' + (type==='desktop'?'Desktop':'Mobile') + '</span><img style="width:100%;height:100%;object-fit:cover;">';
            img = slot.querySelector('img');
        }
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}
</script>
