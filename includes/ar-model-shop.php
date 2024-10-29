<?php
/**
 * AR Display
 * AR For WordPress
 * https://augmentedrealityplugins.com
**/

// Add a custom admin menu
if (!function_exists('ar_modelshop_admin_menu')){
    add_action('admin_menu', 'ar_modelshop_admin_menu');
    
    function ar_modelshop_admin_menu() {
        add_menu_page(
            'AR Model Shop', // Page title
            'AR Model Shop', // Menu title
            'manage_options', // Capability
            'ar-modelshop', // Menu slug
            'ar_modelshop_admin_page', // Callback function
            'dashicons-cart', // Icon
            6 // Position
        );
    }
}

// Enqueue jQuery UI
if (!function_exists('enqueue_jquery_ui')){
    add_action('admin_enqueue_scripts', 'enqueue_jquery_ui');
    function enqueue_jquery_ui() {
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_style('wp-jquery-ui-dialog');
    }
}

// Render the custom admin page
if (!function_exists('ar_modelshop_admin_page')){
    function ar_modelshop_admin_page() {
        global $ar_plugin_id;
        
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'ar_secure_nonce')) {
            wp_die(wp_kses('Security check failed.', ar_allowed_html()));
        }

        $file_urls = isset($_GET['file_urls']) ? esc_attr(sanitize_text_field(wp_unslash($_GET['file_urls']))) : '';
        $redirect_url = admin_url('admin.php?page=ar-modelshop');
        $encoded_redirect_url = $redirect_url;
        ?>
        <div class="wrap">
            <div class="instructions">
                <h1>AR Model Shop</h1>
                <hr>
                <?php if (!$file_urls) { ?>
                    <p><a href="https://armodelshop.com?from_plugin=true&redirect_url=<?php echo esc_url($encoded_redirect_url); ?>" target="_blank">
                        <img src="<?php echo esc_url(plugins_url("../assets/images/ar-model-shop-icon.png", __FILE__)); ?>" width="200px" style="float:right; padding: 10px 0 20px 20px;"></a>
                        <?php echo esc_html__('Explore the AR Model Shop for an extensive collection of 3D models tailored for augmented reality.', 'ar-for-wordpress'); ?>
                    </p>
                    <p><?php echo esc_html__('Simply visit the AR Model Shop by using the button below, choose your desired models, and complete your purchase', 'ar-for-wordpress'); ?></p>
                    <button id="open-ar-modelshop" class="button" style="padding:20px 40px;margin-top:20px"
                        onclick="window.open('https://armodelshop.com?from_plugin=true&redirect_url=<?php echo esc_url($encoded_redirect_url); ?>', '_blank')">
                        <?php echo esc_html__('Open AR Model Shop', 'ar-for-wordpress'); ?> <sup>&#x2197;</sup>
                    </button>
                <?php } ?>
            </div>
        </div>
        <?php
    }
}



