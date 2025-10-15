<?php
/**
 * Plugin Name: Header Slider by Category
 * Description: Full-width, full-height header slider that loops through posts in a given category (default: headerSlider), showing the featured image as the background with title & excerpt overlay on hover. Provides a shortcode [header_slider] and a template tag header_slider().
 * Version: 1.0.1
 * Author: Michele Barone
 * License: GPL-2.0-or-later
 */

if (!defined('ABSPATH')) { exit; }

class HSC_Header_Slider {
	const DEFAULT_CATEGORY = 'headerSlider';

	public function __construct() {
		add_shortcode('header_slider', [$this, 'shortcode']);
		add_action('init', function(){
			if (!function_exists('header_slider')) {
				function header_slider($args = []) { echo (new HSC_Header_Slider())->render($args); }
			}
		});
	}

	private function enqueue_assets($uid, $interval) {
		// Swiper CSS/JS from CDN
		wp_enqueue_style("hsc-swiper-{$uid}", 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11.0.0');
		wp_enqueue_script("hsc-swiper-{$uid}", 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], '11.0.0', true);

		// Strong resets to defeat theme paddings/max-widths that cause side gaps
		$css = "
/* ===== HARD RESET FOR THIS INSTANCE ===== */
#{$uid}.hsc-slider,
#{$uid} .swiper,
#{$uid} .swiper-wrapper,
#{$uid} .swiper-slide,
#{$uid} .hsc-slide {
  margin:0 !important;
  padding:0 !important;
  border:0 !important;
  box-sizing:border-box;
  max-width:none !important;
}

/* Pin slider to the viewport (bypass any theme wrappers) */
#{$uid}.hsc-slider{
  position:absolute;
  inset:0;
  width:100vw;
  height:100vh;
  overflow:hidden;
  background: #000;
  z-index:0; /* keep header/nav above if it's fixed */
}
#{$uid}-spacer{ display:block; width:100%; height:100vh; }

/* Swiper internals must fill the viewport and never shrink */
#{$uid} .swiper-wrapper{ width:100%; height:100%; display:flex; }
#{$uid}.hsc-slider .swiper-slide{ width:100vw !important; height:100vh !important; flex-shrink:0; }

/* Slide content uses a real <img>, not background-image */
#{$uid} .hsc-slide{ position:relative; width:100%; height:100%; overflow:hidden; }
#{$uid} .hsc-bg{
  position:absolute;
  inset:0;
  width:100%;
  height:100%;
  display:block;
  object-fit:cover;
  object-position:center center;
  opacity: 30%;
}

/* Overlay content */
#{$uid} .hsc-slide::after{ content:''; position:absolute; inset:0; background:linear-gradient(180deg, rgba(0,0,0,.15) 0%, rgba(0,0,0,.35) 100%); pointer-events:none; }
#{$uid} .hsc-link{ position:absolute; inset:0; display:block; text-indent:-9999px; }

#{$uid} .hsc-overlay{
  position:absolute;
  top:40%;
  left:clamp(32px, 5vw, 180px); /* nice responsive left margin */
  transform:translateY(-50%);  /* vertical centering */
  width:min(90vw, 800px);
  text-align:left;             /* left-aligned text */
  color:#ffffff;
  z-index:2;                   /* above the full-slide link */
  pointer-events:auto;         /* allow clicks on links/buttons */
  opacity:1;                   /* always visible (optional) */
}

#{$uid} .hsc-overlay h2{
  margin:0 0 .8rem;
  font-size:clamp(2rem, 4vw, 3.5rem);
  line-height:1.1;
  font-weight:700;
  text-transform: none !important;
}

#{$uid} .hsc-overlay p{
  margin:0 0 1rem;
  max-width:600px;
  font-size:clamp(1rem, 1.4vw, 1.25rem);
  line-height:1.5;
  color:rgba(255,255,255,.92);
}

/*
#{$uid} .hsc-overlay{ position:absolute; left:5vw; bottom:8vh; max-width:min(700px,80vw); color:#fff; z-index:2; pointer-events:none; opacity:0; transform:translateY(10px); transition:opacity .3s ease, transform .3s ease; }

#{$uid} .hsc-overlay h2{ margin:0 0 .4rem; font-size:clamp(1.5rem,3vw,3rem); line-height:1.1; font-weight:700; }

#{$uid} .hsc-overlay p{ margin:0; font-size:clamp(.95rem,1.2vw,1.25rem); line-height:1.5; color:rgba(255,255,255,.9); }

#{$uid} .hsc-slide:hover .hsc-overlay{ opacity:1; transform:translateY(0); }

@media (hover:none){ #{$uid} .hsc-overlay{ opacity:1; transform:none; } }
*/

/* Controls */
#{$uid} .swiper-button-prev, #{$uid} .swiper-button-next{ color:#fff; filter:drop-shadow(0 2px 6px rgba(0,0,0,.4)); }
#{$uid} .swiper-pagination-bullet{ background:rgba(255,255,255,.6); opacity:1; }
#{$uid} .swiper-pagination-bullet-active{ background:#fff; }

/* Prevent horizontal scrollbar from any 100vw rounding */
html, body{ overflow-x:hidden; }
		";
		wp_register_style("hsc-style-{$uid}", false);
		wp_add_inline_style("hsc-style-{$uid}", $css);
		wp_enqueue_style("hsc-style-{$uid}");

		// JS (instance-scoped)
		$interval = intval($interval);
		$js = "
