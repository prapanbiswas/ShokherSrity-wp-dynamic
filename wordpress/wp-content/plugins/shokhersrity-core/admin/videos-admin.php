<?php defined('ABSPATH') || exit;
$nonce  = wp_create_nonce('ss_nonce');
$videos = (array) get_option('ss_videos', []);
?>
<div class="wrap ss-admin">

    <div class="ss-page-header">
        <div class="ss-page-header-left">
            <div class="ss-page-icon-wrap">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
            </div>
            <div>
                <h1 class="ss-page-title">Reels Manager</h1>
                <p class="ss-page-subtitle"><?php echo count($videos); ?> reel<?php echo count($videos) !== 1 ? 's' : ''; ?> &middot; Auto-optimized via FFmpeg on upload</p>
            </div>
        </div>
        <div class="ss-page-header-right">
            <button class="ss-btn ss-btn-primary" onclick="document.getElementById('video-upload-modal').style.display='flex'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Upload Reel
            </button>
        </div>
    </div>

    <div id="video-notice" style="display:none;margin-bottom:1rem;"></div>

    <?php if (empty($videos)): ?>
    <div class="ss-empty-state">
        <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#ddd" stroke-width="1.2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
        <p>No reels uploaded yet. Click "Upload Reel" to add your first video.</p>
    </div>
    <?php else: ?>
    <div class="ss-card">
        <div class="ss-card-header">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
            All Reels
            <span class="ss-field-hint" style="font-weight:400;">&nbsp;— drag to reorder, or use arrows</span>
        </div>
        <div class="ss-video-list" id="video-list">
        <?php foreach ($videos as $i => $v): ?>
        <div class="ss-video-row" data-id="<?php echo esc_attr($v['id']); ?>" id="vr-<?php echo esc_attr($v['id']); ?>">

            <div class="ss-video-thumb-wrap">
                <video src="<?php echo esc_url(home_url($v['src'])); ?>" preload="metadata" muted class="ss-video-thumb"></video>
                <div class="ss-video-num"><?php echo $i + 1; ?></div>
            </div>

            <div class="ss-video-info" id="view-<?php echo esc_attr($v['id']); ?>">
                <div class="ss-video-title"><?php echo esc_html($v['title']); ?></div>
                <div class="ss-video-desc"><?php echo esc_html($v['description']); ?></div>
                <div class="ss-video-meta">
                    Uploaded: <?php echo esc_html($v['uploaded_at'] ?? '—'); ?>
                    &middot; <code class="ss-code-small"><?php echo esc_html(basename($v['src'])); ?></code>
                </div>
            </div>

            <div class="ss-video-info ss-video-edit-form" id="edit-<?php echo esc_attr($v['id']); ?>" style="display:none;">
                <div class="ss-form-group" style="margin-bottom:.5rem;">
                    <label>Title</label>
                    <input type="text" class="edit-title" value="<?php echo esc_attr($v['title']); ?>" placeholder="Reel title">
                </div>
                <div class="ss-form-group" style="margin-bottom:0;">
                    <label>Description</label>
                    <input type="text" class="edit-desc" value="<?php echo esc_attr($v['description']); ?>" placeholder="Short description">
                </div>
            </div>

            <div class="ss-video-actions">
                <div style="display:flex;flex-direction:column;gap:.3rem;align-items:stretch;">
                    <?php if ($i > 0): ?>
                    <button class="ss-btn ss-btn-sm ss-btn-outline ss-move-btn" onclick="moveVideo('<?php echo esc_js($v['id']); ?>','up')">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
                    </button>
                    <?php endif; ?>
                    <?php if ($i < count($videos) - 1): ?>
                    <button class="ss-btn ss-btn-sm ss-btn-outline ss-move-btn" onclick="moveVideo('<?php echo esc_js($v['id']); ?>','down')">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <?php endif; ?>
                </div>
                <button class="ss-btn ss-btn-sm ss-btn-outline" id="edit-btn-<?php echo esc_attr($v['id']); ?>" onclick="toggleEdit('<?php echo esc_js($v['id']); ?>')">Edit</button>
                <button class="ss-btn ss-btn-sm ss-btn-primary" id="save-btn-<?php echo esc_attr($v['id']); ?>" onclick="saveVideo('<?php echo esc_js($v['id']); ?>')" style="display:none;">Save</button>
                <button class="ss-btn ss-btn-sm ss-btn-danger" onclick="deleteVideo('<?php echo esc_js($v['id']); ?>')">Delete</button>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Upload Modal -->
