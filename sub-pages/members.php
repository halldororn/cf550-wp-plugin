<?php defined( 'ABSPATH' ) or die( 'Access Denied, get lost!' );
///////////////////////
////   MEMBERS PAGE   ///
///////////////////////

function add_members_page() {
    $parent_slug = "cf550_dashboard";
	$page_title = "Iðkendur";
	$menu_title = "Iðkendur";
	$capability = "edit_others_posts"; // Editors, Admins, 
	$menu_slug = "cf550_members";
	$function = "write_members_page";
    add_submenu_page(
        $parent_slug,
        $page_title,
        $menu_title,
        $capability,
        $menu_slug,
        $function
    );
}
add_action( 'admin_menu', 'add_members_page' );


function write_members_page() {
    cf_write_header("members");
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if (isset( $_POST['update-member'])) {
            update_member();
        } else if (isset( $_POST['add-member'])) { 
            add_new_member();
        } else if (isset ($_POST["delete-member"])) {
            delete_member();
        } else if (isset( $_POST['new-password'])) { 
            new_password();
        }
    } else if ($_SERVER['REQUEST_METHOD'] == "GET") {
        if (isset($_GET["member_id"])) {
            render_member_card($_GET["member_id"]);
        } else {
            render_member_list();
        }
    }
    cf_write_footer();
}

function render_member_list($member = [], $error = "") {
    $value_name = isset($member["name"]) ? 'value="'.htmlspecialchars($member["name"]).'"' : null ; 
    $value_ssn = isset($member["ssn"]) ? 'value="'.htmlspecialchars($member["ssn"]).'"' : null ; 
    ?>
    <h2>Iðkendur</h2>
    <?php
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
    <form name="form_new_member" class="form_add" method="POST" onsubmit="return form_validation()" action=<?php echo esc_url( CF550_ADMIN_URL.'cf550_members' ) ?>>
        <h4>Nýr iðkandi</h4>
        Nafn: <input type="text" id="member_name" name="member_name" <?php echo $value_name; ?> />
        Kennitala: <input type="text" id="member_ssn" name="member_ssn" <?php echo $value_ssn; ?> />
        <input type="submit" name="add-member" value="Bæta við" class="btn btn-default"/>
    </form>

    <script type="text/javascript">
        function form_validation() {
            /* Check the member Name for blank submission*/
            var member_name = document.forms["form_new_member"]["member_name"].value;
            if (member_name == "" || member_name == null) {
                document.getElementById("member_name").style.border = "1px solid red";
                return false;
            }

            /* Check the member ssn for invalid format */
            var member_ssn = document.forms["form_new_member"]["member_ssn"].value;
            if (!/^\d{6}-?\d{4}$/.test(member_ssn)) {
                document.getElementById("member_ssn").style.border = "1px solid red";
                return false;
            }
        }
        function check_delete(memberid) {
            var checkbox = document.getElementById("check-"+memberid);
            return checkbox.checked;
        }
        function check_password(memberid) {
            var password_box = document.getElementById("password-"+memberid);
            if (password_box.value.length == 0) {
                password_box.style.border = "1px solid red";
            }
            return password_box.value.length > 0;
        }
    </script>
    <table class="cf-table">
        <tr>
            <th>Nafn</th>
            <th>Kennitala</th>
            <th>Stofnaður</th>
            <th>Lykilorð?</th>
            <th>Aðgerð</th>
        </tr>
        <?php
            global $wpdb;
            $result = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix."cf_members".' ORDER BY name asc');
            foreach ($result as $r) {
                    echo "<tr>";
                    echo "<td>".htmlspecialchars($r->name)."</td>";
                    echo "<td>".htmlspecialchars($r->ssn)."</td>";
                    echo "<td>".htmlspecialchars($r->created)."</td>";
                    if ($r->access_hash == false) {
                        echo "<td></td>";
                    } else {
                        echo "<td>Já</td>";
                    }
                    echo '<td>
                            <form method="POST" action="'.esc_url(CF550_ADMIN_URL.'cf550_members').'" onsubmit="return check_delete('.$r->id.')">
                                <input type="hidden" name="member_id" value="'.$r->id.'" />
                                <input id="check-'.$r->id.'" type="checkbox" name="allow_delete"/>
                                <input type="submit" name="delete-member" value="Eyða Iðkanda" class="btn btn-default"/>
                                <a class="btn btn-default" href='.esc_url(CF550_ADMIN_URL.'cf550_members').'&member_id='.$r->id.'>Breyta</a>
                            </form>
                            <form class="password-form" method="POST" action="'.esc_url(CF550_ADMIN_URL.'cf550_members').'" onsubmit="return check_password('.$r->id.')">
                                <input type="hidden" name="member_id" value="'.$r->id.'" />
                                <input type="password" name="member_password" placeholder="Lykilorð" id="password-'.$r->id.'" />
                                <input type="submit" name="new-password" value="Nýtt lykilorð" class="btn btn-default"/>
                            </form>
                        </td>';
                echo "</tr>";
            }
        ?>
    </table>
    <?php
}

