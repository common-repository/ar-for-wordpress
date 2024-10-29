<?php
/**
 * AR Display
 * https://augmentedrealityplugins.com
**/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/********** Settings Page **********/
if (!function_exists('ar_subscription_setting')){
    function ar_subscription_setting() {
        global $wpdb, $ar_version, $ar_plugin_id, $ar_wc_active, $ar_wp_active, $ar_rate_this_plugin, $shortcode_examples, $shortcode_examples_wc, $woocommerce_featured_image, $ar_whitelabel, $ar_css_styles, $ar_css_names;
        $ar_licence_key = get_option('ar_licence_key');
        // Verify the nonce before processing
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'ar_secure_nonce' ) ) {
            // If the nonce is invalid, stop the process
          //  wp_die( __( 'Security check failed.', 'ar-for-wordpress' ) );
        }
        if ($_POST){
            //Save Settings
            $post_license_key = isset($_POST['ar_licence_key']) ? sanitize_text_field( wp_unslash( $_POST['ar_licence_key'] )) : '';

            if ($ar_licence_key != $post_license_key){
                update_option( 'ar_licence_renewal', '');
                $ar_licence_key = $post_license_key;
            }
            $settings_fields=array('ar_licence_key','ar_wl_file', 'ar_view_file', 'ar_qr_file', 'ar_qr_destination','ar_view_in_ar','ar_view_in_3d', 'ar_dimensions_units', 'ar_dimensions_label', 'ar_user_upload_button', 'ar_user_default',  'ar_user_default_image', 'ar_user_button','ar_user_modelviewer','ar_fullscreen_file', 'ar_play_file', 'ar_pause_file', 'ar_dimensions_inches', 'ar_hide_dimensions','ar_no_posts', 'ar_hide_arview', 'ar_hide_qrcode', 'ar_hide_reset', 'ar_hide_fullscreen','ar_hide_gallery_sizes','ar_scene_viewer','ar_css','ar_css_positions', 'ar_open_tabs_remember','ar_secure_model_urls');
            foreach ($settings_fields as $k => $v){
                if (!isset($_POST[$v])){$_POST[$v]='';}
                update_option( $v, sanitize_text_field( wp_unslash($_POST[$v])));
            }
        }
        //Delete Post
        if (isset($_GET['delete_post_id'])) {
            $post_id = intval(sanitize_text_field( wp_unslash($_GET['delete_post_id'])));
            $post_type = get_post_type($post_id);
    
            if (current_user_can('delete_post', $post_id)) {
                if ($post_type === 'product') {
                    // Delete the _ar_display meta key from the product
                    delete_post_meta($post_id, '_ar_display');
                    echo '<div class="updated"><p>AR Model deleted from product successfully.</p></div>';
                } else {
                    // Delete the post
                    wp_delete_post($post_id, true);
                    echo '<div class="updated"><p>AR Model deleted successfully.</p></div>';
                }
            } else {
                echo '<div class="error"><p>You do not have permission to delete this post/product.</p></div>';
            }
        }
    
        $ar_logo = esc_url( plugins_url( '../assets/images/Ar_logo.png', __FILE__ ) ); 
        $ar_wl_logo = get_option('ar_wl_file'); 
        $ar_logo_file_txt = wp_kses('Upload', ar_allowed_html());
        ?>
        <div class="message_set"></div>
      
        <div class="licence_key" id="key" style="float:left; padding:0px;">
            <form method="post" action="edit.php?post_type=armodels&page">
        <?php 
        //Renewal Date Check
        $renewal_check = get_option('ar_licence_renewal');
        if (($renewal_check=='')OR( strtotime($renewal_check) < strtotime(gmdate('Y-m-d')) )) {
            ar_cron();
            $renewal_check = get_option('ar_licence_renewal');
        }
        $plugin_check = get_option('ar_licence_valid');
        $plan_check = get_option('ar_licence_plan');
        
        
        if ($ar_whitelabel!=true){ ?>   
            <div class="ar_site_logo">
                <a href = "https://augmentedrealityplugins.com" target = "_blank">              
                <img src="<?php echo esc_url($ar_logo);?>" style="width:300px; padding:20px;float:left" />
                </a>
            </div>
            <br clear="all">
            <?php
                echo '<h1 style="padding:0px 20px;">';
                if ($ar_plugin_id=='ar-for-woocommerce'){
                    echo wp_kses('AR For Woocommerce', ar_allowed_html());
                }else{
                    echo wp_kses('AR For WordPress', ar_allowed_html()); 
                }
                echo ' v'.esc_html($ar_version).'</h1><br>';
            ?>
        <?php }else{
        //White Label Logo 
        ?>
        <div>
            <?php 
            
            if ($ar_wl_logo){
            ?>
                <div class="ar_site_logo">
                                
                    <img src="<?php echo esc_url($ar_wl_logo);?>" style="max-width:300px; padding:0px;float:left" />
                    <input type="hidden" name="ar_wl_file" id="ar_wl_file" class="regular-text" value="<?php echo esc_url($ar_wl_logo); ?>">
                </div>
                <br clear="all">
            <?php }
            if (!get_option('ar_licence_key')){  ?>
                  <div style="min-width:160px;float:left;"><strong>White Label Logo</strong></div>
                  <div style="float:left;"><input type="text" name="ar_wl_file" id="ar_wl_file" class="regular-text" value="<?php echo esc_url($ar_wl_logo); ?>"> <input id="ar_wl_file_button" class="button" type="button" value="White Label Logo File" /> <img src="<?php echo esc_url( plugins_url( "../assets/images/delete.png", __FILE__ ) );?>" style="width: 15px;vertical-align: middle;cursor:pointer" onclick="document.getElementById('ar_wl_file').value = ''"></div>
            <?php } ?>
            </div>
            <br  clear="all">
        
        
        <?php }?>
            <div id="licence_page" class="licence_page">
            
                <button class="ar_accordian ar_active" id="ar_plan_acc" type="button" style="border-top: 1px solid #ccc;"><?php echo wp_kses('Subscription Plan', ar_allowed_html()); ?></button>
                <div id="ar_plan_panel" class="ar_accordian_panel" style="display: block; max-height: 139px;">
                    <br>
                <?php settings_fields( 'ar_display_options_group' ); ?>  
                
                <div>
                  <div style="min-width:160px;float:left;"><strong>
                      <?php
                        echo wp_kses('License Key', ar_allowed_html());
                        ?></strong></div>
                  <div style="float:left;"><input type="text" id="ar_licence_key" name="ar_licence_key" class="regular-text" style="width:160px" value="<?php echo esc_html($ar_licence_key); ?>" /></div>
                </div>
                  
                <?php 
                //Model Count
                $model_count = ar_model_count();
                $disabled = '';
                if($plan_check=='Premium') { 
                    echo '<div style="float:left;margin-top:4px"><span style="color:green;margin-left: 7px; font-size: 19px;">&#10004;</span> '.esc_html(get_option('ar_licence_plan')).'</div>'; 
                } else { 
                    if ($ar_licence_key!=''){
                        echo '<div style="float:left;margin-top:4px"><span style="color:red;margin-left: 7px; font-size: 19px;">&#10008;</span></div>';
                    }
                    if ($model_count>=2){
                        $disabled =' disabled';
                    }
                }
                
                //Display Renewal Date
                if ($renewal_check !=''){ 
                ?>
                  <br clear="all"><br>
                  <div>
                      <div style="min-width:160px;float:left;"><strong>
                          <?php
                            echo wp_kses('Renewal', ar_allowed_html());
                            ?></strong></div>
                      <div style="float:left;"><?php echo esc_html(gmdate('j F Y', strtotime($renewal_check)));?></div>
                    </div>
                <?php } 
                
                
                $alert = '';
                ?>
                  <br clear="all"><br>
                  <div>
                      <div style="float:right"><a href="https://augmentedrealityplugins.com/my-account/" target="_blank">Manage Subscription</a> | <a href="https://augmentedrealityplugins.com/support/" target="_blank">Documentation</a></div>
                      <div style="min-width:160px;float:left;"><strong>
                          <?php
                            echo wp_kses('Model Count', ar_allowed_html());
                            ?></strong></div>
                      <div style="float:left;"><?php 
                      if ($disabled!=''){
                          if ($ar_licence_key==''){
                               $alert = wp_kses('You have too many AR models for the free plugin', ar_allowed_html());
                          }else{
                              $alert = wp_kses('Invalid or Expried Licence Key', ar_allowed_html());
                          }
                      }
                      echo esc_html($model_count);
                      
                      ?></div>
                    </div>
                <?php if ($alert!=''){
                    echo '<br clear="all"><br><div id="upgrade_ribbon" class="notice notice-error is-dismissible"><p>'.esc_html($alert). '</p></div>';
                    if ($disabled!=''){
                        if ($ar_licence_key==''){
                              echo wp_kses(display_armodels_posts(), ar_allowed_html());
                        }
                      }
                }?>
                <?php
                if($plan_check!='Premium') { 
                    echo '<br clear="all"><br><a href="https://augmentedrealityplugins.com/" target="_blank" class="button" style="float:right;">'.'Sign Up For Premium'.'</a>
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">';
                    $disabled =' disabled';
                }
                ?>
                    <br clear="all">
                    <br>
                </div> <!-- end of Accordian Panel -->
                
                <button class="ar_accordian" id="ar_options_acc" type="button"><?php echo wp_kses('Options', ar_allowed_html());
                        if ($disabled!=''){echo ' - '.wp_kses('Premium Plans Only', ar_allowed_html());} ?></button>
                <div id="ar_options_panel" class="ar_accordian_panel">
                    <br>
                    <div>
                          <div style="min-width:160px;float:left;"><strong>
                              <?php
                                echo wp_kses('Custom AR Button', ar_allowed_html());
                                //$ar_logo_file_txt = ar_output('AR Logo File', 'ar-for-wordpress');
                                
                                ?></strong></div>
                          <div style="float:right;"><input type="text" name="ar_view_file" id="ar_view_file" class="regular-text" value="<?php echo esc_html(get_option('ar_view_file')); ?>" <?php echo  esc_html($disabled);?>/> <input id="ar_view_file_button" class="button" type="button" value="<?php echo esc_html($ar_logo_file_txt);?>" <?php echo  esc_html($disabled);?> /> <img src="<?php echo esc_url( plugins_url( "../assets/images/delete.png", __FILE__ ) );?>" style="width: 15px;vertical-align: middle;cursor:pointer" onclick="document.getElementById('ar_view_file').value = ''"></div>
                    </div>
                    <br  clear="all">
                    <br>
                    
                    <div>
                          <div style="min-width:160px;float:left;"><strong>
                              <?php
                                echo wp_kses('Custom Fullscreen Button', ar_allowed_html());
                                //$ar_logo_file_txt = ar_output('AR Fullscreen File', 'ar-for-wordpress');
                                ?></strong></div>
                          <div style="float:right;"><input type="text" name="ar_fullscreen_file" id="ar_fullscreen_file" class="regular-text" value="<?php echo esc_html(get_option('ar_fullscreen_file')); ?>" <?php echo  esc_html($disabled);?>/> <input id="ar_fullscreen_file_button" class="button" type="button" value="<?php echo esc_html($ar_logo_file_txt);?>" <?php echo  esc_html($disabled);?> /> <img src="<?php echo esc_url( plugins_url( "../assets/images/delete.png", __FILE__ ) );?>" style="width: 15px;vertical-align: middle;cursor:pointer" onclick="document.getElementById('ar_fullscreen_file').value = ''"></div>
                    </div>
                    <br  clear="all">
                    <br>
                    <div>
                          <div style="min-width:160px;float:left;"><strong>
                              <?php
                                wp_kses('Custom Play Button', ar_allowed_html());
                                //$ar_logo_file_txt = ar_output('AR Play File', 'ar-for-wordpress');
                                ?></strong></div>
                          <div style="float:right;"><input type="text" name="ar_play_file" id="ar_play_file" class="regular-text" value="<?php echo esc_html(get_option('ar_play_file')); ?>" <?php echo esc_html($disabled);?>/> <input id="ar_play_file_button" class="button" type="button" value="<?php echo esc_html($ar_logo_file_txt);?>" <?php echo esc_html($disabled);?> /> <img src="<?php echo esc_url( plugins_url( "../assets/images/delete.png", __FILE__ ) );?>" style="width: 15px;vertical-align: middle;cursor:pointer" onclick="document.getElementById('ar_play_file').value = ''"></div>
                    </div>
                    <br  clear="all">
                    <br>
                    <div>
                          <div style="min-width:160px;float:left;"><strong>
                              <?php
                                echo wp_kses('Custom Pause Button', ar_allowed_html());
                                //$ar_logo_file_txt = ar_output('AR Pause File', 'ar-for-wordpress');
                                
                                ?></strong></div>
                          <div style="float:right;"><input type="text" name="ar_pause_file" id="ar_pause_file" class="regular-text" value="<?php echo esc_html(get_option('ar_pause_file')); ?>" <?php echo  esc_html($disabled);?>/> <input id="ar_pause_file_button" class="button" type="button" value="<?php echo esc_html($ar_logo_file_txt);?>" <?php echo  esc_html($disabled);?> /> <img src="<?php echo esc_url( plugins_url( "../assets/images/delete.png", __FILE__ ) );?>" style="width: 15px;vertical-align: middle;cursor:pointer" onclick="document.getElementById('ar_pause_file').value = ''"></div>
                    </div>
                    <br  clear="all">
                    <br>
                    
                    <div>
                          <div style="width:50%;float:left;"><strong>
                              <?php
                                echo wp_kses('Dimension Units', ar_allowed_html());
                                $ar_dimensions_units = get_option('ar_dimensions_units');
                                ?></strong><br><select id="ar_dimensions_units" name="ar_dimensions_units" class="ar-input" <?php echo  esc_html($disabled);?>>
                              <option value="">Meters</option>
                              <option value="cm" <?php
                                if ($ar_dimensions_units=='cm'){
                                    echo esc_html('selected');
                                }
                              ?>
                              >Centimeters</option>
                              <option value="mm" <?php
                                if ($ar_dimensions_units=='mm'){
                                    echo esc_html('selected');
                                }
                              ?>
                              >Milimeters</option>
                              <option value="inches" <?php
                                if (($ar_dimensions_units=='inches')OR(get_option('ar_dimensions_inches')==true)){
                                    echo esc_html('selected');
                                }
                              ?>
                              >Inches</option>
                              </select>
                          </div>
                    </div>
                    <div>
                          <div style="width:50%;float:left;"><strong>
                              <?php echo wp_kses('Dimensions Label', ar_allowed_html()); ?></strong><br>
                              <input id="ar_dimensions_label" name="ar_dimensions_label" class="ar-input" style="width:120px" value="<?php echo  esc_html(get_option('ar_dimensions_label')); ?>" placeholder="<?php echo wp_kses('Dimensions', ar_allowed_html());?>" <?php echo  esc_html($disabled);?>>
                          </div>
                    </div>
                    <br  clear="all">
                    <br>
                    <div>
                          <div style="width:50%;float:left;"><strong>
                              [ar-view] <?php
                                echo wp_kses('View in AR Label', ar_allowed_html());
                                
                                ?></strong><br>
                              <input id="ar_view_in_ar" name="ar_view_in_ar" class="ar-input" style="width:120px" value="<?php echo esc_html( get_option('ar_view_in_ar')); ?>" placeholder="<?php echo wp_kses('View in AR', ar_allowed_html());?>"  <?php echo  esc_html($disabled);?>>
                          </div>
                    </div>
                    <div>
                          <div style="width:50%;float:left;"><strong>
                              [ar-view] <?php echo wp_kses('View in 3D Label', ar_allowed_html()); ?></strong><br>
                              <input id="ar_view_in_3d" name="ar_view_in_3d" class="ar-input" style="width:120px" value="<?php echo  esc_html(get_option('ar_view_in_3d')); ?>" placeholder="<?php echo wp_kses('View in 3D', ar_allowed_html());?>" <?php echo  esc_html($disabled);?>>
                          </div>
                    </div>
                    <br clear="all">
                    <br>
                    
                    
                    
                </div> <!-- end of Accordian Panel -->
                <button class="ar_accordian" id="ar_disable_elements_acc" type="button"><?php echo wp_kses('Disable/Hide Elements', ar_allowed_html()); if ($disabled!=''){echo ' - '.wp_kses('Premium Plans Only', ar_allowed_html());}?></button>
                <div id="ar_disable_elements_panel" class="ar_accordian_panel">
                    <br>
                    <?php
                    //Global Checkbox Fields 
                    $field_array = array('ar_hide_dimensions' => 'Dimensions', 
                    'ar_hide_arview' => wp_kses('AR View', ar_allowed_html()), 
                    'ar_hide_qrcode' => wp_kses('QR Code', ar_allowed_html()), 
                    'ar_hide_reset' => wp_kses('Reset', ar_allowed_html()), 
                    'ar_hide_fullscreen' => wp_kses('Fullscreen', ar_allowed_html()), 
                    'ar_hide_gallery_sizes' => wp_kses('Gallery Size Selector', ar_allowed_html()), 
                    'ar_secure_model_urls' => wp_kses('Encrypt Model URLs', ar_allowed_html()), 
                    'ar_scene_viewer' => wp_kses('Android - Prioritise Scene Viewer over WebXR', ar_allowed_html()),
                    'ar_open_tabs_remember' =>wp_kses('Remembering Open Tabs', ar_allowed_html())
                    );
                    if ($ar_plugin_id!='ar-for-woocommerce'){
                        $field_array['ar_no_posts'] = wp_kses('Disable Viewing Model Posts', ar_allowed_html());
                    }
                   // $field_array['ar_secure_model_urls'] = 'Encrypt Model URLs';
                    //$field_array['ar_scene_viewer'] = 'Android - Prioritise Scene Viewer over WebXR';
                    $count = 0;
                    foreach ($field_array as $field => $title){
                        $count++;
                        if ($field == 'ar_secure_model_urls'){
                            echo '<br clear="all"><br>
                            </div> <!-- end of Accordian Panel -->
                            <button class="ar_accordian" id="ar_security_acc" type="button">'.wp_kses('Security & Performance', ar_allowed_html());
                            if ($disabled!=''){echo ' - '.wp_kses('Premium Plans Only', ar_allowed_html());}
                            echo '</button>
                            <div id="ar_security_panel" class="ar_accordian_panel">
                            <br>';
                            $count=1;
                        }
                    ?>
                        <div>
                          <div style="width:50%;float:left;"><input type="checkbox" id="<?php echo esc_html($field);?>" name="<?php echo esc_html($field);?>" class="ar-ui-toggle"  value="1" <?php if (get_option($field)=='1'){echo 'checked'; } ?> <?php echo  esc_html($disabled);?>/> <label for="<?php echo esc_html($field);?>">
                              <?php
                                   echo esc_html($title);
                                    
                                    ?></label></div>
                        </div>
                        <?php 
                        if ($count==2){
                            $count=0;
                            echo '<br  clear="all"><br>'; 
                        } 
                    } ?>
                </div> <!-- end of Accordian Panel -->
                 <button class="ar_accordian" id="ar_qr_acc" type="button"><?php echo wp_kses('QR Code', ar_allowed_html()); if ($disabled!=''){echo ' - '.wp_kses('Premium Plans Only', ar_allowed_html());}?></button>
                <div id="ar_qr_panel" class="ar_accordian_panel"><br>
                    <div>
                          <div style="min-width:160px;float:left;"><strong>
                              <?php
                                echo wp_kses('Custom QR Logo', ar_allowed_html());
                                //$ar_logo_file_txt = ar_output('QR Logo File', 'ar-for-wordpress');
                                
                                ?></strong></div>
                          <div style="float:right;"><input type="text" name="ar_qr_file" id="ar_qr_file" class="regular-text" value="<?php echo esc_html(get_option('ar_qr_file')); ?>" <?php echo  esc_html($disabled);?>> <input id="ar_qr_file_button" class="button" type="button" value="<?php echo esc_html($ar_logo_file_txt);?>" <?php echo  esc_html($disabled);?>/> <img src="<?php echo esc_url( plugins_url( "../assets/images/delete.png", __FILE__ ) );?>" style="width: 15px;vertical-align: middle;cursor:pointer" onclick="document.getElementById('ar_qr_file').value = ''"></div>
                        </div>
                        
                        
                    <br clear="all"><?php echo wp_kses('JPG file 250 x 250px', ar_allowed_html());?> - <?php echo wp_kses('Requires Imagick PHP Extension', ar_allowed_html());?><br><br>
                    <div style="width:50%;float:left;"><strong>
                          <?php
                            echo wp_kses('QR Code Destination', ar_allowed_html());
                            
                            ?></strong><br><select id="ar_qr_destination" name="ar_qr_destination" class="ar-input" <?php echo  esc_html($disabled);?>>
                          <option value="parent-page">Parent Page</option>
                          <option value="model-viewer" <?php
                            if (get_option('ar_qr_destination')=='model-viewer'){
                                echo 'selected';
                            }
                          ?>
                          >AR View</option>
                          </select>
                    </div>
                    <br clear="all"><br>
                </div> <!-- end of Accordian Panel -->
                
                <button class="ar_accordian" id="ar_user_upload_acc" type="button"><?php echo wp_kses('User Upload', ar_allowed_html()); echo ' [ar-user-upload]'; if ($disabled!=''){echo ' - '.wp_kses('Premium Plans Only', ar_allowed_html());}?></button>
                <div id="ar_user_upload_panel" class="ar_accordian_panel">
                    <br>
                    <div style="width:50%;float:left;"><strong>
                      <?php echo wp_kses('Upload Button Label', ar_allowed_html()); ?></strong><br>
                      <input id="ar_user_upload_button" name="ar_user_upload_button" class="ar-input" style="width:120px" value="<?php echo  esc_html(get_option('ar_user_upload_button')); ?>" placeholder="<?php echo wp_kses('Upload Model or Image', ar_allowed_html());?>" <?php echo  esc_html($disabled);?>>
                    </div>
                     <?php    
                            $ar_user_defaults = array(
                                'Default',
                                'Featured Image',
                                'Custom'
                            );
                    
                    ?>
                    
                        <div style="width:50%;float:left;"><strong>
                          <?php
                            echo wp_kses('Default Image/Model', ar_allowed_html());
                            
                            ?></strong><br><select id="ar_user_default" name="ar_user_default" class="ar-input" <?php echo  esc_html($disabled);?>>
                                <?php
                                foreach ($ar_user_defaults as $k => $v){
                                    echo '<option value="'.esc_html($v).'"';
                                    if (get_option('ar_user_default')==$v){
                                        echo ' selected';
                                    }
                                    echo '>'.esc_html($v).'</option>';
                                }
                                ?>
                          </select>
                        </div>
                        <br>
                    <br clear="all"><br>
                    
                    <?php if ($ar_wc_active==true){   
                            $wc_positions = array(
                                'Default',
                                'Hidden',
                                'woocommerce_before_add_to_cart_form',
                                'woocommerce_before_add_to_cart_button',
                                'woocommerce_after_add_to_cart_button',
                                'woocommerce_after_single_product_summary',
                                'woocommerce_before_single_product_summary',
                            );
                    
                    ?>
                        <div style="width:50%;float:left;"><strong>
                          <?php
                            echo wp_kses('Upload Button Location', ar_allowed_html());
                            
                            ?></strong><br><select id="ar_user_button" name="ar_user_button" class="ar-input" <?php echo  esc_html($disabled);?>>
                                <?php
                                foreach ($wc_positions as $k => $v){
                                    echo '<option value="'.esc_html($v).'"';
                                    if (get_option('ar_user_button')==$v){
                                        echo ' selected';
                                    }
                                    echo '>'.esc_html($v).'</option>';
                                }
                                ?>
                          </select>
                        </div> 
                         <div style="width:50%;float:left;"><strong>
                          <?php
                            echo wp_kses('Upload Model Viewer Location', ar_allowed_html());
                            
                            ?></strong><br><select id="ar_user_modelviewer" name="ar_user_modelviewer" class="ar-input" <?php echo  esc_html($disabled);?>>
                          <?php
                                foreach ($wc_positions as $k => $v){
                                    if ($v!='Hidden'){
                                        echo '<option value="'.esc_html($v).'"';
                                        if (get_option('ar_user_modelviewer')==$v){
                                            echo ' selected';
                                        }
                                    echo '>'.esc_html($v).'</option>';
                                    }
                                }
                                ?>
                          </select>
                        </div>
                    
                <br clear="all"><br>
                    <?php } ?>
                
                <div id="ar_custom_default_div" name="ar_custom_default_div" style="width:100%;float:left;">
                    <div style="min-width:160px;float:left;"><strong>
                      <?php echo wp_kses('Custom Default Image/Model', ar_allowed_html()); ?></strong></div>
                          <div style="float:right;"><input type="text" name="ar_user_default_image" id="ar_user_default_image" class="regular-text" value="<?php echo esc_html(get_option('ar_user_default_image')); ?>" <?php echo  esc_html($disabled);?>/> <input id="ar_user_default_image_button" class="button" type="button" value="<?php echo esc_html($ar_logo_file_txt);?>" <?php echo  esc_html($disabled);?> /> <img src="<?php echo esc_url( plugins_url( "../assets/images/delete.png", __FILE__ ) );?>" style="width: 15px;vertical-align: middle;cursor:pointer" onclick="document.getElementById('ar_user_default_image').value = ''"></div>
                      
                    </div>
                    <br clear="all">
                    <br>
                </div> <!-- end of Accordian Panel -->
                
                
                <button class="ar_accordian" id="ar_element_positions_acc" type="button"><?php echo wp_kses('Element Positions and CSS Styles', ar_allowed_html()); if ($disabled!=''){echo ' - '.wp_kses('Premium Plans Only', ar_allowed_html());}?></button>
                <div id="ar_element_positions_panel" class="ar_accordian_panel">
                    <br>
                    <?php //CSS Positions
                    $ar_css_positions = get_option('ar_css_positions');
                    $count=0;
                    foreach ($ar_css_names as $k => $v){
                        $count++;
                        ?>
                        <div>
                          <div style="width:50%;float:left;"><strong>
                              <?php
                                    echo esc_html($k);
                                    
                                    ?> </strong><br><select id="ar_css_positions[<?php echo esc_html($k);?>]" name="ar_css_positions[<?php echo esc_html($k);?>]" class="ar-input" <?php echo  esc_html($disabled);?>>
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
                        </div>
                        <?php 
                        if ($count==2){
                            $count=0;
                            echo '<br  clear="all"><br>'; 
                        }
                    
                    }
                    ?>
                    
                    <br  clear="all"><br>
                    <div>
                        <div style="width:50%;float:left;"><strong>
                        <?php
                        $ar_css = get_option('ar_css');
                        if ($ar_css==''){
                          $ar_css=ar_curl(esc_url( plugins_url( "../assets/css/ar-display-custom.css", __FILE__ ) ));
                        }
                        echo wp_kses('CSS Styling', ar_allowed_html());
                        
                        ?> </strong><br><textarea id="ar_css" name="ar_css" style="width: 450px; height: 200px;" <?php echo  esc_html($disabled);?>><?php echo esc_html($ar_css); ?></textarea></div>
                    </div>
                
                    <br clear="all"><br>
                </div> <!-- end of Accordian Panel -->
                <?php
                //Copy the Woocommerce Featured Product Template to Theme
                //if ($ar_plugin_id=='ar-for-woocommerce'){ 
                if ($ar_wc_active==true){ 
                    ?>
                    <style>
                    .ar-input{float:right !important;}
                    </style>
                    <button class="ar_accordian" id="ar_woocommerce featured_acc" type="button"><?php echo wp_kses('WooCommerce Featured Product Image/Model', ar_allowed_html()); if ($disabled!=''){echo ' - '.wp_kses('Premium Plans Only', ar_allowed_html());}?></button>
                    <div id="ar_woocommerce_featured_panel" class="ar_accordian_panel">
                        <br>
                            <h3><?php echo wp_kses('Set the WooCommerce Featured Product Image to AR Model', ar_allowed_html());?></h3>
                            <?php echo wp_kses('Copy one of the woocommerce single product templates found in the AR for Woocommerce plugin "templates" folder to your theme. The Gallery template will display your featured images as artworks to hang on the wall.', ar_allowed_html());?></p>
                            <?php 
                            $template_file = get_stylesheet_directory() . '/woocommerce/single-product/product-image.php';
                            // Check if the file exists
                            if (!file_exists($template_file) OR (isset($_POST['delete_template_file']))) {
                            ?>
                                <button id="copy-file-btn" type="button" class="button" style="float:left;margin-right:20px"><?php echo wp_kses('Copy Product Model Template', ar_allowed_html());?></button>
                                <script>
                                    jQuery(document).ready(function($) {
                                      $('#copy-file-btn').click(function() {
                                        var btn = $(this);
                                        btn.text('<?php echo wp_kses('Copying...', ar_allowed_html());?>');
                                        var data = {
                                          action: 'ar_copy_file_action',
                                        };
                                        $.post(ajaxurl, data, function(response) {
                                        btn.text(response);
                                        });
                                      });
                                    });
                                </script>
                                
                            <?php 
                            if (isset($_POST['delete_template_file'])){
                                    check_and_delete_woocommerce_template();
                                }
                            }else{
                                
                                    check_and_delete_woocommerce_template();
                                
                            }
                            $template_file = get_stylesheet_directory() . '/woocommerce/single-product/product-image.php';
                            // Check if the file exists
                            if (!file_exists($template_file) OR (isset($_POST['delete_template_file']))) {
                            ?>
                                <button id="copy-file-btn-gall" type="button" class="button" style="float:left;margin-right:20px"><?php echo wp_kses('Copy Gallery Builder Template', ar_allowed_html());?></button>
                                <script>
                                    jQuery(document).ready(function($) {
                                      $('#copy-file-btn-gall').click(function() {
                                        var btn = $(this);
                                        btn.text('<?php echo wp_kses('Copying...', ar_allowed_html());?>');
                                        var data = {
                                          action: 'ar_copy_file_action',
                                          gallery: '1'
                                        };
                                        $.post(ajaxurl, data, function(response) {
                                        btn.text(response);
                                        });
                                      });
                                    });
                                </script>
                                
                            <?php 
                            
                            
                                
                            }
                            ?>
                        
                    <br clear="all"><br>
                    </div> <!-- end of Accordian Panel -->
                
                <?php } ?>
                
                <div style="padding:0px 20px;">
                <?php 
                //if ($ar_plugin_id=='ar-for-wordpress'){
                    submit_button();
                //} ?>
                <style>
                    .components-button.is-primary:disabled{display:none;}
                </style>
                </div>
            </div>
            
        </div>
        
        
        
        <div class="licence_key" id="key" style="float:left; padding:0px;">
        <div style="padding:10px 20px 20px 20px">
        <p><a href = "https://armodelshop.com?from_plugin=true&redirect_url=<?php echo esc_url($encoded_redirect_url); ?>" target="_blank"><img src = "<?php echo esc_url( plugins_url( "../assets/images/ar-model-shop-icon.png", __FILE__ ) ); ?>" width="120px" style="float:right; padding:0 0 20px 20px;width:25%"></a></p>
                <h1>AR Model Shop</h1><h3><?php echo wp_kses('Explore the AR Model Shop for an extensive collection of 3D models tailored for augmented reality.', ar_allowed_html()); ?></h3>
                <button 
                        id="open-ar-modelshop" 
                        class="ar_model_shop_btn button" 
                        onclick="window.open('https://armodelshop.com?from_plugin=true&redirect_url=<?php echo esc_url($encoded_redirect_url); ?>', '_blank')">
                        <?php echo wp_kses('Open AR Model Shop', ar_allowed_html()); ?> <sup>&#x2197;</sup>
                    </button>
                    <br>
                <br  clear="all">
                <hr>
                <br  clear="all">
        
        <?php
        
        echo wp_kses($ar_rate_this_plugin, ar_allowed_html());
        echo '<br  clear="all">';
        if (isset($_REQUEST['tab'])){
            if ($_REQUEST['tab']=='ar_display'){
                echo wp_kses($woocommerce_featured_image, ar_allowed_html());
            }
        }
        ?>
        
        </div>
        <div id="licence_page2" class="licence_page">
        <?php
                
                $redirect_url = admin_url('admin.php?page=ar-modelshop');
                $encoded_redirect_url = urlencode($redirect_url); ?>
                
                <button class="ar_accordian" id="ar_whats_new_acc" type="button" style="border-top: 1px solid #ccc;"><?php echo wp_kses('What\'s New in v', ar_allowed_html()); echo esc_html($ar_version); if ($disabled!=''){echo ' - '.wp_kses('Premium Plans Only', ar_allowed_html());}?></button>
                <div id="ar_whats_new_panel" class="ar_accordian_panel">
                    <br>
        <?php 
        
        //Changelog latest 3 updates
        $limit=3;
        echo wp_kses(ar_changelog_retrieve($limit), ar_allowed_html()); ?>
        <br clear="all"><br>
                </div> <!-- end of Accordian Panel -->
                <button class="ar_accordian" id="ar_shortcodes_acc" type="button"><?php echo wp_kses('Shortcodes', ar_allowed_html()); if ($disabled!=''){echo ' - '.wp_kses('Premium Plans Only', ar_allowed_html());}?></button>
                <div id="ar_shortcodes_panel" class="ar_accordian_panel">
                    <br>
                    <?php
        echo wp_kses($shortcode_examples_wc, ar_allowed_html());
        echo wp_kses($shortcode_examples, ar_allowed_html());
        /*?>
        <h3><?php
        ar_output('Dimensions', 'ar-for-wordpress', 'e' );
        ?></h3> 
        
        <p><?php 
        ar_output('The dimensions show the X, Y, Z, (width, height, depth) directly from the 3D model file. You can turn this off site wide and/or on a per model basis.', 'ar-for-wordpress', 'e' );
        */
        ?>
        <br clear="all">
                </div> <!-- end of Accordian Panel -->
        </div> <!-- end of licence_page2 -->
        <div style="padding:20px">
        <?php if ($ar_whitelabel!=true){ ?>
            <p class = "further_info"> <?php
            echo wp_kses('For further information and assistance using the plugin and converting your models please visit', ar_allowed_html());
            
            ?> <a href = "https://augmentedrealityplugins.com" target = "_blank">https://augmentedrealityplugins.com</a></p>
        <?php } ?>
        </div>
        </div>
        <?php if ($ar_whitelabel!=true){ 
        $licence_result = ar_licence_check();
        if (substr($licence_result,0,5)!='Valid'){?>
            <div style="float:left;"><a href="https://augmentedrealityplugins.com" target="_blank"><img src="<?php echo esc_url( plugins_url( '../assets/images/ar_wordpress_ad.jpg', __FILE__ ) ); ?>" style="padding:10px 10px 10px 0px;"><img src="<?php echo esc_url( plugins_url( '../assets/images/ar_woocommerce_ad.jpg', __FILE__ ) ); ?>" style="padding:10px 10px 10px 0px;"></a></div>
        <?php } 
        }
        wp_enqueue_media();
        ?>
        <br clear="all">
        <script>
            jQuery(document).ready(function($){
            
            var custom_uploader;
            
            $('#ar_wl_file_button, #ar_view_file_button, #ar_qr_file_button, #ar_fullscreen_file_button, #ar_play_file_button, #ar_user_default_image_button, #ar_pause_file_button').click(function(e) {
                var button_clicked = event.target.id;
                var target = button_clicked.substr(0, button_clicked.length - 7);
                e.preventDefault();
            
                // Extend the wp.media object
                custom_uploader = wp.media.frames.file_frame = wp.media({
                    multiple: false
                });
            
                // When a file is selected, grab the URL and set it as the text field's value
                custom_uploader.on('select', function() {
                    var attachments = custom_uploader.state().get('selection').map(
                        function(attachment) {
                            attachment.toJSON();
                            return attachment;
                        }
                    );
            
                    $.each(attachments, function(index, attachment) {
                        var fileurl = attachments[index].attributes.url;
                        var filetype = fileurl.substring(fileurl.length - 4, fileurl.length).toLowerCase();
            
                        // Check if the clicked button is #ar_user_default_image_button and allow additional file types
                        if (button_clicked === 'ar_user_default_image_button') {
                            if (filetype === '.jpg' || filetype === '.png' || filetype === '.glb' || filetype === 'gltf') {
                                $('#' + target).val(fileurl);
                            } else {
                                <?php
                                $js_alert = wp_kses('Invalid file type. Please choose a JPG, PNG, GLB, or GLTF file.', ar_allowed_html());
                                ?>
                                alert('<?php echo esc_html($js_alert); ?>');
                            }
                        } else {
                            // For all other buttons, allow only JPG and PNG files
                            if (filetype === '.jpg' || filetype === '.png') {
                                $('#' + target).val(fileurl);
                            } else {
                                <?php
                                $js_alert = wp_kses('Invalid file type. Please choose a JPG or PNG file.', ar_allowed_html());
                                ?>
                                alert('<?php echo esc_html($js_alert); ?>');
                            }
                        }
                    });
                });
            
                // Open the uploader dialog
                custom_uploader.open();
            });
        });
        </script>
        <?php
    } 
}


