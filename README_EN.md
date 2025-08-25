# Galeon WordPress Custom Theme (English)

### Galeon — WordPress Custom Theme
**This repository contains the source code of a custom WordPress theme built for the Galeon website. The frontend was initially written in SCSS and later compiled into CSS files that are linked in the theme.**

#### Requirements
- WordPress 6.3+
- PHP 8.0+
- MySQL/MariaDB (compatible with WP)

#### Structure
```
galeon/
├─ style.css
├─ functions.php
├─ index.php
├─ header.php / footer.php
├─ front-page.php / home.php / page.php
├─ single.php / archive.php / search.php / 404.php
├─ template-parts/
├─ assets/
│  ├─ css/   # compiled CSS files
│  ├─ js/    # JavaScript files
│  ├─ img/   # images
│  └─ scss/  # (optional: original SCSS sources)
└─ screenshot.png
```

#### Installation
1. Place the folder in `wp-content/themes/galeon`.  
2. Activate via **Appearance → Themes** in the WP admin panel.  
3. Configure menus and theme options via **Customizer**.  
