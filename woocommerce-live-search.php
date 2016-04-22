<?php
/**
 * @developer wpdevelopment.me <shramee@wpdvelopment.me>
 * Plugin Name: WooCommerce Live Search
 * Plugin URI: http://pootlepress.com/wc-live-search
 * Description: Effortlessly create WordPress widgets with this template!
 * Version: 1.0
 * Author: pootlepress
 * Author URI: http://pootlepress.com
 * License: GPL2
 */

/** WooCommerce Live Search main class */
class Wc_Live_Search {

	/** @var Wc_Live_Search Instance */
	private static $instance = null;

	/** @return Wc_Live_Search Instance */
	static function instance() {
		if ( ! Wc_Live_Search::$instance ) {
			Wc_Live_Search::$instance = new Wc_Live_Search();
		}

		return Wc_Live_Search::$instance;
	}

	/** @var string Text domain */
	private $textdomain = null;

	/** Constructor */
	function __construct() {
		$this->textdomain = 'wc-live-search';
		add_action( 'widgets_init', array( $this, 'register' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_ajax_wc_live_search', array( $this, 'search' ) );
	}

	/** Returns the search results */
	function search() {
		$s = filter_input( INPUT_POST, 's' );
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

		echo json_encode( $json );
		//print_r( get_intermediate_image_sizes() );
		exit();
	}

	/** Registers the widget */
	function register () {
		register_widget( "Wc_Live_Search_Widget" );
	}

	/** Enqueue scripts and styles */
	function enqueue () {
		wp_enqueue_script( 'wcls-script', plugin_dir_url( __FILE__ ) . '/script.js', array( 'jquery' ), '1.0.0', 'in_footer' );
		wp_localize_script( 'wcls-script', 'wclsAjax', array(
			'url' => admin_url( 'admin-ajax.php' ),
		) );
		wp_enqueue_style( 'wcls-style', plugin_dir_url( __FILE__ ) . '/style.css' );
	}
}

include 'class-wc-live-search-widget.php';

Wc_Live_Search::instance();