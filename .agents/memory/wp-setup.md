---
name: WordPress Replit setup
description: Required config for WP to work behind Replit's HTTPS proxy with SQLite
---

## HTTPS redirect loop fix
In `wordpress/wp-config.php`, add before `require ABSPATH . 'wp-settings.php'`:
```php
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}
```

## PHP server workers
`start.sh` must use `PHP_CLI_SERVER_WORKERS=8` to handle concurrent asset requests.

## Database
Uses SQLite via `wp-content/db.php` drop-in (no MySQL). All options stored in SQLite options table.

**Why:** Replit built-in server is single-threaded by default → redirect loops and asset failures without these fixes.
