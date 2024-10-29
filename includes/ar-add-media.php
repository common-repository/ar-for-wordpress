<?php
/**
 * AR Display
 * https://augmentedrealityplugins.com
**/
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_action( 'wp_ajax_set_ar_featured_image',  'set_ar_featured_image'  );
add_action( 'wp_ajax_nopriv_set_ar_featured_image',  'set_ar_featured_image'  );

add_action( 'rest_api_init', function () {
    //Path to REST route and the callback function
    register_rest_route( 'arforwp/v2', '/set_ar_featured_image/', array(
            'methods' => 'POST', 
            'callback' => 'set_ar_featured_image' ,
            'permission_callback' => '__return_true',
    ) );
});

if (!function_exists('set_ar_featured_image')){
    function set_ar_featured_image(WP_REST_Request $request){
 
        $data = $request->get_params();
  
        $post_id = $data['post_ID'];
        $post_title = $data['post_title'];
        $base64_string = $data['_ar_poster_image_field'];

        
        $image_name = $post_title."_model_poster_image.png";
        //$plugin_folder = substr($_SERVER["SCRIPT_URI"],0,strrpos($_SERVER["SCRIPT_URI"],"/")+1); 
        $parsedUrl = isset($_SERVER["SCRIPT_URI"]) ? wp_parse_url(esc_url_raw(wp_unslash($_SERVER["SCRIPT_URI"]))) : '';
        $plugin_folder = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . '/';
        $image_data = '';
        $base64_data = '';
        $pattern = '/^data:image\/octet-stream;base64,/';

        if ( preg_match( $pattern, $base64_string, $type ) ) {
            $base64_data = substr( $base64_string, strpos( $base64_string, ',' ) + 1 );
            $base64_data = base64_decode( $base64_data );        
        }    

        ar_wp_custom_file_write($image_name,$base64_data);
        $url = $plugin_folder . $image_name;

        upload_image($image_name, $url, $post_id, $post_title);
        wp_delete_file ($image_name);
    }
}

if (!function_exists('upload_image')){
    function upload_image($image_name, $url, $post_id, $post_title, $return = 0) {
        $image = "";

        if($url != "") {
            $file = array();
            $file['name'] = $image_name;
            $file['tmp_name'] = download_url($url);
            if (is_wp_error($file['tmp_name'])) {
                @wp_delete_file($file['tmp_name']);
                //var_dump( $file['tmp_name']->get_error_messages( ) );
                echo 'Error found.';
            } else {
                $attachmentId = media_handle_sideload($file, $post_id);
                if ( is_wp_error($attachmentId) ) {
                    @wp_delete_file($file['tmp_name']);
                    //var_dump( $attachmentId->get_error_messages( ) );
                    echo 'Error found.';
                } else {                
                    $image = wp_get_attachment_url( $attachmentId );
                    if($return){                       
                        return $attachmentId;
                    } else {
                        echo esc_html($attachmentId);
                        die();
                    }
                }
            }
            
        }
    }
}