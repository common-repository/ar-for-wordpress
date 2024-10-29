<?php
/**
 * AR Display
 * https://augmentedrealityplugins.com
**/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/******* Activate plugin *******/
register_activation_hook(__FILE__, 'ar_plugin_activation');
if (!function_exists('ar_plugin_activation')){
    function ar_plugin_activation() {
            wp_schedule_event( time(), 'daily', 'ar_cron' );
            ar_cron();
    }
}

/******* Deactivate plugin *******/
register_deactivation_hook(__FILE__, 'ar_plugin_deactivation');
if (!function_exists('ar_plugin_deactivation')){
    function ar_plugin_deactivation() {
        wp_clear_scheduled_hook( 'ar_cron' );
    }
}

/******* Enqueue Js ***********/
/* Called from Model Viewer */
if (!function_exists('ar_advance_register_script')){
    function ar_advance_register_script() {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script('ar_admin', plugins_url('../assets/js/ar-admin.js', __FILE__), array('jquery','wp-color-picker'), '1.3', array('in_footer' => false,'strategy' => 'async'));
        wp_enqueue_script('ar_model', plugins_url('../assets/js/ar-model.js', __FILE__), array('jquery'), '1.3', array('in_footer' => false,'strategy' => 'async'));
        wp_enqueue_script('ar_copy_to_clipboard', plugins_url('../assets/js/ar-display.js', __FILE__), array('jquery'), '1.0', array('in_footer' => true, 'strategy' => 'async'));
    }
}


/******* Enqueue Css ***********/
if (!function_exists('ar_advance_register_style')){
    function ar_advance_register_style() {
        wp_enqueue_style('ar_styles', plugins_url('../assets/css/ar-display.css',__FILE__), false, '1.0.0', 'all');        
    }
}

// Function to enqueue model-viewer JavaScript
if (!function_exists('load_model_viewer_js')){
    function load_model_viewer_js(){
        wp_enqueue_script( 'ar-model-viewer-js', plugins_url('../assets/js/model-viewer.min.js', __FILE__), array(), '1.0.0', array('in-footer'=>true,'strategy' => 'async') );
    }
}

// Function to enqueue additional web components JavaScript
if (!function_exists('load_model_viewer_components_js')){
    function load_model_viewer_components_js(){
        wp_enqueue_script( 'ar-model-viewer-webcom-js', plugins_url('../assets/js/webcomponents-loader.js', __FILE__), array(), '1.0.0', array('in-footer'=>true,'strategy' => 'async')  );
        wp_enqueue_script( 'ar-model-viewer-intersection-js', plugins_url('../assets/js/intersection-observer.js', __FILE__), array(), '1.0.0', array('in-footer'=>true,'strategy' => 'async')  );
        wp_enqueue_script( 'ar-model-viewer-resize-js', plugins_url('../assets/js/ResizeObserver.js', __FILE__), array(), '1.0.0', array('in-footer'=>true,'strategy' => 'async')  );
    }
}

// Add 'type="module"' attribute to the model-viewer script tag
add_filter('script_loader_tag', 'ar_add_type_attribute' , 10, 3);


/********** AR Register Settings  **************/
if (!function_exists('ar_register_settings')){
    function ar_register_settings() {
        // Define the settings to be registered
        $settings = [
            'ar_licence_key',
            'ar_licence_valid',
            'ar_licence_plan',
            'ar_licence_renewal',
            'ar_dimensions_inches',
            'ar_hide_dimensions',
            'ar_no_posts',
            'ar_wl_file',
            'ar_view_file',
            'ar_qr_file',
            'ar_qr_destination',
            'ar_view_in_ar',
            'ar_view_in_3d',
            'ar_dimensions_units',
            'ar_dimensions_label',
            'ar_user_upload_button',
            'ar_user_default',
            'ar_user_button',
            'ar_user_modelviewer',
            'ar_user_default_image',
            'ar_fullscreen_file',
            'ar_play_file',
            'ar_pause_file',
            'ar_hide_qrcode',
            'ar_hide_reset',
            'ar_hide_fullscreen',
            'ar_hide_gallery_sizes',
            'ar_hide_arview',
            'ar_animation',
            'ar_animation_selection',
            'ar_autoplay',
            'ar_emissive',
            'ar_light_color',
            'ar_disable_zoom',
            'ar_rotate_limit',
            'ar_scene_viewer',
            'ar_css_positions',
            'ar_css',
            'ar_open_tabs',
            'ar_open_tabs_remember',
            'ar_secure_model_urls'
        ];

        // Register each setting
        foreach ($settings as $setting) {
            add_option($setting, '');
            register_setting('ar_display_options_group', $setting);
        }
    }
}
add_action('admin_init', 'ar_register_settings');

