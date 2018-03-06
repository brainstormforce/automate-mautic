<?php
/**
 * AutomatePlug Table List
 *
 * @package automate-mautic
 * @since 1.0.0
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'APMautic_Table' ) ) {

	/**
	 * Initiator
	 * Create class APMautic_Table
	 */
	class APMautic_Table extends WP_List_Table {

		/**
		 * Number of items of the initial data set (before sort, search, and pagination).
		 *
		 * @since 1.0.0
		 * @var int
		 */
		protected $items_count = 0;

		/**
		 * Initialize the List Table.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			parent::__construct(
				array(
					'singular' => 'rule',
					'plural'   => 'rules',
					'ajax'     => false,
				)
			);
		}

		/**
		 * Modify table columns
		 *
		 * @param array  $item all data.
		 * @param string $column_name column name.
		 * @since 1.0.0
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'post_title':
				case 'post_author':
					return $item[ $column_name ];
				default:
					return print_r( $item, true ); // Show the whole array for troubleshooting purposes.
			}
		}

		/**
		 * Display table empty message
		 *
		 * @since 1.0.0
		 */
		public function no_items() {
			_e( 'No rules avaliable.', 'automate-mautic' );
		}

		/**
		 * Render the bulk edit checkbox
		 *
		 * @param array $item Item ID.
		 *
		 * @return string
		 */
		public function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
			);
		}

		/**
		 * Get post author
		 *
		 * @param array $item table data.
		 *
		 * @return string
		 */
		public function column_post_author( array $item ) {
			if ( '' === trim( $item['post_author'] ) ) {
				$item['post_author'] = __( '(no post_author)', 'automate-mautic' );
			}

			$author = get_the_author_meta( 'display_name', $item['post_author'] );

			return esc_html( $author );
		}

		/**
		 * Get post title
		 *
		 * @param array $item table data.
		 *
		 * @return string
		 */
		public function column_post_title( array $item ) {
			if ( '' === trim( $item['post_title'] ) ) {
				$item['post_title'] = __( '(no post_title)', 'automate-mautic' );
			}

			$url = '&action=edit&post=' . $item['ID'];

			$post_link = APMautic_AdminSettings::get_render_page_url( $url );

			$post_title = "<a href='" . $post_link . "'>" . $item['post_title'] . '</a>';

			$row_actions = array();
			// translators: %1$s: Edit post link.
			// translators: %2$s: Edit title.
			// translators: %3$s: Edit label.
			$row_actions['edit'] = sprintf( '<a href="%1$s" title="%2$s">%3$s</a>', $post_link, esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'automate-mautic' ), $item['post_title'] ) ), __( 'Edit', 'automate-mautic' ) );

			$wpnonce = wp_create_nonce( 'delete-rule' . $item['ID'] );

			$url_base = APMautic_AdminSettings::get_render_page_url( '&tab=all_rules&action=delete_rule' );

			$delete_url = $url_base . '&rule_id=' . $item['ID'] . '&_wpnonce=' . $wpnonce;

			// translators: %1$s: Delete post link.
			// translators: %2$s: Delete title.
			// translators: %3$s: Delete label.
			$row_actions['delete'] = sprintf( '<a href="%1$s" title="%2$s" class="rule-delete-link">%3$s</a>', $delete_url, esc_attr( sprintf( __( 'Delete &#8220;%s&#8221;', 'automate-mautic' ), $item['post_title'] ) ), __( 'Delete', 'automate-mautic' ) );

			return $post_title . $this->row_actions( $row_actions );
		}

		/**
		 * Get a list of columns in this List Table.
		 *
		 * Format: 'internal-name' => 'Column Title'.
		 *
		 * @since 1.0.0
		 *
		 * @return array List of columns in this List Table.
		 */
		public function get_columns() {
			$columns = array(
				'cb'          => '<input type="checkbox" />',
				'post_title'  => 'Title',
				'post_author' => 'Author',
			);

			return $columns;
		}

		/**
		 * Get a list of columns that are sortable.
		 *
		 * Format: 'internal-name' => array( $field for $item[ $field ], true for already sorted ).
		 *
		 * @since 1.0.0
		 *
		 * @return array List of sortable columns in this List Table.
		 */
		protected function get_sortable_columns() {

			$sortable_columns = array(
				'post_title'  => array( 'post_title', true ),
				'post_author' => array( 'post_author', false ),
			);
			return $sortable_columns;
		}

		/**
		 * Get a list (name => title) bulk actions that are available.
		 *
		 * @since 1.0.0
		 *
		 * @return array Bulk actions for this table.
		 */
		protected function get_bulk_actions() {
			$actions = array(
				'bulk-delete' => 'Delete',
			);
			return $actions;
		}

		/**
		 * Handle bulk actions
		 *
		 * @since 1.0.0
		 * @param string $which item action.
		 * @return void
		 */
		protected function bulk_actions( $which = '' ) {
			if ( is_null( $this->_actions ) ) {
				$no_new_actions = $this->get_bulk_actions();
				$this->_actions = $this->get_bulk_actions();
				// This filter is documented in the WordPress function WP_List_Table::bulk_actions() in wp-admin/includes/class-wp-list-table.php.
				$this->_actions = apply_filters( 'bulk_actions-' . $this->screen->id, $this->_actions ); // @codingStandardsIgnoreLine
				$this->_actions = array_intersect_assoc( $this->_actions, $no_new_actions );
				$two            = '';
			} else {
				$two = '2';
			}

			if ( empty( $this->_actions ) ) {
				return;
			}

			$name_id = "bulk-action-{$which}";
			echo "<label for='{$name_id}' class='screen-reader-text'>" . __( 'Select Bulk Action', 'automate-mautic' ) . "</label>\n";
			echo "<select name='{$name_id}' id='{$name_id}'>\n";
			echo "<option value='-1' selected='selected'>" . __( 'Bulk Actions', 'automate-mautic' ) . "</option>\n";
			foreach ( $this->_actions as $name => $title ) {
				echo "\t<option value='{$name}'>{$title}</option>\n";
			}
			echo "</select>\n";
			submit_button(
				__( 'Apply', 'automate-mautic' ), 'action', '', false, array(
					'id' => "doaction{$two}",
				)
			);
			echo "\n";
		}

		/**
		 * Prepares the list of items for displaying, by maybe searching and sorting, and by doing pagination.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function prepare_items() {
			$columns = $this->get_columns();

			$this->process_bulk_action();
			$hidden                = array();
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$this->items           = $this->get_rules();
		}

		/**
		 * Prepares the list of items for displaying, by maybe searching and sorting, and by doing pagination.
		 *
		 * @since 1.0.0
		 * @return array results
		 */
		public function get_rules() {

			global $wpdb;
			$page_number = $this->get_pagenum();
			$query       = "SELECT ID,post_title,post_author,post_modified_gmt FROM {$wpdb->prefix}posts where post_type='%s' && post_status = 'publish'";

			if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {
				$seachkey = trim( $_GET['s'] );
				$seachkey = esc_attr( $seachkey );
				$query   .= " && post_title LIKE '%" . $seachkey . "%'";
			}

			$total_items = count( $wpdb->get_results( $wpdb->prepare( $query, AP_MAUTIC_POSTTYPE ), ARRAY_A ) ); // WPCS: unprepared SQL OK.

			$perpage = 10;

			// How many pages do we have in total ?
			$totalpages = ceil( $total_items / $perpage );

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'total_pages' => $totalpages,
					'per_page'    => $perpage,
				)
			);

			$orderby = ! empty( $_GET['orderby'] ) ? esc_attr( $_GET['orderby'] ) : 'ASC';

			$order = ! empty( $_GET['order'] ) ? esc_attr( $_GET['order'] ) : '';
			if ( ! empty( $orderby ) & ! empty( $order ) ) {
				$query .= ' ORDER BY ' . $orderby . ' ' . $order;
			}

			// Which page is this ?
			$paged = ! empty( $_GET['paged'] ) ? esc_attr( $_GET['paged'] ) : '';

			if ( empty( $paged ) || ! is_numeric( $paged ) || $paged <= 0 ) {
				$paged = 1;
			}

			// adjust the query to take pagination into account.
			if ( ! empty( $paged ) && ! empty( $perpage ) ) {
				$offset = ( $paged - 1 ) * $perpage;
				$query .= ' LIMIT ' . (int) $offset . ',' . (int) $perpage;
			}

			$result = $wpdb->get_results( $wpdb->prepare( $query, AP_MAUTIC_POSTTYPE ), ARRAY_A ); // WPCS: unprepared SQL OK.

			return $result;
		}
	}
}
