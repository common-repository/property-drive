<?php
/**
 * Display property search results
 *
 * @since 1.5.4
 * @return string
 */
function wp4pm_search_type_results($atts) {
    $attributes = shortcode_atts([
        'title' => '',
        'type' => '',
        'market' => '',
        'count' => 24,
        'columns' => 4,
        'pagination' => 'yes',
        'class' => '',
        'views' => 'no',
        'sort' => 'no',
        'in' => '',
        'hidesold' => 'no',
        'author' => '',
        'grid-type' => '',
        'showcount' => 'no'
    ], $atts);

    $args = [
        'post_status' => 'publish',
        'post_type' => 'property'
    ];

    /**
     * Check if this is a search
     */
    if (isset($_GET['property_type']) || isset($_GET['property_status']) || isset($_GET['group_type']) || isset($_GET['location']) || isset($_GET['property_keyword'])) {
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $meta_query = [];
        $tax_query = [];

        // Build search query arguments
        if (isset($_GET['group_type']) && (string) sanitize_text_field($_GET['group_type']) !== '') {
            if (sanitize_text_field($_GET['group_type']) === 'pm_commercial') {
                $tax_query[] = [[
                    'taxonomy' => 'property_type',
                    'field' => 'slug',
                    'terms' => [
                        'industrial',
                        'industrial-distribution',
                        'office',
                        'retail',
                        'warehouse',
                        'site',
                        'development-site',
                        'site-individual',
                        'agricultural',
                        'farm'
                    ]
                ]];
            } else if ((string) sanitize_text_field( $_GET['group_type'] ) === 'pm_residential') {
                $tax_query[] = [[
                    'taxonomy' => 'property_type',
                    'field' => 'slug',
                    'terms' => [
                        'house',
                        'apartment',
                        'flat',
                        'studio',
                        'duplex'
                    ]
                ]];
            } else if ((string) sanitize_text_field( $_GET['group_type'] ) === 'pm_land') {
                $tax_query[] = [[
                    'taxonomy' => 'property_type',
                    'field' => 'slug',
                    'terms' => [
                        'site',
                        'development-site',
                        'development-land',
                        'site-individual',
                        'agricultural',
                        'farm'
                    ]
                ]];
            }
        }

        if (isset($_GET['property_type']) && (string) sanitize_text_field($_GET['property_type']) !== '') {
            $tax_query[] = [[
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => sanitize_text_field($_GET['property_type'])
            ]];
        }

        if (isset($_GET['property_status']) && sanitize_text_field($_GET['property_status']) !== '') {
            $propertyStatusArray = array_map('trim', explode(',', sanitize_text_field($_GET['property_status'])));
            $propertyStatusArray = array_filter($propertyStatusArray);

            $meta_query[] = [
                'key' => 'property_status',
                'value' => $propertyStatusArray,
                'compare' => 'IN'
            ];
        }

        if (isset($_GET['location'])) {
            $locationAreas = sanitize_text_field($_GET['location']);

            $meta_query[] = [
                'key' => 'area',
                'value' => $locationAreas,
                'compare' => 'IN'
            ];
        }
        if (isset($_GET['t'])) {
            $multiType = sanitize_text_field($_GET['t']);
            $propertyType = $propertyLivingType = [];

            foreach ($multiType as $type) {
                $type = explode('|', $type);
                $propertyType[] = $type[0];
                $propertyLivingType[] = (!empty($type[1])) ?? '';
            }

            if (count(array_filter($propertyLivingType)) > 0) {
                $meta_query[] = [
                    'key' => 'living_type',
                    'value' => $propertyLivingType,
                    'compare' => 'IN'
                ];
            } else {
                $tax_query[] = [
                    [
                        'taxonomy' => 'property_type',
                        'field' => 'slug',
                        'terms' => $propertyType
                    ]
                ];
            }
        }

        if (isset($_GET['location_area'])) {
            $locationAreas = sanitize_text_field($_GET['location_area']);

            $meta_query[] = [
                'key' => 'area',
                'value' => $locationAreas,
                'compare' => 'IN'
            ];
        }

        $locationCounty = 0;

        if (isset($_GET['location_county']) && sanitize_text_field($_GET['location_county']) !== '') {
            $locationArray = [
                1 => 'Dublin',
                2 => 'Meath',
                3 => 'Kildare',
                4 => 'Wicklow',
                5 => 'Longford',
                6 => 'Offaly',
                7 => 'Westmeath',
                8 => 'Laois',
                9 => 'Louth',
                10 => 'Carlow',
                11 => 'Kilkenny',
                12 => 'Waterford',
                13 => 'Wexford',
                14 => 'Kerry',
                15 => 'Cork',
                16 => 'Clare',
                17 => 'Limerick',
                18 => 'Tipperary',
                19 => 'Galway',
                20 => 'Mayo',
                21 => 'Roscommon',
                22 => 'Sligo',
                23 => 'Leitrim',
                24 => 'Donegal',
                25 => 'Cavan',
                26 => 'Monaghan'
            ];

            $locationId = absint($_GET['location_county']);
            $locationCounty = (string) $locationArray[$locationId];

            $meta_query[] = [
                'key' => 'county',
                'value' => $locationCounty,
                'compare' => '='
            ];
        }

        if (isset($_GET['beds'])) {
            $meta_query[] = [
                'key' => 'bedrooms',
                'type' => 'NUMERIC',
                'value' => (int) $_GET['beds'],
                'compare' => '>='
            ];
        }
        if (isset($_GET['min_price']) || isset($_GET['max_price'])) {
            $minPrice = isset($_GET['min_price']) ? (int) $_GET['min_price'] : 0;
            $maxPrice = isset($_GET['max_price']) ? (int) $_GET['max_price'] : 10000000;

            $meta_query[] = [
                'key' => 'price',
                'value' => [(int) $minPrice, (int) $maxPrice],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            ];
        }

        $order_direction = 'ASC';
        if (isset($_GET['order_direction']) ) {
            if ($_GET['order_direction'] == 'ASC') {
                $order_direction = 'ASC';
            } else {
                $order_direction = 'DESC';
            }
        }

        if (isset($_GET['orderby']) && (string) $_GET['orderby'] === 'date') {
            $args = [
                'paged' => $paged,
                'posts_per_page' => $count,
                'post_status' => 'publish',
                'post_type' => 'property',
                'order' => $order_direction,
                'tax_query' => $tax_query,
                'meta_query' => $meta_query
            ];
        } else if (!$_GET) {
            $args = [
                'paged' => $paged,
                'posts_per_page' => $count,
                'post_status' => 'publish',
                'post_type' => 'property',
                'meta_key' => 'property_order',
                'orderby' => [
                    'meta_value_num' => 'ASC',
                    'date' => 'DESC'
                ]
            ];
        } else {
            $args = [
                'paged' => $paged,
                'posts_per_page' => $attributes['count'],
                'post_status' => 'publish',
                'post_type' => 'property',
                'meta_key' => 'property_order',
                'orderby' => [
                    'meta_value' => 'ASC',
                    'date' => 'DESC',
                ],
                'relation' => 'AND',
                'tax_query' => $tax_query,
                'meta_query' => $meta_query
            ];
        }

        if (isset($_GET['orderby'])) {
            $orderBy = (string) sanitize_text_field($_GET['orderby']);
            if ($orderBy === 'price') {
                $args['orderby'] = 'meta_value';
                $args['meta_key'] = 'price';
            } else if ($orderBy === 'beds') {
                $args['orderby'] = 'meta_value';
                $args['meta_key'] = 'beds';
            } else {
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
            }
        }

        if (isset($_GET['property_keyword']) && sanitize_text_field($_GET['property_keyword']) !== '') {
            $args['s'] = sanitize_text_field($_GET['property_keyword']);
        }
    } else {

    }

    $query = new WP_Query($args);

    $out = '<div class="grid-pull-right">';
        if ((string) $attributes['showcount'] === 'yes') {
            $out .= '<small>
                <span class="grid-pull-element"><b>' . $query->found_posts . '</b> properties found</span>
            </small>';
        }
    $out .= '</div>';

    $out .= '<div class="parent-flex flex-container-nowrap flex-container-column grid--view grid--' . $attributes['grid-type'] . '">
        <div class="' . $attributes['class'] . ' flex-grid property-grid">';
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();

                    $propertyId = $query->post->ID;

                    $out .= wp4pm_get_property_box($propertyId, $attributes['grid-type']);
                }
            }
            $out .= '<div class="property-card flex-grid-item-blank"></div>
            <div class="property-card flex-grid-item-blank"></div>
            <div class="property-card flex-grid-item-blank"></div>
            <div class="property-card flex-grid-item-blank"></div>
            <div class="property-card flex-grid-item-blank"></div>
        </div>
    </div>';


    if ($query->max_num_pages > 1) { // check if the max number of pages is greater than 1
        $out .= '<div class="search-no-results"><p>No properties found.</p></div>';
    }

    if ((string) trim($attributes['pagination']) === 'yes') {
        $out .= '<div class="pagination-wrap">';
            $out .= get_previous_posts_link('Previous');
            $out .= get_next_posts_link('Next', $query->max_num_pages);
        $out .= '</div>';
    }



    $out .= '<style>
    .prev-next-posts {
        width: 100%;
    }
    .prev-next-posts a {
        background-color: #000;
        color: #fff;
        padding: 10px 20px;
        font-weight: bold;
        text-decoration: none!important;
        margin-right: 5px;
        margin-bottom: 5px;
    }
    .prev-next-posts a:hover {
        color: #fff;
    }
    .prev-posts-link, .next-posts-link {
        display: inline-block;
    }
    </style>';
    /**/

    if ($query->max_num_pages > 1) { // check if the max number of pages is greater than 1
        $out .= '<div class="pd-bootstrap" style="margin-bottom: 50px; text-align: right">
            <div class="container">
                <nav class="prev-next-posts">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="next-posts-link">' . get_previous_posts_link('Previous') . '</div>
                            <div class="prev-posts-link">' . get_next_posts_link('Next', $query->max_num_pages) . '</div>
                        </div>
                    </div>
                </nav>
            </div>
        </div>';
    }
    wp_reset_postdata();

    $out .= '</div>';

    return $out;
}

add_shortcode('search-form-results', 'wp4pm_search_type_results');
