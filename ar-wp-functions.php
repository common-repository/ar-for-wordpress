<?php
/**
 * AR Display
 * https://augmentedrealityplugins.com
**/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Echo transaleted content
if (!function_exists('ar_output')){
    function ar_output($content, $ar_plugin_id, $e = null) {
        $translated_string = '';

        // Determine the translated string based on plugin ID
        //if ($ar_plugin_id == 'ar-for-wordpress') {
            /* translators: %s represents dynamic content to be inserted */
            $translated_string = __('Dynamic content: %s', 'ar-for-wordpress');
        //} 
        $translated_string = str_replace('Dynamic content: ','',$translated_string);
        // If we just need to return the content
        if ($e === null) {
            return sprintf($translated_string, $content); // Insert dynamic content into the translation string
            //return esc_html($content,'ar-for-wordpress');
        } else {
            echo esc_html(sprintf($translated_string,$content)); // Echo the translated content
            //esc_html_e($content,'ar-for-wordpress');
        }
    }
}

if (!function_exists('save_ar_wp_option_fields')){
    function save_ar_wp_option_fields( $post_id ) {
        global $ar_plugin_id;

        //check if nonce is valid
        if (!isset($_POST['arwp-editpost-nonce'])) {
            return;
        }

        // Verify that the nonce is valid.
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['arwp-editpost-nonce'])), 'ar-for-wordpress')) {
            // Nonce is invalid, so do not save the data.
            return;
        } 

        $post_type = get_post_type();
        
        
        if($post_type != 'armodels'){
            //die("HERERE");
            //exit;
            return 1;
        }
        

        $ar_post ='';
        $suffix = '';
        if ( isset( $_POST['_usdz_file'] ) ) {
            update_post_meta( $post_id, '_usdz_file', sanitize_text_field( wp_unslash($_POST['_usdz_file']) ) );
        }
        
        if (( isset( $_POST['_glb_file'] ) ) || ( isset( $_POST['_ar_asset_file'] ) &&  isset( $_POST['_ar_asset_file'.$suffix] )  && isset( $_POST['_asset_texture_file_0'] ) )):
            if ((sanitize_text_field(wp_unslash($_POST['_ar_asset_file'.$suffix])) !='' )AND(sanitize_text_field(wp_unslash($_POST['_asset_texture_file_0'])) !='')){
                //Add the ratio and orientation to the url.
                
                //Asset Builder overrides the GLB field
                $path_parts = pathinfo(sanitize_text_field( wp_unslash($_POST['_ar_asset_file']) ));

                $path_parts['filename'] .= '_' . (isset($_POST['ar_asset_ratio']) ? sanitize_text_field(wp_unslash($_POST['ar_asset_ratio'])) : ''). '_' . (isset($_POST['ar_asset_orientation']) ? sanitize_text_field(wp_unslash($_POST['ar_asset_orientation'])) : '');
                $path_parts['basename'] = $path_parts['filename'] . '.zip';
                
        //print_r($path_parts);print_r($_POST);exit;
            }else{
                $path_parts = isset($_POST['_glb_file']) ? pathinfo(sanitize_text_field( wp_unslash($_POST['_glb_file'] ))) : '';
            }
            
            /***ZIP***/
            /***if zip file, then extract it and put gltf into _glb_file***/
            $zip_gltf='';
            if (isset($path_parts['extension'])){
                if (strtolower($path_parts['extension'])=='zip'){
                    WP_Filesystem();
                    $upload_dir = wp_upload_dir();
                    $destination_path = $upload_dir['path'].'/ar_asset_'.$post_id.'/';
                    if ( $_POST['_ar_asset_file'] !='' ){
                        
                        $src_file=$destination_path.'/temp.zip';
                    }else{
                        //$destination_path = $upload_dir['path'].'/'.$path_parts['filename'].'/';
                        $src_file=$upload_dir['path'].'/'.$path_parts['basename'];
                    }
                    //Delete old Asset folder
                    if (file_exists($destination_path)) {
                        ar_remove_asset($destination_path);
                    }
                    //Create new Asset folder
                    if (!wp_mkdir_p($destination_path, 0755, true)) {
                        die('Failed to create folders...');
                    }
                    $unzipfile = '';
                    if (  $_POST['_ar_asset_file'] !='' ){
                        // If the function it's not available, require it.
                        if ( ! function_exists( 'download_url' ) ) {
                            require_once ABSPATH . 'wp-admin/includes/file.php';
                        }
                        
                        //copy zip from asset_builder to local site
                        $src_file = download_url( sanitize_text_field( $path_parts['dirname'].'/'.$path_parts['basename'] ) );

                        if(ar_check_zip_archive($src_file)){
                            $unzipfile = unzip_file( $src_file  , $destination_path);
                        }
                        wp_delete_file($src_file);
                    }else{
                        if(ar_check_zip_archive($src_file)){
                            $unzipfile = unzip_file( $src_file, $destination_path);
                        }
                    }
                    if ( $unzipfile ) {
                        //echo 'Successfully unzipped the file! '. sanitize_text_field( $_POST['_ar_asset_file']);       
                    } else {
                        ar_output('There was an error unzipping the file.', 'ar-display','e' );
                    }
                        
                
                    if ( $unzipfile ) {
                        $file= glob($destination_path . "/*.gltf");
                        //echo $destination_path.'<br>';
                        foreach($file as $filew){
                            $path_parts2=pathinfo($filew);
                            if ( $_POST['_ar_asset_file'] !='' ){
                                //print_r($_POST);exit;
                                if (( isset( $_POST['_ar_asset_file'] ) )AND( isset( $_POST['_asset_texture_file_0'] ) )){
                                    for($i=0;$i<10;$i++){
                                        if (isset($_POST['_asset_texture_file_'.$i])){
                                            $asset_textures[$i]['newfile']=isset($_POST['_asset_texture_file_'.$i]) ? sanitize_text_field(wp_unslash($_POST['_asset_texture_file_'.$i])) : '';
                                            $asset_textures[$i]['filename']=isset($_POST['_asset_texture_id_'.$i]) ? sanitize_text_field(wp_unslash($_POST['_asset_texture_id_'.$i])) : '';
                                        }
                                    }
                                    $flip = isset($_POST['_asset_texture_flip']) ? sanitize_text_field(wp_unslash($_POST['_asset_texture_flip'])) : '';
                                    asset_builder_texture($upload_dir['path'].'/ar_asset_'.$post_id.'/',$path_parts2['basename'],$asset_textures,$flip);
                                    
                                }
                            }else{
                               // $_POST['_glb_file'] = $path_parts['dirname'].'/'.$path_parts['filename'].'/'.$path_parts2['basename'];
                            }
                            $_POST['_glb_file'] = $upload_dir['url'].'/ar_asset_'.$post_id.'/'.$path_parts2['basename'];
                            $zip_gltf='1'; //If set to 1 then ignore the model conversion process below
                            //echo  $_POST['_glb_file'].'<br>';
                        }
                        
                    } else {
                        ar_output('There was an error unzipping the file.', $ar_plugin_id, 'e');
                               
                    }
                }
            }
            /***Hotspot saving***/
            if (!empty($_POST['_ar_hotspots'])){        

                if ( count($_POST['_ar_hotspots']) ){
                    $hotspot_link = isset($_POST['_ar_hotspots']['link']) ? array_map('sanitize_text_field', wp_unslash($_POST['_ar_hotspots']['link'])) : array();

                    $hotspot_annotation = isset($_POST['_ar_hotspots']['annotation']) ? array_map('sanitize_text_field', wp_unslash($_POST['_ar_hotspots']['annotation'])) : array();

                    $hotspot_normal = isset($_POST['_ar_hotspots']['data-normal']) ? array_map('sanitize_text_field', wp_unslash($_POST['_ar_hotspots']['data-normal'])) : array();

                    $hotspot_position = isset($_POST['_ar_hotspots']['data-position']) ? array_map('sanitize_text_field', wp_unslash($_POST['_ar_hotspots']['data-position'])) : array();

                    //$sanitized_hotspot = sanitize_post_var_array('_ar_hotspots');
                    //print_r($sanitized_hotspot);
                    $sanitized_hotspot = array(
                                    'data-normal' => $hotspot_normal,
                                    'data-position' => $hotspot_position,
                                    'annotation' => $hotspot_annotation,
                                    'link' => $hotspot_link,
                                );
                    $_ar_hotspots = count($sanitized_hotspot) ? wp_json_encode($sanitized_hotspot) : '';

                    update_post_meta( $post_id, '_ar_hotspots', $sanitized_hotspot );                       
                    //die($_ar_hotspots);
                }
            }
            /***Model Conversion***/
            /***if model file for conversion then convert and put gltf into _glb_file***/
            $allowed_files=array('dxf', 'dae', '3ds','obj','pdf','ply','stl','zip');
            if (isset($path_parts['extension'])){
                if ((in_array(strtolower($path_parts['extension']),$allowed_files))AND($zip_gltf=='')){
                    WP_Filesystem();
                    $upload_dir = wp_upload_dir();
                    $destination_file = $upload_dir['path'].'/'.$path_parts['filename'].'.glb';;
                    /*$open = fopen( $destination_file, "w" ); 
                    $write = fputs( $open,  ar_model_conversion(sanitize_text_field(wp_unslash( $_POST['_glb_file'] ))) ); 
                    fclose( $open );*/

                    ar_wp_custom_file_write($destination_file, ar_model_conversion(sanitize_text_field(wp_unslash( $_POST['_glb_file'] ))));

                    $_POST['_glb_file']= $path_parts['dirname'].'/'.$path_parts['filename'].'.glb';
                }
            }
            
            update_post_meta( $post_id, '_glb_file', sanitize_text_field(wp_unslash( $_POST['_glb_file'] )) );
        endif;

        if ((isset( $_POST['_usdz_file'] )) OR( isset($_POST['_glb_file']))){

            update_post_meta( $post_id, '_ar_placement', ( isset($_POST['_ar_placement']) ? sanitize_text_field(wp_unslash($_POST['_ar_placement'])) : '') );
            update_post_meta( $post_id, '_ar_display', '1' );
        }else{
            update_post_meta( $post_id, '_ar_display', '' );
        }

        update_option( 'ar_open_tabs', ( isset($_POST['ar_open_tabs']) ? sanitize_text_field(wp_unslash($_POST['ar_open_tabs'])) : ''));
        $field_array=array('_skybox_file','_ar_environment','_ar_qr_image','_ar_qr_destination','_ar_qr_destination_mv','_ar_variants','_ar_rotate','_ar_prompt','_ar_x','_ar_y','_ar_z','_ar_field_of_view','_ar_zoom_out','_ar_zoom_in','_ar_exposure','_ar_camera_orbit','_ar_environment_image','_ar_shadow_intensity','_ar_shadow_softness','_ar_resizing','_ar_view_hide','_ar_qr_hide','_ar_hide_dimensions','_ar_hide_reset','_ar_animation','_ar_autoplay','_ar_animation_selection','_ar_emissive','_ar_light_color','_ar_disable_zoom','_ar_rotate_limit','_ar_compass_top_value','_ar_compass_bottom_value','_ar_compass_left_value','_ar_compass_right_value','_ar_cta','_ar_cta_url','_ar_css_override','_ar_css_positions','_ar_css','_ar_mobile_id','_ar_alternative_id','_ar_framed','_ar_frame_color','_ar_frame_opacity');

        foreach ($field_array as $k => $v){
            if ( isset( $_POST[$v] ) ) {
              
                update_post_meta( $post_id, $v, sanitize_text_field(wp_unslash($_POST[$v])) );
            }else{
                update_post_meta( $post_id, $v, '');
            }
        }
        update_post_meta( $post_id, '_ar_shortcode', '[ardisplay id='.$post_id.']');
        //return;
        //wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
        return;
  
    }
 }

