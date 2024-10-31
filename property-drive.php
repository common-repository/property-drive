<?php
/**
 * Plugin Name: Property Drive
 * Plugin URI: https://www.4property.com/
 * Description: Property management system for WordPress. Add properties, search, display, filter, sort and map.
 * Version: 1.1.2
 * Author: 4Property
 * Author URI: https://www.4property.com/
 * License: GNU General Public License v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

define( 'WPPD_VERSION', '1.1.2' );
define( 'WPPD_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Initialize plugin
 */
require_once 'includes/setup_plugin_options.php';

/**
 * Activation/deactivation routines
 */
register_activation_hook( __FILE__, 'wppd_on_activation' );

// Build Admin Area
require_once 'includes/admin/build_admin_page.php';

// Helper Functions
require_once 'includes/helpers.php';
require_once 'includes/meta.php';

/**
 * Generic property grid shortcode
 *
 * Can be set to show featured properties, sold and more.
 */
require_once 'shortcodes/property-grid.php';
require_once 'shortcodes/property-map.php';

// Pages & Snippets
require_once 'includes/inline_styling.php';
require_once 'includes/property_search.php';

// Shortcodes
require_once 'shortcodes/search-form.php';
require_once 'shortcodes/search-form-results.php';

require_once 'templates/template-parts.php';
require_once 'templates/part-sidebar-classic.php';

require_once 'modules/init.php';

function wp4pm_enqueue_scripts() {
    wp_register_script( 'foopicker', plugins_url( '/assets/js/foopicker.min.js', __FILE__ ), [], '0.3.4', true );

    // tail.select
    wp_enqueue_style( 'tail.select', plugins_url( '/assets/css/tail.select.min.css', __FILE__ ), [], '0.5.16' );
    wp_enqueue_script( 'tail.select', plugins_url( '/assets/js/tail.select.min.js', __FILE__ ), [], '0.5.16', true );
    //

    wp_enqueue_style( 'wp4pm-ui', plugins_url( '/assets/css/ui.css', __FILE__ ), [], WPPD_VERSION );

    if ( (string) get_option( 'map_provider' ) === 'osm' && ! is_front_page() ) {
        wp_enqueue_style( 'leaflet', plugins_url( '/assets/js/leaflet/leaflet.css', __FILE__ ), [], '1.5.1' );
        wp_enqueue_script( 'leaflet', plugins_url( '/assets/js/leaflet/leaflet.js', __FILE__ ), [], '1.5.1', true );
        wp_enqueue_script( 'leaflet-bundle', plugins_url( '/assets/js/leaflet-bundle.min.js', __FILE__ ), [ 'leaflet' ], '1.0.0', true );
    }

    if ( (int) get_option( 'use_single_content_accordion' ) === 1 ) {
        wp_enqueue_style( 'wp4pm-ui-accordion', plugins_url( '/assets/css/ui-accordion.css', __FILE__ ), [], WPPD_VERSION );
        wp_enqueue_script( 'wp4pm-init-accordion', plugins_url( '/assets/js/init-accordion.js', __FILE__ ), [], WPPD_VERSION, true );
    }

    wp_enqueue_style( 'ui-custom' );

    wp_enqueue_script( 'wp4pm-init', plugins_url( '/assets/js/init.js', __FILE__ ), [], WPPD_VERSION, true );
    wp_localize_script(
        'wp4pm-init',
        'wp4pmAjaxVar',
        [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
        ]
    );
}

function wp4pm_admin_enqueue_scripts() {
    wp_enqueue_style( 'wp-color-picker' );

    wp_enqueue_style( 'admin-ui', plugins_url( 'assets/css/ui-admin.css', __FILE__ ) );

    wp_enqueue_script( 'sortable', plugins_url( 'assets/js/Sortable.min.js', __FILE__ ), [], '1.7.0', true );

    wp_enqueue_script( 'jtg-admin-ui-js', plugins_url( 'assets/js/admin_ui.js', __FILE__ ), [ 'sortable', 'wp-color-picker', 'jquery' ], '', true );
    wp_localize_script(
        'jtg-admin-ui-js',
        'wp4pmAjaxVar',
        [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
        ]
    );
}

add_action( 'wp_enqueue_scripts', 'wp4pm_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'wp4pm_admin_enqueue_scripts' );



/**
 * Add noindex for sold/let properties
 */
add_action(
    'wp_head',
    function() {
        if ( (int) get_option( 'inactive_not_clickable' ) === 1 ) {
            global $post;

            $property_status = get_post_meta( $post->ID, 'property_status', true );

            if ( sanitize_title( $property_status ) === 'sold' || sanitize_title( $property_status ) === 'has-been-let' ) {
                echo '<meta name="robots" content="noindex, nofollow">';
            }
        }
    }
);



function wp4pm_get_property_box( $property_id, $grid_type = '' ) {
    /**
     * Property box/card (inside grid)
     */
    $property_details    = get_post_meta( $property_id );
    $location            = $property_details['latitude'][0] . '|' . $property_details['longitude'][0];
    $property_card_class = wp4pm_get_property_card_class( $property_id );

    $out = '<div class="property-card ' . $property_card_class . '" data-pid="' . $property_id . '" data-uri="' . get_permalink( $property_id ) . '" data-coordinates="' . $location . '|' . $property_id . '">
        <div class="jtg-box-image property-card--image">';

            // Thumbnail overlays
            $out .= wp4pm_get_property_status_ribbon( $property_id );
            $out .= wp4pm_get_property_image_count( $property_id, true );
            $out .= wp4pm_get_favourite_icon( $property_id );

            // Thumbnail
            $out .= wp4pm_get_property_thumbnail( $property_id );

        $out .= '</div>

        <div class="jtg-box property-card--box">
            <div class="property-card--title">' . wp4pm_get_property_title( $property_id );

            if ( wp4pm_get_property_type( $property_id ) === 'Parking Space' ) {
                $out .= '<div class="property-card--subtype"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="parking" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-parking fa-w-14 fa-fw"><path fill="currentColor" d="M400 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zM240 320h-48v48c0 8.8-7.2 16-16 16h-32c-8.8 0-16-7.2-16-16V144c0-8.8 7.2-16 16-16h96c52.9 0 96 43.1 96 96s-43.1 96-96 96zm0-128h-48v64h48c17.6 0 32-14.4 32-32s-14.4-32-32-32z" class=""></path></svg> ' . wp4pm_get_property_type( $property_id ) . '</div>';
            } else {
                $out .= '<div class="property-card--subtype">' . wp4pm_get_property_living_type( $property_id ) . wp4pm_get_property_type( $property_id ) . '</div>';
            }

            $out .= '</div>';

            $out .= wp4pm_get_property_status_badge( $property_id );
            $out .= wp4pm_get_property_description( $property_id );

            $out .= '<div class="property-card--details">
                <div class="property-card--details-left">
                    <span class="property-card--price">' . wp4pm_get_property_price( $property_id ) . '</span><span class="property-card--price-term">' . wp4pm_get_property_price_term( $property_id ) . '</span>
                </div>

                <div class="property-card--details-right">
                    <span class="property-card--bedrooms">' . wp4pm_get_property_bedrooms( $property_id, true ) . '</span>
                    <span class="property-card--bathrooms">' . wp4pm_get_property_bathrooms( $property_id, true ) . '</span>
                    <span class="property-card--ber">' . wp4pm_get_property_ber( $property_id ) . '</span>
                </div>
            </div>
        </div>
    </div>';

    return $out;
}