(function(){
  var el = document.getElementById('{$uid}');
  if(!el) return;
  var swiper = new Swiper(el, {
    loop: true,
    speed: 700,
    autoplay: { delay: {$interval}, disableOnInteraction: false },
    effect: 'fade',
    fadeEffect: { crossFade: true },
    slidesPerView: 1,
    spaceBetween: 0,
    centeredSlides: false,
    grabCursor: true,
    roundLengths: true,
    observer: true,
    observeParents: true,
    pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
    navigation: { nextEl: el.querySelector('.swiper-button-next'), prevEl: el.querySelector('.swiper-button-prev') }
  });
})();
		";
		wp_add_inline_script("hsc-swiper-{$uid}", $js, 'after');
	}

	public function shortcode($atts = [], $content = '') {
		$atts = shortcode_atts([
			'category' => self::DEFAULT_CATEGORY,
			'posts_per_page' => 5,
			'interval' => 5000, // ms
		], $atts, 'header_slider');

		return $this->render($atts);
	}

	public function render($args = []) {
		$category_input = isset($args['category']) ? $args['category'] : self::DEFAULT_CATEGORY;
		$ppp = isset($args['posts_per_page']) ? max(1, intval($args['posts_per_page'])) : 5;
		$interval = isset($args['interval']) ? intval($args['interval']) : 5000;

		$uid = 'hsc-' . wp_generate_password(8, false, false);
		$this->enqueue_assets($uid, $interval);

		// Resolve category by slug OR human-readable name; accept numeric ID too.
		$cat_ids = [];
		$parts = is_array($category_input) ? $category_input : array_map('trim', explode(',', (string)$category_input));
		foreach ($parts as $part) {
			if ($part === '') continue;
			if (is_numeric($part)) { $cat_ids[] = intval($part); continue; }
			$slug = sanitize_title($part);
			$term = get_term_by('slug', $slug, 'category');
			if (!$term) { $term = get_term_by('name', $part, 'category'); }
			if ($term && !is_wp_error($term)) { $cat_ids[] = intval($term->term_id); }
		}

		$q = new WP_Query([
			'posts_per_page' => $ppp,
			'ignore_sticky_posts' => true,
			'no_found_rows' => true,
			'post_status' => 'publish',
			'cat' => $cat_ids,
		]);

		if (!$q->have_posts()) {
			$note = empty($cat_ids) ? ' (Tip: the plugin matches category slug or name. The slug is usually all lowercase, e.g. headerslider.)' : '';
			return '<div class="hsc-empty">No posts found for category filter <code>' . esc_html(is_array($category_input)? implode(',', $category_input) : $category_input) . '</code>' . $note . '</div>';
		}

		ob_start();
		?>
<div id="<?php echo esc_attr($uid); ?>-spacer"></div>
<div id="<?php echo esc_attr($uid); ?>" class="hsc-slider swiper">
  <div class="swiper-wrapper">
    <?php while ($q->have_posts()) : $q->the_post();
      $img = get_the_post_thumbnail_url(get_the_ID(), 'full');
      if (!$img) { $img = function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src() : includes_url('images/media/default.png'); }
      $title = get_the_title();
      $excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 24);
//       $link = get_permalink();
		// Build overlay text: use excerpt for copy
		$overlay_text = has_excerpt()
		  ? get_the_excerpt()
		  : wp_trim_words(wp_strip_all_tags(get_the_content()), 24);

		// Try to extract a CTA <a> from the post content by common button classes
		$raw_content = apply_filters('the_content', get_the_content());
		$cta_html = '';
		if (preg_match('#<a[^>]*class=[\"\\\']([^\"\\\']*\\b(?:button|btn|wp-block-button__link)\\b[^\"\\\']*)[\"\\\'][^>]*>(.*?)</a>#is', $raw_content, $m)) {
			// Allow only safe attributes on the CTA
			$cta_html = wp_kses($m[0], [
				'a' => [
					'href' => [], 'class' => [], 'target' => [], 'rel' => [], 'aria-label' => []
				],
				'span' => ['class' => []], 'strong' => [], 'em' => [], 'br' => []
			]);
		}

    ?>
    <div class="swiper-slide">
      <div class="hsc-slide">
        <img class="hsc-bg" src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($title); ?>">
<!--         <a class="hsc-link" href="<?php echo esc_url($link); ?>" aria-label="<?php echo esc_attr($title); ?>"></a> -->
<!--         <div class="hsc-overlay">
          <h2><?php echo esc_html($title); ?></h2>
          <p><?php echo esc_html($excerpt); ?></p>
        </div> -->
		  <div class="hsc-overlay">
			  <h2><?php echo esc_html($title); ?></h2>
			  <p><?php echo esc_html($overlay_text); ?></p>
			  <?php if ($cta_html) : ?>
				<div class="hsc-cta"><?php echo $cta_html; // safe via wp_kses above ?></div>
			  <?php endif; ?>
			</div>

      </div>
    </div>
    <?php endwhile; wp_reset_postdata(); ?>
  </div>
  <div class="swiper-pagination"></div>
  <div class="swiper-button-prev" aria-label="Previous slide"></div>
  <div class="swiper-button-next" aria-label="Next slide"></div>
</div>
		<?php
		return ob_get_clean();
	}
}

new HSC_Header_Slider();