<div class="ss-modal-overlay" id="video-upload-modal" style="display:none;" onclick="if(event.target===this)closeVideoModal()">
    <div class="ss-modal">
        <div class="ss-modal-header">
            <h3 class="ss-modal-title">Upload Reel</h3>
            <button class="ss-modal-close" onclick="closeVideoModal()">&times;</button>
        </div>
        <div class="ss-modal-body">
            <div class="ss-upload-dropzone" id="video-dropzone" style="margin-bottom:1rem;">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.5"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                <p style="font-weight:600;color:#4a4040;margin:.5rem 0 .25rem;">Drop MP4 video here or click to browse</p>
                <p class="ss-field-hint">MP4 only &middot; Auto-optimized with FFmpeg faststart for faster web streaming</p>
                <input type="file" id="video-file" accept="video/mp4" style="display:none;">
            </div>
            <div id="video-selected-name" style="display:none;font-size:.82rem;color:#4a4040;margin-bottom:.75rem;padding:.4rem .75rem;background:#f4f1eb;border-radius:6px;"></div>
            <div class="ss-form-group">
                <label>Title</label>
                <input type="text" id="video-title" placeholder="A Timeless Love Story">
            </div>
            <div class="ss-form-group">
                <label>Description</label>
                <input type="text" id="video-desc" placeholder="Captured in every frame — a day you will never forget">
            </div>
            <div id="video-upload-progress" style="display:none;">
                <div class="ss-progress-bar"><div class="ss-progress-fill" id="video-progress-fill"></div></div>
                <p class="ss-field-hint" style="margin-top:.35rem;">Uploading &amp; running FFmpeg optimization — this may take a moment for large files…</p>
            </div>
        </div>
        <div class="ss-modal-footer">
            <button class="ss-btn ss-btn-outline" onclick="closeVideoModal()">Cancel</button>
            <button class="ss-btn ss-btn-primary" id="upload-video-btn" onclick="uploadVideo()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/></svg>
                Upload &amp; Optimize
            </button>
        </div>
    </div>
</div>

