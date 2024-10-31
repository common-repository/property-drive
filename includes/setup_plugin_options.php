<?php
/**
 * Build property custom post type
 *
 * Builds the property custom post type with all settings and features.
 */
function wppd_build_property_admin() {
    $labels = [
		'name'                  => _x('Properties', 'Post Type General Name', 'property-drive'),
		'singular_name'         => _x('Property', 'Post Type Singular Name', 'property-drive'),
		'menu_name'             => __('Properties', 'property-drive'),
		'name_admin_bar'        => __('Property', 'property-drive'),
		'archives'              => __('Property Archives', 'property-drive'),
		'attributes'            => __('Property Attributes', 'property-drive'),
		'parent_item_colon'     => __('Parent Property:', 'property-drive'),
		'all_items'             => __('All Properties', 'property-drive'),
		'add_new_item'          => __('Add New Property', 'property-drive'),
		'add_new'               => __('Add New', 'property-drive'),
		'new_item'              => __('New Property', 'property-drive'),
		'edit_item'             => __('Edit Property', 'property-drive'),
		'update_item'           => __('Update Property', 'property-drive'),
		'view_item'             => __('View Property', 'property-drive'),
		'view_items'            => __('View Properties', 'property-drive'),
		'search_items'          => __('Search Property', 'property-drive'),
		'not_found'             => __('Not found', 'property-drive'),
		'not_found_in_trash'    => __('Not found in Trash', 'property-drive'),
		'featured_image'        => __('Featured Image', 'property-drive'),
		'set_featured_image'    => __('Set featured image', 'property-drive'),
		'remove_featured_image' => __('Remove featured image', 'property-drive'),
		'use_featured_image'    => __('Use as featured image', 'property-drive'),
		'insert_into_item'      => __('Insert into property', 'property-drive'),
		'uploaded_to_this_item' => __('Uploaded to this property', 'property-drive'),
		'items_list'            => __('Properties list', 'property-drive'),
		'items_list_navigation' => __('Properties list navigation', 'property-drive'),
		'filter_items_list'     => __('Filter properties list', 'property-drive'),
	];
	$args = [
		'label'                 => __('Property', 'wp-property-drive'),
		'description'           => __('A single Property Drive property', 'wp-property-drive'),
		'labels'                => $labels,
		'supports'              => ['title', 'editor', 'thumbnail'],
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-admin-home',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
        'show_in_rest'          => false,
        'rewrite'               => ['slug' => 'property', 'with_front' => true],
	];
	register_post_type('property', $args);
}
add_action('init', 'wppd_build_property_admin', 0);



/**
 * Build property taxonomies
 *
 * Builds the property taxonomies with all settings and features.
 */
function wppd_setup_taxonomies_data() {
    return [
        'property_type' => [
            'post_type' => 'property',
            'args' => [
                'hierarchical' => true,
                'label' => 'Types',
                'labels' => [
                    'menu_name' => 'Types'
                ],
                'rewrite' => [
                    'slug' => 'property_type'
                ]
            ]
        ],
        'property_county' => [
            'post_type' => 'property',
            'args' => [
                'hierarchical' => true,
                'label' => 'County',
                'labels' => [
                    'menu_name' => 'County'
                ],
                'rewrite' => [
                    'slug' => 'property_county'
                ]
            ]
        ],
        'property_area' => [
            'post_type' => 'property',
            'args' => [
                'hierarchical' => true,
                'label' => 'Area',
                'labels' => [
                    'menu_name' => 'Area'
                ],
                'rewrite' => [
                    'slug' => 'property_area'
                ]
            ]
        ]
    ];
}

function wppd_setup_taxonomies() {
    $importerTaxonomies = wppd_setup_taxonomies_data();

    foreach ($importerTaxonomies as $taxonomy => $options) {
        register_taxonomy($taxonomy, $options['post_type'], $options['args']);
    }
}

add_action('init', 'wppd_setup_taxonomies');



function wppd_on_activation() {
    add_option('search_field_array', 'group|type|status|status_group|price|beds|baths|keyword|location|multitype|features');

    add_option('search_field_group', 0);
    add_option('search_field_type', 0);
    add_option('search_field_status', 0);
    add_option('search_field_price', 0);
    add_option('search_field_beds', 0);
    add_option('search_field_baths', 0);
    add_option('search_field_keyword', 1);
    add_option('search_field_multitype', 0);
    add_option('search_field_location', 0);
    add_option('search_field_features', 0);

    add_option('ribbon_colour_sale', '#1abc9c');
    add_option('ribbon_colour_sale_agreed', '#e67e22');
    add_option('ribbon_colour_sold', '#e74c3c');

    add_option('agency_name', 'Example Agency');
    add_option('agency_email', 'name@example.com');
    add_option('agency_phone', '01 234 5678');

    add_option('jtg_currency', 'euro');
    add_option('jtg_email_alert_frequency', 'weekly');
    add_option('jtg_auto_select_county', 'no-auto-select');
    add_option('map_provider', 'osm');
    add_option('osm_scrollzoom', 1);

    add_option('allow_quick_contact', 1);
    add_option('allow_favourites', 0);
    add_option('show_related_properties', 1);

    add_option('cinematic_overlay', 0);

    add_option('show_status_badge', 1);
    add_option('show_property_card_description', 1);

    add_option('use_single_sidebar', 0);
    add_option('use_single_content_accordion', 0);

    // Clean up routine
    // Remove in v1.2
    delete_option('show_sidebar_login');
    delete_option('grid_columns');
    delete_option('show_status_ribbons');
    delete_option('jtg_auto_select_county');
    delete_option('search_field_county');
    delete_option('search_field_area');
    delete_option('hide_modules');

    delete_option('feature_order_price');
    delete_option('feature_order_type');
    delete_option('feature_order_status');
    delete_option('feature_order_bedrooms');
    delete_option('feature_order_bathrooms');
    delete_option('feature_order_ber');
    delete_option('feature_order_brochure');
    delete_option('feature_order_size');
    delete_option('feature_order_floorplan');
    delete_option('feature_order_video');
    delete_option('feature_order_location');

    if (get_role('property_user')) {
        remove_role('property_user');
    }
}
