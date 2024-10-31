<?php
function supernova_custom_registration() {
    $out = '';

    if (isset($_POST['supernova_register'])) {
        global $registrationErrors;

    	$registrationErrors = new WP_Error;

        $username = sanitize_user($_POST['username']);
        $password = esc_attr($_POST['password']);
        $email = sanitize_email($_POST['email']);
        $firstName = sanitize_text_field($_POST['fname']);
        $lastName = sanitize_text_field($_POST['lname']);

        if (empty($username) || empty($password) || empty($email)) {
            $registrationErrors->add('field', 'You have to fill in all the required fields.');
        }
        if (!validate_username($username) || username_exists($username) || 4 > strlen($username)) {
            $registrationErrors->add('username_invalid', 'The username is invalid or already in use.');
        }
        if (5 > strlen($password)) {
            $registrationErrors->add('password', 'Your password needs to be longer than 5 characters.');
        }
        if (!is_email($email) || email_exists($email)) {
            $registrationErrors->add('email_invalid', 'The email address is invalid or already in use.');
        }
        if (is_wp_error($registrationErrors)) {
            foreach ($registrationErrors->get_error_messages() as $error) {
                $out .= '<div class="ui-notification ui-notification--warning">' . $error . '</div>';
            }
        }

        if (1 > count($registrationErrors->get_error_messages())) {
            $userData = [
                'user_login' => $username,
                'user_email' => $email,
                'user_pass' => $password,
                'first_name' => $firstName,
                'last_name' => $lastName
            ];
            $user = wp_insert_user($userData);

            $out .= '<div class="ui-notification ui-notification--success"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="check" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-check fa-w-16 fa-fw"><path fill="currentColor" d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z" class=""></path></svg> Registration complete! You can now log into your account.</div>';
        }
    }

    $out .= '<div class="supernova-registration-form">
        <form method="post">
            <p>
                <label for="username">Username <b>*</b></label>
                <input type="text" name="username" id="username">
            </p>
            <p>
                <label for="password">Password <b>*</b></label>
                <input type="password" name="password" id="password">
            </p>
            <p>
                <label for="email">Email Address <b>*</b></label>
                <input type="email" name="email" id="email">
            </p>
            <p>
                <label for="fname">First Name <b>*</b></label>
                <input type="text" name="fname" id="fname">
            </p>
            <p>
                <label for="lname">Last Name <b>*</b></label>
                <input type="text" name="lname" id="lname">
            </p>
            <p>
                <button type="submit" name="supernova_register" value="Register">Register</button>
            </p>
        </form>
    </div>';

    return $out;
}

add_shortcode('supernova-registration', 'supernova_custom_registration');
