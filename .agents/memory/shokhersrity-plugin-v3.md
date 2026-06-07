---
name: ShokherSrity Core Plugin v3 Architecture
description: Key architectural decisions in the v3 rewrite of shokhersrity-core.php
---

# ShokherSrity Core Plugin v3

**Why:** Complete overhaul — remove emojis, fix broken sidebar icon, add logo system, user management, update pipeline, sitemap, security headers.

## Admin Menu Icon
- Previous value `'none'` caused broken icon in sidebar
- Fix: generate base64 data URI from inline camera SVG in PHP, pass as 6th arg to `add_menu_page()`
- Pattern: `$svg = '<svg.../>'; $icon = 'data:image/svg+xml;base64,' . base64_encode($svg);`

## Menu Labels
- All submenu labels are plain text (no emojis) — user strict preference
- Submenus: Dashboard, Gallery, Reels, Packages, Settings, Users, System

## Admin Page Callbacks + Files
- 7 pages: dashboard.php, gallery-admin.php, videos-admin.php, packages-admin.php, settings-admin.php, users-admin.php, updates-admin.php
- All in `SS_PLUGIN_DIR . 'admin/'`

## AJAX / Admin-Post Handlers
- AJAX (wp_ajax_*): ss_upload_image, ss_delete_image, ss_update_hero, ss_upload_logo, ss_upload_video, ss_update_video, ss_delete_video, ss_reorder_videos, ss_save_packages, ss_save_settings, ss_reorder_catalog
- Admin-post (admin_post_*): ss_create_user_action, ss_edit_user_action, ss_delete_user_action, ss_check_updates_action, ss_create_backup_action, ss_restore_backup_action, ss_run_update_action

## Security
- XML-RPC disabled via `add_filter('xmlrpc_enabled', '__return_false')`
- Security headers added in `wp_headers` filter
- WP generator removed from wp_head
- Author enumeration blocked via `template_redirect`

## Sitemap
- Registered via rewrite rule + rewrite tag (`ss_sitemap=1`)
- Served at `/sitemap.xml` — must flush rewrite rules on activation

## Backups
- Stored in `WP_CONTENT_DIR . '/ss-backups/'` as JSON
- Only user ID 1 can create/restore/run updates (root-user only)

**How to apply:** When modifying the plugin, preserve all AJAX nonces (check_ajax_referer 'ss_nonce'). The admin redirect in `ss_admin_redirect_to_dashboard` allows SS pages through via `$_GET['page']` prefix check.
