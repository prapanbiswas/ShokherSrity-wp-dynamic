<?php
/**
 * ShokherSrity Theme — functions.php
 */

// ───────────────────────────────────────────────────────────
// THEME SETUP
// ───────────────────────────────────────────────────────────
function ss_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);
}
add_action('after_setup_theme', 'ss_setup');

// Remove admin bar on frontend
add_filter('show_admin_bar', '__return_false');

// Clean up wp_head noise
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);

// ───────────────────────────────────────────────────────────
// ENQUEUE ASSETS
// ───────────────────────────────────────────────────────────
function ss_enqueue_assets() {
    wp_enqueue_style('ss-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,500&family=Inter:wght@300;400;500;600;700&family=Great+Vibes&display=swap',
        [], null);
    wp_enqueue_style('ss-aos', 'https://unpkg.com/aos@2.3.1/dist/aos.css', [], '2.3.1');
    wp_enqueue_style('ss-style', get_stylesheet_uri(), ['ss-fonts', 'ss-aos'], '1.0');

    wp_enqueue_script('ss-aos', 'https://unpkg.com/aos@2.3.1/dist/aos.js', [], '2.3.1', true);
    wp_enqueue_script('ss-script', get_template_directory_uri() . '/js/script.js', ['ss-aos'], '1.0', true);
    wp_enqueue_script('ss-whatsapp', get_template_directory_uri() . '/js/whatsapp-widget.js', ['ss-script'], '1.0', true);

    // Inject dynamic data as JS globals BEFORE script.js runs
    $catalog = get_option('ss_image_catalog', []);
    $hero    = get_option('ss_hero_images', ss_default_hero());
    $settings = ss_get_settings();

    $inline  = 'var IMAGE_CATALOG = ' . wp_json_encode($catalog) . ';';
    $inline .= 'var SS_HERO = '       . wp_json_encode($hero)    . ';';
    $inline .= 'var SS_SETTINGS = '   . wp_json_encode($settings) . ';';
    wp_add_inline_script('ss-aos', $inline, 'before');
}
add_action('wp_enqueue_scripts', 'ss_enqueue_assets');

// Hero background: output a <style> block targeting .hero::after directly so
// CSS custom-property inheritance to pseudo-elements is not required.
add_action('wp_head', function() {
    if (!is_front_page()) return;
    $hero = get_option('ss_hero_images', ss_default_hero());
    $d = esc_url($hero['desktop']);
    $m = esc_url($hero['mobile']);
    echo "<style>
.hero::after{background-image:url('{$d}')!important;}
@media(max-width:768px){.hero::after{background-image:url('{$m}')!important;}}
</style>\n";
}, 20);

// ───────────────────────────────────────────────────────────
// DATA HELPERS
// ───────────────────────────────────────────────────────────
function ss_default_hero() {
    $uploads = content_url('uploads');
    return [
        'desktop' => $uploads . '/hero/hero-desktop.webp',
        'mobile'  => $uploads . '/Wedding Photoshooot/19.webp',
    ];
}

function ss_default_settings() {
    return [
        'site_name'       => 'ShokherSrity',
        'tagline'         => 'Capture your best moment',
        'phone1'          => '+8801799334656',
        'phone1_name'     => 'Kowsik',
        'phone2'          => '+8801700504456',
        'phone2_name'     => 'Dip',
        'email'           => 'shokhersrity@gmail.com',
        'address'         => 'Bhanga, Faridpur, Bangladesh',
        'map_embed_url'   => 'https://maps.google.com/maps?q=Shokher+Srity,+Bhanga,+Faridpur&t=&z=16&ie=UTF8&iwloc=&output=embed',
        'facebook'        => 'https://www.facebook.com/shokhersrity',
        'instagram'       => 'https://www.instagram.com/shokhersrity',
        'whatsapp'        => '8801799334656',
        'youtube'         => 'https://www.youtube.com/@shokhersrity',
        'tiktok'          => 'https://www.tiktok.com/@shokhersrity',
        'about_p1'        => 'At ShokherSrity, we believe that wedding photography is more than just capturing images — it\'s about preserving emotions, telling stories, and creating heirlooms that will be treasured for generations.',
        'about_p2'        => 'Led by the talented duo Kowsik Saha and Dip Pal, our team brings together technical excellence and artistic vision. With over 5 years of experience and 200+ weddings captured, we understand the nuances of Bangladeshi wedding traditions while adding a contemporary, cinematic touch.',
        'about_signature' => '"Capturing the essence of love, one frame at a time."',
        'stat1_count'     => 5,
        'stat1_suffix'    => '+',
        'stat1_label'     => 'Years Experience',
        'stat2_count'     => 200,
        'stat2_suffix'    => '+',
        'stat2_label'     => 'Weddings',
        'stat3_count'     => 20000,
        'stat3_suffix'    => '+',
        'stat3_label'     => 'Moments Captured',
        'geo_lat'         => '23.4961',
        'geo_lng'         => '89.7631',
        'cta_title'       => 'Ready to Capture Your <span class="text-gradient">Special Day?</span>',
        'cta_text'        => "Let's create something beautiful together. Book a consultation to discuss your vision and let us bring it to life.",
    ];
}

