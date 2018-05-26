<?php defined( 'ABSPATH' ) or die( 'Access Denied, get lost!' );
///////////////////////
////   ADMIN PAGE   ///
///////////////////////
add_action( 'admin_menu', 'add_plugin_menu' );

function add_plugin_menu() {
	$page_title = "Crossfit550 Stillingar";
	$menu_title = "Crossfit550";
	$capability = "edit_others_posts"; // Editors, Admins, 
	$menu_slug = "cf550_dashboard";
	$function = "write_dashboard_page";
	$icon_url = "";
	$position = "";
	add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function);
}

function write_dashboard_page() {
    cf_write_header("dashboard");
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if (isset( $_POST['update-database'])) {
            update_database();
        }
	} else if ($_SERVER['REQUEST_METHOD'] == "GET") {
        render_dashboard_page();
    }
    cf_write_footer();
}

function render_dashboard_page() {
	$members = get_members_with_purchases_and_subscription();
	$active_members_and_recent_purchases = filter_out_duplicates_and_inactive_members($members);
	$active_members_purchases_remainder = array_map('add_remainder_to_member', $active_members_and_recent_purchases);
	usort($active_members_purchases_remainder, "remainder_comparer");
	$members_with_inactive_purchases = array_filter($members, "member_is_inactive");
	$inactive_members = filter_out_with_ssn($members_with_inactive_purchases, $active_members_and_recent_purchases);
	$member_names = array_map(function($m) {return $m->name;}, $members);
	$total_members = sizeof(array_unique($member_names));
	$active_members = active_members();
	$total_purchases = total_purchases();
	$subscription_counts = subscription_counts();
	echo '<div id="summary">';
	echo "<h3>Samantekt</h3>";
	echo '<a class="btn btn-primary" href="'.get_site_url().'/wp-admin/admin.php?download=cf550yfirlit.xml">Sækja yfirlit í excel</a>';
	// echo "<h4>Iðkendur - ".$total_members." (100%)</h4>";
	// echo "<p>Virkir: ".$active_members." (".round((100 * $active_members/$total_members),1)."%)</p>";
	echo "<h4>Kaup - ".$total_purchases." (100%)</h4>";
	foreach ($subscription_counts as $sub) {
		echo "<p>".$sub->name." - ".$sub->count." (".round((100*$sub->count/$total_purchases),1)."%)</p>";
	}
	echo "</div>";

	?>
	<script type="text/javascript">
		function toggleUserList() {
			var activeUsers = document.getElementById("active-users-div");
			var inactiveUsers = document.getElementById("inactive-users-div");
			if (activeUsers.style.display == "none") {
				activeUsers.style.display = "block";
				inactiveUsers.style.display = "none";
			} else {
				activeUsers.style.display = "none";
				inactiveUsers.style.display = "block";
			}
		}
	</script>
	<?php
	echo '<div class="text-center"><button class="btn btn-primary" onClick="toggleUserList()">Skipta um lista (virkir/óvirkir)</button></div>';
	echo '<div id="active-users-div" class="user-list-div">';
		echo "<h3>Virkir iðkendur - ".sizeof($active_members_purchases_remainder)."</h3>";
		echo "<table><tr>
				<th>Nafn</th>
				<th>Kennitala</th>
				<th>Kort</th>
				<th>Rennur út</th></tr>";
		array_map("print_active_member", $active_members_purchases_remainder);
		echo "</table>";
	echo '</div>';
	echo '<div id="inactive-users-div" class="user-list-div" style="display:none;">';
		echo "<h3>Óvirkir iðkendur - ".sizeof($inactive_members)."</h3>";
		echo "<table><tr>
				<th>Nafn</th>
				<th>Kennitala</th>";
		array_map("print_inactive_member", $inactive_members);
		echo "</table>";
	echo '</div>';

	// echo "<h3>ALLT - ".sizeof($members)."</h3>";
	// array_map("print_member", $members);
	// echo "<h3>Halldór:</h3>";
	// echo '<form method="POST" action="'.CF550_ADMIN_URL.'cf550_dashboard">';
    // echo '<input type="submit" name="update-database" value="LAGA GAGNAGRUNN" class="btn btn-default"/>';
	// echo '</form>';
}

