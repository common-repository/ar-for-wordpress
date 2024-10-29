<?php
/**
 * AR Display
 * https://augmentedrealityplugins.com
**/

/************* Ensure that WordPress is loaded *******************/
// Define the path to wp-load.php
$wp_load_path = dirname(__FILE__) . '/../../../../wp-load.php';

if (file_exists($wp_load_path)) {
    require_once($wp_load_path);
} elseif (defined('ABSPATH') && file_exists(ABSPATH . 'wp-load.php')) {
    require_once(ABSPATH . 'wp-load.php');
} else {
    wp_die('WordPress core files not found.');
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
            default:
                return 'application/octet-stream'; // Fallback MIME type
        }
    }
}

/************* Function to validate and serve the request *******************/
if (!function_exists('ar_gallery_file')){
    function ar_gallery_file($thumbnail_id, $image_url, $aspect_ratio, $orientation, $height, $width, $framed, $frame_color, $opacity) {
        global $ar_plugin_id;
        // Referrer Check: Ensure the request is coming from a valid source
        $referer = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : '';
        $valid_referer = home_url(); // You can set this to your site's URL or specific referrers
        
        $image_name = '';
        if ($framed =='0'){
            $opacity = 0;
            $framed = '';
        }elseif ($framed =='2'){
            $framed = '_framed';
        }else{
            $framed = '';
        }
        if (strpos($referer, $valid_referer) !== 0) {
            wp_die('Unauthorized access');
        }
        // Get the featured image URL
        if ($thumbnail_id) {
            $image_url = wp_get_attachment_image_url($thumbnail_id, 'full'); // 'full' can be replaced with other image sizes if needed
            if (!$image_url) {
                return 'Image not found';
            }
            $image_id = $thumbnail_id;
        }elseif($image_url){
            //set aspect ratio and orientation
            $image_id = 'upload';
            // Calculate aspect ratio
            if (!$aspect_ratio){
                $aspect_ratio = 1;
                if ($width > $height){
                    $aspect_ratio = round($width / $height,2);
                }elseif ($width < $height){
                    $aspect_ratio = round($height / $width,2);
                }
            }
            if (!$orientation){
                $orientation = 'l';
                if ($width > $height){
                    $orientation = 'l';
                    
                }elseif ($width < $height){
                    $orientation = 'p';
                }
            }else{
               $orientation = substr($orientation, 0, 1); 
            }
            // Path to the JSON file
            $json_file_path = esc_url( plugins_url( "../assets/models/models.json", __FILE__ ) );
            
            // Fetch the JSON data using wp_remote_get
            $response = wp_remote_get($json_file_path);
            
            // Check if the request was successful
            if ( is_wp_error($response) ) {
                die('Error reading JSON file');
            }
            
            // Get the body part of the response, which contains the JSON data
            $json_data = wp_remote_retrieve_body($response);
            
            // Decode JSON data into a PHP array
            $data_array = json_decode($json_data, true);
            
            // Check if json_decode succeeded
            if (json_last_error() !== JSON_ERROR_NONE) {
               die( esc_html( 'Error decoding JSON data: ' . json_last_error_msg() ) );
            }
            
            if ($data_array === null) {
                die('Error decoding JSON data');
            }
            $aspect_ratio = (string) $aspect_ratio;
            // Check if the ratio exists in the data array
            if (!isset($data_array[$aspect_ratio])) {
                // Get the available ratios from the data array
                $available_ratios = array_keys($data_array);
                // Find the closest ratio
                $aspect_ratio = ar_findClosestRatio($aspect_ratio, $available_ratios);
            }
            // Remove query string if present
            $image_name = strtok($image_url, '?');
            
            // Extract file name without the extension
            $file_info = pathinfo($image_name);
            $image_name = $file_info['filename'];
        }
        $mime_type = ar_get_mime_type($image_url);
        
        if($aspect_ratio =='1.0'){
            $orientation = 'square';
        }elseif ($orientation == 'l'){
            $orientation = 'landscape';
        }else{
            $orientation = 'portrait';
        }
        $upload_dir = wp_upload_dir();
        
        $upload_dir = wp_upload_dir();
        $models_dir_url = $upload_dir['baseurl'] . '/' . $ar_plugin_id . '/models/';
        $models_dir = $upload_dir['basedir'] . '/' . $ar_plugin_id . '/models/';
        $json_file_path = $models_dir . $aspect_ratio . '_' . $orientation . $framed . '.gltf';
        
        // Check if the file exists
        if (!file_exists($json_file_path)) {
        
            // Check if the folder exists, if not create it
            if (!is_dir($models_dir)) {
                wp_mkdir_p($models_dir); // Create the directory recursively
            }
        
            // Source directory within the plugin
            $source_dir = plugin_dir_path( __FILE__ ) . '../assets/models/';
        
            // Check if the source directory exists
            if (is_dir($source_dir)) {
        
                // Open the source directory
                $files = scandir($source_dir);
        
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
        
                        $source_file = $source_dir . $file;
                        $destination_file = $models_dir . $file;
        
                        // If the file has a .gltfdata extension, rename it to .bin
                        if (pathinfo($file, PATHINFO_EXTENSION) == 'gltfdata') {
                            $destination_file = $models_dir . pathinfo($file, PATHINFO_FILENAME) . '.bin';
                        }
        
                        // Copy the file from the source directory to the destination directory
                        copy($source_file, $destination_file);
                    }
                }
        
                // Log the action if needed
                //error_log("Copied files from $source_dir to $models_dir");
            } else {
                // Log error if the source directory doesn't exist
                //error_log("Source directory does not exist: $source_dir");
            }
        }
        
        // Use esc_url for safe usage in URLs
        $json_file_url = esc_url($upload_dir['baseurl'] . '/' . $ar_plugin_id . '/models/' . $aspect_ratio . '_' . $orientation . $framed . '.gltf');

