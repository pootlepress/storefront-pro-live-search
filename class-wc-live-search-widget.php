<?php
/**
 * Main widget
 * @developer wpdevelopment.me <shramee@wpdvelopment.me>
 */

/** Class Wc_Live_Search_Widget */
class Wc_Live_Search_Widget extends WP_Widget {

	/** Basic Widget Settings */
	const WIDGET_NAME = "WooCommerce Live Search";
	const WIDGET_DESCRIPTION = "Awesome widget that searches for WooCommerce Products and Product Categories";

	var $textdomain;
	var $fields;

	/** Construct the widget */
	function __construct() {
		$this->textdomain = 'wc-live-search';

		// Add fields
		$this->fields();

		//Translations
		load_plugin_textdomain( $this->textdomain, false, basename( dirname( __FILE__ ) ) . '/languages' );

		// Widget data
		$name = __( self::WIDGET_NAME, $this->textdomain );
		$args = array(
			'description' => __( self::WIDGET_DESCRIPTION, $this->textdomain ),
			'classname'   => $this->textdomain
		);

		// Call parent constructor
		parent::__construct( $this->textdomain, $name, $args );
	}

	/** Add all fields for widget form */
	function fields() {
		$this->add_field( 'title', 'Enter title', '', 'text' );
		$this->add_field( 'placeholder', 'Seach box placeholder', 'Search', 'text' );
	}

	/**
	 * Widget frontend
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		$instance = wp_parse_args(
			$instance,
			array(
				'title' => '',
				'placeholder' => 'Search',
			)
		);

		$title = apply_filters( 'widget_title', $instance['title'] );

		/* Before and after widget arguments are usually modified by themes */
		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		?>
		<div class="wc-live-search-container">
			<input placeholder="<?php echo $instance['placeholder'] ?>" type='search' class='wc-live-search-field' />
			<div class='wc-live-search-results'></div>
		</div>
		<?php

		/* After widget */
		echo $args['after_widget'];
	}

	/**
	 * Widget backend
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
		/* Generate admin for fields */
		foreach ( $this->fields as $field_name => $field_data ) {
			if ( in_array( $field_data['type'], array( 'text', 'number', 'range', 'date', 'time', 'datetime', 'checkbox' ) ) ):
				?>
				<p>
					<label for="<?php echo $this->get_field_id( $field_name ); ?>"><?php _e( $field_data['description'], $this->textdomain ); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id( $field_name ); ?>" name="<?php echo $this->get_field_name( $field_name ); ?>" type="text" value="<?php echo esc_attr( isset( $instance[ $field_name ] ) ? $instance[ $field_name ] : $field_data['default_value'] ); ?>"/>
				</p>
				<?php
			//elseif($field_data['type'] == 'textarea'):
			//You can implement more field types like this.
			else:
				echo __( 'Error - Field type not supported', $this->textdomain ) . ': ' . $field_data['type'];
			endif;
		}
	}

	/**
	 * Adds a text field to the widget
	 *
	 * @param $field_name
	 * @param string $field_description
	 * @param string $field_default_value
	 * @param string $field_type
	 */
	private function add_field( $field_name, $field_description = '', $field_default_value = '', $field_type = 'text' ) {
		if ( ! is_array( $this->fields ) ) {
			$this->fields = array();
		}

		$this->fields[ $field_name ] = array(
			'name'          => $field_name,
			'description'   => $field_description,
			'default_value' => $field_default_value,
			'type'          => $field_type
		);
	}

	/**
	 * Updating widget by replacing the old instance with new
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}
}