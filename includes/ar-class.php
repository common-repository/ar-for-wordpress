<?php
/**
 * AR Display
 * https://augmentedrealityplugins.com
**/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}



class AR_Plugin {

    /**
     * Constructor
     */
    public $ar_model_html;

    public function __construct() {
        // Add any necessary hooks or filters here.
        $this->ar_model_html = '';
    }

    /**
     * Initialize the plugin.
     */
    public function run() {
        add_shortcode( 'ardisplay', [ $this, 'ar_display_shortcode' ] );
        add_shortcode( 'ar-display', [ $this, 'ar_display_shortcode' ] );
        add_shortcode( 'ar-view', [ $this, 'ar_view_shortcode' ] );
        add_shortcode('ar-qr', [ $this, 'ar_qrcode_shortcode' ]);
        add_shortcode('ar-gallery', [ $this, 'ar_gallery_shortcode' ]);
        add_shortcode('ar-user-upload', [ $this, 'ar_user_upload' ]);
    }

    
    public function ar_display_shortcode($atts, $variation_id='') {
        global $ar_plugin_id, $ar_wc_active, $ar_wp_active;
        $model_count = ar_model_count();

        $suffix = $variation_id ? "_var_".$variation_id : '';

        //Check if on a mobile and it supports AR.
        extract(get_screen_type());

        if ((get_option('ar_licence_valid')=='Valid')OR($model_count<=1)){
            
            if ((!is_array($atts)) OR (!isset($atts['id']))) {
                $atts['id'] = get_the_ID(); // Use the current post ID if none set
            }

            // Check if a glb file exists
            $glb_file = get_post_meta($atts['id'], '_glb_file'.$suffix, true);
            
            if (!$glb_file) { // If glb file is not found for the main product
                $arpost = get_post($atts['id']);
                if (isset($arpost->post_type) && $arpost->post_type === 'product') { // Ensure it's a product
                    $product = wc_get_product($atts['id']); // Get the WooCommerce product object
                    
                    if ($product && $product->is_type('variable')) { // Check if the product is a variable product
                        $available_variations = $product->get_children(); // Get all variation IDs
            
                        foreach ($available_variations as $variation_id) {
                            $suffix = "_var_" . $variation_id;
                            // Check if the variation has the '_glb_file' meta
                            $variation_glb_file = get_post_meta($variation_id, '_glb_file' . $suffix, true);
                            
                            if ($variation_glb_file) {
                                $glb_file = ar_get_secure_model_url($variation_glb_file); // If found, set the glb file
                                $atts['id'] = $variation_id;
                                break; // Exit the loop as we found a valid glb file
                            }
                        }
                    } elseif ($arpost->post_type === 'product_variation') { // Check if it's a product variation
                        $suffix = "_var_" . $atts['id'];
                        $glb_file = ar_get_secure_model_url(get_post_meta($atts['id'], '_glb_file' . $suffix, true));
                    }
                }
            }
            
            // If $glb_file is still empty or not found, you can handle the case here, e.g., return a default value or an error message.
            if (!$glb_file) {
                // Display an empty Model Viewer
               // return;
            } 
            $model_array=array();
            $ar_model = new AR_Model($atts, $variation_id);
        	$model_array = $ar_model->model_array;

            $model_array['ar_model_atts']=$atts;
             
            $alt_output = '';
            
            if ($ar_alternative_id = get_post_meta($atts['id'], '_ar_alternative_id', true )){                
                if(($isMob) OR ($isTab) OR ($isIPhone) OR ($isIPad) OR ($isAndroid)){  
                    $alt_output = $this->ar_alternative_model($ar_alternative_id, $suffix);
                }
            }
            
            $output = $this->ar_display_model_viewer($model_array);
            $output = $output.$alt_output;
            $output .= $this->ar_display_popup($model_array);
            
            //}
        }else{
            //Invalid Licence
            if ($ar_plugin_id=='ar-for-wordpress'){
                $output = '<a href="/wp-admin/edit.php?post_type=armodels&page">';
            }else{
                $output = '<a href="/wp-admin/admin.php?page=wc-settings&tab=ar_display">';
            }
            $output .= '<b>'.ar_output('AR Display Limits Exceeded', $ar_plugin_id ).'</b><br>';
            $output .= ar_output('Check Settings', $ar_plugin_id ).'</a> - <a href="https://augmentedrealityplugins.com" target="_blank">'.ar_output('Sign Up for Premium', $ar_plugin_id ).'</a>';
            
        }
        return $output;
    }

    /* AR Popup/fullscreen model display */
    public function ar_display_popup($model_array){

    	$suffix = $model_array['variation_id'] ? "_var_".$model_array['variation_id'] : '';

    	//Fullscreen option - if not disabled in settings
        if ((!isset($model_array['ar_hide_fullscreen']))OR($model_array['ar_hide_fullscreen']=='')){
            $model_array['ar_pop']='pop';
            $model_array['skybox_file'] = get_post_meta($model_array['id'], '_skybox_file'.$suffix, true );
            $popup_output ='
            <div id="ar_popup_'.$model_array['model_id'].'" class="ar_popup">
                <div class="ar_popup-content">
                    '.$this->ar_display_model_viewer($model_array, $model_array['model_id']).'
                </div>
            </div>';

            add_action( 'wp_footer', function( $arg ) use ( $popup_output,$model_array ) {
                echo wp_kses($popup_output, ar_allowed_html());
                ?>
                <script>  
                    jQuery(document).ready(function(){       
                        var options = {                                                   
                            ar_x: <?php echo $model_array['ar_x'] ? esc_html($model_array['ar_x']) : "''";?>,
                            ar_y: <?php echo $model_array['ar_y'] ? esc_html($model_array['ar_y']) : "''";?>,
                            ar_z: <?php echo $model_array['ar_z'] ? esc_html($model_array['ar_z']) : "''";?>,
                            ar_pop: '<?php echo esc_html($model_array['ar_pop']);?>',
                            ar_dimensions_units: '<?php echo esc_html($model_array['ar_dimensions_units']);?>',
                            ar_hide_fullscreen: 'false',
                            ar_model_list: <?php echo count($model_array['ar_model_list']);?>,
                            ar_variants: '<?php echo esc_html($model_array['ar_variants']); ?>',
                            id: <?php echo esc_html($model_array['ar_model_atts']['id']);?>,
                        };
                        
                        var model_<?php echo esc_html($model_array['model_id']);?> = new ARModelViewer('<?php echo esc_html($model_array['model_id']);?>',options);
                    });

                </script>
                <?php
            } );
            add_action( 'admin_footer', function( $arg ) use ( $popup_output,$model_array ) {
                echo wp_kses($popup_output, ar_allowed_html());
                ?>
                <script> 
                    jQuery(document).ready(function(){         
                        var options = {                                                   
                            ar_x: <?php echo $model_array['ar_x'] ? esc_html($model_array['ar_x']) : "''";?>,
                            ar_y: <?php echo $model_array['ar_y'] ? esc_html($model_array['ar_y']) : "''";?>,
                            ar_z: <?php echo $model_array['ar_z'] ? esc_html($model_array['ar_z']) : "''";?>,
                            ar_pop: '<?php echo esc_html($model_array['ar_pop']);?>',
                            ar_dimensions_units: '<?php echo esc_html($model_array['ar_dimensions_units']);?>',
                            ar_hide_fullscreen: <?php echo $model_array['ar_hide_fullscreen'] ? 'true' : 'false';?>,
                            ar_model_list: <?php echo count($model_array['ar_model_list']);?>,
                            ar_variants: '<?php echo esc_html($model_array['ar_variants']); ?>',
                            id: <?php echo esc_html($model_array['ar_model_atts']['id']);?>,
                        };
                        
                        var model_<?php echo esc_html($model_array['model_id']);?> = new ARModelViewer('<?php echo esc_html($model_array['model_id']);?>',options);
                    });

                </script>
                <?php
            } );
        }
    }
    /*Populate Size selector on gallery models*/
    public function read_json_and_populate_gallery_selector($model_array, $aspect_ratio, $orientation, $framed, $frame_color, $wpnonce, $thumbnail_id, $url) {
        global $ar_plugin_id;
        // Path to the JSON file
        $json_file_path = esc_url( site_url('/wp-content/plugins/'.$ar_plugin_id.'/assets/models/models.json' ) );
        
        // Read the JSON file contents
        $response = wp_remote_get($json_file_path);
        $json_data = '';

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: ".esc_html($error_message);
        } else {
            $json_data = wp_remote_retrieve_body($response);
            // Handle the data as needed
        }
        
