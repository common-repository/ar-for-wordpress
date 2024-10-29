            <!-- Tab links -->
                <?php 
                

                global $ar_plugin_id;
    	        $redirect_url = admin_url('admin.php?page=ar-modelshop');
                $encoded_redirect_url = urlencode($redirect_url);
                $gallery_active = 'display:none;';
                $gallery_tab= '';
                $files_active = 'display:block;';
                $files_tab= 'active';
                if (get_post_meta( $model_array['id'], '_glb_file', true )!=''){
                    $glb_upload_image = esc_url( plugins_url( "assets/images/ar_model_icon_tick.jpg", dirname(__FILE__) ) ); 
                    $path_parts = pathinfo(sanitize_text_field( get_post_meta( $model_array['id'], '_glb_file', true ) ));
                    $glb_filename = $path_parts['basename'];
                }else{
                    $glb_upload_image = esc_url( plugins_url( "assets/images/ar_model_icon.jpg", dirname(__FILE__) ) ); 
                    $glb_filename = '';
                }
                if (get_post_meta( $model_array['id'], '_usdz_file', true )!=''){
                    $usdz_upload_image = esc_url( plugins_url( "assets/images/ar_model_icon_tick.jpg", dirname(__FILE__) ) );
                    $path_parts = pathinfo(sanitize_text_field( get_post_meta( $model_array['id'], '_usdz_file', true ) ));
                    $usdz_filename = $path_parts['basename'];
                }else{
                    $usdz_upload_image = esc_url( plugins_url( "assets/images/ar_model_icon.jpg", dirname(__FILE__) ) ); 
                    $usdz_filename = '';
                }
                
                if (strpos($glb_filename, '&ratio=') !== false && strpos($glb_filename, '&o=') !== false) {
                    $glb_filename = '3D Gallery Build';
                    $gallery_active = 'display:block;';
                    $files_active = 'display:none;';
                    $gallery_tab= 'active';
                    $files_tab= '';
                }
                ?>
                <div class="ar_tab">
                  <button class="ar_tablinks <?php echo esc_html($files_tab); ?>" onclick="ar_open_tab(event, 'model_files_content', 'model_files_tab')" id="model_files_tab" type="button"><?php echo esc_html(__( 'Model Files', 'ar-for-wordpress'));?><span style=" vertical-align: super;font-size: smaller;"> </span></button>
                  <button class="ar_tablinks <?php echo esc_html($gallery_tab); ?>" onclick="ar_open_tab(event, 'asset_builder_content', 'asset_builder_tab')" id="asset_builder_tab" type="button"><?php echo esc_html(__( '3D Gallery Builder', 'ar-for-wordpress'));?><span style=" vertical-align: super;font-size: smaller;"> </span></button>
                   <button class="ar_tablinks" onclick="ar_open_tab(event, 'user_upload_content', 'user_upload_tab')" id="user_upload_tab" type="button"><?php echo esc_html(__('User Upload', 'ar-for-wordpress'));?><span style=" vertical-align: super;font-size: smaller;"> </span></button>
                   <button class="ar_tablinks" onclick="ar_open_tab(event, 'instructions_content','instructions_tab')" id="instructions_tab" type="button"><?php echo esc_html(__( 'Shortcodes', 'ar-for-wordpress'));?><span style=" vertical-align: super;font-size: smaller;"> </span></button>
                  <a href="https://armodelshop.com?from_plugin=true&redirect_url=<?php echo esc_url($encoded_redirect_url); ?>" target="_blank"><button class="ar_tablinks" id="support_tab" type="button"> <?php echo esc_html(__( 'AR Model Shop', 'ar-for-wordpress'));?><span style=" vertical-align: super;font-size: smaller;">&#8599;</span></button></a>
                <a href="https://augmentedrealityplugins.com/support/" target="_blank"><button class="ar_tablinks" id="support_tab" type="button"> <?php echo esc_html(__( 'Support','ar-for-wordpress'));?><span style=" vertical-align: super;font-size: smaller;">&#8599;</span></button></a>
                </div>
                
                        
                <div id="model_files_content" class="ar_tabcontent" style="<?php echo esc_html($files_active); ?>">
                <a href="#" id="toggle-model-fields" data-status='hidden'>Show Model Fields</a>
                <br><br>
                <div>
                	<div class="ar_model_files_advert hide_on_devices">
                	    <center>
                	        <img src="<?php echo esc_url( plugins_url( "assets/images/ar_asset_ad_icon.jpg", dirname(__FILE__) ) ); ?>" style="height:60px">
                    	    <h4><?php echo esc_html(__( 'Hang your artwork in AR with just a photo!', 'ar-for-wordpress'));?></h4>
                    	    <button type="button" id="asset_builder_button" onclick="ar_open_tab(event, 'asset_builder_content', 'asset_builder_tab');/*ar_activeclass('asset_builder_tab');*/" class="button ar_admin_button" style="margin-right:20px"><?php echo esc_html(__( '3D Gallery Builder', 'ar-for-wordpress'));?></button>
                	        <!---<p><a href="https://wordpress.org/support/plugin/ar-for-wordpress/reviews/#new-post" target="_blank">Rate this plugin!</a> <a href="https://wordpress.org/support/plugin/ar-for-wordpress/reviews/#new-post" target="_blank"><img src="<?php echo esc_url( plugins_url( "assets/images/5-stars.png", dirname(__FILE__) ) );?>" style="width: 45px;vertical-align: middle;"></a></p>-->
                	    </center>
                	</div>
                	<div class="ar_model_shop_advert hide_on_devices">
                	    <center>
                	        <a href = "https://armodelshop.com?from_plugin=true&redirect_url=<?php echo esc_url($encoded_redirect_url); ?>" target="_blank"><img src = "<?php echo esc_url( plugins_url( "assets/images/ar-model-shop-icon.png", dirname(__FILE__) ) ); ?>" style="width:60px"></a>
                	        <h4><?php echo esc_html(__( 'Purchase an', 'ar-for-wordpress')).' <b>AR Model</b> '.esc_html(__( 'for your site.', 'ar-for-wordpress')); ?></h4>
                	        <button 
                                id="open-ar-modelshop" 
                                class="button ar_admin_button" 
                                onclick="event.preventDefault(); window.open('https://armodelshop.com?from_plugin=true&redirect_url=<?php echo esc_url($encoded_redirect_url); ?>', '_blank')">
                                Open AR Model Shop <sup>&#x2197;</sup>
                            </button>
                	    </center>
                	</div>
                    
                    <div class="ar_model_files_fields">
                        <div style="width:48%; float:left;padding-right:10px; position:relative;">
                            
                            <center>
                            <strong><?php echo esc_html(__(  'GLTF/GLB 3D Model', 'ar-for-wordpress'));?></strong> <br><br>
                            <img src="<?php echo esc_html($glb_upload_image);?>" id="glb_thumb_img" class="ar_file_icons" onclick="document.getElementById('upload_glb_button').click();document.getElementById('glb_thumb_img').src = '<?php echo esc_url( plugins_url( "assets/images/ar_model_icon_tick.jpg", dirname(__FILE__) ) ); ?>';">
                             <a href="#" onclick="document.getElementById('_glb_file').value = '';document.getElementById('glb_filename').innerHTML = '';document.getElementById('glb_thumb_img').src = '<?php echo esc_url( plugins_url( "assets/images/ar_model_icon.jpg", dirname(__FILE__) ) ); ?>';"><img src="<?php echo esc_url( plugins_url( "assets/images/delete.png", dirname(__FILE__) ) );?>" style="width: 15px;vertical-align: middle;"></a>
    
                             
    
                            <br clear="all"><br><span id="glb_filename" class="ar_filenames"><?php echo esc_html($glb_filename);?></span>
                            <div align="center">                            
                                <input type="hidden" pattern="https?://.+" title="<?php echo esc_html(__( 'Secure URLs only', 'ar-for-wordpress')); ?> https://" placeholder="https://" name="_glb_file" id="_glb_file" class="regular-text ar_input_field" value="<?php echo esc_html(get_post_meta( $model_array['id'], '_glb_file', true ));?>"> 
                                <input id="upload_glb_button" class="button nodisplay upload_glb_button" type="button" value="<?php echo esc_html(__(  'Upload', 'ar-for-wordpress'));?>" />
                            </div>
                            <input type="hidden" id="uploader_modelid" value="">
                            </center>
                        </div>
                        <div style="width:48%; float:left;">
                            <center>
                        	<strong><?php echo esc_html(__( 'USDZ/REALITY 3D Model', 'ar-for-wordpress'));?> - <span class="ar_label_tip"><?php echo esc_html(__( 'Optional', 'ar-for-wordpress'));?></span></strong><br><br>
                        	<img src="<?php echo esc_url($usdz_upload_image);?>" id="usdz_thumb_img"  class="ar_file_icons" onclick="document.getElementById('upload_usdz_button').click();document.getElementById('usdz_thumb_img').src = '<?php echo esc_url( plugins_url( "assets/images/ar_model_icon_tick.jpg", dirname(__FILE__) ) ); ?>';">
                            <a href="#" onclick="document.getElementById('_usdz_file').value = '';document.getElementById('usdz_filename').innerHTML = '';document.getElementById('usdz_thumb_img').src = '<?php echo esc_url( plugins_url( "assets/images/ar_model_icon.jpg", dirname(__FILE__) ) ); ?>';"><img src="<?php echo esc_url( plugins_url( "assets/images/delete.png", dirname(__FILE__) ) );?>" style="width: 15px;vertical-align: middle;"></a>
                            <br clear="all"><br><span id="usdz_filename" class="ar_filenames"><?php echo esc_html($usdz_filename);?></span>
                            <div align="center">                            
                                <input type="hidden" pattern="https?://.+" title="<?php echo esc_html(__('Secure URLs only', 'ar-for-wordpress')); ?> https://" placeholder="https://" name="_usdz_file" id="_usdz_file" class="regular-text ar_input_field" value="<?php echo esc_html(get_post_meta( $model_array['id'], '_usdz_file', true ));?>"> 
                                <input id="upload_usdz_button" class="button upload_usdz_button nodisplay" type="button" value="<?php echo esc_html(__( 'Upload', 'ar-for-wordpress'));?>" />
                            </div>
                            </center>
                        </div>
                    </div>
                    <div style="clear:both"></div><?php 
                    if($plan_check!='Premium') { 
                		    $premium_only = '<b> - '.esc_html(__('Premium Plans Only', 'ar-for-wordpress')).'</b>'; 
                		    $disabled = ' disabled';
                		    $readonly = ['readonly' => 'readonly'];
                		    $custom_attributes = $readonly;
                		    echo '<div style="pointer-events: none;">'; //disable mouse clicking 
                		}else{
                		    $disabled = '';
                		    $readonly = '';
                		    $premium_only = '';
                		    //Used for Scale inputs
                		    $custom_attributes = array(
                                'step' => '0.1',
                                'min' => '0.1');
                		}
                		?>
                    </div>
            	</div>
            	<?php /* Asset Builder */ ?>
            	<div id="asset_builder_content" class="ar_tabcontent" style="padding:0px;<?php echo esc_html($gallery_active); ?>">
                     <?php
                     /******** AR Model File Handling************/
                    require_once(plugin_dir_path(__FILE__). '../includes/ar-gallery-builder.php');
                     ?>
                </div>
                <?php 
                $support_links = '';
                if (!$ar_whitelabel){
        		    $support_links = '<br><a href="admin.php?page=wc-settings&tab=ar_display">'.esc_html(__('AR Display Settings', 'ar-for-wordpress')).'</a> | <a href="https://augmentedrealityplugins.com/support/" target="_blank">'.esc_html(__('Documentation', 'ar-for-wordpress')).'</a> | <a href="https://augmentedrealityplugins.com/support/3d-model-resources/" target="_blank">'.esc_html(__('Sample 3D Models', 'ar-for-wordpress')).'</a> | <a href="https://augmentedrealityplugins.com/support/3d-model-resources/#hdr" target="_blank">'.esc_html(__('Sample HDR Images', 'ar-for-wordpress')).'</a> ';
        		}
        		/* User Upload */ ?>
            	<div id="user_upload_content" class="ar_tabcontent ar_tabcontent" style="padding:20px;">
		            <h3><?php echo esc_html(__( 'Allow user to upload their own image or model file?', 'ar-for-wordpress'));?></h3>
            	<br><?php echo esc_html(__( 'Using the [ar-user-upload] shortcode displays an Image/Model upload button and an empty model viewer. Model files (gltf & glb) will be displayed in the model viewer and image files (jpg & png) will be converted to hanging artworks with the 3D Gallery Builder.', 'ar-for-wordpress'));?>
            	<br>
            	<?php echo wp_kses($support_links, ar_allowed_html()); ?>
            	</div>
                <?php /* Instructions */ ?>
            	<div id="instructions_content" class="ar_tabcontent">
                        <p>                		    
        		        <?php echo wp_kses($shortcode_examples, ar_allowed_html());
        		        //echo '<p>'.ar_output( 'Models can be uploaded as a GLB or GLTF file for viewing in AR and within the broswer display. You can also upload a USDZ or REALITY file for iOS, otherwise a USDZ file is generated on the fly. The following formats can be uploaded and will be automatically converted to GLB format - DAE, DXF, 3DS, OBJ, PDF, PLY, STL, or Zipped versions of these files. Model conversion accuracy cannot be guaranteed, please check your model carefully.', $ar_plugin_id );
                        if (!$ar_whitelabel){
                		    echo '<p><a href="https://augmentedrealityplugins.com/support/" target="_blank">'.esc_html(__('Documentation', 'ar-for-wordpress')).'</a> | <a href="https://augmentedrealityplugins.com/support/3d-model-resources/" target="_blank">'.esc_html(__('Sample 3D Models', 'ar-for-wordpress')).'</a> | <a href="https://augmentedrealityplugins.com/support/3d-model-resources/#hdr" target="_blank">'.esc_html(__('Sample HDR Images', 'ar-for-wordpress')).'</a> ';
                		}
                		?>
                		</p>
                </div>
                <?php
                //if ($public == 'y'){
                    echo '</div>';
                //} 
                
                $ar_open_tabs=get_option('ar_open_tabs'); 
                $ar_open_tabs_array = explode(',',$ar_open_tabs);
                $jsArray = wp_json_encode($ar_open_tabs_array);
                //print_r($jsArray);
                //die();
                ?>