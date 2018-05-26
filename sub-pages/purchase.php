<?php defined( 'ABSPATH' ) or die( 'Access Denied, get lost!' );
//////////////////////////
////   PURCHASE PAGE   ///
//////////////////////////

function add_purchase_page() {
    $parent_slug = "cf550_dashboard";
	$page_title = "Kaup";
	$menu_title = "Kaup";
	$capability = "edit_others_posts"; // Editors, Admins, 
	$menu_slug = "cf550_purchase";
	$function = "write_purchase_page";
    add_submenu_page(
        $parent_slug,
        $page_title,
        $menu_title,
        $capability,
        $menu_slug,
        $function
    );
}
add_action( 'admin_menu', 'add_purchase_page' );

function write_purchase_page() {
    cf_write_header("purchase");
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if (isset( $_POST['add-purchase'])) {
            add_purchase();
        } else if (isset( $_POST['delete-purchase'])) { 
            delete_purchase();
        } else if (isset( $_POST['update-purchase'])) { 
            update_purchase();
        }
    } else if ($_SERVER['REQUEST_METHOD'] == "GET") {
        if (isset($_GET["purchase_id"])) {
            render_purchase_card($_GET["purchase_id"]);
        } else {
            render_purchase_list();
        }
    }
    cf_write_footer();
}

function render_purchase_list($purchase = [], $error = "") {
    $value_ssn = isset($purchase["ssn"]) ? 'value="'.htmlspecialchars($purchase["ssn"]).'"' : null ; 
    $value_subscription_id = isset($purchase["subscription_id"]) ? 'value="'.htmlspecialchars($purchase["subscription_id"]).'"' : null ; 
    $value_date = isset($purchase["date"]) ? 'value="'.htmlspecialchars($purchase["date"]).'"' : null ; 
    echo "<h2>Kaup</h2>";
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
    <form name="form_new_purchase" class="form_add" method="POST" onsubmit="return form_validation()" action=<?php echo esc_url( CF550_ADMIN_URL.'cf550_purchase' ) ?>>
        <h4>Ný kaup á korti</h4>
        Kennitala: <input type="text" id="ssn" name="ssn" <?php echo $value_ssn; ?> />
        Korta #: <input type="number" id="subscription_id" name="subscription_id"/>
        Dagsetning kaupa: <input type="text" id="date" name="date" placeholder="ÁÁÁÁ-MM-DD" <?php echo $value_date; ?> />
        <input type="submit" name="add-purchase" value="Bæta við" class="btn btn-default"/>
    </form>

    <script type="text/javascript">
        function form_validation() {
            /* Check the member ssn for invalid format */
            var ssn = document.forms["form_new_purchase"]["ssn"].value;
            if (!/^\d{6}-?\d{4}$/.test(ssn)) {
                document.getElementById("ssn").style.border = "1px solid red";
                return false;
            }
            document.getElementById("date").style.border = "";
            /* Check the subscription id for blank submission*/
            var subscription_id = document.forms["form_new_purchase"]["subscription_id"].value;
            if (subscription_id == null || subscription_id == "") {
                document.getElementById("subscription_id").style.border = "1px solid red";
                return false;
            }
            document.getElementById("subscription_id").style.border = "";
            /* Make sure the date is in the correct format */
            var date = document.forms["form_new_purchase"]["date"].value;
            if (! /^(\d{4})-(\d{2})-(\d{2})$/.test(date)) {
                document.getElementById("validation-errors").innerText = "Dagsetning þarf að vera á forminu: ÁÁÁÁ-MM-DD (t.d. 2016-02-28 fyrir tuttugasta og áttunda febrúar 2016).";
                document.getElementById("date").style.border = "1px solid red";
                return false;
            }
            document.getElementById("date").style.border = "";
        }
        function check_delete(purchaseid) {
            var checkbox = document.getElementById("check-"+purchaseid);
            return checkbox.checked;
        }
    </script>
    <table class="cf-table">
        <tr>
            <th>Kennitala</th>
            <th>Nafn</th>
            <th>Korta #</th>
            <th>Korta nafn</th>
            <th>Dagsetning kaupa</th>
            <th>Frosið</th>
            <th>Eftirstöðvar frystingar</th>
            <th>Endurvirkjað</th>
            <th>Aðgerð</th>
        </tr>
        <?php
            global $wpdb;
            $result = $wpdb->get_results('SELECT p.member_ssn as member_ssn, p.subscription_id as subscription_id, p.date as date, p.id as id, m.name as member_name, s.name as subscription_name, p.frozen as frozen, p.frozen_remainder as frozen_remainder, p.unfrozen as unfrozen FROM ' . $wpdb->prefix.'cf_purchase p LEFT JOIN '.$wpdb->prefix.'cf_members m ON p.member_ssn=m.ssn LEFT JOIN '.$wpdb->prefix.'cf_subscription s ON p.subscription_id=s.id ORDER BY p.id desc');
            foreach ($result as $r) {
                    echo "<tr>";
                    echo "<td>".htmlspecialchars($r->member_ssn)."</td>";
                    echo "<td>".htmlspecialchars($r->member_name)."</td>";
                    echo '<td>'.htmlspecialchars($r->subscription_id)."</td>";
                    echo '<td>'.htmlspecialchars($r->subscription_name)."</td>";
                    echo '<td>'.htmlspecialchars($r->date)."</td>";
                    echo '<td>'.bool_to_string($r->frozen)."</td>";
                    echo '<td>'.htmlspecialchars($r->frozen_remainder)."</td>";
                    echo '<td>'.htmlspecialchars($r->unfrozen)."</td>";
                    echo '<td>
                            <form method="POST" action="'.esc_url(CF550_ADMIN_URL.'cf550_purchase').'" onsubmit="return check_delete('.$r->id.')">
                                <input type="hidden" name="purchase_id" value="'.$r->id.'" />
                                <input id="check-'.$r->id.'" type="checkbox" name="allow_delete"/>
                                <input type="submit" name="delete-purchase" value="Eyða Kaupum" class="btn btn-default"/>
                                <a class="btn btn-default" href='.esc_url(CF550_ADMIN_URL.'cf550_purchase').'&purchase_id='.$r->id.'>Breyta</a>
                            </form>
                        </td>';
                echo "</tr>";
            }
        ?>
    </table>
    <?php
}

