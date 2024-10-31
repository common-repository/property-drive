<?php
function wppd_show_property_images($propertyId) {
    $imageArray = get_post_meta($propertyId, 'detail_images_array', true);
    $imageArray = array_map('trim', explode(',', $imageArray));
    $imageArray = array_filter($imageArray);

    $out = '<div class="single-property-carousel single-property-carousel-main" data-flickity=\'{"pageDots": false, "wrapAround": true, "fullscreen": true, "imagesLoaded": true }\'>';
        foreach ($imageArray as $imageUri) {
            if (!empty($imageUri)) {
                $out .= '<img loading="lazy" src="' . $imageUri . '" alt="' . get_the_title($propertyId) . '">';
            }
        }
    $out .= '</div>';

    reset($imageArray);

    $out .= '<div class="single-property-carousel single-property-carousel-nav" data-flickity=\'{"asNavFor": ".single-property-carousel-main", "pageDots": false, "wrapAround": true, "imagesLoaded": true }\'>';
        foreach ($imageArray as $imageUri) {
            if (!empty($imageUri)) {
                $out .= '<img loading="lazy" src="' . $imageUri . '" alt="' . get_the_title($propertyId) . '">';
            }
        }
    $out .= '</div>';

    echo $out;
}



function wppd_add_var($url, $key, $value) {
    $url = preg_replace('/(.*)(?|&)'. $key .'=[^&]+?(&)(.*)/i', '$1$2$4', $url .'&');
    $url = substr($url, 0, -1);

    if (strpos($url, '?') === false) {
        return ($url .'?'. $key .'='. $value);
    } else {
        return ($url .'&'. $key .'='. $value);
    }
}
function wppd_remove_var($url, $key) {
    $url = preg_replace('/(.*)(?|&)'. $key .'=[^&]+?(&)(.*)/i', '$1$2$4', $url .'&');
    $url = substr($url, 0, -1);

    return ($url);
}

function wppd_get_thumbnail_url($postId) {
    /**
     * (PHP 7 >= 7.3.0)
     * array_key_first — Gets the first key of an array
     */
    if (has_post_thumbnail($postId)) {
        return get_the_post_thumbnail_url($postId);
    }

    $imageArray = get_post_meta($postId, 'detail_images_array', true);
    $imageArray = array_map('trim', explode(',', $imageArray));
    $imageArray = array_filter($imageArray);

    $firstImageUri = WP_PLUGIN_URL . '/property-drive/assets/images/no-image.jpg';

    if (count($imageArray) > 0) {
        $firstImageUri = $imageArray[0];

        $firstImageUri = wppd_remove_var($firstImageUri, 'w');
        $firstImageUri = wppd_remove_var($firstImageUri, 'h');
        $firstImageUri = wppd_add_var($firstImageUri, 'w', '480');
        $firstImageUri = wppd_add_var($firstImageUri, 'h', '320');
    }

    return $firstImageUri;
}



function wp4pm_get_property_card_class($propertyId) {
    $class = '';
    $flexGridSize = '';

    $propertyMarket = (string) get_post_meta($propertyId, 'property_market', true);
    $status = (string) get_post_meta($propertyId, 'property_status', true);

    if ($status === 'Sold' && $propertyMarket === 'New Developments') {
        $status = 'Sold Out';
    } else if (sanitize_title($status) === 'has-been-let') {
        $status = 'Let Agreed';
    }

    if ((int) get_option('flex_grid_size') > 0) {
        $flexGridSize = (int) get_option('flex_grid_size');
        $class .= 'property-card--has-' . $flexGridSize . '-columns ';
    }

    $class .= 'pid-' . $propertyId . ' ';
    $class .= 'status-' . sanitize_title($status) . ' ';
    $class .= 'has-coordinates ';

    return $class;
}



/**
 * Get property BER or combined BER (for new developments).
 *
 * @since 2.1.6
 *
 * @param  int    $propertyId Property ID.
 * @return string $out
 */
function wp4pm_get_property_ber($propertyId) {
    $berRating = (string) strtoupper(get_post_meta($propertyId, 'ber_rating', true));
    $linkedProperties = get_post_meta($propertyId, 'linked_properties', true);

    $berRatingClass = strtolower($berRating);

    $out = '<div class="bercrumb ber-request">
        <div class="ber-title"><span>BER</span></div>
        <div class="ber-value"><span>ON REQUEST</span></div>
    </div>';

    if (isset($linkedProperties) && (string) $linkedProperties !== '') {
        $out .= get_combined_property_ber($linkedProperties);
    } else if (in_array($berRating, ['A1', 'A2', 'A3', 'B1', 'B2', 'B3', 'C1', 'C2', 'C3', 'D1', 'D2', 'E1', 'E2', 'F', 'G'])) {
        $out = '<div class="bercrumb ber-' . $berRatingClass . '">
            <div class="ber-title"><span>BER</span></div>
            <div class="ber-value"><span>' . $berRating . '</span></div>
        </div>';
    } else if (strpos($berRating, '-') !== false) {
        // We have a range
        $berRatingClass = explode('-', $berRating);
        $berRatingClass = strtolower(trim($berRatingClass[0]));

        $out = '<div class="bercrumb ber-' . $berRatingClass . '">
            <div class="ber-title"><span>BER</span></div>
            <div class="ber-value"><span>' . $berRating . '</span></div>
        </div>';
    }

    return $out;
}



/**
 * Get combined property BER for new developments.
 *
 * @since 1.5.2
 *
 * @param  string $linkedProperties Linked property IDs.
 * @return string $out
 */
function get_combined_property_ber($linkedProperties) {
    if ((string) $linkedProperties !== '') {
        $linkedPropertiesArray = explode(',', $linkedProperties);
        $berArray = [];

        foreach ($linkedPropertiesArray as $linkedProperty) {
            $linkedPropertyArgs = [
                'post_type' => 'property',
                'posts_per_page' => 1,
                'meta_query' => [
                    [
                        'key' => 'importer_id',
						'value' => $linkedProperty,
                        'compare' => '='
                    ]
                ]
            ];
            $linkedPropertyObject = new WP_Query($linkedPropertyArgs);
            if ($linkedPropertyObject->have_posts()) {
                while ($linkedPropertyObject->have_posts()) {
                    $linkedPropertyObject->the_post();

                    if (!empty(get_post_meta($linkedPropertyObject->post->ID, 'ber_rating', true))) {
                        $berRating = get_post_meta($linkedPropertyObject->post->ID, 'ber_rating', true);
                        $berArray[] = $berRating;
                    }
                }
            }
        }
    }

    if (is_array($berArray)) {
        sort($berArray);
    }

	$out = '';

	if (count($berArray) === 0) {
		$out .= '';
	} else if (count($berArray) === 1) {
		$out .= '<div class="bercrumb ber-' . strtolower($berArray[0]) . '">
            <div><span>BER</span></div>
            <div><span>' . strtoupper($berArray[0]) . '</span></div>
        </div>';
	} else if (count($berArray) > 1) {
		$firstBer = reset($berArray);
		$lastBer = end($berArray);

        if ((string) $firstBer === (string) $lastBer) {
    		$out .= '<div class="bercrumb ber-' . strtolower($berArray[0]) . '">
                <div><span>BER</span></div>
                <div><span>' . strtoupper($firstBer) . '</span></div>
            </div>';
        } else {
            $out .= '<div class="bercrumb ber-' . strtolower($berArray[0]) . '">
                <div><span>BER</span></div>
                <div><span>' . strtoupper($firstBer) . '</span></div>
                <div><span>' . strtoupper($lastBer) . '</span></div>
            </div>';
        }
	}

    return $out;
}



