<?php
/**
 * Plugin Name: AutomatePlus - Mautic for WordPress
 * Plugin URI: http://www.brainstormforce.com/
 * Description: Add registered WP users and commentors to mautic contacts.
 * Version: 1.0.4
 * Author: Brainstorm Force
 * Author URI: http://www.brainstormforce.com/
 * Text Domain: automateplus-mautic-wp
 *
 * @package automateplus-mautic
 * @author Brainstorm Force
 */

// exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
function ap_check_user_access() {
	if( current_user_can( 'access_automate_mautic' ) ) {
		require_once 'classes/class-amp-mautic-loader.php';
	}
}
add_action( 'plugins_loaded', 'ap_check_user_access' );