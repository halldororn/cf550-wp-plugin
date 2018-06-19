<?php defined( 'ABSPATH' ) or die( 'Access Denied, get lost!' );
///////////////////////
////  PROGRAMS PAGE ///
///////////////////////

function add_programs_page() {
    $parent_slug = "cf550_dashboard";
	$page_title = "Æfingar";
	$menu_title = "Æfingar";
	$capability = "edit_others_posts"; // Editors, Admins, 
	$menu_slug = "cf550_programs";
	$function = "write_programs_page";
    add_submenu_page(
        $parent_slug,
        $page_title,
        $menu_title,
        $capability,
        $menu_slug,
        $function
    );
}
add_action( 'admin_menu', 'add_programs_page' );

function write_programs_page() {
    cf_write_header("programs");
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if (isset( $_POST['program-added'])) {
            add_new_program();
        } else if (isset( $_POST['delete-program'])) { 
            delete_program();
        } else if (isset( $_POST['update-program'])) { 
            update_program();
        }
    } else if ($_SERVER['REQUEST_METHOD'] == "GET") {
        if (isset($_GET["id"])) {
            render_program_card($_GET["id"]);
        } else {
            render_program_list();
        }
    }
    cf_write_footer();
}

function render_program_list($program = [], $error = "") {
    $value_date = isset($program["date"]) ? "value=".htmlspecialchars($program["date"]) : null ; 
    $value_title = isset($program["title"]) ? "value=".htmlspecialchars($program["title"]) : null ; 
    $value_description = isset($program["description"]) ? "value=".htmlspecialchars($program["description"]) : null ; 
    echo "<h2>Æfingar</h2>";
    if($error != "") 
    {
        if($error != "default-error") {
            // echo '<h3 class="error">Kennitala þarf að vera á forminu 012345-6789 eða 0123456789</h3>';
            echo '<h3 class="error">'.htmlspecialchars($error).'</h3>';
        } else {
            echo '<h3 class="error">Eitthvað fór úrskeiðis, vinsamlegast reynið aftur</h3>';
        }
    }
    ?>
    <form name="form_new_program"  class="form_add" method="POST" onsubmit="return form_validation()" action=<?php echo esc_url( CF550_ADMIN_URL.'cf550_programs' ) ?> id="new-program-form">
        <h4>Ný æfing</h4>
        <h5 id="validation-errors" class="error"></h5>
        Dagsetning: <input type="text" id="program_date" name="program_date" placeholder="ÁÁÁÁ-MM-DD" <?php echo $value_date; ?> />
        Titill: <input type="text" id="program_title" name="program_title" <?php echo $value_title; ?> />
        Lýsing: <textarea rows='4' cols='50' id="program_description" name="program_description" form="new-program-form"><?php echo $value_description; ?></textarea>
        <input type="submit" name="program-added" value="Bæta við" class="btn btn-default"/>
    </form>

    <script type="text/javascript">
        function form_validation() {
            /* Make sure the date is in the correct format */
            var program_date = document.forms["form_new_program"]["program_date"].value;
            if (! /^(\d{4})-(\d{2})-(\d{2})$/.test(program_date)) {
                document.getElementById("validation-errors").innerText = "Dagsetning þarf að vera á forminu: ÁÁÁÁ-MM-DD (t.d. 2016-02-05 fyrir fimmta febrúar 2016).";
                return false;
            }
            document.getElementById("validation-errors").innerText = "";

            /* Check that the description isn't empty */
            var program_description = document.forms["form_new_program"]["program_description"].value;
            if (program_description == null || program_description == "") {
                document.getElementById("validation-errors").innerText = "Lýsingu vantar á æfingu";
                return false;
            }
            document.getElementById("validation-errors").innerText = "";
        }
        function check_delete(programid) {
            var checkbox = document.getElementById("check-"+programid);
            return checkbox.checked;
        }
    </script>
    <table class="cf-table">
        <tr>
            <th>#</th>
            <th>Dagsetning</th>
            <th>Titill</th>
            <th>Lýsing</th>
            <th>Aðgerð</th>
        </tr>
        <?php
            global $wpdb;
            $result = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix."cf_programs".' ORDER BY date desc');
            foreach ($result as $r) {
                    echo "<tr>";
                    echo "<td>".htmlspecialchars($r->id)."</td>";
                    echo "<td>".htmlspecialchars($r->date)."</td>";
                    echo "<td>".htmlspecialchars($r->title)."</td>";
                    echo '<td style="white-space: pre-wrap;">'.htmlspecialchars($r->description)."</td>";
                    echo '<td>
                            <form method="POST" action="'.esc_url(CF550_ADMIN_URL.'cf550_programs').'" onsubmit="return check_delete('.$r->id.')">
                                <input type="hidden" name="program_id" value="'.$r->id.'" />
                                <input id="check-'.$r->id.'" type="checkbox" name="allow_delete"/>
                                <input type="submit" name="delete-program" value="Eyða æfingu" class="btn btn-default"/>
                                <a class="btn btn-default" href='.esc_url(CF550_ADMIN_URL.'cf550_programs').'&id='.$r->id.'>Breyta</a>
                            </form>
                        </td>';
                echo "</tr>";
            }
        ?>
    </table>
    <?php
}

