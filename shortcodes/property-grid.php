<?php
function wp4pm_property_grid($atts) {
    $attributes = shortcode_atts([
        'status' => '',
        'type' => '',
        'property-type' => '',
        'property-type-single' => '',
        'market' => '',
        'ignore' => '',
        'count' => 24,
        'columns' => 3,
        'pagination' => 'yes',
        'class' => '',
        'views' => 'no',
        'sort' => 'no',
        'in' => '',
        'author' => '',
        'grid-type' => '',
        'category' => '',
        'showcount' => 'no',
        'exclude_children' => 'no',
        'location' => '',
        'include-new-dev' => 'no'
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

    if (!empty($attributes['location'])) {
        $locationAreas = $attributes['location'];

        $queryArgsMeta['meta_query'] = [
            [
                'key' => 'area',
                'value' => $locationAreas,
                'compare' => 'IN'
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
        if (in_array('parking', $propertyType)) {
            $propertyTypeArray[] = [
                'parking-space'
            ];
        }

        if ((string) $attributes['include-new-dev'] === 'no') {
            $queryArgsMeta['meta_query'] = [
                [
                    'key' => 'property_market',
                    'value' => 'New Developments',
                    'compare' => '!='
                ]
            ];
        }

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
            'compare' => 'IN'
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
            $queryArgsMeta['meta_query'][] = [
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
    $mapClass = '';

    $viewsEnabled = (string) $attributes['views'];

    $out = '<div class="grid-pull-right">';
        if ((string) $attributes['showcount'] === 'yes') {
            $out .= '<small>
                <span class="grid-pull-element"><b>' . $query->found_posts . '</b> properties found</span>
            </small>';
        }

        if ((string) $attributes['sort'] === 'yes') {
            $out .= '<form class="wp4pm grid-inline-block" action="">
                <div>
                    <select name="order" id="pd_order" class="select-small">
                        <option>Order by...</option>
                        <option value="date|ASC">Date added (oldest to newest)</option>
                        <option value="date|DESC">Date added (newest to oldest)</option>
                        <option value="price|ASC">Price (ascending)</option>
                        <option value="price|DESC">Price (descending)</option>
                        <option value="bedrooms|ASC">Bedrooms (ascending)</option>
                        <option value="bedrooms|DESC">Bedrooms (descending)</option>
                    </select>
                </div>
            </form>';
        }

        if ((string) $attributes['views'] === 'yes') {
            $propertyMapPageUri = get_permalink((int) get_option('property_map_id'));

            $classAjax = '';
            if ((int) get_option('property_map_ajax') === 1) {
                $classAjax = 'supernova-view--map--ajax';
            }

            $out .= '<div class="supernova-grid-view-type is-grid-' . $attributes['grid-type'] . '">
                <span id="supernova-view--grid" tooltip="Grid View"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="th-large" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-th-large fa-w-16 fa-fw"><path fill="currentColor" d="M296 32h192c13.255 0 24 10.745 24 24v160c0 13.255-10.745 24-24 24H296c-13.255 0-24-10.745-24-24V56c0-13.255 10.745-24 24-24zm-80 0H24C10.745 32 0 42.745 0 56v160c0 13.255 10.745 24 24 24h192c13.255 0 24-10.745 24-24V56c0-13.255-10.745-24-24-24zM0 296v160c0 13.255 10.745 24 24 24h192c13.255 0 24-10.745 24-24V296c0-13.255-10.745-24-24-24H24c-13.255 0-24 10.745-24 24zm296 184h192c13.255 0 24-10.745 24-24V296c0-13.255-10.745-24-24-24H296c-13.255 0-24 10.745-24 24v160c0 13.255 10.745 24 24 24z" class=""></path></svg></span>
                <span id="supernova-view--list" tooltip="List View"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="th-list" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-th-list fa-w-16 fa-fw"><path fill="currentColor" d="M149.333 216v80c0 13.255-10.745 24-24 24H24c-13.255 0-24-10.745-24-24v-80c0-13.255 10.745-24 24-24h101.333c13.255 0 24 10.745 24 24zM0 376v80c0 13.255 10.745 24 24 24h101.333c13.255 0 24-10.745 24-24v-80c0-13.255-10.745-24-24-24H24c-13.255 0-24 10.745-24 24zM125.333 32H24C10.745 32 0 42.745 0 56v80c0 13.255 10.745 24 24 24h101.333c13.255 0 24-10.745 24-24V56c0-13.255-10.745-24-24-24zm80 448H488c13.255 0 24-10.745 24-24v-80c0-13.255-10.745-24-24-24H205.333c-13.255 0-24 10.745-24 24v80c0 13.255 10.745 24 24 24zm-24-424v80c0 13.255 10.745 24 24 24H488c13.255 0 24-10.745 24-24V56c0-13.255-10.745-24-24-24H205.333c-13.255 0-24 10.745-24 24zm24 264H488c13.255 0 24-10.745 24-24v-80c0-13.255-10.745-24-24-24H205.333c-13.255 0-24 10.745-24 24v80c0 13.255 10.745 24 24 24z" class=""></path></svg></span>
                <span id="supernova-view--map" tooltip="Map Navigator" class="' . $classAjax . '"><a href="' . $propertyMapPageUri . '"><svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="map-marker-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" class="svg-inline--fa fa-map-marker-alt fa-w-12 fa-fw"><path fill="currentColor" d="M192 0C85.903 0 0 86.014 0 192c0 71.117 23.991 93.341 151.271 297.424 18.785 30.119 62.694 30.083 81.457 0C360.075 285.234 384 263.103 384 192 384 85.903 297.986 0 192 0zm0 464C64.576 259.686 48 246.788 48 192c0-79.529 64.471-144 144-144s144 64.471 144 144c0 54.553-15.166 65.425-144 272zm-80-272c0-44.183 35.817-80 80-80s80 35.817 80 80-35.817 80-80 80-80-35.817-80-80z" class=""></path></svg></span>
            </div>';
        }
    $out .= '</div>';
    $out .= '<div id="map-ajax-container"><div class="map-ajax-close">&#10005;</div></div>';

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


    if ((string) trim($attributes['pagination']) === 'yes') {
        $out .= '<div class="pagination-wrap">';
            $out .= get_previous_posts_link('Previous');
            $out .= get_next_posts_link('Next', $query->max_num_pages);
        $out .= '</div>';
    }

    return $out;
}
add_shortcode('property-grid', 'wp4pm_property_grid');