/******* Element Positions *******/
$ar_css_names = array ('AR Button'=> '.ar-button', 'Dimensions'=>'.dimension', 'Fullscreen Button'=>'.ar_popup-btn', 'QR Code'=>'.ar-qrcode', 'Thumbnail Slides'=>'.ar_slider', 'Play/Pause'=>'.ar-button-animation', 'Reset Button'=>'.ar-reset', 'Call To Action'=>'.ar_cta_button', 'Model Variants'=>'.ar-variant-container');
$ar_css_styles = array();
$ar_css_styles['Top Left'] = 'top: 6px !important; bottom: auto !important; left: 6px !important; right: auto !important; margin: 0 !important;';
$ar_css_styles['Top Center'] = 'top: 6px !important; bottom: auto !important; margin: 0 auto !important; left: 0 !important; right: 0 !important;';
$ar_css_styles['Top Right'] = 'top: 6px !important; bottom: auto !important; left: auto !important; right: 6px !important; margin: 0 !important;';
$ar_css_styles['Bottom Left'] = 'top: auto !important; bottom: 6px !important; left: 6px !important; right: auto !important; margin: 0 !important;';
$ar_css_styles['Bottom Center'] = 'top: auto !important; bottom: 6px !important; margin: 0 auto !important; left: 0 !important; right: 0 !important;';
$ar_css_styles['Bottom Right'] = 'top: auto !important; bottom: 6px !important; left: auto !important; right: 6px !important; margin: 0 !important;';