/**
 * Get property bedrooms or combined bedrooms (for new developments).
 *
 * @since 2.1.6
 *
 * @param  int        $propertyId Property ID.
 * @param  bool       $icon       Value icon.
 * @return int|string $bedrooms
 */
function wp4pm_get_property_bedrooms($propertyId, $icon = false) {
    $bedrooms = (int) get_post_meta($propertyId, 'bedrooms', true);
    $linkedProperties = get_post_meta($propertyId, 'linked_properties', true);
    $propertyMarket = (string) get_post_meta($propertyId, 'property_market', true);

    $iconString = '';
    if ((bool) $icon === true) {
        $iconString = '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="bed" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" class="svg-inline--fa fa-bed fa-w-20 fa-fw"><path fill="currentColor" d="M176 256c44.11 0 80-35.89 80-80s-35.89-80-80-80-80 35.89-80 80 35.89 80 80 80zm352-128H304c-8.84 0-16 7.16-16 16v144H64V80c0-8.84-7.16-16-16-16H16C7.16 64 0 71.16 0 80v352c0 8.84 7.16 16 16 16h32c8.84 0 16-7.16 16-16v-48h512v48c0 8.84 7.16 16 16 16h32c8.84 0 16-7.16 16-16V240c0-61.86-50.14-112-112-112z" class=""></path></svg> ';
    }

    if (isset($propertyMarket) && $propertyMarket === 'New Developments' && (string) getLinkedPropertiesBedroomRange($linkedProperties) !== '') {
        return $iconString . getLinkedPropertiesBedroomRange($linkedProperties);
    } else if ($bedrooms > 0) {
        return $iconString . $bedrooms;
    }
}



/**
 * Get property bathrooms.
 *
 * @since 2.1.6
 *
 * @param  int  $propertyId Property ID.
 * @param  bool $icon       Value icon.
 * @return int  $bathrooms
 */
function wp4pm_get_property_bathrooms($propertyId, $icon = false) {
    $bathrooms = (int) get_post_meta($propertyId, 'bathrooms', true);

    $iconString = '';
    if ((bool) $icon === true) {
        $iconString = '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="bath" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-bath fa-w-16 fa-fw"><path fill="currentColor" d="M488 256H80V112c0-17.645 14.355-32 32-32 11.351 0 21.332 5.945 27.015 14.88-16.492 25.207-14.687 59.576 6.838 83.035-4.176 4.713-4.021 11.916.491 16.428l11.314 11.314c4.686 4.686 12.284 4.686 16.971 0l95.03-95.029c4.686-4.686 4.686-12.284 0-16.971l-11.314-11.314c-4.512-4.512-11.715-4.666-16.428-.491-17.949-16.469-42.294-21.429-64.178-15.365C163.281 45.667 139.212 32 112 32c-44.112 0-80 35.888-80 80v144h-8c-13.255 0-24 10.745-24 24v16c0 13.255 10.745 24 24 24h8v32c0 28.43 12.362 53.969 32 71.547V456c0 13.255 10.745 24 24 24h16c13.255 0 24-10.745 24-24v-8h256v8c0 13.255 10.745 24 24 24h16c13.255 0 24-10.745 24-24v-32.453c19.638-17.578 32-43.117 32-71.547v-32h8c13.255 0 24-10.745 24-24v-16c0-13.255-10.745-24-24-24z" class=""></path></svg> ';
    }

    if ($bathrooms > 0) {
        return $iconString . $bathrooms;
    }
}



/**
 * Get property price or combined price (for new developments).
 *
 * @since 2.1.6
 *
 * @param  int    $propertyId Property ID.
 * @return string $price
 */
function wp4pm_get_property_price($propertyId) {
    $price = (int) get_post_meta($propertyId, 'price', true);
    $priceFrom = (string) getLinkedPropertiesPriceRange($propertyId);

    $currency = get_option('jtg_currency');
    $currency_info = jtg_currency_symbol($currency);
    $currency_symbol = $currency_info['symbol'];

    if ($priceFrom !== '') {
        return $priceFrom;
    } else if ($price === 0) {
        return 'Price on Request';
    } else {
        return $currency_symbol . number_format($price, 0);
    }
}



/**
 * Get property price term.
 *
 * @since 2.1.6
 *
 * @param  int    $propertyId Property ID.
 * @return string $priceTerm
 */
function wp4pm_get_property_price_term($propertyId) {
    $priceTerm = (string) get_post_meta($propertyId, 'price_term', true);

    if ($priceTerm !== '') {
        return '/' . $priceTerm;
    }
}



/**
 * Get property description.
 *
 * @since 2.1.6
 *
 * @param  int    $propertyId Property ID.
 * @return string $description
 */
function wp4pm_get_property_description($propertyId) {
    $description = wp_trim_words(get_the_excerpt(), 20, '&hellip;');

    $class = ((int) get_option('show_property_card_description') === 1) ? '' : 'hidden';

    return '<div class="property-card--description ' . $class . '">' . $description . '</div>';
}



/**
 * Get property status badge.
 *
 * @since 2.1.6
 *
 * @param  int    $propertyId Property ID.
 * @return string $status
 */
function wp4pm_get_property_status_badge($propertyId) {
    if ((int) get_option('show_status_badge') === 1) {
        $status = (string) get_post_meta($propertyId, 'property_status', true);
        $propertyMarket = (string) get_post_meta($propertyId, 'property_market', true);

        if ($status === 'Sold' && $propertyMarket == 'New Developments') {
            $status = 'Sold Out';
        } else if (sanitize_title($status) === 'has-been-let') {
            $status = 'Let Agreed';
        }

        $out = '<span>' . $status . '</span>';

        return '<div class="property-card--status-badge">' . $out . '</div>';
    }
}



/**
 * Get property status ribbon.
 *
 * @since 2.1.6
 *
 * @param  int    $propertyId Property ID.
 * @return string $status
 */
function wp4pm_get_property_status_ribbon($propertyId) {
    $class = '';
    $status = (string) get_post_meta($propertyId, 'property_status', true);
    $propertyMarket = (string) get_post_meta($propertyId, 'property_market', true);

    if ($status === 'Sold' && $propertyMarket == 'New Developments') {
        $status = 'Sold Out';
    } else if (sanitize_title($status) === 'has-been-let') {
        $status = 'Let Agreed';
    }

    $statusOverlayStyle = get_option('status_overlay_style');

    $out = '<span>' . $status . '</span>';

    if ($statusOverlayStyle !== 'hidden') {
        return '<div class="property-card--status property-card--status-' . $statusOverlayStyle . '">' . $out . '</div>';
    }
}



/**
 * Get (filtered) property type.
 *
 * @since 2.1.6
 *
 * @param  int    $propertyId Property ID.
 * @return string $type|$customPropertyType
 */
function wp4pm_get_property_type($propertyId) {
    $property_types = get_the_terms($propertyId, 'property_type');
    $propertyMarket = (string) get_post_meta($propertyId, 'property_market', true);

    if ($propertyMarket === 'New Developments') {
        $type = '';
    } else if ($property_types) {
        foreach ($property_types as $type) {
            $type = $type->name;
        }
    }

    $customPropertyType = $type;

    if ((string) $customPropertyType !== '') {
        return $customPropertyType;
    } else {
        return $type;
    }
}



/**
 * Get property living type.
 *
 * @since 1.4.4
 * @updated 2.1.6
 *
 * @param  int    $propertyId Property ID.
 * @return string $livingType
 */
