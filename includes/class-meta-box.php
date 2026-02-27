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

		// Quick Edit column in Posts list
		add_action( 'manage_posts_custom_column', array( $this, 'render_custom_column' ), 10, 2 );
		add_action( 'manage_pages_custom_column', array( $this, 'render_custom_column' ), 10, 2 );
		add_filter( 'manage_posts_columns', array( $this, 'add_custom_column' ) );
		add_filter( 'manage_pages_columns', array( $this, 'add_custom_column' ) );

		// Add quick edit fields
		add_action( 'quick_edit_custom_box', array( $this, 'render_quick_edit_fields' ), 10, 2 );

		// Save quick edit
		add_action( 'wp_ajax_wp_nexus_quick_edit_save', array( $this, 'quick_edit_save' ) );
	}

	/**
	 * Add custom column to posts/pages list
	 */
	public function add_custom_column( $columns ) {
		$columns['wp_nexus_type'] = __( 'Nexus Type', 'wp-nexus' );
		return $columns;
	}

	/**
	 * Render custom column content
	 */
	public function render_custom_column( $column, $post_id ) {
		if ( 'wp_nexus_type' === $column ) {
			$nexus_type = get_post_meta( $post_id, '_seo_nexus_type', true );
			$labels = array(
				'pillar'     => __( 'Pillar', 'wp-nexus' ),
				'sub-pillar' => __( 'Sub-Pillar', 'wp-nexus' ),
				'cluster'   => __( 'Cluster', 'wp-nexus' ),
			);

			if ( $nexus_type && isset( $labels[ $nexus_type ] ) ) {
				$class = 'nexus-' . $nexus_type;
				echo '<span class="wp-nexus-badge ' . esc_attr( $class ) . '">' . esc_html( $labels[ $nexus_type ] ) . '</span>';
			} else {
				echo '<span class="wp-nexus-none">—</span>';
			}
		}
	}

	/**
	 * Render quick edit fields
	 */
	public function render_quick_edit_fields( $column, $post_type_name ) {
		if ( 'wp_nexus_type' !== $column ) {
			return;
		}

		$nexus_type = '';
		$nexus_keyword = '';

		// Get current values if editing
		if ( isset( $_GET['post'] ) ) {
			$post_id = intval( $_GET['post'] );
			$nexus_type    = get_post_meta( $post_id, '_seo_nexus_type', true );
			$nexus_keyword = get_post_meta( $post_id, '_seo_nexus_keyword', true );
		}
		?>
		<fieldset class="inline-edit-col-left">
			<div class="inline-edit-col">
				<label class="inline-edit-group">
					<span class="title"><?php _e( 'Nexus Type', 'wp-nexus' ); ?></span>
					<select name="seo_nexus_type">
						<option value=""><?php _e( '— None —', 'wp-nexus' ); ?></option>
						<option value="pillar" <?php selected( $nexus_type, 'pillar' ); ?>><?php _e( 'Pillar', 'wp-nexus' ); ?></option>
						<option value="sub-pillar" <?php selected( $nexus_type, 'sub-pillar' ); ?>><?php _e( 'Sub-Pillar', 'wp-nexus' ); ?></option>
						<option value="cluster" <?php selected( $nexus_type, 'cluster' ); ?>><?php _e( 'Cluster', 'wp-nexus' ); ?></option>
					</select>
				</label>
				<label class="inline-edit-group">
					<span class="title"><?php _e( 'Keyword', 'wp-nexus' ); ?></span>
					<input type="text" name="seo_nexus_keyword" value="<?php echo esc_attr( $nexus_keyword ); ?>" />
				</label>
			</div>
		</fieldset>
		<?php
	}

	/**
	 * Save quick edit via AJAX
	 */
	public function quick_edit_save() {
		check_ajax_referer( 'wp_nexus_quick_edit', 'nonce' );

		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$nexus_type = isset( $_POST['nexus_type'] ) ? sanitize_text_field( $_POST['nexus_type'] ) : '';
		$nexus_keyword = isset( $_POST['nexus_keyword'] ) ? sanitize_text_field( $_POST['nexus_keyword'] ) : '';

		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( 'Permission denied' );
		}

		if ( $nexus_type ) {
			update_post_meta( $post_id, '_seo_nexus_type', $nexus_type );
		} else {
			delete_post_meta( $post_id, '_seo_nexus_type' );
		}

		if ( $nexus_keyword ) {
			update_post_meta( $post_id, '_seo_nexus_keyword', $nexus_keyword );
		}

		wp_send_json_success();
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
