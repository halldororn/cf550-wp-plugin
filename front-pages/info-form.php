<?php defined( 'ABSPATH' ) or die( 'Access Denied, get lost!' );
//////////////////////////////////
////   Information Form Page   ///
//////////////////////////////////

function html_form() {
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        cf_form_post();
    }

    if ($_SERVER['REQUEST_METHOD'] == "GET") {
        cf_form_get();
    }
}

add_shortcode( 'get_member_info_form', 'html_form' );

function cf_form_post() {
    $member_ssn = isset($_POST["member_ssn"]) ? htmlspecialchars($_POST["member_ssn"]): null ; 
    $member_password = isset($_POST["member_password"]) ? htmlspecialchars($_POST["member_password"]) : null ; 
    if ($member_ssn == null) {
        cf_form_get($member_ssn);
        return;
    }

    //check the ssn and get the two parts:
    $result_array = [];
    preg_match("/^(\d{6})-?(\d{4})$/", $member_ssn, $result_array);
    if (empty($result_array)) {
        cf_form_get($member_ssn, "Kennitala ekki á réttu formi");
        return;
    }
    $member_ssn = $result_array[1]."-".$result_array[2];

    global $wpdb;
    $table_name = $wpdb->prefix . 'cf_members';
    $member = $wpdb->get_row( "SELECT * FROM $table_name WHERE ssn = '$member_ssn'" );
    if ($member == null) {
        cf_form_get($member_ssn, "Kennitala ekki skráð í kerfinu");
        return;
    }

    $purchase = get_latest_purchase($member_ssn);
    $purchase_valid = empty($purchase) ? false : purchase_is_ongoing($purchase);
    $subscription_name = empty($purchase) ? "Þú hefur ekki enn keypt kort" : get_subscription_name($purchase->subscription_id);
    $purchase_remainder = $purchase_valid ? get_purchase_remainder($purchase) : (empty($purchase) ? "" : "Kortið þitt er útrunnið");
    $attendance_made = false;
    if ($purchase_valid) {
        $attendance_made = mark_attendance_now($member_ssn);
    }
    
    $now = getdate();
    $attendance_all = $wpdb->get_var(
        "SELECT COUNT(*) ".
        "FROM ".$wpdb->prefix."cf_attendance a ".
        "LEFT JOIN ".$wpdb->prefix."cf_programs p on a.program_id=p.id ".
        "WHERE a.member_ssn = '".$member_ssn."' AND a.date <= '".$now["year"]."-".$now["mon"]."-".$now["mday"]."'");
    $attendance_year = $wpdb->get_var(
        "SELECT COUNT(*) ".
        "FROM ".$wpdb->prefix."cf_attendance a ".
        "LEFT JOIN ".$wpdb->prefix."cf_programs p on a.program_id=p.id ".
        "WHERE a.member_ssn = '".$member_ssn."' AND a.date >= '".$now["year"]."-01-01'");
    $attendance_month = $wpdb->get_var(
        "SELECT COUNT(*) ".
        "FROM ".$wpdb->prefix."cf_attendance a ".
        "LEFT JOIN ".$wpdb->prefix."cf_programs p on a.program_id=p.id ".
        "WHERE a.member_ssn = '".$member_ssn."' AND a.date >= '".$now["year"]."-".$now["mon"]."-01'");
    
    echo '<p>Góðann daginn <b>'.$member->name.'</b>!</p>';
    if ($purchase_valid && $attendance_made) {
        echo '<p><strong>Merkt hefur verið við mætingu í dag</strong></p>';
    } else if ($purchase_valid && !$attendance_made) {
        echo '<p><strong>Þegar hefur verið merkt við mætingu hjá þér í dag</strong></p>';
    }
    echo '<p>Þú hefur mætt:</p>';
    echo '<p><b>'.$attendance_month.'</b> '.sinniorsinnum($attendance_month).' í þessum mánuði</p>';
    echo '<p><b>'.$attendance_year.'</b> '.sinniorsinnum($attendance_year).' á þessu ári</p>';
    echo '<p><b>'.$attendance_all."</b> ".sinniorsinnum($attendance_all).' síðan þú byrjaðir </p>';
    echo '<p>Staða korts: <strong>'.$subscription_name.' - '.$purchase_remainder.'</strong></p>';
    echo '<p>Þessi síða lokast eftir: <strong><span id="timer"></span></strong></p>';
    ?>
    <script>
        var refreshTime = 15; // 15 seconds
        var endTime = Date.parse(new Date()) + refreshTime*1000;
        setInterval(function() {
            var t = endTime - Date.parse(new Date());
            var seconds = Math.floor( (t/1000) % 60 );
            document.getElementById("timer").innerHTML = seconds + " sekúndur";
        }, 1000)
        setTimeout(function() {
            window.location.href = <?php echo '"'.$_SERVER["HTTP_REFERER"].'"';?>;
        }, refreshTime*1000)
    </script>
    <?php
}

