<?php
function wp4pm_slider_enqueue() {
    wp_enqueue_style('slider', plugins_url('/slider.css', __FILE__), [], '1.0.1');

    wp_enqueue_script('flickity', plugins_url('/flickity.pkgd.min.js', __FILE__), [], '2.2.1', true);

    wp_enqueue_script('slider', plugins_url('/slider.js', __FILE__), ['flickity'], '1.0.1', true);
    wp_localize_script('slider', 'ajaxVar', [
        'ajaxurl' => admin_url('admin-ajax.php')
    ]);
}
add_action('wp_enqueue_scripts', 'wp4pm_slider_enqueue');



function wp4pm_show_slider($atts) {
    $attributes = shortcode_atts([
        'type' => 'slide',
        'mobile' => 0,
        'controls' => 'yes',
        'fullheight' => 'no',
        'fullwidth' => 'yes',
        'zoom' => 'no',
        'ids' => '',
        'interval' => 5000
    ], $atts);

    $out = '<div class="slider-wrap" data-controls="' . $attributes['controls'] . '" data-type="' . $attributes['type'] . '" data-mobile="' . $attributes['mobile'] . '" data-fullheight="' . $attributes['fullheight'] . '" data-fullwidth="' . $attributes['fullwidth'] . '" data-zoom="' . $attributes['zoom'] . '" data-ids="' . $attributes['ids'] . '" data-interval="' . $attributes['interval'] . '"><div class="slider-wrap-spinner"></div></div>';

    return $out;
}

add_shortcode('slider', 'wp4pm_show_slider');



function wp4pm_simple_slider_helper() {
    $out = '';

    $ids = sanitize_text_field($_POST['ids']);

    if ((string) $ids !== '') {
        $idArray = array_map('trim', explode(',', $ids));
        $idArray = array_filter($idArray);
    }

    $type = sanitize_title($_POST['type']);
    $zoom = sanitize_title($_POST['zoom']);

    $fullheight = sanitize_title($_POST['fullheight']);
    $fullwidth = sanitize_title($_POST['fullwidth']);

    $homepageHeroHeight = ((string) $fullheight === 'yes') ? 'fullvh' : '';
    $homepageHeroWidth = ((string) $fullwidth === 'yes') ? 'supernova-fullwidth' : '';

    $out .= '<div class="slider ' . $homepageHeroWidth . ' homepage-hero ' . $homepageHeroHeight . '">
        <div class="homepage-hero-slider">';
            $args = [
                'post_type' => $type,
                'posts_per_page' => -1,
                'order' => 'ASC',
                'orderby' => 'menu_order'
            ];
            if ((string) $ids !== '') {
                $idsArray = array_map('trim', explode(',', $ids));
                $args['post__in'] = $idsArray;
            }

            $the_query = new WP_Query($args);

            if ($the_query->have_posts()) {
                while ($the_query->have_posts()) {
                    $the_query->the_post();

                    $hero = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'homepage_hero');
                    $hero = $hero[0];
                    if ((string) get_post_meta(get_the_ID(), '_hero_property_image', true) !== '') {
                        $hero = get_post_meta(get_the_ID(), '_hero_property_image', true);
                    }

                    $dataVideo = '';
                    $slideContent = do_shortcode(get_the_content(get_the_ID()));
                    if (!empty(get_post_meta(get_the_ID(), 'slide-video', true))) {
                        $dataVideo = '<video playsinline autoplay muted loop src="' . trim(get_post_meta(get_the_ID(), 'slide-video', true)) . '" type="video/mp4" width="100%" height="100%"></video>';
                    }
                    $zoomContent = ((string) $zoom === 'yes') ? '<div class="slide-inner-zoom" style="background: url(' . $hero . ') no-repeat center center; background-size: cover;"></div>' : '';

                    $out .= '<div class="slide slide-' . get_the_ID() . ' block" style="background: url(' . $hero . ') no-repeat center center; background-size: cover;">' .
                        $zoomContent .
                        $dataVideo .

                        '<div class="wrap">' .
                            $slideContent .
                        '</div>
                    </div>';
                }
            }

        $out .= '</div>
    </div>';

    echo $out;

    wp_die();
}

add_action('wp_ajax_simple_slider_helper', 'wp4pm_simple_slider_helper');
add_action('wp_ajax_nopriv_simple_slider_helper', 'wp4pm_simple_slider_helper');



function wp4pm_show_flickity_gallery($propertyId) {
    $imageArray = get_post_meta($propertyId, 'detail_images_array', true);
    $imageArray = array_map('trim', explode(',', $imageArray));
    $imageArray = array_filter($imageArray);

    $wrapAround = ((int) get_option('flickity_wrapAround') === 1) ? 'true' : 'false';
    $groupCells = ((int) get_option('flickity_groupCells') === 1) ? 'true' : 'false';
    $groupCellsValue = ((int) get_option('flickity_groupCellsValue') > 0) ? (int) get_option('flickity_groupCellsValue') : 'false';
    $autoPlay = ((int) get_option('flickity_autoPlay') > 0) ? (int) get_option('flickity_autoPlay') : 'false';

    $out = '<div class="flickity-carousel supernova-fullwidth" data-flickity=\'{ "contain": true, "imagesLoaded": true, "adaptiveHeight": false, "lazyLoad": true, "pageDots": false, "wrapAround": ' . $wrapAround . ', "groupCells": ' . $groupCells . ', "groupCells": ' . $groupCellsValue . ', "fullscreen": true, "autoplay": ' . $autoPlay . ' }\'>';
        foreach ($imageArray as $imageUri) {
            $out .= '<div class="carousel-cell">
                <img loading="eager" src="' . $imageUri . '" height="480" alt="">
            </div>';
        }
    $out .= '</div>';

    echo $out;
}

function wp4pm_show_flickity_parsley_gallery($propertyId) {
    $imageArray = get_post_meta($propertyId, 'detail_images_array', true);
    $imageArray = array_map('trim', explode(',', $imageArray));
    $imageArray = array_filter($imageArray);

    $autoPlay = ((int) get_option('flickity_autoPlay') > 0) ? (int) get_option('flickity_autoPlay') : 'false';

    $out = '<div class="flickity-carousel-parsley--wrap">';

    $out .= '<div class="flickity-carousel flickity-carousel-parsley supernova-fullwidth" data-flickity=\'{ "contain": true, "imagesLoaded": true, "adaptiveHeight": false, "lazyLoad": true, "pageDots": false, "wrapAround": true, "groupCells": true, "groupCells": 1, "fullscreen": true, "autoplay": ' . $autoPlay . ' }\'>';
        foreach ($imageArray as $imageUri) {
            $imageUri = wppd_remove_var($imageUri, 'w');
            $imageUri = wppd_remove_var($imageUri, 'h');
            $imageUri = wppd_add_var($imageUri, 'w', '1920');
            $imageUri = wppd_add_var($imageUri, 'h', '1440');

            $out .= '<div class="carousel-cell">
                <img loading="eager" src="' . $imageUri . '" height="720" alt="">
            </div>';
        }
    $out .= '</div>';
    $out .= '<div class="flickity-carousel-parsley--elements">
        <ul>
            <li><a href="#" id="flickity-view-fullscreen">Gallery</a></li>
        </ul>
    </div>';
    $out .= '</div>';

    echo $out;
}
