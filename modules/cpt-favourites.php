<?php
// Register Custom Post Type
function wp4pm_cpt_favourite() {

	$labels = array(
		'name'                  => _x( 'Favourites', 'Post Type General Name', 'supernova' ),
		'singular_name'         => _x( 'Favourite', 'Post Type Singular Name', 'supernova' ),
		'menu_name'             => __( 'Favourites', 'supernova' ),
		'name_admin_bar'        => __( 'Favourite', 'supernova' ),
		'archives'              => __( 'Favourite Archives', 'supernova' ),
		'attributes'            => __( 'Favourite Attributes', 'supernova' ),
		'parent_item_colon'     => __( 'Parent Favourite:', 'supernova' ),
		'all_items'             => __( 'All Favourites', 'supernova' ),
		'add_new_item'          => __( 'Add New Favourite', 'supernova' ),
		'add_new'               => __( 'Add New', 'supernova' ),
		'new_item'              => __( 'New Favourite', 'supernova' ),
		'edit_item'             => __( 'Edit Favourite', 'supernova' ),
		'update_item'           => __( 'Update Favourite', 'supernova' ),
		'view_item'             => __( 'View Favourite', 'supernova' ),
		'view_items'            => __( 'View Favourites', 'supernova' ),
		'search_items'          => __( 'Search Favourite', 'supernova' ),
		'not_found'             => __( 'Not found', 'supernova' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'supernova' ),
		'featured_image'        => __( 'Featured Image', 'supernova' ),
		'set_featured_image'    => __( 'Set featured image', 'supernova' ),
		'remove_featured_image' => __( 'Remove featured image', 'supernova' ),
		'use_featured_image'    => __( 'Use as featured image', 'supernova' ),
		'insert_into_item'      => __( 'Insert into favourite', 'supernova' ),
		'uploaded_to_this_item' => __( 'Uploaded to this favourite', 'supernova' ),
		'items_list'            => __( 'Favourites list', 'supernova' ),
		'items_list_navigation' => __( 'Favourites list navigation', 'supernova' ),
		'filter_items_list'     => __( 'Filter favourites list', 'supernova' ),
	);
	$args = array(
		'label'                 => __( 'Favourite', 'supernova' ),
		'description'           => __( 'Favourite Property', 'supernova' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'custom-fields' ),
		'hierarchical'          => false,
		'public'                => false,
		'show_ui'               => false,
		'show_in_menu'          => false,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-star-filled',
		'show_in_admin_bar'     => false,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => true,
		'rewrite'               => false,
		'capability_type'       => 'page',
		'show_in_rest'          => true,
	);
	register_post_type( 'favourite', $args );

}
add_action( 'init', 'wp4pm_cpt_favourite', 0 );
