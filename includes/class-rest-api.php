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
						'type'              => 'array',
						'items'             => array(
							'type' => 'string',
							'enum' => array( 'pillar', 'sub-pillar', 'cluster' ),
						),
						'sanitize_callback' => array( $this, 'sanitize_types' ),
					),
				),
			)
		);
	}

	/**
	 * Sanitize type parameter - accepts array or comma-separated string
	 */
	public function sanitize_types( $types ) {
		if ( is_string( $types ) ) {
			$types = explode( ',', $types );
		}

		$valid_types = array( 'pillar', 'sub-pillar', 'cluster' );
		$sanitized    = array();

		foreach ( (array) $types as $type ) {
			$type = trim( $type );
			if ( in_array( $type, $valid_types, true ) ) {
				$sanitized[] = $type;
			}
		}

		return $sanitized;
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
		$types = $request->get_param( 'type' );

		$post_types = get_post_types( array( 'public' => true ), 'names' );

		$args = array(
			'post_type'      => array_values( $post_types ),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);

		// Default: get all types if no filter
		$valid_types = array( 'pillar', 'sub-pillar', 'cluster' );

		if ( ! empty( $types ) && is_array( $types ) ) {
			// Filter by provided types
			$args['meta_query'] = array(
				array(
					'key'     => '_seo_nexus_type',
					'value'   => $types,
					'compare' => 'IN',
				),
			);
		} else {
			// Get all types
			$args['meta_query'] = array(
				array(
					'key'     => '_seo_nexus_type',
					'value'   => $valid_types,
					'compare' => 'IN',
				),
			);
		}

		$query = new WP_Query( $args );
		$links = array();

		foreach ( $query->posts as $post ) {
			$seo_nexus_type = get_post_meta( $post->ID, '_seo_nexus_type', true );

			if ( $seo_nexus_type ) {
				$links[] = array(
					'url'       => get_permalink( $post->ID ),
					'type'      => $seo_nexus_type,
					'post_type' => $post->post_type,
				);
			}
		}

		wp_reset_postdata();

		return rest_ensure_response( $links );
	}
}
