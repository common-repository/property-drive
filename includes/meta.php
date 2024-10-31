<?php
/**
 * Add meta box
 *
 * @param post $post The post object
 */
function wppd_add_meta_boxes($post) {
    add_meta_box('featured_property_meta_box', 'Advanced Property Settings', 'featured_property_build_meta_box', ['property'], 'side', 'low');
}

add_action('add_meta_boxes', 'wppd_add_meta_boxes');



/**
 * Build custom field meta box
 *
 * @param post $post The post object
 */
function featured_property_build_meta_box($post) {
	wp_nonce_field(basename(__FILE__), 'featured_property_meta_box_nonce');

    $currentPropertyTemplate = get_post_meta($post->ID, '_property_template', true);
	?>
    <div class="inside">
		<p>
            <select name="property_template">
                <option value="999" <?php echo ((int) $currentPropertyTemplate === 999) ? 'selected' : ''; ?>>Inherit from global settings</option>
                <option value="0" <?php echo ((int) $currentPropertyTemplate === 0) ? 'selected' : ''; ?>>No overlay (classic)</option>
                <option value="1" <?php echo ((int) $currentPropertyTemplate === 1) ? 'selected' : ''; ?>>Cinematic hero overlay (full screen)</option>
                <option value="2" <?php echo ((int) $currentPropertyTemplate === 2) ? 'selected' : ''; ?>>Cover slide overlay (half screen)</option>
                <option value="3" <?php echo ((int) $currentPropertyTemplate === 3) ? 'selected' : ''; ?>>Flickity (Sydney) carousel (will disable the regular carousel)</option>
            </select>
            <br><small>This option will override the global property brochure template for this property.</small>
		</p>
	</div>
	<?php
}



/**
 * Store custom field meta box data
 *
 * @param int $post_id The post ID.
 */
function featured_property_save_meta_box_data($post_id) {
    if (!isset($_POST['featured_property_meta_box_nonce']) || !wp_verify_nonce($_POST['featured_property_meta_box_nonce'], basename(__FILE__))) {
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

    $propertyTemplate = isset($_POST['property_template']) ? (int) $_POST['property_template'] : '';

    update_post_meta($post_id, '_property_template', $propertyTemplate);
}

add_action('save_post', 'featured_property_save_meta_box_data');