if(!function_exists('ar_allowed_html')){
    function ar_allowed_html(){
        $common_atts = ar_common_attributes();
        $allowed_html = array(
            'a' => $common_atts,
            'b' => array(),
            'p' => $common_atts,
            'div' => array(                    
                'class' => true,
                'style' => true,
                'onclick' => true,
                'data-id' => true,
                'data-normal' => true,
                'data-position' => true,
                'id' => true,                
            ),
            'br' => array(),
            'em' => array(),
            'strong' => array(),
            'img' => array(                    
                'class' => true,
                'style' => true,
                'onclick' => true,
                'src' => true,
                'data-normal' => true,
                'data-position' => true,
                'id' => true,                
            ),
            'ul' => $common_atts,
            'ol' => $common_atts,
            'li' => $common_atts,
            'h1' => $common_atts,
            'h2' => $common_atts,
            'h3' => $common_atts,            
            'button' => array(                    
                'class' => true,
                'style' => true,
                'onclick' => true,
                'data-id' => true,
                'data-normal' => true,
                'data-position' => true,
                'id' => true, 
                'slot' => true,               
            ),
            'input' => array(                    
                'class' => true,
                'style' => true,
                'onclick' => true,
                'data-id' => true,
                'data-normal' => true,
                'data-position' => true,
                'id' => true,
                'type' => true,                
                'value' => true,
                'placeholder' => true,
            ),
            'blockquote' => array(),
            'center' => array(),

            // Newly added elements
            'select' => array(
                'id' => true,
                'name' => true,
                'class' => true,
                'style' => true,
                'multiple' => true,
                'size' => true,
            ),
            'option' => array(
                'value' => true,
                'selected' => true,
            ),
            'textarea' => array(
                'id' => true,
                'name' => true,
                'class' => true,
                'style' => true,
                'rows' => true,
                'cols' => true,
                'placeholder' => true,
            ),

            'model-viewer' => array(
                'id' => true,
                'class' => true,
                'style' => true,
                'src' => true,
                'alt' => true,
                'poster' => true,
                'loading' => true,
                'reveal' => true,
                'ar' => true,
                'ar-modes' => true,
                'ar-scale' => true,
                'ar-placement' => true,
                'ios-src' => true,
                'xr-environment' => true,
                'parent-page' => true,
                'quick-look' => true,
                'scene-viewer' => true,
                'webxr' => true,
                'quick-look-browsers' => true,
                'camera-controls' => true,
                'disable-pan' => true,
                'disable-tap' => true,
                'touch-action' => true,
                'disable-zoom' => true,
                'orbit-sensitivity' => true,
                'zoom-sensitivity' => true,
                'pan-sensitivity' => true,
                'auto-rotate' => true,
                'auto-rotate-delay' => true,
                'rotation-per-second' => true,
                'interaction-prompt' => true,
                'interaction-prompt-style' => true,
                'interaction-prompt-threshold' => true,
                'camera-orbit' => true,
                'camera-target' => true,
                'field-of-view' => true,
                'max-camera-orbit' => true,
                'min-camera-orbit' => true,
                'max-field-of-view' => true,
                'min-field-of-view' => true,
                'interpolation-decay' => true,
                'skybox-image' => true,
                'skybox-height' => true,
                'environment-image' => true,
                'exposure' => true,
                'tone-mapping' => true,
                'shadow-intensity' => true,
                'shadow-softness' => true,
                'animation-name' => true,
                'animation-crossfade-duration' => true,
                'autoplay' => true, 
                'onclick' => true, 
            ),
        );

        return $allowed_html;
    }
}

if(!function_exists('ar_common_attributes')){
    function ar_common_attributes(){
        $common_atts =  array(                    
                'class' => true,
                'style' => true,
                'onclick' => true,
                'data-id' => true,
                'data-normal' => true,
                'data-position' => true,
                'id' => true,
                'name' => true, 
                'src' => true,
                'alt' => true,
                'width' => true,
                'height' => true, 
                'href' => true,
                'title' => true,
                'target' => true,
                'slot' => true,
                'for' => true, 
                'language' => true, 
                'src' => true, 
                'type' => true, 

        );

        return $common_atts;        
    }
}

/************Allow USDZ mime upload in Wordpress Media Library *****************/
if (!function_exists('ar_my_file_types')){
    function ar_my_file_types($mime_types) { //Add Additional File Types
        $mime_types['usdz'] = 'model/vnd.usdz+zip';
        return $mime_types;
    }
}
add_filter('upload_mimes', 'ar_my_file_types', 1, 1);

if (!function_exists('ar_display_media_library')){
    function ar_display_media_library( $data, $file, $filename, $mimes ) {
        if ( ! empty( $data['ext'] ) && ! empty( $data['type'] ) ) {
            return $data;
        }
        $registered_file_types = [
            'usdz' => 'model/vnd.usdz+zip|application/octet-stream|model/x-vnd.usdz+zip',
            'USDZ' => 'model/vnd.usdz+zip|application/octet-stream|model/x-vnd.usdz+zip',
            'reality' => 'model/vnd.reality|application/octet-stream',
            'REALITY' => 'model/vnd.reality|application/octet-stream',
            'glb' => 'model/gltf-binary|application/octet-stream|model',
            'GLB' => 'model/gltf-binary|application/octet-stream|model',
            'gltf' => 'model/gltf+json',
            'GLTF' => 'model/gltf+json',
            'hdr' => 'model/gltf+json',
            'HDR' => 'model/gltf+json',
            'dxf' => 'application/dxf',
            'DXF' => 'application/dxf',
            'dae' => 'application/dae',
            'DAE' => 'application/dae',
            '3ds' => 'application/x-3ds',
            '3DS' => 'application/x-3ds',
            'obj' => 'model/obj',
            'OBJ' => 'model/obj',
            'ply' => 'application/octet-stream',
            'PLY' => 'application/octet-stream',
            'stl' => 'model/stl',
            'STL' => 'model/stl'
            ];
        $filetype = wp_check_filetype( $filename, $mimes );
        if ( ! isset( $registered_file_types[ $filetype['ext'] ] ) ) {
            return $data;
        }
        return [
            'ext' => $filetype['ext'],
            'type' => $filetype['type'],
            'proper_filename' => $data['proper_filename'],
        ];
    }
    add_filter( 'wp_check_filetype_and_ext', 'ar_display_media_library', 10, 4 );
}


