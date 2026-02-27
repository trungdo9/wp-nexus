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
					'type'   => array(
						'required'          => false,
						'type'              => 'array',
						'items'             => array(
							'type' => 'string',
							'enum' => array( 'pillar', 'sub-pillar', 'cluster' ),
						),
						'sanitize_callback' => array( $this, 'sanitize_types' ),
					),
					'format' => array(
						'required'          => false,
						'type'              => 'string',
						'enum'              => array( 'json', 'xml' ),
						'default'           => 'json',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'schema' => array( $this, 'get_links_schema' ),
			)
		);
	}

	/**
	 * Get links endpoint schema
	 */
	public function get_links_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'seo-nexus-links',
			'type'       => 'array',
			'items'      => array(
				'type'       => 'object',
				'properties' => array(
					'url'       => array(
						'type'        => 'string',
						'description' => 'URL of the post',
					),
					'title'     => array(
						'type'        => 'string',
						'description' => 'Post title',
					),
					'keyword'   => array(
						'type'        => 'string',
						'description' => 'SEO nexus keyword',
					),
					'type'      => array(
						'type'        => 'string',
						'description' => 'Nexus type: pillar, sub-pillar, or cluster',
						'enum'        => array( 'pillar', 'sub-pillar', 'cluster' ),
					),
					'post_type' => array(
						'type'        => 'string',
						'description' => 'WordPress post type',
					),
				),
			),
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

		// Check for X-API-Key header - handle both apache_request_headers() and $_SERVER formats
		// apache_request_headers(): X-API-Key -> X-API-KEY
		// $_SERVER: HTTP_X_API_KEY (hyphens converted to underscores by PHP)
		$api_key = $headers['X-API-KEY'] ?? $headers['HTTP_X_API_KEY'] ?? null;

		if ( ! $api_key ) {
			return false;
		}

		return $api_key === WP_NEXUS_API_KEY;
	}

	private function get_headers() {
		if ( function_exists( 'apache_request_headers' ) ) {
			$headers = apache_request_headers();
			// Convert header keys to uppercase with hyphens for case-insensitive matching
			$headers = array_combine( array_map( 'strtoupper', array_keys( $headers ) ), array_values( $headers ) );
		} else {
			$headers = isset( $_SERVER ) ? $_SERVER : array();
			// Also handle $_SERVER which uses HTTP_X_API_KEY format
			$headers = array_combine( array_map( 'strtoupper', array_keys( $headers ) ), array_values( $headers ) );
		}

		return $headers;
	}

	public function get_links( $request ) {
		$types  = $request->get_param( 'type' );
		$format = $request->get_param( 'format' );
		$format = $format ?: 'json';

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
			$seo_nexus_type    = get_post_meta( $post->ID, '_seo_nexus_type', true );
			$seo_nexus_keyword = get_post_meta( $post->ID, '_seo_nexus_keyword', true );

			if ( $seo_nexus_type ) {
				$links[] = array(
					'url'       => get_permalink( $post->ID ),
					'title'     => $post->post_title,
					'keyword'   => $seo_nexus_keyword,
					'type'      => $seo_nexus_type,
					'post_type' => $post->post_type,
				);
			}
		}

		wp_reset_postdata();

		if ( 'xml' === $format ) {
			return $this->render_xml( $links );
		}

		return rest_ensure_response( $links );
	}

	/**
	 * Render links as XML (sitemap-style format)
	 *
	 * @param array $links Links array.
	 * @return WP_REST_Response
	 */
	private function render_xml( $links ) {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		foreach ( $links as $link ) {
			$xml .= "  <url>\n";
			$xml .= '    <loc>' . esc_url( $link['url'] ) . "</loc>\n";
			$xml .= '    <title>' . esc_html( $link['title'] ) . "</title>\n";
			$xml .= '    <keyword>' . esc_html( $link['keyword'] ) . "</keyword>\n";
			$xml .= '    <type>' . esc_html( $link['type'] ) . "</type>\n";
			$xml .= "  </url>\n";
		}

		$xml .= '</urlset>';

		return new WP_REST_Response( $xml, 200, array(
			'Content-Type' => 'application/xml; charset=' . get_bloginfo( 'charset' ),
		) );
	}
}