<style>
.ss-video-list{display:flex;flex-direction:column;gap:.75rem;}
.ss-video-row{display:grid;grid-template-columns:160px 1fr auto;gap:1rem;align-items:center;padding:.9rem;background:#f9f7f3;border-radius:10px;border:1px solid #ede8e0;transition:border-color .18s;}
.ss-video-row:hover{border-color:rgba(212,175,55,.3);}
.ss-video-thumb-wrap{position:relative;}
.ss-video-thumb{width:160px;height:90px;object-fit:cover;border-radius:8px;background:#111;display:block;}
.ss-video-num{position:absolute;top:.35rem;left:.35rem;background:rgba(0,0,0,.7);color:#D4AF37;font-size:.7rem;font-weight:700;padding:.1rem .4rem;border-radius:4px;}
.ss-video-title{font-weight:600;font-size:.92rem;color:#1a120a;margin-bottom:.2rem;}
.ss-video-desc{font-size:.83rem;color:#6b6460;margin-bottom:.4rem;}
.ss-video-meta{font-size:.72rem;color:#aaa;}
.ss-video-edit-form .ss-form-group label{font-size:.75rem;}
.ss-video-actions{display:flex;flex-direction:column;gap:.35rem;align-items:stretch;min-width:80px;}
.ss-move-btn{justify-content:center!important;padding:.3rem!important;}
.ss-code-small{font-size:.7rem;background:#f4f1eb;padding:.1rem .35rem;border-radius:4px;}
.ss-empty-state{text-align:center;padding:3rem 1rem;background:#fff;border-radius:12px;border:1px solid #ede8e0;}
.ss-empty-state p{color:#9b9490;font-size:.88rem;margin:.75rem 0 0;}
@media(max-width:700px){.ss-video-row{grid-template-columns:120px 1fr;}.ss-video-actions{flex-direction:row;flex-wrap:wrap;}.ss-video-thumb{width:120px;height:68px;}}
</style>

<script>
const SS_NONCE = '<?php echo esc_js($nonce); ?>';
const SS_AJAX  = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
let videoOrder = <?php echo wp_json_encode(array_column($videos, 'id')); ?>;

function closeVideoModal() {
    document.getElementById('video-upload-modal').style.display = 'none';
    document.getElementById('video-file').value = '';
    document.getElementById('video-selected-name').style.display = 'none';
}

// Dropzone
const vdz = document.getElementById('video-dropzone');
const vfi = document.getElementById('video-file');
vdz.addEventListener('click', () => vfi.click());
vdz.addEventListener('dragover', e => { e.preventDefault(); vdz.classList.add('drag-over'); });
vdz.addEventListener('dragleave', () => vdz.classList.remove('drag-over'));
vdz.addEventListener('drop', e => {
    e.preventDefault(); vdz.classList.remove('drag-over');
    const f = e.dataTransfer.files[0];
    if (f && f.type === 'video/mp4') { vfi.files = e.dataTransfer.files; showVideoName(f.name, f.size); }
});
vfi.addEventListener('change', () => {
    const f = vfi.files[0];
    if (f) showVideoName(f.name, f.size);
});
function showVideoName(name, size) {
    const n = document.getElementById('video-selected-name');
    n.textContent = name + ' (' + (size/1024/1024).toFixed(1) + 'MB)';
    n.style.display = 'block';
}

async function uploadVideo() {
    const file = document.getElementById('video-file').files[0];
    if (!file) { alert('Please select an MP4 file.'); return; }
    const btn  = document.getElementById('upload-video-btn');
    const prog = document.getElementById('video-upload-progress');
    const fill = document.getElementById('video-progress-fill');
    btn.disabled = true;
    prog.style.display = 'block';
    let p = 0;
    const t = setInterval(() => { p = Math.min(p+3, 85); fill.style.width = p + '%'; }, 300);

    const fd = new FormData();
    fd.append('action', 'ss_upload_video');
    fd.append('nonce', SS_NONCE);
    fd.append('video', file);
    fd.append('title', document.getElementById('video-title').value || 'Wedding Reel');
    fd.append('description', document.getElementById('video-desc').value || '');

    const r    = await fetch(SS_AJAX, { method: 'POST', body: fd });
    const data = await r.json();
    clearInterval(t); fill.style.width = '100%';
    setTimeout(() => { prog.style.display = 'none'; btn.disabled = false; }, 400);

    if (data.success) {
        closeVideoModal();
        location.reload();
    } else {
        showVideoNotice('Upload failed: ' + (data.data || 'unknown error'), 'error');
    }
}

function showVideoNotice(msg, type='success') {
    const n = document.getElementById('video-notice');
    n.className = 'ss-notice ss-notice-' + type;
    n.textContent = msg;
    n.style.display = 'block';
    setTimeout(() => n.style.display = 'none', 4000);
}

function toggleEdit(id) {
    const view    = document.getElementById('view-' + id);
    const edit    = document.getElementById('edit-' + id);
    const editBtn = document.getElementById('edit-btn-' + id);
    const saveBtn = document.getElementById('save-btn-' + id);
    const isEditing = edit.style.display !== 'none';
    view.style.display    = isEditing ? 'block' : 'none';
    edit.style.display    = isEditing ? 'none'  : 'block';
    editBtn.style.display = isEditing ? 'inline-flex' : 'none';
    saveBtn.style.display = isEditing ? 'none' : 'inline-flex';
}

async function saveVideo(id) {
    const row   = document.getElementById('vr-' + id);
    const title = row.querySelector('.edit-title').value;
    const desc  = row.querySelector('.edit-desc').value;
    const fd = new FormData();
    fd.append('action', 'ss_update_video');
    fd.append('nonce', SS_NONCE);
    fd.append('id', id);
    fd.append('title', title);
    fd.append('description', desc);
    const r = await fetch(SS_AJAX, { method: 'POST', body: fd });
    const d = await r.json();
    if (d.success) {
        row.querySelector('.ss-video-title').textContent = title;
        row.querySelector('.ss-video-desc').textContent  = desc;
        toggleEdit(id);
    }
}

async function deleteVideo(id) {
    if (!confirm('Delete this reel? The file will be permanently removed.')) return;
    const row = document.getElementById('vr-' + id);
    row.style.opacity = '.4';
    const fd = new FormData();
    fd.append('action', 'ss_delete_video');
    fd.append('nonce', SS_NONCE);
    fd.append('id', id);
    const r = await fetch(SS_AJAX, { method: 'POST', body: fd });
    const d = await r.json();
    if (d.success) location.reload();
    else { row.style.opacity = '1'; showVideoNotice('Delete failed', 'error'); }
}

async function moveVideo(id, dir) {
    const idx = videoOrder.indexOf(id);
    if (dir === 'up' && idx > 0)
        [videoOrder[idx], videoOrder[idx-1]] = [videoOrder[idx-1], videoOrder[idx]];
    else if (dir === 'down' && idx < videoOrder.length - 1)
        [videoOrder[idx], videoOrder[idx+1]] = [videoOrder[idx+1], videoOrder[idx]];
    const fd = new FormData();
    fd.append('action', 'ss_reorder_videos');
    fd.append('nonce', SS_NONCE);
    videoOrder.forEach(v => fd.append('order[]', v));
    await fetch(SS_AJAX, { method: 'POST', body: fd });
    location.reload();
}
</script>
