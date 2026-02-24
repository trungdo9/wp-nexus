<?php
/**
 * Meta Box class for WP Nexus plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Nexus_Meta_Box {
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta' ) );
		add_action( 'save_post', array( $this, 'sync_from_seo_plugins' ), 20 );
	}

	public function add_meta_boxes() {
		$post_types = $this->get_supported_post_types();

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'wp-nexus-meta-box',
				__( 'WP Nexus SEO', 'wp-nexus' ),
				array( $this, 'render_meta_box' ),
				$post_type,
				'side',
				'default'
			);
		}
	}

	private function get_supported_post_types() {
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		return array_values( $post_types );
	}

	public function render_meta_box( $post ) {
		wp_nonce_field( 'wp_nexus_meta_box', 'wp_nexus_meta_box_nonce' );

		$seo_nexus_type          = get_post_meta( $post->ID, '_seo_nexus_type', true );
		$seo_nexus_keyword       = get_post_meta( $post->ID, '_seo_nexus_keyword', true );
		$seo_nexus_parent_keyword = get_post_meta( $post->ID, '_seo_nexus_parent_keyword', true );

		?>
		<p>
			<label for="seo-nexus-type"><strong><?php _e( 'Nexus Type', 'wp-nexus' ); ?></strong></label>
			<select id="seo-nexus-type" name="seo_nexus_type" style="width: 100%; margin-top: 5px;">
				<option value=""><?php _e( '-- Select Type --', 'wp-nexus' ); ?></option>
				<option value="pillar" <?php selected( $seo_nexus_type, 'pillar' ); ?>><?php _e( 'Pillar', 'wp-nexus' ); ?></option>
				<option value="sub-pillar" <?php selected( $seo_nexus_type, 'sub-pillar' ); ?>><?php _e( 'Sub-Pillar', 'wp-nexus' ); ?></option>
				<option value="cluster" <?php selected( $seo_nexus_type, 'cluster' ); ?>><?php _e( 'Cluster', 'wp-nexus' ); ?></option>
			</select>
		</p>
		<p>
			<label for="seo-nexus-keyword"><strong><?php _e( 'Keyword', 'wp-nexus' ); ?></strong></label>
			<input type="text" id="seo-nexus-keyword" name="seo_nexus_keyword" value="<?php echo esc_attr( $seo_nexus_keyword ); ?>" style="width: 100%; margin-top: 5px;" autocomplete="off" />
			<span class="description"><?php _e( 'Start typing to see existing keywords or add a new one.', 'wp-nexus' ); ?></span>
		</p>
		<p>
			<label for="seo-nexus-parent-keyword"><strong><?php _e( 'Parent Keyword', 'wp-nexus' ); ?></strong></label>
			<input type="text" id="seo-nexus-parent-keyword" name="seo_nexus_parent_keyword" value="<?php echo esc_attr( $seo_nexus_parent_keyword ); ?>" style="width: 100%; margin-top: 5px;" autocomplete="off" />
			<span class="description"><?php _e( 'Link to parent pillar keyword.', 'wp-nexus' ); ?></span>
		</p>
		<?php
	}

	public function save_meta( $post_id ) {
		if ( ! isset( $_POST['wp_nexus_meta_box_nonce'] ) ) {
			return $post_id;
		}

		if ( ! wp_verify_nonce( $_POST['wp_nexus_meta_box_nonce'], 'wp_nexus_meta_box' ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		if ( isset( $_POST['seo_nexus_type'] ) ) {
			update_post_meta( $post_id, '_seo_nexus_type', sanitize_text_field( $_POST['seo_nexus_type'] ) );
		}

		if ( isset( $_POST['seo_nexus_keyword'] ) ) {
			update_post_meta( $post_id, '_seo_nexus_keyword', sanitize_text_field( $_POST['seo_nexus_keyword'] ) );
		}

		if ( isset( $_POST['seo_nexus_parent_keyword'] ) ) {
			update_post_meta( $post_id, '_seo_nexus_parent_keyword', sanitize_text_field( $_POST['seo_nexus_parent_keyword'] ) );
		}
	}

	/**
	 * Sync keyword from Yoast/Rank Math to WP Nexus
	 * Priority: Rank Math -> Yoast
	 * Only sync if WP Nexus keyword is empty
	 */
	public function sync_from_seo_plugins( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Only sync for public post types
		$post_type = get_post_type( $post_id );
		if ( ! $post_type || ! is_post_type_viewable( $post_type ) ) {
			return $post_id;
		}

		// Check if WP Nexus keyword already exists
		$existing = get_post_meta( $post_id, '_seo_nexus_keyword', true );
		if ( ! empty( $existing ) ) {
			return $post_id;
		}

		// Priority 1: Rank Math
		$keyword = get_post_meta( $post_id, 'rank_math_focus_keyword', true );

		// Priority 2: Yoast SEO
		if ( empty( $keyword ) ) {
			$keyword = get_post_meta( $post_id, '_yoast_wpseo_focuskw', true );
		}

		// Sync if found
		if ( ! empty( $keyword ) ) {
			update_post_meta( $post_id, '_seo_nexus_keyword', sanitize_text_field( $keyword ) );
		}

		return $post_id;
	}

	/**
	 * Bulk sync keywords from all posts
	 */
	public static function bulk_sync_keywords() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return 0;
		}

		$post_types = get_post_types( array( 'public' => true ), 'names' );

		$posts = get_posts(
			array(
				'post_type'      => array_values( $post_types ),
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			)
		);

		$synced = 0;

		foreach ( $posts as $post ) {
			// Skip if already has keyword
			$existing = get_post_meta( $post->ID, '_seo_nexus_keyword', true );
			if ( ! empty( $existing ) ) {
				continue;
			}

			// Rank Math
			$keyword = get_post_meta( $post->ID, 'rank_math_focus_keyword', true );

			// Yoast
			if ( empty( $keyword ) ) {
				$keyword = get_post_meta( $post->ID, '_yoast_wpseo_focuskw', true );
			}

			if ( ! empty( $keyword ) ) {
				update_post_meta( $post->ID, '_seo_nexus_keyword', sanitize_text_field( $keyword ) );
				++$synced;
			}
		}

		return $synced;
	}
}
