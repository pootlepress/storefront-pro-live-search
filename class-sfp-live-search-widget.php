<?php
/**
 * Main widget
 * @developer wpdevelopment.me <shramee@wpdvelopment.me>
 */

/** Class Storefront_Pro_Live_Search_Widget */
class Storefront_Pro_Live_Search_Widget extends WP_Widget {

	/** Basic Widget Settings */
	const WIDGET_NAME = "Storefront Pro Live Search";
	const WIDGET_DESCRIPTION = "Awesome widget that searches for WooCommerce Products and Product Categories live!";

	var $textdomain;
	var $fields;

	/** Construct the widget */
	function __construct() {
		$this->textdomain = 'sfp-live-search';

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
	 * Widget frontend
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		echo $this->render_widget( $args, $instance );
	}

	/**
	 * Widget frontend
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @return string Widget HTML
	 */
	public function render_widget( $args, $instance ) {

		$instance = wp_parse_args(
			$instance,
			array(
				'title'       => '',
				'placeholder' => 'Search',
			)
		);

		$title = apply_filters( 'widget_title', $instance['title'] );

		$html = '';

		if ( ! empty( $title ) ) {
			$html .= $args['before_title'] . $title . $args['after_title'];
		}
		$html .= "
		<div class='sfp-live-search-container'>
		<form role='search' method='get' action='" . site_url() . "'>
			<label class='screen-reader-text' for='s'>Search for:</label>
			<input placeholder='$instance[placeholder]' type='search' class='search-field sfp-live-search-field' name='s' title='Search for:'>
			<button type='submit'><span class='fa fa-search'></span></button>
			<input type='hidden' name='post_type[]' value='product'>
			<input type='hidden' name='post_type[]' value='post'>
			<input type='hidden' name='post_type[]' value='page'>
			<div class='sfp-live-search-results'></div>
		</form>
		</div>
		";

		/* Before and after widget arguments */

		return $args['before_widget'] . $html . $args['after_widget'];
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
			if ( in_array( $field_data['type'], array(
				'text',
				'number',
				'range',
				'date',
				'time',
				'datetime',
				'checkbox'
			) ) ):
				?>
				<p>
					<label
						for="<?php echo $this->get_field_id( $field_name ); ?>"><?php _e( $field_data['description'], $this->textdomain ); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id( $field_name ); ?>"
								 name="<?php echo $this->get_field_name( $field_name ); ?>" type="text"
								 value="<?php echo esc_attr( isset( $instance[ $field_name ] ) ? $instance[ $field_name ] : $field_data['default_value'] ); ?>"/>
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