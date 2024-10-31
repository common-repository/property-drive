<?php
function pm_menu_links() {
    add_menu_page('Property Drive Settings', 'Property Drive', 'manage_options', 'property_drive', 'wppd_build_admin_page', 'dashicons-admin-home', 4);
}

add_action('admin_menu', 'pm_menu_links');

function wppd_dashboard_help() {
    // Get basic statistics
    $publishedProperties = 0;
    $draftProperties = 0;
    $countProperties = wp_count_posts('property');
    if (!empty((array) $countProperties)) {
        $publishedProperties = $countProperties->publish;
        $draftProperties = $countProperties->draft;
    }

    $propertyArgs = [
        'post_type' => 'property',
        'meta_query' => [
            [
                'key' => 'property_view_count'
            ]
        ]
    ];

    $propertyViews = 0;
    $propertyQuery = new WP_Query($propertyArgs);
    if ($propertyQuery->have_posts()) {
        while ($propertyQuery->have_posts()) {
            $propertyQuery->the_post();

            $views = get_post_meta(get_the_ID(), 'property_view_count', true);
            if (is_numeric($views)) {
                $propertyViews += (int) $views;
            }
        }
    }

    echo '<div class="ui-flex-container supernova-stats">
        <div class="ui-flex-item supernova-stat">
            <b>' . number_format($publishedProperties) . '</b>
            Published properties
        </div>
        <div class="ui-flex-item supernova-stat">
            <b>' . number_format($draftProperties) . '</b>
            Draft properties
        </div>
        <div class="ui-flex-item supernova-stat">
            <b>' . number_format($propertyViews) . '</b>
            Property views
        </div>
    </div>

    <hr>';
}



