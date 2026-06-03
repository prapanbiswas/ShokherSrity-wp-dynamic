<?php
/**
 * Homepage template
 */
$s    = ss_get_settings();
$hero = ss_get_hero();
get_header();
?>
<main>

    <!-- Hero Section -->
    <section class="hero" data-aos="fade">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title"><?php echo esc_html($s['site_name']); ?></h1>
            <p class="hero-tagline"><?php echo esc_html($s['tagline']); ?></p>
            <div class="hero-cta">
                <a href="<?php echo esc_url(home_url('/packages/')); ?>" class="btn btn-primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    View Packages
                </a>
                <a href="<?php echo esc_url(home_url('/gallery/')); ?>" class="btn btn-secondary glass-effect">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><path d="M21 15l-5-5L5 21"></path></svg>
                    View Gallery
                </a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" data-aos="fade-up">
        <div class="about-container">
            <div class="about-image-wrapper" data-aos="fade-right">
                <div class="about-image-frame"></div>
                <div class="about-image">
                    <img src="<?php echo esc_url(content_url('uploads/Reception/1.webp')); ?>" alt="About Photography">
                </div>
            </div>
            <div class="about-content" data-aos="fade-left">
                <span class="section-label">About Us</span>
                <h2 class="section-title">Where Every Moment <span class="text-gradient">Becomes Art</span></h2>
                <p><?php echo esc_html($s['about_p1']); ?></p>
                <p><?php echo esc_html($s['about_p2']); ?></p>
                <p class="about-signature"><?php echo esc_html($s['about_signature']); ?></p>
                <div class="about-stats">
                    <div class="stat-item">
                        <span class="stat-number" data-count="<?php echo esc_attr($s['stat1_count']); ?>" data-suffix="<?php echo esc_attr($s['stat1_suffix']); ?>"><?php echo esc_html($s['stat1_count'] . $s['stat1_suffix']); ?></span>
                        <span class="stat-label"><?php echo esc_html($s['stat1_label']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" data-count="<?php echo esc_attr($s['stat2_count']); ?>" data-suffix="<?php echo esc_attr($s['stat2_suffix']); ?>"><?php echo esc_html($s['stat2_count'] . $s['stat2_suffix']); ?></span>
                        <span class="stat-label"><?php echo esc_html($s['stat2_label']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" data-count="<?php echo esc_attr($s['stat3_count']); ?>" data-suffix="<?php echo esc_attr($s['stat3_suffix']); ?>"><?php echo esc_html($s['stat3_count'] . $s['stat3_suffix']); ?></span>
                        <span class="stat-label"><?php echo esc_html($s['stat3_label']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Work Section -->
    <section class="featured-work" data-aos="fade-up">
        <div class="section-header">
            <span class="section-label">Portfolio</span>
            <h2 class="section-title">Featured <span class="text-gradient">Work</span></h2>
            <p class="section-subtitle">A glimpse into the magical moments we've had the privilege to capture</p>
        </div>
        <div class="featured-grid stagger-children" id="homepage-featured-grid"></div>
        <div style="text-align:center;margin-top:3rem;">
            <a href="<?php echo esc_url(home_url('/gallery/')); ?>" class="btn btn-outline">
                View Full Gallery
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>
            </a>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials" data-aos="fade-up">
        <div class="section-header">
            <span class="section-label" style="color:var(--color-gold-light);">Testimonials</span>
            <h2 class="section-title">What Couples <span class="text-gradient">Say</span></h2>
        </div>
        <div class="testimonials-grid stagger-children">
            <?php
            $testimonials = [
                ['initials'=>'SR','name'=>'Sarah & Rahul','role'=>'Dhaka, 2023','text'=>'"ShokherSrity captured our wedding day so beautifully that every time we look at the photos, we relive those magical moments. The attention to detail and the artistic vision exceeded all our expectations."'],
                ['initials'=>'NA','name'=>'Nadia & Asif','role'=>'Chittagong, 2023','text'=>'"From the pre-wedding shoot to the main event, Kowsik and Dip were incredibly professional and creative. They made us feel comfortable and captured our personalities perfectly."'],
                ['initials'=>'FA','name'=>'Farah & Adil','role'=>'Sylhet, 2024','text'=>'"The video and photo quality was outstanding! They captured candid moments we didn\'t even know happened. ShokherSrity truly are artists behind the camera. Highly recommended!"'],
            ];
            foreach ($testimonials as $i => $t): ?>
            <div class="testimonial-card" data-aos="fade-up" <?php echo $i > 0 ? 'data-aos-delay="' . ($i * 100) . '"' : ''; ?>>
                <div class="testimonial-stars">
                    <?php for ($j = 0; $j < 5; $j++): ?><svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg><?php endfor; ?>
                </div>
                <p class="testimonial-text"><?php echo esc_html($t['text']); ?></p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar"><?php echo esc_html($t['initials']); ?></div>
                    <div>
                        <div class="testimonial-name"><?php echo esc_html($t['name']); ?></div>
                        <div class="testimonial-role"><?php echo esc_html($t['role']); ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section" data-aos="fade-up">
        <div class="cta-content">
            <h2><?php echo wp_kses_post($s['cta_title']); ?></h2>
            <p><?php echo esc_html($s['cta_text']); ?></p>
            <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="btn btn-primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                Book a Consultation
            </a>
        </div>
    </section>

</main>

<script>
// Inject hero images for responsive swap
(function(){
    var hero = typeof SS_HERO !== 'undefined' ? SS_HERO : {};
    var desktop = hero.desktop || '';
    var mobile  = hero.mobile  || '';
    var h = document.querySelector('.hero');
    if (!h) return;
    function swap(isDesktop) {
        h.style.setProperty('--hero-bg-image', "url('" + (isDesktop ? desktop : mobile) + "')");
    }
    var mq = window.matchMedia('(min-width: 769px)');
    swap(mq.matches);
    mq.addEventListener('change', function(e){ swap(e.matches); });
})();
</script>

<?php get_footer(); ?>