function render_member_card($member_id, $error = "") {
    if (!is_numeric($member_id)) {
        echo "<h4>Enginn iðkandi er til með þetta #</h4>";
        echo "<h4>Smelltu <a href=".esc_url(CF550_ADMIN_URL.'cf550_members').">hér</a> til að fara til baka</h4>";
    }
    else {
        $member = get_member_by_id($member_id);
        if ($member == []) {
            echo "<h4>Enginn iðkandi er til með þetta #</h4>";
            echo "<h4>Smelltu <a href=".esc_url(CF550_ADMIN_URL.'cf550_members').">hér</a> til að fara til baka</h4>";
            return;
        }
        if($error != "") 
            {
                if($error != "default-error") {
                    echo '<h3 class="error">'.htmlspecialchars($error).'</h3>';
                } else {
                    echo '<h3 class="error">Eitthvað fór úrskeiðis, ekki tókst að uppfæra iðkanda</h3>';
                }
            }
        ?>
        <h3>Breyta iðkanda</h3>
        <form method="POST" action=<?php echo esc_url( CF550_ADMIN_URL.'cf550_members' ) ?>>
            <div class="cf-input-div">
                <label for="member_id">#</label>
                <input readonly="readonly" type="text" name="member_id" id="member_id" value="<?php echo htmlspecialchars($member->id); ?>">
            </div>
            <div class="cf-input-div">
                <label for="member_name">Nafn</label>
                <input type="text" name="member_name" id="member_name" value="<?php echo htmlspecialchars($member->name); ?>">
            </div>
            <div class="cf-input-div">
                <label for="member_ssn">Kennitala</label>
                <input type="text" name="member_ssn" id="member_ssn" value="<?php echo htmlspecialchars($member->ssn); ?>">
            </div>
            <div class="cf-input-div">
                <label for="member_created">Stofnaður</label>
                <input readonly="readonly" type="text" name="member_created" id="member_created" value="<?php echo htmlspecialchars($member->created); ?>">
            </div>
            <input class="btn btn-default" type="submit" name="update-member" value="Staðfesta">
            <a href="<?php echo esc_url(CF550_ADMIN_URL."cf550_members")?>">Til baka</a>
        </form>
        <?php
    }
}

function update_member() {
    if (!isset($_POST["member_id"])) {
        render_member_list([], "default-error");
        return;
    }
    $id = $_POST["member_id"];
    $name = $_POST["member_name"];
    $ssn = $_POST["member_ssn"];

    //check the ssn and get the two parts:
    $result_array = [];
    preg_match("/^(\d{6})-?(\d{4})$/", $ssn, $result_array);
    if (empty($result_array)) {
        render_member_card($id, "Kennitala þarf að vera á forminu 012345-6789 eða 0123456789");
        return;
    }
    $pretty_ssn = $result_array[1]."-".$result_array[2];

    global $wpdb;
    $table_name = $wpdb->prefix . 'cf_members';
    $query_response = $wpdb->query( $wpdb->prepare(
        "UPDATE $table_name SET name = %s, ssn = %s WHERE id = %s", 
        $name, 
        $pretty_ssn, 
        $id
    )); 
    if ($query_response === false) { // error
        render_member_card($id, "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_member_card($id, "default-error");
        return;
    }
    render_member_card($id);
}

function add_new_member() {
    if (!isset($_POST["member_name"]) || !isset($_POST["member_ssn"])) {
        render_member_list([], "default-error");
        return;
    }
    $name = $_POST["member_name"];
    $ssn = $_POST["member_ssn"];
    $created = date("Y-m-d");

    //check the ssn and get the two parts:
    $result_array = [];
    preg_match("/^(\d{6})-?(\d{4})$/", $ssn, $result_array);
    if (empty($result_array)) {
        render_member_list(["name" => $name, "ssn" => $ssn], "Kennitala þarf að vera á forminu 012345-6789 eða 0123456789");
        return;
    }
    $pretty_ssn = $result_array[1]."-".$result_array[2];

    global $wpdb;
    $table_name = $wpdb->prefix . 'cf_members';
    $query_response = $wpdb->query( $wpdb->prepare(
        "INSERT INTO $table_name (name,ssn,created) VALUES (%s,%s,%s)", 
        $name, 
        $pretty_ssn, 
        $created
    )); 
    if ($query_response === false) { // error
        render_member_list(["name" => $name, "ssn" => $ssn], "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_member_list(["name" => $name, "ssn" => $ssn], "default-error");
        return;
    }
        render_member_list();
}

function delete_member() {
    if (!isset($_POST["member_id"])) {
        render_member_list([], "default-error");
        return;
    }
    $id = $_POST["member_id"];

    global $wpdb;
    $table_name = $wpdb->prefix . 'cf_members';
    $query_response = $wpdb->query( $wpdb->prepare(
        "DELETE FROM $table_name WHERE id = %d", 
        $id
    )); 
    if ($query_response === false) { // error
        render_member_list([], "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_member_list([], "default-error");
        return;
    }
    render_member_list();
}

function new_password() {
    if (!isset($_POST["member_id"]) || !isset($_POST["member_password"]) || empty($_POST["member_password"])) {
        render_member_list([], "default-error");
        return;
    }
    $id = $_POST["member_id"];
    $password = $_POST["member_password"];

    global $wpdb;
    $table_name = $wpdb->prefix . 'cf_members';
    $member = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = $id" );
    if ($member == null) {
        render_member_list([], "default-error");
        return;
    }
    $access_hash = md5($password.$member->ssn);

    $query_response = $wpdb->query( $wpdb->prepare(
        "UPDATE $table_name SET access_hash=%s WHERE id = %d",
        $access_hash,
        $id
    )); 

    if ($query_response === false) { // error
        render_member_list([], "default-error");
        return;
    }
    if ($query_response === 0) { // no affected rows
        render_member_list([], "default-error");
        return;
    }
    render_member_list();
}

function get_member_by_id($id) {
    global $wpdb;
    $row = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix."cf_members WHERE id = ".$id);
    if (!$row) {
        return [];
    } else {
        return $row;
    }
}

function cf_debug($message) {
    echo '<script>console.log("'.$message.'");</script>';
}
?>
