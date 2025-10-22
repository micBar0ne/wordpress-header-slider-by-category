<?php
/**
 * Plugin Name: Header Slider by Category
 * Description: Full-width, full-height header slider that loops through posts in a given category (default: headerSlider). Uses Swiper.js and supports Gutenberg buttons (incl. groups), preserving theme styles. Provides a shortcode [header_slider] and a template tag header_slider().
 * Version: 1.1.4
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

	/* ---------------------------------------------------
	   Asset loading (CSS + JS)
	--------------------------------------------------- */
	private function enqueue_assets($uid, $interval, $cta_skin = 'inherit') {
		wp_enqueue_style("hsc-swiper-{$uid}", 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11.0.0');
		wp_enqueue_script("hsc-swiper-{$uid}", 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], '11.0.0', true);

		$css = "
#{$uid}.hsc-slider,
#{$uid} .swiper,
#{$uid} .swiper-wrapper,
#{$uid} .swiper-slide,
#{$uid} .hsc-slide {
  margin:0!important;
  padding:0!important;
  border:0!important;
  box-sizing:border-box;
  max-width:none!important;
}
#{$uid}.hsc-slider{
  position:absolute;
  inset:0;
  width:100vw;
  height:100vh;
  overflow:hidden;
  background:#000;
  z-index:0;
}
#{$uid}-spacer{display:block;width:100%;height:100vh;}
#{$uid} .swiper-wrapper{width:100%;height:100%;display:flex;}
#{$uid}.hsc-slider .swiper-slide{width:100vw!important;height:100vh!important;flex-shrink:0;}
#{$uid} .hsc-slide{position:relative;width:100%;height:100%;overflow:hidden;}
#{$uid} .hsc-bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center center;opacity:30%;}
#{$uid} .hsc-slide::after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,.15)0%,rgba(0,0,0,.35)100%);pointer-events:none;}
#{$uid} .hsc-overlay{
  position:absolute;top:40%;left:clamp(32px,5vw,180px);
  transform:translateY(-50%);width:min(90vw,800px);
  text-align:left;color:#fff;z-index:2;pointer-events:auto;
}
#{$uid} .hsc-overlay h2{margin:0 0 .8rem;font-size:clamp(2rem,4vw,3.5rem);line-height:1.1;font-weight:700;}
#{$uid} .hsc-overlay p{margin:0 0 1rem;max-width:600px;font-size:clamp(1rem,1.4vw,1.25rem);line-height:1.5;color:rgba(255,255,255,.92);}

/* CTA layout (preserve theme skins) */
#{$uid} .hsc-ctas{display:flex;flex-wrap:wrap;gap:.75rem;margin-top:1rem;}
#{$uid} .hsc-ctas .wp-block-buttons{display:flex;flex-wrap:wrap;gap:.75rem;margin:0;padding:0;}
#{$uid} .hsc-ctas .wp-block-button{margin:0;}
#{$uid} .hsc-ctas .is-disabled{opacity:.8;pointer-events:none;}
";

		if ($cta_skin === 'light') {
			$css .= "
#{$uid} .hsc-ctas a{
  display:inline-block;text-decoration:none;
  padding:.75rem 1.1rem;border-radius:.5rem;
  background:rgba(255,255,255,.14);
  border:1px solid rgba(255,255,255,.35);
  color:#fff!important;font-weight:600;
  transition:transform .12s ease,background .2s ease,border-color .2s ease;
}
#{$uid} .hsc-ctas a:hover{transform:translateY(-1px);background:rgba(255,255,255,.22);border-color:#fff;}
";
		}

		$css .= "
#{$uid} .swiper-button-prev,#{$uid} .swiper-button-next{color:#fff;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));}
#{$uid} .swiper-pagination-bullet{background:rgba(255,255,255,.6);opacity:1;}
#{$uid} .swiper-pagination-bullet-active{background:#fff;}
html,body{overflow-x:hidden;}
";
		wp_register_style("hsc-style-{$uid}", false);
		wp_add_inline_style("hsc-style-{$uid}", $css);
		wp_enqueue_style("hsc-style-{$uid}");

		$interval = intval($interval);
		$js = "
