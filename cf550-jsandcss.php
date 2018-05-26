<?php defined( 'ABSPATH' ) or die( 'Access Denied, get lost!' );

// Load on admin page
add_action( 'admin_enqueue_scripts', 'cf550_admin_enqueue_scripts' );
function cf550_admin_enqueue_scripts( $hook ) {
    // Load only on ?page=cf550 or internal cf550 plugin pages
    if($hook != 'toplevel_page_cf550' && strpos($hook, "crossfit550_page_") != 0) {
        return;
    }
    // JS
    // wp_enqueue_script( 'admin-page-script',
    //     plugins_url( '/js/admin-page.js', __FILE__ ),
    //     array( 'jquery' )
    // );
    // $title_nonce = wp_create_nonce( CF550_NONCE );
    // wp_localize_script( 'admin-page-script', 'my_ajax_obj', array(
    //    'ajax_url' => admin_url( 'admin-ajax.php' ),
    //    'nonce'    => $title_nonce,
    // ) );

    // CSS
    wp_enqueue_style( 'admin_custom_css_bootstrap', plugins_url('css/bootstrap.min.css', __FILE__) );
    wp_enqueue_style( 'admin_custom_css', plugins_url('css/custom-admin.css', __FILE__) );
}

// Load everywhere:
add_action( 'wp_enqueue_scripts', 'cf550_wp_enqueue_scripts' );
function cf550_wp_enqueue_scripts($hook) {
    // CSS
    //wp_enqueue_style( 'admin_custom_css_bootstrap', plugins_url('css/bootstrap.min.css', __FILE__) );
    // JS
    wp_enqueue_script( 'widget-script',
        plugins_url( '/js/widgets.js', __FILE__ ),
        array( 'jquery' )
    );
    $title_nonce = wp_create_nonce( CF550_NONCE );
    wp_localize_script( 'widget-script', 'my_ajax_obj', array(
       'ajax_url' => admin_url( 'admin-ajax.php' ),
       'nonce'    => $title_nonce,
    ) );
}
?>