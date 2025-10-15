# Header Slider by Category

A simple yet powerful **WordPress header slider plugin** that displays your latest posts from a chosen category in a **full-width, full-height** looping slider.  
Each slide uses the post’s **featured image** as the background and overlays the **title**, **excerpt**, and **optional call-to-action button** extracted from the post content.

---

## ✨ Features
- 🖼 **Full-width, full-height responsive slider**
- 🔁 **Infinite looping** with fade or slide transition
- 📰 **Automatically displays posts** from a chosen category
- 💬 **Title and excerpt overlays** on each image
- 🎯 **Clickable call-to-action buttons** from post content
- 📱 **Responsive design** — perfect on mobile and desktop
- ⚡ **Lightweight**: uses [Swiper.js](https://swiperjs.com) from CDN
- 🔧 **Shortcode and template tag support**

---

## 🚀 Installation

1. **Download or clone** this repository into your WordPress `wp-content/plugins` directory:
   ```bash
   git clone https://github.com/YOUR_USERNAME/wordpress-header-slider-by-category.git
   ```
2. Activate **Header Slider by Category** in your WordPress **Plugins** page.
3. Make sure your posts have:
   - A **featured image**
   - Belong to the category **headerSlider** (or any other category you choose)

---

## 🧩 Usage

### Shortcode
Place this shortcode in any page or post:
```php
[header_slider category="headerSlider" posts_per_page="5" interval="5000"]
```

**Attributes:**

| Attribute | Default | Description |
|------------|----------|-------------|
| `category` | `headerSlider` | Category slug, name, or ID to pull posts from |
| `posts_per_page` | `5` | Number of posts to display |
| `interval` | `5000` | Time (ms) between slide transitions |

### Template Tag
Use this inside a PHP theme file:
```php
if (function_exists('header_slider')) {
    header_slider([
        'category' => 'headerSlider',
        'posts_per_page' => 5,
        'interval' => 5000,
    ]);
}
```

---

## 🧠 How It Works

- The plugin queries published posts in the specified category.
- Each slide uses the featured image as the full background.
- Title and excerpt are displayed in an overlay.
- If the post content contains a `<a>` tag with a class like `.button`, `.btn`, or `.wp-block-button__link`, it’s extracted and displayed as a **clickable CTA** in the overlay.
- The slider is built with **Swiper.js**, offering smooth transitions and touch/swipe support.

---

## 🧱 Folder Structure
```
wordpress-header-slider-by-category/
├── header-slider-by-category.php   # Main plugin file
└── README.md                       # Documentation
```

---

## ⚙️ Customization

You can easily tweak:
- Overlay position (center, bottom, left, etc.) in the inline CSS.
- Transition effect (`fade` or `slide`) and autoplay speed in the inline JS.
- Category filter, number of posts, and slide interval via shortcode attributes.

---

## 📜 License

Licensed under the **GPL-2.0-or-later** license.

```
This plugin is free software: you can redistribute it and/or modify  
it under the terms of the GNU General Public License as published by  
the Free Software Foundation, either version 2 of the License, or  
(at your option) any later version.
```

---

## 💡 Credits
Built with ❤️ using:
- [WordPress](https://wordpress.org)
- [Swiper.js](https://swiperjs.com)

---

**Author:** Your Name  
**Version:** 1.0.1  
**Repository:** [wordpress-header-slider-by-category](https://github.com/YOUR_USERNAME/wordpress-header-slider-by-category)