/******* Model Count***********/
if (!function_exists('ar_model_count')){
    function ar_model_count(){
        global $wpdb, $ar_wp_active, $ar_wc_active;
        $wp_count = 0;
        $wc_count = 0;
        $model_count = 0;
        if ($ar_wc_active== true){
            // Set a unique cache key for the query result.
            $cache_key = 'ar_display_wc_count';
            $wc_count = wp_cache_get( $cache_key );
            
            if ( $wc_count === false ) {
                // If the cached result is not available, perform the query.
                $args = array(
                    'post_type'   => 'product', // Set post type as product
                    'post_status' => 'publish', // Only published products
                    'meta_query'  => array(
                        array(
                            'key'     => '_ar_display', // Meta key to match
                            'value'   => '1',           // Meta value to match
                            'compare' => '=',           // Comparison operator
                        ),
                    ),
                    'fields'      => 'ids', // Only return post IDs to reduce query load
                );
                
                $query = new WP_Query( $args );
                $wc_count = $query->found_posts;
            
                // Cache the result for 12 hours (43200 seconds).
                wp_cache_set( $cache_key, $wc_count, '', 43200 );
            }
            
            // Now you can use $wc_count which is retrieved from cache or from the query
        }
        if ($ar_wp_active == true){
            $wp_count = wp_count_posts( 'armodels' )->publish;
        }
        $model_count += $wp_count + $wc_count;
        return $model_count;
    }
}