// Function to log debug messages
if (!function_exists('ar_debug_log')){
    function ar_debug_log($message) {
        //error_log($message); // Ensure this path is writable by the web server
        echo "<script>console.log(".wp_json_encode($message).");</script>";
    }
}
// Handle the form submission and import files
if (!function_exists('ar_import_files')){
    add_action('admin_post_import_files', 'ar_import_files');
    
    function ar_import_files() {
        ar_debug_log('Import files handler started'); // Debugging line
    
        if (!isset($_POST['import_files_nonce_field']) || !wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['import_files_nonce_field'])), 'import_files_nonce')) {
            ar_debug_log('Nonce verification failed'); // Debugging line
            wp_die('Nonce verification failed');
        }
    
        if (!current_user_can('manage_options')) {
            ar_debug_log('User does not have permissions'); // Debugging line
            return;
        }
    
        if (isset($_POST['file_urls'])) {
            $file_urls = explode(',', sanitize_text_field(wp_unslash($_POST['file_urls'])));
            //ar_debug_log('File URLs: ' . print_r($file_urls, true)); // Debugging line
    
            foreach ($file_urls as $file_url) {
                $file_url = trim($file_url);
                if (!empty($file_url)) {
                    ar_debug_log('Processing file URL: ' . $file_url); // Debugging line
                    ar_download_and_save_file($file_url);
                }
            }
        }
    
        //wp_redirect(admin_url('admin.php?page=ar-modelshop&imported=1'));
        wp_redirect(admin_url('upload.php'));
        exit;
    }
}
if (!function_exists('ar_download_and_save_file')){
    function ar_download_and_save_file($file_url) {
        ar_debug_log('Attempting to download file: ' . $file_url); // Debugging line
    
        // Get cookies from the current session to pass with the request
        $cookies = array();
        foreach ($_COOKIE as $name => $value) {
            $cookies[] = $name . '=' . $value;
        }
        $cookie_string = implode('; ', $cookies);
        ar_debug_log('Cookies: ' . $cookie_string); // Debugging line
    
        // Set up headers to mimic a browser request
        $headers = array(
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:91.0) Gecko/20100101 Firefox/91.0',
            'Cookie' => $cookie_string,
        );
    
        // Use wp_safe_remote_get to handle the request
        $response = wp_safe_remote_get($file_url, array(
            'timeout' => 120,
            'headers' => $headers,
        ));
    
        if (is_wp_error($response)) {
            ar_debug_log('Failed to download file: ' . $file_url . ' - ' . $response->get_error_message());
            return;
        }
    
        $file_contents = wp_remote_retrieve_body($response);
        $http_code = wp_remote_retrieve_response_code($response);
        $headers = wp_remote_retrieve_headers($response);
    
        ar_debug_log('HTTP response code: ' . $http_code); // Debugging line
    
        if ($http_code !== 200) {
            ar_debug_log('HTTP error: ' . $http_code . ' for URL: ' . $file_url);
            return;
        }
    
        if (empty($file_contents)) {
            ar_debug_log('Error: Empty file contents from URL: ' . $file_url);
            return;
        }
    
        // Get the upload directory
        $upload_dir = wp_upload_dir();
        //ar_debug_log('Upload directory: ' . print_r($upload_dir, true)); // Debugging line
    
        // Extract the filename from the Content-Disposition header if available
        $filename = '';
        if (isset($headers['content-disposition'])) {
            if (preg_match('/filename[^;=\n]*=((["\']).*?\2|[^;\n]*)/', $headers['content-disposition'], $matches)) {
                $filename = trim($matches[1], ' "\'');
            }
        }
    
        // If no filename is found, use a default name
        if (!$filename) {
            $filename = basename(parse_url($file_url, PHP_URL_PATH));
            if (!$filename) {
                $filename = 'downloaded_file_' . time();
            }
        }
    
        // Determine the file path
        $file_path = $upload_dir['path'] . '/' . $filename;
        ar_debug_log('File path: ' . $file_path); // Debugging line
    
        // Save the file contents to the specified path
        if (ar_wp_custom_file_write($file_path, $file_contents) === false) {
            ar_debug_log('Error: Failed to write file: ' . $file_path);
            return;
        }
    
        ar_debug_log('File saved to: ' . $file_path); // Debugging line
    
        // Get the file type
        $wp_filetype = wp_check_filetype(basename($file_path), null);
    
        // Create the attachment array
        $attachment = array(
            'guid' => $upload_dir['url'] . '/' . basename($file_path),
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name(basename($file_path)),
            'post_content' => '',
            'post_status' => 'inherit'
        );
    
        // Insert the attachment into the WordPress media library
        $attach_id = wp_insert_attachment($attachment, $file_path);
    
        // Include the image.php file to generate attachment metadata
        require_once(ABSPATH . 'wp-admin/includes/image.php');
    
        // Generate the attachment metadata
        $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
    
        // Update the attachment metadata
        wp_update_attachment_metadata($attach_id, $attach_data);
    
        ar_debug_log('File imported to media library: ' . $file_path . ' - Attachment ID: ' . $attach_id);
    }
}
// Add JavaScript to handle the click event for the "Import to Media Library" button
if (!function_exists('ar_add_import_button_js')){
    add_action('wp_footer', 'ar_add_import_button_js');
    function ar_add_import_button_js() {
        if (is_wc_endpoint_url('order-received')) {
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                console.log('Document ready on thank you page');
    
                // Handle Import to Media Library button click
                $('.import-to-media-library').on('click', function(e) {
                    e.preventDefault();
                    var importUrl = $(this).attr('href');
                    window.opener.location.href = importUrl;
                    //window.close();
                });
            });
            </script>
            <?php
        }
    }
}