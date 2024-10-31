<?php
/**
 * Display the property description.
 *
 * @param int $propertyId Property ID.
 */
function wppd_property_description($propertyId) {
    $out = wppd_get_property_description($propertyId);

    echo $out;
}

/**
 * Retrieve the property description.
 *
 * @param int $propertyId Property ID.
 * @return string
 */
function wppd_get_property_description($propertyId) {
    $propertyDetails = get_post_meta($propertyId);
    $propertyDescription = wpautop(get_post_field('post_content', $propertyId));

    $out = '<section class="grid-property-description">
        <h4 class="listing-section-title" data-listing="property-description">Description</h4>
        <div id="property-description">';

            if (isset($propertyDetails['tours']) && sanitize_text_field($propertyDetails['tours'][0])) {
                $tourUri = $propertyDetails['tours'][0];

                $out .= $tourUri;
            }

            $out .= $propertyDescription;
        $out .= '</div>';
    $out .= '</section>';

    return $out;
}



/**
 * Display the property floorplans.
 *
 * @param int $propertyId Property ID.
 */
function wppd_property_floorplans($propertyId) {
    $out = wppd_get_property_floorplans($propertyId);

    echo $out;
}

/**
 * Retrieve the property floorplans.
 *
 * @param int $propertyId Property ID.
 * @return string
 */
function wppd_get_property_floorplans($propertyId) {
    $propertyDetails = get_post_meta($propertyId);

    if (isset($propertyDetails['property_floors']) && sanitize_text_field($propertyDetails['property_floors'][0]) !== '') {
        $out = '<section id="floorplan" class="grid-property-floorplan">
            <h4 class="listing-section-title" data-listing="property-floorplan">Floorplan</h4>
            <div id="property-floorplan">
                <div data-flickity=\'{"pageDots": false, "wrapAround": true, "fullscreen": true, "imagesLoaded": true}\'>';

                    $floorArray = get_post_meta($propertyId, 'property_floors', true);
                    $floorArray = explode('|', $floorArray);

                    foreach ($floorArray as $floor) {
                        if (!empty($floor)) {
                            $out .= '<img src="' . $floor . '" style="max-height: 480px; width: auto; max-width: 100%;" alt="">';
                        }
                    }

                $out .= '</div>
            </div>
        </section>';

        return $out;
    }
}

/**
 * Display the property directions.
 *
 * @param int $propertyId Property ID.
 */
function wppd_property_directions($propertyId) {
    $out = wppd_get_property_directions($propertyId);

    echo $out;
}

/**
 * Retrieve the property directions.
 *
 * @param int $propertyId Property ID.
 * @return string
 */
function wppd_get_property_directions($propertyId) {
    $propertyDetails = get_post_meta($propertyId);

    if (isset($propertyDetails['directions']) && sanitize_text_field($propertyDetails['directions'][0])) {
        $out = '<section class="grid-property-directions">
            <h4 class="listing-section-title" data-listing="property-directions">Directions</h4>
            <div id="property-directions">' .
                $propertyDetails['directions'][0] .
            '</div>
        </section>';

        return $out;
    }
}

/**
 * Display the property gallery (flexbin).
 *
 * @param int $propertyId Property ID.
 */
function wppd_property_flexbin($propertyId) {
    $out = wppd_get_property_flexbin($propertyId);

    echo $out;
}

/**
 * Retrieve the property gallery (flexbin).
 *
 * @param int $propertyId Property ID.
 * @return string
 */
function wppd_get_property_flexbin($propertyId) {
    if ((int) get_option('use_component_lg') === 1 && function_exists('show_light_gallery')) {
        $out = '<section class="grid-property-lightgallery">
            <h4 class="listing-section-title" data-listing="property-gallery-basic">Gallery</h4>

            <div id="property-gallery-basic">' .
                show_light_gallery($propertyId) .
            '</div>
        </section>';

        return $out;
    }
}







add_shortcode('widget', 'wp4pm_get_supernova_widget');

