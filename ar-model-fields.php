<?php
/**
 * AR Display
 * AR For WordPress
 * https://augmentedrealityplugins.com
**/
if (!defined('ABSPATH'))
    exit;

add_action('admin_enqueue_scripts', 'ar_advance_register_script');
add_action('admin_enqueue_scripts', 'ar_advance_register_style');

/*function ar_enqueue_scripts() {
    wp_enqueue_script('ar-for-wordpress', plugin_dir_url(__FILE__) . 'assets/js/ar-color-picker.js', array('jquery', 'spectrum-color-picker'), '1.0', true);
    wp_enqueue_script('ar-for-wordpress', 'https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.js', array('jquery', 'light-color'), '1.0', true);
}*/

//add_action('wp_enqueue_scripts', 'ar_enqueue_scripts');
if (!function_exists('ar_wp_advance_update_edit_form')){
    add_action('post_edit_form_tag', 'ar_wp_advance_update_edit_form');
    function ar_wp_advance_update_edit_form() {
        echo ' enctype="multipart/form-data"';
    }
}

if (!function_exists('ar_wp_advance_the_upload_metabox')){
    add_action('add_meta_boxes', 'ar_wp_advance_the_upload_metabox');
    function ar_wp_advance_the_upload_metabox() {
        // Define the custom attachment for posts  
        add_meta_box('ar_wp_advance_custom_attachment', esc_html(__( 'Augmented Reality Models', 'ar-for-wordpress' )), 'ar_wp_model_fields', "armodels", "normal", "high", null);
    }
}

// Add the View link to the All AR Models page
if (!function_exists('ar_modify_list_row_actions')){
    add_filter( 'post_row_actions', 'ar_modify_list_row_actions', 10, 2 );
    function ar_modify_list_row_actions( $actions, $post ) {
    	if ( $post->post_type == "armodels" ) {
    		$actions['View'] = sprintf( '<a href="%1$s">%2$s</a>',
    			esc_url( get_permalink($post) ),
    			esc_html( __( 'View', 'ar-for-wordpress' ) ) );
    	}
    	return $actions;
    }
}

function ar_change_featured_image_text( $content ) {
    if ( 'armodels' === get_post_type() ) {
        $content = str_replace( 'Set featured image', __( 'Set AR Poster image', 'ar-for-wordpress' ), $content );
        $content = str_replace( 'Remove featured image', __( 'Remove AR Poster image', 'ar-for-wordpress' ), $content );
    }
    return $content;
}
add_filter( 'admin_post_thumbnail_html', 'ar_change_featured_image_text' );

//Add the AR Model Fields editor to front end
add_shortcode('areditor', 'ar_wp_model_fields_public');