(function(){
 var el=document.getElementById('{$uid}');
 if(!el)return;
 new Swiper(el,{
   loop:true,speed:700,
   autoplay:{delay:{$interval},disableOnInteraction:false},
   effect:'fade',fadeEffect:{crossFade:true},
   slidesPerView:1,spaceBetween:0,centeredSlides:false,
   grabCursor:true,roundLengths:true,observer:true,observeParents:true,
   pagination:{el:el.querySelector('.swiper-pagination'),clickable:true},
   navigation:{nextEl:el.querySelector('.swiper-button-next'),prevEl:el.querySelector('.swiper-button-prev')}
 });
})();";
		wp_add_inline_script("hsc-swiper-{$uid}", $js, 'after');
	}

	/* ---------------------------------------------------
	   Shortcode handler
	--------------------------------------------------- */
	public function shortcode($atts = [], $content = '') {
		$atts = shortcode_atts([
			'category'        => self::DEFAULT_CATEGORY,
			'posts_per_page'  => 5,
			'interval'        => 5000,
			'max_ctas'        => -1,         // -1 = all buttons
			'cta_skin'        => 'inherit',
			'cta_class'       => '',
			'cta_wrap'        => 'auto',     // auto | wp | none
			'missing_href'    => 'fallback', // fallback | disabled | skip
			'fallback_href'   => '',         // optional URL
			'fallback_to_post'=> 'true',     // if true and no fallback_href, use post permalink
		], $atts, 'header_slider');

		return $this->render($atts);
	}

	/* ---------------------------------------------------
	   Helpers
	--------------------------------------------------- */
	private function _allow_tags() {
		return [
			'div'   => ['class'=>[]],
			'a'     => ['href'=>[], 'class'=>[], 'target'=>[], 'rel'=>[], 'aria-label'=>[], 'role'=>[], 'style'=>[], 'tabindex'=>[], 'aria-disabled'=>[]],
			'span'  => ['class'=>[]],
			'strong'=>[], 'em'=>[], 'br'=>[]
		];
	}
	private function _sanitize_fragment($html){ return wp_kses($html, $this->_allow_tags()); }

	private function _node_has_class($node, $class) {
		if (!$node || !$node->attributes || !$node->attributes->getNamedItem('class')) return false;
		return preg_match('/(^|\s)'.preg_quote($class,'/').'(\s|$)/', $node->attributes->getNamedItem('class')->nodeValue);
	}
	private function _node_has_ancestor_class($node, $class) {
		$n = $node->parentNode;
		while ($n && $n->nodeType === XML_ELEMENT_NODE) {
			if ($this->_node_has_class($n, $class)) return true;
			$n = $n->parentNode;
		}
		return false;
	}

	/* ---------------------------------------------------
	   CTA extraction with groups + missing href handling
	--------------------------------------------------- */
	private function extract_ctas($raw_content, $max_ctas, $extra_btn_class, $wrap_mode, $missing_href_mode, $fallback_href, $fallback_to_post, $post_permalink) {
		$max = intval($max_ctas);
		$append_class = trim($extra_btn_class);
		$out_fragments = [];
		$total_added = 0;

		if (!function_exists('mb_convert_encoding')) return $out_fragments;

		$doc = new DOMDocument();
		libxml_use_internal_errors(true);
		$html = mb_convert_encoding($raw_content, 'HTML-ENTITIES', 'UTF-8');
		$loaded = $doc->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.$html);
		libxml_clear_errors();
		if (!$loaded) return $out_fragments;

		$xpath = new DOMXPath($doc);

		/* ---- 1) Process Gutenberg button groups (wp-block-buttons) ---- */
		$groups = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' wp-block-buttons ')]");
		foreach ($groups as $grp) {
			if ($max > -1 && $total_added >= $max) break;

			$links = $grp->getElementsByTagName('a');
			$group_items = [];
			$first_group_href = null;

			// Pass 1: find first href in the group (for fallback)
			foreach ($links as $a) {
				$h = trim($a->getAttribute('href'));
				if ($h) { $first_group_href = $h; break; }
			}

			// Pass 2: build items
			foreach ($links as $a) {
				if ($max > -1 && $total_added >= $max) break;

				$cls = ' '.$a->getAttribute('class').' ';
				$role = strtolower(trim($a->getAttribute('role')));
				$intended_btn = preg_match('/\b(button|btn|wp-block-button__link)\b/i', $cls) || ($role === 'button');
				if (!$intended_btn) continue;

				$href = trim($a->getAttribute('href'));

				// Missing href handling
				if ($href === '') {
					if ($missing_href_mode === 'skip') continue;
					if ($missing_href_mode === 'fallback') {
						$href = $fallback_href ?: ($first_group_href ?: ($fallback_to_post ? $post_permalink : ''));
					}
				}

				// Security normalize
				$target = $a->getAttribute('target');
				$rel = $a->getAttribute('rel');
				if ($target === '_blank' && stripos($rel, 'noopener') === false) {
					$a->setAttribute('rel', trim($rel.' noopener noreferrer'));
				}
				// Append extra classes
				if ($append_class !== '') {
					$a->setAttribute('class', trim($a->getAttribute('class').' '.$append_class));
				}
				// Ensure Gutenberg link class
				if (!preg_match('/\bwp-block-button__link\b/', ' '.$a->getAttribute('class').' ')) {
					$a->setAttribute('class', trim($a->getAttribute('class').' wp-block-button__link'));
				}

				// Apply href or mark disabled
				if ($href !== '') {
					$a->setAttribute('href', $href);
				} else { // disabled
					$a->setAttribute('class', trim($a->getAttribute('class').' is-disabled'));
					$a->setAttribute('aria-disabled', 'true');
					$a->setAttribute('tabindex', '-1');
				}

				$item_html = '<div class="wp-block-button">'.$doc->saveHTML($a).'</div>';
				$group_items[] = $this->_sanitize_fragment($item_html);
				$total_added++;
			}

			if ($group_items) {
				$out_fragments[] = $this->_sanitize_fragment('<div class="wp-block-buttons">'.implode('', $group_items).'</div>');
			}
		}

		/* ---- 2) Standalone buttons NOT inside a group ---- */
		if ($max === -1 || $total_added < $max) {
			$links = $doc->getElementsByTagName('a');
			foreach ($links as $a) {
				if ($max > -1 && $total_added >= $max) break;
				if ($this->_node_has_ancestor_class($a, 'wp-block-buttons')) continue;

				$cls = ' '.$a->getAttribute('class').' ';
				$role = strtolower(trim($a->getAttribute('role')));
				$intended_btn = preg_match('/\b(button|btn|wp-block-button__link)\b/i', $cls) || ($role === 'button');
				if (!$intended_btn) continue;

				$href = trim($a->getAttribute('href'));
				if ($href === '') {
					if ($missing_href_mode === 'skip') continue;
					if ($missing_href_mode === 'fallback') {
						$href = $fallback_href ?: ($fallback_to_post ? $post_permalink : '');
					}
				}

				$target = $a->getAttribute('target');
				$rel = $a->getAttribute('rel');
				if ($target === '_blank' && stripos($rel, 'noopener') === false) {
					$a->setAttribute('rel', trim($rel.' noopener noreferrer'));
				}
				if ($append_class !== '') {
					$a->setAttribute('class', trim($a->getAttribute('class').' '.$append_class));
				}

				$a_classes = ' '.$a->getAttribute('class').' ';
				$should_wrap = ($wrap_mode === 'wp') || ($wrap_mode === 'auto' && preg_match('/\bwp-block-button__link\b/', $a_classes));

				if ($href !== '') {
					$a->setAttribute('href', $href);
				} else {
					$a->setAttribute('class', trim($a->getAttribute('class').' is-disabled'));
					$a->setAttribute('aria-disabled', 'true');
					$a->setAttribute('tabindex', '-1');
				}

				if ($should_wrap) {
					if (!preg_match('/\bwp-block-button__link\b/', $a_classes)) {
						$a->setAttribute('class', trim($a->getAttribute('class').' wp-block-button__link'));
					}
					$out_fragments[] = $this->_sanitize_fragment('<div class="wp-block-button">'.$doc->saveHTML($a).'</div>');
				} else {
					$out_fragments[] = $this->_sanitize_fragment($doc->saveHTML($a));
				}
				$total_added++;
			}
		}

		return $out_fragments;
	}

	/* ---------------------------------------------------
	   Render slider
	--------------------------------------------------- */
	public function render($args = []) {
		$category_input  = $args['category'] ?? self::DEFAULT_CATEGORY;
		$ppp             = max(1, intval($args['posts_per_page'] ?? 5));
		$interval        = intval($args['interval'] ?? 5000);
		$max_ctas        = intval($args['max_ctas'] ?? -1);
		$cta_skin        = sanitize_key($args['cta_skin'] ?? 'inherit');
		$cta_class       = sanitize_text_field($args['cta_class'] ?? '');
		$cta_wrap        = sanitize_key($args['cta_wrap'] ?? 'auto');
		$missing_href    = sanitize_key($args['missing_href'] ?? 'fallback'); // fallback|disabled|skip
		$fallback_href   = esc_url_raw($args['fallback_href'] ?? '');
		$fallback_to_post= filter_var($args['fallback_to_post'] ?? 'true', FILTER_VALIDATE_BOOLEAN);

		$uid = 'hsc-' . wp_generate_password(8, false, false);
		$this->enqueue_assets($uid, $interval, $cta_skin);

		// Resolve categories (slug, name, or ID)
		$cat_ids = [];
		$parts = is_array($category_input) ? $category_input : array_map('trim', explode(',', (string)$category_input));
		foreach ($parts as $part) {
			if ($part === '') continue;
			if (is_numeric($part)) { $cat_ids[] = intval($part); continue; }
			$slug = sanitize_title($part);
			$term = get_term_by('slug', $slug, 'category');
			if (!$term) $term = get_term_by('name', $part, 'category');
			if ($term && !is_wp_error($term)) $cat_ids[] = intval($term->term_id);
		}

		$q = new WP_Query([
			'posts_per_page'      => $ppp,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'cat'                 => $cat_ids,
		]);

		if (!$q->have_posts()) {
			return '<div class="hsc-empty">No posts found for category <code>'.esc_html(is_array($category_input)?implode(',',$category_input):$category_input).'</code>.</div>';
		}

		ob_start();
		?>
<div id="<?php echo esc_attr($uid); ?>-spacer"></div>
<div id="<?php echo esc_attr($uid); ?>" class="hsc-slider swiper">
	<div class="swiper-wrapper">
	<?php while ($q->have_posts()) : $q->the_post();
		$img = get_the_post_thumbnail_url(get_the_ID(), 'full');
		if (!$img) { $img = includes_url('images/media/default.png'); }
		$title = get_the_title();
		$text = has_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(get_the_content()), 24);
		$raw  = apply_filters('the_content', get_the_content());

		$permalink = get_permalink();

		$ctas = $this->extract_ctas(
			$raw,
			$max_ctas,
			$cta_class,
			$cta_wrap,
			$missing_href,
			$fallback_href,
			$fallback_to_post,
			$permalink
		);
	?>
		<div class="swiper-slide">
			<div class="hsc-slide">
				<img class="hsc-bg" src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($title); ?>">
				<div class="hsc-overlay">
					<h2><?php echo esc_html($title); ?></h2>
					<p><?php echo esc_html($text); ?></p>
					<?php if ($ctas): ?>
					<div class="hsc-ctas"><?php echo implode('', $ctas); ?></div>
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