//echo $json_file_url;
        // Fetch the JSON data using wp_remote_get
        $response = wp_remote_get($json_file_url);
        
        // Check if the request was successful
        if ( is_wp_error($response) ) {
            die('Error reading JSON file gltf');
        }
        
        // Get the body part of the response, which contains the JSON data
        $json_data = wp_remote_retrieve_body($response);
        
        // Decode JSON data into a PHP array
        $data_array = json_decode($json_data, true);
        
        // Check if json_decode succeeded
        if (json_last_error() !== JSON_ERROR_NONE) {
            die( esc_html( 'Error decoding JSON data: ' . json_last_error_msg() ) );
        }
        
        if ($data_array === null) {
            die('Error decoding JSON data');
        }
        // Update the images array
        if (isset($data_array['images']) && is_array($data_array['images'])) {
            foreach ($data_array['images'] as &$image) {
                // Assign standard URLs to the 'uri' field without escaping slashes
                $image['uri'] = $image_url;  // Ensure $image_url is a full valid URL
                $image['mimeType'] = $mime_type;
            }
        }
        
        // Update the buffers array
        if (isset($data_array['buffers']) && is_array($data_array['buffers'])) {
            foreach ($data_array['buffers'] as $index => &$buffer) {
                if ($index === 0) {
                    // Use site_url() to generate the full URL to the resource
                    $buffer['uri'] = esc_url($upload_dir['baseurl'] . '/' . $ar_plugin_id . '/models/' . $aspect_ratio . '_' . $orientation . $framed . '.bin');
                } 
            }
        }

        // Convert hex color to RGB
        function hexToRgb($hex) {
            // Remove '#' if it exists
            $hex = ltrim($hex, '#');
        
            // Ensure the hex code is valid (6 characters)
            if (strlen($hex) !== 6) {
                return [0, 0, 0]; // Return black for invalid hex
            }
            
            // Split the hex into RGB components
            $r = hexdec(substr($hex, 0, 2)) / 255;
            $g = hexdec(substr($hex, 2, 2)) / 255;
            $b = hexdec(substr($hex, 4, 2)) / 255;
        
            return [$r, $g, $b, 0];
        }
        
        $rgb = hexToRgb('#'.$frame_color);
        
        // Traverse through the data array to modify the materials
        foreach ($data_array['materials'] as &$material) {
            // Ensure "alphaMode" is set to "BLEND" for all materials
            $material['alphaMode'] = 'BLEND';
        
            // Check if the material is not named "Material.001"
            if ($material['name'] !== 'Material.001') {
                if (isset($material['pbrMetallicRoughness']['baseColorFactor'])) {
                    // Update the baseColorFactor with RGB values and opacity
                    $material['pbrMetallicRoughness']['baseColorFactor'][0] = $rgb[0]; // Red
                    $material['pbrMetallicRoughness']['baseColorFactor'][1] = $rgb[1]; // Green
                    $material['pbrMetallicRoughness']['baseColorFactor'][2] = $rgb[2]; // Blue
                    $material['pbrMetallicRoughness']['baseColorFactor'][3] = $opacity; // Opacity
                }
            }
        }
        
        
        
        // Convert the updated array to JSON
        $updated_json = wp_json_encode($data_array, JSON_UNESCAPED_SLASHES);
        
        // Sanitize the output before serving
        //$updated_json = wp_kses_post( $updated_json );
        
        // Prevent caching
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache'); // For HTTP/1.0 compatibility
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        
        // Serve the file
        header('Content-Type: ' . esc_attr($mime_type));
        header('Content-Length: ' . strlen($updated_json));
        header('Content-Disposition: attachment; filename="' . esc_attr($thumbnail_id) . $image_name. '_ar_model.gltf"'); // Suggest inline display
        
        echo wp_kses_post($updated_json);
        exit;
        
    }
}
// Verify the nonce before processing

if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ar_secure_nonce' ) ) {
    // If the nonce is invalid, stop the process
    wp_die( esc_html__( 'Security check failed.', 'ar-for-wordpress' ) );
}
// Get the encrypted file path from the query string
$thumbnail_id = isset($_GET['id']) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
$aspect_ratio = isset($_GET['ratio']) ? sanitize_text_field(wp_unslash($_GET['ratio'])) : '';
$orientation = isset($_GET['o']) ? sanitize_text_field(wp_unslash($_GET['o'])) : '';
$url = isset($_GET['url']) ? sanitize_text_field(wp_unslash($_GET['url'])) : '';
$height = isset($_GET['height']) ? sanitize_text_field(wp_unslash($_GET['height'])) : '';
$width = isset($_GET['width']) ? sanitize_text_field(wp_unslash($_GET['width'])) : '';
$framed = isset($_GET['f']) ? sanitize_text_field(wp_unslash($_GET['f'])) : '';
$frame_color = isset($_GET['fc']) ? sanitize_text_field(wp_unslash($_GET['fc'])) : '000000';
$opacity = isset($_GET['opacity']) ? sanitize_text_field(wp_unslash($_GET['opacity'])) : '1';
// Validate and serve the file
ar_gallery_file($thumbnail_id, $url, $aspect_ratio, $orientation, $height, $width, $framed, $frame_color, $opacity);