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

/************* Function to decrypt the file path *******************/
if (!function_exists('ar_decrypt_file_path')){
    // Function to decrypt the file path
    function ar_decrypt_file_path($encrypted_data, $key) {
        // Ensure key length is 32 bytes (256 bits)
        $key = substr(hash('sha256', $key, true), 0, 32);
    
        // Decode the encrypted data
        $data = base64_decode($encrypted_data);
        $parts = explode('::', $data, 2);
    
        if (count($parts) < 2) {
            return false; // Invalid data format
        }
    
        $iv = $parts[0];
        $encrypted = $parts[1];
    
        // Decrypt the file path
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
}
/************* Function to validate and serve the request *******************/
if (!function_exists('ar_validate_and_serve_file')){
    function ar_validate_and_serve_file($encrypted_file_path) {
        // Define the secret key for encryption/decryption
        $secret_key = get_option('ar_licence_key'); // Change this to a secure key
    
        // Decrypt the file path
        $file_path = ar_decrypt_file_path($encrypted_file_path, $secret_key);
    
        // Get the uploads directory path dynamically
        $uploads_dir = wp_upload_dir(); // Get the upload directory information
        $allowed_directory = $uploads_dir['basedir']; // Base directory for uploads
        $full_file_path = $allowed_directory . '/' . $file_path;
    
        // Referrer Check: Ensure the request is coming from a valid source
        $referer = isset($_SERVER['HTTP_REFERER']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER'])) : '';
        $valid_referer = home_url(); // You can set this to your site's URL or specific referrers
    
        if (strpos($referer, $valid_referer) !== 0) {
            wp_die('Unauthorized access');
        }
        if (file_exists($full_file_path)) {
            // Sanitize the file path to ensure it's safe.
            $full_file_path = realpath($full_file_path);
            
            // Check if the file exists and is readable before proceeding
            if (!$full_file_path || !is_readable($full_file_path)) {
                wp_die('File not found or inaccessible.');
            }


            // Prevent caching
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache'); // For HTTP/1.0 compatibility
            header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
            
            // Serve the file with the correct MIME type and headers
            header('Content-Type: ' . esc_attr(ar_get_mime_type($full_file_path))); // Use esc_attr for MIME type
            header('Content-Length: ' . filesize($full_file_path));
            header('Content-Disposition: attachment; filename="' . esc_attr(basename($file_path)) . '"'); // Escape the filename
            
            // Use a secure file read function to output the file contents.
            if (ar_wp_readfile($full_file_path) === false) {
                wp_die('Error reading the file.');
            }
            ar_return_file( ar_wp_readfile($full_file_path));
            exit;
        } else {
            wp_die('File not found');
        }
    }
}
// Verify the nonce before processing
if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ar_secure_nonce' ) ) {
    // If the nonce is invalid, stop the process
  //  wp_die( __( 'Security check failed.', 'ar-for-wordpress' ) );
}
// Get the encrypted file path from the query string
$encrypted_file_path = isset($_GET['file']) ? sanitize_text_field(wp_unslash($_GET['file'])) : '';


// Validate and serve the file
ar_validate_and_serve_file($encrypted_file_path);