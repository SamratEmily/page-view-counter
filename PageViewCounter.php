<?php
/**
 * Page View Counter Plugin Main Class
 *
 * @package PageViewCounter
 * @author  SamratEmily
 * @since   1.1.0
 */

/**
 * PageViewCounter Main Class
 */
class PageViewCounter {

	/**
	 * Constructor for the PageViewCounter class.
	 * Initializes the plugin by registering hooks and actions.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'wp', array( $this, 'track_page_views' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_view_counter_meta_box' ) );
		add_filter( 'manage_posts_columns', array( $this, 'add_views_column' ) );
		add_filter( 'manage_pages_columns', array( $this, 'add_views_column' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'display_views_column' ), 10, 2 );
		add_action( 'manage_pages_custom_column', array( $this, 'display_views_column' ), 10, 2 );
		add_action( 'wp_ajax_pvc_reset_views', array( $this, 'ajax_reset_views' ) );
		add_action( 'wp_ajax_pvc_reset_single_view', array( $this, 'ajax_reset_single_view' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}

	/**
	 * Enqueue styles and scripts for the plugin.
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'page-view-counter-style', plugin_dir_url( __FILE__ ) . 'css/style.css', array(), '1.1.0' );
		wp_enqueue_script( 'page-view-counter-script', plugin_dir_url( __FILE__ ) . 'js/script.js', array( 'jquery' ), '1.1.0', true );
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		// Load on post edit screens and our admin page.
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php', 'toplevel_page_page-view-counter' ), true ) ) {
			return;
		}

		// Enqueue CSS for admin pages.
		wp_enqueue_style( 'page-view-counter-admin-style', plugin_dir_url( __FILE__ ) . 'css/style.css', array(), '1.1.0' );

		// Enqueue JavaScript for admin pages.
		wp_enqueue_script( 'page-view-counter-admin-script', plugin_dir_url( __FILE__ ) . 'js/script.js', array( 'jquery' ), '1.1.0', true );

		// Localize script data for AJAX calls.
		wp_localize_script(
			'page-view-counter-admin-script',
			'pvcAdminData',
			array(
				'ajaxurl'          => admin_url( 'admin-ajax.php' ),
				'resetSingleNonce' => wp_create_nonce( 'pvc_reset_single_view' ),
			)
		);
	}

	/**
	 * Track page views for posts and pages.
	 * Only counts views for single posts/pages, excludes admin users and bots.
	 */
	public function track_page_views() {
		// Only track on single posts/pages.
		if ( ! is_single() && ! is_page() ) {
			return;
		}

		// Don't track admin users.
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}

		// Don't track bots (basic bot detection).
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$bots       = array( 'bot', 'crawl', 'spider', 'slurp', 'facebook', 'twitter' );
		foreach ( $bots as $bot ) {
			if ( stripos( $user_agent, $bot ) !== false ) {
				return;
			}
		}

		global $post;
		if ( ! $post ) {
			return;
		}

		$post_id       = $post->ID;
		$current_views = get_post_meta( $post_id, '_pvc_view_count', true );
		$current_views = $current_views ? intval( $current_views ) : 0;

		// Increment view count.
		update_post_meta( $post_id, '_pvc_view_count', $current_views + 1 );