function wp4pm_get_supernova_widget($atts) {
    $atts = shortcode_atts([
        'type' => ''
    ], $atts);

    $type = $atts['type'];

    $out = '';
    $propertyId = get_the_ID();
    $propertyDetails = get_post_meta($propertyId);

    if ((string) $type === 'location') {
        if (!empty(get_post_meta($propertyId, 'latitude', true)) && !empty(get_post_meta($propertyId, 'longitude', true))) {
            $out .= '<span class="strip-title" id="listing-map">Location</span>
            <div class="strip-sidebar-content">
                <div id="osm-map"></div>
                <script>
                window.addEventListener("load", function () {
                    var osmMap = L.map("osm-map").setView([' . $propertyDetails['latitude'][0] . ', ' . $propertyDetails['longitude'][0] . '], 16);

                    L.marker([' . $propertyDetails['latitude'][0] . ', ' . $propertyDetails['longitude'][0] . '])
                        .addTo(osmMap)
                        .bindPopup("' . get_the_title($propertyId) . '");
                        //.openPopup();

                    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                        attribution: "&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors"
                    }).addTo(osmMap);

                    L.control.scale().addTo(osmMap);

                    // Create a fullscreen button and add it to the map
                    osmMap.addControl(new L.Control.Fullscreen());
                }, false);
                </script>
            </div>';
        }

        return $out;
    } else if ((string) $type === 'agent') {
        if ((int) get_option('force_agency_email') === 1) {
            $agentEmailAddress = get_option('agency_email');
        } else if (isset($propertyDetails['agent_email'][0]) && $propertyDetails['agent_email'][0]) {
            $agentEmailAddress = $propertyDetails['agent_email'][0];
        } else {
            $agentEmailAddress = get_option('agency_email');
        }

        $out = '<span class="strip-title">Agent Information</span>
        <div class="strip-sidebar-content strip-sidebar-agent">';
            if (!empty($propertyDetails['agent_photo'][0])) {
                $out .= '<p class="strip-sidebar-logo"><img src="' . $propertyDetails['agent_photo'][0] . '" alt="" width="90%"></p>';
            } else if (!empty(get_option('agency_logo'))) {
                $imageUri = get_option('agency_logo');
                $out .= '<p class="strip-sidebar-logo"><img src="' . $imageUri . '" alt="" width="90%"></p>';
            }

            if ((int) get_option('force_agency_name') === 1) {
                $out .= '<h4 class="strip-sidebar-agent-name no-agent-name">' . stripslashes(get_option('agency_name')) . '</h4>';
            } else if (isset($propertyDetails['agent_name'][0]) && $propertyDetails['agent_name'][0]) {
                $out .= '<h4 class="strip-sidebar-agent-name has-agent-name">' .
                    stripslashes($propertyDetails['agent_name'][0]);

                    if (isset($propertyDetails['agent_qualification']) && $propertyDetails['agent_qualification'][0] !== '') {
                        $out .= '<br><small>' . sanitize_text_field($propertyDetails['agent_qualification'][0]) . '</small>';
                    }
                $out .= '</h4>';
            } else {
                $out .= '<h4 class="strip-sidebar-agent-name no-agent-name">' . stripslashes(get_option('agency_name')) . '</h4>';
            }

            $out .= '<p class="strip-sidebar-agent-phone">';
                if ((int) get_option('mask_agency_email') === 1) {
                    $out .= '<a href="mailto:' . $agentEmailAddress . '" data-mailto="' . $agentEmailAddress . '" class="supernova-button supernova-button-primary supernova-button-regular">Property Enquiry</a><br>';
                } else {
                    $out .= '<a href="mailto:' . $agentEmailAddress . '">' . $agentEmailAddress . '</a><br>';
                }

                $phoneClass = ((int) get_option('mask_agency_phone') === 1) ? 'reveal-number' : '';

                if (isset($propertyDetails['agent_mobile'][0]) && $propertyDetails['agent_mobile'][0]) {
                    $out .= '<div><a href="tel:' . $propertyDetails['agent_mobile'][0] . '" data-number="' . $propertyDetails['agent_mobile'][0] . '" class="strip-link ' . $phoneClass . '">' . $propertyDetails['agent_mobile'][0] . '</a></div>';
                }

                if ((int) get_option('force_agency_phone') === 1) {
                    $out .= '<a href="tel:' . get_option('agency_phone') . '" data-number="' . get_option('agency_phone') . '" class="strip-link ' . $phoneClass . '">' . get_option('agency_phone') . '</a>';
                } else if (isset($propertyDetails['agent_number'][0]) && $propertyDetails['agent_number'][0]) {
                    $out .= '<a href="tel:' . $propertyDetails['agent_number'][0] . '" data-number="' . $propertyDetails['agent_number'][0] . '" class="strip-link ' . $phoneClass . '">' . $propertyDetails['agent_number'][0] . '</a>';
                } else {
                    $out .= '<a href="tel:' . get_option('agency_phone') . '" data-number="' . get_option('agency_phone') . '" class="strip-link ' . $phoneClass . '">' . get_option('agency_phone') . '</a>';
                }
            $out .= '</p>
        </div>';

        return $out;
    } else if ((string) $type === 'share') {
        if ((int) get_option('force_agency_email') === 1) {
            $agentEmailAddress = get_option('agency_email');
        } else if (isset($propertyDetails['agent_email'][0]) && $propertyDetails['agent_email'][0]) {
            $agentEmailAddress = $propertyDetails['agent_email'][0];
        } else {
            $agentEmailAddress = get_option('agency_email');
        }

        $out = '<section class="flex-tabs">';

            if ((int) get_option('allow_quick_contact') === 1) {
                $out .= '<input id="tab-one" type="radio" name="grp" checked>
                <label for="tab-one">Contact</label>
                <div>
                    <h4>Quick Contact</h4>';

                    $out .= '<p>
                        <input type="email" id="contact-to" value="' . $agentEmailAddress . '" readonly>
                        <input type="text" id="contact-name" placeholder="Full Name">
                        <input type="email" id="contact-email" placeholder="Email">
                        <input type="text" id="contact-phone" placeholder="Phone">
                        <textarea id="contact-message" rows="3"></textarea>
                    </p>
                    <p>
                        <a href="#" class="contact-action" data-ip="' . wp4pm_get_pd_user_ip() . '">Send</a>
                    </p>
                </div>';
            }

            $out .= '<input id="tab-two" type="radio" name="grp">
            <label for="tab-two">Share</label>
                <div>
                <h4>Share This Property</h4>';

                $out .= '<p><small>Share to social networks via the icons below.</small></p>
                <p id="share-buttons">';
                    $out .= get_sharing_buttons($propertyId);
                $out .= '</p>
            </div>

            <input id="tab-three" type="radio" name="grp">
            <label for="tab-three">Print</label>

            <div>
                <h4>Print</h4>';

                if (isset($propertyDetails['brochure_1'])) {
                    $out .= '<a target="_blank" href="' . $propertyDetails['brochure_1'][0] . '" class="supernova-button supernova-button-secondary supernova-button-small">
                        Print brochure
                    </a>';
                } else {
                    $out .= '<a href="javascript:;" onclick="window.print()" class="supernova-button supernova-button-secondary supernova-button-small">
                        Print this page
                    </a>';
                }

            $out .= '</div>
        </section>';

        return $out;
    }
}
