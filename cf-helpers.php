<?php defined( 'ABSPATH' ) or die( 'Access Denied, get lost!' );

function cf_write_header($active) {
    echo '<div class="wrap">';
        echo '<ul class="nav nav-tabs" id="cf-550-navigation">';
            echo '<li '.($active=="dashboard" ? 'class="active"' : "" ).'><a href="'.CF550_ADMIN_URL.'cf550_dashboard">Crossfit550</a></li>';
            echo '<li '.($active=="members" ? 'class="active"' : "" ).'><a href="'.CF550_ADMIN_URL.'cf550_members">Iðkendur</a></li>';
            echo '<li '.($active=="programs" ? 'class="active"' : "" ).'><a href="'.CF550_ADMIN_URL.'cf550_programs">Æfingar</a></li>';
            echo '<li '.($active=="programtime" ? 'class="active"' : "" ).'><a href="'.CF550_ADMIN_URL.'cf550_programtime">Æfingatímar</a></li>';
            echo '<li '.($active=="attendance" ? 'class="active"' : "" ).'><a href="'.CF550_ADMIN_URL.'cf550_attendance">Mætingar</a></li>';
            echo '<li '.($active=="subscription" ? 'class="active"' : "" ).'><a href="'.CF550_ADMIN_URL.'cf550_subscription">Kort</a></li>';
            echo '<li '.($active=="purchase" ? 'class="active"' : "" ).'><a href="'.CF550_ADMIN_URL.'cf550_purchase">Kaup</a></li>';
        echo '</ul>';
}

function cf_write_footer() {
    echo '</div>';
}

function date_iso_to_pretty($iso) {
    $parts = [];
    preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $iso, $parts);
    if ($parts == []) return $iso;
    return $parts[3].".".$parts[2].".".$parts[1];
}

function date_pretty_to_iso($pretty) {
    $parts = [];
    preg_match("/^(\d{2})\.(\d{2})\.(\d{4})$/", $pretty, $parts);
    return $parts[3]."-".$parts[2]."-".$parts[1];
}

function validate_iso_date($iso) {
    return preg_match("/^\d{4}-\d{2}-\d{2}$/", $iso);
}

function validate_pretty_date($pretty) {
    return preg_match("/^\d{2}\.\d{2}\.\d{4}$/", $pretty);
}

function validate_ssn($ssn) {
    return preg_match("/^(\d{6})-?(\d{4})$/", $ssn);
}

function validate_time($time) {
    return preg_match("/^([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/", $time);
}

function format_ssn($ssn) {
    $parts = [];
    preg_match("/^(\d{6})-?(\d{4})$/", $ssn, $parts);
    return $parts[1]."-".$parts[2];
}

function get_latest_purchase($ssn) {
    global $wpdb;
    $newest_purchase = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix."cf_purchase WHERE member_ssn = '".$ssn."' ORDER BY date desc");
    return $newest_purchase;
}

function is_subscribed($ssn) {
    $newest_purchase = get_latest_purchase($ssn);
    if (!$newest_purchase) {
        return false;
    } else {
        return purchase_is_ongoing($newest_purchase);
    }
}

function purchase_is_ongoing($purchase, $subscription = null) {
    if ($purchase->frozen) {
        return false;
    } 
    global $wpdb;
    if ($subscription == null) {
        $subscription = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix."cf_subscription WHERE id = '".$purchase->subscription_id."'");
    }
    if (!$subscription)
        return false;
    if ($purchase->unfrozen == '0000-00-00') {
        switch ($subscription->type) {
            case 'expiresbydate':
                return $subscription->value >= date("Y-m-d");
            case 'expiresin':
                $parts = [];
                preg_match("/^(\d{1,3})á(\d{1,3})m(\d{1,3})d$/", $subscription->value, $parts);
                $expires = date("Y-m-d", strtotime($purchase->date." +".$parts[1]." years ".$parts[2]." months ".$parts[3]." days"));
                return $expires >= date("Y-m-d");
            case 'count':
                $result = $wpdb->get_row("SELECT COUNT(*) AS count FROM ".$wpdb->prefix."cf_attendance a LEFT JOIN ".$wpdb->prefix."cf_programs p ON a.program_id=p.id WHERE a.member_ssn = '".$purchase->member_ssn."' AND p.date >= '".$purchase->date."'");
                return $result->count < $subscription->value;
        }
    } else {
        $parts = [];
        if (preg_match("/^(\d{1,3})á(\d{1,3})m(\d{1,3})d$/", $purchase->frozen_remainder, $parts)) {
            $expires = date("Y-m-d", strtotime($purchase->unfrozen." +".$parts[1]." years ".$parts[2]." months ".$parts[3]." days"));
            return $expires >= date("Y-m-d");
        } elseif(preg_match("/^(\d{1,3}) skipti$/", $purchase->frozen_remainder, $parts)) {
            $result = $wpdb->get_row("SELECT COUNT(*) AS count FROM ".$wpdb->prefix."cf_attendance a LEFT JOIN ".$wpdb->prefix."cf_programs p ON a.program_id=p.id WHERE a.member_ssn = '".$purchase->member_ssn."' AND p.date >= '".$purchase->unfrozen."'");
                return $result->count < $parts[1];
        } else {
            return false;
        }
    }
    return false;
}

