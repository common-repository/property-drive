<?php
function wp4pm_get_pd_user_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
    }

    return $ipAddress;
}

/**
 * Grab all possible meta values of the chosen meta key
 */
function wp4pm_get_meta_values($metaKey) {
    $properties = get_posts([
        'post_type' => 'property',
        'meta_key' => $metaKey,
        'posts_per_page' => -1
    ]);

    $metaValues = [];
    foreach ($properties as $property) {
        $metaValues[] = get_post_meta($property->ID, $metaKey, true);
    }
    $metaValues = array_unique($metaValues);

    return $metaValues;
}

function jtg_search_type_4_shortcode($atts) {
    $resultsPage = get_permalink((int) get_option('search_results_page'));
    if (!get_option('search_results_page')) {
        $resultsPage = '/properties';
    }

    extract(shortcode_atts([
        'layout' => 'basic',
        'results' => $resultsPage,
        'land' => 'no',
        'show_type' => '',
        'default_status' => '',
        'default_type' => '',
        'keyword_placeholder' => 'Keyword'
    ], $atts));

    global $wpdb;

    $searchString = [];

    $jtg_table_name = $wpdb->prefix . 'postmeta';

    $termsType = get_terms('property_type', 'hide_empty=1');
    $termsStatus = wp4pm_get_meta_values('property_status');
    $counties = $wpdb->get_results("SELECT DISTINCT meta_value from $jtg_table_name where meta_key='county' ORDER BY meta_value ASC");
    $max_price_in_db = $wpdb->get_results("SELECT max(cast(meta_value as unsigned)) as meta_value FROM $jtg_table_name WHERE meta_key='price'");

    /**
     * Get search form builder options
     */
    $searchFieldGroup = (int) get_option('search_field_group');
    $searchFieldType = (int) get_option('search_field_type');
    $searchFieldStatus = (int) get_option('search_field_status');
    $searchFieldStatusGroup = (int) get_option('search_field_status_group');
    $searchFieldPrice = (int) get_option('search_field_price');
    $searchFieldBeds = (int) get_option('search_field_beds');
    $searchFieldBaths = (int) get_option('search_field_baths');
    $searchFieldKeyword = (int) get_option('search_field_keyword');

    $searchFieldLocation = (int) get_option('search_field_location');
    $searchFieldMultiType = (int) get_option('search_field_multitype');

    $searchFieldFeatures = (int) get_option('search_field_features');

    if ($searchFieldGroup === 1 && (string) $show_type !== 'no') {
        $checkedGroupType = (isset($_GET['group_type'])) ? sanitize_text_field($_GET['group_type']) : 'pm_residential';

        if ((string) $default_type !== '') {
            $checkedGroupType = sanitize_text_field($default_type);
        }

        $show = '<div class="property-tabs-wrapper wp4pm-flex-item">
            <div class="property-group-switch">
                <input type="radio" id="pm_residential" name="group_type" value="pm_residential" ' . checked($checkedGroupType, 'pm_residential', false) . '>
                <label for="pm_residential">Residential</label>

                <input type="radio" id="pm_commercial" name="group_type" value="pm_commercial" ' . checked($checkedGroupType, 'pm_commercial', false) . '>
                <label for="pm_commercial">Commercial</label>';

                if ((string) $land === 'yes') {
                    $show .= '<input type="radio" id="pm_land" name="group_type" value="pm_land" ' . checked($checkedGroupType, 'pm_land', false) . '>
                    <label for="pm_land">Land</label>';
                }
            $show .= '</div>
        </div>';

        $searchString['group'] = $show;
    }

    if ($searchFieldStatusGroup === 1) {
        $checkedStatusGroupType = (isset($_GET['property_status'])) ? sanitize_text_field($_GET['property_status']) : 'For Sale';

        if ((string) $default_status !== '') {
            $checkedStatusGroupType = sanitize_text_field($default_status);
        }

        $searchString['status_group'] = '<div class="property-tabs-wrapper wp4pm-flex-item">
            <div class="property-group-switch">
                <input type="radio" id="for-sale" name="property_status" class="advanced-search-click" value="For Sale" ' . checked($checkedStatusGroupType, 'For Sale', false) . '>
                <label for="for-sale" id="label-for-sale">Sale</label>

                <input type="radio" id="to-let" name="property_status" class="advanced-search-click" value="To Let" ' . checked($checkedStatusGroupType, 'To Let', false) . '>
                <label for="to-let" id="label-to-let">Rent</label>

                <input type="radio" id="sold" name="property_status" class="advanced-search-click" value="Sold,Sale Agreed" ' . checked($checkedStatusGroupType, 'Sold,Sale Agreed', false) . '>
                <label for="sold" id="label-sold" class="hidden">Sold</label>

                <input type="radio" id="let" name="property_status" class="advanced-search-click" value="Let,Has been Let" ' . checked($checkedStatusGroupType, 'Let,Has been Let', false) . '>
                <label for="let" id="label-let" class="hidden">Let</label>
            </div>
        </div>';
    }
    if ($searchFieldType === 1) {
        $searchString['type'] = '<select name="property_type" id="property_type_select" class="wp4pm-flex-item advanced-search-trigger" aria-label="Property Type">
            <option value="">Any Property Type</option>';

            foreach ($termsType as $row) {
                $searchString['type'] .= '<option value="'. $row->slug .'">' . $row->name . '</option>';
            }

        $searchString['type'] .= '</select>';
    }
    if ($searchFieldFeatures === 1) {
        $searchString['features'] = '<select name="f[]" id="property_features_select" class="wp4pm-flex-item" aria-label="Property Features" size="1" multiple>';

            //foreach ($termsType as $row) {
                //$searchString['features'] .= '<option value="'. $row->slug .'">' . $row->name . '</option>';
                $searchString['features'] .= '<option value="">Swimming Pool</option>';
                $searchString['features'] .= '<option value="">Garage</option>';
                $searchString['features'] .= '<option value="">Garden</option>';
            //}

        $searchString['features'] .= '</select>';
    }
    if ($searchFieldKeyword === 1) {
        $keywordPlaceholder = trim($keyword_placeholder);

        $value = isset($_GET['property_keyword']) ? urldecode($_GET['property_keyword']) : '';
        $searchString['keyword'] = '<input type="text" id="keyword" name="property_keyword" placeholder="' . $keywordPlaceholder . '" class="wp4pm-flex-item" value="' . $value . '" aria-label="Keyword">';
    }
    if ($searchFieldPrice === 1) {
        $searchString['price'] = '<div class="wp4pm-flex-item flex-container-nowrap">
            <select name="min_price" id="min_price" aria-label="Minimum Price">
                <option value="" selected disabled>Min Price</option>
                <option value="10000">10,000</option>
                <option value="20000">20,000</option>
                <option value="50000">50,000</option>
                <option value="75000">75,000</option>
                <option value="100000">100,000</option>
                <option value="200000">200,000</option>
                <option value="300000">300,000</option>
                <option value="400000">400,000</option>
                <option value="500000">500,000</option>
            </select>
            <select name="max_price" id="max_price" aria-label="Maximum Price">
                <option value="" selected disabled>Max Price</option>
                <option value="10000">10,000</option>
                <option value="20000">20,000</option>
                <option value="50000">50,000</option>
                <option value="75000">75,000</option>
                <option value="100000">100,000</option>
                <option value="200000">200,000</option>
                <option value="300000">300,000</option>
                <option value="400000">400,000</option>
                <option value="500000">500,000</option>
                <option value="600000">600,000</option>
                <option value="700000">700,000</option>
                <option value="800000">800,000</option>
                <option value="900000">900,000</option>
                <option value="1000000">1,000,000</option>
                <option value="10000000">1,000,000+</option>
            </select>
        </div>';
    }
    if ($searchFieldStatus === 1) {
        $searchString['status'] = '<select id="property_status" name="property_status" class="wp4pm-flex-item" aria-label="Property Status">
            <option value="" selected>Any Property Status</option>';

            foreach ($termsStatus as $row) {
                $searchString['status'] .= '<option>' . $row . '</option>';
            }
        $searchString['status'] .= '</select>';
    }
    if ($searchFieldBeds === 1) {
        $searchString['beds'] = '<select id="bedrooms" name="beds" class="wp4pm-flex-item" aria-label="Bedrooms">
            <option value="" disabled selected>Select Bedrooms</option>
            <option value="1">1+</option>
            <option value="2">2+</option>
            <option value="3">3+</option>
            <option value="4">4+</option>
            <option value="5">5+</option>
            <option value="6">6+</option>
            <option value="7">7+</option>
            <option value="8">8+</option>
        </select>';
    }
    if ($searchFieldBaths === 1) {
        $searchString['baths'] = '<select id="bathrooms" name="baths" class="wp4pm-flex-item" aria-label="Bathrooms">
            <option value="" disabled selected>Select Bathrooms</option>
            <option value="1">1+</option>
            <option value="2">2+</option>
            <option value="3">3+</option>
            <option value="4">4+</option>
            <option value="5">5+</option>
            <option value="6">6+</option>
            <option value="7">7+</option>
            <option value="8">8+</option>
        </select>';
    }

    if ($searchFieldLocation === 1) {
        $options = '';

        /**
         * Build location selector based on current properties area and county
         *
         * @var function
         */
        $metaLocations = $metaCounties = [];

        $properties = get_posts([
            'post_type' => 'property',
            'posts_per_page' => -1
        ]);

        foreach ($properties as $property) {
            $metaLocations[] = [
                'area' => get_post_meta($property->ID, 'area', true),
                'county' => get_post_meta($property->ID, 'county', true)
            ];
            $metaCounties[] = get_post_meta($property->ID, 'county', true);
        }

        $metaCounties = array_unique($metaCounties);
        $metaLocations = array_unique($metaLocations, SORT_REGULAR);
        asort($metaLocations);

        $prefix = '';

        foreach ($metaCounties as $county) {
            if ((string) $county !== '') {
                $options .= '<optgroup label="' . $prefix . $county . '">';
                    foreach ($metaLocations as $metaLocation) {
                        $options .= ((string) $county === (string) $metaLocation['county']) ? '<option>' . $metaLocation['area'] . '</option>' : '';
                    }
                $options .= '</optgroup>';
            }
        }
        //

        $searchString['location'] = '<select id="location-multi" name="location[]" class="wp4pm-flex-item" aria-label="Location" size="1" multiple>
            ' . $options . '
        </select>';
    }

    if ($searchFieldMultiType === 1) {
        $options = '';
        $string = '';

        /**
         * Build property type structure
         */
        $metaResidentialArray = [
            'house',
            'apartment',
            'flat',
            'studio',
            'duplex'
        ];
        $metaCommercialArray = [
            'industrial',
            'industrial-distribution',
            'office',
            'retail',
            'restaurant',
            'warehouse',
            'other'
        ];
        $metaLandArray = [
            'site',
            'development-site',
            'development-land',
            'site-individual',
            'agricultural',
            'farm'
        ];

        //$t = '';
        $resArray = [];
        $comArray = [];
        $lndArray = [];
        foreach ($termsType as $row) {
            if (in_array($row->slug, $metaResidentialArray)) {
                $resArray[] = $row->name;
            } else if (in_array($row->slug, $metaCommercialArray)) {
                $comArray[] = $row->name;
            } else if (in_array($row->slug, $metaLandArray)) {
                $lndArray[] = $row->name;
            }
        }

        $string .= '<select name="t[]" id="property_multitype_select" class="wp4pm-flex-item" aria-label="Type" multiple>';
            if (count ($resArray) > 0) {
                $string .= '<optgroup label="Residential">';
                    foreach ($resArray as $resUnit) {
                        $string .= '<option value="' . $resUnit . '">' . $resUnit . '</option>';
                    }
                $string .= '</optgroup>';
            }
            if (count ($comArray) > 0) {
                $string .= '<optgroup label="Commercial">';
                    foreach ($comArray as $resUnit) {
                        $string .= '<option value="' . $resUnit . '">' . $resUnit . '</option>';
                    }
                $string .= '</optgroup>';
            }
            if (count ($lndArray) > 0) {
                $string .= '<optgroup label="Land">';
                    foreach ($lndArray as $resUnit) {
                        $string .= '<option value="' . $resUnit . '">' . $resUnit . '</option>';
                    }
                $string .= '</optgroup>';
            }
        $string .= '</select>';

        $searchString['multitype'] = $string;
    }

    /**
     * Build search form
     */
    $out = '<form id="wp4pm-search" class="wp4pm-' . $layout . '" method="get" action="' . $results . '">
        <input type="hidden" id="wp4pm-ip-address" value="' . wp4pm_get_pd_user_ip() . '">
        <div class="wp4pm-search-progress-wrap">
            <div class="wp4pm-search-progress"></div>
        </div>
        <div class="wp4pm wp4pm-flex">';

            $searchFieldArray = explode('|', (string) get_option('search_field_array'));
            foreach ($searchFieldArray as $searchFieldItem) {
                if (isset($searchString[$searchFieldItem])) {
                    $out .= $searchString[$searchFieldItem];
                }
            }

            $out .= '<button type="submit" class="wp4pm-flex-item wp4pm-btn-primary"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="search" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-search fa-w-16 fa-fw"><path fill="currentColor" d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z" class=""></path></svg> Search</button>
        </div>
        <div id="wp4pm-status"></div>
    </form>';

    return $out;
}
add_shortcode('jtg_search_type_4', 'jtg_search_type_4_shortcode');
add_shortcode('search_form', 'jtg_search_type_4_shortcode');
add_shortcode('property-search', 'jtg_search_type_4_shortcode');