function wp4pm_get_property_living_type($propertyId) {
    /*
    Semi-detached
    2000+ SqF
    Agricultural
    B&B
    Block
    Bungalow
    Castle
    Commercial only
    Condo
    Conversion
    Cottage
    Council
    Country Residence
    Detached
    Development
    Dormer
    Duplex
    End of Terrace
    Established
    Farm
    Full license
    Garden
    Georgian
    Golf Course
    High Street
    Hotel
    Industrial
    Live-work unit
    Loft Style
    Maisonette
    Mansion Block
    Mews
    Modern
    Not applicable
    Not defined
    not-applicable
    Nursing Home
    Off-street
    On-street
    Penthouse
    Period
    Period Residence
    Pre 1963
    Residential & Commercial
    Residential & Commercial units
    Residential only
    Residential units only
    Retail Space
    Secure
    Semi-detached
    Serviced office
    Site only
    Studio
    Terrace
    Townhouse
    Villa
    Warehouse
    With Fore Court
    With Offices
    With planning
    With residence
    */

    $livingType = get_post_meta($propertyId, 'living_type', true);

    $livingTypes = [
        'Not applicable',
        'With planning',
        'With Fore Court',
        'With residence',
        'Full license',
        'Residential only',
        'Commercial only',
        'Residential & Commercial',
        'Residential &amp; Commercial',
        'Residential units only',
        'Residential & Commercial units',
        'Residential &amp; Commercial units',
        'With Offices',
        'Block',
        'Residential & Commercial',
        'Residential &amp; Commercial',
        'not-applicable'
    ];

    if (get_post_meta($propertyId, 'property_market', true) == 'New Developments') {
        return 'New Development ';
    } else if (!empty($livingType) && !in_array($livingType, $livingTypes)) {
        return $livingType . ' ';
    }
}



/**
 * Get (clickable) property title.
 *
 * @since 2.1.6
 *
 * @param  int    $propertyId Property ID.
 * @return string $title
 */
function wp4pm_get_property_title($propertyId) {
    $status = (string) get_post_meta($propertyId, 'property_status', true);

    if ((int) get_option('inactive_not_clickable') === 1 && (sanitize_title($status) === 'sold' || sanitize_title($status) === 'has-been-let')) {
        return get_the_title($propertyId);
    } else {
        return '<a href="' . get_permalink($propertyId) . '">' . get_the_title($propertyId) . '</a>';
    }
}



/**
 * Get (clickable) property thumbnail.
 *
 * @since 2.1.6
 *
 * @param  int    $propertyId Property ID.
 * @return string
 */
function wp4pm_get_property_thumbnail($propertyId) {
    /*
    if (has_post_thumbnail()) {
        $post_thumbnail_url = get_the_post_thumbnail_url($propertyId);
    } else {
        $post_thumbnail_url = wppd_get_thumbnail_url($propertyId);
    }
    /**/

    $status = (string) get_post_meta($propertyId, 'property_status', true);

    if ((int) get_option('inactive_not_clickable') === 1 && (sanitize_title($status) === 'sold' || sanitize_title($status) === 'has-been-let')) {
        return '<img loading="lazy" class="property-image" width="480" height="320" src="' . wppd_get_thumbnail_url($propertyId) . '" alt="' . get_the_title($propertyId) . '" title="' . get_the_title($propertyId) . '" onerror="imgOnError(this)">';
    } else {
        return '<a href="' . get_permalink($propertyId) . '">
            <img loading="lazy" class="property-image" width="480" height="320" src="' . wppd_get_thumbnail_url($propertyId) . '" alt="' . get_the_title($propertyId) . '" title="' . get_the_title($propertyId) . '" onerror="imgOnError(this)">
        </a>';
    }
}



/**
 * Get favourite icon.
 *
 * @since 2.1.6
 *
 * @param  int    $propertyId Property ID.
 * @return string
 */
function wp4pm_get_favourite_icon($propertyId) {
    if ((int) get_option('allow_favourites') === 1) {
        return '<span class="property-card--favourite pd-box-favourite" data-property-id="' . $propertyId . '" tooltip="Save property" flow="left"></span>';
    }
}



/**
 * Get property image count.
 *
 * @since 2.1.6
 *
 * @param  int    $propertyId Property ID.
 * @param  bool   $icon       Value icon.
 * @return string
 */
function wp4pm_get_property_image_count($propertyId, $icon = false) {
    $imageArray = get_post_meta($propertyId, 'detail_images_array', true);
    $imageArray = array_map('trim', explode(',', $imageArray));
    $imageArray = array_filter($imageArray);
    $imageArrayCount = count($imageArray);

    $iconString = '';
    if ((bool) $icon === true) {
        $iconString = '<svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="images" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg-inline--fa fa-images fa-w-18 fa-fw"><path fill="currentColor" d="M480 416v16c0 26.51-21.49 48-48 48H48c-26.51 0-48-21.49-48-48V176c0-26.51 21.49-48 48-48h16v48H54a6 6 0 0 0-6 6v244a6 6 0 0 0 6 6h372a6 6 0 0 0 6-6v-10h48zm42-336H150a6 6 0 0 0-6 6v244a6 6 0 0 0 6 6h372a6 6 0 0 0 6-6V86a6 6 0 0 0-6-6zm6-48c26.51 0 48 21.49 48 48v256c0 26.51-21.49 48-48 48H144c-26.51 0-48-21.49-48-48V80c0-26.51 21.49-48 48-48h384zM264 144c0 22.091-17.909 40-40 40s-40-17.909-40-40 17.909-40 40-40 40 17.909 40 40zm-72 96l39.515-39.515c4.686-4.686 12.284-4.686 16.971 0L288 240l103.515-103.515c4.686-4.686 12.284-4.686 16.971 0L480 208v80H192v-48z" class=""></path></svg> ';
    }

    return '<span class="property-card--image-count">' . $iconString . $imageArrayCount . '</span>';
}



function jtg_currency_symbol($currency) {
    $currency_symbol = array(
        'symbol' => '&euro;',
        'icon_class' => 'fas fa-euro-sign'
    );

    if ((string) $currency === 'euro') {
        $currency_symbol = array(
            'symbol' => '&euro;',
            'icon_class' => 'fas fa-euro-sign'
        );

        return $currency_symbol;
    } else if ((string) $currency === 'gbp') {
        $currency_symbol = array(
            'symbol' => '&pound;',
            'icon_class' => 'fas fa-pound-sign'
        );

        return $currency_symbol;
    } else if ((string) $currency === 'usd') {
        $currency_symbol = array(
            'symbol' => '$',
            'icon_class' => 'fas fa-dollar-sign'
        );
        return $currency_symbol;
    }
}

function pd_property_view_count($postId, $update = false) {
    $viewsCount = get_post_meta($postId, 'property_view_count', true) ?? 0;

    if ($update == true) {
        $viewsCount++;
        update_post_meta($postId, 'property_view_count', $viewsCount);
    }

    return $viewsCount;
}



/**
 * Increment property views
 *
 * Increments property views via AJAX.
 *
 * @return null
 */
function pd_property_view_increment() {
    $propertyId = $_POST['propertyId'];

    $viewsCount = get_post_meta($propertyId, 'property_view_count', true);
    $viewsCount++;

    update_post_meta($propertyId, 'property_view_count', $viewsCount);

    wp_die();
}
add_action('wp_ajax_pd_property_view_increment', 'pd_property_view_increment');
add_action('wp_ajax_nopriv_pd_property_view_increment', 'pd_property_view_increment');