function render_purchase_card($id, $error = "") {
    if (!is_numeric($id)) {
        echo "<h4>Engin kaup eru til með þetta #</h4>";
        echo "<h4>Smelltu <a href=".esc_url(CF550_ADMIN_URL.'cf550_purchase').">hér</a> til að fara til baka</h4>";
    }
    else {
        $purchase = get_purchase_by_id($id);
        if ($purchase == []) {
            echo "<h4>Engin kaup eru til með þetta #</h4>";
            echo "<h4>Smelltu <a href=".esc_url(CF550_ADMIN_URL.'cf550_purchase').">hér</a> til að fara til baka</h4>";
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
        <h3>Breyta kaupum</h3>
        <form method="POST" action=<?php echo esc_url( CF550_ADMIN_URL.'cf550_purchase' ) ?>>
            <div class="cf-input-div">
                <label for="purchase_id">#</label>
                <input readonly="readonly" type="text" name="purchase_id" id="purchase_id" value="<?php echo htmlspecialchars($purchase->id); ?>">
            </div>
            <div class="cf-input-div">
                <label for="ssn">Kennitala</label>
                <input type="text" name="ssn" id="ssn" value="<?php echo htmlspecialchars($purchase->member_ssn); ?>">
            </div>
            <div class="cf-input-div">
                <label>Nafn</label>
                <input readonly="readonly" type="text" value="<?php echo htmlspecialchars($purchase->member_name); ?>">
            </div>
            <div class="cf-input-div">
                <label for="subscription_id">Korta #</label>
                <input type="text" name="subscription_id" id="subscription_id" value="<?php echo htmlspecialchars($purchase->subscription_id); ?>">
            </div>
            <div class="cf-input-div">
                <label>Korta nafn</label>
                <input readonly="readonly" type="text" value="<?php echo htmlspecialchars($purchase->subscription_name); ?>">
            </div>
            <div class="cf-input-div">
                <label for="date">Dagsetning</label>
                <input type="text" name="date" id="date" value="<?php echo htmlspecialchars($purchase->date); ?>">
            </div>
            <div class="cf-input-div">
                <label for="frozen">Frosið</label>
                <input type="checkbox" name="frozen" id="frozen" <?php echo ($purchase->frozen == 1 ?  'checked':'') ?>>
            </div>
            <div class="cf-input-div">
                <label for="frozen_remainder">Eftirstöðvar frystingar</label>
                <input readonly="readonly" type="text" name="frozen_remainder" id="frozen_remainder" value="<?php echo htmlspecialchars($purchase->frozen_remainder); ?>">
            </div>
            <div class="cf-input-div">
                <label for="unfrozen">Endurvirkjað</label>
                <input readonly="readonly" type="text" name="unfrozen" id="unfrozen" value="<?php echo htmlspecialchars($purchase->unfrozen); ?>">
            </div>
            <input class="btn btn-default" type="submit" name="update-purchase" value="Staðfesta">
            <a href="<?php echo esc_url(CF550_ADMIN_URL."cf550_purchase")?>">Til baka</a>
        </form>
        <?php
    }
}

function update_purchase() {
    if (!isset($_POST["purchase_id"])) {
        render_purchase_list([], "default-error");
        return;
    }
    $id = $_POST["purchase_id"];
    $ssn = isset($_POST["ssn"]) ? $_POST["ssn"] : "";
    $date = isset($_POST["date"]) ? $_POST["date"] : "";
    $frozen = isset($_POST["frozen"]) ? $_POST["frozen"] == "on" : 0;
    $unfrozen = isset($_POST["unfrozen"]) ? $_POST["unfrozen"] : "";
    $subscription_id = isset($_POST["subscription_id"]) ? $_POST["subscription_id"] : "";
    $frozen_remainder = isset($_POST["frozen_remainder"]) ? $_POST["frozen_remainder"] : "";

    // check the date if it exists
    if (validate_iso_date($date) == false || validate_iso_date($unfrozen) == false) {
        render_purchase_card($id, "Dagsetning þarf að vera á forminu: ÁÁÁÁ-MM-DD (t.d. 2016-02-28 fyrir tuttugasta og áttunda febrúar 2016).");
        return;
    }
    // check that the subscription exists
    global $wpdb;
    $subscription = $wpdb->get_row( $wpdb->prepare(
        'SELECT id FROM '. $wpdb->prefix."cf_subscription".' WHERE id = %d',
        $subscription_id
    ));
    if (!$subscription) {
        render_purchase_card($id, "Ekkert kort finnst með þetta #");
        return;
    }
    $old_purchase = get_purchase_by_id($id);
    // Calculate a new frozen state if it changed
    $old_frozen_state = $old_purchase->frozen;
    if ($old_frozen_state != $frozen) {
        if ($frozen) {
            $frozen_remainder = calculate_purchase_remainder_when_frozen($old_purchase);
        } else {
            $unfrozen = date("Y-m-d");
        }
    }
    //check the ssn and get the two parts:
    $result_array = [];
    preg_match("/^(\d{6})-?(\d{4})$/", $ssn, $result_array);
    if (empty($result_array)) {
        render_purchase_card($id, "Kennitalan þarf að vera á forminu 123456-1234 eða 1234561234");
        return;
    }
    $ssn_pretty = $result_array[1]."-".$result_array[2];
    $result = $wpdb->query( $wpdb->prepare(
        'SELECT id FROM '. $wpdb->prefix."cf_members".' WHERE ssn = %s',
        $ssn_pretty
    ));
    if ($wpdb->num_rows <= 0) {
        render_purchase_card($id, "Enginn iðkandi finnst með þessa kennitölu");
        return;
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'cf_purchase';
    $query_response = $wpdb->query( $wpdb->prepare(
        "UPDATE $table_name SET member_ssn = %s, date = %s, subscription_id = %d, frozen = %d, frozen_remainder = %s, unfrozen = %s WHERE id = %d", 
        $ssn_pretty,
        $date,
        $subscription_id,
        $frozen,
        $frozen_remainder,
        $unfrozen,
        $id
    )); 
    if ($query_response === false) { // error
        render_purchase_card($id, "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_purchase_card($id, "default-error");
        return;
    }
    render_purchase_card($id);
}

function add_purchase() {
    $ssn = isset($_POST["ssn"]) ? $_POST["ssn"] : "";
    $date = isset($_POST["date"]) ? $_POST["date"] : "";
    $subscription_id = isset($_POST["subscription_id"]) ? $_POST["subscription_id"] : "";

    // check the date if it exists
    if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $date) == false) {
        render_purchase_list([], "Dagsetning þarf að vera á forminu: ÁÁÁÁ-MM-DD (t.d. 2016-02-28 fyrir tuttugasta og áttunda febrúar 2016).");
        return;
    }
    // check that the subscription exists
    global $wpdb;
    $result = $wpdb->query( $wpdb->prepare(
        'SELECT id FROM '. $wpdb->prefix."cf_subscription".' WHERE id = %d',
        $subscription_id
    ));
    if ($wpdb->num_rows <= 0) {
        render_purchase_list(["ssn" => $ssn, "date" => $date, "subscription_id" => $subscription_id], "Ekkert kort finnst með þetta #");
        return;
    }
    //check the ssn and get the two parts:
    $result_array = [];
    preg_match("/^(\d{6})-?(\d{4})$/", $ssn, $result_array);
    if (empty($result_array)) {
        render_purchase_list(["ssn" => $ssn, "date" => $date, "subscription_id" => $subscription_id], "Kennitalan þarf að vera á forminu 123456-1234 eða 1234561234");
        return;
    }
    $ssn_pretty = $result_array[1]."-".$result_array[2];
    $result = $wpdb->query( $wpdb->prepare(
        'SELECT id FROM '. $wpdb->prefix."cf_members".' WHERE ssn = %s',
        $ssn_pretty
    ));
    if ($wpdb->num_rows <= 0) {
        render_purchase_list(["ssn" => $ssn, "date" => $date, "subscription_id" => $subscription_id], "Enginn iðkandi finnst með þessa kennitölu");
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'cf_purchase';
    $query_response = $wpdb->query( $wpdb->prepare(
        "INSERT INTO $table_name (member_ssn,date,subscription_id) VALUES (%s,%s,%d)", 
        $ssn_pretty,
        $date,
        $subscription_id
    )); 
    if ($query_response === false) { // error
        render_purchase_list(["ssn" => $ssn, "date" => $date, "subscription_id" => $subscription_id], "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_purchase_list(["ssn" => $ssn, "date" => $date, "subscription_id" => $subscription_id], "default-error");
        return;
    }
    render_purchase_list();
}

function delete_purchase() {
    if (!isset($_POST["purchase_id"])) {
        render_purchase_list([], "default-error");
        return;
    }
    $id = $_POST["purchase_id"];

    global $wpdb;
    $table_name = $wpdb->prefix . 'cf_purchase';
    $query_response = $wpdb->query( $wpdb->prepare(
        "DELETE FROM $table_name WHERE id = %d", 
        $id
    )); 
    if ($query_response === false) { // error
        render_purchase_list([], "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_purchase_list([], "default-error");
        return;
    }
    render_purchase_list();
}

function get_purchase_by_id($id) {
    global $wpdb;
    $row = $wpdb->get_row('SELECT p.member_ssn as member_ssn, p.subscription_id as subscription_id, p.date as date, p.id as id, m.name as member_name, s.name as subscription_name, p.frozen as frozen, p.frozen_remainder as frozen_remainder, p.unfrozen as unfrozen FROM ' . $wpdb->prefix.'cf_purchase p LEFT JOIN '.$wpdb->prefix.'cf_members m ON p.member_ssn=m.ssn LEFT JOIN '.$wpdb->prefix.'cf_subscription s ON p.subscription_id=s.id WHERE p.id = '.$id);
    if (!$row) {
        return [];
    } else {
        return $row;
    }
}

function calculate_purchase_remainder_when_frozen($purchase) {
    global $wpdb;
    $subscription = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix."cf_subscription WHERE id = '".$purchase->subscription_id."'");
    if (!$subscription)
        return "";
    switch ($subscription->type) {
        case 'expiresbydate':
            $svalue = new DateTime($subscription->value);
            return datediff_to_ice(date_diff(date_create('now'), $svalue));
        case 'expiresin':
            $parts = [];
            preg_match("/^(\d{1,3})á(\d{1,3})m(\d{1,3})d$/", $subscription->value, $parts);
            $expires = date_create($purchase->date." +".$parts[1]." years ".$parts[2]." months ".$parts[3]." days");
            return datediff_to_ice(date_create('now')->diff($expires));
        case 'count':
            $result = $wpdb->get_row("SELECT COUNT(*) AS count FROM ".$wpdb->prefix."cf_attendance a LEFT JOIN ".$wpdb->prefix."cf_programs p ON a.program_id=p.id WHERE a.member_ssn = '".$purchase->member_ssn."' AND p.date >= '".$purchase->date."'");
            return "".($subscription->value-$result->count)." skipti";
    }
    return false;
}

function datediff_to_ice($datediff) {
    $dd = $datediff;
    return ''.$dd->y.'á'.$dd->m.'m'.$dd->d.'d';
}
?>