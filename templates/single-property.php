<?php
get_header();

wp_enqueue_script('foopicker');

while (have_posts()) : the_post();
    $propertyId = (int) get_the_ID();

    wp4pm_update_property_attachments($propertyId); ?>

    <article id="pd-property-<?php echo $propertyId; ?>" <?php post_class('pid-' . $propertyId); ?> data-pid="<?php echo $propertyId; ?>">
        <?php
        $imageArray = get_post_meta($propertyId, 'detail_images_array', true);
        $imageArray = array_map('trim', explode(',', $imageArray));
        $imageArray = array_filter($imageArray);

        if (count($imageArray) > 0) {
            $cinematicImageUri = preg_replace("#&h=.*&#", '&h=1440&', $imageArray[0]);
            $cinematicImageUri = preg_replace("#&w=.*&#", '&w=1920&', $cinematicImageUri);
        } else {
            $cinematicImageUri = get_the_post_thumbnail_url($propertyId);
        }

        $property_details = get_post_meta($propertyId);
        $property_type = get_the_terms($propertyId, 'property_type');
        $propertyStatus = get_post_meta($propertyId, 'property_status', true);

        $propertyArea = get_the_terms($propertyId, 'property_area');
        $propertyArea = ($propertyArea) ? $propertyArea[0]->name : '';

        if ((int) get_option('inactive_not_clickable') === 1 && (sanitize_title($propertyStatus) === 'sold' || sanitize_title($propertyStatus) === 'has-been-let')) {
            echo '<div class="grid-wrap grid-single-property grid-single-property--not-available" itemscope itemtype="http://schema.org/Place">
                <h2>Property not available.</h2>
            </div>';

            continue;
        }

        // Build the Favourite action string
        $favouriteString = '';
        if ((int) get_option('allow_favourites') === 1) {
            $favouriteString = '<span class="pd-box-favourite" data-property-id="' . $propertyId . '" tooltip="Save property" flow="right"></span>';
        }

        // Build the title string
        $propertyTitleString = get_the_title() . $favouriteString;

        // Generate priceValidUntil
        $date = new DateTime($post->post_date);
        $date->modify("+90 day");
        $priceValidUntil = $date->format('Y-m-d');
        ?>
        <div id="single-hero-image" data-src="<?php echo $cinematicImageUri; ?>"></div>

        <script type="application/ld+json">
        {
            "@type": "SingleFamilyResidence",
            "@context": "http://schema.org",
            "address": {
                "@type": "PostalAddress",
                "@context": "http://schema.org",
                "streetAddress": "<?php echo get_the_title(); ?>",
                "addressLocality": "<?php echo $propertyArea; ?>",
                "addressRegion": "<?php echo $propertyArea; ?>"
            },
            "geo": {
                "@type": "GeoCoordinates",
                "@context": "http://schema.org",
                "latitude": <?php echo $property_details['latitude'][0]; ?>,
                "longitude": <?php echo $property_details['longitude'][0]; ?>
            },
            "url": "<?php echo get_permalink(); ?>"
        }
        </script>
        <script type="application/ld+json">
        {
            "@context": "http://schema.org",
            "@type": "Product",
            "name": "<?php echo get_the_title(); ?>",
            "image": "<?php echo $cinematicImageUri; ?>",
            "offers": {
                "@type": "Offer",
                "priceCurrency": "EUR",
                <?php if ((int) $property_details['price'][0] !== '' && (int) $property_details['price'][0] > 0) {
                    echo '"price": "' . $property_details['price'][0] . '",' . "\n";
                } ?>
                "validFrom": "<?php echo get_the_date('Y-m-d'); ?>",
                "priceValidUntil": "<?php echo $priceValidUntil; ?>",
                "availability": "http://schema.org/InStock",
                "url": "<?php echo get_permalink(); ?>",
                "seller": {
                    "@type": "Organization",
                    "name": "<?php $wpseoTitles = get_option('wpseo_titles'); echo $wpseoTitles['company_name']; ?>"
                }
            },
            "description": "<?php echo strip_tags(get_the_excerpt()); ?>"
        }
        </script>

        <div id="single-property-container" data-property-id="<?php echo $propertyId; ?>"></div>

        <div class="grid-wrap grid-single-property" itemscope itemtype="http://schema.org/Place">
            <div class="print-view">
                <p><img src="<?php echo wppd_get_thumbnail_url($propertyId); ?>" alt="">
                <h2 class="single-property-title"><?php echo $propertyTitleString; ?></h2>
            </div>
            <?php
            // Get property template
            $propertyTemplate = (int) get_option('cinematic_overlay');
            if (get_post_meta($propertyId, '_property_template', true) !== '' && (int) get_post_meta($propertyId, '_property_template', true) !== 999) {
                $propertyTemplate = (int) $property_details['_property_template'][0];
                $propertyTemplate = ($propertyTemplate !== 'none') ? (int) $propertyTemplate : (int) get_option('cinematic_overlay');
            }

            $agentEmailAddress = get_option('agency_email');
            if ($property_details['agent_email'][0]) {
                $agentEmailAddress = $property_details['agent_email'][0];
            }

            if ($propertyTemplate === 2) {
                echo '<div class="supernova-fullwidth supernova-property-hero" style="background: url(' . wppd_get_thumbnail_url($propertyId) . ') no-repeat center center; background-size: cover; margin-top: -128px;">
                    <h1 class="single-property-title grid-wrap overlaid">' . $propertyTitleString . '</h1>
                </div>
                <div class="pd-section-breakdown">
                    <ul class="grid-wrap">
                        <li><a href="#"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="arrow-up" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-arrow-up fa-w-14 fa-fw"><path fill="currentColor" d="M34.9 289.5l-22.2-22.2c-9.4-9.4-9.4-24.6 0-33.9L207 39c9.4-9.4 24.6-9.4 33.9 0l194.3 194.3c9.4 9.4 9.4 24.6 0 33.9L413 289.4c-9.5 9.5-25 9.3-34.3-.4L264 168.6V456c0 13.3-10.7 24-24 24h-32c-13.3 0-24-10.7-24-24V168.6L69.2 289.1c-9.3 9.8-24.8 10-34.3.4z" class=""></path></svg> Top</a></li>
                        <li><a href="#property-page-slider"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="images" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg-inline--fa fa-images fa-w-18 fa-fw"><path fill="currentColor" d="M480 416v16c0 26.51-21.49 48-48 48H48c-26.51 0-48-21.49-48-48V176c0-26.51 21.49-48 48-48h16v208c0 44.112 35.888 80 80 80h336zm96-80V80c0-26.51-21.49-48-48-48H144c-26.51 0-48 21.49-48 48v256c0 26.51 21.49 48 48 48h384c26.51 0 48-21.49 48-48zM256 128c0 26.51-21.49 48-48 48s-48-21.49-48-48 21.49-48 48-48 48 21.49 48 48zm-96 144l55.515-55.515c4.686-4.686 12.284-4.686 16.971 0L272 256l135.515-135.515c4.686-4.686 12.284-4.686 16.971 0L512 208v112H160v-48z" class=""></path></svg> Gallery</a></li>
                        <li><a href="#pd-property-page-description"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="file-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" class="svg-inline--fa fa-file-alt fa-w-12 fa-fw"><path fill="currentColor" d="M224 136V0H24C10.7 0 0 10.7 0 24v464c0 13.3 10.7 24 24 24h336c13.3 0 24-10.7 24-24V160H248c-13.2 0-24-10.8-24-24zm64 236c0 6.6-5.4 12-12 12H108c-6.6 0-12-5.4-12-12v-8c0-6.6 5.4-12 12-12h168c6.6 0 12 5.4 12 12v8zm0-64c0 6.6-5.4 12-12 12H108c-6.6 0-12-5.4-12-12v-8c0-6.6 5.4-12 12-12h168c6.6 0 12 5.4 12 12v8zm0-72v8c0 6.6-5.4 12-12 12H108c-6.6 0-12-5.4-12-12v-8c0-6.6 5.4-12 12-12h168c6.6 0 12 5.4 12 12zm96-114.1v6.1H256V0h6.1c6.4 0 12.5 2.5 17 7l97.9 98c4.5 4.5 7 10.6 7 16.9z" class=""></path></svg> Description</a></li>
                        <li><a href="#listing-map"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="map-marker-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" class="svg-inline--fa fa-map-marker-alt fa-w-12 fa-fw"><path fill="currentColor" d="M172.268 501.67C26.97 291.031 0 269.413 0 192 0 85.961 85.961 0 192 0s192 85.961 192 192c0 77.413-26.97 99.031-172.268 309.67-9.535 13.774-29.93 13.773-39.464 0zM192 272c44.183 0 80-35.817 80-80s-35.817-80-80-80-80 35.817-80 80 35.817 80 80 80z" class=""></path></svg> Location</a></li>
                    </ul>
                </div>';
            } else if ($propertyTemplate === 3) {
                /**
                 * Flickity (Sydney Gallery
                 */
                wp4pm_show_flickity_gallery($propertyId);
                echo '<h1 class="single-property-title">' . $propertyTitleString . '</h1>';
            } else if ($propertyTemplate === 4) {
                /**
                 * Flickity (Parsley) Gallery
                 */
                wp4pm_show_flickity_parsley_gallery($propertyId);
                echo '<h1 class="single-property-title">' . $propertyTitleString . '</h1>';
            } else if ($propertyTemplate === 1) {
                echo '<h1 class="single-property-title">' . $propertyTitleString . '</h1>';
            } else {
                echo '<h1 class="single-property-title">' . $propertyTitleString . '</h1>';
            }

            /**
             * Isotope Gallery
             */
            if ((int) get_option('isotope_gallery') === 1) {
                show_isotope_gallery();
            }
            ?>

            <section class="flex-container flex-single-property">
                <div class="flex-element flex-single-property--details">
                    <section class="grid-property-attributes flex-container">
                        <?php
                        // Only show price if property is not sold or sale agreed
                        if (!in_array($propertyStatus, ['Sale Agreed', 'Sold', 'Let', 'Has Been Let'])) { ?>
                            <div class="grid-property-attribute grid-property-attribute-price flex-element">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="euro-sign" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="svg-inline--fa fa-euro-sign fa-w-10 fa-fw"><path fill="currentColor" d="M310.706 413.765c-1.314-6.63-7.835-10.872-14.424-9.369-10.692 2.439-27.422 5.413-45.426 5.413-56.763 0-101.929-34.79-121.461-85.449h113.689a12 12 0 0 0 11.708-9.369l6.373-28.36c1.686-7.502-4.019-14.631-11.708-14.631H115.22c-1.21-14.328-1.414-28.287.137-42.245H261.95a12 12 0 0 0 11.723-9.434l6.512-29.755c1.638-7.484-4.061-14.566-11.723-14.566H130.184c20.633-44.991 62.69-75.03 117.619-75.03 14.486 0 28.564 2.25 37.851 4.145 6.216 1.268 12.347-2.498 14.002-8.623l11.991-44.368c1.822-6.741-2.465-13.616-9.326-14.917C290.217 34.912 270.71 32 249.635 32 152.451 32 74.03 92.252 45.075 176H12c-6.627 0-12 5.373-12 12v29.755c0 6.627 5.373 12 12 12h21.569c-1.009 13.607-1.181 29.287-.181 42.245H12c-6.627 0-12 5.373-12 12v28.36c0 6.627 5.373 12 12 12h30.114C67.139 414.692 145.264 480 249.635 480c26.301 0 48.562-4.544 61.101-7.788 6.167-1.595 10.027-7.708 8.788-13.957l-8.818-44.49z" class=""></path></svg><br><span>Price</span><br><em><?php echo wp4pm_get_property_price($propertyId); ?></em>
                                <?php echo wp4pm_get_property_price_term($propertyId); ?>
                            </div>
                        <?php } ?>

                        <div class="grid-property-attribute grid-property-attribute-name flex-element">
                            <?php if ($property_type[0]->name === 'Parking Space') { ?>
                                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="parking" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-parking fa-w-14 fa-fw"><path fill="currentColor" d="M400 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zM240 320h-48v48c0 8.8-7.2 16-16 16h-32c-8.8 0-16-7.2-16-16V144c0-8.8 7.2-16 16-16h96c52.9 0 96 43.1 96 96s-43.1 96-96 96zm0-128h-48v64h48c17.6 0 32-14.4 32-32s-14.4-32-32-32z" class=""></path></svg>
                            <?php } else { ?>
                                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="home" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg-inline--fa fa-home fa-w-18 fa-fw"><path fill="currentColor" d="M280.37 148.26L96 300.11V464a16 16 0 0 0 16 16l112.06-.29a16 16 0 0 0 15.92-16V368a16 16 0 0 1 16-16h64a16 16 0 0 1 16 16v95.64a16 16 0 0 0 16 16.05L464 480a16 16 0 0 0 16-16V300L295.67 148.26a12.19 12.19 0 0 0-15.3 0zM571.6 251.47L488 182.56V44.05a12 12 0 0 0-12-12h-56a12 12 0 0 0-12 12v72.61L318.47 43a48 48 0 0 0-61 0L4.34 251.47a12 12 0 0 0-1.6 16.9l25.5 31A12 12 0 0 0 45.15 301l235.22-193.74a12.19 12.19 0 0 1 15.3 0L530.9 301a12 12 0 0 0 16.9-1.6l25.5-31a12 12 0 0 0-1.7-16.93z" class=""></path></svg>
                            <?php } ?>
                            <br><span>Type</span>

                            <?php if ($property_type[0]->name === 'Parking Space') { ?>
                                <br><em>Parking Space</em>
                            <?php } else if ((string) $property_details['property_market'][0] === 'New Developments') { ?>
                                <br><em>New Development</em>
                            <?php } else { ?>
                                <br><em><?php if (!empty(wp4pm_get_property_living_type($propertyId))) { echo wp4pm_get_property_living_type($propertyId) . ' '; } ?><?php echo $property_type[0]->name; ?></em>
                            <?php } ?>
                        </div>

                        <div class="grid-property-attribute grid-property-attribute-status grid-property-attribute-status-<?php echo sanitize_title($propertyStatus); ?> flex-element">
                            <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="th" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-th fa-w-16 fa-fw"><path fill="currentColor" d="M149.333 56v80c0 13.255-10.745 24-24 24H24c-13.255 0-24-10.745-24-24V56c0-13.255 10.745-24 24-24h101.333c13.255 0 24 10.745 24 24zm181.334 240v-80c0-13.255-10.745-24-24-24H205.333c-13.255 0-24 10.745-24 24v80c0 13.255 10.745 24 24 24h101.333c13.256 0 24.001-10.745 24.001-24zm32-240v80c0 13.255 10.745 24 24 24H488c13.255 0 24-10.745 24-24V56c0-13.255-10.745-24-24-24H386.667c-13.255 0-24 10.745-24 24zm-32 80V56c0-13.255-10.745-24-24-24H205.333c-13.255 0-24 10.745-24 24v80c0 13.255 10.745 24 24 24h101.333c13.256 0 24.001-10.745 24.001-24zm-205.334 56H24c-13.255 0-24 10.745-24 24v80c0 13.255 10.745 24 24 24h101.333c13.255 0 24-10.745 24-24v-80c0-13.255-10.745-24-24-24zM0 376v80c0 13.255 10.745 24 24 24h101.333c13.255 0 24-10.745 24-24v-80c0-13.255-10.745-24-24-24H24c-13.255 0-24 10.745-24 24zm386.667-56H488c13.255 0 24-10.745 24-24v-80c0-13.255-10.745-24-24-24H386.667c-13.255 0-24 10.745-24 24v80c0 13.255 10.745 24 24 24zm0 160H488c13.255 0 24-10.745 24-24v-80c0-13.255-10.745-24-24-24H386.667c-13.255 0-24 10.745-24 24v80c0 13.255 10.745 24 24 24zM181.333 376v80c0 13.255 10.745 24 24 24h101.333c13.255 0 24-10.745 24-24v-80c0-13.255-10.745-24-24-24H205.333c-13.255 0-24 10.745-24 24z" class=""></path></svg><br><span>Status</span><br><em><?php echo $propertyStatus; ?></em>
                        </div>

                        <?php if ((int) wp4pm_get_property_bedrooms($propertyId) > 0 || ((string) $property_details['property_market'][0] === 'New Developments' && (int) getLinkedPropertiesBedroomRange($property_details['linked_properties'][0]) > 0)) { ?>
                            <div class="grid-property-attribute grid-property-attribute-bedrooms flex-element">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="bed" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" class="svg-inline--fa fa-bed fa-w-20 fa-fw"><path fill="currentColor" d="M176 256c44.11 0 80-35.89 80-80s-35.89-80-80-80-80 35.89-80 80 35.89 80 80 80zm352-128H304c-8.84 0-16 7.16-16 16v144H64V80c0-8.84-7.16-16-16-16H16C7.16 64 0 71.16 0 80v352c0 8.84 7.16 16 16 16h32c8.84 0 16-7.16 16-16v-48h512v48c0 8.84 7.16 16 16 16h32c8.84 0 16-7.16 16-16V240c0-61.86-50.14-112-112-112z" class=""></path></svg><br><span>BEDROOMS</span><br><em><?php echo wp4pm_get_property_bedrooms($propertyId); ?></em>
                            </div>
                        <?php } ?>

                        <?php if ((int) wp4pm_get_property_bathrooms($propertyId) > 0) { ?>
                            <div class="grid-property-attribute grid-property-attribute-bathrooms flex-element">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="bath" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-bath fa-w-16 fa-fw"><path fill="currentColor" d="M488 256H80V112c0-17.645 14.355-32 32-32 11.351 0 21.332 5.945 27.015 14.88-16.492 25.207-14.687 59.576 6.838 83.035-4.176 4.713-4.021 11.916.491 16.428l11.314 11.314c4.686 4.686 12.284 4.686 16.971 0l95.03-95.029c4.686-4.686 4.686-12.284 0-16.971l-11.314-11.314c-4.512-4.512-11.715-4.666-16.428-.491-17.949-16.469-42.294-21.429-64.178-15.365C163.281 45.667 139.212 32 112 32c-44.112 0-80 35.888-80 80v144h-8c-13.255 0-24 10.745-24 24v16c0 13.255 10.745 24 24 24h8v32c0 28.43 12.362 53.969 32 71.547V456c0 13.255 10.745 24 24 24h16c13.255 0 24-10.745 24-24v-8h256v8c0 13.255 10.745 24 24 24h16c13.255 0 24-10.745 24-24v-32.453c19.638-17.578 32-43.117 32-71.547v-32h8c13.255 0 24-10.745 24-24v-16c0-13.255-10.745-24-24-24z" class=""></path></svg><br><span>BATHROOMS</span><br><em><?php echo wp4pm_get_property_bathrooms($propertyId); ?></em>
                            </div>
                        <?php } ?>

                        <?php if ($property_type[0]->name !== 'Parking Space' && (string) $property_details['property_market'][0] !== 'New Developments') { ?>
                            <div class="grid-property-attribute grid-property-attribute-ber flex-element">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="thermometer-empty" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 512" class="svg-inline--fa fa-thermometer-empty fa-w-8 fa-fw"><path fill="currentColor" d="M192 384c0 35.346-28.654 64-64 64s-64-28.654-64-64c0-35.346 28.654-64 64-64s64 28.654 64 64zm32-84.653c19.912 22.563 32 52.194 32 84.653 0 70.696-57.303 128-128 128-.299 0-.609-.001-.909-.003C56.789 511.509-.357 453.636.002 383.333.166 351.135 12.225 321.755 32 299.347V96c0-53.019 42.981-96 96-96s96 42.981 96 96v203.347zM208 384c0-34.339-19.37-52.19-32-66.502V96c0-26.467-21.533-48-48-48S80 69.533 80 96v221.498c-12.732 14.428-31.825 32.1-31.999 66.08-.224 43.876 35.563 80.116 79.423 80.42L128 464c44.112 0 80-35.888 80-80z" class=""></path></svg><br><span>BER</span><br>
                                <?php echo wp4pm_get_property_ber($propertyId); ?>
                            </div>
                        <?php } ?>

                        <?php if (isset($property_details['brochure_1']) && $property_details['brochure_1'][0] != '') { ?>
                            <div class="grid-property-attribute grid-property-attribute-brochure flex-element">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="file-pdf" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" class="svg-inline--fa fa-file-pdf fa-w-12 fa-fw"><path fill="currentColor" d="M181.9 256.1c-5-16-4.9-46.9-2-46.9 8.4 0 7.6 36.9 2 46.9zm-1.7 47.2c-7.7 20.2-17.3 43.3-28.4 62.7 18.3-7 39-17.2 62.9-21.9-12.7-9.6-24.9-23.4-34.5-40.8zM86.1 428.1c0 .8 13.2-5.4 34.9-40.2-6.7 6.3-29.1 24.5-34.9 40.2zM248 160h136v328c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V24C0 10.7 10.7 0 24 0h200v136c0 13.2 10.8 24 24 24zm-8 171.8c-20-12.2-33.3-29-42.7-53.8 4.5-18.5 11.6-46.6 6.2-64.2-4.7-29.4-42.4-26.5-47.8-6.8-5 18.3-.4 44.1 8.1 77-11.6 27.6-28.7 64.6-40.8 85.8-.1 0-.1.1-.2.1-27.1 13.9-73.6 44.5-54.5 68 5.6 6.9 16 10 21.5 10 17.9 0 35.7-18 61.1-61.8 25.8-8.5 54.1-19.1 79-23.2 21.7 11.8 47.1 19.5 64 19.5 29.2 0 31.2-32 19.7-43.4-13.9-13.6-54.3-9.7-73.6-7.2zM377 105L279 7c-4.5-4.5-10.6-7-17-7h-6v128h128v-6.1c0-6.3-2.5-12.4-7-16.9zm-74.1 255.3c4.1-2.7-2.5-11.9-42.8-9 37.1 15.8 42.8 9 42.8 9z" class=""></path></svg>
                                <br><span>Brochure</span>

                                <?php if ($property_details['brochure_1'][0] != '' && $property_details['brochure_2'][0] == '') { ?>
                                    <br><a target="_blank" href="<?php echo $property_details['brochure_1'][0]; ?>">View Now</a>
                                <?php } else { ?>
                                    <br><a target="_blank" href="<?php echo $property_details['brochure_1'][0]; ?>">1</a>
                                    <?php if (isset($property_details['brochure_2'][0]) && $property_details['brochure_2'][0] !== '') { ?>
                                        <a target="_blank" href="<?php echo $property_details['brochure_2'][0]; ?>">2</a>
                                    <?php } ?>
                                    <?php if (isset($property_details['brochure_3'][0]) && $property_details['brochure_3'][0] !== '') { ?>
                                        <a target="_blank" href="<?php echo $property_details['brochure_3'][0]; ?>">3</a>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        <?php } ?>

                        <?php if ($property_details['property_size'][0]) { ?>
                            <div class="grid-property-attribute grid-property-attribute-size flex-element">
                                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="arrows-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-arrows-alt fa-w-16 fa-fw"><path fill="currentColor" d="M352.201 425.775l-79.196 79.196c-9.373 9.373-24.568 9.373-33.941 0l-79.196-79.196c-15.119-15.119-4.411-40.971 16.971-40.97h51.162L228 284H127.196v51.162c0 21.382-25.851 32.09-40.971 16.971L7.029 272.937c-9.373-9.373-9.373-24.569 0-33.941L86.225 159.8c15.119-15.119 40.971-4.411 40.971 16.971V228H228V127.196h-51.23c-21.382 0-32.09-25.851-16.971-40.971l79.196-79.196c9.373-9.373 24.568-9.373 33.941 0l79.196 79.196c15.119 15.119 4.411 40.971-16.971 40.971h-51.162V228h100.804v-51.162c0-21.382 25.851-32.09 40.97-16.971l79.196 79.196c9.373 9.373 9.373 24.569 0 33.941L425.773 352.2c-15.119 15.119-40.971 4.411-40.97-16.971V284H284v100.804h51.23c21.382 0 32.09 25.851 16.971 40.971z" class=""></path></svg><br><span>Area</span><br><em><?php echo strtok($property_details['property_size'][0], 'm') . 'm.'; ?></em>
                            </div>
                        <?php } ?>

                        <?php if (isset($property_details['property_floors'])) {
                            if (sanitize_text_field($property_details['property_floors'][0]) !== '') { ?>
                                <div class="grid-property-attribute grid-property-attribute-floorplan flex-element">
                                    <svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="map" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg-inline--fa fa-map fa-w-18 fa-fw"><path fill="currentColor" d="M560.02 32c-1.96 0-3.98.37-5.96 1.16L384.01 96H384L212 35.28A64.252 64.252 0 0 0 191.76 32c-6.69 0-13.37 1.05-19.81 3.14L20.12 87.95A32.006 32.006 0 0 0 0 117.66v346.32C0 473.17 7.53 480 15.99 480c1.96 0 3.97-.37 5.96-1.16L192 416l172 60.71a63.98 63.98 0 0 0 40.05.15l151.83-52.81A31.996 31.996 0 0 0 576 394.34V48.02c0-9.19-7.53-16.02-15.98-16.02zM224 90.42l128 45.19v285.97l-128-45.19V90.42zM48 418.05V129.07l128-44.53v286.2l-.64.23L48 418.05zm480-35.13l-128 44.53V141.26l.64-.24L528 93.95v288.97z" class=""></path></svg><br><span>Floorplan</span><br><a href="#floorplan">View Now</a>
                                </div>
                            <?php }
                        } ?>

                        <?php
                        // Get YouTube video
                        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', get_the_content(), $match);

                        if (!empty($match[1])) { ?>
                            <div class="grid-property-attribute grid-property-attribute-video flex-element">
                                <svg aria-hidden="true" focusable="false" data-prefix="fab" data-icon="youtube" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg-inline--fa fa-youtube fa-w-18 fa-fw"><path fill="currentColor" d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z" class=""></path></svg><br><span>Video</span><br><a href="#video" class="listing-video">Video</a>
                            </div>
                        <?php } ?>

                        <div class="grid-property-attribute grid-property-attribute-location flex-element">
                            <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="map-marker-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" class="svg-inline--fa fa-map-marker-alt fa-w-12 fa-fw"><path fill="currentColor" d="M172.268 501.67C26.97 291.031 0 269.413 0 192 0 85.961 85.961 0 192 0s192 85.961 192 192c0 77.413-26.97 99.031-172.268 309.67-9.535 13.774-29.93 13.773-39.464 0zM192 272c44.183 0 80-35.817 80-80s-35.817-80-80-80-80 35.817-80 80 35.817 80 80 80z" class=""></path></svg><br><span>Location</span><br><a href="#listing-map"><?php echo $propertyArea; ?></a>
                        </div>
                    </section>

                    <?php
                    /**
                     * Show the carousel for classic, cover or cinematic overlays
                     */
                    if (in_array((int) $propertyTemplate, [0, 1, 2])) {
                        wppd_show_property_images($propertyId);
                    }

                    do_action('after_slider', $propertyId);

                    /**
                     * Show property description
                     */
                    wppd_property_description($propertyId);

                    /**
                     * Show property floorplans
                     */
                    wppd_property_floorplans($propertyId);

                    /**
                     * Show property directions
                     */
                    wppd_property_directions($propertyId);

                    /**
                     * Show property gallery (flexbin)
                     */
                    wppd_property_flexbin($propertyId);



                    if ($property_details['property_market'][0] == 'New Developments') {
                        $linkedProperties = $property_details['linked_properties'][0];

                        if ((string) $linkedProperties !== '') { ?>
                            <section class="grid-property-types-within">
                                <h4 class="listing-section-title">Property types within this development</h4>

                                <?php
                                $linkedPropertiesArray = explode(',', $linkedProperties);
                                $importerIds = [];

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
                                            $importerIds[] = $linkedPropertyObject->post->ID;
                                        }
                                    }
                                }

                                if (count($importerIds) > 0) {
                                    echo do_shortcode('[property-grid columns="3" in="' . implode(',', $importerIds) . '"]');
                                }
                                ?>
                            </section>
                        <?php }
                    } ?>
                </div>

                <div class="flex-element flex-single-property--sidebar">
                    <aside class="<?php echo ((int) get_option('use_single_sidebar') === 1) ? 'classic' : 'modern' ?>">
                        <?php
                        // get reusable gutenberg block:
                        if ((int) get_option('reusable_sidebar_id') > 0) {
                            $reusableSidebarBlock = get_post((int) get_option('reusable_sidebar_id'));

                            echo apply_filters('the_content', $reusableSidebarBlock->post_content);
                        } else if (is_active_sidebar('sidebar-widget')) {
                            dynamic_sidebar('sidebar-widget');
                        } else {
                            echo wp4pm_get_sidebar_classic($propertyId, $property_details);
                        }
                        ?>
                    </aside>
                </div>
            </section>

            <?php if ((int) get_option('use_single_sidebar') === 0) { ?>
                <section id="listing-map">
                    <h4 class="listing-section-title">Location</h4>
                    <?php $location = $property_details['latitude'][0] . ',' . $property_details['longitude'][0]; ?>

                    <?php if ((string) get_option('map_provider') === 'osm') { ?>
                        <div id="osm-map"></div>

                        <script>
                        window.addEventListener('load', function () {
                            var osmMap = L.map('osm-map').setView([<?php echo $property_details['latitude'][0]; ?>, <?php echo $property_details['longitude'][0]; ?>], 16);

                            L.marker([<?php echo $property_details['latitude'][0]; ?>, <?php echo $property_details['longitude'][0]; ?>])
                                .addTo(osmMap)
                                .bindPopup('<?php echo get_the_title(); ?>');
                                //.openPopup();

                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                            }).addTo(osmMap);
                            L.control.scale().addTo(osmMap);

                            // Disable scrollzoom
                            <?php if ((int) get_option('osm_scrollzoom') === 1) { ?>
                                osmMap.scrollWheelZoom.disable();
                            <?php } ?>
                        }, false);
                        </script>
                    <?php } ?>
                </section>
            <?php } ?>

            <?php if ((int) get_option('show_related_properties') === 1) {
                $perPage = (int) get_option('flex_grid_size');

                $propertyCounty = get_the_terms($propertyId, 'property_county');
                $propertyCounty = ($propertyCounty) ? $propertyCounty[0]->slug : '';

                $propertyArea = get_the_terms($propertyId, 'property_area');
                $propertyArea = ($propertyArea) ? $propertyArea[0]->slug : '';

                $propertyPrice = get_post_meta($propertyId, 'price', true);
                $propertyPriceMin = (int) $propertyPrice - 20000;
                $propertyPriceMax = (int) $propertyPrice + 20000;

                $custom_taxterms = wp_get_object_terms($post->ID, 'property_type', ['fields' => 'ids']);
                $args = [
                    'post_type' => 'property',
                    'post_status' => 'publish',
                    'posts_per_page' => $perPage,
                    'orderby' => 'date',
                    'meta_query' => [
                        [
                            'key' => 'property_status',
                            'value' => ['For Sale', 'To Let', 'For Auction'],
                            'compare' => 'IN',
                        ],
                        [
                            'key' => 'price',
                            'value' => [$propertyPriceMin, $propertyPriceMax],
                            'type' => 'numeric',
                            'compare' => 'BETWEEN',
                        ],
                    ],
                    'tax_query' => [
                        'relation' => 'AND',
                        [
                            'taxonomy' => 'property_type',
                            'field' => 'id',
                            'terms' => $custom_taxterms,
                        ],
                        [
                            'taxonomy' => 'property_area',
                            'field' => 'slug',
                            'terms' => [$propertyArea],
                            'operator' => 'IN',
                        ],
                    ],
                    'post__not_in' => [$post->ID],
                ];
                $relatedProperties = new WP_Query($args);

                if ((int) $relatedProperties->found_posts > 0) { ?>
                    <section class="property-grid">
                        <h4 class="listing-section-title">Related Properties</h4>

                        <div class="flex-container">
                            <?php
                            if ($relatedProperties->have_posts()) : while ($relatedProperties->have_posts()) : $relatedProperties->the_post();
                                echo wp4pm_get_property_box($post->ID);
                            endwhile; endif; ?>
                        </div>
                    </section>
                <?php }
            } ?>
        </div>
    </article>

    <?php
endwhile;

wp_reset_query();

get_footer();