/********** AR Licence Check **************/
if (!function_exists('ar_licence_check')){
    function ar_licence_check() {
        global $wpdb;
        $link = 'https://augmentedrealityplugins.com/ar/ar_subscription_licence_check.php';
        ob_start();
        $model_count = ar_model_count();
        $licence_key = get_option('ar_licence_key');
        if ($licence_key!=''){
            $data = array(
                'method'      => 'POST',
                'body'        => array(
                'domain_name' => site_url(),
                'licence_key' => get_option('ar_licence_key'),
                'model_count' => $model_count
            ));
            $response = wp_remote_post( $link, $data);
            if (!is_wp_error($response)){
                return $response['body'];
            }else{
                $curl_check = ar_curl($link.'?licence_key='.get_option('ar_licence_key').'&model_count='.$model_count);
                if ($curl_check){
                    return $curl_check;
                }else{
                    return 'error';
                }
            }
        }else{ //No Licence Key
            return 'error';
        }
        ob_flush();
    }
}

/************* Check Licence Cron *******************/
if (!function_exists('ar_cron')){
    function ar_cron() { 
        $licence_result = ar_licence_check();
        if (substr($licence_result,0,5)=='Valid'){
            if (substr($licence_result,6,7)=='Premium'){
                update_option( 'ar_licence_plan', 'Premium');
                update_option( 'ar_licence_renewal', substr($licence_result,-10));
                $licence_result='Valid';
            }else{
                update_option( 'ar_licence_plan', '');
            }
            update_option( 'ar_licence_valid', $licence_result);
        //}elseif($licence_result=='error'){
        //   echo '<div id="upgrade_ribbon" class="notice notice-error is-dismissible"><p>Issue connecting to licence server. Please refresh and try again.</p></div>';
        }else{
            update_option( 'ar_licence_plan', '');
            update_option( 'ar_licence_valid', '');
        }
    }
}



