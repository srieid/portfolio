<?php
/**
 * Plugin Name: Woo Checkout Trimmer
 * Description: Removes checkout fields you do not need. Every rule can be changed with a filter.
 * Version: 1.0.0
 * Author: Srini K.
 * Requires Plugins: woocommerce
 * License: GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fields to remove. Change the list with the wct_removed_fields filter, for example:
 *   add_filter( 'wct_removed_fields', fn() => array( 'billing_company', 'billing_address_2' ) );
 */
function wct_removed_fields() {
	return (array) apply_filters(
		'wct_removed_fields',
		array( 'billing_company', 'billing_address_2', 'shipping_company', 'shipping_address_2' )
	);
}

add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
	$remove = wct_removed_fields();
	foreach ( $fields as $group => $group_fields ) {
		foreach ( $remove as $key ) {
			if ( isset( $fields[ $group ][ $key ] ) ) {
				unset( $fields[ $group ][ $key ] );
			}
		}
	}
	return $fields;
}, 20 );

/** Digital-only carts do not need a shipping address or the phone number. */
add_filter( 'woocommerce_cart_needs_shipping_address', function ( $needs ) {
	if ( ! (bool) apply_filters( 'wct_skip_address_for_digital', true ) ) {
		return $needs;
	}
	if ( ! WC()->cart ) {
		return $needs;
	}
	foreach ( WC()->cart->get_cart() as $item ) {
		$product = $item['data'];
		if ( $product && ! $product->is_virtual() && ! $product->is_downloadable() ) {
			return $needs;
		}
	}
	return false;
} );

add_filter( 'woocommerce_billing_fields', function ( $fields ) {
	if ( ! (bool) apply_filters( 'wct_phone_optional', true ) ) {
		return $fields;
	}
	if ( isset( $fields['billing_phone'] ) ) {
		$fields['billing_phone']['required'] = false;
	}
	return $fields;
} );

/** Hide the order notes box. Turn it back on with add_filter( 'wct_hide_order_notes', '__return_false' ). */
add_filter( 'woocommerce_enable_order_notes_field', function ( $enabled ) {
	return (bool) apply_filters( 'wct_hide_order_notes', false ) ? false : $enabled;
} );
