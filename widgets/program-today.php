<?php defined( 'ABSPATH' ) or die( 'Access Denied, get lost!' );
///////////////////////
////     WIDGET     ///
///////////////////////

// Creating the widget 
class wpb_widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			// Base ID of your widget
			'cf_daily_program_widget', 

			// Widget name will appear in UI
			__('Æfing dagsins', 'wpb_widget_domain'), 

			// Widget description
			array( 'description' => __( 'Box til að birta æfingu dagsins', 'wpb_widget_domain' ), ) 
		);
	}

	// Creating widget front-end
	// This is where the action happens
	public function widget( $args, $instance ) {
		$show_tomorrow = intval(date("H")) >= 21;
		$today = date("Y-m-d");
		if ($show_tomorrow) {
			$today = date("Y-m-d", strtotime('+1 day'));
		}
		$pretty_today = date_iso_to_pretty($today);
		// $title = apply_filters( 'widget_title', $instance['title'] );
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		echo $args['before_title'] . 'Æfing '.($show_tomorrow?'morgun':'').'dagsins ('.$pretty_today.')' . $args['after_title'];

		// This is where you run the code and display the output
		global $wpdb;
		$row = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix."cf_programs WHERE date = '".$today."'");
		if (!$row) {
			echo __( 'Leyndó! Þú kemst að því þegar þú mætir :)', 'wpb_widget_domain' );
		} else {
			echo __( '<h3 style="color: #8b0000;">'.$row->title.'</h3>
            <p style="white-space: pre-wrap;color: #8b0000;">'.$row->description.'</p>');
            //<br>
            //<button class="btn btn-default" id="cf-add-attendance">Skrá skor</button>', 'wpb_widget_domain' );
		}
		echo $args['after_widget'];
	}

	// Widget Backend 
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'wpb_widget_domain' );
		}
		// Widget admin form
	}

	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
} // Class wpb_widget ends here

// Register and load the widget
function wpb_load_widget() {
	register_widget( 'wpb_widget' );
}
add_action( 'widgets_init', 'wpb_load_widget' );

function add_day($date) {

}
?>