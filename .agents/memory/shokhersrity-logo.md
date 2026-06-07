---
name: ShokherSrity Logo System
description: How the logo upload system works and where it propagates
---

# Logo System

**Options stored:**
- `ss_logo_url` — full URL to uploaded logo (PNG/SVG/WebP/JPEG)
- `ss_favicon_url` — auto-generated 64x64 PNG favicon (or same as logo for SVG)

**Upload flow (AJAX `ss_upload_logo`):**
1. Receives file via `$_FILES['logo']`
2. Saves to `WP_CONTENT_DIR/uploads/logo/logo-{timestamp}.{ext}`
3. Non-SVG: GD resize to max 600px, save as PNG (preserves transparency)
4. Generates 64x64 favicon PNG, saves as `uploads/logo/favicon.png`
5. Updates `ss_logo_url` and `ss_favicon_url` options
6. SVG logos: favicon URL = logo URL (no raster generation)

**Remove flow:** POST with `remove=1` deletes file + clears both options

**Propagation points:**
- `wp_head` / `admin_head` → `ss_dynamic_favicon()` outputs `<link rel="icon">`
- Admin sidebar: CSS background-image on `.wp-menu-image` when logo set
- `header.php` → `get_option('ss_logo_url')` — shows `<img>` or text fallback
- `footer.php` → same pattern, with `filter:brightness(0) invert(1)` for dark background
- `page-reels.php` → topbar logo + reel author avatar use dynamic logo
- Settings admin → live preview before upload

**Why:** Single source of truth so updating logo in admin instantly reflects everywhere without theme file edits.