function wppd_build_admin_page() {
    $nonce = wp_create_nonce('wppd_property_admin');

    $jtg_plugin_logo = plugin_dir_url(dirname(__DIR__)) . 'assets/images/4property-logo.png';

	$plugin_version_dir = plugin_dir_path(dirname(__DIR__)).'property-drive.php';
	$plugin_version = get_plugin_data($plugin_version_dir, 'Version');

    $tab = (filter_has_var(INPUT_GET, 'tab')) ? filter_input(INPUT_GET, 'tab') : 'dashboard';
    $section = 'admin.php?page=property_drive&amp;tab=';
	?>
    <div class="wrap">
        <h1>Property Drive</h1>

        <h2 class="nav-tab-wrapper">
            <a href="<?php echo $section; ?>dashboard" class="nav-tab <?php echo $tab === 'dashboard' ? 'nav-tab-active' : ''; ?>">Dashboard</a>
            <a href="<?php echo $section; ?>agency" class="nav-tab <?php echo $tab === 'agency' ? 'nav-tab-active' : ''; ?>">Agency Details</a>
            <a href="<?php echo $section; ?>general" class="nav-tab <?php echo $tab === 'general' ? 'nav-tab-active' : ''; ?>">Settings</a>
            <a href="<?php echo $section; ?>design" class="nav-tab <?php echo $tab === 'design' ? 'nav-tab-active' : ''; ?>">Design</a>
            <a href="<?php echo $section; ?>search" class="nav-tab <?php echo $tab === 'search' ? 'nav-tab-active' : ''; ?>">Search</a>
            <a href="<?php echo $section; ?>users" class="nav-tab <?php echo $tab === 'users' ? 'nav-tab-active' : ''; ?>">Users</a>
            <a href="<?php echo $section; ?>pro" class="nav-tab nav-tab--pro <?php echo $tab === 'pro' ? 'nav-tab-active' : ''; ?>">Pro</a>
        </h2>

        <?php if ($tab === 'dashboard') { ?>
            <h3 class="identityblock">WP Property<br>Drive <code class="codeblock"><?php echo $plugin_version['Version']; ?></code></h3>

            <h2 class="titleblock">Welcome to WP Property Drive! Get the most out of your properties.</h2>

            <?php wppd_dashboard_help(); ?>

            <p>
                <a href="https://www.4property.com/"><img src="<?php echo $jtg_plugin_logo; ?>" width="100" alt="4Property"></a>
            </p>
        <?php } else if ($tab === 'agency') { ?>
            <h2><?php _e('Agency Details', 'property-drive'); ?></h2>

            <?php
            if (isset($_POST['save_agency_settings'])) {
                update_option('agency_name', sanitize_text_field(trim($_POST['agency_name'])));
                update_option('agency_email', sanitize_email($_POST['agency_email']));
                update_option('agency_phone', sanitize_text_field($_POST['agency_phone']));

                update_option('force_agency_name', (int) $_POST['force_agency_name']);
                update_option('force_agency_email', (int) $_POST['force_agency_email']);
                update_option('force_agency_phone', (int) $_POST['force_agency_phone']);
                update_option('mask_agency_email', (int) $_POST['mask_agency_email']);
                update_option('mask_agency_phone', (int) $_POST['mask_agency_phone']);

                update_option('agency_logo', $_POST['agency_logo']);

                echo '<div class="updated notice is-dismissible"><p>Settings updated successfully!</p></div>';
            }
            ?>

            <p>This information is used on the single property sidebar. If you have a feed being imported with agents listed, your sidebar will automatically show those values. In case they do not exist, this information will be shown.</p>
            <p>The email address and agency name are also used to send system emails to users of your website.</p>

            <form method="post">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><label>Estate Agency Name</label></th>
                            <td>
                                <input type="text" name="agency_name" value="<?php echo stripslashes(get_option('agency_name')); ?>" class="regular-text">
                                <input type="checkbox" name="force_agency_name" value="1" <?php checked(1, (int) get_option('force_agency_name')); ?>> Force this information, regardless of feed data
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label>Estate Agency Email Address</label></th>
                            <td>
                                <input type="email" name="agency_email" value="<?php echo get_option('agency_email'); ?>" class="regular-text">
                                <input type="checkbox" name="force_agency_email" value="1" <?php checked(1, (int) get_option('force_agency_email')); ?>> Force this information, regardless of feed data
                                <input type="checkbox" name="mask_agency_email" value="1" <?php checked(1, (int) get_option('mask_agency_email')); ?>> Mask this information
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label>Estate Agency Phone Number</label></th>
                            <td>
                                <input type="text" name="agency_phone" value="<?php echo get_option('agency_phone'); ?>" class="regular-text">
                                <input type="checkbox" name="force_agency_phone" value="1" <?php checked(1, (int) get_option('force_agency_phone')); ?>> Force this information, regardless of feed data
                                <input type="checkbox" name="mask_agency_phone" value="1" <?php checked(1, (int) get_option('mask_agency_phone')); ?>> Mask this information
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label>Estate Agency Logo</label></th>
                            <td>
                                <div class="jtg-admin-logo">
                                    <?php
                                    global $wpdb;

                                    if (get_option('agency_logo')) {
                                        $image_url = get_option('agency_logo');
                                        echo '<p><img src="' . $image_url . '" style="max-width: 400px;" alt=""></p>';
                                    } else {
                                        echo '<p>No image selected.</p>';
                                    }

                                    if (!empty($_POST['image'])) {
                                        $image_url = esc_url($_POST['image']);
                                    }

                                    wp_enqueue_media();
                                    ?>
                                    <input id="pm-image-url" type="hidden" name="agency_logo" value="<?php echo get_option('agency_logo'); ?>">
                                    <input id="pm-upload-image-btn" type="button" class="jtg-logo-upload-btn button button-secondary" value="Upload Logo">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><input type="submit" name="save_agency_settings" class="button button-primary" value="Save Changes"></th>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </form>

            <script>
            jQuery(document).ready(function () {
                var mediaUploader;

                jQuery('#pm-upload-image-btn').click(function (e) {
                    e.preventDefault();

                    // If the uploader object has already been created, reopen the dialog
                    if (mediaUploader) {
                        mediaUploader.open();
                        return;
                    }
                    // Extend the wp.media object
                    mediaUploader = wp.media.frames.file_frame = wp.media({
                        title: 'Choose Image',
                        button: {
                            text: 'Choose Image'
                        },
                        multiple: false
                    });

                    // When a file is selected, grab the URL and set it as the text field's value
                    mediaUploader.on('select', function () {
                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                        jQuery('#pm-image-url').val(attachment.url);
                    });

                    // Open the uploader dialog
                    mediaUploader.open();
                });
            });
            </script>
        <?php } else if ($tab === 'general') { ?>
            <h2><?php _e('General Settings', 'property-drive'); ?></h2>

            <?php
            if (isset($_POST['save_general_settings'])) {
                update_option('default_property_order', sanitize_text_field($_POST['default_property_order']));
                update_option('jtg_currency', sanitize_text_field($_POST['jtg_currency']));

                update_option('map_provider', sanitize_text_field($_POST['map_provider']));
                update_option('osm_scrollzoom', (int) $_POST['osm_scrollzoom']);

                update_option('allow_favourites', (int) $_POST['allow_favourites']);

                update_option('allow_quick_contact', (int) $_POST['allow_quick_contact']);

                update_option('show_related_properties', (int) $_POST['show_related_properties']);

                update_option('inactive_not_clickable', (int) $_POST['inactive_not_clickable']);

                update_option('use_single_sidebar', (int) $_POST['use_single_sidebar']);
                update_option('reusable_sidebar_id', (int) $_POST['reusable_sidebar_id']);
                update_option('use_single_content_accordion', (int) $_POST['use_single_content_accordion']);

                // Delete old options
                delete_option('use_single_sidebar_widgets');

                echo '<div class="updated notice is-dismissible"><p>Settings updated successfully!</p></div>';
            }
            ?>

            <form method="post">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><label>Currency</label></th>
                            <td>
                                <select name="jtg_currency">
                                    <option value="euro" <?php if ((string) get_option('jtg_currency') === 'euro') echo 'selected'; ?>>Euro</option>
                                    <option value="gbp" <?php if ((string) get_option('jtg_currency') === 'gbp') echo 'selected'; ?>>Pound Sterling</option>
                                    <option value="usd" <?php if ((string) get_option('jtg_currency') === 'usd') echo 'selected'; ?>>US Dollar</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label>Property Features</label></th>
                            <td>
                                <p>
                                    <input type="checkbox" id="allow_quick_contact" name="allow_quick_contact" value="1" <?php echo ((int) get_option('allow_quick_contact') === 1) ? 'checked' : ''; ?>>
                                    <label for="allow_quick_contact">Allow quick contact (property enquiry)</label>
                                </p>
                                <p>
                                    <input type="checkbox" id="allow_favourites" name="allow_favourites" value="1" <?php echo ((int) get_option('allow_favourites') === 1) ? 'checked' : ''; ?>>
                                    <label for="allow_favourites">Allow favourite properties</label>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" colspan="2"><h3>Property Grid Settings</h3></th>
                        </tr>
                        <tr>
                            <th scope="row"><label>Property Order</label></th>
                            <td>
                                <select name="default_property_order">
                                    <option value="property_order" <?php if ((string) get_option('default_property_order') === 'property_order') echo 'selected'; ?>>Native property order (default, by date published)</option>
                                    <option value="property_order_modified" <?php if ((string) get_option('default_property_order') === 'property_order_modified') echo 'selected'; ?>>Native property order (by date modified)</option>
                                    <option value="date_modified" <?php if ((string) get_option('default_property_order') === 'date_modified') echo 'selected'; ?>>Last modified</option>
                                    <option value="manual" <?php if ((string) get_option('default_property_order') === 'manual') echo 'selected'; ?>>Manual (drag &amp; drop to reorder, not recommended)</option>
                                </select>
                                <br><small>The default property order is property status, then import date.</small>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label>Grid Features</label></th>
                            <td>
                                <p>
                                    <input type="checkbox" id="inactive_not_clickable" name="inactive_not_clickable" value="1" <?php echo ((int) get_option('inactive_not_clickable') === 1) ? 'checked' : ''; ?>>
                                    <label for="inactive_not_clickable">Make <b>Sold</b>/<b>Let</b> properties not clickable</label>
                                    <br><small>Note that this option will make the single property pages inaccessible to search engines.</small>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" colspan="2"><h3>Single Property Settings</h3></th>
                        </tr>
                        <tr>
                            <th scope="row"><label for="use_single_sidebar">Sidebar Style</label></th>
                            <td>
                                <select name="use_single_sidebar" id="use_single_sidebar">
                                    <option value="0" <?php if ((int) get_option('use_single_sidebar') === 0) echo 'selected'; ?>>Classic sidebar</option>
                                    <option value="1" <?php if ((int) get_option('use_single_sidebar') === 1) echo 'selected'; ?>>Strip sidebar (classic)</option>
                                    <option value="3" <?php if ((int) get_option('use_single_sidebar') === 3) echo 'selected'; ?>>Strip sidebar (modern)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="reusable_sidebar_id">Reusable Sidebar Customiser</label></th>
                            <td>
                                <?php
                                $reusableSidebarId = get_option('reusable_sidebar_id');

                                $args = [
                                    'post_type' => 'wp_block',
                                    'posts_per_page' => -1,
                                    'order' => 'ASC',
                                    'orderby' => 'title'
                                ];
                                $wpBlockQuery = new WP_Query($args);

                                $out = '<select name="reusable_sidebar_id" id="reusable_sidebar_id">';
                                    $out .= '<option value="">Select a reusable block...</option>';

                                    if ($wpBlockQuery->have_posts()) {
                                        while ($wpBlockQuery->have_posts()) {
                                            $wpBlockQuery->the_post();

                                            $selected = ((int) $reusableSidebarId === (int) get_the_ID()) ? 'selected' : '';
                                            $out .= '<option value="' . get_the_ID() . '" ' . $selected . '>' . get_the_title() . '</option>';
                                        }
                                    }
                                $out .= '</select>
                                <br><small>Select your reusable sidebar or <a href="' . admin_url('edit.php?post_type=wp_block') . '">create one now</a>.</small>';

                                echo $out;
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="use_single_content_accordion">Content Style</label></th>
                            <td>
                                <input type="checkbox" id="use_single_content_accordion" name="use_single_content_accordion" value="1" <?php echo ((int) get_option('use_single_content_accordion') === 1) ? 'checked' : ''; ?>>
                                <label for="use_single_content_accordion">Convert content to accordion</label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="map_provider">Maps</label></th>
                            <td>
                                <p>
                                    <select id="map_provider" name="map_provider">
                                        <option value="osm" <?php if ((string) get_option('map_provider') === 'osm') echo 'selected'; ?>>OpenStreetMap (default)</option>
                                    </select>
                                </p>
                                <p>
                                    <input type="checkbox" id="osm_scrollzoom" name="osm_scrollzoom" value="1" <?php echo ((int) get_option('osm_scrollzoom') === 1) ? 'checked' : ''; ?>>
                                    <label for="osm_scrollzoom">Disable OpenStreetMap zoom when scrolling (not recommended)</label>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label>Content Features</label></th>
                            <td>
                                <p>
                                    <input type="checkbox" id="show_related_properties" name="show_related_properties" value="1" <?php echo ((int) get_option('show_related_properties') === 1) ? 'checked' : ''; ?>>
                                    <label for="show_related_properties">Show related properties</label>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><input type="submit" name="save_general_settings" class="button button-primary" value="Save Changes"></th>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </form>
        <?php } else if ($tab === 'design') { ?>
            <h2>Design Settings</h2>

            <?php
            if (isset($_POST['save_design_settings'])) {
                update_option('status_overlay_style', sanitize_text_field($_POST['status_overlay_style']));

                update_option('show_status_badge', (int) $_POST['show_status_badge']);
                update_option('show_property_card_description', (int) $_POST['show_property_card_description']);

                update_option('ribbon_colour_sale', sanitize_text_field($_POST['ribbon_colour_sale']));
                update_option('ribbon_colour_sale_agreed', sanitize_text_field($_POST['ribbon_colour_sale_agreed']));
                update_option('ribbon_colour_sold', sanitize_text_field($_POST['ribbon_colour_sold']));

                update_option('property_map_id', (int) $_POST['property_map_id']);
                update_option('property_map_ajax', (int) $_POST['property_map_ajax']);

                // Property brochure
                update_option('cinematic_overlay', (int) $_POST['cinematic_overlay']);

                // Flickity options
                update_option('flickity_wrapAround', (int) $_POST['flickity_wrapAround']);
                update_option('flickity_groupCells', (int) $_POST['flickity_groupCells']);
                update_option('flickity_groupCellsValue', (int) $_POST['flickity_groupCellsValue']);
                update_option('flickity_autoPlay', (int) $_POST['flickity_autoPlay']);
                //

                if ((int) $_POST['force_cinematic_overlay'] === 1) {
                    $tempArgs = [
                        'fields' => 'ids',
                        'posts_per_page' => -1,
                        'post_type' => 'property'
                    ];
                    $tempQuery = new WP_Query($tempArgs);

                    if ($tempQuery->have_posts()) {
                        while ($tempQuery->have_posts()) {
                            $tempQuery->the_post();

                            update_post_meta(get_the_ID(), '_property_template', 999);
                        }
                    }
                }

                // General design
                update_option('flex_grid_size', (int) $_POST['flex_grid_size']);

                echo '<div class="updated notice is-dismissible"><p>Settings updated successfully!</p></div>';
            }
            ?>

            <form method="post">
                <div class="flex-container">
                    <div class="flex-item flex-item-half flex-item-box">
                        <h3>Property Grid Settings</h3>
                        <p>These settings apply to the property grid page and the individual property cards, wherever they may appear.</p>

                        <table class="form-table">
                            <tbody>
                                <tr>
                                    <th scope="row"><label>Grid Size</label></th>
                                    <td>
                                        <p>
                                            <select name="flex_grid_size">
                                                <option value="0">Select a property grid size option...</option>
                                                <option value="2" <?php if ((int) get_option('flex_grid_size') === 2) echo 'selected'; ?>>2</option>
                                                <option value="3" <?php if ((int) get_option('flex_grid_size') === 3) echo 'selected'; ?>>3 (recommended)</option>
                                                <option value="4" <?php if ((int) get_option('flex_grid_size') === 4) echo 'selected'; ?>>4</option>
                                            </select> per row
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label>Card Status</label></th>
                                    <td>
                                        <p>
                                            <select name="status_overlay_style" id="status_overlay_style">
                                                <option value="">Select an overlay status style...</option>
                                                <option value="hidden" <?php if ((string) get_option('status_overlay_style') === 'hidden') echo 'selected'; ?>>Hidden</option>
                                                <option value="ribbon" <?php if ((string) get_option('status_overlay_style') === 'ribbon') echo 'selected'; ?>>Ribbon</option>
                                                <option value="ribbon-corner" <?php if ((string) get_option('status_overlay_style') === 'ribbon-corner') echo 'selected'; ?>>Ribbon (corner cover)</option>
                                                <option value="pill" <?php if ((string) get_option('status_overlay_style') === 'pill') echo 'selected'; ?>>Pill (rounded corners)</option>
                                                <option value="sticker" <?php if ((string) get_option('status_overlay_style') === 'sticker') echo 'selected'; ?>>Sticker (sharp corners)</option>
                                            </select>
                                            <label for="status_overlay_style">Status overlay style</label>
                                        </p>
                                        <p>
                                            <input type="checkbox" id="show_status_badge" name="show_status_badge" value="1" <?php echo ((int) get_option('show_status_badge') === 1) ? 'checked' : ''; ?>> <label for="show_status_badge">Show status (property type and badge, below address)</label>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label>Card Description</label></th>
                                    <td>
                                        <p>
                                            <input type="checkbox" id="show_property_card_description" name="show_property_card_description" value="1" <?php echo ((int) get_option('show_property_card_description') === 1) ? 'checked' : ''; ?>> <label for="show_property_card_description">Show description excerpt</label>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label>Card Status Colours</label></th>
                                    <td>
                                        <p>
                                            <label>Ribbon colour (<b>For Sale</b> and <b>To Let</b>)</label>
                                            <input class="color-picker" data-default-color="#1abc9c" name="ribbon_colour_sale" type="text" value="<?php echo get_option('ribbon_colour_sale'); ?>">
                                        </p>
                                        <p>
                                            <label>Ribbon colour (<b>Sale Agreed</b> and <b>Let Agreed</b>)</label>
                                            <input class="color-picker" data-default-color="#e67e22" name="ribbon_colour_sale_agreed" type="text" value="<?php echo get_option('ribbon_colour_sale_agreed'); ?>">
                                        </p>
                                        <p>
                                            <label>Ribbon colour (<b>Sold</b>, <b>Let</b> and <b>Has Been Let</b>)</label>
                                            <input class="color-picker" data-default-color="#e74c3c" name="ribbon_colour_sold" type="text" value="<?php echo get_option('ribbon_colour_sold'); ?>">
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" colspan="2"><hr></th>
                                </tr>
                                <tr>
                                    <th scope="row"><label>Map Navigator Page</label></th>
                                    <td>
                                        <p>
                                            <?php
                                            wp_dropdown_pages([
                                                'name' => 'property_map_id',
                                                'selected' => get_option('property_map_id')
                                            ]);
                                            ?>
                                            <br><small>This is the map navigator page.</small>
                                        </p>
                                        <p>
                                            <input type="checkbox" id="property_map_ajax" name="property_map_ajax" value="1" <?php echo ((int) get_option('property_map_ajax') === 1) ? 'checked' : ''; ?>> <label for="property_map_ajax">Open map navigator in modal window</label>
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex-item flex-item-half flex-item-box">
                        <h3>Property Brochure Settings<br><small>Single property page</small></h3>
                        <p>These settings apply to the single property page. Some of these settings can be overridden based on the selected single property template.</p>

                        <table class="form-table">
                            <tbody>
                                <tr>
                                    <th scope="row"><label>Property Hero</label></th>
                                    <td>
                                        <p>
                                            <select name="cinematic_overlay">
                                                <option value="">Select a property hero type...</option>
                                                <option value="0" <?php if ((int) get_option('cinematic_overlay') === 0) echo 'selected'; ?>>No overlay (classic)</option>
                                                <option value="1" <?php if ((int) get_option('cinematic_overlay') === 1) echo 'selected'; ?>>Cinematic hero overlay (full screen)</option>
                                                <option value="2" <?php if ((int) get_option('cinematic_overlay') === 2) echo 'selected'; ?>>Cover slide overlay (half screen)</option>
                                                <option value="3" <?php if ((int) get_option('cinematic_overlay') === 3) echo 'selected'; ?>>Flickity (Sydney) carousel (full width)</option>
                                                <option value="4" <?php if ((int) get_option('cinematic_overlay') === 4) echo 'selected'; ?>>Flickity (Parsley) slider (full width)</option>
                                            </select>
                                            <br><input type="checkbox" id="force_cinematic_overlay" name="force_cinematic_overlay" value="1" <?php echo ((int) get_option('force_cinematic_overlay') === 1) ? 'checked' : ''; ?>>
                                            <label for="force_cinematic_overlay">Force reset this overlay for all properties</label>
                                        </p>
                                        <hr>
                                        <p>
                                            <b>Flickity Options</b>
                                        </p>
                                        <p>
                                            <input type="checkbox" id="flickity_wrapAround" name="flickity_wrapAround" value="1" <?php echo ((int) get_option('flickity_wrapAround') === 1) ? 'checked' : ''; ?>>
                                            <label for="flickity_wrapAround"><a href="https://flickity.metafizzy.co/options.html#wraparound">wrapAround</a> (bool)</label>
                                            <br>
                                            <input type="checkbox" id="flickity_groupCells" name="flickity_groupCells" value="1" <?php echo ((int) get_option('flickity_groupCells') === 1) ? 'checked' : ''; ?>>
                                            <label for="flickity_groupCells"><a href="https://flickity.metafizzy.co/options.html#groupcells">groupCells</a> (bool)</label>
                                            <br>
                                            <input type="number" id="flickity_groupCellsValue" name="flickity_groupCellsValue" placeholder="1" value="<?php echo (int) get_option('flickity_groupCellsValue'); ?>">
                                            <label for="flickity_groupCellsValue"><a href="https://flickity.metafizzy.co/options.html#groupcells">groupCells</a> (int)</label>
                                            <br>
                                            <input type="number" id="flickity_autoPlay" name="flickity_autoPlay" placeholder="3000" value="<?php echo (int) get_option('flickity_autoPlay'); ?>">
                                            <label for="flickity_autoPlay">
                                                <a href="https://flickity.metafizzy.co/options.html#autoplay">autoPlay</a> (int, milliseconds)
                                                <br><small>Uses <code>pauseAutoPlayOnHover: true</code></small>
                                            </label>
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><input type="submit" name="save_design_settings" class="button button-primary" value="Save Changes"></th>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </form>
        <?php } else if ($tab === 'search') { ?>
            <h2>Search Form Builder</h2>

            <?php
            if (isset($_POST['save_search_settings'])) {
                update_option('search_field_array', sanitize_text_field($_POST['search_field_array']));

                update_option('search_field_group', (int) $_POST['search_field_group']);
                update_option('search_field_type', (int) $_POST['search_field_type']);
                update_option('search_field_status', (int) $_POST['search_field_status']);
                update_option('search_field_status_group', (int) $_POST['search_field_status_group']);
                update_option('search_field_price', (int) $_POST['search_field_price']);
                update_option('search_field_beds', (int) $_POST['search_field_beds']);
                update_option('search_field_baths', (int) $_POST['search_field_baths']);
                update_option('search_field_keyword', (int) $_POST['search_field_keyword']);

                update_option('search_field_location', (int) $_POST['search_field_location']);
                update_option('search_field_multitype', (int) $_POST['search_field_multitype']);

                update_option('search_field_features', (int) $_POST['search_field_features']);

                update_option('search_results_page', (int) $_POST['search_results_page']);

                echo '<div class="updated notice is-dismissible"><p>Settings updated successfully!</p></div>';
            }
            ?>

            <form method="post">
                <input type="hidden" class="regular-text" name="search_field_array" id="form-items-order" value="<?php echo get_option('search_field_array'); ?>">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><label>Search form builder</label></th>
                            <td>
                                <p>Select desired fields and drag them around to build your search form. Use the <code>[search_form]</code> shortcode in any post, page or widget to show the search form.</p>
                                <ul id="form-items">
                                    <?php
                                    if (empty(get_option('search_field_array'))) {
                                        update_option('search_field_array', 'group|type|status|status_group|price|beds|baths|keyword|location|multitype|features');
                                    }

                                    $searchFieldArray = explode('|', (string) get_option('search_field_array'));
                                    foreach ($searchFieldArray as $searchFieldItem) {
                                        switch ($searchFieldItem) {
                                            case 'group' :
                                                $fieldType = 'Property Group';
                                                break;
                                            case 'type' :
                                                $fieldType = 'Property Type';
                                                break;
                                            case 'status' :
                                                $fieldType = 'Property Status (dropdown)';
                                                break;
                                            case 'status_group' :
                                                $fieldType = 'Property Status (tab group)';
                                                break;
                                            case 'features' :
                                                $fieldType = 'Feature Selector (multiselect)';
                                                break;
                                            case 'price' :
                                                $fieldType = 'Price Range';
                                                break;
                                            case 'beds' :
                                                $fieldType = 'Bedroom Selector';
                                                break;
                                            case 'baths' :
                                                $fieldType = 'Bathroom Selector';
                                                break;
                                            case 'keyword' :
                                                $fieldType = 'Custom Keyword';
                                                break;
                                            case 'location' :
                                                $fieldType = 'Property Location (multiselect)';
                                                break;
                                            case 'multitype' :
                                                $fieldType = 'Property Type (multiselect)';
                                                break;
                                        }

                                        echo '<li data-id="' . $searchFieldItem . '">
                                            <svg class="svg-inline--fa fa-bars fa-w-14 fa-fw" aria-hidden="true" focusable="false" data-prefix="fa" data-icon="bars" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg="" height="16"><path fill="currentColor" d="M16 132h416c8.837 0 16-7.163 16-16V76c0-8.837-7.163-16-16-16H16C7.163 60 0 67.163 0 76v40c0 8.837 7.163 16 16 16zm0 160h416c8.837 0 16-7.163 16-16v-40c0-8.837-7.163-16-16-16H16c-8.837 0-16 7.163-16 16v40c0 8.837 7.163 16 16 16zm0 160h416c8.837 0 16-7.163 16-16v-40c0-8.837-7.163-16-16-16H16c-8.837 0-16 7.163-16 16v40c0 8.837 7.163 16 16 16z"></path></svg> <input type="checkbox" id="search_field_' . $searchFieldItem . '" name="search_field_' . $searchFieldItem . '" value="1" ' . (((int) get_option('search_field_' . $searchFieldItem) === 1) ? 'checked' : '') . '>
                                            <label for="search_field_' . $searchFieldItem . '">' . $fieldType . '</label>
                                        </li>';
                                    }
                                    ?>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="search_results_page">Search results page</label></th>
                            <td>
                                <?php
                                wp_dropdown_pages([
                                    'id' => 'search_results_page',
                                    'name' => 'search_results_page',
                                    'selected' => (int) get_option('search_results_page')
                                ]);
                                ?>
                                <br><small>This is the search results page.</small>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><input type="submit" name="save_search_settings" class="button button-primary" value="Save Changes"></th>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </form>
        <?php } else if ($tab === 'users') {
            if (isset($_POST['save_users_settings'])) {
                update_option('supernova_account_page_id', (int) $_POST['supernova_account_page_id']);

                echo '<div class="updated notice is-dismissible"><p>Settings updated successfully!</p></div>';
            }
            ?>
            <h3>Users Settings</h3>

            <form method="post" action="">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><label>User Account Page</label></th>
                            <td>
                                <p>
                                    <?php
                                    wp_dropdown_pages([
                                        'name' => 'supernova_account_page_id',
                                        'selected' => (int) get_option('supernova_account_page_id')
                                    ]);
                                    ?>
                                    <br><small>This is the user account/dashboard page. Use the <code>[user-dashboard]</code> shortcode to display the login/registration form.</small>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label>Available Features</label></th>
                            <td>
                                <p>
                                    <input type="checkbox" checked disabled> Profile Editor<br>
                                    <input type="checkbox" checked disabled> Favourite Properties<br>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <hr>

                <p><input type="submit" name="save_users_settings" value="Save Changes" class="button button-primary"></p>
            </form>
        <?php } else if ($tab === 'pro') { ?>
            <h2>WP Property Drive PRO</h2>

            <div class="flex-container">
                <div class="flex-item flex-item-half flex-item-box">
                    <h3>What's included?</h3>

                    <ul class="feature-list--pro">
                        <li><span class="dashicons dashicons-yes-alt"></span> Exclusive Irish property market integration</li>
                        <li><span class="dashicons dashicons-yes-alt"></span> <a href="https://www.4property.com/" rel="external noopener">Property Drive</a> integration</li>
                        <li><span class="dashicons dashicons-yes-alt"></span> <a href="https://www.daft.ie/" rel="external noopener">Daft</a> integration</li>
                        <li><span class="dashicons dashicons-yes-alt"></span> <a href="https://acquaint.ie/" rel="external noopener">Acquaint CRM</a> integration</li>
                        <li><span class="dashicons dashicons-yes-alt"></span> Additional styles and themes</li>
                        <li><span class="dashicons dashicons-yes-alt"></span> Additional property hero templates</li>
                        <li><span class="dashicons dashicons-yes-alt"></span> SEO features</li>
                        <li><span class="dashicons dashicons-yes-alt"></span> Reusable block support for the single property template</li>
                        <li><span class="dashicons dashicons-yes-alt"></span> EU GDPR compliancy</li>
                        <li><span class="dashicons dashicons-yes-alt"></span> And lots more!</li>
                    </ul>
                </div>
                <div class="flex-item flex-item-half flex-item-box">
                    <h3>What's optional?</h3>

                    <ul class="feature-list--pro--optional">
                        <li><span class="dashicons dashicons-yes-alt"></span> <a href="https://www.4property.com/4sites/supernova/" rel="external noopener">Supernova</a>: Native WordPress theme</li>
                        <li><span class="dashicons dashicons-yes-alt"></span> <a href="https://www.4property.com/4sites/4bids/" rel="external noopener">4Bids</a>: Native auction/private treaty platform</li>
                        <li><span class="dashicons dashicons-yes-alt"></span> 4Leads: Native lead generation platform</li>
                        <li><span class="dashicons dashicons-yes-alt"></span> Custom development and support</li>
                        <li><span class="dashicons dashicons-yes-alt"></span> And lots more!</li>
                    </ul>
                </div>
            </div>

            <hr>

            <p>
                <a href="https://www.4property.com/4sites/wp-property-drive/" rel="external noopener" class="button button-primary button-hero">Get WP Property Drive PRO!</a>
                <a href="https://propertywebsite.ie/" rel="external noopener" class="button button-secondary button-hero">WP Property Drive PRO Demo</a>
            </p>
        <?php } ?>
    </div>
	<?php
}
