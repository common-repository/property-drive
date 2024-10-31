<?php
function wp4pm_property_map($atts) {
    $attributes = shortcode_atts([
        'status' => '',
        'type' => '',
        'property-type' => '',
        'property-type-single' => '',
        'market' => '',
        'ignore' => '',
        'count' => -1,
        'columns' => 3,
        'class' => '',
        'in' => '',
        'grid-type' => '',
        'category' => '',
        'showcount' => 'no',
        'exclude_children' => 'no'
    ], $atts);

    global $wp_query;

    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    $queryArgsMeta = [
        'posts_per_page' => (int) $attributes['count'],
        'post_type' => 'property',
        'post_status' => 'publish',
        'suppress_filters' => true,
        'paged' => $paged,
    ];

    if (!empty($attributes['in'])) {
        $postIn = explode(',', $attributes['in']);

        $queryArgsMeta['post__in'] = $postIn;
    }
    if (!empty($attributes['author'])) {
        $author = (int) $attributes['author'];

        $queryArgsMeta['meta_query'] = [
            [
                'key' => 'agent_id',
                'value' => $author,
                'compare' => '='
            ]
        ];
    }

    if ((string) trim($attributes['type']) === 'featured') {
        $queryArgsMeta['meta_query'] = [
            [
                'key' => 'is_featured',
                'value' => 'true',
                'compare' => '='
            ]
        ];
    } else if ((string) trim($attributes['type']) === 'auction') {
        $queryArgsMeta['meta_query'] = [
            [
                'key' => 'selling_type',
                'value' => 'Auction',
                'compare' => '='
            ]
        ];
    } else if ((string) trim($attributes['type']) === 'period') {
        $queryArgsMeta['meta_query'] = [
            [
                'key' => 'living_type',
                'value' => 'Period',
                'compare' => '='
            ]
        ];
    } else if ((string) trim($attributes['category']) !== '') {
        $queryArgsMeta['meta_query'] = [
            [
                'key' => 'property_category',
                'value' => trim($attributes['category']),
                'compare' => '='
            ]
        ];
    } else if ((string) trim($attributes['type']) === 'farm') {
        $queryArgsMeta['tax_query'] = [
            [
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => [
                    'farm'
                ]
            ]
        ];
    } else if ((string) trim($attributes['type']) === 'agricultural') {
        $queryArgsMeta['tax_query'] = [
            [
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => [
                    'agricultural'
                ]
            ]
        ];
    } else if ((string) trim($attributes['type']) === 'development-site') {
        $queryArgsMeta['tax_query'] = [
            [
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => [
                    'development-site'
                ]
            ]
        ];
    } else if ((string) trim($attributes['type']) === 'new-developments') {
        $queryArgsMeta['meta_query'] = [
            [
                'key' => 'property_market',
                'value' => 'New Developments',
                'compare' => '='
            ]
        ];
    } else if ((string) trim($attributes['type']) === 'short-term-let') {
        $queryArgsMeta['meta_query'] = [
            [
                'key' => 'property_market',
                'value' => 'Short Term Let',
                'compare' => '='
            ]
        ];
    } else if ((string) trim($attributes['ignore']) === 'short-term-let') {
        $queryArgsMeta['meta_query'] = [
            [
                'key' => 'property_market',
                'value' => 'Short Term Let',
                'compare' => '!='
            ]
        ];
    }



    // Relationship is always AND
    $queryArgsMeta['tax_query'] = [
        'relation' => 'AND'
    ];

    // Query by type (single)
    if ((string) trim($attributes['property-type-single']) !== '') {
        $propertyType = array_map('trim', explode(',', $attributes['property-type-single']));

        array_push($queryArgsMeta['tax_query'], [
            'taxonomy' => 'property_type',
            'field' => 'slug',
            'terms' => $propertyType
        ]);
    }

    // Query by type (general)
    if ((string) trim($attributes['property-type']) !== '') {
        $propertyTypeArray = [];
        $propertyType = array_map('trim', explode(',', $attributes['property-type']));

        if (in_array('residential', $propertyType)) {
            $propertyTypeArray[] = [
                'house',
                'apartment',
                'flat',
                'studio',
                'duplex'
            ];
        }
        if (in_array('commercial', $propertyType)) {
            $propertyTypeArray[] = [
                'industrial',
                'industrial-distribution',
                'office',
                'retail',
                'restaurant',
                'warehouse',
                'block-multi-units',
                'other'
            ];
        }
        if (in_array('land', $propertyType)) {
            $propertyTypeArray[] = [
                'site',
                'development-site',
                'development-land',
                'site-individual',
                'agricultural',
                'farm'
            ];
        }

        $queryArgsMeta['meta_query'] = [
            [
                'key' => 'property_market',
                'value' => 'New Developments',
                'compare' => '!='
            ]
        ];

        if ((string) $attributes['exclude_children'] === 'yes') {
            $queryArgsMeta['meta_query'] = [
                [
                    'key' => 'parent_id',
                    'value' => '',
                    'compare' => '='
                ]
            ];
        }

        array_push($queryArgsMeta['tax_query'], [
            'taxonomy' => 'property_type',
            'field' => 'slug',
            'terms' => array_flatten($propertyTypeArray)
        ]);
    }

    // Query by status
    if ((string) trim($attributes['status']) !== '') {
        $status = str_replace('-', ' ', trim($attributes['status']));
        $status = ucwords($status);
        $status = array_map('trim', explode(',', $status));

        $queryArgsMeta['meta_query'][] = [
                'key' => 'property_status',
                'value' => $status,
                'compare' => 'IN',
        ];
    }



    if (isset($_GET['orderby'])) {
        $orderBy = (string) sanitize_text_field($_GET['orderby']);
        $order = (string) sanitize_text_field($_GET['order_direction']);

        if ($orderBy === 'price') {
            $queryArgsMeta['orderby'] = 'meta_value_num';
            $queryArgsMeta['meta_key'] = 'price';
            $queryArgsMeta['order'] = $order;
        } else if ($orderBy === 'bedrooms') {
            $queryArgsMeta['orderby'] = 'meta_value_num';
            $queryArgsMeta['meta_key'] = 'bedrooms';
            $queryArgsMeta['order'] = $order;
        } else if ($orderBy === 'date') {
            $queryArgsMeta['orderby'] = 'date';
            $queryArgsMeta['order'] = $order;
        } else {
            $queryArgsMeta['orderby'] = 'date';
            $queryArgsMeta['order'] = 'DESC';
        }
    } else {
        if ((string) get_option('default_property_order') === 'property_order') {
            $queryArgsMeta['meta_key'] = 'property_order';
            $queryArgsMeta['orderby'] = [
                'meta_value' => 'ASC',
                'date' => 'DESC'
            ];
        } else if ((string) get_option('default_property_order') === 'property_order_modified') {
            $queryArgsMeta['meta_query'] = [
                'relation' => 'AND',
                'property_order_query' => [
                    'key' => 'property_order'
                ],
                'date_modified_query' => [
                    'key' => 'date_modified'
                ]
            ];
            $queryArgsMeta['orderby'] = [
                'property_order_query' => 'ASC',
                'date_modified_query' => 'DESC'
            ];
        } else if ((string) get_option('default_property_order') === 'date_modified') {
            $queryArgsMeta['meta_key'] = 'date_modified';
            $queryArgsMeta['orderby'] = [
                'meta_value' => 'DESC'
            ];
        } else if ((string) get_option('default_property_order') === 'manual') {
            $queryArgsMeta['meta_key'] = 'property_order';
            $queryArgsMeta['orderby'] = [
                'meta_value' => 'ASC',
                'menu_order' => 'ASC'
            ];
        } else {
            $queryArgsMeta['meta_key'] = 'property_order';
            $queryArgsMeta['orderby'] = [
                'meta_value' => 'ASC',
                'date' => 'DESC'
            ];
        }
    }

    $query = new WP_Query($queryArgsMeta);

    $parentClass = 'grid--view';

    $out = '<div class="grid-pull-right">';
        if ((string) $attributes['showcount'] === 'yes') {
            $out .= '<small>
                <span class="grid-pull-element"><b>' . $query->found_posts . '</b> properties found</span>
            </small>';
        }
    $out .= '</div>';

    $out = '<div class="parent-flex flex-container-nowrap flex-container-column ' . $parentClass . ' grid--split-map supernova-fullwidth">
        <div class="child-flex">
            <div id="osm-map" class="properties-osm-map"></div>
        </div>

        <div class="child-flex">
            <div id="grid-view" class="supernova-map-search">
                <div class="' . $attributes['class'] . ' flex-grid property-grid">';
                    if ($query->have_posts()) {
                        while ($query->have_posts()) {
                            $query->the_post();

                            $propertyId = $query->post->ID;

                            $out .= wp4pm_get_property_box($propertyId, 'split-map');
                        }
                    }

                    $flexGridSize = '';
                    if ((int) get_option('flex_grid_size') > 0) {
                        $flexGridSize = (int) get_option('flex_grid_size');
                        $flexGridSize = floor(98/$flexGridSize);
                        $flexGridSize = 'style="flex-basis: ' . $flexGridSize . '%;"';
                    }

                    for ($i=1;$i<=5;$i++) {
                        $out .= '<div class="property-card flex-grid-item-blank" ' . $flexGridSize . '></div>';
                    }
                $out .= '</div>
            </div>
        </div>
    </div>';


    return $out;
}
add_shortcode('property-map', 'wp4pm_property_map');