function cf_form_get($member_ssn = "", $error_message = "") {
    $value_ssn = 'value="'.htmlspecialchars($member_ssn).'"';
    ?>
    <h5 style="color: red">
        <?php 
        if ($error_message != null) {
            echo htmlspecialchars($error_message);
        }
        ?>
    </h5>
	<form name="form_get_info" method="POST" onsubmit="return form_validation()" action=<?php echo '"'.$_SERVER['REQUEST_URI'].'"' ?> id="get-info">
        Kennitala: <input autofocus type="text" id="member_ssn" name="member_ssn" placeholder="123456-1234" <?php echo $value_ssn;?> onfocus="var temp_value=this.value; this.value=''; this.value=temp_value"/>
        <br>
        <br>
        <input type="submit" id="submit-get-info" name="submit-get-info-form" value="Sjá stöðu"/>
    </form>
    <script>
        function form_validation() {
            /* Check the member ssn for invalid format */
            var member_ssn = document.forms["form_get_info"]["member_ssn"].value;
            if (!/^\d{6}-?\d{4}$/.test(member_ssn)) {
                document.getElementById("member_ssn").style.border = "1px solid red";
                return false;
            }
            document.getElementById("member_ssn").style.border = "";
        }
    </script>
    <?php
}

function sinniorsinnum($num) {
    if ($num % 10 == 1) {
        return "sinni";
    } else {
        return "sinnum";
    }
}

function get_purchase_remainder($purchase) {
    global $wpdb;
    $subscription = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix."cf_subscription WHERE id = '".$purchase->subscription_id."'");
    if (!$subscription)
        return "";
    if ($purchase->unfrozen == '0000-00-00') {
        switch ($subscription->type) {
            case 'expiresbydate':
                return "Rennur út: ".date_iso_to_pretty($subscription->value);
            case 'expiresin':
                $parts = [];
                preg_match("/^(\d{1,3})á(\d{1,3})m(\d{1,3})d$/", $subscription->value, $parts);
                $expires = date("Y-m-d", strtotime($purchase->date." +".$parts[1]." years ".$parts[2]." months ".$parts[3]." days"));
                return "Rennur út: ".date_iso_to_pretty($expires);
            case 'count':
                $result = $wpdb->get_row("SELECT COUNT(*) AS count FROM ".$wpdb->prefix."cf_attendance a LEFT JOIN ".$wpdb->prefix."cf_programs p ON a.program_id=p.id WHERE a.member_ssn = '".$purchase->member_ssn."' AND p.date >= '".$purchase->date."'");
                return "Þú átt ".($subscription->value-$result->count)." skipti eftir";
        }
    } else {
        $parts = [];
        if (preg_match("/^(\d{1,3})á(\d{1,3})m(\d{1,3})d$/", $purchase->frozen_remainder, $parts)) {
            $expires = date("Y-m-d", strtotime($purchase->unfrozen." +".$parts[1]." years ".$parts[2]." months ".$parts[3]." days"));
            return "Rennur út: ".date_iso_to_pretty($expires);
        } elseif(preg_match("/^(\d{1,3}) skipti$/", $purchase->frozen_remainder, $parts)) {
            $result = $wpdb->get_row("SELECT COUNT(*) AS count FROM ".$wpdb->prefix."cf_attendance a LEFT JOIN ".$wpdb->prefix."cf_programs p ON a.program_id=p.id WHERE a.member_ssn = '".$purchase->member_ssn."' AND p.date >= '".$purchase->unfrozen."'");
            return "Þú átt ".($parts[1]-$result->count)." skipti eftir";
        }
    }
    return false;
}

function mark_attendance_now($ssn) {
    global $wpdb;
    //has the member already marked attendance today ?
    $wpdb->get_row("SELECT * FROM " . $wpdb->prefix."cf_attendance a INNER JOIN ".$wpdb->prefix."cf_programs p ON a.program_id=p.id WHERE a.member_ssn = '".$ssn."' AND p.date = '".date("Y-m-d")."'");
    if ($wpdb->num_rows != 0) return false;
    $wpdb->get_row("SELECT * FROM " . $wpdb->prefix."cf_attendance WHERE member_ssn = '".$ssn."' AND date = '".date("Y-m-d")."'");
    if ($wpdb->num_rows != 0) return false;
    //is there a program today ?
    $program = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix."cf_programs WHERE date = '".date("Y-m-d")."'");
    $program_id = !empty($program) ? $program->id : 0;

    $query_response = $wpdb->query( $wpdb->prepare(
        "INSERT INTO ".$wpdb->prefix."cf_attendance (program_id,day,date,time,member_ssn) VALUES (%d,%s,%s,%s,%s)", 
        $program_id, 
        ice_day_from_iso_date(date("Y-m-d")),
        date("Y-m-d"),
        date("H:i:s"),
        $ssn
    )); 
    return true;
}

?>