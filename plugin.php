<?php // @codingStandardsIgnoreLine
/**
 * Rank Math SEO Plugin - Extended.
 * Plugin Name:       Rank Math SEO - Extended
 * Version:           1.0.0
 * Plugin URI:        https://bagusp.com
 * Description:       Rank Math SEO Plugin - Extended.
 * Author:            Bagus Pribadi Setiawan
 * Author URI:        https://bagusp.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rank-math-ext
 * Domain Path:       /languages
 */

class Rank_Math_Extended {

    protected static $instance = null;

    protected $plugin_path;

    protected $rank_math_path;

    public static function get_instance() {
		if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        
		return self::$instance;
    }

    public function __construct() {
        register_activation_hook( __FILE__ , [ $this, 'activate' ] );

        // get path of current plugin
        $this->plugin_path = $plugin_path = plugin_dir_path( __FILE__ );

        #1 add cdn image functionality

        // add filter to override the configuration
        add_filter( 'rank_math/admin/options/options_sitemap_tabs', function( $tabs ) {
            $tabs[ 'general' ][ 'file' ] = $this->plugin_path . 'settings/general.php';
            return $tabs;
        } );

        // add filter to change the cdn image
        add_filter( 'rank_math/sitemap/xml_img_src', function( $src, $post ){
            $cdn_domain = \RankMath\Helper::get_settings( 'sitemap.cdn_domain' );
            
            // if cdn domain name is exist and valid url
            if ( !empty( $cdn_domain ) && wp_http_validate_url( $cdn_domain ) ) {

                // replace old domain with the new one
                $src = str_replace( home_url(), $cdn_domain, $src );
            }
            return $src;
        }, 10, 2);

        #2 add breadcrumb functionality

        // add filter to override the configuration
        add_filter( 'rank_math/admin/options/options_general_tabs', function( $tabs ) {
            $tabs[ 'breadcrumbs' ][ 'file' ] = $this->plugin_path . 'settings/breadcrumbs.php';
            return $tabs;
        } );

        add_action( 'template_redirect', function() {
            ob_start( function( $buffer ) {
                $selector = \RankMath\Helper::get_settings( 'general.breadcrumbs_inject_after' );
                if ( !empty( $selector ) ) {
                    require_once $this->plugin_path . 'lib/simple_html_dom.php';

                    // cleanup string
                    $buffer = preg_replace( '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/', '', $buffer );
                    $buffer = preg_replace( '~[\r\n]+~', '', $buffer );
                    $buffer = str_replace( '> <', '><', $buffer );
    
                    // find selector
                    $html = str_get_html( $buffer );
                    $find = $html->find( $selector, 0 )->outertext;
    
                    if ( !is_null( $find ) ) {
                        // replace it
                        $buffer = str_replace( $find, $find . do_shortcode( '[rank_math_breadcrumb]' ), $buffer );
                    }
                }

                return $buffer;
            } );
        }, 0 );

        #3 add more schema info into post
        add_filter( 'rank_math/json_ld', function( $data ) {
            global $post;

            if ( $post->post_type == 'post' ) {
                // add more schema
                $data['NewSchema'] = [
                    '@type' => 'NewSchema',
                    'FieldName' => 'FieldValue'
                ];
            }

            return $data;
        }, 99 );
    }

    public function activate() {
        // get rank math path
        $rank_math = new \ReflectionClass( 'RankMath' );
        $this->rank_math_path = plugin_dir_path( $rank_math->getFileName() );

        $this->duplicate_sitemap_config();
        $this->duplicate_breadcrumb_config();
    }

    public function duplicate_sitemap_config() {
        // duplicate required files to inject
        $general_config_new = file_get_contents( $this->rank_math_path . 'includes/modules/sitemap/settings/general.php' );

        // inject with added new configuration
        $general_config_new .= str_replace( '<?php', '', file_get_contents( $this->plugin_path . 'inject/general.php' ) );

        // save the file
        file_put_contents( $this->plugin_path . 'settings/general.php', $general_config_new );
    }

    public function duplicate_breadcrumb_config() {
        // duplicate required files to inject
        $breadcrumb_config_new = file_get_contents( $this->rank_math_path . 'includes/settings/general/breadcrumbs.php' );

        // inject with added new configuration
        $breadcrumb_config_new .= str_replace( '<?php', '', file_get_contents( $this->plugin_path . 'inject/breadcrumbs.php' ) );

        // save the file
        file_put_contents( $this->plugin_path . 'settings/breadcrumbs.php', $breadcrumb_config_new );
    }
}

Rank_Math_Extended::get_instance();