// Get the current version of the plugin dynamically
if (!function_exists('ar_get_plugin_version')){
    function ar_get_plugin_version() {
        global $ar_plugin_id;
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $plugin_folder = plugin_dir_path( __FILE__ );
        $folder_parts = explode(DIRECTORY_SEPARATOR, rtrim($plugin_folder, DIRECTORY_SEPARATOR));
        // Remove the last element ('includes')
        array_pop($folder_parts);
        $last_folder = end($folder_parts);
        $plugin_filename = str_replace('for-', '', $last_folder) . '.php';
        $plugin_file = plugin_dir_path( __FILE__ ) . '../' .$plugin_filename; // Replace with your plugin's main file name
        $plugin_data = get_plugin_data( $plugin_file );
        // Retrieve the plugin name and version
        $plugin_name = isset($plugin_data['Name']) ? $plugin_data['Name'] : 'Unknown Plugin';
        $plugin_version = isset($plugin_data['Version']) ? $plugin_data['Version'] : 'Unknown Version';
    
        // Return the plugin name and version as an array
        return array(
            'name'    => $plugin_name,
            'version' => $plugin_version
        );
    }
}

// Check and store the current plugin version
if (!function_exists('ar_check_plugin_version')){
    function ar_check_plugin_version() {
        $plugin_info = ar_get_plugin_version(); 
        $current_version = $plugin_info['version'];
        $saved_version = get_option('ar_plugin_version');
    
        if ($saved_version !== $current_version) {
            // If the plugin version is different, update the version and set a transient to show the notice
            update_option('ar_plugin_version', $current_version);
            set_transient('ar_show_update_notice', true, WEEK_IN_SECONDS); // Show for one week
        }
    }
    add_action('admin_init', 'ar_check_plugin_version');
}

