<?php
/**
 * AR Display
 * https://augmentedrealityplugins.com
**/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/************* AR User Upload output *******************/
if (!function_exists('ar_user_upload_wp')){
    function ar_user_upload_wp($atts) {
        global $ar_plugin_id, $post;
        
        add_action('wp_enqueue_scripts', 'ar_advance_register_style');
        $output_html ='';
        $output_atts ='';
        $input_field = '';
        $ar_user_upload_button_hidden = '';
        
        $ar_user_default = get_option('ar_user_default');
        $ar_user_default_image = get_option('ar_user_default_image');
        $ar_user_button_location = get_option('ar_user_button');
        $gallery_url = site_url('/wp-content/plugins/' . $ar_plugin_id . '/includes/ar-gallery.php?url=');
        $glb_url = $gallery_url.site_url('/wp-content/plugins/' . $ar_plugin_id . '/assets/images/drag-drop-upload.jpg&width=760&height=420&_wpnonce='.esc_html(wp_create_nonce( 'ar_secure_nonce' )));
        if (($ar_user_default=='Custom')AND($ar_user_default_image!='')){
            // Get the user default image URL with width and height
            $user_default_image_width = 760; // Default width if no image
            $user_default_image_height = 420; // Default height if no image
            if ($ar_user_default_image != '') {
                $upload_dir = wp_upload_dir(); // Get the WordPress upload directory
                // Check if $ar_user_default_image is a full URL and belongs to the current site
                $site_url = site_url(); // Get the current site URL
                
                $ar_user_default_image_path ='';
                // Ensure that $ar_user_default_image is a relative path, not a full URL
                if (strpos($ar_user_default_image, $upload_dir['baseurl']) !== false) {
                    // If $ar_user_default_image contains the full URL, remove the base URL
                    $ar_user_default_image_2 = str_replace($upload_dir['baseurl'], '', $ar_user_default_image);
                }elseif (strpos($ar_user_default_image, $site_url) !== false) {
                    // Strip the domain and make the path relative if it matches the site URL
                    $ar_user_default_image_2 = str_replace($site_url, '', $ar_user_default_image);
                    // Ensure there is no leading slash
                    $ar_user_default_image_2 = ltrim($ar_user_default_image, '/');
                }
                // Full path to the image file
                $ar_user_default_image_path = $upload_dir['basedir'] . '/' . $ar_user_default_image_2;
                if (file_exists($ar_user_default_image_path)) {
                    // Get the file extension
                    $file_extension = pathinfo($ar_user_default_image_path, PATHINFO_EXTENSION);
                    
                    // Convert the extension to lowercase for case-insensitive comparison
                    $file_extension = strtolower($file_extension);
                    
                    // Check if the file is one of the allowed types
                    if (in_array($file_extension, ['glb', 'gltf'])) {
                        $glb_url = $ar_user_default_image;
                    }elseif (in_array($file_extension, ['jpg', 'png'])) {
                        $ar_user_default_image_data = getimagesize($ar_user_default_image_path); // Get image size data
                        if ($ar_user_default_image_data) {
                            $user_default_image_width = $ar_user_default_image_data[0]; // Width of the user default image
                            $user_default_image_height = $ar_user_default_image_data[1]; // Height of the user default image
                        }
                        $glb_url = $gallery_url.$ar_user_default_image.'&width='.$user_default_image_width.'&height='.$user_default_image_height.'&_wpnonce='.esc_html(wp_create_nonce( 'ar_secure_nonce' ));
                    }else{
                        
                    }
                }
            }
            
        }elseif (($ar_user_default=='Featured Image')AND$featured_image_id = get_post_thumbnail_id(get_the_ID())){
                // Get the featured image ID and its URL with width and height
                $featured_image_id = get_post_thumbnail_id(get_the_ID()); // Get the featured image ID of the current post
                $featured_image = '';
                $featured_image_width = 760; // Default width if no image
                $featured_image_height = 420; // Default height if no image
                
                if ($featured_image_id) {
                    $featured_image_data = wp_get_attachment_image_src($featured_image_id, 'full'); // Retrieve the image data
                    if ($featured_image_data) {
                        $featured_image = $featured_image_data[0]; // URL of the featured image
                        $featured_image_width = $featured_image_data[1]; // Width of the featured image
                        $featured_image_height = $featured_image_data[2]; // Height of the featured image
                    }
                }

                $glb_url = $gallery_url.$featured_image.'&width='.$featured_image_width.'&height='.$featured_image_height.'&_wpnonce='.esc_html(wp_create_nonce( 'ar_secure_nonce' ));
        }else{
            $glb_url = $gallery_url.site_url('/wp-content/plugins/' . $ar_plugin_id . '/assets/images/drag-drop-upload.jpg&width=760&height=420&_wpnonce='.esc_html(wp_create_nonce( 'ar_secure_nonce' )));
        }
        
        // Display the file input and model viewer
        $button_label = wp_kses('Upload Model or Image', ar_allowed_html());
        $ar_upload_label = get_option('ar_user_upload_button');
        if ($ar_upload_label !=''){
            $button_label = get_option('ar_user_upload_button');
        }
        
        if ($ar_user_button_location=='Hidden'){
            $ar_user_upload_button_hidden =' style="display: none;"';
        }
            $input_field = '<div class="custom-file-upload">
            <input type="file" id="ar_upload_model_file" name="ar_upload_model_file" accept=".glb,.gltf,.jpg,.png" style="display: none;">
            <button type="button" id="ar_user_upload_button" class="button ar_user_upload_button" '.$ar_user_upload_button_hidden.'>'.$button_label.'</button>
        </div>';
        

        if ($atts == 'input'){
            return $input_field; 
        }

        $model_array['model_id'] = 'user_upload';
        $settings_fields = array('ar_view_file', 'ar_scene_viewer', 'ar_qr_file', 'ar_qr_destination', 'ar_dimensions_units', 'ar_dimensions_label', 'ar_fullscreen_file', 'ar_dimensions_inches', 'ar_hide_dimensions', 'ar_hide_arview', 'ar_hide_qrcode', 'ar_hide_reset', 'ar_hide_fullscreen','ar_hide_gallery_sizes','ar_css','ar_css_positions');
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'ar_secure_nonce' ) ) {
            // If the nonce is invalid, stop the process
          //  wp_die( __( 'Security check failed.', 'ar-for-wordpress' ) );
        }
        foreach ($settings_fields as $k => $v){
            if (!isset($_POST[$v])){$_POST[$v]='';}
            $model_array[$v] = get_option($v);
        }
        
        $viewers = ($model_array['ar_scene_viewer'] == 1) ? 'scene-viewer webxr quick-look' : 'webxr scene-viewer quick-look';
        $model_array['ar_hide_arview'] = ($model_array['ar_hide_arview'] != '') ? ' nodisplay' : '';
        $show_ar = ($model_array['ar_hide_arview'] != '') ? '' : ' ar ar-modes="'.$viewers.'" ';

        if ($model_array['ar_view_file'] == ''){
            $output_html .= '<button slot="ar-button" class="ar-button ar-button-default '.$model_array['ar_hide_arview'].'" id="ar-button_user_upload"><img id="ar-img_user_upload" src="'.esc_url(plugins_url($ar_plugin_id."/assets/images/ar-view-btn.png", dirname(__FILE__))).'" class="ar-button-img"></button>';
        } else {
            $output_html .= '<button slot="ar-button" class="ar-button '.$model_array['ar_hide_arview'].'" id="ar-button_user_upload"><img id="ar-img_user_upload" src="'.esc_url($model_array['ar_view_file']).'" class="ar-button-img"></button>';
        }
        
        load_model_viewer_js();

        $model_viewer = '
        <div class="ardisplay_viewer"><model-viewer id="model_user_upload" camera-controls '.$show_ar.' src="'.ar_get_secure_model_url($glb_url).'" alt="AR Display 3D model" class="ardisplay_viewer ar_model_user_upload" quick-look-browsers="safari chrome" '.$output_atts.' >'.$output_html.'</model-viewer></div>';
        $model_viewer .= '
        <script>
            jQuery(document).ready(function($) {
                // When the custom button with ID #ar_user_upload_button is clicked
                $("#ar_user_upload_button").on("click", function() {
                    // Trigger the click event on the hidden file input
                    $("#ar_upload_model_file").click();
                });

                // Handle file input change
                $("#ar_upload_model_file").on("change", function(e) {
                    handleFileUpload(e.target.files[0]);
                });

                // Add drag-and-drop functionality to the model-viewer element
                $("#model_user_upload").on("dragover", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).addClass("dragover");
                });

                $("#model_user_upload").on("dragleave", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).removeClass("dragover");
                });

                $("#model_user_upload").on("drop", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).removeClass("dragover");

                    var file = e.originalEvent.dataTransfer.files[0];
                    $("#ar_upload_model_file")[0].files = e.originalEvent.dataTransfer.files; // Update input file
                    handleFileUpload(file);
                });

                // Function to handle file upload
                function handleFileUpload(file) {
                    if (file) {
                        var fileType = file.type;
                        if (!fileType) {
                            var fileExtension = file.name.split(".").pop().toLowerCase();
                            if (fileExtension === "glb") {
                                fileType = "model/gltf-binary";
                            } else if (fileExtension === "gltf") {
                                fileType = "model/gltf+json";
                            } else if (fileExtension === "jpg" || fileExtension === "jpeg") {
                                fileType = "image/jpeg";
                            } else if (fileExtension === "png") {
                                fileType = "image/png";
                            }
                        }

                        if (fileType === "model/gltf-binary" || fileType === "model/gltf+json") {
                            var url = URL.createObjectURL(file);
                            $("#model_user_upload").attr("src", url);
                        } else if (fileType === "image/jpeg" || fileType === "image/png") {
                            // Handle image files and calculate dimensions
                            ar_upload_file_dimenions(file, function(width, height, file) {
                                var fileUrl = encodeURIComponent(URL.createObjectURL(file));
                                var url = "' . site_url('/wp-content/plugins/' . $ar_plugin_id . '/includes/ar-gallery.php?url=') . '" + fileUrl + "&width=" + width + "&height=" + height + "&_wpnonce=" + "'.esc_html(wp_create_nonce( 'ar_secure_nonce' )).'";
                                $("#model_user_upload").attr("src", url);
                            });
                        } else {
                            console.log("Unsupported file type.");
                        }
                    } else {
                        console.log("No file selected or file is empty.");
                    }
                }

                // Function to handle image upload and get dimensions
                function ar_upload_file_dimenions(file, callback) {
                    if (file && file.type.match(\'image.*\')) {
                        var reader = new FileReader();

                        reader.onload = function(event) {
                            var img = new Image();
                            img.src = event.target.result;

                            img.onload = function() {
                                var width = img.width;
                                var height = img.height;

                                callback(width, height, file);
                            };
                            
                            img.onerror = function() {
                                console.log(\'Failed to load the image for dimensions.\');
                            };
                        };

                        reader.onerror = function() {
                            console.log(\'Failed to read the file.\');
                        };

                        reader.readAsDataURL(file);
                    } else {
                        console.log(\'Please select a valid image file.\');
                    }
                }
            });
        </script>';

        if ($atts == 'modelviewer'){
            return $model_viewer;
        }
        return $input_field . $model_viewer;
    }
}