function get_subtypes($typeSlug, $typeName) {
    $subtypes = '';

    $livingTypes = [
        'Not applicable',
        'With planning',
        'With Fore Court',
        'With residence',
        'Full license',
        'Residential only',
        'Commercial only',
        'Residential & Commercial',
        'Residential &amp; Commercial',
        'Residential units only',
        'Residential & Commercial units',
        'Residential &amp; Commercial units',
        'With Offices',
        'Block',
        'Residential & Commercial',
        'Residential &amp; Commercial',
        'not-applicable'
    ];

    $linkedPropertyArgs = [
        'post_type' => 'property',
        'posts_per_page' => -1,
        'tax_query' => [
            [
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => [$typeSlug]
            ]
        ]
    ];
    $linkedPropertyObject = new WP_Query($linkedPropertyArgs);
    if ($linkedPropertyObject->have_posts()) {
        while ($linkedPropertyObject->have_posts()) {
            $linkedPropertyObject->the_post();

            $livingType = get_post_meta($linkedPropertyObject->post->ID, 'living_type', true);

            if (empty($livingType) || in_array($livingType, $livingTypes)) {
                $subtypes .= '';
            } else {
                $subtypes .= '<option value="' . sanitize_title($typeName) . '|' . $livingType . '" data-description="">' . $typeName . ' (' . $livingType . ')</option>';
            }
        }
    }

    return $subtypes;
}



function pd_favourites_fetch_public() {
    $posts = $_POST['favourites'];

    if (!empty($posts)) {
        echo do_shortcode('[property-grid in="' . $posts . '" more="no" columns="3" pagination="no"]');
    }

    die();
}
add_action('wp_ajax_pd_favourites_fetch_public', 'pd_favourites_fetch_public');
add_action('wp_ajax_nopriv_pd_favourites_fetch_public', 'pd_favourites_fetch_public');



function pd_favourites_fetch() {
    $out = '<div id="pd-favourites"></div>';

    return $out;
}
add_shortcode('favourites', 'pd_favourites_fetch');


function save_user_favourite() {
    if (is_user_logged_in()) {
        $userId = get_current_user_id();
        $propertyId = (int) $_POST['property_id'];

        // Check if favourite exists
        $existingFavouriteArgs = [
            'author' => $userId,
            'post_type' => 'favourite',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_property_id',
                    'value' => $propertyId,
                    'compare' => '='
                ]
            ]
        ];
        $existingFavouriteObject = new WP_Query($existingFavouriteArgs);

        if ((int) $existingFavouriteObject->found_posts <= 0) {
            // Save favourite
            if ($propertyId > 0) {
                $favouriteId = wp_insert_post([
                    'post_title' => 'Favourite #' . $propertyId . ' - ' . uniqid() . ' - ' . date('Y-m-d, H:i'),
                    'post_type' => 'favourite',
                    'post_status' => 'private',
                    'post_author' => $userId
                ]);
                if ($favouriteId) {
                    add_post_meta($favouriteId, '_property_id', $propertyId);
                }
            }
        }
    }

    wp_die();
}
add_action('wp_ajax_save_user_favourite', 'save_user_favourite');
add_action('wp_ajax_nopriv_save_user_favourite', 'save_user_favourite');

function remove_user_favourite() {
    if (is_user_logged_in()) {
        $userId = get_current_user_id();
        $favouriteId = (int) $_POST['favourite_id'];

        wp_delete_post($favouriteId, true);
    }

    wp_die();
}
add_action('wp_ajax_remove_user_favourite', 'remove_user_favourite');
add_action('wp_ajax_nopriv_remove_user_favourite', 'remove_user_favourite');



function pd_request_contact() {
    $pid = (int) $_POST['id'];

    $to = sanitize_email($_POST['to']);
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $message = wpautop($_POST['message']);

    $subjectLine = 'Contact Request (ACQ)';

    $body = '<h3>A new quick contact has been sent!</h3>
    <p>Contact details:</p>
    <ul>
        <li><b>Name:</b> ' . $name . '</li>
        <li><b>Email:</b> ' . $email . '</li>
        <li><b>Phone:</b> ' . $phone . '</li>
        <li><b>Sent from:</b> <a href="' . get_permalink($pid) . '">' . get_the_title($pid) . '</a></li>
        <li><b>Message:</b> ' . $message . '</li>
    </ul>';

    $headers[] = "Content-Type: text/html;";
    $headers[] = "X-Mailer: WordPress/PropertyDrive;";
    $headers[] = "Reply-To: $name <$email>;";

    wp_mail($to, $subjectLine, $body, $headers);
}
add_action('wp_ajax_pd_request_contact', 'pd_request_contact');
add_action('wp_ajax_nopriv_pd_request_contact', 'pd_request_contact');



function get_properties_by_id() {
    $ids = (string) $_POST['ids'];

    if (!empty($ids)) {
        echo do_shortcode('[property-grid property-type="residential" count="-1" pagination="no" views="no" in="' . $ids . '"]');
    }

    wp_die();
}
add_action('wp_ajax_get_properties_by_id', 'get_properties_by_id');
add_action('wp_ajax_nopriv_get_properties_by_id', 'get_properties_by_id');





/**
 * Get linked properties prices as a numerically sorted array, based on parent
 * property ID (New Developments property market).
 *
 * @since 1.3.1
 *
 * @param int $parentId WordPress property (CPT) ID.
 */
function getLinkedPropertiesPriceRange($parentId) {
    if (get_post_meta($parentId, 'property_market', true) == 'New Developments') {
        $currency = get_option('jtg_currency');
        $currency_info = jtg_currency_symbol($currency);
        $currency_symbol = $currency_info['symbol'];

        $linkedProperties = get_post_meta($parentId, 'linked_properties', true);

        if ((string) $linkedProperties !== '') {
            $linkedPropertiesArray = explode(',', $linkedProperties);
            $priceArray = [];

            foreach ($linkedPropertiesArray as $linkedProperty) {
                $linkedPropertyArgs = [
                    'post_type' => 'property',
                    'posts_per_page' => 1,
                    'meta_query' => [
                        [
                            'key' => 'importer_id',
                            'value' => $linkedProperty,
                            'compare' => '='
                        ]
                    ]
                ];
                $linkedPropertyObject = new WP_Query($linkedPropertyArgs);
                if ($linkedPropertyObject->have_posts()) {
                    while ($linkedPropertyObject->have_posts()) {
                        $linkedPropertyObject->the_post();

                        if (is_numeric(get_post_meta($linkedPropertyObject->post->ID, 'price', true))) {
                            $priceArray[] = get_post_meta($linkedPropertyObject->post->ID, 'price', true);
                        }
                    }
                }
            }

            sort($priceArray);

            if (count($priceArray) > 0) {
                return 'From ' . $currency_symbol . number_format($priceArray[0], 0);
            }
        }
    }
}

/**
 * Get linked properties bedrooms as a numerically sorted array, based on a comma
 * separated list of linked properties (New Developments property market).
 *
 * @since 1.3.1
 *
 * @param string $propertyList A list of importer_ids.
 */