/********** AR Upgrade to Premium Version Banner Ribbon **************/
if (!function_exists('ar_admin_notice_upgrade_banner')){
    function ar_admin_notice_upgrade_banner() {
        global $ar_whitelabel;
        $plugin_check = get_option('ar_licence_valid');
        if (($plugin_check!='Valid')AND($ar_whitelabel!=true)){
            ar_upgrade_banner(); 
        }
    }
    add_action( 'admin_notices', 'ar_admin_notice_upgrade_banner' );
}

if (!function_exists('ar_upgrade_banner')){
    function ar_upgrade_banner() { 
        global $ar_plugin_id; 
        
        ?>
        <style>
        #upgrade_premium {
            cursor: pointer;
            padding: 10px 12px;
            margin-left: -17px;
            font-style: normal !important;
            font-size: 20px;
            margin-right: 12px;
            color:#fff;
            font-weight: bold;
        }
        #upgrade_premium a{
            color:#fff;
            text-decoration:none; 
            font-size:16px;
            
        }
        #upgrade_premium_meta {
            color:#fff;
            text-decoration:none; 
            font-size:14px;
            font-weight: normal;
        }
        #upgrade_premium_meta a{
            color:#fff;
            text-decoration:none; 
            font-size:14px;
        }
        #upgrade_premium_button{
            padding-bottom:10px;
        }
        .ar_button_orange{
            background-color: #f37a23 !important;
            padding:20px
            margin:20px;
            color:#fff !important;
            border-color: #fff !important;
            font-weight: bold;
        }
        
        </style>
        
        <div id="ar_shortcode_instructions" class="notice notice-warning is-dismissible">
                    <div style="width:100%;background-color:#12383d">
                        <div class="ar_admin_view_title">
                      <?php  
                        if ($ar_plugin_id == 'ar-for-wordpress'){
                            $plugin_url = 'wordpress';
                        }else{
                            $plugin_url = 'woocommerce';
                        }
                        echo '<img src="'.esc_url( plugins_url( "assets/images/".$ar_plugin_id."-box.jpg", __FILE__ ) ).'" style="padding: 10px 30px 10px 10px; height:60px" align="left">
                        <h1 style="color:#ffffff; padding-top:20px">'.wp_kses('AR Display', ar_allowed_html()).'</h1>
                        </div>';
                        echo '<div id="upgrade_premium">'.wp_kses('Upgrade to Premium', ar_allowed_html());
                            echo '<span id="upgrade_premium_meta"> - '.wp_kses('Unlimited Models & Full Settings', ar_allowed_html()).'</span>';
                        echo '</div>
                            <div id="upgrade_premium_button">
                            <a href="https://augmentedrealityplugins.com/ar/'.esc_url($plugin_url).'/?ar_code=userUpgrade" target="_blank" class="button ar_button_orange">'.wp_kses('1st Month Free', ar_allowed_html()).'</a>.
                            </div>';
                        ?>
            </div>
        </div>
        <?php
       /* echo '<div id="upgrade_ribbon" class="notice notice-warning is-dismissible">
            
                <div id="upgrade_ribbon_top">
                    <div id="upgrade_ribbon_left">
                    </div>
                    <div id="upgrade_ribbon_base">
                        <span id="upgrade_premium"><a href="https://augmentedrealityplugins.com" target="_blank">';
                        ar_output('AR Display', 'ar-for-wordpress' , 'e');
                            
                        echo '</a></span>
                        <span id="upgrade_premium_meta"><a href="https://augmentedrealityplugins.com" target="_blank">';
                        ar_output('Upgrade to Premium - Unlimited Models & Full Settings For Only', 'ar-for-wordpress' , 'e');
                            
                        echo ' $20 per month!</a></span>
                    </div>
                    <div id="upgrade_ribbon_right">
                    </div>
                </div>
            </div>';*/
    }        
}    

