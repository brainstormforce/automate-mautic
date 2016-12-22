<?php
/**
 * Plugin Name: AutomatePlus - Mautic for WordPress
 * Plugin URI: http://www.brainstormforce.com/
 * Description: Sync your new reigstered WP users, contact form user with mautic contacts.
 * Version: 1.0.0
 * Author: Brainstorm Force
 * Author URI: http://www.brainstormforce.com/
 * Text Domain: automateplus-mautic-wp
 */
//exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
define( 'AUTOMATEPLUS_MAUTIC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AUTOMATEPLUS_MAUTIC_PLUGIN_URL', plugins_url( '/', __FILE__ ) );

/**
 * Initiate plugin
 */
require_once( AUTOMATEPLUS_MAUTIC_PLUGIN_DIR . '/classes/class-apm-automateplus.php' );
$AutomatePlus_Mautic = AutomatePlus_Mautic::instance();