if (!function_exists('ar_display_mimes')){
    function ar_display_mimes( $mime_types ) {
        if ( ! in_array( 'usdz', $mime_types ) ) { 
            $mime_types['usdz'] = 'model/vnd.usdz+zip|application/octet-stream|model/x-vnd.usdz+zip';
        }
        if ( ! in_array( 'reality', $mime_types ) ) { 
            $mime_types['reality'] = 'model/vnd.reality|application/octet-stream';
        }
        if ( ! in_array( 'glb', $mime_types ) ) { 
            $mime_types['glb'] = 'model/gltf-binary|application/octet-stream|model';
        }
        if ( ! in_array( 'gltf', $mime_types ) ) { 
            $mime_types['gltf'] = 'model/gltf+json';
        }
        if ( ! in_array( 'hdr', $mime_types ) ) { 
            $mime_types['hdr'] = 'image/vnd.radiance';
        }
        if ( ! in_array( 'dxf', $mime_types ) ) { 
            $mime_types['dxf'] = 'application/dxf';
        }
        if ( ! in_array( 'dae', $mime_types ) ) { 
            $mime_types['dae'] = 'application/dae';
        }
        if ( ! in_array( '3ds', $mime_types ) ) { 
            $mime_types['3ds'] = 'application/x-3ds';
        }
        if ( ! in_array( 'obj', $mime_types ) ) { 
            $mime_types['obj'] = 'model/obj';
        }
        if ( ! in_array( 'ply', $mime_types ) ) { 
            $mime_types['ply'] = 'application/octet-stream';
        }
        if ( ! in_array( 'stl', $mime_types ) ) { 
            $mime_types['stl'] = 'model/stl';
        }
        return $mime_types;
    }
    
    add_filter( 'upload_mimes', 'ar_display_mimes' );
}


/************* Get Mime Types for corresponding files *******************/
if (!function_exists('ar_get_mime_type')){
    function ar_get_mime_type($file_path) {
        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        switch ($ext) {
            case 'gltf':
                return 'model/gltf+json';
            case 'glb':
                return 'model/gltf-binary';
            case 'usdz':
                return 'model/vnd.usdz+zip';
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            default:
                return 'application/octet-stream'; // Fallback MIME type
        }
    }
}



if(!function_exists('ar_add_type_attribute')){
    function ar_add_type_attribute($tag, $handle, $src) {
        // Target only the 'ar-model-viewer-js' handle
        if ( 'ar-model-viewer-js' !== $handle ) {
            return $tag;
        }

        // Modify the script tag to add 'type="module"'
        $tag = str_replace( ' src', ' type="module" src', $tag );
        return $tag;
    }
}

remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
add_action( 'shutdown', function() {
    if (ob_get_contents()) {
        if (ob_get_length() && !ini_get('zlib.output_compression')) {
            while (ob_get_level()) {
                @ob_end_flush();
            }
        }
    }
} );