// Display an admin notice if the plugin has been updated
if (!function_exists('ar_show_update_notice')){
    function ar_show_update_notice() {
        global $ar_plugin_id;
        // Check if the transient is set
        if (get_transient('ar_show_update_notice')) {
            $plugin_info = ar_get_plugin_version(); 
            $current_version = $plugin_info['version'];
            ?>
            <div class="notice notice-success is-dismissible ar-update-notice">
                <h3><?php echo esc_html($plugin_info['name']). esc_html( ar_output(' has been updated to version ', $ar_plugin_id)) . esc_html($plugin_info['version']) . wp_kses('! Check out the new features and improvements.', ar_allowed_html()); ?></h3>
            <?php 
            $limit = 1;
            $icons_only = 1;
            echo wp_kses(ar_changelog_retrieve($limit, $icons_only), ar_allowed_html());?>
            <p></p>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $('.ar-update-notice').on('click', '.notice-dismiss', function() {
                        // Make an AJAX request to mark the notice as dismissed
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'ar_dismiss_update_notice'
                            }
                        });
                    });
                });
            </script>
            <?php
        }
    }
    add_action('admin_notices', 'ar_show_update_notice');
}


// Handle the AJAX request to dismiss the update notice
if (!function_exists('ar_dismiss_update_notice')){
    function ar_dismiss_update_notice() {
        // Delete the transient to stop showing the notice
        delete_transient('ar_show_update_notice');
        wp_die(); // Required to end AJAX request gracefully
    }
    add_action('wp_ajax_ar_dismiss_update_notice', 'ar_dismiss_update_notice');
}


