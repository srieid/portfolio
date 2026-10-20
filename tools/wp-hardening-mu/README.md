# wp-hardening-mu

The security settings I apply first when I take over a neglected or hacked WordPress site. It covers the basics only. It doesn't replace updates, backups, a firewall and two-factor login.

## What it does

- Turns off XML-RPC
- Hides the WordPress version
- Shows one message for any failed login
- Hides the REST user list from logged-out visitors
- Redirects `?author=1` scans
- Adds three safe response headers

## Install

Copy `wp-hardening.php` into `wp-content/mu-plugins/`. To turn a rule off:

```php
add_filter( 'wph_disable_xmlrpc', '__return_false' ); // if you use the WordPress mobile app or Jetpack
```

Also add this to `wp-config.php`:

```php
define( 'DISALLOW_FILE_EDIT', true );
```

## My steps on a hacked site

1. Back everything up, then work on a copy.
2. Update core, plugins and themes. Delete the ones that aren't used.
3. Replace core files with a fresh download and compare plugin and theme files with clean copies.
4. Look for PHP files in `wp-content/uploads`, and check `.htaccess` and `wp-config.php`.
5. Check the user list for accounts you don't recognise. Reset all passwords and the salts.
6. Scan again. If Google flagged the site, request a review in Search Console.
7. Turn on two-factor login, automatic backups and a firewall.

GPL-2.0-or-later.
