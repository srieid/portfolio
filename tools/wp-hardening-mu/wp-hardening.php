<?php
/**
 * Plugin Name: WP Hardening (mu-plugin)
 * Description: Basic security settings. Each one can be switched off with a filter.
 * Version: 1.0.0
 * Author: Srini K.
 * License: GPL-2.0-or-later
 *
 * This covers the basics only. It does not replace updates, backups,
 * a firewall, two-factor login and a malware scan.
 */

defined( 'ABSPATH' ) || exit;

function wph_on( $key ) {
	return (bool) apply_filters( "wph_{$key}", true );
}

/** Turn off XML-RPC (a common brute-force target) unless you need it. */
add_filter( 'xmlrpc_enabled', function ( $enabled ) {
	return wph_on( 'disable_xmlrpc' ) ? false : $enabled;
} );

/** Hide the WordPress version from the page source and the feeds. */
add_action( 'init', function () {
	if ( wph_on( 'hide_version' ) ) {
		remove_action( 'wp_head', 'wp_generator' );
		add_filter( 'the_generator', '__return_empty_string' );
	}
} );

/** Use the same message for a wrong user name and a wrong password. */
add_filter( 'login_errors', function ( $error ) {
	return wph_on( 'generic_login_error' ) ? 'The details you entered are not correct.' : $error;
} );

/** Stop public user name lists through the REST API for visitors who are not logged in. */
add_filter( 'rest_endpoints', function ( $endpoints ) {
	if ( wph_on( 'hide_rest_users' ) && ! is_user_logged_in() ) {
		unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
} );

/** Block author archive scans such as /?author=1. */
add_action( 'template_redirect', function () {
	if ( wph_on( 'block_author_scan' ) && ! is_user_logged_in() && isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
} );

/** Security headers. Test before enabling on sites that use iframes or third-party embeds. */
add_action( 'send_headers', function () {
	if ( ! wph_on( 'security_headers' ) ) {
		return;
	}
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
} );

/** Define in wp-config.php instead of here: DISALLOW_FILE_EDIT stops the theme and plugin editor. */
