<?php
/**
* Plugin Name:       htaccess protect
* Plugin URI:        https://github.com/zoltanlaczko/wp-htaccess-protect/
* Description:       Protect your login or admin pages with password.
* Version:           0.7.0
* Requires PHP:      5.6
* Author:            Zoltan Laczko
* Author URI:        https://github.com/zoltanlaczko
* Licence:           GPLv2 or later
* License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
* Text Domain:       zotya-htaccess-protect
* Domain Path:       /languages
*
* Copyright (C) 2020 Zoltan Laczko (https://github.com/zoltanlaczko)
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* @package zotya_htaccess_protect
*/

define('ZOTYA_HP_FILE', __FILE__);

// Require the plugin files
require_once( __DIR__ . '/class.htaccess.php' );
require_once( __DIR__ . '/settings-page.php' );

// Create global object
global $ZOTYA_HP;
$ZOTYA_HP = new ZOTYA_HP();

// Register installing/uninstalling functions
register_activation_hook( __FILE__, array( $ZOTYA_HP, 'activate' ) );
register_deactivation_hook( __FILE__, array( $ZOTYA_HP, 'deactivate' ) );

if ( is_admin() ){
	// Register plugin scripts
	add_action( 'admin_enqueue_scripts', array( $ZOTYA_HP, 'register_plugin_scripts' ) );
}
