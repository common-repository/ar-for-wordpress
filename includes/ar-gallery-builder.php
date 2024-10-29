<?php
/**
 * AR Display
 * https://augmentedrealityplugins.com
**/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div id="asset_builder">
                        
    <div class="asset_builder_img" style="max-width:50%;" onclick="toggleMaxWidth(this)">
        <img src="<?php echo esc_url(plugins_url('assets/images/wall_art_guide.jpg', dirname(__FILE__))); ?>" style="max-width:100%; max-height:200px;">
    </div>
    
    <script>
        function toggleMaxWidth(element) {
            var imgElement = element.querySelector('img');
            if (element.style.maxWidth === '100%') {
                element.style.maxWidth = '50%';
                imgElement.style.maxHeight = '200px';
            } else {
                element.style.maxWidth = '100%';
                imgElement.style.maxHeight = null;
            }
        }
    </script>
    <div id="asset_builder_top_content" style="padding:6px 10px;">   
    <?php global $ar_plugin_id; 
    $asset_image = plugins_url( "assets/images/ar_asset_icon.jpg", dirname(__FILE__) );
    if (get_post_meta( $model_array['id'], '_glb_file', true )!=''){
        $glb_file = sanitize_text_field(get_post_meta( $model_array['id'], '_glb_file', true ));
        
        // Parse the URL to get its components
        $url_components = wp_parse_url($glb_file);
        if (isset($url_components['query'])){
        // Extract the query string (the part after the ? in the URL)
        $query_string = $url_components['query'];
        
        // Parse the query string into an associative array
        parse_str($query_string, $query_parts);
        
        // Now $query_parts will contain the parts of the URL as an associative array
        $url = $query_parts['url']; // Extracts the 'url' part
        $ratio = $query_parts['ratio']; // Extracts the 'ratio' part
        $orientation = $query_parts['o']; // Extracts the 'o' part
        $asset_image = $url;
        }
    }
    
    $nodisplay = ' class=""';
    for($i = 0; $i<1; $i++) { //Previously 10 - Cube will require 6
    if ($i>0){$nodisplay = ' class="nodisplay"';}
    ?>
       <div  id="texture_container_<?php echo esc_html($i)?>" <?php echo esc_html($nodisplay);?> style="padding: 0px 20px 10px 0px; float: left;">
         <p><strong><?php echo wp_kses('JPG/PNG Image', ar_allowed_html());?></strong> <span id="ar_asset_builder_texture_done"></span><br>
        <img src="<?php echo esc_url( $asset_image ); ?>" id="asset_thumb_img" style="max-heigth:200px"  class="ar_file_icons" onclick="document.getElementById('upload_asset_texture_button_<?php echo esc_html($i); ?>').click();">
        <span id="texture_<?php echo esc_html($i)?>">
        <input type="hidden" name="_asset_texture_file_<?php echo esc_html($i); ?>" id="_asset_texture_file_<?php echo esc_html($i); ?>" class="regular-text" value="<?php if (isset($url)){echo esc_url($url);}?>"> <input id="upload_asset_texture_button_<?php echo esc_html($i); ?>" class="upload_asset_texture_button_<?php echo esc_html($i); ?> upload_asset_texture_button button nodisplay" type="button" value="<?php echo wp_kses('Upload', ar_allowed_html());?>" /> <img src="<?php echo esc_url( plugins_url( "assets/images/delete.png", dirname(__FILE__) ) );?>" style="width: 15px;vertical-align: middle;cursor:pointer" onclick="document.getElementById('_asset_texture_file_<?php echo esc_html($i); ?>').value = '';document.getElementById('ar_asset_builder_texture_done').innerHTML = '';document.getElementById('asset_thumb_img').src = '<?php echo esc_url( plugins_url( "assets/images/ar_asset_ad_icon.jpg", dirname(__FILE__) ) ); ?>';">
        <input type="text" name="_asset_texture_id_<?php echo esc_html($i); ?>" id="_asset_texture_id_<?php echo esc_html($i); ?>" class="nodisplay"></span></p>
        
        </div>
    
    <?php }
    ?><input type="text" name="_asset_texture_flip" id="_asset_texture_flip" class="nodisplay">

    
    
	<input type="hidden" name="_ar_asset_file" id="_ar_asset_file" class="regular-text" value="">
	<input type="hidden" name="_ar_asset_id" id="_ar_asset_id" class="regular-text" value="<?php echo esc_html($model_array['id']); ?>">
	<input type="hidden" name="_ar_asset_url" id="_ar_asset_url" class="regular-text" value="<?php echo esc_url(site_url('/wp-content/plugins/'.$ar_plugin_id.'/includes/ar-gallery.php'));?>">
    <input type="hidden" name="_ar_asset_ratio" id="_ar_asset_ratio" value="<?php if (isset($ratio)){echo esc_html($ratio); } ?>">
    

    <div style="min-height:100px;padding-top:10px;float: left;">
     <div id="ar_asset_size_container" <?php if (!isset($ratio)){echo ' style="display:none;"'; } ?>>
          <div style="float:left;padding:5px;display:none">
              <strong><?php echo wp_kses( 'Orientation', ar_allowed_html());?></strong><br>
              <select name="_ar_asset_orientation" id="_ar_asset_orientation">
                <option value="portrait" <?php echo (isset($orientation) && $orientation == 'portrait') ? 'selected' : ''; ?>>Portrait</option>
                <option value="landscape" <?php echo (isset($orientation) && $orientation == 'landscape') ? 'selected' : ''; ?>>Landscape</option>
            </select>
          </div>
         <div style="float:left;padding:5px">
             <strong><?php echo wp_kses('Image Ratio', ar_allowed_html());?></strong><br>
              <?php
                $ratio = isset($query_parts['ratio']) ? $query_parts['ratio'] : null;
                
                // If $ratio is set to '1', set it to '1.0'
                if ($ratio == '1') {
                    $ratio = '1.0';
                }
                
                // Define the array of options
                $ratio_options = array(
                    '1.0'     => '1:1',
                    '1.4142'  => 'A4-A1',
                    '1.5'     => '2:3',
                    '1.25'    => '4:5',
                    '1.33'    => '3:4'
                );
                
                ?>
                
                <select id="_ar_asset_ratio_select">
                <?php
                // Loop through the array and generate the <option> elements
                foreach ($ratio_options as $value => $label) {
                    // Check if the current value matches the selected ratio
                    $selected = ($value == $ratio) ? ' selected' : '';
                    echo "<option id='ar_asset_ratio_options' value='".esc_html($value),"'".esc_html($selected).">".esc_html($label)."</option>";
                }
                ?>
                </select>
              
          </div>
          <div style="float:left;padding:5px">
              <strong><?php echo wp_kses( 'Print Size', ar_allowed_html());?></strong><br>
              <select id="ar_asset_size">
                    <option  id="ar_asset_size_options" value="-1" selected="selected">Select your Asset Below First</option>
              </select>
          </div>
          <br clear="all">
          <div style="float:left;padding:5px">
              <strong><?php echo wp_kses( 'Framed', ar_allowed_html());?></strong><br>
              <?php $frame_type= get_post_meta( $model_array['id'], '_ar_framed', true ); ?>
              <select id="_ar_framed" name="_ar_framed" >
                  <option value="0">None</option>
                  <option <?php if ($frame_type == '1'){ echo ' selected';} ?> value="1">Mounted</option>
                  <option <?php if ($frame_type == '2'){ echo ' selected';} ?> value="2">Framed</option>
              </select>
             </div>
          <div style="float:left;padding:5px">
              <strong><?php echo wp_kses( 'Color', ar_allowed_html());?></strong><br>
              
        <?php $ar_frame_color = get_post_meta( $model_array['id'], '_ar_frame_color', true );?>
    	<input id="_ar_frame_color" name="_ar_frame_color" type="text" value="<?php echo esc_html($ar_frame_color); ?>" <?php echo esc_html($disabled);?>>
    	
        <input id="_ar_wpnonce" name="_ar_wpnonce" type="hidden" value="<?php  echo esc_html(wp_create_nonce( 'ar_secure_nonce' )); ?>">
       </div>
         <div style="float:left;padding:5px">
              <strong><?php echo wp_kses( 'Opacity', ar_allowed_html());?></strong><br>
              <?php $ar_frame_opacity = get_post_meta( $model_array['id'], '_ar_frame_opacity', true ); ?>
                <select id="_ar_frame_opacity" name="_ar_frame_opacity">
                    <?php
                    for ($i = 1; $i >= 0; $i -= 0.1) {
                        $selected = ($ar_frame_opacity == number_format($i, 1)) ? 'selected="selected"' : '';
                        echo '<option value="' . esc_attr(number_format($i, 1)) . '" ' . esc_html($selected) . '>' . esc_html(number_format($i, 1)) . '</option>' . PHP_EOL;
                    }
                    ?>
                </select>
          </div>
          <br clear="all">
      </div>
        
        <span id="ar_asset_builder_submit_container" style="display:none;">
            <br clear="all"><!--<br>
            <button id = "ar_asset_builder_submit" class="button ar_admin_button" >Build Asset</button>-->
            <strong><span style="color:#f37a23"><?php echo wp_kses( 'Please Publish/Update your post to build the Gallery Asset. You may need to refresh your browser once updated to ensure the latest files are displayed.', ar_allowed_html());?></span></strong>
            <br><br>
            
        </span>
        </div>
    </div>
</div>
