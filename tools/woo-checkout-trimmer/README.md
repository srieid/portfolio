# woo-checkout-trimmer

A shorter checkout tends to convert better, and these are the fields I most often remove for clients. Every rule is a filter, so you can change it from your theme.

## What it does

- Removes the company and address line 2 fields (you can change the list)
- Skips the shipping address when the cart only has virtual or downloadable products
- Makes the phone number optional
- Can hide the order notes box (off by default)

## Install

Upload the folder as a plugin and activate it. WooCommerce is required. Place a real test order on staging first, because some shipping plugins expect certain fields.

## Changing the rules

```php
add_filter( 'wct_removed_fields', function () {
	return array( 'billing_company' );
} );
add_filter( 'wct_phone_optional', '__return_false' );
add_filter( 'wct_hide_order_notes', '__return_true' );
```

## Things to test

1. Guest checkout with a physical product
2. Guest checkout with a download only
3. Logged-in checkout with a saved address
4. A shipping zone where the carrier needs a phone number

GPL-2.0-or-later.
