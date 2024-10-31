<?php
function wp4pm_get_sidebar_classic($propertyId, $propertyDetails) {
    $agentEmailAddress = get_option('agency_email');
    if ($propertyDetails['agent_email'][0]) {
        $agentEmailAddress = $propertyDetails['agent_email'][0];
    }

    $out = '<div class="sidebar-section sidebar-identity">
        <div class="contact-image">';
            if (!empty($propertyDetails['agent_photo'][0])) {
                $out .= '<p><img src="' . $propertyDetails['agent_photo'][0] . '" alt="" width="90%"></p>';
            } else if (!empty(get_option('agency_logo'))) {
                $imageUri = get_option('agency_logo');
                $out .= '<p><img src="' . $imageUri . '" alt="" width="90%"></p>';
            }
        $out .= '</div>';

        if ($propertyDetails['agent_name'][0]) {
            $out .= '<h4>' . stripslashes($propertyDetails['agent_name'][0]) . '</h4>';
        } else {
            $out .= '<h4>' . stripslashes(get_option('agency_name')) . '</h4>';
        }

        if (isset($propertyDetails['agent_number'][0])) {
            if ($propertyDetails['agent_number'][0]) {
                $out .= '<a href="tel:' . $propertyDetails['agent_number'][0] . '" class="button button-primary button-small">' . $propertyDetails['agent_number'][0] . '</a>';
            }
        } else {
            $out .= '<a href="tel:' . get_option('agency_phone') . '" class="button button-primary button-small">' . get_option('agency_phone') . '</a>';
        }

        $out .= '<a href="mailto:' . $agentEmailAddress . '" class="button button-secondary button-small">' . $agentEmailAddress . '</a>';
    $out .= '</div>';

    $out .= '<div class="sidebar-section sidebar-sharing">
        <div id="share-buttons">';
            $out .= get_sharing_buttons($propertyId);
        $out .= '</div>
    </div>';

    return $out;
}