/************* AR Custom column *************/
if (!function_exists('ar_advance_custom_armodels_column')){
    function ar_advance_custom_armodels_column( $column, $post_id ) {
        global $ar_plugin_id;
        $get_model_check = get_post_meta($post_id, '_usdz_file', true);
        if(empty($get_model_check)){
          $get_model_check = get_post_meta($post_id, '_glb_file', true);
        }
        if(!empty($get_model_check)){
            switch ( $column ) { 
                case 'Shortcode' :
                    
                    echo '<input id="ar_shortcode_'.esc_html($post_id).'" type="text" value="[ar-display id='.esc_html($post_id).']" readonly style="width:150px" onclick="copyToClipboard(\'ar_shortcode_'.esc_html($post_id).'\');document.getElementById(\'copied_'.esc_html($post_id).'\').innerHTML=\'&nbsp;Copied!\';"><span id="copied_'.esc_html($post_id).'"></span>';
                    break;
                case 'thumbs' :
                    $ARimgSrc = plugins_url( "../assets/images/chair.png", __FILE__ );
                    $product_link = admin_url( 'post.php?post=' . $post_id ) . '&action=edit#ar_woo_advance_custom_attachment"';
                    echo '<a href="'.esc_url($product_link).'"><div class="ar_tooltip"><img src="'.esc_url($ARimgSrc).'" width="20"></div></a>';
                    break;
            }   
        }
    }
}


/********** list of 'armodels' posts **************/
if (!function_exists('display_armodels_posts')){

    // Function to display the list of published 'armodels' posts and WooCommerce products with _ar_display meta key
    function display_armodels_posts() {
        global $ar_plugin_id, $ar_wc_active, $ar_wp_active;
        
        if ($ar_wp_active === true) {
            // Set cache key for 'armodels' posts
            $cache_key_armodels = 'ar_models_posts';
            $armodels = wp_cache_get($cache_key_armodels);
        
            if ($armodels === false) {
                // Query to get published posts of type 'armodels', sorted by ID ascending
                $args_armodels = array(
                    'post_type'      => 'armodels',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'orderby'        => 'ID',
                    'order'          => 'ASC',
                    'fields'         => 'ids', // Only retrieve IDs to improve performance
                );
                $armodels = get_posts($args_armodels);
        
                // Cache the result
                wp_cache_set($cache_key_armodels, $armodels, '', 43200); // Cache for 12 hours
            }
        }
        
        if ($ar_wc_active === true) {
            // Set cache key for WooCommerce products
            $cache_key_products = 'ar_wc_products';
            $products = wp_cache_get($cache_key_products);
        
            if ($products === false) {
                // Query to get WooCommerce products with meta key _ar_display
                $args_products = array(
                    'post_type'      => 'product',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'orderby'        => 'ID',
                    'order'          => 'ASC',
                    'meta_query'     => array(
                        array(
                            'key'     => '_ar_display',
                            'compare' => 'EXISTS',
                        ),
                    ),
                    'fields'         => 'ids', // Only retrieve IDs to improve performance
                );
                $products = get_posts($args_products);
        
                // Cache the result
                wp_cache_set($cache_key_products, $products, '', 43200); // Cache for 12 hours
            }
        }
        
        // Display the list of posts and products
        echo '<ul>';
        
        if ($ar_wp_active === true && !empty($armodels)) {
            foreach ($armodels as $post_id) {
                $post_title = get_the_title($post_id);
        
                // Display post ID and title with a delete link, using proper escaping
                echo '<li><b>Model:</b> <a href="' . esc_url('post.php?post=' . esc_html($post_id) . '&action=edit') . '">' . esc_html($post_id) . ' - ' . esc_html($post_title) . '</a> 
                <a href="' . esc_url(add_query_arg('delete_post_id', $post_id)) . '" onclick="return confirm(\'Are you sure you want to delete this AR Model?\');">
                <img src="' . esc_url(plugins_url("../assets/images/delete.png", __FILE__)) . '" style="width: 15px;vertical-align: middle;cursor:pointer"></a></li>';
            }
        }
        
        if ($ar_wc_active === true && !empty($products)) {
            foreach ($products as $post_id) {
                $post_title = get_the_title($post_id);
        
                // Display product ID and title with a delete link
                echo '<li><b>Product:</b> <a href="' . esc_url('post.php?post=' . esc_html($post_id) . '&action=edit') . '">' . esc_html($post_id) . ' - ' . esc_html($post_title) . '</a> 
                <a href="' . esc_url(add_query_arg('delete_post_id', $post_id)) . '" onclick="return confirm(\'Are you sure you want to delete AR Model from this Product?\');">
                <img src="' . esc_url(plugins_url("../assets/images/delete.png", __FILE__)) . '" style="width: 15px;vertical-align: middle;cursor:pointer"></a></li>';
            }
        }
        
        echo '</ul>';
        
        // Reset post data to prevent conflicts later on
        wp_reset_postdata();
    }
}

