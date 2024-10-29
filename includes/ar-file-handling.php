<?php
/**
 * AR Display
 * https://augmentedrealityplugins.com
**/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


if(!function_exists('ar_wp_custom_file_write')){
    function ar_wp_custom_file_write($file_path, $data) {
        // Initialize the WP Filesystem API
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        
        global $wp_filesystem;

        // Initialize the API
        $creds = request_filesystem_credentials( site_url() );
        
        if ( ! WP_Filesystem( $creds ) ) {
            return false; // Exit if unable to initialize the Filesystem
        }

        // Check if the file exists or can be created
        if ( ! $wp_filesystem->exists( $file_path ) ) {
            // Create the file if it doesn't exist
            $wp_filesystem->put_contents( $file_path, '' );
        }

        // Open the file and write data
        if ( $wp_filesystem->put_contents( $file_path, $data, FS_CHMOD_FILE ) ) {
            return true; // Data successfully written
        } else {
            return false; // Writing failed
        }
    }
}

if(!function_exists('ar_wp_is_writable')){
    function ar_wp_is_writable($path) {
        // Load the WordPress filesystem API if it's not already loaded
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        global $wp_filesystem;

        // Initialize the WordPress filesystem
        if ( ! WP_Filesystem() ) {
            return new WP_Error( 'filesystem_init_failed', __( 'Could not initialize filesystem.', 'ar-for-wordpress' ) );
        }

        // Check if the path is writable
        if ( $wp_filesystem->is_writable( $path ) ) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('ar_wp_readfile')) {
    function ar_wp_readfile($file_path) {
        // Load the WordPress filesystem API if it's not already loaded
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        global $wp_filesystem;

        // Initialize the WordPress filesystem
        if ( ! WP_Filesystem() ) {
            return new WP_Error( 'filesystem_init_failed', __( 'Could not initialize filesystem.', 'ar-for-wordpress' ) );
        }

        // Check if the file exists and is readable
        if ( ! $wp_filesystem->exists( $file_path ) ) {
            return new WP_Error( 'file_not_found', __( 'The file does not exist.', 'ar-for-wordpress' ) );
        }

        // Check file permissions to ensure it's readable
        if ( ! $wp_filesystem->is_readable( $file_path ) ) {
            return new WP_Error( 'file_not_readable', __( 'The file is not readable.', 'ar-for-wordpress' ) );
        }

        // Read the file contents
        $file_contents = $wp_filesystem->get_contents( $file_path );

        if ( false === $file_contents ) {
            return new WP_Error( 'read_error', __( 'Could not read the file.', 'ar-for-wordpress' ) );
        }

        return $file_contents;
    }
}

if(!function_exists('ar_wp_get_page_by_title')){
    function ar_wp_get_page_by_title( $title, $post_type = 'page' ) {
        $args = array(
            'post_type'   => $post_type, // Type of post (e.g., 'page', 'post', etc.)
            'title'       => $title,     // The title of the page to search for
            'post_status' => 'publish',  // Ensure the post is published (modify as needed)
            'numberposts' => 1           // We only need one result
        );

        $query = new WP_Query( $args );

        // If a page is found, return the first result
        if ( $query->have_posts() ) {
            return $query->posts[0]; // Return the page/post object
        }

        return null; // Return null if no page is found
    }
}
//used to return the file contents in secure download and QR code popup
if(!function_exists('ar_return_file')){
    function ar_return_file($file_contents) {
        //No foreseeable way to escape the file contents without corrupting them
        echo $file_contents;
    }
}


// Check Zip Archive
if (!function_exists('ar_check_zip_archive')){
    function ar_check_zip_archive($filename) {
        $zip = new ZipArchive();
        $error = 0;

        $blacklist = array("php", "exe", "sh", "js", "bat", "pl", "py");

        // Get the file extension        
        if ($zip->open($filename)!==TRUE) {
           $error++;
        }

        for ($i=0; $i<$zip->numFiles;$i++) {
           $info = $zip->statIndex($i);
           $file = pathinfo($info['name']);
           
           $file_ext = pathinfo($file["name"], PATHINFO_EXTENSION);

            // Check if the file extension is blacklisted
            if(in_array($file_ext, $blacklist)){
                $error++;
            }

        }
        $zip->close();

        return $error ? false : true;
    }
}

/********** Curl Get File **********/
if (!function_exists('ar_curl')){
    function ar_curl($url) {
        /*$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        $data = curl_exec($ch);

        if (curl_errno($ch)) {            
            $data = wp_remote_get($url);
        }
        
        curl_close($ch);*/
        
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: ".esc_html($error_message);
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = $body;
            // Handle the data as needed
        }

        //var_dump($data);
        return $data;
    }
}


