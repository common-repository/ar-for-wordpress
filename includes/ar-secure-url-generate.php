<?php
/**
 * AR Display
 * https://augmentedrealityplugins.com
**/



/************* Encrypt the file path *******************/
if (!function_exists('ar_encrypt_file_path')){
    // Function to encrypt the file path
    function ar_encrypt_file_path($file_path, $key) {
        // Ensure key length is 32 bytes (256 bits)
        $key = substr(hash('sha256', $key, true), 0, 32);
        
        // Generate a random IV
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
    
        // Encrypt the file path
        $encrypted = openssl_encrypt($file_path, 'AES-256-CBC', $key, 0, $iv);
    
        // Encode IV and encrypted data together
        return base64_encode($iv . '::' . $encrypted);
    }

}

/************* Check if the URL is within the WordPress installation *******************/
if (!function_exists('ar_is_within_wordpress_install')){
    function ar_is_within_wordpress_install($url) {
        $home_url = home_url(); // Get the base URL of the WordPress installation
        $upload_dir = wp_upload_dir(); // Get the upload directory path
        $upload_base_url = $upload_dir['baseurl']; // Base URL of the uploads directory
        // Check if the URL starts with the base URL or uploads base URL
        return strpos($url, $home_url) === 0 || strpos($url, $upload_base_url) === 0;
    }
}

/************* Check if the file extension is valid *******************/
if (!function_exists('ar_is_valid_file_extension')){
    function ar_is_valid_file_extension($file_path) {
        $valid_extensions = array('gltf', 'glb', 'usdz');
        $ext = pathinfo($file_path, PATHINFO_EXTENSION);
        return in_array($ext, $valid_extensions);
    }
}

/************* Generate the secure model URL *******************/
if (!function_exists('ar_get_secure_model_url')){
    function ar_get_secure_model_url($file_url) {
        global $ar_plugin_id;
        if ((get_option('ar_licence_valid')=='Valid') AND (get_option('ar_secure_model_urls')==1)){
            $secret_key = get_option('ar_licence_key');
            // Check if the URL is within the WordPress installation and encrypt it
            if (ar_is_within_wordpress_install($file_url)) {
                $uploads_dir = wp_upload_dir();
                $uploads_url = $uploads_dir['baseurl'].'/';
                // Extract the upload path from the URL
                $file_path = str_replace($uploads_url, '', $file_url);
                // Check if the file is from galllery builder
                if (strpos($file_path, 'Frameless') !== false) {
                    // Use the original URL if the file is from galllery builder
                    $secure_url = $file_url;
                }elseif (ar_is_valid_file_extension($file_path)) {
                    // Check if the file extension is valid
                    $encrypted_file_path = ar_encrypt_file_path($file_path, $secret_key);
                    $secure_url = site_url('/wp-content/plugins/'.$ar_plugin_id.'/includes/ar-secure-download.php?file=' . urlencode($encrypted_file_path));
                } else {
                    // Use the original URL if the file type is not valid
                    $secure_url = $file_url;
                }
            } else {
                // Use the original URL if it's not within the WordPress installation
                $secure_url = $file_url;
            }
        }else{
            // Use the original URL if no valid licence subscription
            $secure_url = $file_url;
        }
        return $secure_url;
    }
}