function getLinkedPropertiesBedroomRange($propertyList) {
    if ((string) $propertyList !== '') {
        $linkedPropertiesArray = explode(',', $propertyList);
        $bedroomArray = array();

        foreach ($linkedPropertiesArray as $linkedProperty) {
            $linkedPropertyArgs = [
                'post_type' => 'property',
                'posts_per_page' => 1,
                'meta_query' => [
                    [
                        'key' => 'importer_id',
                        'value' => $linkedProperty,
                        'compare' => '='
                    ]
                ]
            ];
            $linkedPropertyObject = new WP_Query($linkedPropertyArgs);
            if ($linkedPropertyObject->have_posts()) {
                while ($linkedPropertyObject->have_posts()) {
                    $linkedPropertyObject->the_post();

                    if (is_numeric(get_post_meta($linkedPropertyObject->post->ID, 'bedrooms', true))) {
                        $bedroomArray[] = get_post_meta($linkedPropertyObject->post->ID, 'bedrooms', true);
                    }
                }
            }
        }

        sort($bedroomArray);

        if (count($bedroomArray) > 0) {
            return $bedroomArray[0] . '-' . end($bedroomArray);
        }
    }
}




/**
 * Membership module
 *
 * A simple front-end login/registration system. Adds template tags and shortcodes.
 * Shortcodes: [tiny_form_login]/[tiny_form_register].
 * Template tags: get_tiny_form_login()/get_tiny_form_register() and the_tiny_form_login()/the_tiny_form_register().
 */
//add_action('admin_init', 'disable_dashboard');
//add_action('after_setup_theme', 'cc_disable_admin_bar');

function disable_dashboard() {
    if (current_user_can('subscriber') && is_admin()) {
        wp_redirect(home_url());

        exit;
    }
}

function cc_hide_admin_bar() {
    if (!current_user_can('edit_posts')) {
        show_admin_bar(false);
    }
}

function cc_disable_admin_bar() {
    if (current_user_can('subscriber')) {
    	add_filter('show_admin_bar', '__return_false');
    }
}

function get_tiny_form_login($redirect = false) {
    global $tiny_form_count;

    ++$tiny_form_count;

    if (!is_user_logged_in()) {
        $return = '<form action="" method="post" class="tiny_form tiny_form_login">';
            $error = get_tiny_error($tiny_form_count);
            if ($error) {
                $return .= '<p class="error">' . $error . '</p>';
            }
            $success = get_tiny_success($tiny_form_count);
            if ($success) {
                $return .= '<p class="success">' . $success . '</p>';
            }

            $return .= '<p>
                <label for="tiny_username">' . __('Username', 'property-drive') . '</label>
                <input type="text" id="tiny_username" name="tiny_username">
            </p>
            <p>
                <label for="tiny_password">' . __('Password', 'property-drive') . '</label>
                <input type="password" id="tiny_password" name="tiny_password">
            </p>';

            if ($redirect) {
                $return .= '<input type="hidden" name="redirect" value="' . $redirect . '">';
            }

            $return .= '<input type="hidden" name="tiny_action" value="login">
            <input type="hidden" name="tiny_form" value="' . $tiny_form_count . '">
            <button type="submit">' . __('Login', 'property-drive') . '</button>
        </form>';
    } else {
        $return = '<p>You are already logged in.</p>';
    }

    return $return;
}

function the_tiny_form_login($redirect = false) {
    echo get_tiny_form_login($redirect);
}
add_shortcode('tiny_form_login','tiny_form_login_shortcode');

function tiny_form_login_shortcode($atts, $content = false) {
    $atts = shortcode_atts(array(
        'redirect' => false
    ), $atts);

    return get_tiny_form_LOGIN($atts['redirect']);
}

function get_tiny_form_register($redirect = false) {
   global $tiny_form_count;
   ++$tiny_form_count;
   if (!is_user_logged_in()) :
     $return = "<form action=\"\" method=\"post\" class=\"tiny_form tiny_form_register\">\r\n";
     $error = get_tiny_error($tiny_form_count);
     if ($error)
       $return .= "<p class=\"error\">{$error}</p>\r\n";
     $success = get_tiny_success($tiny_form_count);
     if ($success)
       $return .= "<p class=\"success\">{$success}</p>\r\n";

   // add as many inputs, selects, textareas as needed
     $return .= "  <p>
       <label for=\"tiny_username\">".__('Username','property-drive')."</label>
       <input type=\"text\" id=\"tiny_username\" name=\"tiny_username\"/>
     </p>\r\n";
     $return .= "  <p>
       <label for=\"tiny_email\">".__('Email','property-drive')."</label>
       <input type=\"email\" id=\"tiny_email\" name=\"tiny_email\"/>
     </p>\r\n";
   // where to redirect on success
     if ($redirect)
       $return .= "  <input type=\"hidden\" name=\"redirect\" value=\"{$redirect}\">\r\n";

     $return .= "  <input type=\"hidden\" name=\"tiny_action\" value=\"register\">\r\n";
     $return .= "  <input type=\"hidden\" name=\"tiny_form\" value=\"{$tiny_form_count}\">\r\n";

     $return .= "  <button type=\"submit\">".__('Register','property-drive')."</button>\r\n";
     $return .= "</form>\r\n";
   else :
     $return = __('User is logged in.','property-drive');
   endif;
   return $return;
 }
 // print form #1
 /* usage: <?php the_tiny_form_register(); ?> */
 function the_tiny_form_register($redirect=false) {
   echo get_tiny_form_register($redirect);
 }
 // shortcode for form #1
 // usage: [tiny_form_register] in post/page content
 add_shortcode('tiny_form_register','tiny_form_register_shortcode');
 function tiny_form_register_shortcode ($atts,$content=false) {
   $atts = shortcode_atts(array(
     'redirect' => false
   ), $atts);
   return get_tiny_form_register($atts['redirect']);
 }

 // <============== LOGIN FORM

 // ============ FORM SUBMISSION HANDLER
 add_action('init','tiny_handle');
 function tiny_handle() {
   $success = false;
   if (isset($_REQUEST['tiny_action'])) {
     switch ($_REQUEST['tiny_action']) {
       case 'login':
         if (!$_POST['tiny_username']) {
           set_tiny_error(__('<strong>ERROR</strong>: Empty username','property-drive'),$_REQUEST['tiny_form']);
         } else if (!$_POST['tiny_password']) {
           set_tiny_error(__('<strong>ERROR</strong>: Empty password','property-drive'),$_REQUEST['tiny_form']);
         } else {
           $creds = array();
           $creds['user_login'] = $_POST['tiny_username'];
           $creds['user_password'] = $_POST['tiny_password'];
           //$creds['remember'] = false;
           $user = wp_signon( $creds );
           if ( is_wp_error($user) ) {
             set_tiny_error($user->get_error_message(),$_REQUEST['tiny_form']);
           } else {
             set_tiny_success(__('Log in successful','property-drive'),$_REQUEST['tiny_form']);
             $success = true;
           }
         }
         break;
       case 'register':
         if (!$_POST['tiny_username']) {
           set_tiny_error(__('<strong>ERROR</strong>: Empty username','property-drive'),$_REQUEST['tiny_form']);
         } else if (!$_POST['tiny_email']) {
           set_tiny_error(__('<strong>ERROR</strong>: Empty email','property-drive'),$_REQUEST['tiny_form']);
         } else {
           $creds = array();
           $creds['user_login'] = $_POST['tiny_username'];
           $creds['user_email'] = $_POST['tiny_email'];
           $creds['user_pass'] = wp_generate_password();
           $creds['role'] = get_option('default_role');
           //$creds['remember'] = false;
           $user = wp_insert_user( $creds );
           if ( is_wp_error($user) ) {
             set_tiny_error($user->get_error_message(),$_REQUEST['tiny_form']);
           } else {
             set_tiny_success(__('Registration successful. Your password will be sent via email shortly.','property-drive'),$_REQUEST['tiny_form']);
             wp_new_user_notification($user);
             $success = true;
           }
         }
         break;
       // add more cases if you have more forms
     }

     // if redirect is set and action was successful
     if (isset($_REQUEST['redirect']) && $_REQUEST['redirect'] && $success) {
       wp_redirect($_REQUEST['redirect']);
       die();
     }
   }
 }


 // ================= UTILITIES

 if (!function_exists('set_tiny_error')) {
   function set_tiny_error($error,$id=0) {
     $_SESSION['tiny_error_'.$id] = $error;
   }
 }
 // shows error message
 if (!function_exists('the_tiny_error')) {
   function the_tiny_error($id=0) {
     echo get_tiny_error($id);
   }
 }

 if (!function_exists('get_tiny_error')) {
   function get_tiny_error($id=0) {
     if ($_SESSION['tiny_error_'.$id]) {
       $return = $_SESSION['tiny_error_'.$id];
       unset($_SESSION['tiny_error_'.$id]);
       return $return;
     } else {
       return false;
     }
   }
 }
 if (!function_exists('set_tiny_success')) {
   function set_tiny_success($error,$id=0) {
     $_SESSION['tiny_success_'.$id] = $error;
   }
 }
 if (!function_exists('the_tiny_success')) {
   function the_tiny_success($id=0) {
     echo get_tiny_success($id);
   }
 }

 if (!function_exists('get_tiny_success')) {
   function get_tiny_success($id=0) {
     if ($_SESSION['tiny_success_'.$id]) {
       $return = $_SESSION['tiny_success_'.$id];
       unset($_SESSION['tiny_success_'.$id]);
       return $return;
     } else {
       return false;
     }
   }
 }



