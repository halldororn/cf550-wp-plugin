<?php defined( 'ABSPATH' ) or die( 'Access Denied, get lost!' );
//////////////////////////
////  PROGRAMTIME PAGE ///
//////////////////////////

function add_programtime_page() {
    $parent_slug = "cf550_dashboard";
	$page_title = "Æfingatímar";
	$menu_title = "Æfingatímar";
	$capability = "edit_others_posts"; // Editors, Admins, 
	$menu_slug = "cf550_programtime";
	$function = "write_programtime_page";
    add_submenu_page(
        $parent_slug,
        $page_title,
        $menu_title,
        $capability,
        $menu_slug,
        $function
    );
}
add_action( 'admin_menu', 'add_programtime_page' );

function write_programtime_page() {
    cf_write_header("programtime");
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if (isset( $_POST['programtime-added'])) {
            add_new_programtime();
        } else if (isset( $_POST['delete-programtime'])) { 
            delete_programtime();
        } else if (isset( $_POST['update-programtime'])) { 
            update_programtime();
        }
    } else if ($_SERVER['REQUEST_METHOD'] == "GET") {
        if (isset($_GET["id"])) {
            render_programtime_card($_GET["id"]);
        } else {
            render_programtime_list();
        }
    }
    cf_write_footer();
}

