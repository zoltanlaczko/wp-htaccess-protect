=== htaccess protect ===
Contributors: zoltanlaczko
Tags: htaccess, htpasswd, security, protect, protection, wp-admin, wp-login
Author URI: https://github.com/zoltanlaczko
Plugin URI: https://github.com/zoltanlaczko/wp-htaccess-protect/
Requires at least: 5.0
Tested up to: 5.6
Requires PHP: 5.6
Stable tag: 0.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

htaccess protect - Protect your wordpress login or admin pages with password.

== Description ==

Using the password protection will give you extra security layer of protection from brute force hacking attacks. Additionally, it's also an easy way to password protect your entire site, without needing to create separate WordPress users for each visitor.

When you enable the password protection, the user won't be able to see anything - not even see the protected page - until he/she inserts the username/password. You can password protect the whole website, including the administrator pages; you can password protect the administrator pages; or you can password protect the WordPress login page.

The plugin options include:

*   Enabling/disabling the password protection to wp-login.php, WordPress admin pages.
*   Modifying the existing users: you can change any .htaccess user’s password and remove the users.
*   Create/modify an unlimited number of .htaccess users;
*   Protect your whole site, making it accessible to only those who have the .htaccess user.

This plugin is originally was based on <a href="https://wordpress.org/plugins/htaccess-site-access-control/" target="_blank">.htaccess Site Access Control</a>. That plugin was working fine but it was abandoned for years and not compatible with the latest Wordpress. Most part of the plugin were refactored and translated.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/zotya-htaccess-protect` directory, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the `Settings -> htaccess protect` screen

== Frequently Asked Questions ==

= Which options do you modify? =

You can choose between the following options:
1. Enabling/disabling the password protection to wp-login.php, WordPress admin pages, and/or the whole site.
2. Modifying the existing users: you can change any .htaccess user's password and remove the users.
3. Adding a new .htaccess user.

Note that you have to have at least one user to be able to enable any of the options: otherwise you would be locked out!

= The plugin is giving a warning that some of the files need to be writable for it to work, what does this mean? =

Since the plugin is protecting your site via modifying .htaccess and .htpasswd files, it works only if these files are writable by WordPress. If the files don't exist, you can just create empty writable files to the location brought out in the plugin's warning. You can also see from there which files are already writable and which not.

= I forgot my password, and got locked out from the site! What can I do? =
For accessing your site again, you have to modify two files:
1. .htaccess file in your WordPress root directory (the directory where the file wp-config.php is located);
2. .htaccess file in your WordPress wp-admin folder

From both files, delete everything BETWEEN these two lines:

*   # BEGIN ZOTYA htaccess protect
*   # END ZOTYA htaccess protect

IMPORTANT: Before modifying either of the files, make a copy of them!

For accessing the files, either use FTP or log in to your web hosting service provider, usually they also enable direct file modification.

== Changelog ==

= 0.4.0 =

* Add WordPress 5.6 support

= 0.3.0 =

* Add WordPress 5.5 support

= 0.2.0 =

* Add authorization fix option if you have loopback issues
* refactor remove function

= 0.1.0 =

* Initial release
