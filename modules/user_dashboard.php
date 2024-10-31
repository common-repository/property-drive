<?php
function jtg_user_dashboard() {
    $out = '';

    if (is_user_logged_in()) {
        $userData = get_userdata(get_current_user_id());

        $userExpiryDay = get_the_author_meta('expiry_day', $userData->ID);
        $userExpiryMonth = get_the_author_meta('expiry_month', $userData->ID);
        $userExpiryYear = get_the_author_meta('expiry_year', $userData->ID);

        // Save user details
        if (isset($_POST['supernova-save-user-profile'])) {
            wp_update_user([
                'ID' => $userData->ID,
                'first_name' => sanitize_text_field($_POST['first_name']),
                'last_name' => sanitize_text_field($_POST['last_name']),
                'user_email' => sanitize_email($_POST['user_email'])
            ]);
            update_user_meta($userData->ID, 'user_phone', sanitize_text_field($_POST['user_phone']));

            $out .= '<p>Profile updated successfully.</p>';
        }

        $out .= '<h2>' . $userData->first_name . ' ' . $userData->last_name;
        $out .= '</h2>';

        $out .= '<p>Hi '. $userData->first_name . ', you can manage your profile and account settings. <a href="' . wp_logout_url(home_url()) . '">Logout</a>.</p>';

        $out .= '<details class="user-dashboard-menu-item">
            <summary title="Click to expand this section">
                My Profile
                <small>Manage profile details and settings.</small>
            </summary>

            <h3>My Profile</h3>
            <form class="supernova-profile-form" method="post">
                <div class="wp-block-columns has-2-columns">
                    <div class="wp-block-column">
                        <p>
                            First Name<br>
                            <input type="text" id="first_name" name="first_name" value="' . $userData->first_name . '" size="48">
                        </p>
                        <p>
                            Last Name<br>
        					<input type="text" id="last_name" name="last_name" value="' . $userData->last_name . '" size="48">
                        </p>
                    </div>
                    <div class="wp-block-column">
                        <p>
                            Phone Number<br>
                            <input type="text" id="user_phone" name="user_phone" value="' . get_the_author_meta('user_phone', $userData->ID) . '" size="48">
                        </p>
                        <p>
                            Email Address<br>
                            <input type="email" id="user_email" name="user_email" value="' . $userData->user_email . '" size="48">
                        </p>
                        <p>
        					<input type="submit" name="supernova-save-user-profile" value="Update My Profile">
                        </p>
                    </div>
                </div>
            </form>
        </details>

        <details class="user-dashboard-menu-item">
            <summary title="Click to expand this section">
                My Favourite Properties
                <small>Save favourite properties in a shortlist.</small>
            </summary>

            <h3>Favourite Properties</h3>';

			$property_favourites = get_posts([
                'post_type' => 'favourite',
                'posts_per_page' => -1,
                'post_status' => 'private',
                'author' => get_current_user_id(),
            ]);

            if ($property_favourites) {
                $out .= '<div>
                    <table width="100%">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Property Name</th>
                                <th>Added</th>
                            </tr>
                        </thead>
                        <tbody>';

                            foreach ($property_favourites as $favourite) {
                                $propertyId = get_post_meta($favourite->ID, '_property_id', true);
				    			$property_title = get_the_title($propertyId);
                                $property_url = get_permalink($propertyId);

                                $out .= '<tr data-property-id="' . $propertyId . '" id="favourite-row-' . $favourite->ID . '">
                                    <td>
                                        <a href="#" class="remove-user-favourite" data-favourite-id="' . $favourite->ID . '">
                                            Delete
                                        </a>
                                    </td>
                                    <td>
                                        <a href="'.$property_url.'" target="_blank">'.$property_title.'</a>
                                    </td>
                                    <td>' . $favourite->post_date . '</td>
                                </tr>';
                            }

                        $out .= '</tbody>
                    </table>
                </div>';
			} else {
                $out .= '<p>You have not saved any properties to your favourites list yet, you can do this by visiting a property and clicking the red heart.</p>
				<p>To remove a favourite, you can do so from here or clicking the empty heart on the property.</p>';
            }
        $out .= '</details>';

        $out .= '<details class="user-dashboard-menu-item">
            <summary title="Click to expand this section">
                My Documents
                <small>Upload agent requested documents.</small>
            </summary>

            <h4>My Documents</h4>';

            $attachmentsQuery = new WP_Query([
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'posts_per_page' => -1,
                'author' => $userData->ID
            ]);

            $out .= '
            <div style="display: flex;">
                <div style="height: 360px; overflow: auto; flex-basis: 50%; padding: 24px; background-color: #f1f1f1; border-radius: 3px; align-self: flex-start;">
                <h4>My Uploaded Documents</h4>';

                    if ($attachmentsQuery->have_posts()) {
                        while ($attachmentsQuery->have_posts()) {
                            $attachmentsQuery->the_post();
                            $out .= '<div>
                                <a href="' . $attachmentsQuery->post->guid . '" class="ui-button" target="_blank">View document</a>' . $attachmentsQuery->post->post_title .
                            '</div>';
                        }
                    } else {
                        $out .= '<p>No documents.</p>';
                    }

            $out .= '</div>
            <div style="flex-basis: 50%; padding: 24px; align-self: flex-start;">
                <h4>Upload New Document</h4>
                <form method="post" enctype="multipart/form-data">
                    <p>
                        <input type="file" name="userFileInput" id="userFileInput">
                    </p>
                    <p>
                        <input type="submit" value="Upload" name="upload">
                    </p>
                </form>
            </div>';

        $out .= '</details>';

        return $out;
    } else {
        $args = [
            'echo' => false,
            'redirect' => get_permalink((int) get_option('supernova_account_page_id'))
        ];

        $out .= '<div class="supernova-account-container">
            <div class="supernova-account-container--box">
                <h4>Log In</h4>
                <p>Log into your account or sign up for instant access to your user dashboard.</p>' .

                wp_login_form($args) .

                '<p>
                    <small>Your account gives you access to alerts, notifications, favourites and more.</small>
                </p>
            </div>
            <div class="supernova-account-container--box">
                <h4>Sign Up</h4>' .

                supernova_custom_registration() . '
            </div>
        </div>

        <p><small>By entering your email, you agree that we may send you emails about your activity on this site. You may unsubscribe at any time. Learn more about our Privacy Policy. This is a secure connection. Data sent within this form is transferred completely securely. The entire transmission is SSL-encrypted. This means that any data you submit, cannot be retrieved by 3rd parties.</small></p>';

        return $out;
    }
}

add_shortcode('user-dashboard', 'jtg_user_dashboard');