function ss_get_settings() {
    return array_merge(ss_default_settings(), (array) get_option('ss_settings', []));
}

function ss_get_hero() {
    $saved = get_option('ss_hero_images', []);
    return array_merge(ss_default_hero(), $saved);
}

function ss_get_catalog() {
    return (array) get_option('ss_image_catalog', []);
}

function ss_get_videos() {
    return (array) get_option('ss_videos', []);
}

function ss_get_packages() {
    $saved = get_option('ss_packages', null);
    return $saved ?: ss_default_packages();
}

// ───────────────────────────────────────────────────────────
// DEFAULT PACKAGES
// ───────────────────────────────────────────────────────────
function ss_default_packages() {
    return [
        'tiers' => [
            ['id' => 'standard',  'label' => 'Standard Tier',  'subtitle' => 'Essential Collection',      'columns' => 3],
            ['id' => 'premium',   'label' => 'Premium Tier',   'subtitle' => 'Elevated Elegance',          'columns' => 2],
            ['id' => 'exclusive', 'label' => 'Exclusive Tier', 'subtitle' => 'Unparalleled Excellence',    'columns' => 2],
        ],
        'packages' => [
            [
                'id' => 'silver', 'tier' => 'standard', 'name' => 'Silver',
                'price' => '৳15,000', 'period' => '/ day', 'note' => 'Perfect for intimate ceremonies',
                'badge' => null, 'is_popular' => false, 'style' => 'standard',
                'features' => ['1 Professional Photographer','1 Cinematographer','Unlimited Photo Clicks','100 Specially Edited Photos','50 Photo Prints (4R)','1 Cinematic Promo Video','1 Full Event Video','5 Hours Coverage','All Photos & Videos on Pen-drive'],
                'complementary_note' => '',
                'whatsapp_message' => 'Hello! I want to inquire about the Silver package. Please share more details.',
            ],
            [
                'id' => 'golden', 'tier' => 'standard', 'name' => 'Golden',
                'price' => '৳20,000', 'period' => '/ day', 'note' => 'Our best-selling package',
                'badge' => 'Most Popular', 'is_popular' => true, 'style' => 'featured',
                'features' => ['2 Professional Photographers','1 Cinematographer','Unlimited Photo Clicks','150 Specially Edited Photos','75 Photo Prints (4R)','1 Cinematic Promo Video','1 Full Event Video','5 Hours Coverage','All Photos & Videos on Pen-drive'],
                'complementary_note' => '',
                'whatsapp_message' => 'Hello! I want to inquire about the Golden package. Please share more details.',
            ],
            [
                'id' => 'platinum', 'tier' => 'standard', 'name' => 'Platinum',
                'price' => '৳25,000', 'period' => '/ day', 'note' => 'The ultimate experience',
                'badge' => null, 'is_popular' => false, 'style' => 'standard',
                'features' => ['2 Professional Photographers','2 Cinematographers','Unlimited Photo Clicks','200 Specially Edited Photos','100 Photo Prints + Premium Album','1 Cinematic Promo Video','1 Full Event Video','6 Hours Coverage','All Photos & Videos on Pen-drive'],
                'complementary_note' => '',
                'whatsapp_message' => 'Hello! I want to inquire about the Platinum package. Please share more details.',
            ],
            [
                'id' => 'sapphire', 'tier' => 'premium', 'name' => 'Sapphire',
                'price' => '৳30,000', 'period' => '/ day', 'note' => 'A premium artistic approach',
                'badge' => 'Best Value', 'is_popular' => false, 'style' => 'liquid-glass',
                'features' => ['2 Top Photographers','1 Top Cinematographer','200 Specially Edited Photos','1 Cinematic Promo','1 Full Event Video','6 Hours Coverage','1 Day Drone Coverage [COMPLEMENTARY]','1 Premium Photobook (80-100 Photos)','1 (12R) Photo Print without Frame','All Photos & Videos on Pen-drive'],
                'complementary_note' => '* Drone coverage complementary when you book a full wedding event',
                'whatsapp_message' => 'Hello! I want to inquire about the Sapphire package. Please share more details.',
            ],
            [
                'id' => 'emerald', 'tier' => 'premium', 'name' => 'Emerald',
                'price' => '৳40,000', 'period' => '/ day', 'note' => 'Opulent wedding coverage',
                'badge' => null, 'is_popular' => false, 'style' => 'liquid-glass',
                'features' => ['2 Top Photographers','2 Top Cinematographers','250 Specially Edited Photos','1 Cinematic Promo & 1 Trendy Reel','1 Full Event Video','6 Hours Coverage','1 Day Drone Coverage [COMPLEMENTARY]','1 Premium Photobook (120-140 Photos)','1 (12R) Photo Print without Frame','All Photos & Videos on Pen-drive'],
                'complementary_note' => '* Drone coverage complementary when you book a full wedding event',
                'whatsapp_message' => 'Hello! I want to inquire about the Emerald package. Please share more details.',
            ],
            [
                'id' => 'diamond', 'tier' => 'exclusive', 'name' => 'Diamond',
                'price' => '৳65,000', 'period' => '/ day', 'note' => 'Impeccable, timeless storytelling',
                'badge' => 'VIP Exclusive', 'is_popular' => false, 'style' => 'exclusive',
                'features' => ['3 Top Photographers','2 Top Cinematographers','300 Specially Edited Photos','1 Cinematic Promo & 1 Trendy Reel','1 Full Event Video','6 Hours Coverage','Every Day Drone Coverage','Pre/Post-Wedding Shoot [COMPLEMENTARY]','1 Imported Premium Photobook','1 (20L) Photo Print without Frame','All Photos & Videos on Pen-drive'],
                'complementary_note' => '* Pre/Post-wedding shoot complementary when you book a full wedding event',
                'whatsapp_message' => 'Hello! I want to inquire about the Diamond package. Please share more details.',
            ],
            [
                'id' => 'signature', 'tier' => 'exclusive', 'name' => 'Signature',
                'price' => '৳80,000', 'period' => '/ day', 'note' => 'For those who want it all',
                'badge' => null, 'is_popular' => false, 'style' => 'exclusive',
                'features' => ['3 Top Photographers','3 Top Cinematographers','300 Specially Edited Photos','2 Cinematic Promo & 2 Trendy Reel','1 Full Event Video','6 Hours Coverage','Every Day Drone Coverage','Pre/Post-Wedding Shoot [COMPLEMENTARY]','1 Imported Premium Photobook','1 (20L) Photo Print without Frame','All Photos & Videos on Pen-drive'],
                'complementary_note' => '* Pre/Post-wedding shoot complementary when you book a full wedding event',
                'whatsapp_message' => 'Hello! I want to inquire about the Signature package. Please share more details.',
            ],
        ],
    ];
}

// ───────────────────────────────────────────────────────────
// REST API (public catalog endpoints)
// ───────────────────────────────────────────────────────────
add_action('rest_api_init', function() {
    register_rest_route('ss/v1', '/catalog', [
        'methods' => 'GET',
        'callback' => fn() => rest_ensure_response(ss_get_catalog()),
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('ss/v1', '/videos', [
        'methods' => 'GET',
        'callback' => fn() => rest_ensure_response(ss_get_videos()),
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('ss/v1', '/settings', [
        'methods' => 'GET',
        'callback' => fn() => rest_ensure_response(ss_get_settings()),
        'permission_callback' => '__return_true',
    ]);
});
