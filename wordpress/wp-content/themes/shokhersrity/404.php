<?php get_header(); ?>
<main style="min-height:80vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:4rem 2rem;">
    <p class="section-label" style="color:var(--color-gold);">Oops!</p>
    <h1 class="section-title" style="font-size:6rem;line-height:1;margin-bottom:0.5rem;">404</h1>
    <h2 class="section-title">Page Not <span class="text-gradient">Found</span></h2>
    <p style="color:var(--color-charcoal-light);margin:1.5rem 0 2.5rem;max-width:480px;">The page you're looking for doesn't exist or has moved. Let's get you back to capturing beautiful moments.</p>
    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary">Back to Home</a>
</main>
<?php get_footer(); ?>
