<?php defined( 'ABSPATH' ) or die( 'Access Denied, get lost!' );
//////////////////////////////
////   SUBSCRIPTION PAGE   ///
//////////////////////////////

function add_subscription_page() {
    $parent_slug = "cf550_dashboard";
	$page_title = "Kort";
	$menu_title = "Kort";
	$capability = "edit_others_posts"; // Editors, Admins, 
	$menu_slug = "cf550_subscription";
	$function = "write_subscription_page";
    add_submenu_page(
        $parent_slug,
        $page_title,
        $menu_title,
        $capability,
        $menu_slug,
        $function
    );
}
add_action( 'admin_menu', 'add_subscription_page' );

function write_subscription_page() {
    cf_write_header("subscription");
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if (isset( $_POST['add-subscription'])) {
            add_subscription();
        } else if (isset( $_POST['delete-subscription'])) { 
            delete_subscription();
        } else if (isset( $_POST['update-subscription'])) { 
            update_subscription();
        }
    } else if ($_SERVER['REQUEST_METHOD'] == "GET") {
        if (isset($_GET["subscription_id"])) {
            render_subscription_card($_GET["subscription_id"]);
        } else {
            render_subscription_list();
        }
    }
    cf_write_footer();
}

function render_subscription_list($subscription = [], $error = "") {
    $value_name = isset($subscription["name"]) ? 'value="'.htmlspecialchars($subscription["name"]).'"' : null ; 
    echo "<h2>Kort</h2>";
    if($error != "") 
    {
        if($error != "default-error") {
            echo '<h3 class="error">'.htmlspecialchars($error).'</h3>';
        } else {
            echo '<h3 class="error">Eitthvað fór úrskeiðis, vinsamlegast reynið aftur</h3>';
        }
    }
        ?>
        <h3 id="validation-errors" class="error"></h3>
        <form id="newsubform" name="form_new_subscription" class="form_add" method="POST" onsubmit="return form_validation()" action=<?php echo esc_url( CF550_ADMIN_URL.'cf550_subscription' ) ?>>
            <h4>Nýtt kort</h4>
            Nafn: <input type="text" id="subscription_name" name="subscription_name" <?php echo $value_name; ?> required />
            Tegund: <select id="subscription_type" name="subscription_type" form="newsubform">
                        <option value="expiresbydate">Dagsetning</option>
                        <option value="expiresin">Gildistími</option>
                        <option value="count">Skipti</option>
                    </select>           
            Gildi: <input type="text" id="subscription_value" name="subscription_value" required />
            Crossfit: <input type="checkbox" id="subscription_crossfit" name="subscription_crossfit" />
            <input type="submit" name="add-subscription" value="Bæta við" class="btn btn-default"/>
        </form>

        <script type="text/javascript">
            function form_validation() {
                /* Make sure the date is in the correct format */
                var type = document.forms["form_new_subscription"]["subscription_type"].value;
                var value = document.forms["form_new_subscription"]["subscription_value"].value;
                if (type == "expiresbydate" && /^\d{4}-\d{2}-\d{2}$/.test(value) == false) {
                    document.getElementById("validation-errors").innerText = "Dagsetning þarf að vera á forminu: ÁÁÁÁ-MM-DD (t.d. 2016-02-28 fyrir tuttugasta og áttunda febrúar 2016).";
                    document.getElementById("subscription_value").style.border = "1px solid red";
                    return false;
                }
                else if (type == "expiresin" && /^(\d{1,3})á(\d{1,3})m(\d{1,3})d$/.test(value) == false) {
                    document.getElementById("validation-errors").innerText = "Gildistími þarf að vera á forminu: XáYmZd (t.d. 2á0m48d fyrir 2 ár, 0 mánuði og 48 daga).";
                    document.getElementById("subscription_value").style.border = "1px solid red";
                    return false;
                }
                else if (type == "count" && /^\d+$/ ($value) == false) {
                    document.getElementById("validation-errors").innerText = "Skipti getur bara verið tala.";
                    document.getElementById("subscription_value").style.border = "1px solid red";
                    return false;
                }
            }
            function check_delete(subscriptionid) {
                var checkbox = document.getElementById("check-"+subscriptionid);
                return checkbox.checked;
            }
        </script>
        <table class="cf-table">
            <tr>
                <th>#</th>
                <th>Nafn</th>
                <!--<th>Lýsing</th>-->
                <th>Tegund</th>
                <th>Gildi</th>
                <th>Crossfit</th>
                <th>Aðgerð</th>
            </tr>
            <?php
                global $wpdb;
                $result = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix."cf_subscription".' ORDER BY id desc');
                foreach ($result as $r) {
                     echo "<tr>";
                        echo "<td>".htmlspecialchars($r->id)."</td>";
                        echo "<td>".htmlspecialchars($r->name)."</td>";
                        //echo '<td style="white-space: pre-wrap;">'.htmlspecialchars($r->description)."</td>";
                        echo '<td>'.htmlspecialchars(type_en_to_is($r->type))."</td>";
                        echo '<td>'.htmlspecialchars($r->value)."</td>";
                        echo '<td>'.bool_to_string($r->crossfit)."</td>";
                        echo '<td>
                                <form method="POST" action="'.esc_url(CF550_ADMIN_URL.'cf550_subscription').'" onsubmit="return check_delete('.$r->id.')">
                                    <input type="hidden" name="subscription_id" value="'.$r->id.'" />
                                    <input id="check-'.$r->id.'" type="checkbox" name="allow_delete"/>
                                    <input type="submit" name="delete-subscription" value="Eyða Korti" class="btn btn-default"/>
                                    <a class="btn btn-default" href='.esc_url(CF550_ADMIN_URL.'cf550_subscription').'&subscription_id='.$r->id.'>Breyta</a>
                                </form>
                            </td>';
                    echo "</tr>";
                }
            ?>
        </table>
    <?php
}

