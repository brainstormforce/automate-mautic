<?php
/**
 * Plugin Name: AutomatePlug - Mautic for WordPress
 * Plugin URI: http://www.brainstormforce.com/
 * Description: Add registered WP users and commentors to mautic contacts.
 * Version: 1.0.6
 * Author: Brainstorm Force
 * Author URI: http://www.brainstormforce.com/
 * Text Domain: automate-mautic
 *
 * @package automate-mautic
 * @author Brainstorm Force
 */

// exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
require_once 'classes/class-apmautic-loader.php';
