<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0805">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php $is_home = is_front_page(); ?>
<?php if (!$is_home): ?>
<div class="nav-top-backdrop" aria-hidden="true"></div>
<?php endif; ?>

<header>
    <nav>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="logo">ShokherSrity</a>
        <button class="mobile-menu-btn" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
        <ul class="nav-links">
            <li><a href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
            <li><a href="<?php echo esc_url(home_url('/gallery/')); ?>">Gallery</a></li>
            <li><a href="<?php echo esc_url(home_url('/reels/')); ?>">Reels</a></li>
            <li><a href="<?php echo esc_url(home_url('/packages/')); ?>">Packages</a></li>
            <li><a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a></li>
        </ul>
    </nav>
</header>
