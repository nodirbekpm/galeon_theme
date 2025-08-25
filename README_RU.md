# Galeon WordPress Custom Theme (Русский)

### Galeon — Пользовательская тема WordPress
**Этот репозиторий содержит исходный код кастомной темы WordPress, созданной для сайта Galeon. Фронтенд изначально был написан на SCSS, затем скомпилирован в CSS-файлы, которые подключены в теме.**

#### Требования
- WordPress 6.3+
- PHP 8.0+
- MySQL/MariaDB (совместимо с WP)

#### Структура
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
│  ├─ css/   # скомпилированные CSS-файлы
│  ├─ js/    # JavaScript-файлы
│  ├─ img/   # изображения
│  └─ scss/  # (опционально: исходные SCSS)
└─ screenshot.png
```

#### Установка
1. Скопируйте папку в `wp-content/themes/galeon`.  
2. Активируйте тему через **Внешний вид → Темы** в админ-панели WP.  
3. Настройте меню и параметры через **Customizer**.  
