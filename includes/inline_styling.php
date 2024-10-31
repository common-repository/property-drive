<?php
function wp4pm_add_plugin_styles() {
    $ribbonColourSale = get_option('ribbon_colour_sale');
    $ribbonColourSaleAgreed = get_option('ribbon_colour_sale_agreed');
    $ribbonColourSold = get_option('ribbon_colour_sold');

    $css = ':root {
        --badge_for_sale: ' . $ribbonColourSale . ';
        --badge_sale_agreed: ' . $ribbonColourSaleAgreed . ';
        --badge_sold: ' . $ribbonColourSold . ';
    }';

    wp_register_style('ui-custom', false);
    wp_add_inline_style('ui-custom', $css);
}
add_action('wp_enqueue_scripts', 'wp4pm_add_plugin_styles');
