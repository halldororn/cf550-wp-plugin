<?php defined( 'ABSPATH' ) or die( 'Access Denied, get lost!' );
///////////////////////
////  PROGRAMS PAGE ///
///////////////////////

function add_attendance_page() {
    $parent_slug = "cf550_dashboard";
	$page_title = "Mætingar";
	$menu_title = "Mætingar";
	$capability = "edit_others_posts"; // Editors, Admins, 
	$menu_slug = "cf550_attendance";
	$function = "write_attendance_page";
    add_submenu_page(
        $parent_slug,
        $page_title,
        $menu_title,
        $capability,
        $menu_slug,
        $function
    );
}
add_action( 'admin_menu', 'add_attendance_page' );


function write_attendance_page() {
    cf_write_header("attendance");
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if (isset( $_POST['attendance-added'])) {
            add_attendance();
        } else if (isset( $_POST['delete-attendance'])) { 
            delete_attendance();
        } else if (isset( $_POST['update-attendance'])) { 
            update_attendance();
        }
    } else if ($_SERVER['REQUEST_METHOD'] == "GET") {
        if (isset($_GET["attendance_id"])) {
            render_attendance_card($_GET["attendance_id"]);
        } else {
            render_attendance_list();
        }
    }
    cf_write_footer();
}

function render_attendance_list($attendance = [], $error = "") {
    $value_ssn = isset($attendance["member_ssn"]) ? 'value="'.htmlspecialchars($attendance["member_ssn"]).'"' : null ; 
    $value_id = isset($attendance["program_id"]) ? 'value="'.htmlspecialchars($attendance["program_id"]).'"' : null ; 
    $value_score = isset($attendance["score"]) ? 'value="'.htmlspecialchars($attendance["score"]).'"' : null ; 
    $value_date = isset($attendance["date"]) ? 'value="'.htmlspecialchars($attendance["date"]).'"' : null ; 
    $value_time = isset($attendance["time"]) ? 'value="'.htmlspecialchars($attendance["time"]).'"' : null ; 

    echo "<h2>Mætingar</h2>";
    if($error != "") 
    {
        if($error != "default-error") {
            echo '<h3 class="error">'.htmlspecialchars($error).'</h3>';
        } else {
            echo '<h3 class="error">Eitthvað fór úrskeiðis, vinsamlegast reynið aftur</h3>';
        }
    }
        ?>
        <form name="form_new_attendance"  class="form_add" method="POST" onsubmit="return form_validation()" action=<?php echo esc_url( CF550_ADMIN_URL.'cf550_attendance' ) ?> id="new-attendance-form">
            <h4>Ný mæting</h4>
            <h5 id="validation-errors" class="error"></h5>
            Æfingar #: <input type="text" id="program_id" name="program_id" <?php echo $value_id;?>/>
            Dagsetning: <input type="text" id="date" name="date" placeholder="áááá-mm-dd" <?php echo $value_date; ?>/>
            Tími: <input type="text" id="time" name="time" placeholder="kk:mm:ss" <?php echo $value_time; ?>/>
            Kennitala Iðkanda: <input type="text" id="member_ssn" name="member_ssn" placeholder="123456-1234" <?php echo $value_ssn; ?>/>
            Skor: <input type="text" id="score" name="score" <?php echo $value_score; ?>/>
            <input type="submit" name="attendance-added" value="Bæta við" class="btn btn-default"/>
        </form>

        <script type="text/javascript">
            function form_validation() {
                var date = document.forms["form_new_attendance"]["date"].value;
                /* Make sure the date is in the correct format if it is set */
                if (!/^(\d{4})-(\d{2})-(\d{2})$/.test(date)) {
                    document.getElementById("validation-errors").innerText = "Dagsetning þarf að vera á forminu: ÁÁÁÁ-MM-DD (t.d. 2016-02-05 fyrir fimmta febrúar 2016).";
                    return false;
                }
                var time = document.forms["form_new_attendance"]["time"].value;
                if (!/^([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/.test(time)) {
                    document.getElementById("validation-errors").innerText = "Tími þarf að vera á forminu: KK:MM:SS (t.d. 16:15:00 fyrir akkúrat korter yfir fjögur).";
                    return false;
                }
                /* Check the member ssn for invalid format */
                var member_ssn = document.forms["form_new_attendance"]["member_ssn"].value;
                if (!/^\d{6}-?\d{4}$/.test(member_ssn)) {
                    document.getElementById("validation-errors").innerText = "Kennitala þarf að vera á forminu 123456-7890 eða 1234567890";
                    document.getElementById("member_ssn").style.border = "1px solid red";
                    return false;
                }
            }
            function check_delete(programid) {
                var checkbox = document.getElementById("check-"+programid);
                return checkbox.checked;
            }
        </script>
        <table class="cf-table">
            <tr>
                <th>Æfingar#</th>
                <th>Dagur</th>
                <th>Dagsetning</th>
                <th>Tími</th>
                <th>Kennitala</th>
                <th>Skor</th>
                <th>Aðgerð</th>
            </tr>
            <?php
                global $wpdb;
                $result = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix."cf_attendance".' ORDER BY id desc');
                foreach ($result as $r) {
                     echo "<tr>";
                        echo "<td>".htmlspecialchars($r->program_id)."</td>";
                        echo "<td>".htmlspecialchars($r->day)."</td>";
                        echo "<td>".htmlspecialchars($r->date)."</td>";
                        echo "<td>".htmlspecialchars($r->time)."</td>";
                        echo "<td>".htmlspecialchars($r->member_ssn)."</td>";
                        echo "<td>".htmlspecialchars($r->score)."</td>";
                        echo '<td>
                                <form method="POST" action="'.esc_url(CF550_ADMIN_URL.'cf550_attendance').'" onsubmit="return check_delete('.$r->id.')">
                                    <input type="hidden" name="attendance_id" value="'.$r->id.'" />
                                    <input id="check-'.$r->id.'" type="checkbox" name="allow_delete"/>
                                    <input type="submit" name="delete-attendance" value="Eyða mætingu" class="btn btn-default"/>
                                    <a class="btn btn-default" href='.esc_url(CF550_ADMIN_URL.'cf550_attendance').'&attendance_id='.$r->id.'>Breyta</a>
                                </form>
                            </td>';
                    echo "</tr>";
                }
            ?>
        </table>
    <?php
}

function render_attendance_card($id, $error = "") {
    if (!is_numeric($id)) {
        echo "<h4>Engin mæting er til með þetta #</h4>";
        echo "<h4>Smelltu <a href=".esc_url(CF550_ADMIN_URL.'cf550_attendance').">hér</a> til að fara til baka</h4>";
    }
    else {
        $attendance = get_attendance_by_id($id);
        if ($attendance == []) {
            echo "<h4>Engin mæting er til með þetta #</h4>";
            echo "<h4>Smelltu <a href=".esc_url(CF550_ADMIN_URL.'cf550_attendance').">hér</a> til að fara til baka</h4>";
            return;
        }
        if($error != "") 
        {
            if($error != "default-error") {
                echo '<h3 class="error">'.htmlspecialchars($error).'</h3>';
            } else {
                echo '<h3 class="error">Eitthvað fór úrskeiðis, ekki tókst að uppfæra kort</h3>';
            }
        }
        ?>
        <h3>Breyta mætingu</h3>
        <form method="POST" action=<?php echo esc_url( CF550_ADMIN_URL.'cf550_attendance' ) ?>>
            <div class="cf-input-div">
                <label for="attendance_id">#</label>
                <input readonly="readonly" type="text" name="attendance_id" id="attendance_id" value="<?php echo htmlspecialchars($attendance->id); ?>">
            </div>
            <div class="cf-input-div">
                <label for="program_id">Æfing</label>
                <input type="text" name="program_id" id="program_id" value="<?php echo htmlspecialchars($attendance->program_id); ?>">
            </div>
            <div class="cf-input-div">
                <label for="day">Dagur</label>
                <input readonly="readonly"  type="text" name="day" id="day" value="<?php echo htmlspecialchars($attendance->day); ?>">
            </div>
            <div class="cf-input-div">
                <label for="date">Dagsetning</label>
                <input type="text" name="date" id="date" value="<?php echo htmlspecialchars($attendance->date); ?>">
            </div>
            <div class="cf-input-div">
                <label for="time">Tími</label>
                <input type="text" name="time" id="time" value="<?php echo htmlspecialchars($attendance->time); ?>">
            </div>
            <div class="cf-input-div">
                <label for="member_ssn">Kennitala</label>
                <input type="text" name="member_ssn" id="member_ssn" value="<?php echo htmlspecialchars($attendance->member_ssn); ?>">
            </div>
            <div class="cf-input-div">
                <label for="score">Skor</label>
                <input type="text" name="score" id="score" value="<?php echo htmlspecialchars($attendance->score); ?>">
            </div>
            <input class="btn btn-default" type="submit" name="update-attendance" value="Staðfesta">
            <a href="<?php echo esc_url(CF550_ADMIN_URL."cf550_attendance")?>">Til baka</a>
        </form>
        <?php
    }
}

function update_attendance() {
    if (!isset($_POST["attendance_id"])) {
        render_attendance_list([], "default-error");
        return;
    }
    $id = $_POST["attendance_id"];
    $member_ssn = $_POST["member_ssn"];
    $program_id = $_POST["program_id"];
    $date = $_POST["date"];
    $time = $_POST["time"];
    $score = $_POST["score"];

    global $wpdb;
    if ($program_id != "" && $program_id != 0) {
        $result = $wpdb->query( $wpdb->prepare(
            'SELECT id FROM '. $wpdb->prefix."cf_programs".' WHERE id = %d',
            $program_id
        ));
        if ($wpdb->num_rows <= 0) {
            render_attendance_card($id, "Engin æfing finnst með þetta #");
            return;
        }
    }
    // check the date if it exists
    if (!($date == "" || $date == "0000-00-00") && validate_iso_date($date) == false) {
        render_attendance_card($id, "Dagsetning þarf að vera á forminu: ÁÁÁÁ-MM-DD (t.d. 2016-02-28 fyrir tuttugasta og áttunda febrúar 2016).");
        return;
    }
    if (!($time == "" || $time == "0000-00-00") && validate_time($time) == false) {
        render_attendance_card($id, "Tími þarf að vera á forminu: KK:MM:SS (t.d. 16:15:00 fyrir akkúrat korter yfir fjögur).");
        return;
    }
    //check the ssn and get the two parts:
    if (!validate_ssn($member_ssn)) {
        render_attendance_card($id, "Kennitala þarf að vera á forminu 123456-1234 eða 1234561234");
        return;
    }
    $member_ssn_pretty = format_ssn($member_ssn);
    $result = $wpdb->query( $wpdb->prepare(
        'SELECT id FROM '. $wpdb->prefix."cf_members".' WHERE ssn = %s',
        $member_ssn_pretty
    ));
    if ($wpdb->num_rows <= 0) {
        render_attendance_card($id, "Enginn iðkandi finnst með þessa kennitölu");
        return;
    }

    $table_name = $wpdb->prefix . 'cf_attendance';
    $query_response = $wpdb->query( $wpdb->prepare(
        "UPDATE $table_name SET program_id = %d, day = %s, date = %s, time = %s, member_ssn = %s, score = %s WHERE id = %d", 
        $program_id,
        ice_day_from_iso_date($date),
        $date,
        $time,
        $member_ssn_pretty,
        $score,
        $id
    )); 
    if ($query_response === false) { // error
        render_attendance_card($id, "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_attendance_card($id, "default-error");
        return;
    }
    render_attendance_card($id);
}

function add_attendance() {
    $member_ssn = $_POST["member_ssn"];
    $program_id = $_POST["program_id"];
    $date = $_POST["date"];
    $time = $_POST["time"];
    $score = $_POST["score"];
    $attendance = ["member_ssn" => $member_ssn, "program_id" => $program_id, "score" => $score, "date" => $date, "time" => $time];

    global $wpdb;
    if ($program_id != "") {
        $result = $wpdb->query( $wpdb->prepare(
            'SELECT id FROM '. $wpdb->prefix."cf_programs".' WHERE id = %d',
            $program_id
        ));
        if ($wpdb->num_rows <= 0) {
            render_attendance_card($id, "Engin æfing finnst með þetta #");
            return;
        }
    }

    // check the date if it exists
    if (!($date == "" || $date == "0000-00-00") && validate_iso_date($date) == false) {
        render_attendance_list($attendance, "Dagsetning þarf að vera á forminu: ÁÁÁÁ-MM-DD (t.d. 2016-02-28 fyrir tuttugasta og áttunda febrúar 2016).");
        return;
    }
    if (!($time == "" || $time == "0000-00-00") && validate_time($time) == false) {
        render_attendance_list($attendance, "Tími þarf að vera á forminu: KK:MM:SS (t.d. 16:15:00 fyrir akkúrat korter yfir fjögur).");
        return;
    }
    //check the ssn and get the two parts:
    $result_array = [];
    preg_match("/^(\d{6})-?(\d{4})$/", $member_ssn, $result_array);
    if (empty($result_array)) {
        render_attendance_list($attendance, "Kennitala þarf að vera á forminu 123456-1234 eða 1234561234");
        return;
    }
    $member_ssn_pretty = $result_array[1]."-".$result_array[2];
    $result = $wpdb->query( $wpdb->prepare(
        'SELECT id FROM '. $wpdb->prefix."cf_members".' WHERE ssn = %s',
        $member_ssn_pretty
    ));
    if ($wpdb->num_rows <= 0) {
        render_attendance_list($attendance, "Enginn iðkandi finnst með þessa kennitölu");
        return;
    }

    $table_name = $wpdb->prefix . 'cf_attendance';
    $query_response = $wpdb->query( $wpdb->prepare(
        "INSERT INTO $table_name (program_id,day,date,time,member_ssn,score) VALUES (%d,%s,%s,%s,%s,%s)", 
        $program_id, 
        ice_day_from_iso_date($date),
        $date,
        $time,
        $member_ssn_pretty, 
        $score
    )); 
    if ($query_response === false) { // error
        render_attendance_list($attendance, "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_attendance_list($attendance, "default-error");
        return;
    }
    render_attendance_list();
}

function delete_attendance() {
    if (!isset($_POST["attendance_id"])) {
        render_attendance_list([], "default-error");
        return;
    }
    $id = $_POST["attendance_id"];

    global $wpdb;
    $table_name = $wpdb->prefix . 'cf_attendance';
    $query_response = $wpdb->query( $wpdb->prepare(
        "DELETE FROM $table_name WHERE id = %d", 
        $id
    )); 
    if ($query_response === false) { // error
        render_attendance_list($attendance, "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_attendance_list($attendance, "default-error");
        return;
    }
        render_attendance_list();
}

function get_attendance_by_id($id) {
    global $wpdb;
    $row = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix."cf_attendance WHERE id = ".$id);
    if (!$row) {
        return [];
    } else {
        return $row;
    }
}
?>