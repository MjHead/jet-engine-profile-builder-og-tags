<?php
/**
 * Plugin Name: JetEngine - profile builder OG tags
 * Plugin URI:
 * Description: Allow to set OG tags for users pages
 * Version:     1.0.0
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

class JEPB_OG_Tags {

    private $user    = null;
    private $printed = [];
    private $tags    = [];
    
    public function __construct() {
        add_action( 'jet-engine/profile-builder/settings/tabs', [ $this, 'add_settings' ] );
        add_action( 'jet-engine/profile-builder/query/setup-props', [ $this, 'hook_tags' ] );
    }

    public function add_settings() {
        include plugin_dir_path( __FILE__ ) . 'templates/settings.php';
    }

    public function hook_tags( $query ) {

        $pagenow = get_query_var( Jet_Engine\Modules\Profile_Builder\Module::instance()->rewrite->page_var );

        if ( 'single_user_page' !== $pagenow ) {
            return;
        }

        $subpagenow = get_query_var( Jet_Engine\Modules\Profile_Builder\Module::instance()->rewrite->subpage_var );

        $pages = $this->settings()->get( $this->settings()->user_key, [] );

        if ( ! $subpagenow && ! empty( $pages ) ) {

            $pages      = array_values( $pages );
            $subpagenow = $pages[0]['slug'];

        }

        $tags       = $this->settings()->get( 'og_tags_list' );
        $this->user = $query->get_queried_user();

        $rewrite = $this->settings()->get( 'rewrite_og_' . $subpagenow );

        if ( $rewrite ) {
            $tags = $this->settings()->get( 'og_tags_' . $subpagenow );
        }

        if ( ! empty( $tags ) ) {
            $this->tags = $this->parse_tags( $tags );
            $this->rewrite_rank_math_tags();
            add_action( 'wp_head', [ $this, 'print_tags' ] );
        }

    }

    public function settings() {
        return Jet_Engine\Modules\Profile_Builder\Module::instance()->settings;
    }

    public function rewrite_rank_math_tags() {
        foreach ( $this->tags as $tag => $value ) {
            $og_tag = str_replace( ':', '_', $tag );
            add_filter( 'rank_math/opengraph/facebook/' . $og_tag, function( $content ) use ( $tag, $value ) {
                $this->printed[] = $tag;
                return $value;
            } );
        }
    }

    public function print_tags() {
        foreach ( $this->tags as $tag => $value ) {
            if ( ! in_array( $tag, $this->printed ) ) {
                printf( '<meta property="%s" content="%s"/>', $tag, $value );
            }
        }
    }

    public function parse_tags( $tags_string ) {
        
        $tags     = [];
        $raw_tags = preg_split( '/\r\n|\r|\n/', $tags_string );

        foreach ( $raw_tags as $tag_data ) {
            $tag_data = explode( '=', $tag_data );
            $tags[ $tag_data[0] ] = $this->prepare_value( $tag_data[1], $tag_data[0] );
        }

        return $tags;
    }

    public function prepare_value( $raw_value, $key ) {
        
        $values = explode( '+', $raw_value );
        $values = array_map( function( $single_value ) use ( $key ) {
            return $this->prepare_single_value( $single_value, $key );
        }, $values );

        return implode( '', $values );

    }

    public function prepare_single_value( $raw_value, $key ) {
       
        $value_data = explode( '.', $raw_value );
        $value_key  = $value_data[0];
        $value_prop = isset( $value_data[1] ) ? $value_data[1] : false;

        $result = null;

        switch ( $value_key ) {
            case 'user':
                if ( 'avatar' === $value_prop ) {
                    $result = get_avatar_url( $this->user->ID, array( 'size' => 450 ) );
                } else {
                    $result = $this->user->data->$value_prop;
                }
                break;
            
            case 'user_field':
                $result = get_user_meta( $this->user->ID, $value_prop, true );
                break;

            default:
                $result = $raw_value;
                break;
        }

        if ( 'og:image' === $key ) {
            $result = $this->ensure_image( $result );
        }

        return $result;

    }

    public function ensure_image( $value ) {

        if ( is_numeric( $value ) ) {
            return wp_get_attachment_image_url( $value, array( 1200, 600 ) );
        } elseif ( is_array( $value ) ) {
            if ( isset( $value['id'] ) ) {
                return wp_get_attachment_image_url( $value, array( 1200, 600 ) );
            } else {
                return $value[0];
            }
        } else {
            return $value;
        }

    }

}

new JEPB_OG_Tags();