// function update_database() {
// 	echo "<p>YESYESYESYESYES</p>";

// 	global $wpdb;
// 	$table_name = $wpdb->prefix."cf_attendance";
// 	$result = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix."cf_attendance");
// 	foreach ($result as $r) {
// 		$wpdb->query( $wpdb->prepare(
// 			"UPDATE $table_name set day = %s, time = '00:00:00' WHERE id = %s", 
// 			ice_day_from_iso_date($r->date),
// 			$r->id
// 		)); 
// 	}

// 	render_dashboard_page();
// }

function total_members() {
	global $wpdb;
	$result = $wpdb->get_row("SELECT COUNT(*) AS count FROM ".$wpdb->prefix."cf_members");
	return $result->count;
}

function active_members() {
	global $wpdb;
	$result = $wpdb->get_results("SELECT ssn FROM ".$wpdb->prefix."cf_members");
	$count = 0;
	foreach ($result as $member) {
		if (is_subscribed($member->ssn)) {
			$count++;
		}
	}
	return $count;
}

function total_purchases() {
	global $wpdb;
	$result = $wpdb->get_row("SELECT COUNT(*) AS count FROM ".$wpdb->prefix."cf_purchase");
	return $result->count;
}

function subscription_counts() {
	global $wpdb;
	$results = $wpdb->get_results("SELECT s.name as name, COUNT(p.id) AS count FROM ".$wpdb->prefix."cf_subscription s LEFT JOIN ".$wpdb->prefix."cf_purchase p ON p.subscription_id=s.id GROUP BY s.name");
	return $results;
}

function print_member($m) {
	echo "<p>";
	echo $m->name." - ";
	echo $m->ssn." - ";
	echo $m->p_date." - ";
	echo $m->s_name." - ";
	echo $m->s_type." - ";
	echo $m->s_value;
	echo "</p>";
}

function print_active_member($m) {
	echo "<tr>";
	echo "<td>".$m->name."</td>";
	echo "<td>".$m->ssn."</td>";
	echo "<td>".$m->s_name."</td>";
	echo "<td>".date_iso_to_pretty($m->remainder)."</td>";
	echo "</tr>";
}

function print_inactive_member($m) {
	echo "<tr>";
	echo "<td>".$m->name."</td>";
	echo "<td>".$m->ssn."</td>";
	echo "</tr>";
}

function member_is_inactive($m) {
	$purchase = new stdClass();
	$purchase->date = $m->p_date;
	$purchase->member_ssn = $m->ssn;
	$purchase->frozen = 0;
	$purchase->unfrozen = '0000-00-00';
	$subscription = new stdClass();
	$subscription->type = $m->s_type;
	$subscription->value = $m->s_value;
	return !purchase_is_ongoing($purchase, $subscription);
}

function member_is_active($m) {
	return !member_is_inactive($m);
}

function filter_out_duplicates_and_inactive_members($marr) {
	$marractive = array_filter($marr, "member_is_active");
	usort($marractive, "purchase_date_comparer");
	$names = [];
	$active_members_and_only_most_recent_purchases = [];
	foreach ($marractive as $m) {
		if (array_search($m->name, $names) === false) {
			array_push($names, $m->name);
			array_push($active_members_and_only_most_recent_purchases, $m);
		}
	}
	return $active_members_and_only_most_recent_purchases;
}

function add_remainder_to_member($member) {
		$member->remainder = get_remainder_for_member_purchase($member);
		return $member;
}

function purchase_date_comparer($a, $b) {
	return ($a->p_date > $b->p_date ? -1 : 1);
}

function remainder_comparer($a, $b) {
	return ($a->remainder > $b->remainder ? 1 : -1);
}

function filter_out_with_ssn($ma, $mb) {
	$mbssns = array_map(function($m) {return $m->ssn;}, $mb);
	$massns = [];
	$mresults = [];
	foreach ($ma as $mem) {
		if (!in_array($mem->ssn, $mbssns)) {
			if (!in_array($mem->ssn, $massns)) {
				$mresults[] = $mem;
			}
			$massns[] = $mem->ssn;
		}
	}
	return $mresults;
}

?>