<?php
/**
 * REST API class for WP Nexus plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Nexus_REST_API {
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route(
			'seo-nexus/v1',
			'/links',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_links' ),
				'permission_callback' => array( $this, 'check_api_key' ),
				'args'                => array(
					'type' => array(
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	public function check_api_key() {
		$headers = $this->get_headers();

		if ( isset( $headers['X-API-Key'] ) ) {
			$api_key = $headers['X-API-Key'];
		} else {
			return false;
		}

		return $api_key === WP_NEXUS_API_KEY;
	}

	private function get_headers() {
		if ( function_exists( 'apache_request_headers' ) ) {
			$headers = apache_request_headers();
			$headers = array_combine( array_map( 'ucwords', array_keys( $headers ) ), array_values( $headers ) );
		} else {
			$headers = isset( $_SERVER ) ? $_SERVER : array();
		}

		return $headers;
	}

	public function get_links( $request ) {
		$type = $request->get_param( 'type' );

		$post_types = get_post_types( array( 'public' => true ), 'names' );

		$args = array(
			'post_type'      => array_values( $post_types ),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => '_seo_nexus_type',
					'value'   => array( 'pillar', 'sub-pillar' ),
					'compare' => 'IN',
				),
			),
		);

		if ( $type && in_array( $type, array( 'pillar', 'sub-pillar' ), true ) ) {
			$args['meta_query'] = array(
				array(
					'key'   => '_seo_nexus_type',
					'value' => $type,
				),
			);
		}

		$query  = new WP_Query( $args );
		$links  = array();

		foreach ( $query->posts as $post ) {
			$seo_nexus_type = get_post_meta( $post->ID, '_seo_nexus_type', true );

			if ( $seo_nexus_type ) {
				$links[] = array(
					'url'  => get_permalink( $post->ID ),
					'type' => $seo_nexus_type,
				);
			}
		}

		wp_reset_postdata();

		return rest_ensure_response( $links );
	}
}
