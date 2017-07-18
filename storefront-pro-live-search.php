<?php
/**
 * @developer wpdevelopment.me <shramee@wpdvelopment.me>
 * Plugin Name: Storefront Pro Live Search
 * Plugin URI: http://pootlepress.com/sfp-live-search
 * Description: Search WooCommerce products and categories live.
 * Version: 1.0
 * Author: pootlepress
 * Author URI: http://pootlepress.com
 * License: GPL2
 */

/** WooCommerce Live Search main class */
class Storefront_Pro_Live_Search {

	/** @var Storefront_Pro_Live_Search Instance */
	private static $instance = null;
	/** @var string Text domain */
	private $textdomain = null;

	/** Constructor */
	function __construct() {
		$this->textdomain = 'sfp-live-search';
		add_action( 'widgets_init', array( $this, 'register' ) );
		add_action( 'wp', array( $this, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'rest_api_init', array( $this, 'rest_routes' ) );
		add_filter( 'storefront_pro_fields', array( $this, 'fields' ) );
		add_filter( 'storefront_handheld_footer_bar_links', array( $this, 'footer_links' ) );
		add_action( 'post_updated', array( $this, 'maybe_update_cache' ), 10, 3 );
		//Now handled by REST api
//		add_action( 'wp_ajax_Storefront_Pro_Live_Search', array( $this, 'search' ) );
	}

	/** @return Storefront_Pro_Live_Search Instance */
	static function instance() {
		if ( ! Storefront_Pro_Live_Search::$instance ) {
			Storefront_Pro_Live_Search::$instance = new Storefront_Pro_Live_Search();
		}

		return Storefront_Pro_Live_Search::$instance;
	}

	/** Initiate hooks */
	function fields( $fields ) {
		$fields[] = array(
			'id'       => 'show-live-search',
			'label'    => 'Show live search',
			'section'  => 'existing_header_image',
			'priority' => 25,
			'type'     => 'select',
			'default'  => '',
			'choices'  => array(
				'' => "Don't show",
				'1'   => 'Replace default search',
				'2'   => 'Add in header',
			),
		);
		return $fields;
	}

	/** Initiate hooks */
	function init() {
		$show = Storefront_Pro::instance()->public->get( 'show-live-search' );
		if ( 1 == $show ) {
			add_filter( 'sfp_search_form_html', array( $this, 'replace_storefront_search' ) );
		} else if ( 2 == $show ) {
			add_filter( 'storefront_header', array( $this, 'header_searchbar' ), 23 );
		}
	}

	/** Returns the search results */
	function rest_routes() {
		register_rest_route( 'sfp-live-search/v1', '/search', array(
			'methods'  => [ 'POST', 'GET' ],
			'callback' => array( $this, 'search' ),
		) );

	}

	/** Returns the search results */
	function header_searchbar() {
		?>
		<div class="sfp-header-live-search">
			<?php the_widget( 'Storefront_Pro_Live_Search_Widget' ); ?>
		</div>
		<?php
	}

	/** Returns the search results */
	function replace_storefront_search( $html, $args = [] ) {

		ob_start();
		the_widget( 'Storefront_Pro_Live_Search_Widget' );
		$live_search = ob_get_clean();
		if ( $live_search ) {
			return $live_search;
		} else {
			return $html;
		}
	}

	/** Returns the search results */
	function search() {

		$s         = filter_input( INPUT_POST, 's' );
		$s         = ! $s ? filter_input( INPUT_GET, 's' ) : $s;
		$json      = get_transient( "sfp-ls-q-$s" );

		if ( $json ) {
			return $json;
		} else {
			$cats = __( 'Categories', $this->textdomain );
			$prods = __( 'Products', $this->textdomain );
			$json = array(
			);

		}

/*
		$terms     = get_terms( array(
			'taxonomy'   => 'product_cat',
			'number'     => 7,
			'name__like' => $s,
		) );
		$cats_json = array();
		foreach ( $terms as $t ) {
			$link               = get_term_link( $t );
			$cats_json[ $link ] = [
				'url' => $link,
				'title' => $t->name,
			];
			//"<a class='wcls-tax' href='$link'>$t->name</a>";
		}

		if ( $cats_json ) {
			$json[ $cats ] = $cats_json;
		}
{"_url":"http:\/\/wp\/ppb\/wp-content\/uploads","Products":[{"url":"http:\/\/wp\/ppb\/shop\/\/?post_type=product&amp;p=31","title":"Ninja Silhouette","img":null},{"url":"http:\/\/wp\/ppb\/shop\/\/?post_type=product&amp;p=34","title":"Woo Ninja","img":"2013\/06\/T_6_front.jpg"},{"url":"http:\/\/wp\/ppb\/shop\/\/?post_type=product&amp;p=37","title":"Happy Ninja","img":"2013\/06\/T_7_front.jpg"},{"url":"http:\/\/wp\/ppb\/shop\/\/?post_type=product&amp;p=47","title":"Woo Ninja","img":"2013\/06\/hoodie_2_front.jpg"},{"url":"http:\/\/wp\/ppb\/shop\/\/?post_type=product&amp;p=50","title":"Patient Ninja","img":"2013\/06\/hoodie_3_front.jpg"},{"url":"http:\/\/wp\/ppb\/shop\/\/?post_type=product&amp;p=53","title":"Happy Ninja","img":"2013\/06\/hoodie_4_front.jpg"},{"url":"http:\/\/wp\/ppb\/shop\/\/?post_type=product&amp;p=56","title":"Ninja Silhouette","img":"2013\/06\/hoodie_5_front.jpg"}]}
*/
		global $wpdb;

		$s = explode( ' ', $s );

		$qry = implode( '%" AND post_title LIKE "%', $s );

		$json[ $prods ] = $wpdb->get_results(
			'SELECT guid AS url, post_title AS title, m2.meta_value AS img ' .
			'FROM ' . $wpdb->posts . ' AS post  ' .
			'LEFT JOIN ' . $wpdb->postmeta . ' as m ON post.ID = m.post_id AND m.meta_key = "_thumbnail_id" ' .
			'LEFT JOIN ' . $wpdb->postmeta . ' as m2 ON m.meta_value = m2.post_id AND m2.meta_key = "_wp_attached_file" ' .
			'WHERE post_type = "product" AND post_title LIKE "%' . $qry . '%" LIMIT 25' );

		return $json;
	}

	/**
	 * @param Int $post_id
	 * @param WP_Post $post
	 */
	function maybe_update_cache( $post_id, $post ) {
		if ( $post && $post->post_type == 'product' ) {
			$this->cache_all_products();
		}
	}

	function cache_all_products() {
		global $wpdb;

		$all_products = $wpdb->get_results(
			'SELECT guid AS url, post_title AS title, m2.meta_value AS img ' .
			'FROM ' . $wpdb->posts . ' AS post  ' .
			'LEFT JOIN ' . $wpdb->postmeta . ' as m ON post.ID = m.post_id AND m.meta_key = "_thumbnail_id" ' .
			'LEFT JOIN ' . $wpdb->postmeta . ' as m2 ON m.meta_value = m2.post_id AND m2.meta_key = "_wp_attached_file" ' .
			'WHERE post_type = "product" LIMIT 999' );

		update_option( "sfp-ls-all-products", $all_products );

		return $all_products;
	}

	/** Registers the widget */
	function register() {
		register_widget( "Storefront_Pro_Live_Search_Widget" );
	}

	/** Enqueue scripts and styles */
	function enqueue() {

		$products = get_option( "sfp-ls-all-products" );

		if ( ! $products ) {
			$products = $this->cache_all_products();
		}

		$upload_dir = wp_get_upload_dir();

		wp_enqueue_script( 'wcls-script', plugin_dir_url( __FILE__ ) . '/script.min.js', array( 'jquery' ), '1.0.0', 'in_footer' );
		wp_localize_script( 'wcls-script', 'wclsAjax', array(
//			'url' => admin_url( 'admin-ajax.php' ),
			'url' => get_rest_url( null, '/sfp-live-search/v1/search' ),
			'upload_dir' => $upload_dir['baseurl'],
			'categories' => __( 'Categories', $this->textdomain ),
			'products' => __( 'Products', $this->textdomain ),
			'prods' => $products,
//			'cats' => $categories,
		) );
		wp_enqueue_style( 'wcls-style', plugin_dir_url( __FILE__ ) . '/style.css' );
	}

	function footer_links( $links ) {
		$links['search']['callback'] = array( $this, 'footer_search' );

		return $links;
	}

	function footer_search() {
		echo '<a href="">' . esc_attr__( 'Search', 'storefront' ) . '</a>';
		?>
		<div class="site-search footer-search">
			<?php the_widget( 'Storefront_Pro_Live_Search_Widget' ); ?>
		</div>
		<?php
	}
}

include 'class-sfp-live-search-widget.php';

Storefront_Pro_Live_Search::instance();