// Add a "Duplicate" link to each post in the post list for armodels custom post type
add_filter('post_row_actions', 'ar_add_duplicate_link', 10, 2);

function ar_add_duplicate_link($actions, $post) {
    // Check if the post type is 'armodels'
    if ($post->post_type == 'armodels') {
        // Add the duplicate link
        $actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=ar_duplicate_post&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce' ) . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
    }
    return $actions;
}

// Handle the duplicate post action
add_action('admin_action_ar_duplicate_post', 'ar_duplicate_post_as_draft');

function ar_duplicate_post_as_draft() {
    // Security check: Verify nonce and ensure the user has the appropriate capability
    if (!isset($_GET['duplicate_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['duplicate_nonce'])), basename(__FILE__))) {
        wp_die('Security check failed.');
    }

    // Get the post ID
    $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;

    // Get the original post
    $post = get_post($post_id);

    if (!isset($post) || $post->post_type !== 'armodels') {
        wp_die('Post not found or not of the correct post type.');
    }

    // Create an array with the duplicated post details
    $new_post = array(
        'post_title'    => $post->post_title . ' (Copy)',
        'post_content'  => $post->post_content,
        'post_status'   => 'draft', // Set status to draft
        'post_type'     => $post->post_type,
        'post_author'   => get_current_user_id(),
        'post_excerpt'  => $post->post_excerpt,
        'post_category' => wp_get_post_categories($post_id),
    );

    // Insert the duplicated post
    $new_post_id = wp_insert_post($new_post);

    // Duplicate all post meta
    $post_meta = get_post_meta($post_id);

    foreach ($post_meta as $key => $values) {
        foreach ($values as $value) {
            update_post_meta($new_post_id, $key, maybe_unserialize($value));
        }
    }

    // Redirect to the newly created draft
    wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
    exit;
}



if(!function_exists('ar_redirect_after_save_post')){
    add_action('save_post', 'ar_redirect_after_save_post', 10, 3);

    function ar_redirect_after_save_post($post_id, $post, $update) {

        if (!$update && $post->post_type === 'armodels') {
            // Ensure this is not an auto-save or a revision
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            set_transient('ar_redirect_after_save_post', $post_id, 30);
        }
    }
}

if(!function_exists('ar_check_redirect_armodels')){
    add_action('admin_notices', 'ar_check_redirect_armodels');

    function ar_check_redirect_armodels() {

        $post_id = get_transient('ar_redirect_after_save_post');
        if ($post_id) {

            delete_transient('ar_redirect_after_save_post');

            // Redirect
            wp_redirect(admin_url('post.php?post=' . $post_id . '&action=edit'));
            exit; // Always call exit after a redirect
        }
    }
}
?>