<?php get_header(); ?>
<main style="min-height:60vh;padding:6rem 2rem 4rem;max-width:900px;margin:0 auto;">
    <?php while (have_posts()): the_post(); ?>
    <h1 class="section-title"><?php the_title(); ?></h1>
    <div class="page-content"><?php the_content(); ?></div>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