if (!function_exists('get_local_file_contents')){
    function get_local_file_contents( $file_path ) {
        ob_start();
        include $file_path;
        $contents = ob_get_clean();
    
        return $contents;
    }
}

/************* Upload AR Model Files Javascript *******************/
if (!function_exists('ar_model_fields_js')){
    function ar_model_fields_js($model_id, $variation_id='') { 
        global $ar_plugin_id;
        $wc_model = 0;
        $suffix = $variation_id ? "_var_".$variation_id : '';

        $arpost = get_post( $model_id );
        $ar_animation_selection = get_post_meta( $model_id, '_ar_animation_selection', true ); 

        if($arpost->post_type == 'product'){
            $product=wc_get_product($model_id);
            $product_parent=$product->get_parent_id();
            $wc_model = 1;
            if($product_parent==0){
                $product_parent = $model_id;
            }
        } else {
            $product_parent = $model_id;
        }

        $public = '';
        //Check if on admin edit page or public page
        if (is_admin()){
            $screen = get_current_screen();
        }
        if (!isset($screen)){
            //Showing Editor on Public Side
            $post   = get_post( $model_id );
            $public = 'y';
        }
        
        add_action('wp_footer', function (){ 
           
        ?>
            <script>
            
                var modelFieldOptions = {                                                   
                    product_parent: '<?php echo esc_html($product_parent);?>',
                    usdz_thumb: '<?php echo esc_url( plugins_url( "../assets/images/ar_model_icon_tick.jpg", __FILE__ ) );?>',
                    glb_thumb: '<?php echo esc_url( plugins_url( "../assets/images/ar_model_icon_tick.jpg", __FILE__ ) );?>',
                    site_url: '<?php echo esc_url(get_site_url());?>',
                    js_alert: '<?php echo esc_html(__('Invalid file type. Please choose a USDZ, REALITY, GLB or GLTF.', 'ar-for-wordpress' ));?>',
                    uploader_title: '<?php echo esc_html(__('Choose your AR Files', 'ar-for-wordpress' ));?>',
                    suffix: '<?php echo esc_html($suffix);?>',
                    ar_animation_selection: '<?php echo esc_html($ar_animation_selection);?>', 
                    public: '<?php echo esc_html($public);?>',
                    wc_model: '<?php echo esc_html($wc_model);?>',
                };
                
                var modelFields_<?php echo esc_html($model_id);?> = new ARModelFields(<?php echo esc_html($model_id);?>,modelFieldOptions);
                    
                
            </script>

        <?php   
        });
        
        
    }
}

/********** AR 3D Model Conversion **************/
if (!function_exists('ar_model_conversion')){
    function ar_model_conversion($model) {
        $link = 'https://augmentedrealityplugins.com/converters/glb_conversion.php';
        ob_start();
        $response = wp_remote_get( $link.'?model_url='.rawurlencode($model));
        if ( !is_wp_error($response) && isset( $response[ 'body' ] ) ) {
            return $response['body'];
        }
        ob_flush();
    }
 }
 
 

if (!function_exists('ar_remove_asset')){
    function ar_remove_asset($dir) {
       if (is_dir($dir)) {
         $objects = scandir($dir);
         foreach ($objects as $object) {
           if ($object != "." && $object != "..") {
             if (filetype($dir."/".$object) == "dir") ar_remove_asset($dir."/".$object); else wp_delete_file($dir."/".$object);
           }
         }
         reset($objects);
         WP_Filesystem_Direct::rmdir($dir);
       }
    }
}



