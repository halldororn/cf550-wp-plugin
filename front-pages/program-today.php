<?php defined( 'ABSPATH' ) or die( 'Access Denied, get lost!' );

function get_program_today_function() {
    $show_tomorrow = intval(date("H")) >= 21;
    $today = date("Y-m-d");
    if ($show_tomorrow) {
        $today = date("Y-m-d", strtotime('+1 day'));
    }
    $pretty_today = date_iso_to_pretty($today);
    $ten_days_from_now = date("Y-m-d", strtotime('+10 day'));
    $ten_days_ago = date("Y-m-d", strtotime('-10 day'));
    echo '<h4>Æfing dagsins</h4>';

    // This is where you run the code and display the output
    global $wpdb;
    // $row = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix."cf_programs WHERE date = '".$today."'");
    $rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix."cf_programs WHERE date >= '".$ten_days_ago."' and date <= '".$ten_days_from_now."'");
    //var_dump($rows);
    $rows = add_program_today_if_not_exist($rows, $today);
    echo '
    <script type="text/javascript">
        function previous_day_page() {
            const wods = document.getElementsByClassName("WOD_page")
            let activeWod = 0
            for (i = 0; i < wods.length; i++) {
                if (wods[i].style.display == "block") activeWod = wods[i]
            }
            if (activeWod.previousSibling) {
                activeWod.previousSibling.style.display = "block"
                activeWod.style.display = "none"
            }
        }
        function next_day_page() {
            const wods = document.getElementsByClassName("WOD_page")
            let activeWod = 0
            for (i = 0; i < wods.length; i++) {
                if (wods[i].style.display == "block") activeWod = wods[i]
            }
            if (activeWod.nextSibling) {
                activeWod.nextSibling.style.display = "block"
                activeWod.style.display = "none"
            }
        }
    </script>
    <button onclick="previous_day_page()" class=".btn.btn-default"><</button>
    <button onclick="next_day_page()" class=".btn.btn-default">></button>
    ';
    echo '<div>';
    array_map(function($o) use ($today) {
        echo '<div class="WOD_page" style="display: '.($o->date==$today?"block":"none").'">
        <h3>'.date_iso_to_pretty($o->date).'</h3>
        <h3 style="color: #8b0000;">'.$o->title.'</h3>
        <p style="white-space: pre-wrap;color: #8b0000;">'.$o->description.'</p>
        </div>';
    }, $rows);
    echo '</div>';
}

function add_program_today_if_not_exist($rows, $today) {
    $row_today = array_filter($rows, function($o) use ($today) {
        if ($o->date === $today) {
            return true;
        } 
        return false;
    });
    // If the current day is already in the programs then we don't need to do anything.
    if (count($row_today) >= 1) {
        return $rows;
    }
    // Since it isn't here we add it in the array and return that.
    $row_today = (object) [
        "date" => $today,
        "title" => "Leyndó!",
        "description" => "Þú kemst að því þegar þú mætir"
    ];
    array_push($rows, $row_today);
    usort($rows, function($a,$b) {
        return strcmp($a->date, $b->date);
    });
    return $rows;
}

add_shortcode( 'get_program_today', 'get_program_today_function' );