function render_subscription_card($id, $error = "") {
    if (!is_numeric($id)) {
        echo "<h4>Ekkert kort er til með þetta #</h4>";
        echo "<h4>Smelltu <a href=".esc_url(CF550_ADMIN_URL.'cf550_subscription').">hér</a> til að fara til baka</h4>";
    }
    else {
        $subscription = get_subscription_by_id($id);
        if ($subscription == []) {
            echo "<h4>Ekkert kort er til með þetta #</h4>";
            echo "<h4>Smelltu <a href=".esc_url(CF550_ADMIN_URL.'cf550_subscription').">hér</a> til að fara til baka</h4>";
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
        <h3>Breyta korti</h3>
        <form id="updatesubform" method="POST" action=<?php echo esc_url( CF550_ADMIN_URL.'cf550_subscription' ) ?>>
            <div class="cf-input-div">
                <label for="subscription_id">#</label>
                <input readonly="readonly" type="text" name="subscription_id" id="subscription_id" value="<?php echo htmlspecialchars($subscription->id); ?>">
            </div>
            <div class="cf-input-div">
                <label for="subscription_name">Nafn</label>
                <input type="text" name="subscription_name" id="subscription_name" value="<?php echo htmlspecialchars($subscription->name); ?>">
            </div>
            <div class="cf-input-div">
                <label for="subscription_type">Tegund</label>
                <select id="subscription_type" name="subscription_type" form="updatesubform">
                    <option value="expiresbydate" <?php echo ($subscription->type=="expiresbydate"?'selected="selected"':"")?>>Dagsetning</option>
                    <option value="expiresin" <?php echo ($subscription->type=="expiresin"?'selected="selected"':"")?>>Gildistími</option>
                    <option value="count" <?php echo ($subscription->type=="count"?'selected="selected"':"")?>>Skipti</option>
                </select> 
            </div>
            <div class="cf-input-div">
                <label for="subscription_value">Gildi</label>
                <input type="text" name="subscription_value" id="subscription_value" value="<?php echo htmlspecialchars($subscription->value); ?>">
            </div>
            <div class="cf-input-div">
                <label for="subscription_crossfit">Crossfit</label>
                <input type="checkbox" name="subscription_crossfit" id="subscription_crossfit" <?php echo ($subscription->crossfit == 1 ?  'checked':'') ?>>
            </div>
            <input class="btn btn-default" type="submit" name="update-subscription" value="Staðfesta">
            <a href="<?php echo esc_url(CF550_ADMIN_URL."cf550_subscription")?>">Til baka</a>
        </form>
        <?php
    }
}

function update_subscription() {
    if (!isset($_POST["subscription_id"])) {
        render_subscription_list([], "default-error");
        return;
    }
    $id = $_POST["subscription_id"];
    $name = isset($_POST["subscription_name"]) ? $_POST["subscription_name"] : "";
    $description = isset($_POST["subscription_description"]) ? $_POST["subscription_description"] : "";
    $type = isset($_POST["subscription_type"]) ? $_POST["subscription_type"] : "";
    $value = isset($_POST["subscription_value"]) ? $_POST["subscription_value"] : "";
    $crossfit = isset($_POST["subscription_crossfit"]) ? $_POST["subscription_crossfit"] == "on" : 0;
    echo "Crossfit: ".$crossfit;
    if ($name == "" || $type == "" || $value == "") {
        echo "<p>name: $name, type: $type, value: $value</p>";
        render_subscription_card($id, "Það verður að fylla í nafn, tegund og gildi");
    }

    if ($type == "expiresbydate" && preg_match("/^\d{4}-\d{2}-\d{2}$/", $value) == false) {
        render_subscription_card($id, "Dagsetning þarf að vera á forminu: ÁÁÁÁ-MM-DD (t.d. 2016-02-28 fyrir tuttugasta og áttunda febrúar 2016).");
        return;
    }
    else if ($type == "expiresin" && preg_match("/^(\d{1,3})á(\d{1,3})m(\d{1,3})d$/", $value) == false) {
        render_subscription_card($id, "Gildistími þarf að vera á forminu: XáYmZd (t.d. 2á0m48d fyrir 2 ár, 0 mánuði og 48 daga).");
        return;
    }
    else if ($type == "count" && is_numeric($value) == false) {
        render_subscription_card($id, "Skipti getur bara verið tala.");
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'cf_subscription';
    $query_response = $wpdb->query( $wpdb->prepare(
        "UPDATE $table_name set name = %s, description = %s, type = %s, value = %s, crossfit = %d WHERE id = %d", 
        $name,
        $description,
        $type,
        $value,
        $crossfit,
        $id
    )); 
    if ($query_response === false) { // error
        render_subscription_card($id, "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_subscription_card($id, "default-error");
        return;
    }
    render_subscription_card($id);
}

function add_subscription() {
    $name = isset($_POST["subscription_name"]) ? $_POST["subscription_name"] : "";
    $description = isset($_POST["subscription_description"]) ? $_POST["subscription_description"] : "";
    $type = isset($_POST["subscription_type"]) ? $_POST["subscription_type"] : "";
    $value = isset($_POST["subscription_value"]) ? $_POST["subscription_value"] : "";
    $crossfit = isset($_POST["subscription_crossfit"]) ? $_POST["subscription_crossfit"] == "on" : 0;
    $subscription = ["name" => $name, "description" => $description, "type" => $type, "value" => $value];

    if ($name == "" || $type == "" || $value == "") {
        render_subscription_list($subscription, "Það verður að fylla í nafn, tegund og gildi");
    }
    if ($type == "expiresbydate" && preg_match("/^\d{4}-\d{2}-\d{2}$/", $value) == false) {
        render_subscription_list($subscription, "Dagsetning þarf að vera á forminu: ÁÁÁÁ-MM-DD (t.d. 2016-02-28 fyrir tuttugasta og áttunda febrúar 2016).");
        return;
    }
    else if ($type == "expiresin" && preg_match("/^(\d{1,3})á(\d{1,3})m(\d{1,3})d$/", $value) == false) {
        render_subscription_list($subscription, "Gildistími þarf að vera á forminu: XáYmZd (t.d. 2á0m48d fyrir 2 ár, 0 mánuði og 48 daga).");
        return;
    }
    else if ($type == "count" && is_numeric($value) == false) {
        render_subscription_list($subscription, "Skipti getur bara verið tala.");
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'cf_subscription';
    $query_response = $wpdb->query( $wpdb->prepare(
        "INSERT INTO $table_name (name,description,type,value,crossfit) VALUES (%s,%s,%s,%s,%d)", 
        $name,
        $description,
        $type,
        $value,
        $crossfit
    )); 
    if ($query_response === false) { // error
        render_subscription_list($subscription, "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_subscription_list($subscription, "default-error");
        return;
    }
    render_subscription_list();
}

function delete_subscription() {
    if (!isset($_POST["subscription_id"])) {
        render_subscription_list([], "default-error");
        return;
    }
    $id = $_POST["subscription_id"];

    global $wpdb;
    $table_name = $wpdb->prefix . 'cf_subscription';
    $query_response = $wpdb->query( $wpdb->prepare(
        "DELETE FROM $table_name WHERE id = %d", 
        $id
    )); 
    if ($query_response === false) { // error
        render_subscription_list([], "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_subscription_list([], "default-error");
        return;
    }
    render_subscription_list();
}

function get_subscription_by_id($id) {
    global $wpdb;
    $row = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix."cf_subscription WHERE id = ".$id);
    if (!$row) {
        return [];
    } else {
        return $row;
    }
}

function type_en_to_is($type) {
    switch($type) {
        case "expiresbydate":
            return "Dagsetning";
        case "expiresin":
            return "Gildistími";
        case "count":
            return "Skipti";
    }
}
function type_is_to_en($type) {
    switch($type) {
        case "Dagsetning":
            return "expiresbydate";
        case "Gildistími":
            return "expiresin";
        case "Skipti":
            return "count";
    }
}
?>