function wp4pm_update_property_attachments($propertyId) {
    /**
     * Get all post attachments and exclude featured image
     */
    $args = [
        'post_type' => 'attachment',
        'numberposts' => -1,
        'post_status' => null,
        'post_parent' => $propertyId,
        'post_mime_type' => 'image',
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'exclude' => get_post_thumbnail_id($propertyId),
    ];
    $attachments = get_posts($args);
    $images = '';

    if ($attachments) {
        foreach ($attachments as $a) {
            $images .= wp_get_attachment_image_src($a->ID, 'large')[0] . ', ';
        }

        update_post_meta($propertyId, 'detail_images_array', $images);
    }
}



function show_property_attachments($propertyId) {
    /**
     * Get all post attachments and exclude featured image
     */
    $args = [
        'post_type' => 'attachment',
        'numberposts' => -1,
        'post_status' => null,
        'post_parent' => $propertyId,
        'post_mime_type' => 'image',
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'exclude' => get_post_thumbnail_id($propertyId),
    ];
    $attachments = get_posts($args);
    $output = '';

    if ($attachments) {
        foreach ($attachments as $a) {
            $output .= '<img loading="lazy" src="' . wp_get_attachment_image_src($a->ID, 'thumbnail')[0] . '" alt="" width="150" height="150">';
        }
    }

    echo $output;
}

function get_sharing_buttons($propertyId) {
    $propertyId = (int) $propertyId;

    $out = '';

    return $out;
}



/**
 * Register property meta box
 */
function pd_add_meta_box() {
	add_meta_box('pd_meta_box', 'Property Details', 'property_metabox_callback', ['property']);
}
add_action('add_meta_boxes', 'pd_add_meta_box');

/**
 * eCards meta box Callback
 */
