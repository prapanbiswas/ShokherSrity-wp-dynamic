---
name: Image catalog structure and rebuild
description: How to rebuild the 156-image catalog from disk, folder name quirks
---

## Folder name quirks (keep as-is, match static repo)
- Wedding → `Wedding Photoshooot` (3 o's — intentional typo in repo)
- Bride → `Bride Photoshoot`
- Reception → `Reception`
- Engagement → `Engegment Photoshoot` (typo — no 'a' in Engegment)
- Baby Shower → `Baby Shower`
- Baby → `Baby Photoshoot`

## Catalog stored in WordPress option `ss_image_catalog`
Each entry: `{id, src, category, label, width, height}`
src format: `/wp-content/uploads/<folder>/<filename>`

## Rebuild command
Run the PHP CLI rebuild script in start comment of shokhersrity-core.php, or use:
```php
require 'wordpress/wp-load.php';
// scan each folder with glob, getimagesize for dims, update_option('ss_image_catalog', $catalog)
```

## GD optimization on upload
Added to `ss_ajax_upload_image()`: resize to max 2000px, convert to WebP (quality 85) if `imagewebp()` available.

**Why:** Images from static repo are large; web-optimized WebP saves bandwidth.
