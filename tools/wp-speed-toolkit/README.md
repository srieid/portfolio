# wp-speed-toolkit

One file with the speed fixes I end up adding to most WordPress and WooCommerce sites. Each fix can be switched off from your theme, so you don't have to edit the plugin.

Measure before and after. A fix that helps one site can break another.

## What's in it

1. Removes the emoji scripts and styles
2. Removes the embed script and oEmbed links
3. Slows the Heartbeat API to once a minute
4. Turns the Heartbeat off on the front end
5. Strips `?ver=` from CSS and JS (off by default)
6. Adds `defer` to front-end scripts, except jQuery
7. WooCommerce: skips cart fragments on pages that aren't shop pages
8. WooCommerce: skips the Woo styles and scripts on pages that aren't shop pages
9. Preloads any font files you list

## Install

Copy `wp-speed-toolkit.php` into `wp-content/mu-plugins/`, or upload it as a normal plugin. Try it on a staging copy first. Deferring scripts can break things that depend on load order.

## Switching things off

```php
add_filter( 'wpst_defer_scripts', '__return_false' );
add_filter( 'wpst_disable_emojis', '__return_false' );
```

Keep cart fragments if your header shows a mini cart:

```php
add_filter( 'wpst_has_mini_cart', '__return_true' );
```

Leave a script out of the defer list:

```php
add_filter( 'wpst_defer_skip', function ( $handles ) {
	$handles[] = 'my-slider';
	return $handles;
} );
```

Preload a font:

```php
add_filter( 'wpst_preload_fonts', function () {
	return array( 'https://example.com/fonts/main.woff2' );
} );
```

## How I test it

Run PageSpeed Insights on the home page, a product page and the checkout, and note LCP, INP and CLS. Install the plugin, click through the cart, checkout, sliders and forms, then run the same pages again.

GPL-2.0-or-later.