function render_programtime_list($programtime = [], $error = "") {
    // $value_date = isset($programtime["date"]) ? "value=".htmlspecialchars($programtime["date"]) : null ; 
    // $value_title = isset($programtime["title"]) ? "value=".htmlspecialchars($programtime["title"]) : null ; 
    // $value_description = isset($programtime["description"]) ? "value=".htmlspecialchars($programtime["description"]) : null ; 
    $value_description = isset($programtime["description"]) ? "value=".htmlspecialchars($programtime["description"]) : null;
    $value_begin_date = isset($programtime["begin_date"]) ? "value=".htmlspecialchars($programtime["begin_date"]) : null;
    $value_end_date = isset($programtime["end_date"]) ? "value=".htmlspecialchars($programtime["end_date"]) : null;
    $value_begin_time = isset($programtime["begin_time"]) ? "value=".htmlspecialchars($programtime["begin_time"]) : null;
    $value_end_time = isset($programtime["end_time"]) ? "value=".htmlspecialchars($programtime["end_time"]) : null;
    $value_is_public = isset($programtime["is_public"]) && $programtime["is_public"] == 1 ? "checked" : null;
    $value_monday = isset($programtime["monday"]) && $programtime["monday"] == 1 ? "checked" : null;
    $value_tuesday = isset($programtime["tuesday"]) && $programtime["tuesday"] == 1 ? "checked" : null;
    $value_wednesday = isset($programtime["wednesday"]) && $programtime["wednesday"] == 1 ? "checked" : null;
    $value_thursday = isset($programtime["thursday"]) && $programtime["thursday"] == 1 ? "checked" : null;
    $value_friday = isset($programtime["friday"]) && $programtime["friday"] == 1 ? "checked" : null;
    $value_saturday = isset($programtime["saturday"]) && $programtime["saturday"] == 1 ? "checked" : null;
    $value_sunday = isset($programtime["sunday"]) && $programtime["sunday"] == 1 ? "checked" : null;
    echo "<h2>Æfingatímar</h2>";
    if($error != "") 
    {
        if($error != "default-error") {
            echo '<h3 class="error">'.htmlspecialchars($error).'</h3>';
        } else {
            echo '<h3 class="error">Eitthvað fór úrskeiðis, vinsamlegast reynið aftur</h3>';
        }
    }
    ?>
    <form name="form_new_programtime"  class="form_add" method="POST" onsubmit="return form_validation()" action=<?php echo esc_url( CF550_ADMIN_URL.'cf550_programtime' ) ?> id="new-programtime-form">
        <h4>Nýr æfingatími</h4>
        <h5 id="validation-errors" class="error"></h5>
        Lýsing: <input type="text" name="programtime_description" form="new-programtime-form"><?php echo $value_description; ?></input>
        D. frá: <input type="text" name="programtime_begin_date" placeholder="áááá-mm-dd" <?php echo $value_begin_date; ?> />
        D. til: <input type="text" name="programtime_end_date" placeholder="áááá-mm-dd" <?php echo $value_end_date; ?> />
        T. frá: <input type="text" name="programtime_begin_time" placeholder="kk:mm:ss" <?php echo $value_begin_time; ?> />
        T. til: <input type="text" name="programtime_end_time" placeholder="kk:mm:ss" <?php echo $value_end_time; ?> />
        Opinn öllum: <input type="checkbox" name="programtime_is_public" <?php echo $value_is_public; ?> /><br>
        Mán: <input type="checkbox" name="programtime_monday" <?php echo $value_monday; ?> />
        Þri: <input type="checkbox" name="programtime_tuesday" <?php echo $value_tuesday; ?> />
        Mið: <input type="checkbox" name="programtime_wednesday" <?php echo $value_wednesday; ?> />
        Fim: <input type="checkbox" name="programtime_thursday" <?php echo $value_thursday; ?> />
        Fös: <input type="checkbox" name="programtime_friday" <?php echo $value_friday; ?> />
        Lau: <input type="checkbox" name="programtime_saturday" <?php echo $value_saturday; ?> />
        Sun: <input type="checkbox" name="programtime_sunday" <?php echo $value_sunday; ?> />
        <input type="submit" name="programtime-added" value="Bæta við" class="btn btn-default"/>
    </form>

    <script type="text/javascript">
        function form_validation() {
            /* Make sure the date is in the correct format */
            const begin_date = document.forms["form_new_programtime"]["programtime_begin_date"].value;
            if (! /^(\d{4})-(\d{2})-(\d{2})$/.test(begin_date)) {
                document.getElementById("validation-errors").innerText = '"D. frá" þarf að vera á forminu: áááá-mm-dd (t.d. 2016-02-05 fyrir fimmta febrúar 2016).';
                return false;
            }
            const end_date = document.forms["form_new_programtime"]["programtime_end_date"].value;
            if (! /^(\d{4})-(\d{2})-(\d{2})$/.test(end_date)) {
                document.getElementById("validation-errors").innerText = '"D. til" þarf að vera á forminu: áááá-mm-dd (t.d. 2016-02-05 fyrir fimmta febrúar 2016).';
                return false;
            }
            const begin_time = document.forms["form_new_programtime"]["programtime_begin_time"].value;
            if (!/^([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/.test(begin_time)) {
                document.getElementById("validation-errors").innerText = '"T. frá" þarf að vera á forminu: KK:MM:SS (t.d. 16:15:00 fyrir akkúrat korter yfir fjögur).';
                return false;
            }
            const end_time = document.forms["form_new_programtime"]["programtime_end_time"].value;
            if (!/^([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/.test(end_time)) {
                document.getElementById("validation-errors").innerText = '"T. til" þarf að vera á forminu: KK:MM:SS (t.d. 16:15:00 fyrir akkúrat korter yfir fjögur).';
                return false;
            }
            document.getElementById("validation-errors").innerText = "";
        }
        function check_delete(programtimeid) {
            var checkbox = document.getElementById("check-"+programtimeid);
            return checkbox.checked;
        }
    </script>
    <table class="cf-table">
        <tr>
            <th>Lýsing</th>
            <th>D. frá</th>
            <th>D. til</th>
            <th>T. frá</th>
            <th>T. til</th>
            <th>Opinn öllum</th>
            <th>Mán</th>
            <th>Þri</th>
            <th>Mið</th>
            <th>Fim</th>
            <th>Fös</th>
            <th>Lau</th>
            <th>Sun</th>
            <th>Aðgerð</th>
        </tr>
        <?php
            global $wpdb;
            $result = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix."cf_programtime".' ORDER BY begin_time asc');
            foreach ($result as $r) {
                    echo "<tr>";
                    echo '<td style="white-space: pre-wrap;">'.htmlspecialchars($r->description)."</td>";
                    echo "<td>".htmlspecialchars($r->begin_date)."</td>";
                    echo "<td>".htmlspecialchars($r->end_date)."</td>";
                    echo "<td>".htmlspecialchars($r->begin_time)."</td>";
                    echo "<td>".htmlspecialchars($r->end_time)."</td>";
                    echo "<td>".($r->is_public == 1 ? "✔" : "")."</td>";
                    echo "<td>".($r->monday == 1 ? "✔" : "")."</td>";
                    echo "<td>".($r->tuesday == 1 ? "✔" : "")."</td>";
                    echo "<td>".($r->wednesday == 1 ? "✔" : "")."</td>";
                    echo "<td>".($r->thursday == 1 ? "✔" : "")."</td>";
                    echo "<td>".($r->friday == 1 ? "✔" : "")."</td>";
                    echo "<td>".($r->saturday == 1 ? "✔" : "")."</td>";
                    echo "<td>".($r->sunday == 1 ? "✔" : "")."</td>";
                    echo '<td>
                            <form method="POST" action="'.esc_url(CF550_ADMIN_URL.'cf550_programtime').'" onsubmit="return check_delete('.$r->id.')">
                                <input type="hidden" name="programtime_id" value="'.$r->id.'" />
                                <input id="check-'.$r->id.'" type="checkbox" name="allow_delete"/>
                                <input type="submit" name="delete-programtime" value="Eyða æfingatíma" class="btn btn-default"/>
                                <a class="btn btn-default" href='.esc_url(CF550_ADMIN_URL.'cf550_programtime').'&id='.$r->id.'>Breyta</a>
                            </form>
                        </td>';
                echo "</tr>";
            }
        ?>
    </table>
    <?php
}

function render_programtime_card($programtime_id, $error = "") {
    if (!is_numeric($programtime_id)) {
        render_programtime_list([], "Ekki tókst að opna valinn æfingatíma");
        return;
    }
    else {
        $programtime = get_programtime_by_id($programtime_id);
        if ($programtime == []) {
            render_programtime_list([], "Ekki tókst að opna valinn æfingatíma");
            return;
        }
        if($error != "") 
            {
                if($error != "default-error") {
                    echo '<h3 class="error">'.htmlspecialchars($error).'</h3>';
                } else {
                    echo '<h3 class="error">Eitthvað fór úrskeiðis, ekki tókst að uppfæra æfingatíma</h3>';
                }
            }
        ?>
        <h3>Breyta æfingatíma</h3>
        <form method="POST" action=<?php echo esc_url( CF550_ADMIN_URL.'cf550_programtime' ) ?> id="update-programtime-form">
            <div class="cf-input-div">
                <label for="programtime_id">#</label>
                <input readonly="readonly" type="text" name="programtime_id" value="<?php echo htmlspecialchars($programtime->id); ?>">
            </div>
            <div class="cf-input-div">
                <label for="programtime_description">Lýsing</label>
                <input type="text" name="programtime_description" value="<?php echo htmlspecialchars($programtime->description); ?>">
            </div>
            <div class="cf-input-div">
                <label for="programtime_begin_date">Dagsetning frá</label>
                <input type="text" name="programtime_begin_date" value="<?php echo htmlspecialchars($programtime->begin_date); ?>">
            </div>
            <div class="cf-input-div">
                <label for="programtime_end_date">Dagsetning til</label>
                <input type="text" name="programtime_end_date" value="<?php echo htmlspecialchars($programtime->end_date); ?>">
            </div>
            <div class="cf-input-div">
                <label for="programtime_begin_time">Tími frá</label>
                <input type="text" name="programtime_begin_time" value="<?php echo htmlspecialchars($programtime->begin_time); ?>">
            </div>
            <div class="cf-input-div">
                <label for="programtime_end_time">Tími til</label>
                <input type="text" name="programtime_end_time" value="<?php echo htmlspecialchars($programtime->end_time); ?>">
            </div>
            <div class="cf-input-div">
                <label for="programtime_is_public">Opinn öllum</label>
                <input type="checkbox" name="programtime_is_public" <?php echo ($programtime->is_public == 1 ?  'checked':'') ?>>
            </div>
            <div class="cf-input-div">
                <label for="programtime_monday">Mánudagur</label>
                <input type="checkbox" name="programtime_monday" <?php echo ($programtime->monday == 1 ?  'checked':'') ?>>
            </div>
            <div class="cf-input-div">
                <label for="programtime_tuesday">Þriðjudagur</label>
                <input type="checkbox" name="programtime_tuesday" <?php echo ($programtime->tuesday == 1 ?  'checked':'') ?>>
            </div>
            <div class="cf-input-div">
                <label for="programtime_wednesday">Miðvikudagur</label>
                <input type="checkbox" name="programtime_wednesday" <?php echo ($programtime->wednesday == 1 ?  'checked':'') ?>>
            </div>
            <div class="cf-input-div">
                <label for="programtime_thursday">Fimmtudagur</label>
                <input type="checkbox" name="programtime_thursday" <?php echo ($programtime->thursday == 1 ?  'checked':'') ?>>
            </div>
            <div class="cf-input-div">
                <label for="programtime_friday">Föstudagur</label>
                <input type="checkbox" name="programtime_friday" <?php echo ($programtime->friday == 1 ?  'checked':'') ?>>
            </div>
            <div class="cf-input-div">
                <label for="programtime_saturday">Laugardagur</label>
                <input type="checkbox" name="programtime_saturday" <?php echo ($programtime->saturday == 1 ?  'checked':'') ?>>
            </div>
            <div class="cf-input-div">
                <label for="programtime_sunday">Sunnudagur</label>
                <input type="checkbox" name="programtime_sunday" <?php echo ($programtime->sunday == 1 ?  'checked':'') ?>>
            </div>
            <input class="btn btn-default" type="submit" name="update-programtime" value="Staðfesta">
            <a href="<?php echo esc_url(CF550_ADMIN_URL."cf550_programtime")?>">Til baka</a>
        </form>
        <?php
    }
}

function update_programtime() {
    if (!isset($_POST["programtime_id"])) {
        render_programtime_list([], "Ekki tókst að uppfæra æfingatíma");
        return;
    }
    $programtime = [
        "id" => (isset($_POST["programtime_id"]) ? $_POST["programtime_id"] : null ),
        "description" => (isset($_POST["programtime_description"]) ? $_POST["programtime_description"] : null ),
        "begin_date" => (isset($_POST["programtime_begin_date"]) ? $_POST["programtime_begin_date"] : null ),
        "end_date" => (isset($_POST["programtime_end_date"]) ? $_POST["programtime_end_date"] : null ),
        "begin_time" => (isset($_POST["programtime_begin_time"]) ? $_POST["programtime_begin_time"] : null ),
        "end_time" => (isset($_POST["programtime_end_time"]) ? $_POST["programtime_end_time"] : null ),
        "is_public" => (isset($_POST["programtime_is_public"]) ? ($_POST["programtime_is_public"] == "on" ? 1 : 0) : null ),
        "monday" => (isset($_POST["programtime_monday"]) ? ($_POST["programtime_monday"] == "on" ? 1 : 0) : null ),
        "tuesday" => (isset($_POST["programtime_tuesday"]) ? ($_POST["programtime_tuesday"] == "on" ? 1 : 0) : null ),
        "wednesday" => (isset($_POST["programtime_wednesday"]) ? ($_POST["programtime_wednesday"] == "on" ? 1 : 0) : null ),
        "thursday" => (isset($_POST["programtime_thursday"]) ? ($_POST["programtime_thursday"] == "on" ? 1 : 0) : null ),
        "friday" => (isset($_POST["programtime_friday"]) ? ($_POST["programtime_friday"] == "on" ? 1 : 0) : null ),
        "saturday" => (isset($_POST["programtime_saturday"]) ? ($_POST["programtime_saturday"] == "on" ? 1 : 0) : null ),
        "sunday" => (isset($_POST["programtime_sunday"]) ? ($_POST["programtime_sunday"] == "on" ? 1 : 0) : null )
    ];

    if (!validate_iso_date($programtime["begin_date"])) {
        render_programtime_card($programtime["id"], '"Dagsetning frá" þarf að vera á forminu: ÁÁÁÁ-MM-DD (t.d. 2016-02-28 fyrir tuttugasta og áttunda febrúar 2016).');
        return;
    }
    if (!validate_iso_date($programtime["end_date"])) {
        render_programtime_card($programtime["id"], '"Dagsetning til" þarf að vera á forminu: ÁÁÁÁ-MM-DD (t.d. 2016-02-28 fyrir tuttugasta og áttunda febrúar 2016).');
        return;
    }
    if (!validate_time($programtime["begin_time"])) {
        render_programtime_card($programtime["id"], '"Tími frá" þarf að vera á forminu: KK:MM:SS (t.d. 16:15:00 fyrir akkúrat korter yfir fjögur).');
        return;
    }
    if (!validate_time($programtime["end_time"])) {
        render_programtime_card($programtime["id"], '"Tími til" þarf að vera á forminu: KK:MM:SS (t.d. 16:15:00 fyrir akkúrat korter yfir fjögur).');
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'cf_programtime';
    $query_response = $wpdb->query( $wpdb->prepare(
        "UPDATE $table_name SET description = %s, begin_date = %s, end_date = %s, begin_time = %s, end_time = %s, is_public = %d, monday = %d, tuesday = %d, wednesday = %d, thursday = %d, friday = %d, saturday = %d, sunday = %d WHERE id = %s", 
        $programtime["description"],
        $programtime["begin_date"],
        $programtime["end_date"],
        $programtime["begin_time"],
        $programtime["end_time"],
        $programtime["is_public"],
        $programtime["monday"],
        $programtime["tuesday"],
        $programtime["wednesday"],
        $programtime["thursday"],
        $programtime["friday"],
        $programtime["saturday"],
        $programtime["sunday"],
        $programtime["id"]
    )); 
    if ($query_response === false) { // error
        render_programtime_card($programtime["id"], "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_programtime_card($programtime["id"], "default-error");
        return;
    }
    render_programtime_card($programtime["id"]);
}

function add_new_programtime() {
    $programtime = [
        "description" => (isset($_POST["programtime_description"]) ? $_POST["programtime_description"] : null ),
        "begin_date" => (isset($_POST["programtime_begin_date"]) ? $_POST["programtime_begin_date"] : null ),
        "end_date" => (isset($_POST["programtime_end_date"]) ? $_POST["programtime_end_date"] : null ),
        "begin_time" => (isset($_POST["programtime_begin_time"]) ? $_POST["programtime_begin_time"] : null ),
        "end_time" => (isset($_POST["programtime_end_time"]) ? $_POST["programtime_end_time"] : null ),
        "is_public" => (isset($_POST["programtime_is_public"]) ? ($_POST["programtime_is_public"] == "on" ? 1 : 0) : null ),
        "monday" => (isset($_POST["programtime_monday"]) ? ($_POST["programtime_monday"] == "on" ? 1 : 0) : null ),
        "tuesday" => (isset($_POST["programtime_tuesday"]) ? ($_POST["programtime_tuesday"] == "on" ? 1 : 0) : null ),
        "wednesday" => (isset($_POST["programtime_wednesday"]) ? ($_POST["programtime_wednesday"] == "on" ? 1 : 0) : null ),
        "thursday" => (isset($_POST["programtime_thursday"]) ? ($_POST["programtime_thursday"] == "on" ? 1 : 0) : null ),
        "friday" => (isset($_POST["programtime_friday"]) ? ($_POST["programtime_friday"] == "on" ? 1 : 0) : null ),
        "saturday" => (isset($_POST["programtime_saturday"]) ? ($_POST["programtime_saturday"] == "on" ? 1 : 0) : null ),
        "sunday" => (isset($_POST["programtime_sunday"]) ? ($_POST["programtime_sunday"] == "on" ? 1 : 0) : null )
    ];
    if (
        !validate_iso_date($programtime["begin_date"]) ||
        !validate_iso_date($programtime["end_date"]) ||
        !validate_time($programtime["begin_time"]) ||
        !validate_time($programtime["end_time"])
    ) 
    {
        render_programtime_list($programtime, "default-error");
        return;
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'cf_programtime';
    $query_response = $wpdb->query( $wpdb->prepare(
        "INSERT INTO $table_name (description, begin_date, end_date, begin_time, end_time, is_public, monday, tuesday, wednesday, thursday, friday, saturday, sunday) VALUES (%s,%s,%s,%s,%s,%d,%d,%d,%d,%d,%d,%d,%d)", 
        $programtime["description"],
        $programtime["begin_date"],
        $programtime["end_date"],
        $programtime["begin_time"],
        $programtime["end_time"],
        $programtime["is_public"],
        $programtime["monday"],
        $programtime["tuesday"],
        $programtime["wednesday"],
        $programtime["thursday"],
        $programtime["friday"],
        $programtime["saturday"],
        $programtime["sunday"]
    )); 
    if ($query_response === false) { // error
        render_programtime_list($programtime, "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_programtime_list($programtime, "default-error");
        return;
    }
    render_programtime_list();
}

function delete_programtime() {
    if (!isset($_POST["programtime_id"])) {
        render_programtime_list([], "default-error");
        return;
    }
    $id = $_POST["programtime_id"];

    global $wpdb;
    $table_name = $wpdb->prefix . 'cf_programtime';
    $query_response = $wpdb->query( $wpdb->prepare(
        "DELETE FROM $table_name WHERE id = %d", 
        $id
    )); 
    if ($query_response === false) { // error
        render_programtime_list([], "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_programtime_list([], "default-error");
        return;
    }
    render_programtime_list([], "");
}

function get_programtime_by_id($id) {
    global $wpdb;
    $row = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix."cf_programtime WHERE id = ".$id);
    if (!$row) {
        return [];
    } else {
        return $row;
    }
}
?>