//Encode custom CSS code for importing into text field
if (!function_exists('ar_encodeURIComponent')){
    function ar_encodeURIComponent($str) {
        $unescaped = array(
            '%2D'=>'-','%5F'=>'_','%2E'=>'.','%21'=>'!', '%7E'=>'~',
            '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')'
        );
        $reserved = array(
            '%3B'=>';','%2C'=>',','%2F'=>'/','%3F'=>'?','%3A'=>':',
            '%40'=>'@','%26'=>'&','%3D'=>'=','%2B'=>'+','%24'=>'$'
        );
        $score = array(
            '%23'=>'#'
        );
        return strtr(rawurlencode($str), array_merge($reserved,$unescaped,$score));
    }
}

if(!function_exists('get_screen_type')){
    function get_screen_type(){
        $screen = array();

        $user_agent = isset($_SERVER["HTTP_USER_AGENT"]) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

        $screen['isMob'] = is_numeric(strpos(strtolower($user_agent), "mobile")); 
        $screen['isTab'] = is_numeric(strpos(strtolower($user_agent), "tablet")); 
        $screen['isWin'] = is_numeric(strpos(strtolower($user_agent), "windows")); 
        $screen['isAndroid'] = is_numeric(strpos(strtolower($user_agent), "android")); 
        $screen['isIPhone'] = is_numeric(strpos(strtolower($user_agent), "iphone")); 
        $screen['isIPad'] = is_numeric(strpos(strtolower($user_agent), "ipad")); 
        $screen['isFireFox'] = is_numeric(strpos(strtolower($user_agent), "firefox"));
        $screen['isFireFoxiOS'] = is_numeric(strpos(strtolower($user_agent), "fxios"));
        $screen['isIOS'] = $screen['isIPhone'] || $screen['isIPad'];

        return $screen;
    }
}



/********** Reload the Gutenburg editor when AR model post is updated **********/
if (!function_exists('ar_reload_page_after_publish')){
    function ar_reload_page_after_publish() {
        global $post;
    
        // Check if the current post type is 'armodels'
        if ($post && $post->post_type === 'armodels') {
            ?>
            <script>
                (function ($) {
                    $(document).ready(function () {
                        // Add the event listener for post updates
                        $(document).on('click', '#publish, .editor-post-publish-button, #save-post', function (e) {
                            // Check if the post is being published or updated
                            if ($('#original_post_status').val() !== $('#post_status').val()) {
                                // Reload the page after a short delay (adjust as needed)
                                setTimeout(function () {
                                    location.reload();
                                }, 2000);
                            }
                        });
                    });
                })(jQuery);
            </script>
            <?php
        }
    }

    add_action('admin_footer', 'ar_reload_page_after_publish');
}


if (get_option('ar_no_posts')) {
    // Hook into the 'pre_get_posts' action to modify the query before it's executed
    add_action('pre_get_posts', 'set_armodels_to_private');

    function set_armodels_to_private($query) {
        // Only modify the main query on the frontend
        if (!is_admin() && $query->is_main_query()) {
            // Check if the query is for 'armodels' post type
            if (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'armodels') {
                // Modify the query to only show private posts if the 'ar_no_posts' option is set
                $query->set('post_status', 'private');
            }
        }
    }
}


/************* Function to find the closest ratio - used for ar-gallery shortcode *******************/
if (!function_exists('ar_findClosestRatio')){
    function ar_findClosestRatio($aspect_ratio, $ratios) {
        $closest_ratio = null;
        $smallest_difference = null;
        
        foreach ($ratios as $ratio) {
            $difference = abs($aspect_ratio - floatval($ratio));
            if ($smallest_difference === null || $difference < $smallest_difference) {
                $smallest_difference = $difference;
                $closest_ratio = $ratio;
            }
        }
        
        return $closest_ratio;
    }
}

//used for the gallery builder size selector
if(!function_exists('ar_array_flatten')){
    function ar_array_flatten($array) {
        $flatten = array();
        foreach ($array as $value) {
            if (is_array($value)) {
                $flatten = array_merge($flatten, ar_array_flatten($value));
            } else {
                $flatten[] = $value;
            }
        }
        return $flatten;
    }
}
