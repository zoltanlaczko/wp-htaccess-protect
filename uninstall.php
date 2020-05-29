<?php
// File for uninstalling the plugin

//if uninstall not called from WordPress
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();

// Require the plugin files
require_once( __DIR__ . '/class.htaccess.php' );

global $ZOTYA_HP;
$ZOTYA_HP = new ZOTYA_HP();

// Remove htaccess
$ZOTYA_HP->remove_wp_root_protection();
$ZOTYA_HP->remove_wp_admin_protection();

// Delete the plugin options from DB
delete_option( 'zotya_hp_options' );
