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

	/** Constructor */
	function __construct() {
		add_action( 'widgets_init', array( $this, 'register' ) );
		add_action( 'enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_ajax_wc_live_search', array( $this, 'search' ) );
	}

	/** Returns the search results */
	function search() {

	}

	/** Registers the widget */
	function register () {
		register_widget( "Wc_Live_Search_Widget" );
	}

	/** Enqueue scripts and styles */
	function enqueue () {
		wp_enqueue_script( 'wcls-script', plugin_dir_url( __FILE__ ) . '/script.js' );
	}
}

include 'class-wc-live-search-widget.php';

Wc_Live_Search::instance();