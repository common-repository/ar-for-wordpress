<?php
/**
 * AR Display
 * https://augmentedrealityplugins.com
**/

add_action('init', 'ar_secure_nonce');
function ar_secure_nonce() {
    if (isset($_POST['your_nonce_field'])) {
        // Verify nonce here
        if (!wp_verify_nonce($_GET['_wpnonce'] , 'ar_secure_nonce')) {
            // Nonce is invalid
            return;
        }
        // Continue with your logic
    }
}
// Hook to enqueue the CSS file properly
add_action( 'wp_enqueue_scripts', 'ar_enqueue_standalone_styles' );
if ( ! function_exists( 'ar_enqueue_standalone_styles' ) ) {
    function ar_enqueue_standalone_styles() {
        global $ar_plugin_id;
        $ar_display_css = '';
        $ar_display_custom_css = '';
        // Enqueue the stylesheet only if the page is meant to display the AR standalone content
        if ( isset( $_REQUEST['ar-view'] ) || isset( $_REQUEST['ar-cat'] ) ) {
            $ar_display_css = str_replace('includes/','', plugin_dir_url( __FILE__ ) . 'assets/css/ar-display.css');
            $ar_display_custom_css = str_replace('includes/','', plugin_dir_url( __FILE__ ) . 'assets/css/ar-display-custom.css');
            wp_enqueue_style( $ar_plugin_id . '-display', $ar_display_css, array(), '1.0.0', 'all' );
            wp_enqueue_style( $ar_plugin_id . '-display-custom', $ar_display_custom_css, array(), '1.0.0', 'all' );
            add_action( 'wp_head', 'ar_standalone' );
        }
    }
}

// AR View Standalone - Loads the AR Model viewer and triggers the AR view automatically
if ( ! function_exists( 'ar_standalone' ) ) {
    function ar_standalone() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'ar_secure_nonce' ) ) {
            // If the nonce is invalid, stop the process
            //wp_die( esc_html__( 'Security check failed.', 'ar-for-wordpress' ) );
        }

        global $_REQUEST;

        ?>
        <center><span id="ar_standalone_loading">Loading</span></center>
        <div id="ar_standalone_container" style="">
        <?php
        if ( isset( $_REQUEST['ar-view'] ) || isset( $_REQUEST['ar-cat'] ) ) {
            if ( ! empty( $_REQUEST['ar-view'] ) ) {
                $model_id = absint( $_REQUEST['ar-view'] );
                echo do_shortcode( '[ar-display id=\'' . $model_id . '\']' );
            } elseif ( ! empty( $_REQUEST['ar-cat'] ) ) {
                $cat_id = absint( $_REQUEST['ar-cat'] );
                echo do_shortcode( '[ar-display cat=\'' . $cat_id . '\']' );
            }
            ?>
            </div>
            <script>
                const modelViewer = document.getElementById("model_<?php echo esc_html($model_id); ?>");
                function checkagain() {
                    if (modelViewer && modelViewer.modelIsVisible === true) {
                        document.getElementById("ar-button_<?php echo esc_html($model_id); ?>").click();
                    } else {
                        setTimeout(ar_open, 2);
                    }
                }

                function ar_open() {
                    if (modelViewer && modelViewer.modelIsVisible === true) {
                        document.getElementById("ar-button_<?php echo esc_html($model_id); ?>").click();
                    } else {
                        setTimeout(checkagain, 2);
                    }
                } 

                modelViewer.addEventListener("load", function() {
                    ar_open();
                    document.getElementById("ar_standalone_loading").style.display = "none";
                    document.getElementById("ar_standalone_container").style.opacity = "100";
                });

                var count = 0;
                setInterval(function(){
                    count++;
                    document.getElementById('ar_standalone_loading').innerHTML = "Loading" + new Array(count % 5).join('.');
                }, 1000);
            </script>
            <?php
            echo '<center><a href="' . esc_url( get_site_url() ) . '"><button type="button" class="button">Return to Site</button></a></center>';
        }
        // Trigger the wp_footer action to load footer scripts and content
        wp_footer();
        // Stop all further WordPress output
        exit;
    }
}
