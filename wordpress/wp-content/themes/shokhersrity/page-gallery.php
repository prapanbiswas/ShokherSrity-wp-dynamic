<?php
/**
 * Template Name: Gallery
 */
get_header();
?>
<main class="gallery-page">
    <div class="gallery-header" data-aos="fade-down">
        <span class="section-label" style="color:var(--color-gold);">Portfolio</span>
        <h1>Our <span class="text-gradient">Gallery</span></h1>
        <p>Explore our collection of wedding stories, each frame a testament to love, joy, and celebration</p>
    </div>

    <div class="gallery-filters" data-aos="fade-up">
        <button class="filter-btn active" data-filter="all">All</button>
        <button class="filter-btn" data-filter="wedding">Wedding</button>
        <button class="filter-btn" data-filter="bride">Bride</button>
        <button class="filter-btn" data-filter="reception">Reception</button>
        <button class="filter-btn" data-filter="engagement">Engagement</button>
        <button class="filter-btn" data-filter="babyshower">Baby Shower</button>
        <button class="filter-btn" data-filter="baby">Baby Photoshoot</button>
    </div>

    <div class="masonry-grid" data-aos="fade-up" id="gallery-grid"></div>
</main>

<div class="lightbox">
    <div class="lightbox-content">
        <img src="" alt="" class="lightbox-img">
        <div class="lightbox-caption"></div>
    </div>
    <div class="lightbox-close">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </div>
    <div class="lightbox-nav">
        <div class="lightbox-prev"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg></div>
        <div class="lightbox-next"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg></div>
    </div>
</div>

<?php get_footer(); ?>