function get_subscription_name($id) {
    global $wpdb;
    $sub = $wpdb->get_row("SELECT name as name FROM " . $wpdb->prefix."cf_subscription WHERE id = ".$id);
    return $sub ? $sub->name : "";
}

function xor_strings($one, $two) {
    $o = (isset($one) && $one != "");
    $t = (isset($two) && $two != "");
    return ( ( $o || $t ) && !( $o && $t ) );
}

function get_members_with_purchases_and_subscription() {
    global $wpdb;
    $results = $wpdb->get_results( 
        "SELECT m.name as name, m.ssn as ssn, p.date as p_date, s.name as s_name, s.type as s_type, s.value as s_value 
        FROM {$wpdb->prefix}cf_members m 
        LEFT JOIN {$wpdb->prefix}cf_purchase p ON m.ssn = p.member_ssn 
        LEFT JOIN {$wpdb->prefix}cf_subscription s ON p.subscription_id = s.id 
        ORDER BY name asc"
    );
    return $results; 
}

function get_remainder_for_member_purchase($member) {
    global $wpdb;
    if (!$member->s_value)
        return "";
    switch ($member->s_type) {
        case 'expiresbydate':
            return $member->s_value;
        case 'expiresin':
            $parts = [];
            preg_match("/^(\d{1,3})á(\d{1,3})m(\d{1,3})d$/", $member->s_value, $parts);
            $expires = date("Y-m-d", strtotime($member->p_date." +".$parts[1]." years ".$parts[2]." months ".$parts[3]." days"));
            return $expires;
        case 'count':
            $result = $wpdb->get_row("SELECT COUNT(*) AS count FROM ".$wpdb->prefix."cf_attendance a LEFT JOIN ".$wpdb->prefix."cf_programs p ON a.program_id=p.id WHERE a.member_ssn = '".$member->ssn."' AND p.date >= '".$member->p_date."'");
            return '('.($member->s_value - $result->count).') skipti';
    }
    return false;
}

function bool_to_string($bool) {
    if ($bool == 1) {
        return "Já";
    } else {
        return "Nei";
    }
}

/*
 * Sun = 1
 * Mon = 2
 * Tue = 3
 * Wed = 4
 * Thu = 5
 * Fri = 6
 * Sat = 7
 */
function day_from_iso_date($date) {
    $datetime = date_create($date);
    $d = date_format($datetime,"D");
    switch ($d) {
        case 'Sun':
            return 1;
        case 'Mon':
            return 2;
        case 'Tue':
            return 3;
        case 'Wed':
            return 4;
        case 'Thu':
            return 5;
        case 'Fri':
            return 6;
        case 'Sat':
            return 7;
        default:
            return 0;
    }
}

function ice_day_from_iso_date($date) {
    $index = day_from_iso_date($date);
    switch ($index) {
        case '1':
            return "Sunnudagur";
        case '2':
            return "Mánudagur";
        case '3':
            return "Þriðjudagur";
        case '4':
            return "Miðvikudagur";
        case '5':
            return "Fimmtudagur";
        case '6':
            return "Föstudagur";
        case '7':
            return "Laugardagur";
        default:
            return "";
    }
}


//javascript_helpers

?>