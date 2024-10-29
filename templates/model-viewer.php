<?php
/**
 * AR Display
 * https://augmentedrealityplugins.com
**/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Hook the enqueue functions to wp_enqueue_scripts
add_action('wp_enqueue_scripts', 'load_model_viewer_js');
add_action('wp_enqueue_scripts', 'load_model_viewer_components_js');
add_action('wp_enqueue_scripts', 'ar_advance_register_style');
add_action('wp_enqueue_scripts', 'ar_advance_register_script');
    extract(get_screen_type());

    $output_atts = '';
    if (($model_array['ar_prompt']==true)){
       $output_atts .= ' interaction-prompt="none"  ';
    }
    if($model_array['ar_rotate']!=true){
       $output_atts .= ' auto-rotate ';
    }
    $output_html = '';
    
    $glb_file = sanitize_text_field($model_array['glb_file']);
    
    $ar_hide_gallery_sizes = get_option('ar_hide_gallery_sizes');

    if (empty($ar_hide_gallery_sizes)) {
        // Parse the URL to get its components
        $url_components = wp_parse_url($glb_file);
        if (isset($url_components['query'])){
            // Extract the query string (the part after the ? in the URL)
            $query_string = $url_components['query'];
            
            // Parse the query string into an associative array
            parse_str($query_string, $query_parts);
            
            if (isset($query_parts['url'])){
                // Now $query_parts will contain the parts of the URL as an associative array
                $url = $query_parts['url']; 
                $ratio = $query_parts['ratio']; 
                $orientation = $query_parts['o']; 
                $framed = isset($query_parts['f']) ? $query_parts['f'] : '';
                $frame_color = isset($query_parts['fc']) ? $query_parts['fc'] : '';
                $wpnonce = esc_html(wp_create_nonce( 'ar_secure_nonce' )); 
                $asset_image = $url;
                $model_array = $this->read_json_and_populate_gallery_selector($model_array, $ratio, $orientation, $framed, $frame_color, $wpnonce, $model_array['id'], $url);
            }
        }
    }
    
    $output_atts .= $model_array['ar_placement'].' 
    ios-src="'.ar_get_secure_model_url($model_array['usdz_file']).'" 
    src="'. ar_get_secure_model_url($model_array['glb_file']).'" 
    '. $model_array['skybox_file'].'
    '. $model_array['ar_environment'].'
    '. $model_array['ar_qr_image'].'
    '. $model_array['ar_qr_destination'].'
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
    '.$model_array['ar_autoplay'];
    if ($model_array['ar_animation'] !=''){
        $output_atts .=' animation-name="'.$model_array['ar_animation_selection'].'" ';
    }
    $output_atts .= $model_array['ar_disable_zoom'];

