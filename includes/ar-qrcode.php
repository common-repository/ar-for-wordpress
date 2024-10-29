<?php
/**
 * AR Display
 * https://augmentedrealityplugins.com
**/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

spl_autoload_register(function ($class) {
     $prefix = 'chillerlan\QRCode\\'; 
     $base_dir = plugin_dir_path( __FILE__ ) . '/php-qrcode/src/'; 
     $len = strlen($prefix); 
     if (strncmp($prefix, $class, $len) !== 0) { 
        return; 
     } 
     $relative_class = substr($class, $len); 
     $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php'; 
     
     if (file_exists($file)) { require_once $file; } 
});

spl_autoload_register(function ($class) {
     $prefix = 'chillerlan\Settings\\'; 
     $base_dir = plugin_dir_path( __FILE__ ) . '/php-settings-container/src/'; 
     $len = strlen($prefix); 
     if (strncmp($prefix, $class, $len) !== 0) { 
        return; 
     } 
     $relative_class = substr($class, $len); 
     $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php'; 
   
     if (file_exists($file)) { require_once $file; } 
});

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\LogoOptions;
use chillerlan\QRCode\QRImageWithLogo;

if(extension_loaded('imagick')) {
    
    defined('IMGCK_ENABLED') or DEFINE('IMGCK_ENABLED', 1);

} else {
    defined('IMGCK_ENABLED') or DEFINE('IMGCK_ENABLED', 0);
}

/*** QR Code + Logo Generator */
if (!function_exists('ar_qr_code')){
    function ar_qr_code($logo,$id,$data='') {
        if ( filter_var( ini_get( 'allow_url_fopen' ), FILTER_VALIDATE_BOOLEAN ) ){
            $data = $data ? $data : esc_url( get_permalink($id) );
            $size = isset($size) ? $size : '250x250';
            $logo = isset($logo) ? $logo : FALSE;
         
            
            if(IMGCK_ENABLED){
                //wp_die('here');
                $options = new LogoOptions;

                $options->version          = QRCode::VERSION_AUTO;
                $options->eccLevel         = QRCode::ECC_H;
                $options->imageBase64      = false;
                $options->logoSpaceWidth   = 10;
                $options->logoSpaceHeight  = 10;
                $options->scale            = 5;
                $options->imageTransparent = false;

                if($logo !== FALSE){
                    $logo_data = ar_curl($logo);
                    $logo_str = @imagecreatefromstring($logo_data);
                                        
                //header('Content-type: image/png');

                    $qrOutputInterface = new QRImageWithLogo($options, (new QRCode($options))->getMatrix($data));

                // dump the output, with an additional logo
                    $QR = $qrOutputInterface->dump(null, $logo_str);
                    
                    if(strstr($QR, 'Error:')){
                        $QR = ar_qr_code_api($logo, $data);
                    }
    
                    //die('<img src="data:image/png;base64,'.base64_encode($QR).'" />');
                    return $QR;

                }

                
            } else {
                
                //use google api to generate qr
                return ar_qr_code_api($logo,$data);
            }            
            
        }
    }
}

if (!function_exists('ar_qr_code_api')){
    function ar_qr_code_api($logo, $data){
        $data = $data ? $data : 'https://augmentedrealityplugins.com';
        $size = isset($size) ? $size : '250x250';
        $logo = isset($logo) ? $logo : FALSE;

        if (function_exists('imagecreatefrompng')) {
            // GD library is enabled
            //google qr 'https://chart.googleapis.com/chart?cht=qr&chld=H|1&chs='.$size.'&chl='.urlencode($data)
            
            
            $logo_url = urlencode($logo);
            $qr_source_url = 'https://quickchart.io/qr?text='.urlencode($data);
            $qr_source_url .= '&centerImageUrl='.$logo_url;
            $qr_source_url .= '&centerImageSizeRatio=0.35';
            //wp_die($qr_source_url);
            $QR = @imagecreatefrompng($qr_source_url);
            //var_dump($QR);
            //wp_die($QR);
        
            /*if($logo !== FALSE && $QR){
                $logo_data = ar_curl($logo);
                $logo = imagecreatefromstring($logo_data);
                if ($logo !== false) {
                    $QR_width = imagesx($QR);
                    $QR_height = imagesy($QR);
                    
                    $logo_width = imagesx($logo);
                    $logo_height = imagesy($logo);
                    
                    // Scale logo to fit in the QR Code
                    $logo_qr_width = intval($QR_width/3);
                    $scale = $logo_width/$logo_qr_width;
                    $logo_qr_height = intval($logo_height/$scale);
                    imagecopyresampled($QR, $logo, intval($QR_width/3), intval($QR_height/3), 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
                }
            }*/

            if($QR){                   
                ob_start();
                imagepng($QR);
                $imgData=ob_get_clean();
                if (!is_bool($QR)){
                    imagedestroy($QR);
                }
                return $imgData;

            }else{
                return '';
            }
        }else{
                //failed
                return '';
        }
    }
}