function property_metabox_callback($post) {
    wp_nonce_field('property', 'property_nonce');

    $postId = $post->ID;
    ?>

    <p>
        <svg class="svg-inline--fa fa-lock fa-w-14 fa-fw" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="lock" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg=""><path fill="currentColor" d="M400 224h-24v-72C376 68.2 307.8 0 224 0S72 68.2 72 152v72H48c-26.5 0-48 21.5-48 48v192c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V272c0-26.5-21.5-48-48-48zm-104 0H152v-72c0-39.7 32.3-72 72-72s72 32.3 72 72v72z"></path></svg>
        <input id="property_lock" name="property_lock" type="checkbox" value="1" <?php checked(1, get_post_meta($postId, 'property_lock', true)); ?>>
        <label for="property_lock">Lock property</label>
        <br><small>Lock this property so that future Property Drive imports won't overwrite its details.</small>
    </p>

    <p>
        <label>Property Importer ID</label>
        <br><input type="text" name="importer_id" value="<?php echo get_post_meta($postId, 'importer_id', true); ?>">
    </p>

    <p>
        <a href="#" class="button button-secondary" id="property-import" data-property-id="<?php echo $post_id; ?>" data-importer-id="<?php echo get_post_meta($post_id, 'importer_id', true); ?>">Re-import property</a>
    </p>
    <div id="property-import-notice"></div>

    <div class="jtg-admin-notice-light">
        <p>Please be aware that importing or having auto sync enabled may overwrite this data if it is different to your feed.</p>
    </div>

    <h3>Property Media</h3>
    <div class="admin-gallery">
        <?php echo show_property_attachments(get_the_ID()); ?>
    </div>

    <p>
        <label>Property Tour</label>
        <br><input type="text" name="tour" value="<?php echo get_post_meta($postId, 'tours', true); ?>">
    </p>

    <div class="flex-container flex-max-width">
        <div class="flex-item-nowrap flex-item-box">
            <h3>Property Details</h3>
            <p>
                <label for="property-status">Property Status (required)</label>
                <br>
                <?php
                function selectPropertyStatus($postId, $status) {
                    $propertyStatusDb = (string) get_post_meta($postId, 'property_status', true);

                    return ($propertyStatusDb === $status) ? 'selected' : '';
                }
                ?>
                <select name="property_status" id="property-status" required>
                    <optgroup label="Active">
                        <option <?php echo selectPropertyStatus($postId, 'For Sale'); ?>>For Sale</option>
                        <option <?php echo selectPropertyStatus($postId, 'To Let'); ?>>To Let</option>
                    </optgroup>
                    <optgroup label="Inactive">
                        <option <?php echo selectPropertyStatus($postId, 'Sale Agreed'); ?>>Sale Agreed</option>
                        <option <?php echo selectPropertyStatus($postId, 'Sold'); ?>>Sold</option>
                        <option <?php echo selectPropertyStatus($postId, 'Has Been Let'); ?>>Has Been Let</option>
                    </optgroup>
                    <optgroup label="In Progress/Other">
                        <option <?php echo selectPropertyStatus($postId, 'For Sale/To Let'); ?>>For Sale/To Let</option>
                        <option <?php echo selectPropertyStatus($postId, 'Coming Soon'); ?>>Coming Soon</option>
                        <option <?php echo selectPropertyStatus($postId, 'Under Offer'); ?>>Under Offer</option>
                        <option <?php echo selectPropertyStatus($postId, 'Open To Offers'); ?>>Open To Offers</option>
                        <option <?php echo selectPropertyStatus($postId, 'Reserved'); ?>>Reserved</option>
                        <option <?php echo selectPropertyStatus($postId, 'For Auction'); ?>>For Auction</option>
                        <option <?php echo selectPropertyStatus($postId, 'Seeking'); ?>>Seeking</option>
                        <option <?php echo selectPropertyStatus($postId, 'Let'); ?>>Let</option>
                    </optgroup>
                </select>
            </p>

            <p>
                <label>Property Market</label>
                <br><input type="text" class="regular-text" name="market" value="<?php echo get_post_meta($postId, 'property_market', true); ?>">
            </p>

            <p>
                <label>Property Category</label>
                <br><input type="text" class="regular-text" name="property_category" value="<?php echo get_post_meta($postId, 'property_category', true); ?>">
                <br><small>e.g. Investment, Waterfront, Urban, Coastal</small>
            </p>

            <p>
                <label>Price</label>
                <br><input id="jtg_price" type="text" class="regular-text" name="price" value="<?php echo get_post_meta($postId, 'price', true); ?>">
                <br>
                <label>Price Term</label>
                <br><input id="jtg_price_term" type="text" class="regular-text" name="price_term" value="<?php echo get_post_meta($postId, 'price_term', true); ?>">
            </p>
            <p>
                <label>Bedrooms</label>
                <br><input id="jtg_bedrooms" type="text" class="regular-text" name="bedrooms" value="<?php echo get_post_meta($postId, 'bedrooms', true); ?>">
                <br>
                <label>Bathrooms</label>
                <br><input id="jtg_bathrooms" type="text" class="regular-text" name="bathrooms" value="<?php echo get_post_meta($postId, 'bathrooms', true); ?>">
            </p>
            <p>
                <label>Property Size</label>
                <br><input id="jtg_property_size" type="text" class="regular-text" name="property_size" value="<?php echo get_post_meta($postId, 'property_size', true); ?>">
                <br>
                <label>Property Floors</label>
                <br><input id="jtg_property_floors" type="text" class="regular-text" name="property_floors" value="<?php echo get_post_meta($postId, 'property_floors', true); ?>">
            </p>
            <p>
                <label>BER Rating</label>
                <br><input id="jtg_ber_rating" type="text" class="regular-text" name="ber_rating" value="<?php echo get_post_meta($postId, 'ber_rating', true); ?>">
                <bR>
                <label>Energy Details</label>
                <br><input id="jtg_energy_details" type="text" class="regular-text" name="energy_details" value="<?php echo get_post_meta($postId, 'energy_details', true); ?>">
            </p>

            <p>
                <input id="is_featured" name="is_featured" type="checkbox" value="1" <?php checked('true', get_post_meta($postId, 'is_featured', true)); ?>>
                <label>Featured property</label>
            </p>
        </div>
        <div class="flex-item-nowrap flex-item-box">
            <h3>Location Details</h3>
            <p>
                <label>Latitude</label>
                <br><input id="jtg_latitude" type="text" class="regular-text" name="latitude" value="<?php echo get_post_meta($postId, 'latitude', true); ?>">
                <br>
                <label>Longitude</label>
                <br><input id="jtg_longitude" type="text" class="regular-text" name="longitude" value="<?php echo get_post_meta($postId, 'longitude', true); ?>">
            </p>

            <h3>Agent Details</h3>
            <p>
                <label>Agent ID</label>
                <br><input id="jtg_agent_id" type="text" class="regular-text" name="agent_id" value="<?php echo get_post_meta($postId, 'agent_id', true); ?>">
                <br>
                <label>Agent Name</label>
                <br><input id="jtg_agent_name" type="text" class="regular-text" name="agent_name" value="<?php echo get_post_meta($postId, 'agent_name', true); ?>">
                <br>
                <label>Agent Email</label>
                <br><input id="jtg_agent_email" type="text" class="regular-text" name="agent_email" value="<?php echo get_post_meta($postId, 'agent_email', true); ?>">
                <br>
                <label>Agent Phone</label>
                <br><input id="jtg_agent_number" type="text" class="regular-text" name="agent_number" value="<?php echo get_post_meta($postId, 'agent_number', true); ?>">
                <br>
                <label>Agent Mobile</label>
                <br><input id="jtg_agent_mobile" type="text" class="regular-text" name="agent_mobile" value="<?php echo get_post_meta($postId, 'agent_mobile', true); ?>">
                <br>
                <label>Agent Qualification</label>
                <br><input id="jtg_agent_qualification" type="text" class="regular-text" name="agent_qualification" value="<?php echo get_post_meta($postId, 'agent_qualification', true); ?>">
            </p>

            <h3>Attachments</h3>
            <p>
                <label>Brochure 1</label>
                <br><input id="jtg_brochure_1" type="text" class="regular-text" name="brochure_1" value="<?php echo get_post_meta($postId, 'brochure_1', true); ?>">
                <br>
                <label>Brochure 2</label>
                <br><input id="jtg_brochure_2" type="text" class="regular-text" name="brochure_2" value="<?php echo get_post_meta($postId, 'brochure_2', true); ?>">
                <br>
                <label>Brochure 3</label>
                <br><input id="jtg_brochure_3" type="text" class="regular-text" name="brochure_3" value="<?php echo get_post_meta($postId, 'brochure_3', true); ?>">
            </p>
        </div>
    </div>

	<?php
}

/**
 * Save property meta box
 */
