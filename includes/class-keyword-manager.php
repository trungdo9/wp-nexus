<?php
/**
 * Keyword Manager class for WP Nexus plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Nexus_Keyword_Manager {
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_ajax_wp_nexus_get_keywords', array( $this, 'ajax_get_keywords' ) );
		add_action( 'wp_ajax_wp_nexus_add_keyword', array( $this, 'ajax_add_keyword' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function enqueue_scripts() {
		global $post;

		// Only enqueue on post edit screens
		if ( ! $post || ! in_array( $post->post_type, get_post_types( array( 'public' => true ), 'names' ), true ) ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );

		wp_register_script(
			'wp-nexus-admin',
			WP_NEXUS_URL . 'assets/js/admin.js',
			array( 'jquery', 'jquery-ui-autocomplete' ),
			WP_NEXUS_VERSION,
			true
		);

		wp_localize_script(
			'wp-nexus-admin',
			'wpNexus',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wp_nexus_nonce' ),
			)
		);

		wp_enqueue_script( 'wp-nexus-admin' );

		wp_register_style(
			'wp-nexus-admin',
			WP_NEXUS_URL . 'assets/css/admin.css',
			array(),
			WP_NEXUS_VERSION
		);
		wp_enqueue_style( 'wp-nexus-admin' );
	}

	public function get_keywords( $term = '' ) {
		global $wpdb;

		if ( empty( $term ) ) {
			return array();
		}

		$query = $wpdb->prepare(
			"SELECT DISTINCT pm.meta_value
			FROM $wpdb->postmeta pm
			INNER JOIN $wpdb->posts p ON pm.post_id = p.ID
			WHERE pm.meta_key = '_seo_nexus_keyword'
			AND pm.meta_value LIKE %s
			AND p.post_status = 'publish'
			ORDER BY pm.meta_value ASC
			LIMIT 20",
			'%' . $wpdb->esc_like( $term ) . '%'
		);

		return $wpdb->get_col( $query );
	}

	public function ajax_get_keywords() {
		check_ajax_referer( 'wp_nexus_nonce', 'nonce' );

		$term = isset( $_GET['term'] ) ? sanitize_text_field( $_GET['term'] ) : '';
		$keywords = $this->get_keywords( $term );

		wp_send_json_success( $keywords );
	}

	public function ajax_add_keyword() {
		check_ajax_referer( 'wp_nexus_nonce', 'nonce' );

		// For future expansion - add custom keyword storage
		wp_send_json_success( array( 'message' => 'Keyword added' ) );
	}
}
