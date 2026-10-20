<?php
/**
 * Plugin Name: WP Speed Toolkit
 * Description: Speed fixes for WordPress and WooCommerce. Each one can be switched off with a filter.
 * Version: 1.0.0
 * Author: Srini K.
 * License: GPL-2.0-or-later
 *
 * Drop this file in wp-content/mu-plugins/ (or install as a normal plugin).
 * Turn a fix off from your theme or another plugin, for example:
 *   add_filter( 'wpst_disable_emojis', '__return_false' );
 */

defined( 'ABSPATH' ) || exit;

/** Every fix is on unless its filter returns false. */
function wpst_on( $key ) {
	return (bool) apply_filters( "wpst_{$key}", true );
}

/** 1. Remove the emoji scripts and styles (about two requests per page). */
add_action( 'init', function () {
	if ( ! wpst_on( 'disable_emojis' ) ) {
		return;
	}
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
} );

/** 2. Remove oEmbed discovery and the embed script if you do not embed other sites. */
add_action( 'init', function () {
	if ( ! wpst_on( 'disable_embeds' ) ) {
		return;
	}
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	add_action( 'wp_footer', function () {
		wp_dequeue_script( 'wp-embed' );
	} );
} );

/** 3. Slow the Heartbeat API down (default is every 15 to 60 seconds). */
add_filter( 'heartbeat_settings', function ( $settings ) {
	if ( wpst_on( 'throttle_heartbeat' ) ) {
		$settings['interval'] = (int) apply_filters( 'wpst_heartbeat_interval', 60 );
	}
	return $settings;
} );

/** 4. Stop the Heartbeat on the front end, where it is rarely needed. */
add_action( 'init', function () {
	if ( ! is_admin() && wpst_on( 'no_frontend_heartbeat' ) ) {
		wp_deregister_script( 'heartbeat' );
	}
}, 1 );

/** 5. Drop the version query string from CSS and JS (helps some CDNs cache). Off by default. */
add_filter( 'style_loader_src', 'wpst_strip_version', 15 );
add_filter( 'script_loader_src', 'wpst_strip_version', 15 );
function wpst_strip_version( $src ) {
	if ( ! (bool) apply_filters( 'wpst_strip_versions', false ) || ! is_string( $src ) ) {
		return $src;
	}
	return remove_query_arg( 'ver', $src );
}

/** 6. Add defer to front-end scripts, except a skip list (jQuery stays blocking). */
add_filter( 'script_loader_tag', function ( $tag, $handle, $src ) {
	if ( is_admin() || ! wpst_on( 'defer_scripts' ) ) {
		return $tag;
	}
	$skip = apply_filters( 'wpst_defer_skip', array( 'jquery', 'jquery-core', 'jquery-migrate' ) );
	if ( in_array( $handle, $skip, true ) || false !== strpos( $tag, ' defer' ) || false !== strpos( $tag, ' async' ) ) {
		return $tag;
	}
	return str_replace( ' src=', ' defer src=', $tag );
}, 10, 3 );

/** 7. WooCommerce: load cart fragments only where a mini cart can appear. */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! class_exists( 'WooCommerce' ) || ! wpst_on( 'trim_cart_fragments' ) ) {
		return;
	}
	if ( is_cart() || is_checkout() || is_woocommerce() || is_account_page() ) {
		return;
	}
	if ( (bool) apply_filters( 'wpst_has_mini_cart', false ) ) {
		return;
	}
	wp_dequeue_script( 'wc-cart-fragments' );
}, 99 );

/** 8. WooCommerce: skip the WooCommerce styles and scripts on pages that are not shop pages. */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! class_exists( 'WooCommerce' ) || ! wpst_on( 'woo_assets_only_on_shop' ) ) {
		return;
	}
	if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
		return;
	}
	foreach ( array( 'woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen', 'wc-blocks-style' ) as $h ) {
		wp_dequeue_style( $h );
	}
	foreach ( array( 'woocommerce', 'wc-add-to-cart', 'jquery-blockui', 'js-cookie' ) as $h ) {
		wp_dequeue_script( $h );
	}
}, 99 );

/** 9. Preload a font file. Pass your URLs with the wpst_preload_fonts filter. */
add_action( 'wp_head', function () {
	$fonts = (array) apply_filters( 'wpst_preload_fonts', array() );
	foreach ( $fonts as $url ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( $url ) );
	}
}, 1 );
