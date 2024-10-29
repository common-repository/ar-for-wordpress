<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class AR_Model {

    /**
     * Constructor
     */
    public $model_array;
    public $model_id;
    public $attributes;
    public $variation_id;

    public function __construct($atts, $variation_id='') {
        global $ar_wp_active, $ar_wc_active;
        
        if (isset($atts['cat'])){
            $ar_cat_list=explode(',',$atts['cat']);
            if (isset($ar_wp_active)){
                $args = array(
                    'post_type' => 'armodels',
                    'tax_query' => array(
                        'relation' => 'OR', // Use 'OR' to match either category or tag
                        array(
                            'taxonomy' => 'model_category', // Ensure this matches your custom taxonomy name
                            'field'    => 'id',
                            'terms'    => $ar_cat_list, // Array of category IDs to filter by
                        ),
                        array(
                            'taxonomy' => 'post_tag', // Taxonomy for tags; use 'post_tag' for default tags in WordPress
                            'field'    => 'id',
                            'terms'    => $ar_cat_list, // Array of tag IDs to filter by
                        ),
                    ),
                );
                $the_query = new WP_Query( $args );
                
                // The Loop
                if ( $the_query->have_posts() ) { 
                    while ( $the_query->have_posts() ) {
                        $the_query->the_post();
                        $ar_model_list[]= get_the_ID();
                    }
                }
            }
            if (isset($ar_wc_active)){
                foreach ($ar_cat_list as $k=>$v){
                    $ar_model_list_1 = array();
                    $ar_model_list_1 = wc_get_term_product_ids( $v, 'product_cat' );
                    $ar_model_list = array_merge($ar_model_list,$ar_model_list_1);
                }
            }
            if (count($ar_model_list)==0){
                // no posts found
                return 'no models found';
            }
            foreach (array_unique($ar_model_list) as $k => $v){
                $this->model_array['ar_model_list'][] = preg_replace("/[^0-9]/", "",$v);
            }
            if (isset($ar_model_list[0])){
                $atts['id'] = $ar_model_list[0];
                $this->model_id = $ar_model_list[0];
            } else { 
                return 'no models found';
            }
        } else {
            $this->model_id = $atts['id'];
            extract(get_screen_type());

            //If Alternative mobile ID exists then display mobile model if viewing on mobile or tablet
            if ($mob_id = get_post_meta($atts['id'], '_ar_mobile_id', true )){
                
                if(($isMob) OR ($isTab) OR ($isIPhone) OR ($isIPad) OR ($isAndroid)){  
                    $atts['id'] = $mob_id;
                }
            }
       

            $ar_model_list=explode(',',$atts['id']);
        }
            foreach ($ar_model_list as $k => $v){
                $model_array['ar_model_list'][] = preg_replace("/[^0-9]/", "",$v);
            }
            $atts['id'] = $ar_model_list[0];
            // Check if WooCommerce is active
            if (function_exists('is_product') && is_product()) {
            
                // Get the global WooCommerce product object
                global $product;
            
                // Check if the product object exists and is a variable product
                if ($product && $product->is_type('variable')) {
            
                    // Get an array of variation IDs for the variable product
                    $variation_ids = $product->get_children();
            
                    foreach (array_unique($variation_ids) as $k => $v){
                     $model_array['ar_model_list'][] = preg_replace("/[^0-9]/", "",$v);
                    }
                } 
            } 
        //}
        $this->attributes = $atts;
        $this->variation_id = $variation_id;
        $this->model_array = $model_array;
        $this->get_model_array();
    }

    /**
     * Get model array from atts.
     */
    public function get_model_array() {
        $atts = $this->attributes;
       
        $suffix = ''; //change later for variations
        $arpost = get_post( $atts['id'] ); 
        if (isset($arpost->post_type)){
            if($arpost->post_type == 'product_variation'){
                $variation_id = $atts['id'];
                $this->variation_id = $variation_id;
                $suffix = $this->variation_id ? "_var_".$this->variation_id : '';
            }
        }

        $model_array['id'] = $atts['id'];
        $model_array['model_id'] = $this->model_id;
        $model_array['variation_id'] = $this->variation_id;        
        $post_meta_list = [
            '_usdz_file',
            '_glb_file',
            '_ar_variants',
            '_ar_rotate',
            '_ar_prompt',
            '_ar_x',
            '_ar_y',
            '_ar_z',
            '_ar_field_of_view',
            '_ar_zoom_out',
            '_ar_zoom_in',
            '_ar_resizing',
            '_ar_view_hide',
            '_ar_autoplay',
            '_ar_disable_zoom',
            '_ar_rotate_limit',
            '_ar_compass_top_value',
            '_ar_compass_bottom_value',
            '_ar_compass_left_value',
            '_ar_compass_right_value',
            '_ar_animation',
            '_ar_animation_selection',
            '_skybox_file',
            '_ar_hide_dimensions',
            '_ar_exposure',
            '_ar_framed',
            '_ar_frame_color',
            '_ar_frame_opacity',
            '_ar_shadow_intensity',
            '_ar_shadow_softness',
            '_ar_camera_orbit',
            '_ar_environment_image',
            '_ar_emissive',
            '_ar_light_color',
            '_ar_hotspots',
            '_ar_cta',
            '_ar_cta_url',
            '_ar_qr_destination_mv',
            '_ar_css_override'
        ];
        
        // Loop through the post_meta_list array and populate $model_array
        foreach ($post_meta_list as $meta_key) {
            $key = ltrim($meta_key, '_'); // Remove the leading underscore to use as the key in $model_array
            $model_array[$key] = get_post_meta($atts['id'], $meta_key . $suffix, true);
        }
        
        
        $ar_model_settings = [
            'ar_dimensions_inches',
            'ar_hide_arview',
            'ar_scene_viewer',
            'ar_view_file',
            'ar_qr_file',
            'ar_view_in_ar',
            'ar_view_in_3d',
            'ar_dimensions_units',
            'ar_dimensions_label',
            'ar_fullscreen_file',
            'ar_play_file',
            'ar_pause_file'
        ];
        
        // Loop through the ar_model_settings array and populate $model_array
        foreach ($ar_model_settings as $setting_key) {
            $model_array[$setting_key] = get_option($setting_key);
        }
        
        if (isset($atts['ar_hide_model'])){
            $model_array['ar_hide_model'] = $atts['ar_hide_model'];
        }else{
            $model_array['ar_hide_model'] = '';
        }

        if ($model_array['ar_hide_dimensions']==''){
            $model_array['ar_hide_dimensions']=get_option('ar_hide_dimensions');
        }
        if (isset($atts['hide_qr'])){
            $model_array['ar_hide_qrcode']=1;
        }else{
            $model_array['ar_hide_qrcode']=get_option('ar_hide_qrcode');
        }
        if (isset($atts['hide_reset'])){
            $model_array['ar_hide_reset']=1;
        }else{
            $model_array['ar_hide_reset']=get_option('ar_hide_reset');
        }
        
        if (!isset($atts['ar_enable_fullscreen'])){
            $model_array['ar_hide_fullscreen']=get_option('ar_hide_fullscreen');
        }
        if (isset($atts['ar_show_close_on_devices'])){
            $model_array['ar_show_close_on_devices']=$atts['ar_show_close_on_devices'];
        }
        if (!isset($model_array['ar_qr_destination'])){
            $model_array['ar_qr_destination']=get_option('ar_qr_destination');
        }
        if (get_post_meta( $atts['id'], '_ar_qr_destination', true )){
                $model_array['ar_qr_destination']=get_post_meta( $atts['id'], '_ar_qr_destination'.$suffix, true );
        }

        
        if ((isset($ar_css_override)) AND (get_post_meta($atts['id'], '_ar_css_positions'.$suffix, true )!='')){
            $model_array['ar_css_positions']=get_post_meta($atts['id'], '_ar_css_positions'.$suffix, true );
        }else{
            $model_array['ar_css_positions']=get_option('ar_css_positions');
        }
        if ((isset($ar_css_override)) AND (get_post_meta($atts['id'], '_ar_css'.$suffix, true )!='')){
            $model_array['ar_css']=get_post_meta($atts['id'], '_ar_css'.$suffix, true );
        }else{
            $model_array['ar_css']=get_option('ar_css');
        }
        $model_array['ar_pop']='';
        if (get_option('ar_open_tabs_remember')!=1){ 
            $model_array['ar_open_tabs'] = get_option('ar_open_tabs');
        }else{
            $model_array['ar_open_tabs'] = '';
        }
        
        if ($model_array['ar_hide_arview']==''){
            if (get_post_meta($atts['id'], '_ar_view_hide'.$suffix, true )!=''){
                $model_array['ar_hide_arview'] = '1';
            }
        }
        
        if ($model_array['ar_hide_qrcode']==''){
            if (get_post_meta($atts['id'], '_ar_qr_hide'.$suffix, true )!=''){
                $model_array['ar_hide_qrcode'] = '1';
            }
        }
        if ($model_array['ar_hide_reset']==''){
            if (get_post_meta($atts['id'], '_ar_hide_reset'.$suffix, true )!=''){
                $model_array['ar_hide_reset'] = '1';
            }
        }
        
        if (get_post_meta( $atts['id'], '_ar_placement'.$suffix, true )=='wall'){
            $model_array['ar_placement']='ar-placement="wall"';
        }else{
            $model_array['ar_placement']='';
        }
        if (get_post_meta( $atts['id'], '_ar_environment'.$suffix, true )){
            $model_array['ar_environment']='environment-image="'.get_post_meta( $atts['id'], '_ar_environment'.$suffix, true ).'"';
        }else{
            $model_array['ar_environment']='';
        }
        if (get_post_meta( $atts['id'], '_ar_qr_image'.$suffix, true )){
            $model_array['ar_qr_image']=get_post_meta( $atts['id'], '_ar_qr_image'.$suffix, true ).'"';
        }
        
        /*Add https to http urls before displaying*/
        $ar_ssl_urls=array('usdz_file','glb_file','skybox_file','ar_environment','ar_qr_image');
        foreach ($ar_ssl_urls as $k=>$url){
            if ( isset( $model_array[$url] ) ) {
                if (substr(sanitize_text_field( $model_array[$url] ),0,7)=='http://'){
                    $model_array[$url] = 'https://'.substr(sanitize_text_field( $model_array[$url] ),7);
                }
            }
        }

        //alternative ar model view
        $model_array['ar_alternative_id'] = 0;//print_r($model_array);   
        $alt_output = '';
        
        if ($ar_alternative_id = get_post_meta($this->model_id, '_ar_alternative_id', true )){
            $model_array['ar_alternative_id'] = $ar_alternative_id;                
        }

        $model_array['ar_model_atts'] = $atts;

        $this->model_array = array_merge($this->model_array,$model_array);

    }
}	