function property_save_postdata($post_id) {
    if (!isset($_POST['property_nonce'])) {
        return $post_id;
    }

    $nonce = $_POST['property_nonce'];

    if (!wp_verify_nonce($nonce, 'property')) {
        return $post_id;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    $propertyLock = isset($_POST['property_lock']) ? 1 : 0;

    $propertyImporterId = isset($_POST['importer_id']) ? (int) $_POST['importer_id'] : '';
    $propertyTour = isset($_POST['tour']) ? $_POST['tour'] : '';
    $propertyStatus = isset($_POST['property_status']) ? sanitize_text_field($_POST['property_status']) : '';
    $propertyMarket = isset($_POST['market']) ? sanitize_text_field($_POST['market']) : '';
    $propertyCategory = isset($_POST['property_category']) ? sanitize_text_field($_POST['property_category']) : '';

    $propertyPrice = isset($_POST['price']) ? sanitize_text_field($_POST['price']) : '';
    $propertyPriceTerm = isset($_POST['price_term']) ? sanitize_text_field($_POST['price_term']) : '';
    $propertyBedrooms = isset($_POST['bedrooms']) ? (int) $_POST['bedrooms'] : '';
    $propertyBathrooms = isset($_POST['bathrooms']) ? (int) $_POST['bathrooms'] : '';
    $propertySize = isset($_POST['property_size']) ? sanitize_text_field($_POST['property_size']) : '';
    $propertyFloors = isset($_POST['property_floors']) ? sanitize_text_field($_POST['property_floors']) : '';
    $propertyBerRating = isset($_POST['ber_rating']) ? sanitize_text_field($_POST['ber_rating']) : '';
    $propertyEnergyDetails = isset($_POST['energy_details']) ? sanitize_text_field($_POST['energy_details']) : '';

    $propertyIsFeatured = isset($_POST['is_featured']) ? 'true' : 'false';

    $propertyLatitude = isset($_POST['latitude']) ? sanitize_text_field($_POST['latitude']) : '';
    $propertyLongitude = isset($_POST['longitude']) ? sanitize_text_field($_POST['longitude']) : '';
    $propertyAgentId = isset($_POST['agent_id']) ? (int) $_POST['agent_id'] : '';
    $propertyAgentName = isset($_POST['agent_name']) ? sanitize_text_field($_POST['agent_name']) : '';
    $propertyAgentEmail = isset($_POST['agent_email']) ? sanitize_email($_POST['agent_email']) : '';
    $propertyAgentNumber = isset($_POST['agent_number']) ? sanitize_text_field($_POST['agent_number']) : '';
    $propertyAgentMobile = isset($_POST['agent_mobile']) ? sanitize_text_field($_POST['agent_mobile']) : '';
    $propertyAgentQualification = isset($_POST['agent_qualification']) ? sanitize_text_field($_POST['agent_qualification']) : '';

    $propertyBrochure1 = isset($_POST['brochure_1']) ? sanitize_text_field($_POST['brochure_1']) : '';
    $propertyBrochure2 = isset($_POST['brochure_2']) ? sanitize_text_field($_POST['brochure_2']) : '';
    $propertyBrochure3 = isset($_POST['brochure_3']) ? sanitize_text_field($_POST['brochure_3']) : '';

    update_post_meta($post_id, 'property_lock', $propertyLock);
    update_post_meta($post_id, 'importer_id', $propertyImporterId);

    update_post_meta($post_id, 'tours', $propertyTour);
    update_post_meta($post_id, 'property_status', $propertyStatus);
    update_post_meta($post_id, 'property_market', $propertyMarket);
    update_post_meta($post_id, 'property_category', $propertyCategory);

    update_post_meta($post_id, 'price', $propertyPrice);
    update_post_meta($post_id, 'price_term', $propertyPriceTerm);
    update_post_meta($post_id, 'bedrooms', $propertyBedrooms);
    update_post_meta($post_id, 'bathrooms', $propertyBathrooms);
    update_post_meta($post_id, 'property_size', $propertySize);
    update_post_meta($post_id, 'property_floors', $propertyFloors);
    update_post_meta($post_id, 'ber_rating', $propertyBerRating);
    update_post_meta($post_id, 'energy_details', $propertyEnergyDetails);

    update_post_meta($post_id, 'is_featured', $propertyIsFeatured);

    update_post_meta($post_id, 'latitude', $propertyLatitude);
    update_post_meta($post_id, 'longitude', $propertyLongitude);
    update_post_meta($post_id, 'agent_id', $propertyAgentId);
    update_post_meta($post_id, 'agent_name', $propertyAgentName);
    update_post_meta($post_id, 'agent_email', $propertyAgentEmail);
    update_post_meta($post_id, 'agent_number', $propertyAgentNumber);
    update_post_meta($post_id, 'agent_mobile', $propertyAgentMobile);
    update_post_meta($post_id, 'agent_qualification', $propertyAgentQualification);

    update_post_meta($post_id, 'brochure_1', $propertyBrochure1);
    update_post_meta($post_id, 'brochure_2', $propertyBrochure2);
    update_post_meta($post_id, 'brochure_3', $propertyBrochure3);

    /**
     * Add custom meta based on property status
     */
    $propertyStatusArray = [
        'for-sale' => 0,
        'for-sale-to-let' => 0,
        'to-let' => 0,
        'coming-soon' => 0,
        'under-offer' => 1,
        'open-to-offers' => 1,
        'reserved' => 1,
        'for-auction' => 2,
        'seeking' => 3,
        'let-agreed' => 4,
        'sale-agreed' => 4,
        'has-been-let' => 5,
        'let' => 5,
        'sold' => 5
    ];

    $propertyOrder = $propertyStatusArray[sanitize_title($propertyStatus)];
    update_post_meta($post_id, 'property_order', $propertyOrder);
}
add_action('save_post', 'property_save_postdata');



/**
 * Convert a multi-dimensional array into a single-dimensional array.
 */
function array_flatten($array) {
    if (!is_array($array)) {
        return false;
    }

    $result = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $result = array_merge($result, array_flatten($value));
        } else {
            $result[$key] = $value;
        }
    }

    return $result;
}



/**
 * Property brochure template selector
 *
 * @param  string $single_template  Native WordPress single template
 * @return string                   Overridden single template
 */
function wp4pm_single_prop($single_template) {
    global $wp_query, $post;

    $single_template = plugin_dir_path(__DIR__) . 'templates/single-property.php';

    return $single_template;
}
add_filter('single_template', 'wp4pm_single_prop');


add_action('wp_footer', 'wp4pm_analytics_footer');
function wp4pm_analytics_footer() {
    echo '<span id="pd-ip" data-ip="' . wp4pm_get_pd_user_ip() . '"></span>';
}




add_action('admin_init', 'supernova_add_property_menu_order');

function supernova_add_property_menu_order() {
    add_post_type_support('property', 'page-attributes');
}




function supernova_accordion_item($atts, $content = null) {
    $attributes = shortcode_atts([
        'title' => ''
    ], $atts);

    $out = '<details class="accordion">
        <summary>' . $attributes['title'] . '</summary>
        <div class="inner-details">' .
            do_shortcode($content) .
        '</div>
    </details>';

    return $out;
}

add_shortcode('supernova-accordion-item', 'supernova_accordion_item');






add_action('manage_posts_custom_column', 'wppd_post_columns_data', 10, 2);
add_filter('manage_edit-property_columns', 'wppd_post_columns_display');

function wppd_post_columns_data($column, $postId) {
    switch ($column) {
        case 'thumbnail':
            if (has_post_thumbnail($postId)) {
                $post_thumbnail_url = get_the_post_thumbnail_url($postId);
            } else {
                $post_thumbnail_url = wppd_get_thumbnail_url($postId);
            }

            echo '<img src="' . $post_thumbnail_url . '" height="60" alt="' . get_the_title($postId) . '">';

            break;
        case 'views':
            $viewCounter = (int) pd_property_view_count(get_the_ID(), false);

            echo number_format($viewCounter);

            break;
        case 'status':
            $status = (string) get_post_meta($postId, 'property_status', true);
            $propertyMarket = (string) get_post_meta($postId, 'property_market', true);

            if ($status === 'Sold' && $propertyMarket == 'New Developments') {
                $status = 'Sold Out';
            } else if (sanitize_title($status) === 'has-been-let') {
                $status = 'Let Agreed';
            }

            echo $status;

            break;
        case 'modified':
            $m_orig = get_post_field('post_modified', $postId, 'raw');
            $m_stamp = strtotime($m_orig);
            $modified = date(get_option('date_format') . ', ' . get_option('time_format'), $m_stamp);
            $modr_id = get_post_meta($postId, '_edit_last', true);
            $auth_id = get_post_field('post_author', $postId, 'raw');
            $user_id = !empty($modr_id) ? $modr_id : $auth_id;
            $user_info = get_userdata($user_id);

            echo $modified . '<br>by <strong>' . $user_info->display_name . '<strong>';

            break;
        case 'modified_source':
            echo date(get_option('date_format') . ', ' . get_option('time_format'), strtotime(get_post_meta($postId, 'date_modified', true))) . '<br>by <strong>' . get_post_meta($postId, 'source', true) . '<strong>';;

            break;
    }
}

function wppd_post_columns_display($columns) {
    $columns['thumbnail'] = 'Thumbnail';
    $columns['status'] = 'Status';
    $columns['views'] = 'Views';
    $columns['modified'] = 'Last Modified';
    $columns['modified_source'] = 'Last Modified (Source)';

    /**
     * Move thumbnail column first
     *
     * $columnThumbnail = ['thumbnail' => 'Thumbnail'];
     * $columns = array_slice($columns, 0, 1, true) + $columnThumbnail + array_slice($columns, 1, NULL, true);
     */

    return $columns;
}


/**/
add_action('init', 'supernova_access_init');
function supernova_access_init() {
    if (is_admin() && !current_user_can('administrator') && !(defined('DOING_AJAX') && DOING_AJAX)) {
        wp_redirect(home_url());

        exit;
    }
}
add_action('after_setup_theme', 'supernova_remove_admin_bar');

function supernova_remove_admin_bar() {
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
}
/**/