        // Decode JSON data into a PHP array
        $data_array = json_decode($json_data, true);
        
        if ($data_array === null) {
            die('Error decoding JSON data');
        }
        //var_dump($data_array);die();
        
        // Check if the ratio exists in the data array
        $aspect_ratio = (string) $aspect_ratio;
        if (!isset($data_array[$aspect_ratio])) {
            // Get the available ratios from the data array
            $available_ratios = array_keys($data_array);
            // Find the closest ratio
            $aspect_ratio = ar_findClosestRatio($aspect_ratio, $available_ratios);
        }
        // Populate the model link
        if((isset($url))&&($url!='')){
            $model_link = site_url('/wp-content/plugins/'.$ar_plugin_id.'/includes/ar-gallery.php?url=' . $url . '&ratio=' . $aspect_ratio . '&o=' . $orientation . '&f=' . $framed . '&fc=' . $frame_color . '&_wpnonce=' . $wpnonce);
        }elseif (isset($thumbnail_id)&&($thumbnail_id!='')){
            $model_link = site_url('/wp-content/plugins/'.$ar_plugin_id.'/includes/ar-gallery.php?id=' . $thumbnail_id . '&ratio=' . $aspect_ratio . '&o=' . $orientation . '&f=' . $framed . '&fc=' . $frame_color . '&_wpnonce=' . $wpnonce);
        }
        $model_array['glb_file'] = $model_link;
    
