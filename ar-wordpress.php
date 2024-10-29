<?php
/**
 * Plugin Name: AR for WordPress
 * Plugin URI: https://augmentedrealityplugins.com
 * Description: AR for WordPress Augmented Reality plugin.
 * Version: 7.0
 * Author: Web and Print Design	
 * Author URI: https://webandprint.design
 * License:  GPL2
 * Text Domain: ar-for-wordpress
 * Domain Path: /languages
 **/
 
if (!defined('ABSPATH'))
    exit;

$ar_plugin_id='ar-for-wordpress';
$ar_wp_active = true;
if ( is_admin() ) {
    if( ! function_exists( 'get_plugin_data' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $ar_plugin_data = get_plugin_data( __FILE__ );
    $ar_version = $ar_plugin_data['Version'];
}

if(!class_exists('AR_Plugin')){
    require_once(plugin_dir_path(__FILE__). '/includes/ar-class.php');
    require_once(plugin_dir_path(__FILE__). '/includes/ar-model.php');
}

add_action( 'plugins_loaded', 'ar_load_text_domain' );
add_action( 'plugins_loaded', 'run_ar_plugin' );

function ar_load_text_domain() {
    load_plugin_textdomain( 'ar-for-wordpress', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

if (!function_exists('run_ar_plugin')){
    function run_ar_plugin() {
        $plugin = new AR_Plugin();
        $plugin->run();
    }
}

// Functions Load
require_once(plugin_dir_path(__FILE__). 'ar-wp-functions.php');

// AR Model Custom Fields (Save 3D Model Files and Images)
require_once(plugin_dir_path(__FILE__). 'ar-model-fields.php');

// AR Initialisation
require_once(plugin_dir_path(__FILE__). 'includes/ar-initialise.php');

// AR Settings Page and Licence Checks
require_once(plugin_dir_path(__FILE__). 'includes/ar-settings.php');

// AR Model File Handling
require_once(plugin_dir_path(__FILE__). 'includes/ar-file-handling.php');

// AR User Upload
require_once(plugin_dir_path(__FILE__). 'includes/ar-user-upload.php');

// AR QR Code
require_once(plugin_dir_path(__FILE__). 'includes/ar-qrcode.php');

// AR Standalone
require_once(plugin_dir_path(__FILE__). 'includes/ar-standalone.php');

// Custom Wordpress Post Type
require_once(plugin_dir_path(__FILE__) . 'ar-model-post-type.php');

// Widgets Load
require_once(plugin_dir_path(__FILE__). 'ar-widgets.php');

// Endpoint API Load
require_once(plugin_dir_path(__FILE__). 'ar-api.php');

// Endpoint for Media upload
require_once(plugin_dir_path(__FILE__). 'includes/ar-add-media.php');

// AR Model Shop
require_once(plugin_dir_path(__FILE__). 'includes/ar-model-shop.php');

// Secure Encrypted URLs for Model Files
require_once(plugin_dir_path(__FILE__). 'includes/ar-secure-url-generate.php');

// Block Gutenberg Load
require_once(plugin_dir_path(__FILE__). 'gutenberg-block/src/init.php');

// Plugin Updates
$this_file = __FILE__;
$update_check = "https://augmentedrealityplugins.com/plugins/check-update-ar-for-wordpress.txt";

require_once(plugin_dir_path(__FILE__) . 'ar-updates.php');

// Create Menu
add_action('admin_menu', 'ar_wp_advance_setting_menu');

function ar_wp_advance_setting_menu() {
    add_submenu_page('edit.php?post_type=armodels', __('Settings', 'ar-for-wordpress'), __('Settings', 'ar-for-wordpress'), 'manage_options', '', 'ar_subscription_setting');
    add_submenu_page('edit.php?post_type=armodels', __('Whats New', 'ar-for-wordpress'), __('Whats New', 'ar-for-wordpress'), 'manage_options', 'ar-whats-new', 'ar_whats_new');
    

}

// Hide the featured Image Box
add_action('admin_head', 'ar_wp_advance_remove_my_meta_boxen');

function ar_wp_advance_remove_my_meta_boxen() {
    remove_meta_box('postimagediv', 'armodels', 'side');
     add_meta_box('postimagediv', __('AR Poster Image', 'ar-for-wordpress'), 'post_thumbnail_meta_box', 'armodels', 'side', 'low');
}

// Add the custom columns to the Ar Model post type
add_filter('manage_armodels_posts_columns', 'ar_wp_advance_custom_edit_posts_columns');

function ar_wp_advance_custom_edit_posts_columns($columns) {
    unset($columns['date']);
    unset($columns['pro-image']);
    $columns['Shortcode'] = __('Shortcode', 'ar-for-wordpress' );
    $ARimgSrc = esc_url(plugins_url("assets/images/chair.png", __FILE__));
    $columns['thumbs'] = '<div class="ar_tooltip"><img src="' . $ARimgSrc . '" width="15"><span class="ar_tooltip_text">'.__('AR Model', 'ar-for-wordpress' ).'</span></div>'; //name of the column 
    $columns['date'] = __('Date', 'ar-for-wordpress');
    return $columns;
}

// Add the data to the custom columns for the AR Model post type
add_action('manage_armodels_posts_custom_column', 'ar_advance_custom_armodels_column', 10, 2);

// Remove View option form listing
add_filter('post_row_actions', 'ar_wp_advance_remove_row_actions', 10, 1);

function ar_wp_advance_remove_row_actions($actions) {
    if (get_post_type() === 'armodels')
        unset($actions['view']);
    return $actions;
}

// Add links to Settings page on Plugins page
add_filter( 'plugin_action_links_ar-for-wordpress/ar-wordpress.php', 'ar_settings_link' );
function ar_settings_link( $links ) {
	$url = esc_url( add_query_arg(
		'post_type',
		'armodels',
		get_admin_url() . 'edit.php'
	) );
	$settings_link = "<a href='$url&page'>" . __( 'Settings', 'ar-for-wordpress' ) . '</a>';
	array_push($links,$settings_link);
	$url = esc_url( add_query_arg(
		'post_type',
		'armodels',
		get_admin_url() . 'edit.php?page=ar-whats-new'
	) );
	$settings_link = "<a href='$url'>" . __( 'Whats New', 'ar-for-wordpress' ) . '</a>';
	array_push($links,$settings_link);
	return $links;
}

//Add documentation link to the plugin on the plugins page
add_filter('plugin_row_meta', 'ar_plugin_documentation_link', 10, 2);

function ar_plugin_documentation_link($links, $file) {
    if (plugin_basename(__FILE__) === $file) {
        $documentation_link = '<a href="https://augmentedrealityplugins.com/support/" target="_blank">Documentation</a>';
        $links[] = $documentation_link;
    }
    return $links;
}

if ((!isset($ar_wcfm))){
    $shortcode_examples_wc = '<b>[ar-display]</b> - '.esc_html(__('Place on the Woocommerce product page to display the 3D model for the product id and includes all variations. Can be used in the product description or in your theme templates. See settings page for template options.', 'ar-for-wordpress' )).'<br>';
    $shortcode_examples = '
        <b>[ar-display id=X]</b> - '.esc_html(__('Displays the 3D model for a given model/post id.', 'ar-for-wordpress' )).'<br>
        <b>[ar-display id=\'X,Y,Z\']</b> - '.esc_html(__('Displays the 3D models for multiple comma seperated model/post ids within 1 viewer and thumbnails to select model.', 'ar-for-wordpress' )).'<br>
        <b>[ar-display cat=X]</b> - '.esc_html(__('Displays the 3D models for a given category within 1 viewer and thumbnails to select model.', 'ar-for-wordpress' )).'<br>
        <b>[ar-display cat=\'X,Y,Z\']</b> - '.esc_html(__('Displays the 3D models for multiple comma seperated category ids within 1 viewer and thumbnails to select model.', 'ar-for-wordpress' )).'<br>
        <b>[ar-gallery]</b> - '.esc_html(__('Displays the 3D Gallery Model using the featured image of the current post. Includes a size selector.', 'ar-for-wordpress' )).'<br>
        <b>[ar-user-upload]</b> - '.esc_html(__('Displays the Model Viewer allowing the end user to drag and drop a model or image file to have it display.', 'ar-for-wordpress' )).'<br>
        <b>[ar-view id=X text=true (OR) buttons=true]</b> - '.esc_html(__('Display either the AR View button, the text link \'text=true\' "View in AR / View in 3D" or html buttons \'buttons=true\' for a given model/post id without the need for the 3D Model viewer being displayed. Custom text can be set on the AR Settings page.', 'ar-for-wordpress' )).'<br>
        <b>[ar-qr]</b> - '.esc_html(__('QR Code shortcode display for the page or post the shortcode is added to.<br>', 'ar-for-wordpress' ));
        
    $ar_rate_this_plugin = '<h3 style="margin-top:0px">'.esc_html(__('Rate This Plugin', 'ar-for-wordpress' )).'</h3><img src="'.esc_url( plugins_url( "assets/images/5-stars.png", __FILE__ ) ).'" style="height:30px"><br>
    '.esc_html(__('We really hope you like using AR For WordPress and would be very greatful if you could leave a rating for it on the WordPress Plugin repository.', 'ar-for-wordpress' )).'<br>
    <a href="https://wordpress.org/support/plugin/ar-for-wordpress/reviews/" target="_blank">'.esc_html(__('Please click here to leave a rating for AR For WordPress.', 'ar-for-wordpress' )).'</a>';
}



?>