function render_program_card($program_id, $error = "") {
    if (!is_numeric($program_id)) {
        echo "<h4>Engin æfing er til með þetta #</h4>";
        echo "<h4>Smelltu <a href=".esc_url(CF550_ADMIN_URL.'cf550_programs').">hér</a> til að fara til baka</h4>";
    }
    else {
        $program = get_program_by_id($program_id);
        if ($program == []) {
            echo "<h4>Engin æfing er til með þetta #</h4>";
            echo "<h4>Smelltu <a href=".esc_url(CF550_ADMIN_URL.'cf550_programs').">hér</a> til að fara til baka</h4>";
            return;
        }
        if($error != "") 
            {
                if($error != "default-error") {
                    echo '<h3 class="error">'.htmlspecialchars($error).'</h3>';
                } else {
                    echo '<h3 class="error">Eitthvað fór úrskeiðis, ekki tókst að uppfæra æfingu</h3>';
                }
            }
        ?>
        <h3>Breyta æfingu</h3>
        <form method="POST" action=<?php echo esc_url( CF550_ADMIN_URL.'cf550_programs' ) ?> id="update-program-form">
            <div class="cf-input-div">
                <label for="program_id">#</label>
                <input readonly="readonly" type="text" name="program_id" id="program_id" value="<?php echo htmlspecialchars($program->id); ?>">
            </div>
            <div class="cf-input-div">
                <label for="program_date">Dagsetning</label>
                <input type="text" name="program_date" id="program_date" value="<?php echo htmlspecialchars($program->date); ?>">
            </div>
            <div class="cf-input-div">
                <label for="program_title">Titill</label>
                <input type="text" name="program_title" id="program_title" value="<?php echo htmlspecialchars($program->title); ?>">
            </div>
            <div class="cf-input-div">
                <label for="program_description">Lýsing</label>
                <textarea rows='4' cols='50' name="program_description" id="program_description" form="update-program-form"><?php echo htmlspecialchars($program->description); ?></textarea>
            </div>
            <input class="btn btn-default" type="submit" name="update-program" value="Staðfesta">
            <a href="<?php echo esc_url(CF550_ADMIN_URL."cf550_programs")?>">Til baka</a>
        </form>
        <?php
    }
}

function update_program() {
    if (!isset($_POST["program_id"])) {
        render_program_card([], "default-error");
        return;
    }
    $id = $_POST["program_id"];
    $date = $_POST["program_date"];
    $title = $_POST["program_title"];
    $description = $_POST["program_description"];

    // check the date if it exists
    if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $date) == false) {
        render_program_card($id, "Dagsetning þarf að vera á forminu: ÁÁÁÁ-MM-DD (t.d. 2016-02-28 fyrir tuttugasta og áttunda febrúar 2016).");
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'cf_programs';
    $query_response = $wpdb->query( $wpdb->prepare(
        "UPDATE $table_name SET date = %s, title = %s, description = %s WHERE id = %s", 
        $date, 
        $title, 
        $description,
        $id
    )); 
    if ($query_response === false) { // error
        render_program_list(["date" => $date, "title" => $title, "description" => $description], "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_program_list(["date" => $date, "title" => $title, "description" => $description], "default-error");
        return;
    }
    render_program_list();
}

function add_new_program() {
    if (!isset($_POST["program_description"]) || !isset($_POST["program_date"])) {
        render_program_list([], "default-error");
    }
    $date = $_POST["program_date"];
    $title = $_POST["program_title"];
    $description = $_POST["program_description"];

    global $wpdb;
    $table_name = $wpdb->prefix . 'cf_programs';
    $query_response = $wpdb->query( $wpdb->prepare(
        "INSERT INTO $table_name (date,title,description) VALUES (%s,%s,%s)", 
        $date, 
        $title, 
        $description
    )); 
    if ($query_response === false) { // error
        render_program_list(["date" => $date, "title" => $title, "description" => $description], "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_program_list(["date" => $date, "title" => $title, "description" => $description], "default-error");
        return;
    }
    render_program_list();
}

function delete_program() {
    if (!isset($_POST["program_id"])) {
        render_program_list([], "default-error");
        return;
    }
    $id = $_POST["program_id"];

    global $wpdb;
    $table_name = $wpdb->prefix . 'cf_programs';
    $query_response = $wpdb->query( $wpdb->prepare(
        "DELETE FROM $table_name WHERE id = %d", 
        $id
    )); 
    if ($query_response === false) { // error
        render_program_list([], "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_program_list([], "default-error");
        return;
    }
    render_program_list([], "");
}

function get_program_by_id($id) {
    global $wpdb;
    $row = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix."cf_programs WHERE id = ".$id);
    if (!$row) {
        return [];
    } else {
        return $row;
    }
}
?>