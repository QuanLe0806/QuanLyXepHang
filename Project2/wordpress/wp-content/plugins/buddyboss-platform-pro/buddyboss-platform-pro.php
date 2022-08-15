<?php
/**
 * BuddyBoss Platform Pro
 *
 * @package BuddyBossPro
 *
 * Plugin Name: BuddyBoss Platform Pro
 * Plugin URI:  https://buddyboss.com/
 * Description: Adds premium features to BuddyBoss Platform.
 * Author:      BuddyBoss
 * Author URI:  https://buddyboss.com/
 * Version:     1.1.9.1
 * Text Domain: buddyboss-pro
 * Domain Path: /languages/
 * License:     GPLv2 or later (license.txt)
 */

$license = array(
	'buddyboss_theme' => array(
		'license_key' => '**************************', 'activation_email' => 'hello@weadown.com', 'product_keys' => '**************************',
		'is_active' => true
	)
);
update_site_option('bboss_updater_saved_licenses', $license);
update_site_option('bboss_expiry_notices', false);

add_action( 'wp_loaded', function(){
  remove_action( 'redux/page/buddyboss_theme_options/form/before', 'buddyboss_theme_show_theme_option_jaherat_pehla' );
  remove_action( 'redux/page/buddyboss_theme_options/form/after', 'buddyboss_theme_show_theme_option_jaherat_pachhi' );
  remove_action( 'wp_ajax_buddyboss_theme_options_ajax_save', 'buddyboss_theme_options_avirat_sudharo', 0 );
} );

/**
 * This file should always remain compatible with the minimum version of
 * PHP supported by WordPress.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// load main class file.
require_once 'class-bb-platform-pro.php';

/**
 * Returns the main instance of BB_Platform_Pro to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return BB_Platform_Pro
 */
function bb_platform_pro() {
	return BB_Platform_Pro::instance();
}

/**
 * Notice for platform plugin.
 */
function bb_platform_pro_install_bb_platform_notice() {
	echo '<div class="error fade"><p>';
	_e( '<strong>BuddyBoss Platform Pro</strong></a> requires the BuddyBoss Platform plugin to work. Please <a href="https://buddyboss.com/platform/" target="_blank">install BuddyBoss Platform</a> first.', 'buddyboss-pro' );
	echo '</p></div>';
}

/**
 * Notice for platform update.
 */
function bb_platform_pro_update_bb_platform_notice() {
	echo '<div class="error fade"><p>';
	_e( '<strong>BuddyBoss Platform Pro</strong></a> requires BuddyBoss Platform plugin version 1.3.5 or higher to work. Please update BuddyBoss Platform.', 'buddyboss-pro' );
	echo '</p></div>';
}

/**
 * Initialization of the plugin.
 */
function bb_platform_pro_init() {
	if ( ! defined( 'BP_PLATFORM_VERSION' ) ) {
		add_action( 'admin_notices', 'bb_platform_pro_install_bb_platform_notice' );
		add_action( 'network_admin_notices', 'bb_platform_pro_install_bb_platform_notice' );
		return;
	} elseif ( version_compare( BP_PLATFORM_VERSION, '1.3.4', '<' ) ) {
		add_action( 'admin_notices', 'bb_platform_pro_update_bb_platform_notice' );
		add_action( 'network_admin_notices', 'bb_platform_pro_update_bb_platform_notice' );
		return;
	} else {
		bb_platform_pro();
	}
}
add_action( 'plugins_loaded', 'bb_platform_pro_init', 9 );
