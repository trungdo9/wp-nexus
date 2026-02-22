<?php
/**
 * Plugin Name: WP Nexus
 * Description: SEO nexus type management plugin for pillar content strategy
 * Version: 1.0.0
 * Author: Developer
 * Text Domain: wp-nexus
 */

if ( ! defined( 'WP_NEXUS_VERSION' ) ) {
	define( 'WP_NEXUS_VERSION', '1.0.0' );
}

if ( ! defined( 'WP_NEXUS_PATH' ) ) {
	define( 'WP_NEXUS_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WP_NEXUS_URL' ) ) {
	define( 'WP_NEXUS_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'WP_NEXUS_API_KEY' ) ) {
	define( 'WP_NEXUS_API_KEY', '1234567890' );
}

class WP_Nexus {
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		// Load text domain
		load_plugin_textdomain( 'wp-nexus', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		// Initialize components
		require_once WP_NEXUS_PATH . 'includes/class-meta-box.php';
		require_once WP_NEXUS_PATH . 'includes/class-rest-api.php';
		require_once WP_NEXUS_PATH . 'includes/class-keyword-manager.php';
		require_once WP_NEXUS_PATH . 'includes/class-admin-page.php';

		WP_Nexus_Meta_Box::get_instance();
		WP_Nexus_REST_API::get_instance();
		WP_Nexus_Keyword_Manager::get_instance();
		WP_Nexus_Admin_Page::get_instance();
	}
}

function wp_nexus() {
	return WP_Nexus::get_instance();
}

wp_nexus();
