<?php
/**
 * Admin Page class for WP Nexus plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Nexus_Admin_Page {
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_init', array( $this, 'handle_bulk_sync' ) );
		add_action( 'admin_init', array( $this, 'handle_bulk_update' ) );
	}

	public function add_menu_pages() {
		add_menu_page(
			__( 'WP Nexus', 'wp-nexus' ),
			__( 'WP Nexus', 'wp-nexus' ),
			'manage_options',
			'wp-nexus',
			array( $this, 'render_admin_page' ),
			'dashicons-networking',
			30
		);

		// Submenu: Dashboard (same as main)
		add_submenu_page(
			'wp-nexus',
			__( 'Dashboard', 'wp-nexus' ),
			__( 'Dashboard', 'wp-nexus' ),
			'manage_options',
			'wp-nexus',
			array( $this, 'render_admin_page' )
		);

		// Submenu: SEO Audit
		add_submenu_page(
			'wp-nexus',
			__( 'SEO Audit', 'wp-nexus' ),
			__( 'SEO Audit', 'wp-nexus' ),
			'manage_options',
			'wp-nexus-seo-audit',
			array( $this, 'render_seo_audit_page' )
		);

		// Submenu: Settings (placeholder for future)
		add_submenu_page(
			'wp-nexus',
			__( 'Settings', 'wp-nexus' ),
			__( 'Settings', 'wp-nexus' ),
			'manage_options',
			'wp-nexus-settings',
			array( $this, 'render_settings_page' )
		);

		// Submenu: Sitemap
		add_submenu_page(
			'wp-nexus',
			__( 'Sitemap', 'wp-nexus' ),
			__( 'Sitemap', 'wp-nexus' ),
			'manage_options',
			'wp-nexus-sitemap',
			array( $this, 'render_sitemap_page' )
		);
	}

	/**
	 * Render SEO Audit page
	 */
	public function render_seo_audit_page() {
		$missing = $this->get_missing_seo_posts();
		?>
		<div class="wrap wp-nexus-admin">
			<h1><?php _e( 'WP Nexus - SEO Audit', 'wp-nexus' ); ?></h1>

			<?php $this->render_missing_seo_content(); ?>
		</div>
		<?php
	}

	/**
	 * Render Settings page
	 */
	public function render_settings_page() {
		?>
		<div class="wrap wp-nexus-admin">
			<h1><?php _e( 'WP Nexus - Settings', 'wp-nexus' ); ?></h1>

			<div class="wp-nexus-settings">
				<p><?php _e( 'Settings page coming soon.', 'wp-nexus' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Sitemap page
	 */
	public function render_sitemap_page() {
		$hierarchy = $this->get_posts_hierarchy();
		?>
		<div class="wrap wp-nexus-admin">
			<h1><?php _e( 'WP Nexus - Sitemap', 'wp-nexus' ); ?></h1>

			<?php $this->render_sitemap( $hierarchy ); ?>
		</div>
		<?php
	}

	public function enqueue_assets( $hook ) {
		// Load on main plugin page and submenu pages
		$allowed_hooks = array(
			'toplevel_page_wp-nexus',
			'wp-nexus_page_wp-nexus-seo-audit',
			'wp-nexus_page_wp-nexus-settings',
			'wp-nexus_page_wp-nexus-sitemap',
		);

		if ( ! in_array( $hook, $allowed_hooks, true ) ) {
			return;
		}

		wp_enqueue_style(
			'wp-nexus-admin-page',
			WP_NEXUS_URL . 'assets/css/admin-page.css',
			array(),
			WP_NEXUS_VERSION
		);

		wp_enqueue_script(
			'wp-nexus-admin-page',
			WP_NEXUS_URL . 'assets/js/admin-page.js',
			array( 'jquery' ),
			WP_NEXUS_VERSION,
			true
		);
	}

	public function render_admin_page() {
		?>
		<div class="wrap wp-nexus-admin">
			<h1><?php _e( 'WP Nexus', 'wp-nexus' ); ?></h1>

			<?php $this->render_endpoint_info(); ?>
			<?php $this->render_seo_tools(); ?>
		</div>
		<?php
	}

	private function render_seo_tools() {
		?>
		<div class="wp-nexus-seo-tools">
			<h2><?php _e( 'SEO Tools', 'wp-nexus' ); ?></h2>
			<p><?php _e( 'Sync keywords from Yoast SEO or Rank Math to WP Nexus.', 'wp-nexus' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'wp_nexus_bulk_sync', 'wp_nexus_bulk_sync_nonce' ); ?>
				<button type="submit" name="wp_nexus_bulk_sync" class="button button-primary">
					<?php _e( 'Sync Keywords from SEO Plugins', 'wp-nexus' ); ?>
				</button>
			</form>
		</div>

		<div class="wp-nexus-bulk-update">
			<h2><?php _e( 'Bulk Update Nexus Type', 'wp-nexus' ); ?></h2>
			<p><?php _e( 'Select posts and update their Nexus Type in bulk.', 'wp-nexus' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=wp-nexus' ) ); ?>" id="wp-nexus-bulk-form">
				<?php wp_nonce_field( 'wp_nexus_bulk_update', 'wp_nexus_bulk_update_nonce' ); ?>

				<!-- Filter by Post Type -->
				<p>
					<label for="post-type-filter"><strong><?php _e( 'Filter by Post Type:', 'wp-nexus' ); ?></strong></label>
					<select name="post_type_filter" id="post-type-filter" onchange="this.form.submit()">
						<option value=""><?php _e( 'All Post Types', 'wp-nexus' ); ?></option>
						<?php
						$post_types         = get_post_types( array( 'public' => true ), 'objects' );
						$selected_post_type = isset( $_GET['post_type_filter'] ) ? sanitize_text_field( $_GET['post_type_filter'] ) : '';
						foreach ( $post_types as $pt ) :
							?>
							<option value="<?php echo esc_attr( $pt->name ); ?>" <?php selected( $selected_post_type, $pt->name ); ?>><?php echo esc_html( $pt->labels->singular_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<br />

				<?php
				// Get posts with optional filter and pagination
				$post_type_filter = isset( $_GET['post_type_filter'] ) ? sanitize_text_field( $_GET['post_type_filter'] ) : '';
				$post_type_filter = $post_type_filter ? array( $post_type_filter ) : get_post_types( array( 'public' => true ), 'names' );

				$paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
				$per_page = 20;

				// Get total count
				$count_args = array(
					'post_type'      => is_array( $post_type_filter ) ? $post_type_filter : array( $post_type_filter ),
					'post_status'    => 'publish',
					'posts_per_page' => -1,
				);
				$count_query = new WP_Query( $count_args );
				$total_posts = $count_query->found_posts;
				$total_pages = max( 1, ceil( $total_posts / $per_page ) );
				wp_reset_postdata();

				// Get posts for current page
				$all_posts = get_posts(
					array(
						'post_type'      => is_array( $post_type_filter ) ? $post_type_filter : array( $post_type_filter ),
						'post_status'    => 'publish',
						'posts_per_page' => $per_page,
						'paged'          => $paged,
						'orderby'        => 'title',
						'order'          => 'ASC',
					)
				);
				?>

				<!-- Select Posts Table -->
				<h3><?php _e( 'Select Posts', 'wp-nexus' ); ?></h3>
				<p><label><input type="checkbox" id="select-all-posts" onchange="jQuery('.post-checkbox').prop('checked', this.checked)" /> <?php _e( 'Select All', 'wp-nexus' ); ?></label></p>

				<?php if ( empty( $all_posts ) ) : ?>
					<p><?php _e( 'No posts found.', 'wp-nexus' ); ?></p>
				<?php else : ?>
					<table class="widefat fixed striped">
						<thead>
							<tr>
								<th style="width: 30px;"><?php _e( 'Select', 'wp-nexus' ); ?></th>
								<th><?php _e( 'Post Title', 'wp-nexus' ); ?></th>
								<th><?php _e( 'Post Type', 'wp-nexus' ); ?></th>
								<th><?php _e( 'Level', 'wp-nexus' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $all_posts as $post ) : ?>
								<?php
								$current_type = get_post_meta( $post->ID, '_seo_nexus_type', true );
								?>
								<tr>
									<td>
										<input type="checkbox" name="post_ids[]" value="<?php echo esc_attr( $post->ID ); ?>" class="post-checkbox" />
									</td>
									<td><?php echo esc_html( $post->post_title ); ?></td>
									<td><?php echo esc_html( $post->post_type ); ?></td>
									<td>
										<?php if ( $current_type ) : ?>
											<span class="wp-nexus-badge nexus-<?php echo esc_attr( $current_type ); ?>"><?php echo esc_html( $current_type ); ?></span>
										<?php else : ?>
											<span style="color: #999;">—</span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

				<?php if ( $total_pages > 1 ) : ?>
					<div class="tablenav" style="margin-top: 10px;">
						<div class="tablenav-pages">
							<span class="displaying-num">
								<?php
								printf(
									esc_html__( '%d posts total', 'wp-nexus' ),
									$total_posts
								);
								?>
							</span>
							<?php
							$page_base = admin_url( 'admin.php?page=wp-nexus' );
							if ( $post_type_filter && is_array( $post_type_filter ) && count( $post_type_filter ) === 1 ) {
								$page_base = add_query_arg( 'post_type_filter', reset( $post_type_filter ), $page_base );
							}
							$page_links = paginate_links(
								array(
									'base'    => add_query_arg( 'paged', '%#%', $page_base ),
									'format'  => '',
									'prev_text' => __( '&laquo; Previous' ),
									'next_text' => __( 'Next &raquo;' ),
									'total'   => $total_pages,
									'current' => $paged,
								)
							);
							if ( $page_links ) {
								echo '<span class="pagination-links">' . $page_links . '</span>';
							}
							?>
						</div>
					</div>
				<?php endif; ?>

				<br />

				<!-- Nexus Type Selection (below table) -->
				<p>
					<label for="nexus-type-select"><strong><?php _e( 'Set Nexus Type to:', 'wp-nexus' ); ?></strong></label>
					<select name="nexus_type" id="nexus-type-select" required>
						<option value=""><?php _e( '— Select Type —', 'wp-nexus' ); ?></option>
						<option value="pillar"><?php _e( 'Pillar', 'wp-nexus' ); ?></option>
						<option value="sub-pillar"><?php _e( 'Sub-Pillar', 'wp-nexus' ); ?></option>
						<option value="cluster"><?php _e( 'Cluster', 'wp-nexus' ); ?></option>
					</select>
				</p>
				<p>
					<button type="submit" name="wp_nexus_bulk_update" value="1" class="button button-primary">
						<?php _e( 'Update Selected Posts', 'wp-nexus' ); ?>
					</button>
				</p>
			</form>
		</div>

		<div class="wp-nexus-docs">
			<h2><?php _e( 'Documentation', 'wp-nexus' ); ?></h2>

			<div class="wp-nexus-docs-section">
				<h3><?php _e( 'Meta Box', 'wp-nexus' ); ?></h3>
				<p><?php _e( 'On each post edit screen, you can set:', 'wp-nexus' ); ?></p>
				<ul>
					<li><strong><?php _e( 'Nexus Type', 'wp-nexus' ); ?>:</strong> <?php _e( 'pillar, sub-pillar, or cluster', 'wp-nexus' ); ?></li>
					<li><strong><?php _e( 'Keyword', 'wp-nexus' ); ?>:</strong> <?php _e( 'Main keyword for this content', 'wp-nexus' ); ?></li>
					<li><strong><?php _e( 'Parent Keyword', 'wp-nexus' ); ?>:</strong> <?php _e( 'Link to parent pillar keyword', 'wp-nexus' ); ?></li>
				</ul>
			</div>

			<div class="wp-nexus-docs-section">
				<h3><?php _e( 'REST API', 'wp-nexus' ); ?></h3>
				<p><?php _e( 'Access your nexus data programmatically:', 'wp-nexus' ); ?></p>
				<pre><code><?php echo esc_html( 'GET /wp-json/seo-nexus/v1/links' . "\n" .
'Header: X-API-Key: ' . WP_NEXUS_API_KEY ); ?></code></pre>
				<p><strong><?php _e( 'Query Parameters:', 'wp-nexus' ); ?></strong></p>
				<ul>
					<li><code>type</code>: <?php _e( 'Filter by type: pillar, sub-pillar, cluster (supports multiple values)', 'wp-nexus' ); ?></li>
				</ul>
				<p><strong><?php _e( 'Examples:', 'wp-nexus' ); ?></strong></p>
				<pre><code><?php echo esc_html(
'# All links
/wp-json/seo-nexus/v1/links

# Filter by type
/wp-json/seo-nexus/v1/links?type=pillar
/wp-json/seo-nexus/v1/links?type=pillar,sub-pillar' ); ?></code></pre>
			</div>

			<div class="wp-nexus-docs-section">
				<h3><?php _e( 'SEO Integration', 'wp-nexus' ); ?></h3>
				<p><?php _e( 'WP Nexus integrates with Yoast SEO and Rank Math:', 'wp-nexus' ); ?></p>
				<ul>
					<li><?php _e( 'Auto-syncs focus keywords from Yoast/Rank Math to WP Nexus', 'wp-nexus' ); ?></li>
					<li><?php _e( 'Displays SEO scores in the sitemap', 'wp-nexus' ); ?></li>
					<li><?php _e( 'Shows improvements needed (via tooltip)', 'wp-nexus' ); ?></li>
				</ul>
			</div>
		</div>
	<?php
	}

	public function handle_bulk_sync() {
		if ( ! isset( $_POST['wp_nexus_bulk_sync'] ) ) {
			return;
		}

		if ( ! isset( $_POST['wp_nexus_bulk_sync_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['wp_nexus_bulk_sync_nonce'], 'wp_nexus_bulk_sync' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		require_once WP_NEXUS_PATH . 'includes/class-meta-box.php';
		$synced = WP_Nexus_Meta_Box::bulk_sync_keywords();

		add_action(
			'admin_notices',
			function() use ( $synced ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php printf( esc_html__( 'WP Nexus: Synced %d posts with keywords from SEO plugins.', 'wp-nexus' ), esc_html( $synced ) ); ?></p>
				</div>
				<?php
			}
		);
	}

	/**
	 * Handle bulk update nexus type
	 */
	public function handle_bulk_update() {
		if ( ! isset( $_POST['wp_nexus_bulk_update'] ) ) {
			return;
		}

		if ( ! isset( $_POST['wp_nexus_bulk_update_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['wp_nexus_bulk_update_nonce'], 'wp_nexus_bulk_update' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$nexus_type = isset( $_POST['nexus_type'] ) ? sanitize_text_field( $_POST['nexus_type'] ) : '';
		$post_ids = isset( $_POST['post_ids'] ) ? array_map( 'intval', $_POST['post_ids'] ) : array();

		// Debug: log values
		error_log( 'WP Nexus Bulk Update - Type: ' . print_r( $nexus_type, true ) );
		error_log( 'WP Nexus Bulk Update - IDs: ' . print_r( $post_ids, true ) );

		if ( empty( $nexus_type ) || empty( $post_ids ) ) {
			add_action(
				'admin_notices',
				function() {
					?>
					<div class="notice notice-error is-dismissible">
						<p><?php _e( 'WP Nexus: Please select a Nexus Type and at least one post.', 'wp-nexus' ); ?></p>
					</div>
					<?php
				}
			);
			return;
		}

		$updated = 0;
		foreach ( $post_ids as $post_id ) {
			if ( current_user_can( 'edit_post', $post_id ) ) {
				update_post_meta( $post_id, '_seo_nexus_type', $nexus_type );
				++$updated;
			}
		}

		add_action(
			'admin_notices',
			function() use ( $updated, $nexus_type ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php printf( esc_html__( 'WP Nexus: Updated %d posts to "%s".', 'wp-nexus' ), esc_html( $updated ), esc_html( $nexus_type ) ); ?></p>
				</div>
				<?php
			}
		);
	}

	/**
	 * Render SEO score badge for a node
	 */
	private function render_seo_score_badge( $seo ) {
		if ( null === $seo['score'] ) {
			return;
		}

		$score = $seo['score'];
		if ( $score >= 80 ) {
			$class = 'good';
		} elseif ( $score >= 50 ) {
			$class = 'ok';
		} else {
			$class = 'poor';
		}

		$tooltip = '';
		if ( ! empty( $seo['issues'] ) ) {
			$tooltip = ' title="' . esc_attr( implode( "\n", $seo['issues'] ) ) . '"';
		}
		?>
		<span class="seo-score <?php echo esc_attr( $class ); ?>"<?php echo $tooltip; ?>>
			<?php echo esc_html( $score ); ?>
			<?php if ( ! empty( $seo['issues'] ) ) : ?>
				<span class="seo-issues-icon">⚠️</span>
			<?php endif; ?>
		</span>
		<?php
	}

	private function render_endpoint_info() {
		$api_url = rest_url( 'seo-nexus/v1/links' );
		$api_key = WP_NEXUS_API_KEY;
		?>
		<div class="wp-nexus-endpoint-info">
			<h2><?php _e( 'API Endpoint', 'wp-nexus' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><?php _e( 'Endpoint URL', 'wp-nexus' ); ?></th>
					<td>
						<code id="api-url"><?php echo esc_url( $api_url ); ?></code>
						<button class="button copy-btn" data-copy="<?php echo esc_url( $api_url ); ?>">
							<?php _e( 'Copy', 'wp-nexus' ); ?>
						</button>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'API Key', 'wp-nexus' ); ?></th>
					<td>
						<code id="api-key"><?php echo esc_html( $api_key ); ?></code>
						<button class="button copy-btn" data-copy="<?php echo esc_html( $api_key ); ?>">
							<?php _e( 'Copy', 'wp-nexus' ); ?>
						</button>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	private function render_sitemap( $hierarchy ) {
		?>
		<div class="wp-nexus-sitemap">
			<div class="sitemap-header">
				<h2><?php _e( 'Content Sitemap', 'wp-nexus' ); ?></h2>
				<div class="sitemap-actions">
					<button class="button expand-all-btn"><?php _e( 'Expand All', 'wp-nexus' ); ?></button>
					<button class="button collapse-all-btn"><?php _e( 'Collapse All', 'wp-nexus' ); ?></button>
				</div>
			</div>
			<?php if ( empty( $hierarchy['pillars'] ) ) : ?>
				<p><?php _e( 'No posts with nexus type found. Add nexus type to your posts using the WP Nexus SEO meta box.', 'wp-nexus' ); ?></p>
			<?php else : ?>
				<div class="sitemap-tree">
					<?php foreach ( $hierarchy['pillars'] as $pillar ) : ?>
						<?php
						$sp_count = count( $pillar['sub_pillars'] ?? array() );
						$seo     = $this->get_seo_score( $pillar['id'] );
						?>
						<div class="sitemap-node pillar" data-type="pillar" data-id="<?php echo esc_attr( $pillar['id'] ); ?>">
							<div class="node-header">
								<span class="toggle-icon <?php echo $sp_count > 0 ? 'expandable' : ''; ?>"><?php echo $sp_count > 0 ? '&#9658;' : ''; ?></span>
								<a href="<?php echo esc_url( $pillar['edit_url'] ); ?>" class="node-link" target="_blank">
									<span class="node-title"><?php echo esc_html( $pillar['title'] ); ?></span>
									<span class="node-keyword"><?php echo esc_html( $pillar['keyword'] ); ?></span>
								</a>
								<?php $this->render_seo_score_badge( $seo ); ?>
								<span class="node-count"><?php echo $sp_count; ?> sub-pillars</span>
							</div>
							<?php if ( $sp_count > 0 ) : ?>
								<div class="sitemap-children" style="display: none;">
									<?php foreach ( $pillar['sub_pillars'] as $sp ) : ?>
										<?php
										$cluster_key   = $sp['keyword'];
										$cluster_count = count( $hierarchy['clusters_by_sp'][ $cluster_key ] ?? array() );
										$seo_sp       = $this->get_seo_score( $sp['id'] );
										?>
										<div class="sitemap-node sub-pillar" data-type="sub-pillar" data-id="<?php echo esc_attr( $sp['id'] ); ?>">
											<div class="node-header">
												<span class="toggle-icon <?php echo $cluster_count > 0 ? 'expandable' : ''; ?>"><?php echo $cluster_count > 0 ? '&#9658;' : ''; ?></span>
												<a href="<?php echo esc_url( $sp['edit_url'] ); ?>" class="node-link" target="_blank">
													<span class="node-title"><?php echo esc_html( $sp['title'] ); ?></span>
													<span class="node-keyword"><?php echo esc_html( $sp['keyword'] ); ?></span>
												</a>
												<?php $this->render_seo_score_badge( $seo_sp ); ?>
												<span class="node-count"><?php echo $cluster_count; ?> clusters</span>
											</div>
											<?php if ( $cluster_count > 0 ) : ?>
												<div class="sitemap-children" style="display: none;">
													<?php foreach ( $hierarchy['clusters_by_sp'][ $cluster_key ] as $cluster ) : ?>
														<?php $seo_cluster = $this->get_seo_score( $cluster['id'] ); ?>
														<div class="sitemap-node cluster" data-type="cluster" data-id="<?php echo esc_attr( $cluster['id'] ); ?>">
															<div class="node-header">
																<span class="toggle-icon"></span>
																<a href="<?php echo esc_url( $cluster['edit_url'] ); ?>" class="node-link" target="_blank">
																	<span class="node-title"><?php echo esc_html( $cluster['title'] ); ?></span>
																	<span class="node-keyword"><?php echo esc_html( $cluster['keyword'] ); ?></span>
																</a>
																<?php $this->render_seo_score_badge( $seo_cluster ); ?>
															</div>
														</div>
													<?php endforeach; ?>
												</div>
											<?php endif; ?>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Get SEO score from Yoast or Rank Math
	 */
	private function get_seo_score( $post_id ) {
		$result = array(
			'score'  => null,
			'source' => null,
			'issues' => array(),
		);

		// Check Rank Math first
		$score = get_post_meta( $post_id, 'rank_math_seo_score', true );
		if ( '' !== $score && false !== $score ) {
			$result['score']  = intval( $score );
			$result['source'] = 'rankmath';

			// Get validation issues
			$validation = get_post_meta( $post_id, 'rank_math_validation_notice', true );
			if ( is_array( $validation ) && ! empty( $validation ) ) {
				foreach ( $validation as $item ) {
					if ( isset( $item['message'] ) ) {
						$result['issues'][] = $item['message'];
					}
				}
				$result['issues'] = array_slice( $result['issues'], 0, 3 );
			}
			return $result;
		}

		// Check Yoast
		$score = get_post_meta( $post_id, '_yoast_wpseo_score', true );
		if ( '' !== $score && false !== $score && 'na' !== $score ) {
			$result['score']  = intval( $score );
			$result['source'] = 'yoast';

			// Get results/issues
			$results = get_post_meta( $post_id, '_yoast_wpseo_results', true );
			if ( is_array( $results ) && ! empty( $results ) ) {
				foreach ( $results as $issue ) {
					if ( isset( $issue['msg'] ) ) {
						$result['issues'][] = $issue['msg'];
					}
				}
				$result['issues'] = array_slice( $result['issues'], 0, 3 );
			}
			return $result;
		}

		return $result;
	}

	private function get_posts_hierarchy() {
		$post_types = get_post_types( array( 'public' => true ), 'names' );

		$args = array(
			'post_type'      => array_values( $post_types ),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => '_seo_nexus_type',
					'value'   => array( 'pillar', 'sub-pillar', 'cluster' ),
					'compare' => 'IN',
				),
			),
		);

		$query  = new WP_Query( $args );
		$posts  = array();
		$pillars      = array();
		$sub_pillars  = array();
		$clusters     = array();

		foreach ( $query->posts as $post ) {
			$type           = get_post_meta( $post->ID, '_seo_nexus_type', true );
			$keyword        = get_post_meta( $post->ID, '_seo_nexus_keyword', true );
			$parent_keyword = get_post_meta( $post->ID, '_seo_nexus_parent_keyword', true );

			$post_data = array(
				'id'            => $post->ID,
				'title'         => $post->post_title,
				'type'          => $type,
				'keyword'       => $keyword,
				'parent_keyword'=> $parent_keyword,
				'edit_url'      => get_edit_post_link( $post->ID ),
			);

			if ( 'pillar' === $type ) {
				$pillars[] = $post_data;
			} elseif ( 'sub-pillar' === $type ) {
				$sub_pillars[] = $post_data;
			} else {
				$clusters[] = $post_data;
			}
		}

		// Build hierarchy
		$hierarchy = array(
			'pillars'        => array(),
			'clusters_by_sp' => array(),
		);

		// Assign sub-pillars to pillars
		foreach ( $sub_pillars as $sp ) {
			$found = false;
			foreach ( $pillars as &$p ) {
				if ( $sp['parent_keyword'] === $p['keyword'] ) {
					if ( ! isset( $p['sub_pillars'] ) ) {
						$p['sub_pillars'] = array();
					}
					$p['sub_pillars'][] = $sp;
					$found = true;
					break;
				}
			}
			if ( ! $found ) {
				// Orphan sub-pillar - add to first pillar or create placeholder
				$hierarchy['orphans']['sub_pillars'][] = $sp;
			}
		}

		// Assign clusters to sub-pillars
		foreach ( $clusters as $cluster ) {
			foreach ( $sub_pillars as $sp ) {
				if ( $cluster['parent_keyword'] === $sp['keyword'] ) {
					if ( ! isset( $hierarchy['clusters_by_sp'][ $sp['keyword'] ] ) ) {
						$hierarchy['clusters_by_sp'][ $sp['keyword'] ] = array();
					}
					$hierarchy['clusters_by_sp'][ $sp['keyword'] ][] = $cluster;
					break;
				}
			}
		}

		$hierarchy['pillars'] = $pillars;

		wp_reset_postdata();

		return $hierarchy;
	}

	/**
	 * Get posts with incomplete SEO (RankMath only)
	 */
	private function get_missing_seo_posts( $paged = 1, $per_page = 20, $issue_filter = '' ) {
		$post_types = get_post_types( array( 'public' => true ), 'names' );

		$args = array(
			'post_type'      => array_values( $post_types ),
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $paged,
		);

		$query  = new WP_Query( $args );
		$missing = array();

		foreach ( $query->posts as $post ) {
			$issues = array();

			// 1. Check if post has tags
			$tags = get_the_terms( $post->ID, 'post_tag' );
			if ( empty( $tags ) || is_wp_error( $tags ) ) {
				$issues['no_tags'] = __( 'No Tags', 'wp-nexus' );
			}

			// 2. Check Rank Math focus keyword
			$rank_math_keyword = get_post_meta( $post->ID, 'rank_math_focus_keyword', true );
			if ( empty( $rank_math_keyword ) ) {
				$issues['no_rankmath_keyword'] = __( 'No RankMath Keyword', 'wp-nexus' );
			}

			// 3. Check Rank Math SEO score
			$rank_math_score = get_post_meta( $post->ID, 'rank_math_seo_score', true );
			if ( ! empty( $rank_math_score ) && intval( $rank_math_score ) < 75 ) {
				$issues['low_rankmath_score'] = sprintf(
					__( 'Low RankMath Score: %d', 'wp-nexus' ),
					intval( $rank_math_score )
				);
			}

			// 4. Check if post has internal links
			$has_internal_link = $this->check_internal_links( $post->post_content );
			if ( ! $has_internal_link ) {
				$issues['no_internal_links'] = __( 'No Internal Links', 'wp-nexus' );
			}

			// 5. Check Rank Math SEO title
			$rank_math_title = get_post_meta( $post->ID, 'rank_math_title', true );
			if ( empty( $rank_math_title ) ) {
				$issues['no_seo_title'] = __( 'No SEO Title', 'wp-nexus' );
			}

			// 6. Check Rank Math indexing
			$rank_math_index = get_post_meta( $post->ID, 'rank_math_advanced_robots', true );
			if ( empty( $rank_math_index ) || 'on' !== $rank_math_index ) {
				$issues['not_indexed'] = __( 'Not Indexed', 'wp-nexus' );
			}

			// Only add if has at least one issue
			if ( ! empty( $issues ) ) {
				// Apply filter: only show posts with specific issue
				if ( $issue_filter && ! isset( $issues[ $issue_filter ] ) ) {
					continue;
				}
				$missing[] = array(
					'id'       => $post->ID,
					'title'    => $post->post_title,
					'edit_url' => get_edit_post_link( $post->ID ),
					'issues'   => $issues,
				);
			}
		}

		// Get total count for pagination
		if ( $issue_filter ) {
			// With filter: accurate count
			$all_args = $args;
			$all_args['posts_per_page'] = -1;
			$all_query = new WP_Query( $all_args );
			$all_missing = 0;
			foreach ( $all_query->posts as $p ) {
				$iss = array();
				$tg = get_the_terms( $p->ID, 'post_tag' );
				if ( empty( $tg ) || is_wp_error( $tg ) ) { $iss['no_tags'] = 1; }
				$rk = get_post_meta( $p->ID, 'rank_math_focus_keyword', true );
				if ( empty( $rk ) ) { $iss['no_rankmath_keyword'] = 1; }
				$rs = get_post_meta( $p->ID, 'rank_math_seo_score', true );
				if ( ! empty( $rs ) && intval( $rs ) < 75 ) { $iss['low_rankmath_score'] = 1; }
				$il = $this->check_internal_links( $p->post_content );
				if ( ! $il ) { $iss['no_internal_links'] = 1; }
				$rt = get_post_meta( $p->ID, 'rank_math_title', true );
				if ( empty( $rt ) ) { $iss['no_seo_title'] = 1; }
				$ri = get_post_meta( $p->ID, 'rank_math_advanced_robots', true );
				if ( empty( $ri ) || 'on' !== $ri ) { $iss['not_indexed'] = 1; }
				if ( isset( $iss[ $issue_filter ] ) ) { ++$all_missing; }
			}
			$total_posts = $all_missing;
			$total_pages = max( 1, ceil( $total_posts / $per_page ) );
			wp_reset_postdata();
		} else {
			// Without filter
			$total_posts = count( $missing );
			$total_pages = max( 1, ceil( $total_posts / $per_page ) );
		}

		return array(
			'posts'       => $missing,
			'total'       => $total_posts,
			'total_pages' => max( 1, $total_pages ),
			'paged'       => $paged,
		);
	}

	/**
	 * Check if post content has internal links
	 */
	private function check_internal_links( $content ) {
		$site_url = get_site_url();

		// Extract all links from content
		preg_match_all( '/<a\s+[^>]*href=["\']([^"\']+)["\']/i', $content, $matches );

		if ( empty( $matches[1] ) ) {
			return false;
		}

		foreach ( $matches[1] as $href ) {
			// Check if it's an internal link
			if ( strpos( $href, $site_url ) !== false || strpos( $href, '/' ) === 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Render missing SEO content section
	 */
	private function render_missing_seo_content() {
		$paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
		$issue_filter = isset( $_GET['issue_filter'] ) ? sanitize_text_field( $_GET['issue_filter'] ) : '';

		$result = $this->get_missing_seo_posts( $paged, 20, $issue_filter );
		$missing = $result['posts'];
		$total = $result['total'];
		$total_pages = $result['total_pages'];
		$current_page = $result['paged'];

		$page_base = admin_url( 'admin.php?page=' . $_GET['page'] );

		// Issue filter options (RankMath only)
		$issue_filters = array(
			''                      => __( 'All Issues', 'wp-nexus' ),
			'no_tags'               => __( 'No Tags', 'wp-nexus' ),
			'no_rankmath_keyword'   => __( 'No RankMath Keyword', 'wp-nexus' ),
			'low_rankmath_score'    => __( 'Low RankMath Score', 'wp-nexus' ),
			'no_internal_links'     => __( 'No Internal Links', 'wp-nexus' ),
			'no_seo_title'          => __( 'No SEO Title', 'wp-nexus' ),
			'not_indexed'           => __( 'Not Indexed', 'wp-nexus' ),
		);
		?>
		<div class="wp-nexus-missing-content">
			<h2><?php _e( 'Incomplete SEO Posts', 'wp-nexus' ); ?></h2>
			<p><?php _e( 'Posts that need attention to improve SEO performance.', 'wp-nexus' ); ?></p>

			<!-- Filter by Issue -->
			<form method="get" action="<?php echo admin_url( 'admin.php' ); ?>">
				<input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>" />
				<select name="issue_filter">
					<?php foreach ( $issue_filters as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $issue_filter, $key ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<button type="submit" class="button"><?php _e( 'Filter', 'wp-nexus' ); ?></button>
				<?php if ( $issue_filter ) : ?>
					<a href="<?php echo admin_url( 'admin.php?page=' . esc_attr( $_GET['page'] ) ); ?>" class="button button-link"><?php _e( 'Clear Filter', 'wp-nexus' ); ?></a>
				<?php endif; ?>
			</form>
			<br />

			<?php if ( empty( $missing ) ) : ?>
				<p class="description"><?php _e( 'No posts found with this issue.', 'wp-nexus' ); ?></p>
			<?php else : ?>
				<table class="widefat fixed striped">
					<thead>
						<tr>
							<th><?php _e( 'Post', 'wp-nexus' ); ?></th>
							<th><?php _e( 'Issues', 'wp-nexus' ); ?></th>
							<th><?php _e( 'Action', 'wp-nexus' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $missing as $post ) : ?>
							<tr>
								<td>
									<a href="<?php echo esc_url( $post['edit_url'] ); ?>" target="_blank">
										<?php echo esc_html( $post['title'] ); ?>
									</a>
								</td>
								<td>
									<?php foreach ( $post['issues'] as $issue_key => $issue_label ) : ?>
										<span class="wp-nexus-badge wp-nexus-badge-<?php echo esc_attr( $issue_key ); ?>">
											<?php echo esc_html( $issue_label ); ?>
										</span>
									<?php endforeach; ?>
								</td>
								<td>
									<a href="<?php echo esc_url( $post['edit_url'] ); ?>" class="button button-small" target="_blank">
										<?php _e( 'Edit', 'wp-nexus' ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<?php if ( $total_pages > 1 ) : ?>
					<div class="tablenav">
						<div class="tablenav-pages">
							<span class="displaying-num">
								<?php
								printf(
									esc_html__( '%d posts', 'wp-nexus' ),
									$total
								);
								?>
							</span>
							<?php
							$page_links = paginate_links(
								array(
									'base'    => add_query_arg( 'paged', '%#%', $page_base ),
									'format'  => '',
									'prev_text' => __( '&laquo; Previous' ),
									'next_text' => __( 'Next &raquo;' ),
									'total'   => $total_pages,
									'current' => $current_page,
								)
							);
							if ( $page_links ) {
								echo '<span class="pagination-links">' . $page_links . '</span>';
							}
							?>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
	}
}
