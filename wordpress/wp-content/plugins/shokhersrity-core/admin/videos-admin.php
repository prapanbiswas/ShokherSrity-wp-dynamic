<?php defined('ABSPATH') || exit;
$nonce  = wp_create_nonce('ss_nonce');
$videos = (array) get_option('ss_videos', []);
?>
<div class="wrap ss-admin">
    <div class="ss-header">
        <div class="ss-header-logo">🎬</div>
        <div>
            <h1>Video Manager</h1>
            <p><?php echo count($videos); ?> reels · All videos auto-optimized (FFmpeg faststart)</p>
        </div>
        <div style="margin-left:auto;">
            <button class="ss-btn ss-btn-primary" onclick="document.getElementById('video-upload-modal').style.display='flex'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Upload Video
            </button>
        </div>
    </div>

    <div class="ss-card" id="notice-area"></div>

    <?php if (empty($videos)): ?>
    <div class="ss-card" style="text-align:center;padding:3rem;">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.5" style="display:block;margin:0 auto 1rem;"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
        <p style="color:#888;">No videos uploaded yet. Click "Upload Video" to add your first reel.</p>
    </div>
    <?php else: ?>
    <div class="ss-card">
        <h2>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
            Reels (shown in order on the Reels page)
        </h2>
        <div class="ss-video-list" id="video-list">
            <?php foreach ($videos as $i => $v): ?>
            <div class="ss-video-card" data-id="<?php echo esc_attr($v['id']); ?>" id="vc-<?php echo esc_attr($v['id']); ?>">
                <video src="<?php echo esc_url(home_url($v['src'])); ?>" controls preload="metadata" style="width:200px;height:112px;object-fit:cover;border-radius:8px;background:#111;"></video>
                <div>
                    <div id="view-<?php echo esc_attr($v['id']); ?>">
                        <div style="font-weight:600;font-size:0.95rem;margin-bottom:0.35rem;"><?php echo esc_html($v['title']); ?></div>
                        <div style="color:#888;font-size:0.85rem;margin-bottom:0.75rem;"><?php echo esc_html($v['description']); ?></div>
                        <div style="font-size:0.75rem;color:#aaa;">Uploaded: <?php echo esc_html($v['uploaded_at'] ?? ''); ?> · <code style="font-size:0.72rem;"><?php echo esc_html($v['src']); ?></code></div>
                    </div>
                    <div id="edit-<?php echo esc_attr($v['id']); ?>" style="display:none;">
                        <input type="text" class="edit-title" value="<?php echo esc_attr($v['title']); ?>" placeholder="Title" style="width:100%;margin-bottom:0.5rem;padding:0.4rem 0.6rem;border:1px solid #ddd;border-radius:6px;font-size:0.88rem;">
                        <input type="text" class="edit-desc" value="<?php echo esc_attr($v['description']); ?>" placeholder="Description" style="width:100%;padding:0.4rem 0.6rem;border:1px solid #ddd;border-radius:6px;font-size:0.88rem;">
                    </div>
                </div>
                <div class="ss-video-actions">
                    <div style="font-size:0.8rem;font-weight:600;color:#D4AF37;text-align:center;">Reel <?php echo $i + 1; ?></div>
                    <button class="ss-btn ss-btn-secondary" id="edit-btn-<?php echo esc_attr($v['id']); ?>" onclick="toggleEdit('<?php echo esc_js($v['id']); ?>')">Edit</button>
                    <button class="ss-btn ss-btn-primary" id="save-btn-<?php echo esc_attr($v['id']); ?>" onclick="saveVideo('<?php echo esc_js($v['id']); ?>')" style="display:none;">Save</button>
                    <?php if ($i > 0): ?>
                    <button class="ss-btn ss-btn-secondary" onclick="moveVideo('<?php echo esc_js($v['id']); ?>','up')">↑ Up</button>
                    <?php endif; ?>
                    <?php if ($i < count($videos) - 1): ?>
                    <button class="ss-btn ss-btn-secondary" onclick="moveVideo('<?php echo esc_js($v['id']); ?>','down')">↓ Down</button>
                    <?php endif; ?>
                    <button class="ss-btn ss-btn-danger" onclick="deleteVideo('<?php echo esc_js($v['id']); ?>')">Delete</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Video Upload Modal -->