/********** Whats New Page **********/
if (!function_exists('ar_whats_new')){
    function ar_whats_new() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'ar_secure_nonce' ) ) {
            // If the nonce is invalid, stop the process
          //  wp_die( __( 'Security check failed.', 'ar-for-wordpress' ) );
        }
        global $ar_version, $ar_plugin_id, $woocommerce_featured_image, $ar_whitelabel;
        $ar_logo = esc_url( plugins_url( '../assets/images/Ar_logo.png', __FILE__ ) ); 
        $ar_wl_logo = get_option('ar_wl_file'); 
        ?>
        <div class="licence_key" id="key" style="float:left;">
        <?php 
        if ($ar_whitelabel!=true){ ?>   
            <div class="ar_site_logo">
                <a href = "https://augmentedrealityplugins.com" target = "_blank">              
                <img src="<?php echo esc_html($ar_logo);?>" style="width:300px; padding:0px;float:left" />
                </a>
            </div>
            <br clear="all">
            <?php
            if ($ar_plugin_id=='ar-for-wordpress'){
                echo '<h1>'.wp_kses('AR For WordPress', ar_allowed_html()).' - '.wp_kses('What\'s New', ar_allowed_html()).'</h1>';
                    
            }elseif ($ar_plugin_id=='ar-for-woocommerce'){
                echo '<h1>'.wp_kses('AR For Woocommerce', ar_allowed_html()).' - '.wp_kses('What\'s New', ar_allowed_html()).'</h1>';
                
            }
            ?>
        <?php }else{
        //White Label Logo 
        ?>
        <div>
            <?php 
            
            if ($ar_wl_logo){
            ?>
                <div class="ar_site_logo">
                    <img src="<?php echo esc_html($ar_wl_logo);?>" style="max-width:300px; padding:0px;float:left" />
                    <input type="hidden" name="ar_wl_file" id="ar_wl_file" class="regular-text" value="<?php echo esc_html($ar_wl_logo); ?>">
                </div>
                <br clear="all">
            <?php }
             ?>
            </div>
            <br  clear="all">
        <?php }?>
        <div id="licence_page" class="licence_page" style="min-width:400px;>
        <br clear="all">
        <?php
        $limit=10;
        echo wp_kses(ar_changelog_retrieve($limit), ar_allowed_html());
        
        ?>
        </div>
        <?php
        if (isset($_REQUEST['tab'])){
            if ($_REQUEST['tab']=='ar_display'){
                echo esc_html($woocommerce_featured_image);
            }
        }
        ?>
        
        <?php if ($ar_whitelabel!=true){ ?>
        <hr>
        <p class = "further_info"> <?php
            echo wp_kses('For further information and assistance using the plugin and converting your models please visit', ar_allowed_html());
            
            ?> <a href = "https://augmentedrealityplugins.com" target = "_blank">https://augmentedrealityplugins.com</a></p>
        <?php } ?>
        </div>
        <?php if ($ar_whitelabel!=true){ 
            $licence_result = ar_licence_check();
        if (substr($licence_result,0,5)!='Valid'){
        ?>
            <div style="float:left;"><a href="https://augmentedrealityplugins.com" target="_blank"><img src="<?php echo esc_url( plugins_url( '../assets/images/ar_wordpress_ad.jpg', __FILE__ ) ); ?>" style="padding:10px 10px 10px 0px;"><img src="<?php echo esc_url( plugins_url( '../assets/images/ar_woocommerce_ad.jpg', __FILE__ ) ); ?>" style="padding:10px 10px 10px 0px;"></a></div>
        <?php } 
        }
    }
}

/********** Whats New Change Log **********/
if (!function_exists('ar_changelog_retrieve')){
    function ar_changelog_retrieve($limit, $icons_only = '') {
        global $ar_plugin_id;
        $ar_readme= ar_curl(esc_url( plugins_url( '../readme.txt', __FILE__ ) ));
        $ar_changelog_pos = strpos($ar_readme, '== Changelog ==')+15;
        $output='';
        if ($ar_changelog_pos>15){
            $ar_changelog = substr($ar_readme, $ar_changelog_pos);
            $ar_changelog_array = array_filter(explode('=',$ar_changelog));
            if (isset($limit)){
                $ar_changelog_array = array_splice($ar_changelog_array, 0,($limit *2)+1);
            }
            $ar_highlight = false;
            $count=0;
            $output ='';
            foreach ($ar_changelog_array as $k => $v){
                if (strpos($v,'*')>=1){
                    $ar_highlight_style ='';
                    if ($ar_highlight == true){
                        $ar_highlight_style = 'font-weight:bold;font-size:16px';
                    }
                    $output .= '<ul style="list-style:disc;">';
                    $v = explode('*',$v);
                    $v = implode('<li style="margin-left:40px; '.$ar_highlight_style.'">',$v);
                    $output .= $v.'</ul>';
                    $count ++;
                }else{
                    if ($icons_only == ''){
                        if ($count == 0){
                            $output .= '<h2>'.$v.'</h2>';
                            $ar_highlight = true;
                        }else{
                            $output .= '<h3>'.$v.'</h3>';
                            $ar_highlight = false;
                        }
                    }
                }
            }
        }
        if ($ar_plugin_id=='ar-for-wordpress'){
            $output .= '<a href="https://augmentedrealityplugins.com/support/whats-new/" target="_blank">'.wp_kses('Please visit Augmented Reality Plugins to view the full change log.', ar_allowed_html()).'</a>';
        }elseif ($ar_plugin_id=='ar-for-woocommerce'){
            $output .= '<a href="https://augmentedrealityplugins.com/support/whats-new/" target="_blank">'.wp_kses('Please visit Augmented Reality Plugins to view the full change log.', ar_allowed_html()).'</a>';
        }
        return $output;
    }
}


