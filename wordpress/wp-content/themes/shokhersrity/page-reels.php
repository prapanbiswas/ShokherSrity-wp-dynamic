<?php
/**
 * Template Name: Reels
 */
$videos = ss_get_videos();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0a0805">
    <title>Cinematic Wedding Reels | ShokherSrity Bangladesh</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo get_stylesheet_uri(); ?>">
    <?php wp_head(); ?>
</head>
<body class="reels-body">

<div class="reels-topbar">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="reels-back" aria-label="Go back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </a>
    <div class="reels-topbar-title"><span class="reels-topbar-label">Reels</span></div>
    <?php $reel_logo = get_option('ss_logo_url',''); $reel_name = get_option('ss_settings',[])['site_name'] ?? 'ShokherSrity'; ?>
    <a href="<?php echo esc_url(home_url('/')); ?>" class="reels-logo-link" aria-label="<?php echo esc_attr($reel_name); ?>">
        <?php if ($reel_logo): ?>
        <img src="<?php echo esc_url($reel_logo); ?>" alt="<?php echo esc_attr($reel_name); ?>" style="height:28px;width:auto;object-fit:contain;display:block;filter:brightness(0) invert(1);" loading="lazy">
        <?php else: ?><span class="reels-logo-text"><?php echo esc_html($reel_name); ?></span><?php endif; ?>
    </a>
</div>

<div class="reel-progress-bar" id="reel-progress">
    <?php foreach ($videos as $i => $v): ?>
    <div class="reel-progress-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></div>
    <?php endforeach; ?>
</div>

<div class="reels-feed" id="reels-feed">
    <?php foreach ($videos as $i => $v):
        $src   = esc_url($v['src']);
        $title = esc_html($v['title'] ?? 'Cinematic Wedding Reel');
        $desc  = esc_html($v['description'] ?? 'A beautiful moment captured in every frame ✨');
        $logo  = esc_url(get_option('ss_logo_url', content_url('uploads/logo.webp')));
    ?>
    <div class="reel-item" data-index="<?php echo $i; ?>">
        <video class="reel-video" src="<?php echo $src; ?>" loop playsinline preload="metadata" poster=""></video>

        <div class="reel-tap-zone reel-tap-left"></div>
        <div class="reel-tap-zone reel-tap-right"></div>

        <div class="reel-play-feedback" id="feedback-<?php echo $i; ?>">
            <div class="reel-play-icon"><svg viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg></div>
            <div class="reel-pause-icon"><svg viewBox="0 0 24 24" fill="white"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg></div>
        </div>

        <div class="reel-video-progress">
            <div class="reel-video-progress-fill" id="progress-<?php echo $i; ?>"></div>
        </div>

        <div class="reel-info">
            <div class="reel-info-left">
                <div class="reel-author-row">
                    <div class="reel-author-avatar"><img src="<?php echo $logo; ?>" alt="ShokherSrity" width="36" height="36"></div>
                    <span class="reel-author-name">ShokherSrity</span>
                </div>
                <h3 class="reel-title"><?php echo $title; ?></h3>
                <p class="reel-desc"><?php echo $desc; ?></p>
            </div>
            <div class="reel-info-right">
                <button class="reel-action-btn reel-mute-btn" data-index="<?php echo $i; ?>" aria-label="Toggle mute">
                    <svg class="icon-unmuted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path><path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
                    <svg class="icon-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><line x1="23" y1="9" x2="17" y2="15"></line><line x1="17" y1="9" x2="23" y2="15"></line></svg>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const feed = document.getElementById('reels-feed');
    const reelItems = document.querySelectorAll('.reel-item');
    const progressDots = document.querySelectorAll('.reel-progress-dot');
    let isMuted = true;

    document.querySelectorAll('.reel-video').forEach(v => { v.muted = true; });
    document.querySelectorAll('.reel-mute-btn').forEach(b => { b.classList.add('muted'); });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const video = entry.target.querySelector('.reel-video');
            const idx = parseInt(entry.target.dataset.index);
            const feedback = document.getElementById('feedback-' + idx);
            if (entry.isIntersecting) {
                video.play().then(() => feedback.classList.add('playing')).catch(() => feedback.classList.remove('playing'));
                progressDots.forEach(d => d.classList.remove('active'));
                if (progressDots[idx]) progressDots[idx].classList.add('active');
            } else {
                video.pause(); video.currentTime = 0;
                feedback.classList.remove('playing');
                const prog = document.getElementById('progress-' + idx);
                if (prog) prog.style.width = '0%';
            }
        });
    }, { threshold: 0.6 });

    reelItems.forEach(reel => observer.observe(reel));

    reelItems.forEach(reel => {
        const video = reel.querySelector('.reel-video');
        const idx = parseInt(reel.dataset.index);
        const feedback = document.getElementById('feedback-' + idx);
        reel.addEventListener('click', (e) => {
            if (e.target.closest('.reel-action-btn')) return;
            if (video.paused) { video.play(); feedback.classList.add('playing'); feedback.classList.remove('show-play'); }
            else { video.pause(); feedback.classList.remove('playing'); feedback.classList.add('show-play'); }
        });
        video.addEventListener('timeupdate', () => {
            if (video.duration) {
                const pct = (video.currentTime / video.duration) * 100;
                const prog = document.getElementById('progress-' + idx);
                if (prog) prog.style.width = pct + '%';
            }
        });
    });

    document.querySelectorAll('.reel-mute-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            isMuted = !isMuted;
            document.querySelectorAll('.reel-video').forEach(v => { v.muted = isMuted; });
            document.querySelectorAll('.reel-mute-btn').forEach(b => { b.classList.toggle('muted', isMuted); });
        });
    });

    progressDots.forEach(dot => {
        dot.addEventListener('click', () => {
            const idx = parseInt(dot.dataset.index);
            if (reelItems[idx]) reelItems[idx].scrollIntoView({ behavior: 'smooth' });
        });
    });

    document.addEventListener('keydown', (e) => {
        const currentIdx = [...progressDots].findIndex(d => d.classList.contains('active'));
        if (e.key === 'ArrowDown' || e.key === ' ') { e.preventDefault(); if (reelItems[currentIdx + 1]) reelItems[currentIdx + 1].scrollIntoView({ behavior: 'smooth' }); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); if (reelItems[currentIdx - 1]) reelItems[currentIdx - 1].scrollIntoView({ behavior: 'smooth' }); }
        else if (e.key === 'm' || e.key === 'M') { isMuted = !isMuted; document.querySelectorAll('.reel-video').forEach(v => { v.muted = isMuted; }); document.querySelectorAll('.reel-mute-btn').forEach(b => { b.classList.toggle('muted', isMuted); }); }
    });
});
</script>
<?php wp_footer(); ?>
</body>
</html>
