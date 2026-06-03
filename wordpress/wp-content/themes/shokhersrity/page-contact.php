<?php
/**
 * Template Name: Contact
 */
$s = ss_get_settings();
get_header();
?>
<main class="contact-page">
    <div class="contact-header" data-aos="fade-down">
        <span class="section-label" style="color:var(--color-gold);">Get in Touch</span>
        <h1>Contact <span class="text-gradient">Us</span></h1>
        <p>We would love to hear from you. Reach out to discuss your wedding photography needs and let's create something beautiful together.</p>
    </div>

    <div class="contact-container">
        <div class="contact-cards">
            <div class="contact-card" data-aos="fade-up">
                <div class="contact-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"></path></svg>
                </div>
                <h3 class="contact-card-title">Call Us</h3>
                <div class="contact-card-info">
                    <p><a href="tel:<?php echo esc_attr($s['phone1']); ?>"><?php echo esc_html($s['phone1_name']); ?>: <?php echo esc_html($s['phone1']); ?></a></p>
                    <?php if (!empty($s['phone2'])): ?>
                    <p><a href="tel:<?php echo esc_attr($s['phone2']); ?>"><?php echo esc_html($s['phone2_name']); ?>: <?php echo esc_html($s['phone2']); ?></a></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="contact-card" data-aos="fade-up" data-aos-delay="100">
                <div class="contact-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                </div>
                <h3 class="contact-card-title">Email Us</h3>
                <div class="contact-card-info">
                    <p><a href="mailto:<?php echo esc_attr($s['email']); ?>"><?php echo esc_html($s['email']); ?></a></p>
                    <p class="contact-card-hint">We reply within 24 hours</p>
                </div>
            </div>

            <div class="contact-card" data-aos="fade-up" data-aos-delay="200">
                <div class="contact-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                </div>
                <h3 class="contact-card-title">Visit Us</h3>
                <div class="contact-card-info">
                    <p><?php echo esc_html($s['address']); ?></p>
                    <p class="contact-card-hint">Available for travel nationwide</p>
                </div>
            </div>
        </div>

        <div class="map-card" data-aos="fade-up">
            <div class="map-card-inner">
                <h3 class="map-card-title">Our <span class="text-gradient">Location</span></h3>
                <div class="map-frame">
                    <iframe src="<?php echo esc_url($s['map_embed_url']); ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <p class="map-card-address"><span aria-hidden="true">📍</span> <?php echo esc_html($s['address']); ?></p>
            </div>
        </div>

        <div class="business-hours" data-aos="fade-up">
            <div class="business-hours-header">
                <div class="business-hours-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></div>
                <h3>Business Hours</h3>
                <p class="business-hours-sub">We are available to capture your special moments</p>
            </div>
            <div class="hours-grid">
                <div class="hour-item"><div class="hour-day">Monday - Friday</div><div class="hour-time">9:00 AM - 8:00 PM</div></div>
                <div class="hour-item"><div class="hour-day">Saturday</div><div class="hour-time">10:00 AM - 6:00 PM</div></div>
                <div class="hour-item"><div class="hour-day">Sunday</div><div class="hour-time">By Appointment</div></div>
                <div class="hour-item featured"><span class="hour-badge">Always</span><div class="hour-day">Wedding Days</div><div class="hour-time">24/7 Coverage</div></div>
            </div>
        </div>

        <div class="faq-section" id="faq" data-aos="fade-up">
            <div class="faq-header">
                <span class="section-label" style="color:var(--color-gold);">Common Questions</span>
                <h3>Frequently Asked <span class="text-gradient">Questions</span></h3>
                <p class="faq-sub">Everything you might want to know before reaching out</p>
            </div>
            <div class="faq-list">
                <details class="faq-item">
                    <summary><span class="faq-q">Do you travel outside Bhanga or Faridpur for weddings?</span><span class="faq-toggle" aria-hidden="true"></span></summary>
                    <div class="faq-a"><p>Yes, we cover weddings nationwide across Bangladesh. Travel costs for venues outside the Faridpur district are added on top of the package price. Just share your venue when you message us and we will give you the full breakdown.</p></div>
                </details>
                <details class="faq-item">
                    <summary><span class="faq-q">How early should I book my wedding date?</span><span class="faq-toggle" aria-hidden="true"></span></summary>
                    <div class="faq-a"><p>We recommend booking at least 2 to 3 months in advance, especially for the November to February wedding season. Popular dates fill up quickly. Once your date is confirmed with an advance, it is locked exclusively for you.</p></div>
                </details>
                <details class="faq-item">
                    <summary><span class="faq-q">What is included in a typical wedding package?</span><span class="faq-toggle" aria-hidden="true"></span></summary>
                    <div class="faq-a"><p>Every package includes professional photographers, edited high-resolution images, and a curated highlight set. Cinematography, drone coverage, same-day reels, and printed albums vary by tier — check the <a href="<?php echo esc_url(home_url('/packages/')); ?>">Packages page</a> for a full comparison.</p></div>
                </details>
                <details class="faq-item">
                    <summary><span class="faq-q">When will I receive my photos and videos?</span><span class="faq-toggle" aria-hidden="true"></span></summary>
                    <div class="faq-a"><p>Edited photos are delivered within 3 to 4 weeks. Highlight reels and short edits are shared within 7 to 10 days. The full cinematic film, where included, is delivered within 6 to 8 weeks of the event.</p></div>
                </details>
                <details class="faq-item">
                    <summary><span class="faq-q">How do I confirm a booking?</span><span class="faq-toggle" aria-hidden="true"></span></summary>
                    <div class="faq-a"><p>Reach out by phone, email, or WhatsApp with your wedding date, venue, and the package you are interested in. Once we confirm availability, a small advance secures the date and we share a written agreement covering everything you can expect.</p></div>
                </details>
            </div>
        </div>

        <div class="quick-inquiry" data-aos="fade-up">
            <div class="quick-inquiry-header">
                <h3>Quick Inquiry</h3>
                <p>Select the type of service you're interested in</p>
            </div>
            <div class="inquiry-options">
                <?php
                $inquiries = [
                    ['type'=>'wedding','icon'=>'M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z','title'=>'Wedding Photography','desc'=>'Full day coverage of your special day','msg'=>'Hello! I am interested in booking your wedding photography package. Could you please provide more information?'],
                    ['type'=>'portrait','icon'=>'M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z M12 13a4 4 0 1 1 0-8 4 4 0 0 1 0 8','title'=>'Portrait Session','desc'=>'Pre-wedding or couple portraits','msg'=>'Hello! I would like to book a portrait photography session. What are your availability and rates?'],
                    ['type'=>'event','icon'=>'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2 M9 7a4 4 0 1 0 0-8 4 4 0 0 0 0 8 M23 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75','title'=>'Event Coverage','desc'=>'Holud, reception, or special events','msg'=>'Hello! I am interested in event photography services. Could you share your package details?'],
                ];
                foreach ($inquiries as $inq): ?>
                <div class="inquiry-card" data-inquiry="<?php echo esc_attr($inq['type']); ?>" data-whatsapp="<?php echo esc_attr($s['whatsapp']); ?>" data-message="<?php echo esc_attr($inq['msg']); ?>">
                    <div class="inquiry-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="<?php echo esc_attr($inq['icon']); ?>"></path></svg></div>
                    <h4 class="inquiry-title"><?php echo esc_html($inq['title']); ?></h4>
                    <p class="inquiry-desc"><?php echo esc_html($inq['desc']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="whatsapp-cta">
                <p style="color:rgba(255,255,255,0.6);margin-bottom:1rem;">Prefer to chat directly?</p>
                <a href="https://wa.me/<?php echo esc_attr($s['whatsapp']); ?>?text=Hello! I would like to inquire about your wedding photography services." target="_blank" class="whatsapp-btn">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Message us on WhatsApp
                </a>
            </div>
        </div>
    </div>
</main>
<?php get_footer(); ?>