		// Store last viewed date.
		update_post_meta( $post_id, '_pvc_last_viewed', current_time( 'mysql' ) );
	}

	/**
	 * Add meta box to display view count in admin.
	 */
	public function add_view_counter_meta_box() {
		$screens = array( 'post', 'page' );
		foreach ( $screens as $screen ) {
			add_meta_box(
				'pvc-view-counter',
				'Page Views',
				array( $this, 'view_counter_meta_box_callback' ),
				$screen,
				'side',
				'default'
			);
		}
	}

	/**
	 * Meta box callback to display view statistics.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function view_counter_meta_box_callback( $post ) {
		$view_count  = get_post_meta( $post->ID, '_pvc_view_count', true );
		$last_viewed = get_post_meta( $post->ID, '_pvc_last_viewed', true );

		$view_count = $view_count ? intval( $view_count ) : 0;

		echo '<div style="padding: 10px 0;">';
		echo '<p><strong>Total Views:</strong> ' . number_format( $view_count ) . '</p>';

		if ( $last_viewed ) {
			$formatted_date = gmdate( 'M j, Y g:i A', strtotime( $last_viewed ) );
			echo '<p><strong>Last Viewed:</strong> ' . esc_html( $formatted_date ) . '</p>';
		} else {
			echo '<p><strong>Last Viewed:</strong> Never</p>';
		}

		if ( $view_count > 0 ) {
			echo '<p style="margin-top: 15px;">';
			echo '<button type="button" class="button" onclick="pvcResetViews(' . esc_attr( $post->ID ) . ')">Reset Views</button>';
			echo '</p>';
		}
		echo '</div>';

		// Add nonce for security.
		wp_nonce_field( 'pvc_reset_views', 'pvc_reset_views_nonce' );
	}

	/**
	 * Add views column to posts/pages list.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_views_column( $columns ) {
		$columns['pvc_views'] = 'Views';
		return $columns;
	}

	/**
	 * Display view count in the custom column.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public function display_views_column( $column, $post_id ) {
		if ( 'pvc_views' === $column ) {
			$view_count = get_post_meta( $post_id, '_pvc_view_count', true );
			$view_count = $view_count ? intval( $view_count ) : 0;
			echo number_format( $view_count );
		}
	}

	/**
	 * AJAX handler to reset view count.
	 */
	public function ajax_reset_views() {
		// Check nonce for security.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'pvc_reset_views' ) ) {
			wp_die(
				wp_json_encode(
					array(
						'success' => false,
						'data'    => 'Security check failed',
					)
				)
			);
		}

		// Check user capabilities.
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die(
				wp_json_encode(
					array(
						'success' => false,
						'data'    => 'Insufficient permissions',
					)
				)
			);
		}

		$post_id = intval( $_POST['post_id'] ?? 0 );

		if ( ! $post_id ) {
			wp_die(
				wp_json_encode(
					array(
						'success' => false,
						'data'    => 'Invalid post ID',
					)
				)
			);
		}

		// Reset the view count.
		update_post_meta( $post_id, '_pvc_view_count', 0 );
		delete_post_meta( $post_id, '_pvc_last_viewed' );

		wp_die(
			wp_json_encode(
				array(
					'success' => true,
					'data'    => 'Views reset successfully',
				)
			)
		);
	}

	/**
	 * AJAX handler to reset single view count from admin page.
	 */
	public function ajax_reset_single_view() {
		// Check nonce for security.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'pvc_reset_single_view' ) ) {
			wp_die(
				wp_json_encode(
					array(
						'success' => false,
						'data'    => 'Security check failed',
					)
				)
			);
		}

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				wp_json_encode(
					array(
						'success' => false,
						'data'    => 'Insufficient permissions',
					)
				)
			);
		}

		$post_id = intval( $_POST['post_id'] ?? 0 );

		if ( ! $post_id ) {
			wp_die(
				wp_json_encode(
					array(
						'success' => false,
						'data'    => 'Invalid post ID',
					)
				)
			);
		}

		// Reset the view count.
		update_post_meta( $post_id, '_pvc_view_count', 0 );
		delete_post_meta( $post_id, '_pvc_last_viewed' );

		wp_die(
			wp_json_encode(
				array(
					'success' => true,
					'data'    => 'Views reset successfully',
				)
			)
		);
	}

	/**
	 * Get view count for a specific post (helper function for themes).
	 *
	 * @param int $post_id Post ID.
	 * @return int View count.
	 */
	public static function get_view_count( $post_id = null ) {
		if ( ! $post_id ) {
			global $post;
			$post_id = $post ? $post->ID : 0;
		}

		if ( ! $post_id ) {
			return 0;
		}

		$view_count = get_post_meta( $post_id, '_pvc_view_count', true );
		return $view_count ? intval( $view_count ) : 0;
	}

	/**
	 * Display view count (helper function for themes).
	 *
	 * @param int    $post_id Post ID.
	 * @param string $text    Text to display before count.
	 */
	public static function display_view_count( $post_id = null, $text = 'Views: ' ) {
		$count = self::get_view_count( $post_id );
		echo esc_html( $text ) . number_format( $count );
	}

	/**
	 * Add admin menu for page views.
	 */
	public function add_admin_menu() {
		add_menu_page(
			'Page View Counter',
			'Page Views',
			'manage_options',
			'page-view-counter',
			array( $this, 'admin_page_views' ),
			'dashicons-chart-line',
			30
		);
	}

	/**
	 * Admin page to display page view statistics.
	 */
	public function admin_page_views() {
		// Handle bulk actions.
		if ( isset( $_POST['bulk_action'] ) && 'reset' === $_POST['bulk_action'] && isset( $_POST['post_ids'] ) && ! empty( $_POST['post_ids'] ) ) {
			if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'bulk_reset_views' ) ) {
				$post_ids    = array_map( 'intval', $_POST['post_ids'] );
				$reset_count = 0;
				foreach ( $post_ids as $post_id ) {
					if ( $post_id > 0 ) {
						update_post_meta( $post_id, '_pvc_view_count', 0 );
						delete_post_meta( $post_id, '_pvc_last_viewed' );
						++$reset_count;
					}
				}
				echo '<div class="notice notice-success"><p>' . sprintf( '%d post(s) have been reset successfully.', esc_html( $reset_count ) ) . '</p></div>';
			} else {
				echo '<div class="notice notice-error"><p>Security check failed. Please try again.</p></div>';
			}
		}

		// Get sorting parameters.
		$orderby   = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'views';
		$order     = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'desc';
		$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : 'all';
		$per_page  = 20;
		$paged     = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;

		// Get posts with view counts.
		$posts_data  = $this->get_posts_with_views( $orderby, $order, $post_type, $per_page, $paged );
		$total_posts = $this->get_total_posts_with_views( $post_type );
		$total_pages = ceil( $total_posts / $per_page );

		?>
		<div class="wrap">
			<h1>Page View Statistics</h1>

			<div class="pvc-stats-summary">
				<?php $this->display_view_summary(); ?>
			</div>

			<form method="get" class="pvc-filters">
				<input type="hidden" name="page" value="page-view-counter">
				<select name="post_type">
					<option value="all" <?php selected( $post_type, 'all' ); ?>>All Types</option>
					<option value="post" <?php selected( $post_type, 'post' ); ?>>Posts</option>
					<option value="page" <?php selected( $post_type, 'page' ); ?>>Pages</option>
				</select>
				<input type="submit" class="button" value="Filter">
			</form>

			<form method="post" id="pvc-views-form">
				<?php wp_nonce_field( 'bulk_reset_views' ); ?>

				<div class="tablenav top">
					<div class="alignleft actions bulkactions">
						<select name="bulk_action" id="bulk-action-selector-top">
							<option value="">Bulk Actions</option>
							<option value="reset">Reset Views</option>
						</select>
						<input type="submit" class="button action" value="Apply" onclick="return pvcBulkAction();">
					</div>
				</div>

				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<td class="manage-column column-cb check-column">
								<input type="checkbox" id="cb-select-all">
							</td>
							<th class="manage-column column-title">
								<a href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'title', 'order' => ( 'title' === $orderby && 'asc' === $order ) ? 'desc' : 'asc' ) ) ); ?>">
									Title <?php if ( 'title' === $orderby ) echo ( 'asc' === $order ) ? '↑' : '↓'; ?>
								</a>
							</th>
							<th class="manage-column column-type">Type</th>
							<th class="manage-column column-views">
								<a href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'views', 'order' => ( 'views' === $orderby && 'desc' === $order ) ? 'asc' : 'desc' ) ) ); ?>">
									Views <?php if ( 'views' === $orderby ) echo ( 'desc' === $order ) ? '↓' : '↑'; ?>
								</a>
							</th>
							<th class="manage-column column-last-viewed">
								<a href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'last_viewed', 'order' => ( 'last_viewed' === $orderby && 'desc' === $order ) ? 'asc' : 'desc' ) ) ); ?>">
									Last Viewed <?php if ( 'last_viewed' === $orderby ) echo ( 'desc' === $order ) ? '↓' : '↑'; ?>
								</a>
							</th>
							<th class="manage-column column-actions">Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $posts_data ) ) : ?>
							<tr>
								<td colspan="6">No posts with view data found.</td>
							</tr>
						<?php else : ?>
							<?php foreach ( $posts_data as $post_data ) : ?>
								<tr>
									<th scope="row" class="check-column">
										<input type="checkbox" name="post_ids[]" value="<?php echo esc_attr( $post_data['ID'] ); ?>">
									</th>
									<td class="column-title">
										<strong>
											<a href="<?php echo esc_url( get_edit_post_link( $post_data['ID'] ) ); ?>">
												<?php echo esc_html( $post_data['post_title'] ); ?>
											</a>
										</strong>
										<div class="row-actions">
											<span class="view">
												<a href="<?php echo esc_url( get_permalink( $post_data['ID'] ) ); ?>" target="_blank">View</a>
											</span>
										</div>
									</td>
									<td class="column-type"><?php echo esc_html( ucfirst( $post_data['post_type'] ) ); ?></td>
									<td class="column-views">
										<strong><?php echo number_format( $post_data['view_count'] ); ?></strong>
									</td>
									<td class="column-last-viewed">
										<?php
										if ( $post_data['last_viewed'] ) {
											echo esc_html( gmdate( 'M j, Y g:i A', strtotime( $post_data['last_viewed'] ) ) );
										} else {
											echo 'Never';
										}
										?>
									</td>
									<td class="column-actions">
										<button type="button" class="button button-small" onclick="pvcResetSingleView(<?php echo esc_attr( $post_data['ID'] ); ?>)">
											Reset
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>

				<?php if ( $total_pages > 1 ) : ?>
					<div class="tablenav bottom">
						<div class="tablenav-pages">
							<?php
							$pagination_args = array(
								'base'      => add_query_arg( 'paged', '%#%' ),
								'format'    => '',
								'prev_text' => '&laquo;',
								'next_text' => '&raquo;',
								'total'     => $total_pages,
								'current'   => $paged,
							);
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo paginate_links( $pagination_args );
							?>
						</div>
					</div>
				<?php endif; ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Get posts with view counts for admin display.
	 *
	 * @param string $orderby   Order by field.
	 * @param string $order     Order direction.
	 * @param string $post_type Post type filter.
	 * @param int    $per_page  Posts per page.
	 * @param int    $paged     Current page.
	 * @return array Posts data.
	 */
	private function get_posts_with_views( $orderby = 'views', $order = 'desc', $post_type = 'all', $per_page = 20, $paged = 1 ) {
		global $wpdb;

		$offset           = ( $paged - 1 ) * $per_page;
		$post_type_clause = 'all' !== $post_type ? $wpdb->prepare( 'AND p.post_type = %s', $post_type ) : '';

		$order_clause = '';
		switch ( $orderby ) {
			case 'title':
				$order_clause = 'ORDER BY p.post_title ' . ( 'desc' === $order ? 'DESC' : 'ASC' );
				break;
			case 'last_viewed':
				$order_clause = 'ORDER BY lv.meta_value ' . ( 'desc' === $order ? 'DESC' : 'ASC' );
				break;
			case 'views':
			default:
				$order_clause = 'ORDER BY CAST(vc.meta_value AS UNSIGNED) ' . ( 'desc' === $order ? 'DESC' : 'ASC' );
				break;
		}

		$query = $wpdb->prepare(
			"SELECT p.ID, p.post_title, p.post_type, 
				   COALESCE(vc.meta_value, 0) as view_count,
				   lv.meta_value as last_viewed
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} vc ON p.ID = vc.post_id AND vc.meta_key = '_pvc_view_count'
			LEFT JOIN {$wpdb->postmeta} lv ON p.ID = lv.post_id AND lv.meta_key = '_pvc_last_viewed'
			WHERE p.post_status = 'publish' 
			AND p.post_type IN ('post', 'page')
			{$post_type_clause}
			AND (vc.meta_value IS NOT NULL OR lv.meta_value IS NOT NULL)
			{$order_clause}
			LIMIT %d OFFSET %d",
			$per_page,
			$offset
		);

		return $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Get total count of posts with views.
	 *
	 * @param string $post_type Post type filter.
	 * @return int Total posts count.
	 */
	private function get_total_posts_with_views( $post_type = 'all' ) {
		global $wpdb;

		$post_type_clause = 'all' !== $post_type ? $wpdb->prepare( 'AND p.post_type = %s', $post_type ) : '';

		$query = "SELECT COUNT(DISTINCT p.ID)
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} vc ON p.ID = vc.post_id AND vc.meta_key = '_pvc_view_count'
			LEFT JOIN {$wpdb->postmeta} lv ON p.ID = lv.post_id AND lv.meta_key = '_pvc_last_viewed'
			WHERE p.post_status = 'publish' 
			AND p.post_type IN ('post', 'page')
			{$post_type_clause}
			AND (vc.meta_value IS NOT NULL OR lv.meta_value IS NOT NULL)";

		return $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Display view summary statistics.
	 */
	private function display_view_summary() {
		global $wpdb;

		// Get total views.
		$total_views = $wpdb->get_var(
			"SELECT SUM(CAST(meta_value AS UNSIGNED)) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_pvc_view_count'"
		);

		// Get posts with views.
		$posts_with_views = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_pvc_view_count' AND meta_value > 0"
		);

		// Get most viewed post.
		$most_viewed = $wpdb->get_row(
			"SELECT p.post_title, pm.meta_value as views
			FROM {$wpdb->postmeta} pm
			JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE pm.meta_key = '_pvc_view_count'
			AND p.post_status = 'publish'
			ORDER BY CAST(pm.meta_value AS UNSIGNED) DESC
			LIMIT 1"
		);

		// Get average views.
		$avg_views = $posts_with_views > 0 ? round( $total_views / $posts_with_views, 1 ) : 0;

		?>
		<div class="stats-grid">
			<div class="stat-item">
				<span class="stat-number"><?php echo number_format( $total_views ?: 0 ); ?></span>
				<span class="stat-label">Total Views</span>
			</div>
			<div class="stat-item">
				<span class="stat-number"><?php echo number_format( $posts_with_views ?: 0 ); ?></span>
				<span class="stat-label">Posts with Views</span>
			</div>
			<div class="stat-item">
				<span class="stat-number"><?php echo number_format( $avg_views ); ?></span>
				<span class="stat-label">Average Views</span>
			</div>
			<?php if ( $most_viewed ) : ?>
				<div class="stat-item">
					<span class="stat-number"><?php echo number_format( $most_viewed->views ); ?></span>
					<span class="stat-label">Most Viewed<br><small><?php echo esc_html( wp_trim_words( $most_viewed->post_title, 4 ) ); ?></small></span>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}