?>
    <div id="ardisplay_viewer_<?php echo esc_html($model_array['model_id']); ?>" class="ardisplay_viewer<?php echo esc_html($model_array['ar_pop']).esc_html($model_array['ar_hide_model']); ?>">
        <model-viewer id="model_<?php echo esc_html($model_array['model_id']); ?>" camera-controls <?php echo wp_kses($hotspot_js_click, ar_allowed_html()).' '.wp_kses($show_ar, ar_allowed_html()). ' '.wp_kses($output_atts, ar_allowed_html()); ?>>

            <div class="ar-animation-btn-container"><button id="animationButton_<?php echo esc_html($model_array['ar_pop']);?>" slot="hotspot-one" data-position="..." data-normal="..." class="ar-button-animation" type="button"><img src="<?php echo esc_url($play_btn); ?>" class="ar-button-animation" id="ar-button-animation_<?php echo esc_html($model_array['ar_pop']);?>" style="<?php echo esc_html($play_hide); ?>"></button></div>
    <?php
    if ($model_array['ar_view_file']==''){
        if ($ar_whitelabel!=true){
            if($model_array['ar_alternative_id'] && ( ($isMob) OR ($isTab) OR ($isIPhone) OR ($isIPad) OR ($isAndroid) )){
                ?>
                <button data-id="<?php echo esc_html($model_array['model_id']);?>" data-alt="<?php echo esc_html($model_array['ar_alternative_id']);?>" class="ar-button ar-button-default <?php echo esc_html($model_array['ar_hide_arview']);?>" id="ar-button_<?php echo esc_html($model_array['model_id']);?>"><img id="ar-img_<?php echo esc_html($model_array['model_id']);?>" src="<?php echo esc_url( plugins_url( "assets/images/ar-view-btn.png", dirname(__FILE__) ) );?>" class="ar-button-img"></button>

                <button slot="ar-button" data-id="<?php echo esc_html($model_array['model_id']);?>" data-alt="<?php echo esc_html($model_array['ar_alternative_id']);?>" class="ar-button ar-button-default <?php echo esc_html($model_array['ar_hide_arview']);?>" id="ar-button_<?php echo esc_html($model_array['model_id']);?>"><img id="ar-img_<?php echo esc_html($model_array['model_id']);?>" src="<?php echo esc_url( plugins_url( "assets/images/ar-view-btn.png", dirname(__FILE__) ) );?>" class="ar-button-img"></button>
            <?php

               

            } else {
            ?>
                <button slot="ar-button" data-id="<?php echo esc_html($model_array['model_id']);?>" class="ar-button ar-button-default <?php echo esc_html($model_array['ar_hide_arview']);?>" id="ar-button_<?php echo esc_html($model_array['model_id']);?>"><img id="ar-img_<?php echo esc_html($model_array['model_id']);?>" src="<?php echo esc_url( plugins_url( "assets/images/ar-view-btn.png", dirname(__FILE__) ) );?>" class="ar-button-img"></button>
            <?php
            }
        }
    }else{
        if($model_array['ar_alternative_id'] && ( ($isMob) OR ($isTab) OR ($isIPhone) OR ($isIPad) OR ($isAndroid) )){
            ?>
            <button data-id="<?php echo esc_html($model_array['model_id']);?>" data-alt="<?php echo esc_html($model_array['ar_alternative_id']);?>" class="ar-button <?php echo esc_html($model_array['ar_hide_arview']);?>" id="ar-button_<?php echo esc_html($model_array['model_id']);?><?php echo esc_html($model_array['model_id']);?>"><img id="ar-img_<?php echo esc_html($model_array['model_id']);?>" src="<?php echo esc_url($model_array['ar_view_file']);?>" class="ar-button-img"></button>

            <button slot="ar-button" style="display:none;" data-id="<?php echo esc_html($model_array['model_id']);?>" class="ar-button <?php echo esc_html($model_array['ar_hide_arview']);?>" id="ar-button_<?php echo esc_html($model_array['model_id']);?>"><img id="ar-img_<?php echo esc_html($model_array['model_id']);?>" src="<?php echo esc_url($model_array['ar_view_file']);?>" class="ar-button-img"></button>
        <?php
        } else {
        ?>
            <button slot="ar-button" data-id="<?php echo esc_html($model_array['model_id']);?>" class="ar-button <?php echo esc_html($model_array['ar_hide_arview']);?>" id="ar-button_<?php echo esc_html($model_array['model_id']);?>"><img id="ar-img_<?php echo esc_html($model_array['model_id']);?>" src="<?php echo esc_url($model_array['ar_view_file']);?>" class="ar-button-img"></button>
        <?php
        }
    }    

    
    if (!isset($model_array['ar_hide_fullscreen'])){
        $model_array['ar_hide_fullscreen'] ='';
    }
    //CTA Button
    if (($model_array['ar_cta']!='')AND($model_array['ar_cta_url']!='')){
    ?>
        <div class="ar-cta-button-container">
            <center><a href="<?php echo esc_html($model_array['ar_cta_url']);?>"><button slot="ar-cta-button" class="ar_cta_button button" id="ar-cta-button"><?php echo esc_html($model_array['ar_cta']);?></button></a></center>
        </div>
    <?php
    }
    //Hotspots
    if ($model_array['ar_hotspots']!=''){
        foreach ($model_array['ar_hotspots']['annotation'] as $k => $v){
            if ($model_array['ar_hotspots']['link'][$k] !=''){
                $v = '<a href="'.$model_array['ar_hotspots']['link'][$k].'" target="_blank">'.$v.'</a>';
            }
            $output_html.='<button slot="hotspot-'.($k-1).'" class="hotspot" id="hotspot-'.$k.'" data-position="'.$model_array['ar_hotspots']['data-position'][$k].'" data-normal="'.$model_array['ar_hotspots']['data-normal'][$k].'"><div class="annotation">'.$v.'</div></button>';
        }
    }
    if ($model_array['ar_hide_qrcode']==''){
        $ar_qr_display = 'block';
    }else{
        $ar_qr_display = 'none';
    }
    
    if (isset($model_array['ar_gallery_selector'])) {
        $output_html = ''; // Initialize the output HTML string
        $output_html .= '
            <div class="ar_gallery_selector">
                <select id="scale">';
    
        $model_types = explode(',', $model_array['ar_gallery_selector']);
    
        // Iterate through the array and display the values
        for ($i = 0; $i < count($model_types); $i += 2) {
            // Even keys are the value, odd keys are the option's title
            $value = esc_attr($model_types[$i]);         // Even index - escape for attributes
            $title = esc_html($model_types[$i + 1]);     // Odd index - escape for HTML content
    
            // Append the option tag with the correct value and title
            $output_html .= '<option value="' . $value . '">' . $title . '</option>';
        }
    
        $output_html .= '</select>
            </div>';
    }
    
    echo wp_kses($output_html, ar_allowed_html());

    ?>
    <?php
    if (isset($ar_qr_image_data)){
    ?>
        <div class="ar-qrcode-btn-container hide_on_devices">
        <button id="ar-qrcode_<?php echo esc_html($model_array['model_id']);?>" type="button" class="ar-qrcode hide_on_devices" onclick="this.classList.toggle('ar-qrcode-large');" style="display: <?php echo esc_html($ar_qr_display);?>; background-image: url(<?php echo esc_attr($ar_qr_image_data);?>);"></button>
        </div>
    <?php
    } else {
    ?>
        <div class="ar-qrcode-btn-container hide_on_devices"><div>
    
    <?php
    }

    echo wp_kses($output, ar_allowed_html());
   

    ?>

        </model-viewer>
    <?php
   
    if(!$model_array['ar_pop']) { ?>

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
    <?php } ?>

    </div>