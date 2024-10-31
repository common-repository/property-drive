<?php
function jtg_add_query_vars($public_query_vars) {
    $public_query_vars[] = 'property_status';
    $public_query_vars[] = 'property_type';
    $public_query_vars[] = 'type_group';
    $public_query_vars[] = 'location_county';
    $public_query_vars[] = 'location_area';
    $public_query_vars[] = 'bedrooms';
    $public_query_vars[] = 'min_price';
    $public_query_vars[] = 'max_price';
    $public_query_vars[] = 'orderby';
    $public_query_vars[] = 'property_keyword';

    return $public_query_vars;
}
add_filter('query_vars', 'jtg_add_query_vars');
