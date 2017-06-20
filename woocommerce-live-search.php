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

	/** @return Storefront_Pro_Live_Search Instance */
	static function instance() {
		if ( ! Storefront_Pro_Live_Search::$instance ) {
			Storefront_Pro_Live_Search::$instance = new Storefront_Pro_Live_Search();
		}

		return Storefront_Pro_Live_Search::$instance;
	}

	/** @var string Text domain */
	private $textdomain = null;

	/** Constructor */
	function __construct() {
		$this->textdomain = 'sfp-live-search';
		add_action( 'widgets_init', array( $this, 'register' ) );
		add_filter( 'sfp_search_form_html', array( $this, 'replace_storefront_search' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'rest_api_init', array( $this, 'rest_routes' ) );

		//Now handled by REST api
//		add_action( 'wp_ajax_Storefront_Pro_Live_Search', array( $this, 'search' ) );
	}

	/** Returns the search results */
	function rest_routes() {
		register_rest_route( 'sfp-live-search/v1', '/search', array(
			'methods' => [ 'POST', 'GET' ],
			'callback' => array( $this, 'search' ),
		) );

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
		$bench_seconds = microtime(true);

		$s = filter_input( INPUT_POST, 's' );
		$s = ! $s ? filter_input( INPUT_GET, 's' ) : $s;
		$json = array();
		$terms = get_terms( array(
			'taxonomy' => 'product_cat',
			'number' => 7,
			'name__like' => $s,
		) );
		$cats_json = array();
		foreach( $terms as $t ) {
			$link = get_term_link( $t );
			$cats_json[ $link ] = "<a class='wcls-tax' href='$link'>$t->name</a>";
		}

		if ( $cats_json ) {
			$json[ __( 'Categories', $this->textdomain ) ] = $cats_json;
		}

		$prods = get_posts( array(
			's' => $s,
			'post_type' => 'product',
			'number' => 16,
		) );
		$prods_json = array();
		foreach( $prods as $p ) {
			$link = $p->guid;
			$thumb = get_the_post_thumbnail_url( $p, 'thumbnail' );
			$prods_json[ $link ] = "<a class='wcls-prod' href='$link'><img src='$thumb'>$p->post_title</a>";
		}

		if ( $prods_json ) {
			$json[ __( 'Products', $this->textdomain ) ] = $prods_json;
		}

		if ( isset( $_REQUEST['bench'] ) ) {
			echo "<h3>Time taken : " . (microtime(true) - $bench_seconds);
		}

		return $json;
	}

	/** Registers the widget */
	function register () {
		register_widget( "Storefront_Pro_Live_Search_Widget" );
	}

	/** Enqueue scripts and styles */
	function enqueue () {
		wp_enqueue_script( 'wcls-script', plugin_dir_url( __FILE__ ) . '/script.js', array( 'jquery' ), '1.0.0', 'in_footer' );
		wp_localize_script( 'wcls-script', 'wclsAjax', array(
//			'url' => admin_url( 'admin-ajax.php' ),
			'url' => get_rest_url( null, '/sfp-live-search/v1/search'),
		) );
		wp_enqueue_style( 'wcls-style', plugin_dir_url( __FILE__ ) . '/style.css' );
	}
}

include 'class-sfp-live-search-widget.php';

Storefront_Pro_Live_Search::instance();