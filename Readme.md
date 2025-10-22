# Header Slider by Category

A simple yet powerful WordPress header slider plugin that displays your latest posts from a chosen category in a full-width, full-height looping slider.  
Each slide uses the post’s featured image as the background and overlays the title, excerpt, and optional call-to-action buttons extracted from the post content.

## ✨ Features
- 🖼 Full-width, full-height responsive slider
- 🔁 Infinite looping with fade transition
- 📰 Automatically displays posts from a chosen category
- 💬 Title and excerpt overlays on each image
- 🎯 Clickable multiple call-to-action buttons from post content
- 📱 Responsive design — perfect on mobile and desktop
- ⚡ Lightweight: uses Swiper.js from CDN
- 🔧 Shortcode and template tag support
- 🧩 Gutenberg button group support
- 🛠 Smart fallback for buttons without links

## 🚀 Installation

1. Download or clone this repository into your WordPress `wp-content/plugins` directory:
   git clone https://github.com/YOUR_USERNAME/wordpress-header-slider-by-category.git
2. Activate "Header Slider by Category" in your WordPress Plugins page.
3. Ensure your posts have:
   - A featured image
   - Belong to the category "headerSlider" (or any other you choose)

## 🧩 Usage

Shortcode example:
[header_slider category="headerSlider" posts_per_page="5" interval="5000"]

### Attributes

| Attribute | Default | Description |
|------------|----------|-------------|
| category | headerSlider | Category slug, name, or ID to pull posts from |
| posts_per_page | 5 | Number of posts to display |
| interval | 5000 | Time (ms) between slide transitions |
| max_ctas | -1 | Maximum number of buttons per slide (-1 = all) |
| cta_skin | inherit | Use theme buttons or 'light' for slider overlay style |
| cta_class | (empty) | Extra CSS classes added to each button |
| cta_wrap | auto | auto / wp / none - wrapping method for Gutenberg buttons |
| missing_href | fallback | fallback / disabled / skip - how to handle missing href |
| fallback_href | (empty) | Fallback URL for missing button links |
| fallback_to_post | true | Link missing-href buttons to the post URL |

### Template Tag

if (function_exists('header_slider')) {
    header_slider([
        'category' => 'headerSlider',
        'posts_per_page' => 5,
        'interval' => 5000,
    ]);
}

## 🧠 How It Works

- The plugin queries published posts in the specified category.
- Each slide uses the featured image as a full background.
- Title and excerpt are displayed in an overlay.
- If post content contains buttons or Gutenberg button blocks, all are extracted.
- Supports multiple CTAs per slide with fallback links for missing href attributes.
- Built with Swiper.js for smooth transitions and touch/swipe support.

## ⚙️ Customization

You can easily tweak:
- Overlay position and text alignment in the inline CSS.
- Transition effect (fade/slide) and autoplay speed in the inline JS.
- Category filter, number of posts, and timing via shortcode attributes.

## 🧱 Folder Structure

wordpress-header-slider-by-category/
├── header-slider-by-category.php
└── README.md

## 📜 License

Licensed under GPL-2.0-or-later.

This plugin is free software: you can redistribute it and/or modify  
it under the terms of the GNU General Public License as published by  
the Free Software Foundation, either version 2 of the License, or  
(at your option) any later version.

## 💡 Credits

Built with ❤️ using:
- WordPress
- Swiper.js

Author: Michele Barone  
Version: 1.1.4  
Repository: https://github.com/YOUR_USERNAME/wordpress-header-slider-by-category