        // Populate the gallery selector
        if (isset($data_array[$aspect_ratio])) {
                $model_types = $data_array[$aspect_ratio];
                // Flatten $model_types if needed
                $flat_model_types = ar_array_flatten($model_types);
                
                // Implode after flattening
                $model_array['ar_gallery_selector'] = implode(',', $flat_model_types);
            } 
        return $model_array;
    }
    /* AR Gallery model display */
    public function ar_gallery_shortcode($model_array){
        global $post, $ar_plugin_id;
        // Get the featured image ID
        $thumbnail_id = get_post_thumbnail_id($post->ID);
        
        // Get image metadata
        $image_metadata = wp_get_attachment_metadata($thumbnail_id);
    
        if (!$image_metadata || !isset($image_metadata['width']) || !isset($image_metadata['height'])) {
            return 'Image metadata not available';
        }
    
        // Get width and height
        $width = $image_metadata['width'];
        $height = $image_metadata['height'];
        
        if ($height == 0) {
            return 'Invalid height';
        }
    
        // Calculate aspect ratio
        $aspect_ratio = 1;
        if ($width > $height){
            $orientation = 'l';
            $aspect_ratio = round($width / $height, 2);
        } elseif ($width < $height){
            $orientation = 'p';
            $aspect_ratio = round($height / $width, 2);
        }
        
        // Initialize the model array and attributes
        $atts = array();
        $atts['id'] = $thumbnail_id;
        $ar_model = new AR_Model($atts);
        $model_array = $ar_model->model_array;
        $model_array['ar_model_atts'] = $atts;
        $wpnonce = esc_html(wp_create_nonce( 'ar_secure_nonce' ));
        $framed = $model_array['ar_framed'];
        $frame_color = $model_array['ar_frame_color'];

        //print_r($model_array);die();

        // Populate gallery selector using the new function
        $model_array = $this->read_json_and_populate_gallery_selector($model_array, $aspect_ratio, $orientation, $framed, $frame_color, $wpnonce, $thumbnail_id, '');
    
        // Generate and return output
        $output = $this->ar_display_model_viewer($model_array);
        $output .= $this->ar_display_popup($model_array);
        
        return $output;
    }        

    /************* AR View Short Code Display *******************/
    public function ar_view_shortcode($atts) { 
        global $ar_plugin_id;
        

        if (get_option('ar_licence_valid')=='Valid'){
            $logo='';
            $ar_button_default='';
            if (get_option('ar_view_file')==''){
                $logo=esc_url( plugins_url( "assets/images/ar-view-btn.png", dirname(__FILE__) ) );
                $ar_button_default=' ar-button-default';
            }else{
                $logo=get_option('ar_view_file');
            }

            if ((!isset($atts))||(!isset($atts['id']))){
                return 'Please include AR model id in shortcode. [ar-view id=X]';
            }else{
                $mdl_id = $atts['id'];
                $atts['ar_hide_model']='1';
                $atts['ar_enable_fullscreen']='1';
                if (get_option('ar_view_in_ar')){
                    $ar_view_text = get_option('ar_view_in_ar');
                }else{
                    $ar_view_text =ar_output('View in AR', $ar_plugin_id );
                }
                if (get_option('ar_view_in_3d')){
                    $ar_view_text_3d = get_option('ar_view_in_3d');
                }else{
                    $ar_view_text_3d =ar_output('View in 3D', $ar_plugin_id );
                }
                $atts['ar_show_close_on_devices']='1';
                $ar_not_supported =ar_output('Your device does not support Augmented Reality. You can view the 3D model or scan the QR code with an AR supported mobile device.', $ar_plugin_id );
                //Check if on a mobile and it supports AR.
                extract(get_screen_type());

                if(($isMob) OR ($isTab) OR ($isIPhone) OR ($isIPad) OR ($isAndroid)){  
                    //Mobile
                    //If Alternative mobile ID exists then display mobile model if viewing on mobile or tablet
                    if ($mob_id = get_post_meta($atts['id'], '_ar_mobile_id', true )){
                        $atts['id'] = $mob_id;
                    }

                    $popup_output = '<div id="arqr_popup_'.$atts['id'].'" class="ar_popup" style="display:none;"><div class="ar_popup-content arqr_popup-content"><div id="ar_qr_'.$atts['id'].'" class=" arqr_popup-container">'.$this->ar_qrcode_shortcode($atts).'<p>'.ar_output('Scan the QR with your device to view in Augmented Reality',$ar_plugin_id).'</p></div><div class="ar-popup-btn-container hide_on_devices"><button id="arqr_close_'.$atts['id'].'_pop" class="ar_popup-btn hide_on_devices"  onclick="document.getElementById(\'arqr_popup_'.$atts['id'].'\').style.display = \'none\';"><img src="'.esc_url( plugins_url( "assets/images/close.png", dirname(__FILE__) ) ).'" class="ar-fullscreen_btn-img"></button></div></div></div>';
                    $fallback = urlencode(get_permalink($atts['id']));

                    if ((isset($atts['text']))AND($atts['text']==true)){
                        //Text - Mobile
                        $atts['ar_hide_model']='0';
                        return '<span class="ar_view_text_link '.$ar_button_default.'" id="ar-button-standalone" onclick="document.getElementById(\'ar-button_'.$atts['id'].'\').click()">'.$ar_view_text.'</span> / <span class="ar_view_text_link ar_cursor_pointer '.$ar_button_default.'" id="ar-button-standalone_'.$atts['id'].'" onclick="document.getElementById(\'ardisplay_viewer_'.$atts['id'].'_pop\').classList.remove(\'nodisplay\');document.getElementById(\'ar_popup_'.$atts['id'].'\').style.display = \'block\'; ">'.$ar_view_text_3d.'</span>'.$this->ar_display_shortcode($atts).'<script languange="javascript">document.getElementById(\'model_'.$atts['id'].'\').classList.add(\'nodisplay\');</script>';

                    }elseif ((isset($atts['buttons']))AND($atts['buttons']==true)){
                        $atts['ar_hide_model']='0';

                        if($isAndroid){
                            $glb_file = get_post_meta($atts['id'], '_glb_file', true );

                            if($ar_alternative_id = get_post_meta($mdl_id, '_ar_alternative_id', true )){
                                
                                $glb_file = get_post_meta($ar_alternative_id, '_glb_file', true );
                                if (substr(sanitize_text_field($glb_file),0,7)=='http://'){
                                    $glb_file = 'https://'.substr(sanitize_text_field($glb_file),7);
                                }

                            } else {
                                if (substr(sanitize_text_field($glb_file),0,7)=='http://'){
                                    $glb_file = 'https://'.substr(sanitize_text_field($glb_file),7);
                                }
                            }

                            $ar_plcmnt  = get_post_meta($atts['id'], '_ar_placement', true );
                           

                            $enable_vertical = '';

                            if($ar_plcmnt == 'wall'){
                                $enable_vertical = 'enable_vertical_placement=true&';
                            }

                            $arview_btn = '<a class="button ar_button" href="intent://arvr.google.com/scene-viewer/1.0?mode=ar_preferred&'.$enable_vertical.'disable_occlusion=true&file='.$glb_file.'#Intent;scheme=https;package=com.google.ar.core;action=android.intent.action.VIEW;S.browser_fallback_url='.$fallback.';end">'.$ar_view_text.'</a>';

                        } else {
                            $arview_btn = '<span class="button ar_button" id="ar-button-standalone" onclick="document.getElementById(\'ar-button_'.$atts['id'].'\').click();">'.$ar_view_text.'</span>';
                        }
                        return $arview_btn.'  <span class="button ar_button ar_cursor_pointer" id="ar-button-standalone_'.$atts['id'].'" onclick="document.getElementById(\'ardisplay_viewer_'.$atts['id'].'_pop\').classList.remove(\'nodisplay\');document.getElementById(\'ar_popup_'.$atts['id'].'\').style.display = \'block\';">'.$ar_view_text_3d.'</span>'.$this->ar_display_shortcode($atts).'<script languange="javascript">document.getElementById(\'model_'.$atts['id'].'\').classList.add(\'nodisplay\'); jQuery(function() { jQuery(\'#ardisplay_viewer_'.$atts['id'].'_pop .ar-popup-btn-container\').removeClass(\'hide_on_devices\'); jQuery(\'#ardisplay_viewer_'.$atts['id'].'_pop  #ar_close_'.$atts['id'].'_pop\').removeClass(\'hide_on_devices\'); });</script>';
                    }else{
                        //AR Logo - Mobile
                        if($isAndroid){

                            $glb_file = get_post_meta($atts['id'], '_glb_file', true );

                            if (substr(sanitize_text_field($glb_file),0,7)=='http://'){
                                $glb_file = 'https://'.substr(sanitize_text_field($glb_file),7);
                            }

                            $ar_plcmnt  = get_post_meta($atts['id'], '_ar_placement', true );
                           

                            $enable_vertical = '';

                            if($ar_plcmnt == 'wall'){
                                $enable_vertical = 'enable_vertical_placement=true&';
                            }

                            return $this->ar_display_shortcode($atts).'<a class="ar-button_standalone '.$ar_button_default.'" href="intent://arvr.google.com/scene-viewer/1.0?mode=ar_preferred&'.$enable_vertical.'disable_occlusion=true&file='.$glb_file.'#Intent;scheme=https;package=com.google.ar.core;action=android.intent.action.VIEW;S.browser_fallback_url='.$fallback.';end"><img id="ar-img_'.$atts['id'].'" src="'.$logo.'" class="ar-button-img"></a>';

                        } else {

                            return $this->ar_display_shortcode($atts).'<span class="ar-button_standalone '.$ar_button_default.'" id="ar-button-standalone" onclick="document.getElementById(\'ar-button_'.$atts['id'].'\').click();"><img id="ar-img_'.$atts['id'].'" src="'.$logo.'" class="ar-button-img"></span>';
                        }
                    }    
                }else{ 
                    //Desktop

                    $atts['ar_hide_model']='0';
                    $atts['ar_qr_large']='1';
                    $popup_output = '<div id="arqr_popup_'.$atts['id'].'" class="ar_popup" style="display:none;"><div class="ar_popup-content arqr_popup-content"><div id="ar_qr_'.$atts['id'].'" class=" arqr_popup-container">'.$this->ar_qrcode_shortcode($atts).'<p>'.ar_output('Scan the QR with your device to view in Augmented Reality',$ar_plugin_id).'</p></div><div class="ar-popup-btn-container hide_on_devices"><button id="arqr_close_'.$atts['id'].'_pop" class="ar_popup-btn hide_on_devices"  onclick="document.getElementById(\'arqr_popup_'.$atts['id'].'\').style.display = \'none\';"><img src="'.esc_url( plugins_url( "assets/images/close.png", dirname(__FILE__) ) ).'" class="ar-fullscreen_btn-img"></button></div></div></div>';
                    if (((isset($atts['text']))AND($atts['text']==true))OR((isset($atts['buttons']))AND($atts['buttons']==true))){
                        add_action( 'wp_footer', function( $arg ) use ( $popup_output ) {
                            //echo wp_kses($popup_output, ar_allowed_html());
                            ar_return_file ($popup_output);
                        } );
                    }
                    if ((isset($atts['text']))AND($atts['text']==true)){
                        //Text  
                        return '<span class="ar_view_text_link ar_cursor_pointer '.$ar_button_default.'" id="ar-button-standalone" onclick="document.getElementById(\'arqr_popup_'.$atts['id'].'\').style.display = \'block\';document.getElementById(\'ar-qrcode\').classList.add(\'ar-qrcode-large\');">'.$ar_view_text.'</span> / 
                       <span class="ar_view_text_link ar_cursor_pointer '.$ar_button_default.'" id="ar-button-standalone_'.$atts['id'].'" onclick="document.getElementById(\'ardisplay_viewer_'.$atts['id'].'_pop\').classList.remove(\'nodisplay\');document.getElementById(\'ar_popup_'.$atts['id'].'\').style.display = \'block\';">'.$ar_view_text_3d.'</span>'.$this->ar_display_shortcode($atts).'<script languange="javascript">document.getElementById(\'model_'.$atts['id'].'\').classList.add(\'nodisplay\');</script>';

                    }elseif ((isset($atts['buttons']))AND($atts['buttons']==true)){
                        //Buttons      
                        return '<span class="button ar_button ar_cursor_pointer" id="ar-button-standalone" onclick="document.getElementById(\'arqr_popup_'.$atts['id'].'\').style.display = \'block\';document.getElementById(\'ar-qrcode\').classList.add(\'ar-qrcode-large\');">'.$ar_view_text.'</span> 
                       <span class="button ar_button ar_cursor_pointer" id="ar-button-standalone_'.$atts['id'].'" onclick="document.getElementById(\'ardisplay_viewer_'.$atts['id'].'_pop\').classList.remove(\'nodisplay\');document.getElementById(\'ar_popup_'.$atts['id'].'\').style.display = \'block\';">'.$ar_view_text_3d.'</span>'.$this->ar_display_shortcode($atts).'<script languange="javascript">document.getElementById(\'model_'.$atts['id'].'\').classList.add(\'nodisplay\');</script>';

                    }else{
                        //AR Logo
                        return $this->ar_display_shortcode($atts).'<span class="ar-button_standalone ar_cursor_pointer '.$ar_button_default.'" id="ar-button-standalone_'.$atts['id'].'" onclick="document.getElementById(\'ardisplay_viewer_'.$atts['id'].'_pop\').classList.remove(\'nodisplay\');document.getElementById(\'ar_popup_'.$atts['id'].'\').style.display = \'block\';"><img id="ar-img_'.$atts['id'].'" src="'.$logo.'" class="ar-button-img"></span><script languange="javascript">document.getElementById(\'model_'.$atts['id'].'\').classList.add(\'nodisplay\');</script>';
                    }
                }
            }
        }
    }


    /************* QR Code Short Code Display *******************/
    public function ar_qrcode_shortcode($atts) { 
        if (get_option('ar_licence_valid')=='Valid'){
            global $wp;

            $qr_logo_image=esc_url( plugins_url( "assets/images/app_logo.png", dirname(__FILE__) ) );
                if (get_option('ar_qr_file')!=''){
                    $qr_logo_image=get_option('ar_qr_file');
                }
            //Check ar_qr_destination and if ids then pass shortcode ids to ar_qr_code, otherwise use url of parent page 
            $ar_qr_url = home_url( $wp->request );

            $ar_qr_destination='';
            if (isset($atts['id'])){
                //If Alternative mobile ID exists then display mobile model if viewing on mobile or tablet
                if ($mob_id = get_post_meta($atts['id'], '_ar_mobile_id', true )){
                    //Check if on a mobile and it supports AR.
                    extract(get_screen_type());

                    if(($isMob) OR ($isTab) OR ($isIPhone) OR ($isIPad) OR ($isAndroid)){  
                        $atts['id'] = $mob_id;
                    }
                }
                $ar_qr_destination=get_post_meta($atts['id'], '_ar_qr_destination_mv', true );
            }
            if ($ar_qr_destination==''){
                $ar_qr_destination=get_option('ar_qr_destination') ? get_option('ar_qr_destination') : 'parent-page';
            }
            if (isset($atts['id'])){
                if (get_post_meta( $atts['id'], '_ar_qr_destination', true )){
                        $ar_qr_destination=get_post_meta( $atts['id'], '_ar_qr_destination', true ).'"';
                }
            }

            if ($ar_qr_destination == 'model-viewer'){
                if (isset($atts['cat'])){
                    $ar_qr_url = get_site_url().'?ar-cat='.$atts['cat'];
                }elseif (isset($atts['id'])){
                    $ar_qr_url = get_site_url().'?ar-view='.$atts['id'];
                }
            }

             $ar_attid = 0;
            if (isset($atts['cat'])){
                $ar_attid = $atts['cat'];
            }elseif (isset($atts['id'])){
                $ar_attid = $atts['id'];
            }

            $ar_qr_image_data = base64_encode(ar_qr_code($qr_logo_image,$ar_attid,$ar_qr_url));
            $ar_qr_large ='';
            if (isset($atts['ar_qr_large'])){
                $ar_qr_large = 'ar-qrcode-large';
            }
            if ($ar_qr_image_data!=''){
                return '<button id="ar-qrcode" type="button" class="ar-qrcode_standalone hide_on_devices '.$ar_qr_large.'" onclick="this.classList.toggle(\'ar-qrcode-large\');" style="background-image: url(\'data:image/png;base64,'.$ar_qr_image_data.'\');"></button>';
            }
        }
    }
    
    /************* User Upload 3D Model Viewer Shortcode *******************/
    public function ar_user_upload($atts) { 
        if (get_option('ar_licence_valid') == 'Valid') {
            global $wp, $post, $ar_plugin_id;
            $output = array();
            $output=ar_user_upload_wp($atts);
            
            
            return $output;
        }
    }
    



    /*********** Display the AR Model Viewer ***********/
    function ar_display_model_viewer($model_array, $atts_id=''){
        
        global $model_viewer_js_loaded, $ar_scale_js;
        global $wp, $ar_plugin_id, $ar_whitelabel, $ar_css_names, $ar_css_styles;

        $output='';
        $model_viewer_js = '';

        $model_style='';
        $model_id =  $model_array['model_id'];
        if ($model_array['skybox_file']!=''){
            $model_array['skybox_file']=' skybox-image="'.$model_array['skybox_file'].'"';
        }
        if ($model_array['ar_pop']=='pop'){
            $model_array['model_id'].='_'.$model_array['ar_pop'];
        }
        if ($model_array['ar_resizing']==1){
            $model_array['ar_resizing']=' ar-scale="fixed"';
        }
        if ($model_array['ar_scene_viewer']==1){
            $viewers = 'scene-viewer webxr quick-look';
        }else{
            $viewers = 'webxr scene-viewer quick-look';
        }
        if ($model_array['ar_hide_arview']!=''){
           $model_array['ar_hide_arview'] = ' nodisplay';
           $show_ar='';
        }else{
            $show_ar=' ar ar-modes="'.$viewers.'" ';
        }
        if ($model_array['ar_hide_model']!=''){
           $model_array['ar_hide_model'] = ' nodisplay';
           $model_array['ar_hide_arview'] = '';
           $show_ar=' ar ar-modes="'.$viewers.'" ';
        }
        if ($model_array['ar_autoplay']!=''){
            $model_array['ar_autoplay'] = 'autoplay';                
        }
        if ($model_array['ar_disable_zoom']!=''){
            $model_array['ar_disable_zoom'] = 'disable-zoom';                
        }
        if ($model_array['ar_field_of_view']!=''){
            $model_array['ar_field_of_view'] = 'field-of-view="'.$model_array['ar_field_of_view'].'deg"';                
        }else{
            $model_array['ar_field_of_view'] = 'field-of-view=""';
        }
        if (!isset($model_array['ar_qr_image'])){
            $model_array['ar_qr_image']='';
        }
        $min_theta = 'auto';
        $min_pi = 'auto';
        $min_zoom = '20%';
        $max_theta = 'Infinity';
        $max_pi = 'auto';
        $max_zoom = '300';
        if (($model_array['ar_zoom_in']!='')AND($model_array['ar_zoom_in']!='default')){
            $model_array['ar_zoom_in'] = 100 - $model_array['ar_zoom_in'];
            //$ar_zoom_in_output = 'min-camera-orbit="auto auto '.$model_array['ar_zoom_in'].'%"';  
            $min_zoom = $model_array['ar_zoom_in'].'%"';
        }else{
            //$ar_zoom_out_output = 'min-camera-orbit="Infinity auto 20%"';
        }
        
        if (($model_array['ar_zoom_out']!='')AND($model_array['ar_zoom_out']!='default')){
            $model_array['ar_zoom_out'] = (($model_array['ar_zoom_out']/100)*400)+100;
            $ar_zoom_out_output = 'max-camera-orbit="Infinity auto '.$model_array['ar_zoom_out'].'%"'; 
            $max_zoom = $model_array['ar_zoom_out'].'%"';
        }else{
            //$ar_zoom_in_output = 'max-camera-orbit="Infinity auto 300%"';
        }
        
        //set the X and Y rotation limits in min-camera-orbit and max-camera-orbit
        //
        //
        //
        if ($model_array['ar_rotate_limit']!=''){
            if ($model_array['ar_compass_top_value']!=''){
                $min_pi = $model_array['ar_compass_top_value'];
            } 
            if ($model_array['ar_compass_bottom_value']!=''){
                $max_pi = $model_array['ar_compass_bottom_value'];
            }
            if ($model_array['ar_compass_left_value']!=''){
                $min_theta = $model_array['ar_compass_left_value'];
            }
            if ($model_array['ar_compass_right_value']!=''){
                $max_theta = $model_array['ar_compass_right_value'];
            } 
        }
        $ar_zoom_out_output = 'min-camera-orbit="'.$min_theta.' '.$min_pi.' '.$min_zoom.'"';
        $ar_zoom_in_output = 'max-camera-orbit="'.$max_theta.' '.$max_pi.' '.$max_zoom.'"';
        
        
        if ($model_array['ar_exposure']!=''){
            $model_array['ar_exposure'] = 'exposure="'.$model_array['ar_exposure'].'"';                
        }
        if ($model_array['ar_shadow_intensity']!=''){
            $model_array['ar_shadow_intensity'] = 'shadow-intensity="'.$model_array['ar_shadow_intensity'].'"';                
        }
        if ($model_array['ar_shadow_softness']!=''){
            $model_array['ar_shadow_softness'] = 'shadow-softness="'.$model_array['ar_shadow_softness'].'"';                
        }
        if ($model_array['ar_camera_orbit']!=''){
            $model_array['ar_camera_orbit_reset'] = $model_array['ar_camera_orbit'];
            $model_array['ar_camera_orbit'] = 'camera-orbit="'.$model_array['ar_camera_orbit'].'"';                
        }else{
            $model_array['ar_camera_orbit_reset']='';
        }
        if ($model_array['ar_environment_image']!=''){
            $model_array['ar_environment_image'] = 'environment-image="legacy"';                
        }
        if ($model_array['ar_emissive']!=''){
            $model_array['ar_emissive'] = ' emissive ';                
        }
        if ($model_array['ar_light_color']!=''){
            $model_array['ar_light_color'] = 'light-color="'.$model_array['ar_light_color'].'"';               
        }
        
        //If on the admin page
        global $pagenow;
        $hotspot_js_click ='';
        if (( $pagenow == 'post.php' ) ) {
            // editing a page or product
            $hotspot_js_click = 'onclick="addHotspot()"';
        }
                
        if (($model_viewer_js_loaded != 1)OR(is_admin())){
            load_model_viewer_js();
            load_model_viewer_components_js();

            $model_viewer_js = '';
            
            $model_viewer_js_loaded ==1;
        }
        
        extract(get_screen_type());

        $server_host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field( wp_unslash($_SERVER['HTTP_HOST'])) : '';
        $server_uri = isset($_SERVER['REQUEST_URI']) ? esc_url_raw( wp_unslash($_SERVER['REQUEST_URI'])) : '';

        if (($isFireFox) || ($isFireFoxiOS)){
            if ($isAndroid){
                $output .= '<b>For an optimal Augmented Reality experience it is suggested you use a different browser.</b> <a href="intent://'.$server_host.$server_uri.'#Intent;scheme=https;package=com.android.chrome;end">Please click here to open in Chrome.</a>';
            }elseif($isIOS){
                $output .= '<b>For an optimal Augmented Reality experience it is suggested you use a different browser such as Safari or <a href="googlechrome://'.$server_host.$server_uri.'">Chrome.</a></b>';
                
            }
        }  

        if ((isset($model_array['ar_play_file']))AND($model_array['ar_play_file']!='')){
            $play_btn= esc_url( $model_array['ar_play_file'] );
        }else{
            $play_btn= esc_url( plugins_url( "assets/images/ar-play-btn.png", dirname(__FILE__) ) );  
        }
        if ((isset($model_array['ar_pause_file']))AND($model_array['ar_pause_file']!='')){
            $pause_btn= esc_url( $model_array['ar_pause_file'] );
        }else{
            $pause_btn= esc_url( plugins_url( "assets/images/ar-pause-btn.png", dirname(__FILE__) ) );  
        }
        if ($model_array['ar_animation']==true){
            $play_hide='';
        }else{
            $play_hide = 'display:none;';
        }

        //If Alternative AR view model ID exists then display alternative model in

        $output .= $this->get_ar_model_html($model_array); 
        $ar_qr_image_data =  $this->get_ar_qr_html($model_array);

    	$args = ['output'=>$output, 'model_array'=>$model_array, 'ar_qr_image_data'=>$ar_qr_image_data];
    	$args['ar_whitelabel'] = $ar_whitelabel;
    	$args['show_ar'] = $show_ar;
    	$args['ar_zoom_out_output'] = $ar_zoom_out_output;
    	$args['ar_zoom_in_output'] = $ar_zoom_in_output;
    	$args['hotspot_js_click'] = $hotspot_js_click;
    	$args['play_btn'] = $play_btn;
    	$args['play_hide'] = $play_hide;
        $args['play_hide'] = $play_hide;

        //}
        ob_start();
        $this->load_template( 'model-viewer', $args  );
        return $model_viewer_js.ob_get_clean().$this->get_ar_custom_css($model_array) ;

        //return $output;
    }


    public function get_ar_model_html($model_array){
    	$output = '';

    	//$output .= $this->get_ar_qr_html($model_array);
    	$output .= $this->get_ar_popup_html($model_array);
    	$output .= $this->get_ar_slider_html($model_array);
    	$output .= $this->get_ar_hotspots($model_array);
    	//$output .= $this->get_ar_custom_css($model_array);

        return $output;
    }


    public function get_ar_qr_html($model_array){
    	global $wp;

    	$output = '';

    	if ($model_array['ar_hide_qrcode']==''){
            $ar_qr_display = 'block';
        }else{
            $ar_qr_display = 'none';
        }
        $qr_logo_image=esc_url( plugins_url( "assets/images/app_logo.png", dirname(__FILE__) ) );
        $ar_wl_logo = get_option('ar_wl_file'); 
        if ($model_array['ar_qr_file']!=''){
            $qr_logo_image=$model_array['ar_qr_file'];
        }elseif ($ar_wl_logo){ //Show Whitelabel url in QR
            $qr_logo_image=$ar_wl_logo;
        }
        
        /*if($model_array['model_id'] == 'user_upload'){
	        $ar_qr_url = home_url( $wp->request );
	    }else*/
	    if ($model_array['ar_qr_destination'] == 'model-viewer'){
	        if (isset($model_array['ar_model_atts']['cat'])){
	            $ar_qr_url = get_site_url().'?ar-cat='.$model_array['ar_model_atts']['cat'];
	        }elseif (isset($model_array['ar_model_atts']['id'])){
	            //If Alternative mobile ID exists then display mobile model if viewing on mobile or tablet
	            if ($mob_id = get_post_meta($model_array['ar_model_atts']['id'], '_ar_mobile_id', true )){
	                $model_array['ar_model_atts']['id'] = $mob_id;
	            }
	            $ar_qr_url = get_site_url().'?ar-view='.$model_array['ar_model_atts']['id'];
	        }
	    } elseif(($model_array['ar_qr_destination'] == 'parent-page')AND(isset($model_array['ar_model_atts']))){
	        $ar_attid = isset($model_array['ar_model_atts']['cat']) ? $model_array['ar_model_atts']['cat'] : $model_array['ar_model_atts']['id'];
	        $ar_qr_url = esc_url( get_permalink($ar_attid) );

            //if shortcode is ar-gallery
            if($model_array['ar_model_atts']['id']){
                if(get_post_type( $model_array['ar_model_atts']['id'] ) == 'attachment'){
                    $ar_qr_url = home_url( $wp->request );
                }
            }
	    }else {
	        $ar_qr_url = $model_array['ar_qr_destination'];
	    }
        
	    //Custom QR Image or generated QR Code
	    if (isset($model_array['ar_qr_image'])AND($model_array['ar_qr_image']!='')){
	        $ar_qr_image_data = $model_array['ar_qr_image'];
	    }elseif (isset($model_array['ar_model_atts']['id'])){                    
	        $ar_qr_image_data = base64_encode(ar_qr_code($qr_logo_image,$model_array['ar_model_atts']['id'],$ar_qr_url));
	        $ar_qr_image_data = 'data:image/png;base64,'.$ar_qr_image_data;
	        
	    }else{
	        $ar_attid = $model_array['model_id'];
	        $ar_qr_image_data = base64_encode(ar_qr_code($qr_logo_image,$ar_attid,$ar_qr_url));
	    }
	    
        

        return $ar_qr_image_data;
    }


    public function get_ar_popup_html($model_array){
    	//Reset View Button
    	$output = '';
    	$atts_id = $model_array['ar_model_atts']['id'];

        if ($model_array['ar_hide_reset']==1){ $reset_style = 'style="display:none"';}else{$reset_style='';}
        
        $output.='<div class="ar-reset-btn-container">
	        <button id="ar-reset_'.$model_array['model_id'].'" class="ar-reset" '.$reset_style.' onclick="document.getElementById(\'model_'.$model_array['model_id'].'\').setAttribute(\'camera-orbit\', \''.$model_array['ar_camera_orbit_reset'].'\');return getData()"><img src="'.esc_url( plugins_url( "assets/images/reset.png", dirname(__FILE__) ) ).'"></button>
	        </div>';
           
        //If the popup is triggered by the ar-view shortcode then show close button   
        $ar_show_close_on_devices = '';

        if (!isset($model_array['ar_show_close_on_devices'])){
            $ar_show_close_on_devices = 'hide_on_devices';
        }

        $output.='<div class="ar-popup-btn-container '.$ar_show_close_on_devices.'">';
        
        //Fullscreen option - if not disabled in settings
        $ar_hide_fullscreen='';
        if ((!isset($model_array['ar_hide_fullscreen']))OR($model_array['ar_hide_fullscreen']=='')){
            if ($model_array['ar_pop']=='pop'){
                if($atts_id)
                    $mdl_id = $atts_id;
                else
                    $mdl_id = $model_array['model_id'];
                
                $output.='<button id="ar_close_'.$model_array['model_id'].'" class="ar_popup-btn '.$ar_show_close_on_devices.'" onclick="document.getElementById(\'ar_popup_'.$mdl_id.'\').style.display = \'none\';"><img src="'.esc_url( plugins_url( "assets/images/close.png", dirname(__FILE__) ) ).'" class="ar-fullscreen_btn-img"></button>';
            }else{
                if ($model_array['ar_fullscreen_file']!=''){
                    $ar_fullscreen_image = $model_array['ar_fullscreen_file'];
                }else{
                    $ar_fullscreen_image = esc_url( plugins_url( "assets/images/fullscreen.png", dirname(__FILE__) ) );
                }
                
                $output.='<button id="ar_pop_Btn_'.$model_array['model_id'].'" class="ar_popup-btn hide_on_devices" type="button"><img src="'.$ar_fullscreen_image.'" class="ar-fullscreen_btn-img"></button>';
            }
        }
        $output.='</div>';
            
        if ($model_array['ar_variants']!=''){
            $output.='<div class="ar-variant-container"><select  id="variant_'.$model_array['model_id'].'"></select></div> ';
        }

        return $output;
    }


    public function get_ar_slider_html($model_array){
    	/**** Thumbnail Slider ****/
        $output = '';       
        if ($model_array['ar_model_list']!=''){ 
            $model_array['ar_model_list']=array_filter($model_array['ar_model_list']);
            
            $model_array['ar_model_list'] = array_unique($model_array['ar_model_list']);
            if (count($model_array['ar_model_list'])>1){ 
                $open_div = '<div id="ar_slider" class="ar_slider">
                    <div class="ar_slides">';
                $slide_count = 0;
                foreach ($model_array['ar_model_list'] as $k =>$v){
                    $slide_count++;
                    $slide_selected = '';
                    $glb_file = '';
                    $suffix = '';
                    if ($slide_count=='1'){$slide_selected = 'selected';}
                    $arpost = get_post( $v ); 
                    if (isset($arpost->post_type)){
                        if($arpost->post_type == 'product_variation'){
                            $suffix = "_var_".$v;
                        }
                    }
                    $glb_file = ar_get_secure_model_url(get_post_meta($v, '_glb_file'.$suffix, true ));
                    if ($glb_file != '' ){
                        $output.='<button id="ar_btn_'.$v.'" class="ar_slide '.$slide_selected.'" onclick="switchSrc(\'model_'.$model_array['model_id'].'\', this, \''.$glb_file.'\', \''.ar_get_secure_model_url(get_post_meta($v, '_usdz_file'.$suffix, true )).'\')" style="background-image: url(\''.esc_url( get_the_post_thumbnail_url($v) ).'\');"></button>
                        ';
                    }
                }
                $close_div='</div>
                </div>';
            }
            if ($output!=''){
                $output = $open_div.$output.$close_div;
            }
        }
        $output.='<input type="hidden" id="src_'.$model_array['model_id'].'" value="'. ar_get_secure_model_url($model_array['glb_file']).'">';

        return $output;
    }


    public function get_ar_hotspots($model_array){
    	global $ar_plugin_id;

    	$output = '';
    	if ($model_array['ar_hide_dimensions']==''){
            $ar_dimensions_display = 'block';
        }else{
            $ar_dimensions_display = 'none';
        }
        $ar_dimensions_label = esc_html(__('Dimensions', 'ar-for-wordpress' ));
        if ((isset($model_array['ar_dimensions_label']) && $model_array['ar_dimensions_label'] !== '')) {
            $ar_dimensions_label = $model_array['ar_dimensions_label'];
        }
        $output.='
        <button slot="hotspot-dot+X-Y+Z" class="dot nodisplay" data-position="1 -1 1" data-normal="1 0 0"></button>
        <button slot="hotspot-dim+X-Y" class="dimension nodisplay" data-position="1 -1 0" data-normal="1 0 0"></button>
        <button slot="hotspot-dot+X-Y-Z" class="dot nodisplay" data-position="1 -1 -1" data-normal="1 0 0"></button>
        <button slot="hotspot-dim+X-Z" class="dimension nodisplay" data-position="1 0 -1" data-normal="1 0 0"></button>
        <button slot="hotspot-dot+X+Y-Z" class="dot nodisplay" data-position="1 1 -1" data-normal="0 1 0"></button>
        <button slot="hotspot-dim+Y-Z" class="dimension nodisplay" data-position="0 -1 -1" data-normal="0 1 0"></button>
        <button slot="hotspot-dot-X+Y-Z" class="dot nodisplay" data-position="-1 1 -1" data-normal="0 1 0"></button>
        <button slot="hotspot-dim-X-Z" class="dimension nodisplay" data-position="-1 0 -1" data-normal="-1 0 0"></button>
        <button slot="hotspot-dot-X-Y-Z" class="dot nodisplay" data-position="-1 -1 -1" data-normal="-1 0 0"></button>
        <button slot="hotspot-dim-X-Y" class="dimension nodisplay" data-position="-1 -1 0" data-normal="-1 0 0"></button>
        <button slot="hotspot-dot-X-Y+Z" class="dot nodisplay" data-position="-1 -1 1" data-normal="-1 0 0"></button>
        <div id="controls" class="dimension" style="display:'.$ar_dimensions_display.'">
            <label for="show-dimensions_'.$model_array['model_id'].'" style="margin:0px !important;">'.$ar_dimensions_label.':</label>
            <input id="show-dimensions_'.$model_array['model_id'].'" type="checkbox" style="cursor: pointer;">
        </div>';

        return $output;
    }


    public function get_ar_custom_css($model_array){
        global $ar_css_names, $ar_css_styles;
    	$output = '';
        ob_start();
    	if ($model_array['ar_css_positions']!=''){
            if (is_array($model_array['ar_css_positions'])){
                $ar_no_move_ar_button = '';
                $ar_no_move_ar_dimensions = '';
                $ar_no_move_ar_reset = '';
                ?>
                <style>/* Custom CSS Styling */
                <?php
                    foreach($model_array['ar_css_positions'] as $element => $pos){
                        if (($pos != 'Default')AND($element != '')AND($pos != '')){
                            echo esc_attr($ar_css_names[$element]).'{'.esc_attr($ar_css_styles[$pos]).'}';
                            if ($element =='AR Button'){$ar_no_move_ar_button=1;}
                            if ($element =='Dimensions'){$ar_no_move_ar_dimensions=1;}
                            if ($element =='Reset Button'){$ar_no_move_ar_reset=1;}
                        }
                    }
                ?>
                </style>
            <?php  
            }
            if ((isset($model_array['ar_show_close_on_devices']))AND($ar_no_move_ar_button!='1')){
            ?>
                <style> #ar-button_<?php echo esc_html($model_array['model_id']);?>_pop{top:40px !important;}</style>
            <?php
            }
            if (($ar_no_move_ar_reset!='1')AND($ar_no_move_ar_dimensions!='1')AND($model_array['ar_hide_reset']!='1')){
            ?>
                <style> .dimension{left:50px !important;}</style>
            <?php
            }
        }
        if (($model_array['ar_css']!='')AND($model_array['ar_pop']!='pop')){
        ?>
            <style>
                <?php echo esc_attr($model_array['ar_css']); ?>
            </style>
            
        <?php               
        }

        return ob_get_clean();
    }


    /*********** Display the AR Alternate Model ***********/

    public function ar_alternative_model($alt_id, $suffix=''){        
        global $wp, $ar_plugin_id, $ar_whitelabel, $ar_css_names, $ar_css_styles;

        $output='';

        $atts['id'] = $alt_id;
        $ar_model = new AR_Model($atts);
        $model_array = $ar_model->model_array;
        $model_array['ar_model_atts']=$atts;

        if (get_post_meta( $atts['id'], '_ar_environment'.$suffix, true )){
            $model_array['ar_environment']='environment-image="'.get_post_meta( $atts['id'], '_ar_environment'.$suffix, true ).'"';
        }else{
            $model_array['ar_environment']='';
        }

        if ($model_array['skybox_file']!=''){
            $model_array['skybox_file']=' skybox-image="'.$model_array['skybox_file'].'"';
        }        
        if ($model_array['ar_resizing']==1){
            $model_array['ar_resizing']=' ar-scale="fixed"';
        }
        
        if ($model_array['ar_scene_viewer']==1){
            $viewers = 'scene-viewer webxr quick-look';
        }else{
            $viewers = 'webxr scene-viewer quick-look';
        }
       
        $show_ar=' ar ar-modes="'.$viewers.'" ';
        
        if ($model_array['ar_autoplay']!=''){
            $model_array['ar_autoplay'] = 'autoplay';                
        }
        if ($model_array['ar_disable_zoom']!=''){
            $model_array['ar_disable_zoom'] = 'disable-zoom';                
        }
        if ($model_array['ar_field_of_view']!=''){
            $model_array['ar_field_of_view'] = 'field-of-view="'.$model_array['ar_field_of_view'].'deg"';                
        }else{
            $model_array['ar_field_of_view'] = 'field-of-view=""';
        }
        if (!isset($model_array['ar_qr_image'])){
            $model_array['ar_qr_image']='';
        }
        $min_theta = 'auto';
        $min_pi = 'auto';
        $min_zoom = '20%';
        $max_theta = 'Infinity';
        $max_pi = 'auto';
        $max_zoom = '300';
        if (($model_array['ar_zoom_in']!='')AND($model_array['ar_zoom_in']!='default')){
            $model_array['ar_zoom_in'] = 100 - $model_array['ar_zoom_in'];
            //$ar_zoom_in_output = 'min-camera-orbit="auto auto '.$model_array['ar_zoom_in'].'%"';  
            $min_zoom = $model_array['ar_zoom_in'].'%"';
        }else{
            //$ar_zoom_out_output = 'min-camera-orbit="Infinity auto 20%"';
        }
        
        if (($model_array['ar_zoom_out']!='')AND($model_array['ar_zoom_out']!='default')){
            $model_array['ar_zoom_out'] = (($model_array['ar_zoom_out']/100)*400)+100;
            $ar_zoom_out_output = 'max-camera-orbit="Infinity auto '.$model_array['ar_zoom_out'].'%"'; 
            $max_zoom = $model_array['ar_zoom_out'].'%"';
        }else{
            //$ar_zoom_in_output = 'max-camera-orbit="Infinity auto 300%"';
        }
        
        //set the X and Y rotation limits in min-camera-orbit and max-camera-orbit
       
        if ($model_array['ar_rotate_limit']!=''){
            if ($model_array['ar_compass_top_value']!=''){
                $min_pi = $model_array['ar_compass_top_value'];
            } 
            if ($model_array['ar_compass_bottom_value']!=''){
                $max_pi = $model_array['ar_compass_bottom_value'];
            }
            if ($model_array['ar_compass_left_value']!=''){
                $min_theta = $model_array['ar_compass_left_value'];
            }
            if ($model_array['ar_compass_right_value']!=''){
                $max_theta = $model_array['ar_compass_right_value'];
            } 
        }

        $ar_zoom_out_output = 'min-camera-orbit="'.$min_theta.' '.$min_pi.' '.$min_zoom.'"';
        $ar_zoom_in_output = 'max-camera-orbit="'.$max_theta.' '.$max_pi.' '.$max_zoom.'"';
        
        
        if ($model_array['ar_exposure']!=''){
            $model_array['ar_exposure'] = 'exposure="'.$model_array['ar_exposure'].'"';                
        }
        if ($model_array['ar_shadow_intensity']!=''){
            $model_array['ar_shadow_intensity'] = 'shadow-intensity="'.$model_array['ar_shadow_intensity'].'"';                
        }
        if ($model_array['ar_shadow_softness']!=''){
            $model_array['ar_shadow_softness'] = 'shadow-softness="'.$model_array['ar_shadow_softness'].'"';                
        }
        if ($model_array['ar_camera_orbit']!=''){
            $model_array['ar_camera_orbit_reset'] = $model_array['ar_camera_orbit'];
            $model_array['ar_camera_orbit'] = 'camera-orbit="'.$model_array['ar_camera_orbit'].'"';                
        }else{
            $model_array['ar_camera_orbit_reset']='';
        }
        if ($model_array['ar_environment_image']!=''){
            $model_array['ar_environment_image'] = 'environment-image="legacy"';                
        }
        if ($model_array['ar_emissive']!=''){
            $model_array['ar_emissive'] = ' emissive ';                
        }
        if ($model_array['ar_light_color']!=''){
            $model_array['ar_light_color'] = 'light-color="'.$model_array['ar_light_color'].'"';               
        }
        $output.='<div class="ar_alternative_model_container">';
        $output.='<model-viewer id="model_'.$model_array['model_id'].'" '.$show_ar;   
        $output .= '
        ios-src="'.ar_get_secure_model_url($model_array['usdz_file']).'" src="'. ar_get_secure_model_url($model_array['glb_file']).'" 
        '. $model_array['skybox_file'].'
        '. $model_array['ar_environment'].'
        '. $model_array['ar_resizing'].'
        '. $model_array['ar_field_of_view'].'
        '. $ar_zoom_in_output.'
        '. $ar_zoom_out_output.'
        '. $model_array['ar_camera_orbit'].'
        '. $model_array['ar_exposure'].'
        '. $model_array['ar_shadow_intensity'].'
        '. $model_array['ar_shadow_softness'].'
        '. $model_array['ar_environment_image'].' 
        '. $model_array['ar_emissive'].'  
        '. $model_array['ar_light_color'].'  
        poster="'.esc_url( get_the_post_thumbnail_url($model_array['model_id']) ).'"
        alt="AR Display 3D model" 
        class="ar-display-model-viewer" 
        quick-look-browsers="safari chrome" 
        ';

        $output .= $model_array['ar_disable_zoom'].'>';
        
        
        $output.='<button slot="ar-button" data-id="'.$model_array['model_id'].'" class="ar-button ar-button-default " id="ar-button_'.$model_array['model_id'].'"><img id="ar-img_'.$model_array['model_id'].'" src="'.esc_url( plugins_url( "assets/images/ar-view-btn.png", __FILE__ ) ).'" class="ar-button-img"></button>';
           

        $output.='<input type="hidden" id="src_'.$model_array['model_id'].'" value="'. ar_get_secure_model_url($model_array['glb_file']).'">';
        
        $output.='</model-viewer></div>';

        if ((is_numeric($model_array['ar_x']))AND(is_numeric($model_array['ar_y']))AND(is_numeric($model_array['ar_z']))){
            $output.='<script>
            const modelViewerTransform'.$model_array['model_id'].' = document.querySelector("model-viewer#model_'.$model_array['model_id'].'");
            const updateScale'.$model_array['model_id'].' = () => {
              modelViewerTransform'.$model_array['model_id'].'.scale = \''.$model_array['ar_x'].' '.$model_array['ar_y'].' '.$model_array['ar_z'].'\';
            };
            updateScale'.$model_array['model_id'].'();
            </script>';
            $ar_scale_js = 1;
        }
        //wp_die($output);
        return $output;
    }

    /**
     * Load a template file.
     *
     * @param string $template_name The name of the template.
     * @param array $args Arguments to pass to the template.
     */
    private function load_template( $template_name, $args = [] ) {
        extract( $args ); // Extract the args to be used as variables in the template.
        
        //$model_array = $args;
        $template_file = plugin_dir_path( dirname(__FILE__) ) . 'templates/' . $template_name . '.php';

        if ( file_exists( $template_file ) ) {
            include $template_file;
        }
    }
}	
