<?php defined( 'ABSPATH' ) or die( 'Access Denied, get lost!' );

function get_program_today_function() {
    $show_tomorrow = intval(date("H")) >= 21;
    $today = date("Y-m-d");
    if ($show_tomorrow) {
        $today = date("Y-m-d", strtotime('+1 day'));
    }
    $pretty_today = date_iso_to_pretty($today);
    echo '<h4>Æfing '.($show_tomorrow?'morgun':'').'dagsins ('.$pretty_today.')</h4>';

    global $wpdb;
    $row = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix."cf_programs WHERE date = '".$today."'");
    if (!$row) {
        echo 'Leyndó! Þú kemst að því þegar þú mætir :)';
    } else {
        echo '<h3 style="color: #8b0000;">'.$row->title.'</h3>
        <p style="white-space: pre-wrap;color: #8b0000;">'.$row->description.'</p>';
    }
}

add_shortcode( 'get_program_today', 'get_program_today_function' );
?>