<div id="video-upload-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)closeVideoModal()">
    <div style="background:white;border-radius:16px;padding:2rem;width:480px;max-width:95vw;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
            <h3 style="margin:0;">Upload Video</h3>
            <button onclick="closeVideoModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
        </div>
        <div class="ss-form-group">
            <label>Video File (MP4)</label>
            <input type="file" id="video-file" accept="video/mp4">
        </div>
        <div class="ss-form-group">
            <label>Title</label>
            <input type="text" id="video-title" placeholder="A Timeless Love Story">
        </div>
        <div class="ss-form-group">
            <label>Description</label>
            <input type="text" id="video-desc" placeholder="Captured in every frame ✨">
        </div>
        <div id="video-upload-progress" style="display:none;margin-bottom:1rem;">
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <div class="spinner"></div>
                <span style="color:#888;font-size:0.88rem;">Uploading & optimizing with FFmpeg…</span>
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:0.75rem;">
            <button class="ss-btn ss-btn-secondary" onclick="closeVideoModal()">Cancel</button>
            <button class="ss-btn ss-btn-primary" id="upload-video-btn" onclick="uploadVideo()">Upload</button>
        </div>
    </div>
</div>

<script>
const SS_NONCE  = '<?php echo esc_js($nonce); ?>';
const SS_AJAX   = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
let videoOrder  = <?php echo wp_json_encode(array_column($videos, 'id')); ?>;

function closeVideoModal() { document.getElementById('video-upload-modal').style.display = 'none'; }

async function uploadVideo() {
    const file  = document.getElementById('video-file').files[0];
    const title = document.getElementById('video-title').value;
    const desc  = document.getElementById('video-desc').value;
    if (!file) { alert('Select a video file'); return; }

    document.getElementById('upload-video-btn').disabled = true;
    document.getElementById('video-upload-progress').style.display = 'block';

    const fd = new FormData();
    fd.append('action', 'ss_upload_video');
    fd.append('nonce', SS_NONCE);
    fd.append('video', file);
    fd.append('title', title || 'Wedding Reel');
    fd.append('description', desc || '');

    const r    = await fetch(SS_AJAX, { method: 'POST', body: fd });
    const data = await r.json();
    document.getElementById('video-upload-progress').style.display = 'none';
    document.getElementById('upload-video-btn').disabled = false;

    if (data.success) {
        closeVideoModal();
        location.reload();
    } else {
        alert('Upload failed: ' + (data.data || 'unknown error'));
    }
}

function toggleEdit(id) {
    const view = document.getElementById('view-' + id);
    const edit = document.getElementById('edit-' + id);
    const editBtn = document.getElementById('edit-btn-' + id);
    const saveBtn = document.getElementById('save-btn-' + id);
    const isEditing = edit.style.display !== 'none';
    view.style.display = isEditing ? 'block' : 'none';
    edit.style.display = isEditing ? 'none' : 'block';
    editBtn.style.display = isEditing ? 'inline-flex' : 'none';
    saveBtn.style.display = isEditing ? 'none' : 'inline-flex';
}

async function saveVideo(id) {
    const card  = document.getElementById('vc-' + id);
    const title = card.querySelector('.edit-title').value;
    const desc  = card.querySelector('.edit-desc').value;
    const fd = new FormData();
    fd.append('action', 'ss_update_video');
    fd.append('nonce', SS_NONCE);
    fd.append('id', id);
    fd.append('title', title);
    fd.append('description', desc);
    const r = await fetch(SS_AJAX, { method: 'POST', body: fd });
    const data = await r.json();
    if (data.success) {
        card.querySelector('#view-' + id).querySelector('div').textContent = title;
        toggleEdit(id);
    }
}

async function deleteVideo(id) {
    if (!confirm('Delete this video? The file will be permanently removed.')) return;
    const fd = new FormData();
    fd.append('action', 'ss_delete_video');
    fd.append('nonce', SS_NONCE);
    fd.append('id', id);
    const r = await fetch(SS_AJAX, { method: 'POST', body: fd });
    const data = await r.json();
    if (data.success) location.reload();
}

async function moveVideo(id, dir) {
    const idx = videoOrder.indexOf(id);
    if (dir === 'up' && idx > 0) {
        [videoOrder[idx], videoOrder[idx-1]] = [videoOrder[idx-1], videoOrder[idx]];
    } else if (dir === 'down' && idx < videoOrder.length - 1) {
        [videoOrder[idx], videoOrder[idx+1]] = [videoOrder[idx+1], videoOrder[idx]];
    }
    const fd = new FormData();
    fd.append('action', 'ss_reorder_videos');
    fd.append('nonce', SS_NONCE);
    videoOrder.forEach(v => fd.append('order[]', v));
    await fetch(SS_AJAX, { method: 'POST', body: fd });
    location.reload();
}
</script>
