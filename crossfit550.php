<?php

/*

Plugin Name: Crossfit550

Description: Custom plugin for Crossfit550

Author:      Halldór Örn Kristjánsson

*/

defined( 'ABSPATH' ) or die( 'Access Denied, get lost!' );

define( 'CF550_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
define( 'CF550_NONCE', 'crossfit550supernonce' );
define( 'CF550_ADMIN_URL', get_site_url()."/wp-admin/admin.php?page=");

require_once CF550_PLUGIN_DIR. '/cf-helpers.php';
require_once CF550_PLUGIN_DIR. '/cf550-database.php';
require_once CF550_PLUGIN_DIR. '/cf550-jsandcss.php';
require_once CF550_PLUGIN_DIR. '/cf550-admin-page.php';
require_once CF550_PLUGIN_DIR. '/sub-pages/members.php';
require_once CF550_PLUGIN_DIR. '/sub-pages/programs.php';
require_once CF550_PLUGIN_DIR. '/sub-pages/programtime.php';
require_once CF550_PLUGIN_DIR. '/sub-pages/attendance.php';
require_once CF550_PLUGIN_DIR. '/sub-pages/subscription.php';
require_once CF550_PLUGIN_DIR. '/sub-pages/purchase.php';
require_once CF550_PLUGIN_DIR. '/widgets/program-today.php';
require_once CF550_PLUGIN_DIR. '/front-pages/info-form.php';
require_once CF550_PLUGIN_DIR. '/front-pages/program-today.php';
require_once CF550_PLUGIN_DIR. '/reports/excel-generator.php';
?>