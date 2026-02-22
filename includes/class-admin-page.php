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
	}

	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_wp-nexus' !== $hook ) {
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
		$hierarchy = $this->get_posts_hierarchy();
		?>
		<div class="wrap wp-nexus-admin">
			<h1><?php _e( 'WP Nexus', 'wp-nexus' ); ?></h1>

			<?php $this->render_endpoint_info(); ?>
			<?php $this->render_seo_tools(); ?>
			<?php $this->render_sitemap( $hierarchy ); ?>
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
}