if (!function_exists('ar_wp_model_fields_public')){
    function ar_wp_model_fields_public($arr = NULL) {

        ob_start();
        ar_wp_model_fields($arr);
        $output = ob_get_clean();

        return $output;
    }
}
// Model File Fields
if (!function_exists('ar_wp_model_fields')){
    function ar_wp_model_fields($arr = NULL) {
        global $wpdb, $post, $shortcode_examples, $ar_whitelabel, $ar_css_styles, $ar_css_names;
        $public = '';
        //Check if on admin edit page or public page
        if (is_admin()){
            $screen = get_current_screen();
        }
        if (!isset($screen)){
            //Showing Editor on Public Side
            $post   = get_post( $arr['id'] );
            $public = 'y';
        }
        
        $plan_check = get_option('ar_licence_plan');
        //Model Count
        $model_count = ar_model_count();
        //Hide the post content area
        ?>
        <style>
            .postarea{display:none;}
            
        </style>

        <?php wp_nonce_field( 'ar-for-wordpress', 'arwp-editpost-nonce' ); ?>

        <?php if ($public != 'y'){ ?>
        <div id="ardisplay_panel" class="panel woocommerce_options_panel">
            <div class="options_group">
        <?php } ?>
                <?php //Hide instructions and file uploads if showing on public side
                if ($public == 'y'){
                    echo '<div style="display:none">';
                }
                ?>
                    <div id="ar_shortcode_instructions">
                        <div style="width:100%;height:80px;background-color:#12383d">
                            <div class="ar_admin_view_title">
                                <img src="<?php echo esc_url( plugins_url( "assets/images/ar-for-wordpress-box.jpg", __FILE__ ) );?>" style="padding: 10px 30px 10px 10px; height:60px" align="left">
                                <h1 style="color:#ffffff; padding-top:20px;font-size:20px"><?php esc_html_e('AR for WordPress','ar-for-wordpress'); ?></h1>
                            </div>
                            <?php
                        if ((substr(get_option('ar_licence_valid'),0,5)!='Valid')AND($model_count>=2)){?>
                        
                        </div>
                            <p><b><a href="edit.php?post_type=armodels&page"><?php esc_html_e( 'Please check your subscription & license key.</a> If you are using the free version of the plugin then you have exceeded the limit of allowed models.', 'ar-for-wordpress' );?></a></b></p>
                    </div>
            </div>
        </div>
                        <?php
                        }else{
                            $model_array=array();
                            $model_array['id'] = $post->ID;
                        ?>
                        <div  class="ar_admin_view_post">
                    		<?php if (get_post_meta( $model_array['id'], '_glb_file', true )!=''){
                               // echo '<div class="ar_admin_view_post">'.sprintf( __('<a href="%s" target="_blank"><button type="button" class="button ar_admin_button" style="margin-right:20px">'.__('View Model Post','ar-for-wordpress').'</button></a>'), esc_url( get_permalink($model_array['id']) ) ).'</div>';
                                echo ''.sprintf( ('<a href="%s" target="_blank"><button type="button" class="button ar_admin_button" style="margin-right:20px">'.esc_html(__('View Model Post','ar-for-wordpress')).'</button></a>'), esc_url( get_permalink($model_array['id']) ) );
                            }
                            ?>
                    	</div>
                        <div  class="ar_admin_view_shortcode" onclick="copyToClipboard('ar_shortcode');document.getElementById('copied').innerHTML='-&nbsp;Copied!';" style="cursor: pointer;">
                    	    <span class="dashicons dashicons-admin-page" style="color:#fff;float:left;padding-left:20px;"></span><div style="float: left;"><b>Shortcode</b> <span id="copied" class="ar_label_tip"></span></div>
                    	        <a heref="#" style="float:left;">
                    	        <input id="ar_shortcode" type="text" class="button ar_admin_button" value="[ar-display id=<?php echo esc_html($model_array['id']);?>]" readonly style="width:164px;background: none !important; border: none !important;color:#f37a23 !important;font-size: 16px;float:left;">
                    	        </a>
                    	        
                    	   </div>
                    
                		
                    </div>
                        	
            	</div>
                <div style="clear:both"></div>
                
                <!-- AR MODEL FIELDS TABS -->
                <?php include plugin_dir_path( __FILE__ ) . 'templates/model-fields-tabs.php'; ?>


                <div style="clear:both"></div>
                <div class="ar_admin_viewer" id="ar_admin_options">
                    <input type="hidden" name="ar_open_tabs" id="ar_open_tabs" value="<?php echo esc_html($ar_open_tabs);?>">
                	<button class="ar_accordian" id="ar_display_options_acc" type="button"><?php esc_html_e('Display Options', 'ar-for-wordpress' ); echo wp_kses($premium_only, ar_allowed_html());?></button>
                    <div id="ar_display_options_panel" class="ar_accordian_panel">
                        <br>
                        <?php if ($public != 'y'){ ?>
                            <div style="clear:both"></div>
                            <div class="ar_admin_label" style="width:100%"><label for="_skybox_file"><?php esc_html_e( 'Skybox/Background Image', 'ar-for-wordpress' ); echo '<span class="ar_label_tip"> - ';esc_html_e('HDR, JPG or PNG', 'ar-for-wordpress' );?></span></label> </div>
                        	<div class="ar_admin_field"><input type="url" pattern="https?://.+" title="<?php esc_html_e('Secure URLs only','ar-for-wordpress');?> https://" placeholder="https://" name="_skybox_file" id="_skybox_file" class="regular-text" value="<?php echo esc_html(get_post_meta( $model_array['id'], '_skybox_file', true ));?>" <?php echo esc_html($disabled);?>> <input id="upload_skybox_button" class="button upload_skybox_button" type="button" value="<?php esc_html_e( 'Upload', 'ar-for-wordpress' );?>"  <?php echo esc_html($disabled);?>/> <a href="#" onclick="document.getElementById('_skybox_file').value = ''"><img src="<?php echo esc_url( plugins_url( "assets/images/delete.png", __FILE__ ) );?>" style="width: 15px;vertical-align: middle;"></a></div>
                            <div style="clear:both"></div>
                            <div class="ar_admin_label" style="width:100%"><label for="_ar_environment"><?php esc_html_e( 'Environment Image', 'ar-for-wordpress' ); echo '<span class="ar_label_tip"> - ';esc_html_e('HDR, JPG or PNG', 'ar-for-wordpress' );?></span></label></div>
                            <div class="ar_admin_field"><input type="url" pattern="https?://.+" title="<?php esc_html_e('Secure URLs only','ar-for-wordpress'); ?> https://" placeholder="https://" name="_ar_environment" id="_ar_environment" class="regular-text" value="<?php echo esc_html(get_post_meta( $model_array['id'], '_ar_environment', true ));?>" <?php echo esc_html($disabled);?>> <input id="upload_environment_button" class="button upload_environment_button" type="button" value="<?php esc_html_e( 'Upload', 'ar-for-wordpress' );?>" <?php echo esc_html($disabled);?>/> <a href="#" onclick="document.getElementById('_ar_environment').value = ''"><img src="<?php echo esc_url( plugins_url( "assets/images/delete.png", __FILE__ ) );?>" style="width: 15px;vertical-align: middle;"></a></div>
                	        <div style="clear:both"></div>
                            <div class="ar_admin_label"><label for="_ar_placement"><?php esc_html_e( 'Model Placement', 'ar-for-wordpress' );?></label></div>
                        	<div class="ar_admin_field"><select name="_ar_placement" id="_ar_placement" class="ar-input ar-input-wide" <?php echo esc_html($disabled);?>>
                        			<option value="floor" <?php selected( get_post_meta( $model_array['id'], '_ar_placement', true ), 'floor' ); ?>><?php esc_html_e( 'Floor - Horizontal', 'ar-for-wordpress' );?></option>
                        			<option value="wall" <?php selected( get_post_meta( $model_array['id'], '_ar_placement', true ), 'wall' ); ?>><?php esc_html_e( 'Wall - Vertical', 'ar-for-wordpress' );?></option>
                        	</select></div>
                    	<?php } ?>
                    	<div style="clear:both"></div>
                    	<div class="ar_admin_label"><?php esc_html_e( 'Scale', 'ar-for-wordpress' );?><br><span class="ar_label_tip"><?php esc_html_e( '1 = 100%, only affects desktop view, not available in AR', 'ar-for-wordpress' );?></span></div>
                    	<?php
                    	$ar_x = 1;
                    	$ar_y = 1;
                    	$ar_z = 1;
                    	if (get_post_meta( $model_array['id'], '_ar_x', true )){
                    	    $ar_x = get_post_meta( $model_array['id'], '_ar_x', true );
                    	}
                    	if (get_post_meta( $model_array['id'], '_ar_y', true )){
                    	    $ar_y = get_post_meta( $model_array['id'], '_ar_y', true );
                    	}
                    	if (get_post_meta( $model_array['id'], '_ar_z', true )){
                    	    $ar_z = get_post_meta( $model_array['id'], '_ar_z', true );
                    	}
                    	?>
                        <div class="ar_admin_field"><span style="float:left">X: <input id="_ar_x" name="_ar_x" type="number" style="width: 60px;" class="ar-input" value="<?php echo esc_html($ar_x);?>" size="3" step="0.1" min="0.1" <?php echo esc_html($disabled);?>></span>
                            <span style="float:left">  Y: <input id="_ar_y" name="_ar_y" type="number" style="width: 60px;" class="ar-input" value="<?php echo esc_html($ar_y);?>" size="3" step="0.1" min="0.1" <?php echo esc_html($disabled);?>> </span>
                            <span style="float:left">  Z: <input id="_ar_z" name="_ar_z" type="number" style="width: 60px;" class="ar-input" value="<?php echo esc_html($ar_z);?>" size="3" step="0.1" min="0.1" <?php echo esc_html($disabled);?>></span>
                        </div>
                        <div style="clear:both"></div>
                        
                        <div class="ar_admin_label"><label for="_ar_field_of_view"><?php esc_html_e( 'Field of View', 'ar-for-wordpress' );?></label></div>
                        <?php 
                        $ar_field_of_view = get_post_meta( $model_array['id'], '_ar_field_of_view', true );
                        $ar_zoom_out = get_post_meta( $model_array['id'], '_ar_zoom_out', true );
                    	$ar_zoom_in = get_post_meta( $model_array['id'], '_ar_zoom_in', true );?>
                    	<div class="ar_admin_field"><select name="_ar_field_of_view" id="_ar_field_of_view" class="ar-input ar-input-wide" <?php echo esc_html($disabled);?>>
                            <option value=""><?php esc_html_e('Default','ar-for-wordpress');?></option>
                            <?php 
                            for ($x = 10; $x <= 180; $x+=10) {
                              echo '<option value="'.esc_html($x).'"';
                              if ($x==$ar_field_of_view){echo ' selected';}
                              echo '>'.esc_html($x).' ';
                              esc_html_e( 'Degrees', 'ar-for-wordpress');
                              echo '</option>';
                            }
                            ?>
                            </select>
                        </div>
                        
                        <div style="clear:both"></div>
                        <?php $ar_exposure = get_post_meta( $model_array['id'], '_ar_exposure', true );
                        if ((!$ar_exposure)AND($ar_exposure!='0')){ $ar_exposure = 1; } ?>
                        <div class="ar_admin_label"><label for="_ar_exposure"><?php esc_html_e( 'Exposure', 'ar-for-wordpress' );?></label></div>
                    	<div class="ar_admin_field"><input id="_ar_exposure" name="_ar_exposure" type="range" class="ar-slider" min="0" max="2" step=".1" value="<?php echo esc_html($ar_exposure); ?>" <?php echo esc_html($disabled);?> oninput="this.nextElementSibling.value = this.value">&nbsp;<output><?php echo esc_html($ar_exposure); ?></output></div>
                    	<div style="clear:both"></div>
                        <?php $ar_shadow_intensity = get_post_meta( $model_array['id'], '_ar_shadow_intensity', true );
                        if ((!$ar_shadow_intensity)AND($ar_shadow_intensity!='0')){ $ar_shadow_intensity = 1; } ?>
                        <div class="ar_admin_label"><label for="_ar_shadow_intensity"><?php esc_html_e( 'Shadow Intensity', 'ar-for-wordpress' );?></label></div>
                    	<div class="ar_admin_field"><input id="_ar_shadow_intensity" name="_ar_shadow_intensity" type="range" class="ar-slider" min="0" max="2" step=".1" value="<?php echo esc_html($ar_shadow_intensity); ?>" <?php echo esc_html($disabled);?> oninput="this.nextElementSibling.value = this.value"> <output><?php echo esc_html($ar_shadow_intensity); ?></output></div>
                        <div style="clear:both"></div>
                        <?php $ar_shadow_softness = get_post_meta( $model_array['id'], '_ar_shadow_softness', true );
                        if ((!$ar_shadow_softness)AND($ar_shadow_softness!='0')){ $ar_shadow_softness = 1; } ?>
                        <div class="ar_admin_label"><label for="_ar_shadow_softness"><?php esc_html_e( 'Shadow Softness', 'ar-for-wordpress' );?></label></div>
                    	<div class="ar_admin_field"><input id="_ar_shadow_softness" name="_ar_shadow_softness" type="range" class="ar-slider" min="0" max="1" step=".1" value="<?php echo esc_html($ar_shadow_softness); ?>" <?php echo esc_html($disabled);?> oninput="this.nextElementSibling.value = this.value"> <output><?php echo esc_html($ar_shadow_softness); ?></output></div>
                        <div style="clear:both"></div>
                        <div class="ar_admin_label"><label for="_ar_zoom_in"><?php esc_html_e( 'Zoom Restraints', 'ar-for-wordpress' );?></label></div>
                        <span style="float:left">
                        <?php esc_html_e('In', 'ar-for-wordpress');?><select name="_ar_zoom_in" id="_ar_zoom_in" class="ar-input" style="min-width:100px;margin: 0px 10px;" <?php echo esc_html($disabled);?>>
                          <option value="default" <?php if (($ar_zoom_in == 'default')OR($ar_zoom_in == '')){echo 'selected';}?>><?php esc_html_e('Default', 'ar-for-wordpress');?></option>
                          <?php 
                          for ($x = 100; $x >= 0; $x-=10) {
                              echo '<option value="'.esc_html($x).'"';
                              if (($ar_zoom_in != 'default')AND($ar_zoom_in==$x)){echo ' selected';}
                              echo '>'.esc_html($x).'%</option>';
                          }
                          ?>
                        </select></span>
                        <span style="float:left">
                        <?php esc_html_e('Out', 'ar-for-wordpress');?> <select name="_ar_zoom_out" id="_ar_zoom_out" class="ar-input" style="min-width:100px;margin: 0px 10px;" <?php echo esc_html($disabled);?>>
                          <option value="default" <?php if (($ar_zoom_out == 'default')OR($ar_zoom_out == '')){echo 'selected';}?>><?php esc_html_e('Default', 'ar-for-wordpress');?></option>
                          <?php 
                          for ($x = 0; $x <= 100; $x+=10) {
                              echo '<option value="'.esc_html($x).'"';
                              if (($ar_zoom_out != 'default')AND($ar_zoom_out==$x)){echo ' selected';}
                              echo '>'.esc_html($x).'%</option>';
                          }
                          ?>
                        </select></span>
                        <div style="clear:both"></div>
                        <br>
                        <?php $ar_light_color = get_post_meta( $model_array['id'], '_ar_light_color', true );?>
                        <div class="ar_admin_label"><label for="_ar_light_color"><?php esc_html_e( 'Light Color', 'ar-for-wordpress' );?></label></div>
                    	<div class="ar_admin_field"><input id="_ar_light_color" name="_ar_light_color" type="text" value="<?php echo esc_html($ar_light_color); ?>" <?php echo esc_html($disabled);?>></div>
                        <div style="clear:both"></div>
                        
                        <br>
                        <?php 
                        
                        //Checkbox Field Array
                        $hide_rotate_limit = '';
                        $field_array = array('_ar_animation' => 'Animation - Play/Pause button', '_ar_autoplay' => 'Animation - Auto Play', '_ar_environment_image' => 'Legacy lighting', '_ar_emissive' => 'Emissive lighting', '_ar_variants' => 'Model includes variants', '_ar_rotate_limit' => 'Set Limits');
                        foreach ($field_array as $field => $title){
                            if ($field=='_ar_rotate_limit'){
                                if (get_post_meta( $model_array['id'], $field, true )=='1'){
                                    $hide_rotate_limit = 'border-color:#49848f';
                                }
                                ?>
                            	<div style="clear:both"></div>
                            </div> <!-- end of Accordian Panel -->
                            <button class="ar_accordian" id="ar_rotation_acc" type="button"><?php esc_html_e('Rotation Limits', 'ar-for-wordpress' ); echo wp_kses($premium_only, ar_allowed_html());?></button>
                            <div id="ar_rotation_panel" class="ar_accordian_panel"><br>
                            <?php
                            }
                            
                            ?>
                        
                            <div style="float:left">
                                <div class="ar_admin_label"><label for="<?php echo esc_html($field);?>"><?php echo esc_html( $title);?> </label> </div> 
                    	        <div class="ar_admin_field" style="padding-right:20px"><input type="checkbox" name="<?php echo esc_html($field)?>" id="<?php echo esc_html($field);?>" class="ar-ui-toggle" value="1" <?php if (get_post_meta( $model_array['id'], $field, true )=='1'){echo 'checked';} echo esc_html($disabled);?>></div>
                            </div>
                        <?php 
                            if ($field=='_ar_autoplay'){
                                //check in animations in the file and list
                                ?><div style="clear:both"></div>
                                <div id="animationDiv" style="display:none">
                                    <div class="ar_admin_label"><label for="_ar_animation_selection"><?php esc_html_e( 'Animation Selection', 'ar-for-wordpress' );?></label></div>
                        	        <div class="ar_admin_field"><select name="_ar_animation_selection" id="_ar_animation_selection" class="ar-input ar-input-wide" <?php echo esc_html($disabled);?>></select></div>
                    	        </div><div style="clear:both"></div>
       <?php
                            }
                            
                            
                        } 
                        //if ar_rotate_limit is true show limit options
                        $ar_compass_top_value = '';
                        $ar_compass_top_selected = '';
                        if (get_post_meta( $model_array['id'], '_ar_compass_top_value', true )){
                    	    $ar_compass_top_value = get_post_meta( $model_array['id'], '_ar_compass_top_value', true );
                    	    $ar_compass_top_selected = 'style="background-color:#f37a23 !important"';
                    	}
                    	$ar_compass_bottom_value = '';
                        $ar_compass_bottom_selected = '';
                        if (get_post_meta( $model_array['id'], '_ar_compass_bottom_value', true )){
                    	    $ar_compass_bottom_value = get_post_meta( $model_array['id'], '_ar_compass_bottom_value', true );
                    	    $ar_compass_bottom_selected = 'style="background-color:#f37a23 !important"';
                    	}
                    	$ar_compass_left_value = '';
                        $ar_compass_left_selected = '';
                        if (get_post_meta( $model_array['id'], '_ar_compass_left_value', true )){
                    	    $ar_compass_left_value = get_post_meta( $model_array['id'], '_ar_compass_left_value', true );
                    	    $ar_compass_left_selected = 'style="background-color:#f37a23 !important"';
                    	}
                    	$ar_compass_right_value = '';
                        $ar_compass_right_selected = '';
                        if (get_post_meta( $model_array['id'], '_ar_compass_right_value', true )){
                    	    $ar_compass_right_value = get_post_meta( $model_array['id'], '_ar_compass_right_value', true );
                    	    $ar_compass_right_selected = 'style="background-color:#f37a23 !important"';
                    	}
                    	
                        ?>
                        <br>
                        <div id="ar_rotation_limits" class="ar_rotation_limits_containter" style="<?php echo esc_html($hide_rotate_limit);?>">
                            <center>
                                <input id="camera_view_button" class="button" type="button" style="margin-top: 10px; margin-left: -200px;" value="<?php esc_html_e( 'Set Current Camera View as Initial First', 'ar-for-wordpress' );?>" <?php echo esc_html($disabled);?> />
                                <br clear="all">
                                <p><?php esc_html_e( 'Then rotate your model to each of your desired limits and click the arrows to apply.', 'ar-for-wordpress' ); ?></p>
                                <div class="ar-compass-container">
                                    <img src="<?php echo esc_url( plugins_url( "assets/images/rotate_up_arrow.png", __FILE__ ) );?>" alt="Compass" id="ar-compass-image" class="ar-compass-image">
                                    <button id = "ar-compass-top" class="ar-compass-button ar-compass-top" <?php echo esc_html($ar_compass_top_selected); ?> data-rotate="0" type="button">&UpArrowBar;</button>
                                    <button id = "ar-compass-bottom" class="ar-compass-button ar-compass-bottom" <?php echo esc_html($ar_compass_bottom_selected); ?> data-rotate="180" type="button">&DownArrowBar;</button>
                                    <button id = "ar-compass-left" class="ar-compass-button ar-compass-left" <?php echo esc_html($ar_compass_left_selected); ?> data-rotate="270" type="button">&LeftArrowBar;</button>
                                    <button id = "ar-compass-right" class="ar-compass-button ar-compass-right" <?php echo esc_html($ar_compass_right_selected); ?> data-rotate="90" type="button">&RightArrowBar;</button>
                                </div>
                            </center>
                            <input id="_ar_compass_top_value" name="_ar_compass_top_value" type="hidden" value="<?php echo esc_html($ar_compass_top_value);?>" <?php echo esc_html($disabled);?>> 
                            <input id="_ar_compass_bottom_value" name="_ar_compass_bottom_value" type="hidden" value="<?php echo esc_html($ar_compass_bottom_value);?>" <?php echo esc_html($disabled);?>> 
                            <input id="_ar_compass_left_value" name="_ar_compass_left_value" type="hidden" value="<?php echo esc_html($ar_compass_left_value);?>" <?php echo esc_html($disabled);?>> 
                            <input id="_ar_compass_right_value" name="_ar_compass_right_value" type="hidden" value="<?php echo esc_html($ar_compass_right_value);?>" <?php echo esc_html($disabled);?>> 
                        </div>
                    </div> <!-- end of Accordian Panel -->
                    <button class="ar_accordian" id="ar_disable_elements_acc" type="button"><?php esc_html_e('Disable/Hide Elements', 'ar-for-wordpress' ); if ($disabled!=''){echo ' - '.esc_html(__('Premium Plans Only', 'ar-for-wordpress'));}?></button>
                    <div id="ar_disable_elements_panel" class="ar_accordian_panel">
                        <br>
                    	<?php 
                        //Checkbox Field Array
                        $field_array = array('_ar_view_hide' => 'AR View Button', '_ar_rotate' => 'Auto Rotate', '_ar_hide_dimensions' =>'Dimensions', '_ar_prompt' => 'Interaction Prompt', '_ar_resizing' => 'Resizing in AR', '_ar_qr_hide' => 'QR Code', '_ar_hide_reset' =>'Reset Button', '_ar_disable_zoom' => 'Zoom');
                        foreach ($field_array as $field => $title){
                        ?>
                            <div style="float:left">
                            <div class="ar_admin_label"><label for="<?php echo esc_html($field);?>"><?php echo esc_html($title);?> </label> </div>
                    	    <div class="ar_admin_field" style="padding-right:20px"><input type="checkbox" name="<?php echo esc_html($field);?>" id="<?php echo esc_html($field);?>" class="ar-ui-toggle" value="1" <?php if (get_post_meta( $model_array['id'], $field, true )=='1'){echo 'checked';} echo esc_html($disabled);?>></div>
                            </div>
                        <?php 
                        
                        } 
                    	?>
                    	<div style="clear:both"></div>
                    </div> <!-- end of Accordian Panel -->
                    
                        <?php 
                    $hotspot_count = 0;
                    if ($public != 'y'){ ?>
                        <button class="ar_accordian" id="ar_qr_code_acc" type="button"><?php esc_html_e('QR Code Options', 'ar-for-wordpress' ); echo wp_kses($premium_only, ar_allowed_html());?></button>
                        <div id="ar_qr_code_panel" class="ar_accordian_panel">
                        <br>
                        	<?php $ar_qr_destination = get_post_meta( $model_array['id'], '_ar_qr_destination_mv', true );?>
                        	<div class="ar_admin_label"><label for="_ar_qr_image"><?php esc_html_e('QR Code Destination', 'ar-for-wordpress' );?></div>
                            <div class="ar_admin_field"><select id="_ar_qr_destination_mv" name="_ar_qr_destination_mv" class="ar-input ar-input-wide" <?php echo  esc_html($disabled);?>>
                              <option value=""><?php esc_html_e('Use Global Setting', 'ar-for-wordpress' );?></option>
                              <option value="parent-page" <?php
                                if ($ar_qr_destination=='parent-page'){
                                    echo 'selected';
                                }
                              ?>><?php esc_html_e('Parent Page', 'ar-for-wordpress' );?></option>
                              <option value="model-viewer" <?php
                                if ($ar_qr_destination=='model-viewer'){
                                    echo 'selected';
                                }
                              ?>
                              ><?php esc_html_e('AR View', 'ar-for-wordpress' );?></option>
                              </select>
                            </div>
                            
                            <div style="clear:both"></div>
                        	<div class="ar_admin_label" style="width:100%"><label for="_ar_qr_image"><?php esc_html_e( 'Custom QR Code Image', 'ar-for-wordpress' ); echo "<span class=\"ar_label_tip\">"; esc_html_e(' - JPG file 250 x 250px', 'ar-for-wordpress');?> - <?php esc_html_e('Requires Imagick PHP Extension', 'ar-for-wordpress');?></span></label></div>
                            <div class="ar_admin_field"><input type="url" pattern="https?://.+" title="<?php esc_html_e('Secure URLs only','ar-for-wordpress'); ?> https://" placeholder="https://" name="_ar_qr_image" id="_ar_qr_image" class="regular-text" value="<?php echo esc_html(get_post_meta( $model_array['id'], '_ar_qr_image', true ));?>" <?php echo esc_html($disabled);?>> <input id="upload_qr_image_button" class="upload_qr_image_button button" type="button" value="<?php esc_html_e( 'Upload', 'ar-for-wordpress' );?>" <?php echo esc_html($disabled);?>/> <a href="#" onclick="document.getElementById('_ar_qr_image').value = ''"><img src="<?php echo esc_url( plugins_url( "assets/images/delete.png", __FILE__ ) );?>" style="width: 15px;vertical-align: middle;"></a></div>
                        	
                            <div style="clear:both"></div>
                        	<div class="ar_admin_label"><label for="_ar_qr_destination"><?php esc_html_e( 'Custom QR Code URL', 'ar-for-wordpress' ); echo "<br>"; echo '<span class="ar_label_tip"></span>';?></label></div>
                            <div class="ar_admin_field"><input type="url" pattern="https?://.+" title="<?php esc_html_e('Secure URLs only','ar-for-wordpress'); ?> https://" placeholder="https://" name="_ar_qr_destination" id="_ar_qr_destination" class="regular-text" value="<?php echo esc_html(get_post_meta( $model_array['id'], '_ar_qr_destination', true ));?>" <?php echo esc_html($disabled);?>>  <a href="#" onclick="document.getElementById('_ar_qr_destination').value = ''"><img src="<?php echo esc_url( plugins_url( "assets/images/delete.png", __FILE__ ) );?>" style="width: 15px;vertical-align: middle;"></a></div>
                        	
                            <div style="clear:both"></div>
                        </div> <!-- end of Accordian Panel -->
                        <button class="ar_accordian" id="ar_additional_interactions_acc" type="button"><?php esc_html_e('Additional Interactions', 'ar-for-wordpress' ); echo wp_kses($premium_only, ar_allowed_html());?></button>
                        <div id="ar_additional_interactions_panel" class="ar_accordian_panel">
                        <br>
                            <div class="ar_admin_label"><label for="_ar_cta"><?php esc_html_e( 'Call To Action Button', 'ar-for-wordpress' ); ?></label><br><span class="ar_label_tip"><?php esc_html_e( 'Button Displays in 3D Model view and in AR view on Android only', 'ar-for-wordpress' );?></span></div>
                            <div class="ar_admin_field"><input type="text" name="_ar_cta" id="_ar_cta" class="regular-text" value="<?php echo esc_html(get_post_meta( $model_array['id'], '_ar_cta', true ));?>" <?php echo esc_html($disabled);?> placeholder="Click For More"> </div>
                            <div style="clear:both"></div>
                            <div class="ar_admin_label"><label for="_ar_cta_url"><?php esc_html_e( 'Call To Action URL', 'ar-for-wordpress' ); ?></label></div>
                            <div class="ar_admin_field"><input type="url" pattern="https?://.+" name="_ar_cta_url" id="_ar_cta_url" class="regular-text" value="<?php echo esc_html(get_post_meta( $model_array['id'], '_ar_cta_url', true ));?>" <?php echo esc_html($disabled);?> placeholder="https://"> </div>
                        	<div style="clear:both"></div><hr>
                            <div class="ar_admin_label"><label for="_ar_hotspot_text"><?php esc_html_e( 'Hotspots', 'ar-for-wordpress' );?></label><br><span class="ar_label_tip"><?php esc_html_e( 'Add your text which can include html and an optional link, click the Add Hotspot button, then click on your model where you would like it placed', 'ar-for-wordpress' );?></span></div>
                        	<div class="ar_admin_field"><input type="text" name="_ar_hotspot_text" id="_ar_hotspot_text" class="ar-input" style="width: 140px;" placeholder="<?php esc_html_e( 'Hotspot Text', 'ar-for-wordpress' );?>" <?php echo esc_html($disabled);?>> <input type="text" name="_ar_hotspot_link" id="_ar_hotspot_link" class="ar-input" style="width: 140px;" placeholder="<?php esc_html_e( 'Hotspot Link', 'ar-for-wordpress' );?>" <?php echo esc_html($disabled);?>>
                            	<input type="checkbox" name="_ar_hotspot_check" id="_ar_hotspot_check" class="regular-text" value="y" style="display:none;">
                            	<input type="button" class="button" onclick="enableHotspot()" value="<?php esc_html_e( 'Add Hotspot', 'ar-for-wordpress' );?>" <?php echo esc_html($disabled);?>>
                            </div>
                            
                        	<div style="clear:both"></div>
                        	<?php 
                        	if (get_post_meta( $model_array['id'], '_ar_hotspots', true )){
                        	    $_ar_hotspots = get_post_meta( $model_array['id'], '_ar_hotspots', true );
                        	    $hotspot_count = count($_ar_hotspots['annotation']);
                        	    $hide_remove_btn = '';
                        	    foreach ($_ar_hotspots['annotation'] as $k => $v){
                        	        if (isset($_ar_hotspots["link"][$k])){
                        	            $link = $_ar_hotspots["link"][$k];
                        	        }else{
                        	            $link ='';
                        	        }
                        	        echo '<div id="_ar_hotspot_container_'.esc_html($k).'"><div class="ar_admin_label"><label for="ar_admin_label">Hotspot '.esc_html($k).'</label></div><div class="ar_admin_field" id="_ar_hotspot_field_'.esc_html($k).'">
                        	        <input hidden="true" id="_ar_hotspots[data-normal]['.esc_html($k).']" name="_ar_hotspots[data-normal]['.esc_html($k).']" value="'.esc_html($_ar_hotspots['data-normal'][$k]).'">
                        	        <input hidden="true" id="_ar_hotspots[data-position]['.esc_html($k).']" name="_ar_hotspots[data-position]['.esc_html($k).']" value="'.esc_html($_ar_hotspots['data-position'][$k]).'">
                        	        <input type="text" class="regular-text hotspot_annotation" id="_ar_hotspots[annotation]['.esc_html($k).']" name="_ar_hotspots[annotation]['.esc_html($k).']" hotspot_name="hotspot-'.esc_html($k).'" value="'.esc_html($v).'">
                        	        <input type="text" class="regular-text hotspot_annotation" id="_ar_hotspots[link]['.esc_html($k).']" name="_ar_hotspots[link]['.esc_html($k).']" hotspot_link="hotspot-'.esc_html($k).'" value="'.esc_url($link).'" placeholder="Link">
                        	        </div></div><div style="clear:both"></div>';
                        	    
                        	    }
                        	}else{
                        	    $hide_remove_btn = 'style="display:none;"';
                        	    echo '<div id="_ar_hotspot_container_0"></div>';
                        	}
                        	?>
                        	<div class="ar_admin_label"><label for="_ar_remove_hotspot"></label></div>
                        	<div class="ar_admin_field"><input id="_ar_remove_hotspot" type="button" class="button" <?php echo esc_html($hide_remove_btn);?> onclick="removeHotspot()" value="Remove last hotspot" <?php echo esc_html($disabled);?>></div>
                        </div> <!-- end of Accordian Panel --> 	
                        <button class="ar_accordian" id="ar_alternative_acc" type="button"><?php esc_html_e('Alternative Model For Mobile', 'ar-for-wordpress' ); echo wp_kses($premium_only, ar_allowed_html()); ?></button>
                        <div id="ar_additional_interactions_panel" class="ar_accordian_panel">
                        <br>
                            <div style="clear:both"></div>
                            <div class="ar_admin_label"><label for="_ar_mobile_id"><?php esc_html_e( 'Display a different AR model when viewing on mobile devices', 'ar-for-wordpress' );?></label></div>
                            <?php 
                            $temp_post = $post;
                            //Get list of AR Models
                            $args = array(
                                'post_type'=> 'armodels',
                                'orderby'        => 'title',
                                'posts_per_page' => -1,
                                'order'    => 'ASC'
                            );           
                            $ar_id_array = array();
                            $the_query = new WP_Query( $args );
                            if($the_query->have_posts() ) { 
                                while ( $the_query->have_posts() ) { 
                                    $the_query->the_post();
                                    $mob_title = get_the_title();
                                    $mob_id = get_the_ID();
                                    if (($mob_title)){
                                        $ar_id_array[$mob_id] = $mob_title;
                                    }
                                } 
                                wp_reset_postdata(); 
                            }
                            $post = $temp_post;
                            ?>
                            
                        	<div class="ar_admin_field"><select name="_ar_mobile_id" id="_ar_mobile_id" class="ar-input ar-input-wide" <?php echo esc_html($disabled);?>>
                        	    <option value=''></option>
                        	    <?php
                        	    foreach ($ar_id_array as $mob_id => $mob_title){
                        	        if ($mob_id != $model_array['id']){
                        	            echo '<option value="'.esc_html($mob_id).'" '.selected( get_post_meta( $model_array['id'], '_ar_mobile_id', true ), $mob_id ).'>'.esc_html($mob_title).' (#'.esc_html($mob_id).')</option>';
                        	        }
                        	    }
                        	    ?>
                        	</select></div>
                            <div style="clear:both"></div>

                            <div class="ar_admin_label"><label for="_ar_alternative_id"><?php esc_html_e( 'Display a different AR model when viewing on AR mode', 'ar-for-wordpress' );?></label></div>
                            <div class="ar_admin_field"><select name="_ar_alternative_id" id="_ar_alternative_id" class="ar-input ar-input-wide" <?php echo esc_html($disabled);?>>
                                <option value=''></option>
                                <?php
                                foreach ($ar_id_array as $mob_id => $mob_title){
                                    if ($mob_id != $model_array['id']){
                                        echo '<option value="'.esc_html($mob_id).'" '.selected( get_post_meta( $model_array['id'], '_ar_alternative_id', true ), $mob_id ).'>'.esc_html($mob_title).' (#'.esc_html($mob_id).')</option>';
                                    }
                                }
                                ?>
                            </select></div>
                            <div style="clear:both"></div>
                        </div> <!-- end of Accordian Panel -->
                        <button class="ar_accordian" id="ar_element_positions_acc" type="button"><?php esc_html_e('Element Positions and CSS Styles', 'ar-for-wordpress' );echo wp_kses($premium_only, ar_allowed_html());?></button>
                        <div id="ar_additional_interactions_panel" class="ar_accordian_panel">
                        <br>
                            <div style="clear:both"></div>
                            <input type="button" class="button" style="float:right" onclick="importCSS()" value="<?php esc_html_e( 'Import Global Settings', 'ar-for-wordpress' );?>" <?php echo esc_html($disabled);?>>
                            <div class="ar_admin_label"><label for="_ar_css_override"><strong><?php esc_html_e( 'Override Global Settings', 'ar-for-wordpress' );?></strong></label></div>
                        	<div class="ar_admin_field"><input type="checkbox" name="_ar_css_override" id="_ar_css_override" class="ar-ui-toggle" value="1" <?php if (get_post_meta( $model_array['id'], '_ar_css_override', true )=='1'){echo 'checked';$hide_custom_css='';}else{$hide_custom_css='style="display:none;"';} echo esc_html($disabled);?>> </div>
                            <div style="clear:both"></div>
                            <div id="ar_custom_css_div" <?php //echo $hide_custom_css;?>>
                            <br>
                            
                                <?php //CSS Positions
                                $ar_css_positions = get_post_meta( $model_array['id'], '_ar_css_positions', true );
                                foreach ($ar_css_names as $k => $v){
                                    ?>
                                    <div style="float:left;padding-right:20px">
                                      <div style="width:160px;float:left;">
                                          <?php echo esc_html($k);?> </div>
                                      <div style="float:left;"><select id="_ar_css_positions[<?php echo esc_html($k);?>]" name="_ar_css_positions[<?php echo esc_html($k);?>]" class="ar-input" <?php echo  esc_html($disabled);?>>
                                          <option value="">Default</option>
                                          <?php 
                                          foreach ($ar_css_styles as $pos => $css){
                                            echo '<option value = "'.esc_html($pos).'"';
                                            if (isset($ar_css_positions[$k])){
                                                if ($ar_css_positions[$k]==$pos){echo ' selected';}
                                            }
                                            echo '>'.esc_html($pos).'</option>';
                                          }?>
                                          
                                          </select></div>
                                          
                                    <br  clear="all">
                                    <br>
                                    </div>
                                <?php
                                }
                                ?>
                             <br  clear="all">
                                <div >
                                  <div style="width:160px;float:left;">
                                      <?php
                                        $ar_css = get_post_meta( $model_array['id'], '_ar_css', true );
                                        $ar_css_import_global='';
                                        if (get_option('ar_css')!=''){
                                            $ar_css_import_global = get_option('ar_css');
                                        }
                                        $ar_css_import=ar_curl(esc_url( plugins_url( "assets/css/ar-display-custom.css", __FILE__ ) ));
                                  
                                	    esc_html_e('CSS Styling', 'ar-for-wordpress' );
                                        ?>
                                        </div>
                                  <div style="float:left;"><textarea id="_ar_css" name="_ar_css" style="width: 350px; height: 200px;" <?php echo esc_html($disabled);?>><?php echo esc_textarea($ar_css); ?></textarea></div>
                                </div>
                        </div> <!-- end of Accordian Panel --> 
                        <?php } ?>
                    </div>
                </div>
            
                    <?php 
                        /* Display the 3D model if it exists */
                        $ar_plugin = new AR_Plugin();
                        
                        $hide_ar_view = '';
                        if (get_post_meta($model_array['id'], '_glb_file', true )==''){ 
                            //$hide_ar_view = 'display:none;';
                            
                        }
                        echo '<div class="ar_admin_viewer" id="ar_admin_model_'.esc_html($model_array['id']).'" style="padding: 10px; '.esc_html($hide_ar_view).'"><div id="ar_admin_modelviewer">';
                            echo '<div style="width: 100%; border: 1px solid #f8f8f8;">';

                            //echo $ar_plugin->ar_display_shortcode($model_array);
                            echo do_shortcode('[ar-display id="'.$model_array['id'].'"]');

                            echo '</div>'; 
                            $ar_camera_orbit = get_post_meta( $model_array['id'], '_ar_camera_orbit', true );
                            if ($public != 'y'){
                            ?>
                        
                            <button id="downloadPosterToBlob" onclick="downloadPosterToDataURL('<?php echo esc_html($model_array['id']);?>')" class="button" type="button" style="margin-top:10px"><?php esc_html_e( 'Set AR Poster Image', 'ar-for-wordpress' );?></button>
                            <input type="hidden" id="_ar_poster_image_field" name="_ar_poster_image_field">
                            
                            <input id="camera_view_button" class="button" type="button" style="float:right;margin-top: 10px" value="<?php esc_html_e( 'Set Current Camera View as Initial', 'ar-for-wordpress' );?>" <?php echo esc_html($disabled);?> />
                            <div id="_ar_camera_orbit_set" style="float:right;margin: 10px;display:none"><span style="color:green;margin-left: 7px; font-size: 19px;">&#10004;</span></div>
                            <input id="_ar_camera_orbit" name="_ar_camera_orbit" type="text" value="<?php echo esc_html($ar_camera_orbit);?>" style="display:none;"><br clear="all" style="float:right;">
                        
                        <?php  
                        }
                        
                        ?>
                    </div>
                </div>
                
                <div style="clear:both"></div>
                   
                        
                       
                    <?php
                    
                    if($plan_check!='Premium') { 
                	    echo '</div>'; 
                	//close the div that disables mouse clicking 
                	}
                	?>
            
    
        <?php
            /*Set post content to include AR shortcode*/
        	//$post = array('ID'=> $model_array['id'], 'post_content' => '[ardisplay id='.$model_array['id'].']');
            wp_update_post( $post );
            //Output Upload Choose AR Model Files Javascript
            
        }

        $wc_model = 0;
        $variation_id = '';
        $suffix = $variation_id ? "_var_".$variation_id : '';

        $arpost = get_post( $model_array['id'] );
        $ar_animation_selection = get_post_meta( $model_array['id'], '_ar_animation_selection', true ); 

        if($arpost->post_type == 'product'){
            $product=wc_get_product($model_array['id']);
            $product_parent=$product->get_parent_id();
            $wc_model = 1;
            if($product_parent==0){
                $product_parent = $model_array['id'];
            }
        } else {
            $product_parent = $model_array['id'];
        }
        

        ?>
        </div>
        <script>
            jQuery(document).ready(function(){  
                var modelFieldOptions = {                                                   
                    product_parent: '<?php echo esc_html($product_parent);?>',
                    usdz_thumb: '<?php echo esc_url( plugins_url( "assets/images/ar_model_icon_tick.jpg", __FILE__ ) );?>',
                    glb_thumb: '<?php echo esc_url( plugins_url( "assets/images/ar_model_icon_tick.jpg", __FILE__ ) );?>',
                    site_url: '<?php echo esc_url(get_site_url());?>',
                    js_alert: '<?php echo esc_html(__('Invalid file type. Please choose a USDZ, REALITY, GLB or GLTF.', 'ar-for-wordpress' ));?>',
                    uploader_title: '<?php echo esc_html(__('Choose your AR Files', 'ar-for-wordpress' ));?>',
                    suffix: '<?php echo esc_html($suffix);?>',
                    ar_animation_selection: '<?php echo esc_html($ar_animation_selection);?>', 
                    public: '<?php echo esc_html($public);?>',
                    wc_model: '<?php echo esc_html($wc_model);?>',
                };
                
                var modelFields_<?php echo esc_html($model_array['id']);?> = new ARModelFields(<?php echo esc_html($model_array['id']);?>,modelFieldOptions);
            });
                
           
                              
            <?php if ($public != 'y'){ ?>
                //Custom CSS Importing
                function importCSS(){
                    var css_content = '<?php if ($ar_css_import_global!=''){ echo esc_js(ar_encodeURIComponent($ar_css_import_global));}else{ echo esc_js(ar_encodeURIComponent($ar_css_import));}?>';

                    

                    document.getElementById('_ar_css').value = decodeURI(css_content);
                    <?php 
                    $ar_css_positions = get_option('ar_css_positions');
                    if (is_array($ar_css_positions)){
                        foreach ($ar_css_positions as $k => $v){
                              echo "document.getElementById('_ar_css_positions[".esc_html($k)."]').value = '".esc_html($v)."';
                              ";
                        }
                    }
                    ?>
                }
                
                document.getElementById('_ar_css_override').addEventListener('change', function() {
                    var element = document.getElementById("ar_custom_css_div");
                    if (document.getElementById("_ar_css_override").checked == true){
                        element.style.display = "block";
                    }else{
                        element.style.display = "none";
                    }
                });
            <?php } ?>
    
      jQuery(document).ready(function($){
        // Convert the JSON-encoded PHP array to a JavaScript array
        var arOpenTabsArray = [];

        // Loop through each button ID and trigger a click
        arOpenTabsArray.forEach(function (buttonId) {
            var ar_tab_button = document.getElementById(buttonId);

            // Check if the button element exists
            if (ar_tab_button) {
                ar_tab_button.click();
            }
        });
      });
        
    </script>
        
        <!-- HOTSPOTS -->
        <!-- The following libraries and polyfills are recommended to maximize browser support -->
        <!-- Web Components polyfill to support Edge and Firefox < 63 -->
        <?php load_model_viewer_components_js(); ?>
        <script>
            var hotspotCounter = <?php echo esc_html($hotspot_count); ?>;
            function addHotspot(MouseEvent) {
                //var _ar_hotspot_check = document.getElementById('_ar_hotspot_check').value;
                if (document.getElementById("_ar_hotspot_check").checked != true){
                return;
                    
                }
                var inputtext = document.getElementById('_ar_hotspot_text').value;
            
                // if input = nothing then alert error if it isnt then add the hotspot
                if (inputtext == ""){
                    alert("<?php esc_html_e( 'Enter hotspot text first, then click the Add Hotspot button.', 'ar-for-wordpress' );?>");
                    return;
                }else{
                    var inputlink = document.getElementById('_ar_hotspot_link').value;
                    if (inputlink){
                        inputtext = '<a href="'+inputlink+'" target="_blank">'+inputtext+'</a>';
                    }
                    const viewer = document.querySelector('#model_<?php echo esc_html($model_array['id']); ?>');
                    const x = event.clientX;
                    const y = event.clientY;
                    const positionAndNormal = viewer.positionAndNormalFromPoint(x, y);
                    
                    // if the model is not clicked return the position in the console
                    if (positionAndNormal == null) {
                        console.log('no hit result: mouse = ', x, ', ', y);
                        return;
                    }
                    const {position, normal} = positionAndNormal;
                    
                    // create the hotspot
                    const hotspot = document.createElement('button');
                    hotspot.slot = `hotspot-${hotspotCounter ++}`;
                    hotspot.classList.add('hotspot');
                    hotspot.id = `hotspot-${hotspotCounter}`;
                    hotspot.dataset.position = position.toString();
                    if (normal != null) {
                        hotspot.dataset.normal = normal.toString();
                    }
                    viewer.appendChild(hotspot);
                    
                    // adds the text to last hotspot
                    var element = document.createElement("div");
                    element.classList.add('annotation');
                    element.innerHTML = inputtext;
                    document.getElementById(`hotspot-${hotspotCounter}`).appendChild(element);
                    
                    //Add Hotspot Input fields
                    var hotspot_container = document.getElementById(`_ar_hotspot_container_${hotspotCounter -1}`);
                    hotspot_container.insertAdjacentHTML('afterend', `<div style="clear:both"></div><div id="_ar_hotspot_container_${hotspotCounter}" style="padding-bottom: 10px"><div class="ar_admin_label"><label for="ar_admin_field">Hotspot ${hotspotCounter}</label></div><div class="ar_admin_field" id="_ar_hotspot_field_${hotspotCounter}">`);
                    
                    var hotspot_fields = document.getElementById(`_ar_hotspot_field_${hotspotCounter}`);
                    
                    var inputList = document.createElement("input");
                    inputList.setAttribute('type','text');
                    inputList.setAttribute('class','regular-text hotspot_annotation');
                    inputList.setAttribute('id',`_ar_hotspots[link][${hotspotCounter}]`);
                    inputList.setAttribute('name',`_ar_hotspots[link][${hotspotCounter}]`);
                    inputList.setAttribute('hotspot_name',`hotspot-${hotspotCounter}`);
                    inputList.setAttribute('value',document.getElementById('_ar_hotspot_link').value);
                    inputList.setAttribute('placeholder','Link');
                    hotspot_fields.insertAdjacentElement('afterend', inputList);   
                    
                    var inputList = document.createElement("input");
                    inputList.setAttribute('type','text');
                    inputList.setAttribute('class','regular-text hotspot_annotation');
                    inputList.setAttribute('id',`_ar_hotspots[annotation][${hotspotCounter}]`);
                    inputList.setAttribute('name',`_ar_hotspots[annotation][${hotspotCounter}]`);
                    inputList.setAttribute('hotspot_name',`hotspot-${hotspotCounter}`);
                    inputList.setAttribute('value',document.getElementById('_ar_hotspot_text').value);
                    inputList.setAttribute('placeholder','Annotation');
                    hotspot_fields.insertAdjacentElement('afterend', inputList);
                    
                    var inputList = document.createElement("input");
                    inputList.setAttribute('hidden','true');
                    inputList.setAttribute('id',`_ar_hotspots[data-position][${hotspotCounter}]`);
                    inputList.setAttribute('name',`_ar_hotspots[data-position][${hotspotCounter}]`);
                    inputList.setAttribute('value',hotspot.dataset.position);
                    hotspot_fields.insertAdjacentElement('afterend', inputList);
                    
                    var inputList = document.createElement("input");
                    inputList.setAttribute('hidden','true');
                    inputList.setAttribute('id',`_ar_hotspots[data-normal][${hotspotCounter}]`);
                    inputList.setAttribute('name',`_ar_hotspots[data-normal][${hotspotCounter}]`);
                    inputList.setAttribute('value',hotspot.dataset.normal);
                    hotspot_fields.insertAdjacentElement('afterend', inputList);
                    
                    hotspot_fields.insertAdjacentHTML('afterend', '</div></div>');
                    
                    var additionalPanel = document.getElementById("ar_additional_interactions_panel");

                    // Check if the element exists
                    if (additionalPanel) {
                        // Get the current height and add 100px to it
                        var newHeight = additionalPanel.offsetHeight + 100;
                    
                        // Set the new height to the element
                        //additionalPanel.style.height = newHeight + "px";
                        additionalPanel.style.maxHeight = newHeight + "px";
                    }
                    //Reset hotspot text box and checkbox
                    document.getElementById('_ar_hotspot_text').value = "";
                    document.getElementById('_ar_hotspot_link').value = "";
                    document.getElementById("_ar_hotspot_check").checked = false;
                    
                    //Show Remove Hotspot button
                    document.getElementById('_ar_remove_hotspot').style = "display:block;";
                }
            }
            function enableHotspot(){
                var inputtext = document.getElementById('_ar_hotspot_text').value;
                if (inputtext == ""){
                    alert("<?php esc_html_e( 'Enter hotspot text first, then click Add Hotspot button.', 'ar-for-wordpress' );?>");
                    return;
                }else{
                    document.getElementById("_ar_hotspot_check").checked = true;
                }
            }
            function removeHotspot(){
                var el = document.getElementById(`_ar_hotspot_container_${hotspotCounter}`);
                var el2 = document.getElementById(`hotspot-${hotspotCounter}`);
                if (el == null){
                    alert("No hotspots to delete");
                }else{
                    hotspotCounter --;
                    el.remove(); // Removes the last added hotspot fields
                    el2.remove(); // Removes the last added hotspot from model
                }
            }
            document.addEventListener('DOMContentLoaded', function () {
            // Array of button IDs
            var buttonIds = ['ar_display_options_acc', 'ar_rotation_acc', 'ar_disable_elements_acc', 'ar_qr_code_acc', 'ar_additional_interactions_acc', 'ar_alternative_acc', 'ar_element_positions_acc'];
            
            // Text field
            var arOpenTabsTextField = document.getElementById('ar_open_tabs');
            
            // Add click event listeners to buttons
            buttonIds.forEach(function (buttonId) {
            var button = document.getElementById(buttonId);
            
            if (button) {
              button.addEventListener('click', function () {
                // Get the current value of the text field
                var currentText = arOpenTabsTextField.value;
            
                // Check if the button ID is already present in the text field
                var isButtonInText = currentText.includes(buttonId);
            
                // Update the text field based on whether the button ID is already present
                if (isButtonInText) {
                  // Remove the button ID from the text field
                  var newText = currentText.replace(buttonId + ',', '').replace(',' + buttonId, '');
                  arOpenTabsTextField.value = newText;
                } else {
                  // Add the button ID to the text field
                  var newText = currentText + buttonId + ',';
                  arOpenTabsTextField.value = newText;
                }
              });
            }
            });
            });

        </script>
        <script nonce="<?php echo esc_js(wp_create_nonce('set_ar_featured_image')); ?>">
            
            //Save screenshot of model
            function downloadPosterToDataURL() {
                const modelViewer = document.querySelector('#model_<?php echo esc_html($model_array['id']); ?>');
                var btn = document.getElementById("downloadPosterToBlob");
                btn.innerHTML = 'Creating Image';
                btn.disabled = true;
                const url = modelViewer.toDataURL("image/png").replace("image/png", "image/octet-stream");
                const a = document.createElement("a");
                document.getElementById("_ar_poster_image_field").value=url;
                var xhr = new XMLHttpRequest();
                //document.getElementById("nonce").value="<?php wp_create_nonce('set_ar_featured_image'); ?>"
                var data = new FormData();
                data.append('post_ID', document.getElementById("post_ID").value);
                
                if(document.getElementById("original_post_title")){
                    data.append('post_title', document.getElementById("original_post_title").value);
                } else if(document.getElementsByClassName("wp-block-post-title")) {
                    data.append('post_title', document.getElementsByClassName("wp-block-post-title")[0].value);
                } else {
                    data.append('post_title','armodel-' + document.getElementById("post_ID").value);
                }
                data.append('_ar_poster_image_field',document.getElementById("_ar_poster_image_field").value);
                data.append('action',"set_ar_featured_image");
                data.append('nonce',"<?php echo esc_js(wp_create_nonce('set_ar_featured_image')); ?>");
                //data.nonce = "<?php wp_create_nonce('set_ar_featured_image'); ?>";
               // console.log(data);
                xhr.open("POST", "<?php echo esc_url(site_url('wp-json/arforwp/v2/set_ar_featured_image/'));?>", true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                /*xhr.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        var attachmentID = xhr.responseText; 
                    wp.media.featuredImage.set( attachmentID );
                   }
                };*/

                //convert to json
                var object = {};
                data.forEach(function(value, key){
                    object[key] = value;
                });
                var json = JSON.stringify(object);


                xhr.onload = function () { 
                    var attachmentID = xhr.responseText; 
                    wp.media.featuredImage.set( attachmentID );
                    btn.innerHTML = 'Set AR Poster Image';
                    btn.disabled = false;
                }
                
                xhr.send(json);
                return false;
            }
        </script>
        
        <?php
    }
}


add_action('save_post', 'save_ar_wp_option_fields', 10); // Saving the uploaded file details
    

add_filter( 'wp_insert_post_data' , 'filter_post_data' , '99', 2 );

function filter_post_data( $data , $postarr ) {
    global $post;
    if (isset($post)){
        if ($data['post_type']=='armodels'){
            // Add AR Display shortcode to the post content field
            $data['post_content'] = '[ardisplay id='.$post->ID.']';
        }
    }
    return $data;
}
?>