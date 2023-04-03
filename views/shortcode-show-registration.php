<?php
if (!is_user_logged_in()) {
    echo '<p>' . __('You must be <a href="/my-account">logged in</a> and an active member to register for a show. Not an OPA member? <a href="/membership">Click here to learn more</a>', OPA_DOMAIN) . '</p>';
} else {
    $user = wp_get_current_user();
    $user_id = $user->ID;

    $user_region = get_user_meta($user_id, 'opa_region', true);
    if (!$user_region) $user_region = get_opa_region(get_user_meta($user_id, 'billing_state', true));

    global $show_id;
    $show_id = intval($atts['show']);

    $is_show_national = false;
    foreach (get_the_terms($show_id, "shows") as $term) {
        if ($term->slug == "national") {
            $is_show_national = true;
            break;
        }
    }
    $all_memberships = wc_memberships_get_user_memberships($user_id); // Here
    $membershipsStatus = array();

    // Checks for student membership experation
    if (is_student_art_competition() == true) {
        // Get memberships for the current user.
        $memberships = wc_memberships_get_user_memberships();

        // Verify that they have some memberships.
        if ( $memberships ) {
            foreach( $memberships as $membership ) {
                // Print the expiration date in mysql format.
                //echo $membership->get_end_date();
                if($membership->get_end_date()<"2023-01-01 06:00:00"){
                    echo "You are not eligible for this show because you need a 2023 membership. <a href='https://www.oilpaintersofamerica.com/membership/'>View Memberships.</a>";

                    // Stop Registration
                    return;
                }
            }
        }
    }
    //Checks for Membership experation
    if ($is_show_national){
        // Get memberships for the current user.
        $memberships = wc_memberships_get_user_memberships();

        // Verify that they have some memberships.
        if ( $memberships ) {
            foreach( $memberships as $membership ) {
                // Print the expiration date in mysql format.
                //echo $membership->get_end_date();
                if($membership->get_end_date()<"2023-01-01 06:00:00"){
                    echo "You are not eligible for this show because you need a 2023 membership. <a href='https://www.oilpaintersofamerica.com/membership/'>View Memberships.</a>";

                    // Stop Registration
                    return;
                }
            }
        }
    }

    if (is_student_art_competition() == true){
        // Get memberships for the current user.
        $memberships = wc_memberships_get_user_memberships();

        // Verify that they have some memberships.
        if ( $memberships ) {
            foreach( $memberships as $membership ) {
                // Print the expiration date in mysql format.
                //echo $membership->get_end_date();
                if($membership->get_end_date()<"2023-01-01 06:00:00"){
                    echo "You are not eligible for this show because you need a 2023 membership. <a href='".site_url()."/membership/'>View Memberships.</a>";

                    // Stop Registration
                    return;
                }
            }
        }
    }


    if ($is_show_national){
        // Get memberships for the current user.
        $memberships = wc_memberships_get_user_memberships();

        // Verify that they have some memberships.
        if ( $memberships ) {
            foreach( $memberships as $membership ) {
                // Print the expiration date in mysql format.
                //echo $membership->get_end_date();
                if($membership->get_end_date()<"2023-01-01 06:00:00"){
                    echo "You are not eligible for this show because you need a 2023 membership. <a href='https://www.oilpaintersofamerica.com/membership/'>View Memberships.</a>";

                    // Stop Registration
                    return;
                }
            }
        }
    }

    $secondary_membership_data = get_secondary_membership_data($user_id);

    $secondary_membership_grants_free_entry = get_secondary_membership_free_entry_into_show($secondary_membership_data, $show_id);

    if (count($all_memberships) <= 0 && !$secondary_membership_data["can_enter_shows_with_any_membership"]) {
        echo get_show_error_html('You are not eligible for this show because you need a membership. <a href="https://www.oilpaintersofamerica.com/membership/">View Memberships.</a>');
        return;
    }
    $current_membership_name = $all_memberships[0]->get_plan()->name;

    $show_membership = get_field('opa_show_membership', $show_id);
    $current_membership_slug = '';
    $current_membership_status = '';
    $current_membership_end_date_year = 0;
    $next_year = intval(date('Y')) + 1;
    foreach ($all_memberships as $plan) {
        $current_membership_slug = $plan->get_plan()->slug;
        $current_membership_status = $plan->get_status();
        if (in_array($current_membership_slug, $show_membership) && $current_membership_status != 'active') {

            if ($current_membership_status == 'expired') {
                $current_membership_status = '<a href="' . site_url() . '/my-account/members-area/">' . $current_membership_status . '</a>';
            }
            echo get_show_error_html('You are not eligible for this show because Your membership status is ' . $current_membership_status . '</a>');
            return;
        }
        $current_membership_end_date_year = intval(explode('-', $plan->get_end_date())[0]);
    }

    $show_region = get_field('opa_show_region', $show_id);
    $show_membership = get_field('opa_show_membership', $show_id);

    $memberships = wc_memberships_get_user_memberships($user_id); // Here
    $allPlans = array();
    foreach ($memberships as $plan) {
        $allPlans[] = $plan->plan->slug;
    }

    $num_of_registrations = OPA_Model_Art::get_user_registrations($user, $show_id);

    if (is_online_showcase()) {
        $max_registrations = 1000;
    } else {
        $max_registrations = count(OPA_SHOW_REGISTRATION_FEE);
    }

    // Check if user has an active member type (reference: wp-content/plugins/opa/opa.php)
    if (!is_active_opa_member()) {
        // Let the function display the appropriate message
    } else if (($show_region == 'eastern' || $show_region == 'western') && $show_region != $user_region && !in_array('master-signature-membership', $allPlans) && !$secondary_membership_data["can_enter_shows_with_any_region"]) {
        echo get_show_error_html('You are not eligible for this show because of its region.');
    } else {

        $registration_price = OPA_Model_Art::get_user_price_for_registration($user, intval($atts['show']));
        $registrations_remaining = OPA_Model_Art::get_user_registrations_remaining($user, intval($atts['show']));

        ?>
        <div class="opa-show-registration">

        <?php
        if ($registrations_remaining === 0 && !is_online_showcase()) {
            printf(
                '<p>' . __('Max registrations reached (%s). You can edit your submissions in your', OPA_DOMAIN) . ' <a href="' . /* get_permalink(get_option('opa_profile_page_id')) */ '/my-account/entries/' . '">' . __('profile', OPA_DOMAIN) . '</a>.</p>',
                count(OPA_SHOW_REGISTRATION_FEE)
            );
            require_once(OPA_PATH . 'views/user-paintings-preview.php');
        } else {

            $opa_start_registration_date = parse_acf_date(get_field('opa_start_registration_date', get_the_ID()));
            $opa_end_registration_date = parse_acf_date(get_field('opa_end_registration_date', get_the_ID()));
            $free_entry = get_field('free_entry_for', get_the_ID());

            $reg_start_date = date("Y-m-d H:i:s", strtotime($opa_start_registration_date));
            $reg_end_date = date("Y-m-d H:i:s", strtotime($opa_end_registration_date));

            $date_now = convert_date_to_chicago_time("now");

            if ($reg_end_date > $date_now || $secondary_membership_data["can_enter_shows_anytime"]) {
                ?>
                <style>
                    .main-painting-sec {
                        border: 1px solid #eee;
                        padding: 20px;
                        border-radius: 11px;
                        margin-top: 10px;
                        position: relative;
                    }

                    .addMoreBtn, .continueBtn {
                        float: right;
                        margin: 15px;
                        padding: 7px;

                    }

                    .crossBtn {
                        right: 10px;
                        position: absolute;
                        top: 10px;
                        font-size: 31px;
                    }


                    .opa_show_registration_price {
                        position: fixed;
                        right: 152px;
                        top: 232px;
                        z-index: 1;
                        border: 1px solid #eee;
                        padding: 20px;
                        display: none;
                        border-radius: 17px;
                    }

                    @media screen and (max-width: 720px) {
                        .opa_show_registration_price {
                            position: inherit;
                            z-index: 1;
                            border: 1px solid #eee;
                            padding: 20px;
                            border-radius: 17px;
                            display: block;
                        }
                    }
                </style>

                <div class="opa-show-address-confirmation">
                    <p>Please make sure your address is correct.</p>

                    <p class="form-row form-row-wide address-field">
                        <label for="billing_country" class="">Country / Region</label>
                        <span class="woocommerce-input-wrapper">
                                <input disabled name="billing_country" id="billing_country" class="input-text "
                                       value="<?php echo get_user_meta($user->ID, "billing_country", true) ?>">
                            </span>
                    </p>
                    <p class="form-row form-row-wide address-field" id="billing_address_1_field">
                        <label for="billing_address_1" class="">Street address</label>
                        <span class="woocommerce-input-wrapper">
                                <input disabled type="text" class="input-text " name="billing_address_1"
                                       id="billing_address_1"
                                       value="<?php echo get_user_meta($user->ID, "billing_address_1", true) ?>">
                            </span>
                    </p>
                    <p class="form-row form-row-wide address-field" id="billing_address_2_field">
                            <span class="woocommerce-input-wrapper">
                                <input disabled type="text" class="input-text " name="billing_address_2"
                                       id="billing_address_2"
                                       value="<?php echo get_user_meta($user->ID, "billing_address_2", true) ?>">
                            </span>
                    </p>
                    <p class="form-row form-row-wide address-field" id="billing_city_field">
                        <label for="billing_city" class="">Town / City</label>
                        <span class="woocommerce-input-wrapper">
                                <input disabled type="text" class="input-text " name="billing_city" id="billing_city"
                                       placeholder=""
                                       value="<?php echo get_user_meta($user->ID, "billing_city", true) ?>">
                            </span>
                    </p>
                    <p class="form-row form-row-wide address-field" id="billing_state_field">
                        <label for="billing_state" class="">State / County</label>
                        <span class="woocommerce-input-wrapper">
                                <input disabled name="billing_state" id="billing_state"
                                       value="<?php echo get_user_meta($user->ID, "billing_state", true) ?>">
                            </span>
                    </p>
                    <p class="form-row form-row-wide address-field validate-required" id="billing_postcode_field">
                        <label for="billing_postcode" class="">Postcode / ZIP</label>
                        <span class="woocommerce-input-wrapper">
                                <input disabled type="text" class="input-text " name="billing_postcode"
                                       id="billing_postcode" placeholder=""
                                       value="<?php echo get_user_meta($user->ID, "billing_postcode", true) ?>">
                            </span>
                    </p>

                    <p>If this information is not correct please update it in your <a
                                href="/my-account/edit-address/billing/">Membership Information Settings</a></p>
                </div>

                <div id="js-payment-form-widget"
                     data-stripe-publishable-key="<?php echo OPA_STRIPE_PUBLISHABLE_KEY; ?>">
                    <form id="payment-form" class="payment-form 1" method="POST">
                        <div class="opa-show-registration__art">

                            <div class="opa-show-registration__product">
                                <label><?php _e('Art Submission', OPA_DOMAIN) ?></label>
                                <br>
                                <label><?php
                                    if (is_online_showcase()) {
                                        echo "( " . $num_of_registrations . " out of unlimited entries )";
                                    } else {
                                        echo "( " . $num_of_registrations . " out of " . $max_registrations . " )";
                                    } ?></label>


                                <?php


                                function calculate_signature($string, $private_key)
                                {
                                    $hash = hash_hmac("sha1", $string, $private_key, true);
                                    $sig = rawurlencode(base64_encode($hash));
                                    return $sig;
                                }


                                $api_key = "700d06cc-b6b2-43d7-bb15-e46a7ff95ac3";
                                $private_key = "ck_bef5199665cf3301cc52ebc84350c4783e9f439a";
                                $method = "GET";
                                $route = "forms/1/entries";
                                $expires = strtotime("+60 mins");
                                $string_to_sign = sprintf("%s:%s:%s:%s", $api_key, $method, $route, $expires);
                                $sig = calculate_signature($string_to_sign, $private_key);


                                require_once(OPA_PATH . 'views/user-paintings-preview.php');

                                ?>
                            </div>
                            <div id="PaintingData"></div>
                            <?php
                            if (!in_array($current_membership_name, $free_entry) && !$secondary_membership_grants_free_entry) {
                                ?>
                                <button class="continueBtn" type="button">Check Out</button>
                                <?php
                            }
                            ?>
                            <button class="addMoreBtn" type="button">Add More</button>


                            <?php if ($requirement_notes = get_field('opa_show_painting_requirement_notes', $show_id)) {
                                printf(
                                    '<div class="opa-show-registration__note">
                                                    %s: %s
                                                </div>',
                                    __('Note', OPA_DOMAIN),
                                    $requirement_notes
                                );
                            } ?>

                            <div class="opa-show-registration__terms">
                                <input type="checkbox" name="show_terms" id="show_terms" required/>
                                <label for="show_terms"><?php _e('Agree', OPA_DOMAIN) ?></label> <a
                                        class="opa-toggle-terms"
                                        href="javascript:void(0)"><?php _e('to terms', OPA_DOMAIN) ?></a>
                                <div class="opa-show-registration__terms-src">
                                    <?php
                                    $terms = get_field('opa_show_terms', $show_id) ?: get_option('opa_show_terms');
                                    echo wp_kses_post($terms);
                                    ?>
                                </div>
                            </div>

                        </div>
                        <div class="opa_show_registration_price">
                            <table cellspacing="0" class="shop_table shop_table_responsive">

                                <tbody>
                                <tr class="cart-subtotal">
                                    <th>Total Paintings</th>
                                    <td data-title="Subtotal"><span class="woocommerce-Price-amount amount"><bdi><span
                                                        id="TotalPainting"></span></bdi></span></td>
                                </tr>


                                <tr class="tax-total">
                                    <th>Tax</th>
                                    <td data-title="Tax"><span class="woocommerce-Price-amount amount"><bdi><span
                                                        class="woocommerce-Price-currencySymbol">$</span>0.00</bdi></span>
                                    </td>
                                </tr>


                                <tr class="order-total">
                                    <th>Total</th>
                                    <td data-title="Total"><strong><span class="woocommerce-Price-amount amount"><bdi><span
                                                            class="woocommerce-Price-currencySymbol"
                                                            id="totalAmount"></span></bdi></span></strong></td>
                                </tr>


                                </tbody>
                            </table>

                        </div>
                        <?php

                        if (!in_array($current_membership_name, $free_entry) && !$secondary_membership_grants_free_entry) {
                            ?>
                            <div class="opa-show-registration__payment">
                                <label class="opa-show-registration__payment-title"
                                       for="card"><?php _e('Payment Information', OPA_DOMAIN) ?></label>
                                <div id="card"></div>
                                <?php
                                $show_payment_address = false;

                                if ($show_payment_address) { ?>
                                    <div class="opa-show-registration__address">
                                        <label><?php _e('Address', OPA_DOMAIN) ?></label>
                                        <input type="text" name="address_line_1" id="address_line_1"/>
                                    </div>
                                    <div class="opa-show-registration__group">
                                        <div class="opa-show-registration__city">
                                            <label><?php _e('City', OPA_DOMAIN) ?></label>
                                            <input type="text" name="address_city" id="address_city"/>
                                        </div>
                                        <div class="opa-show-registration__state">
                                            <label><?php _e('State', OPA_DOMAIN) ?></label>
                                            <input type="text" name="address_state" id="address_state"/>
                                        </div>
                                        <div class="opa-show-registration__zip">
                                            <label><?php _e('Zip', OPA_DOMAIN) ?></label>
                                            <input type="text" name="address_zip" id="address_zip"/>
                                        </div>
                                    </div>
                                <?php } ?>
                                <!-- Used to display Element errors. -->
                                <div id="card-errors" role="alert"></div>
                            </div>
                        <?php } ?>
                        <input type="hidden" id="site_url" value="<?php echo site_url(); ?>">
                        <input type="hidden" name="current_membership" id="current_membership"
                               value="<?php echo $current_membership_name ?>">
                        <input type="hidden" name="free_entry" id="free_entry"
                               value='<?php echo json_encode($free_entry) ?>'>
                        <input type="hidden" name="secondary_membership_grants_free_entry"
                               id="secondary_membership_grants_free_entry"
                               value='<?php echo $secondary_membership_grants_free_entry ?>'>
                        <input type="hidden" name="stripeToken" id="stripeToken"/>
                        <input type="hidden" name="action" value="opa_show_registration"/>
                        <input type="hidden" name="show_id" value="<?php echo $show_id ?>"/>
                        <?php echo wp_nonce_field('opa_show_registration', 'opa_show_registration_nonce', true, false); ?>
                        <?php if (!in_array($current_membership_name, $free_entry) && !$secondary_membership_grants_free_entry) { ?>
                            <button class="opa-show-registration__submit"><?php _e('Submit', OPA_DOMAIN) ?></button>
                            <span id="countDown"></span>
                            <?php
                        } else { ?>


                            <span class="opa_show_registration_free_entry"><?php _e('Submit', OPA_DOMAIN) ?></span><span
                                    id="countDown"></span>
                        <?php } ?>
                    </form>
                </div>
                <?php
            }
        }
        ?>

        </div><?php
    }


    function opa_show_registration_scripts()
    {
        echo get_file_type_validation_script();
        ?>

        <script type="text/javascript">

            <?php
            $user = wp_get_current_user();
            $user_id = $user->ID;
            $show_id = get_the_ID();
            $num_of_registrations = OPA_Model_Art::get_user_registrations($user, $show_id);
            if (is_online_showcase()) {
                $max_registrations = 1000;
            } else {
                $max_registrations = count(OPA_SHOW_REGISTRATION_FEE);
            }
            ?>
            var max_registrations = <?php echo(empty($max_registrations) ? -1 : $max_registrations); ?>;
            var num_of_registrations = <?php echo(empty($num_of_registrations) ? 0 : $num_of_registrations); ?>;

            jQuery(document).ready(function ($) {

                $(".opa-show-registration__submit").click(function (e) {
                    jQuery(".validation-error-message").remove();

                    const form = jQuery("#payment-form")[0];

                    var paymentFormValid = true;

                    if (form.checkValidity() === true && paymentFormValid) {
                        jQuery("#card-errors").html("");

                        $(this).hide();
                        $(this).after('<div class="loading-message">Please wait while your files are being uploaded...</div>');

                        setTimeout(() => {

                            $(".loading-message").remove();
                            $(this).show();
                            if (jQuery("#card-errors").html()) {
                                jQuery('html, body').animate({scrollTop: (jQuery("#card").offset().top - 275)}, 500);
                                $(this).prop('disabled', false);
                            }
                        }, 500);
                    } else {
                        e.preventDefault();
                        e.stopPropagation();
                        var $requiredFields = jQuery('#payment-form input,textarea,select').filter('[required]:visible');
                        $requiredFields = $requiredFields.toArray();

                        $requiredFields.sort(function (a, b) {
                            return jQuery(a).offset().top > jQuery(b).offset().top ? 1 : -1;
                        });
                        var hasScrolled = false;
                        for (var i = 0; i < $requiredFields.length; i++) {
                            if (!$requiredFields[i].checkValidity() || !$($requiredFields[i]).val()) {
                                if (!hasScrolled) jQuery('html, body').animate({scrollTop: (jQuery($requiredFields[i]).offset().top - 275)}, 500);

                                jQuery($requiredFields[i]).parent().append('<div class="validation-error-message opa-show-registration__error">Required</div>');
                                hasScrolled = true;
                            }
                        }


                    }
                });

                $(".continueBtn").click(function () {
                    $('html, body').animate({scrollTop: ($('.opa-show-registration__payment').offset().top - 200)}, 500);
                })
                var i = 1;

                if (num_of_registrations < max_registrations) {

                    $(".addMoreBtn").click(function () {
                        if (i == 30) return;

                        $("#PaintingData").append(incrementPainting(i));
                        $(".main-painting-sec_" + i).find("#painting_file").change(function () {
                            validateFileType(jQuery(this));
                        });
                        getPriceVal()
                        $(".crossBtn").click(function () {

                            var rel = $(this).attr("rel");
                            if (rel == 1) return;
                            $(".main-painting-sec_" + rel).fadeOut(1000);
                            setTimeout(function () {
                                $(".main-painting-sec_" + rel).remove();
                                getPriceVal()
                                setPaintingTitle();

                                if ($(".main-painting-sec").length >= max_registrations - num_of_registrations) {
                                    $(".addMoreBtn").hide();
                                } else {
                                    $(".addMoreBtn").show();
                                }
                            }, 1000);


                        })
                        if (i != 1) {
                            $('html, body').animate({scrollTop: ($('.main-painting-sec_' + i).offset().top - 200)}, 500);
                        }
                        i++;
                        setPaintingTitle();
                        onScrollPriceShow();
                        registerEvents();

                        if ($(".main-painting-sec").length >= max_registrations - num_of_registrations) {
                            $(".addMoreBtn").hide();
                        } else {
                            $(".addMoreBtn").show();
                        }
                    })
                    $(".addMoreBtn").trigger("click");
                }

                function setPaintingTitle() {
                    $(".paintingtitle").each(function (index) {
                        $(this).text('Painting ' + (index + 1));
                    })
                }

                function getPriceVal() {
                    var current_membership = $("#current_membership").val();
                    var free_entry = $("#free_entry").val();
                    var secondary_membership_grants_free_entry = $("#secondary_membership_grants_free_entry").val();

                    free_entry = JSON.parse(free_entry)


                    var multiple = $(".main-painting-sec").length;
                    $("#TotalPainting").text(multiple);
                    var prices = [ <?php echo '35, 15, 10'; ?> ];
                    var total = 0;


                    if ($.inArray(current_membership, free_entry) == -1 && !secondary_membership_grants_free_entry) {
                        <?php if (is_online_showcase()) {
                        echo 'for (var i = num_of_registrations; i < num_of_registrations + multiple; i++) total += 15;';
                    } else {
                        echo 'for (var i = num_of_registrations; i < num_of_registrations + multiple; i++) total += prices[i];';
                    } ?>



                    } else {
                        total = 00;
                        // $(".opa-show-registration__payment").remove();
                    }

                    $("#totalAmount").text('$' + total + '.00');
                }

                function incrementPainting(rel) {
                    var site_url = $("#site_url").val();
                    var priceInput = '<div class="opa-show-registration__painting-price submission_painting_price"> <label for="painting_price_display"><?php _e('Painting Price', OPA_DOMAIN) ?></label> <input type="text" name="" id="painting_price_display" class="painting_price_display_class"/> <input type="text" name="painting_price[]" id="painting_price" style="display:none;"/> <div class="opa-show-registration__note">This includes both the painting and frame price in US Dollars.</div> </div>';
                    var priceInputRequired = '<div class="opa-show-registration__painting-price submission_painting_price"> <label for="painting_price_display"><?php _e('Painting Price', OPA_DOMAIN) ?></label> <input type="text" name="" id="painting_price_display" class="painting_price_display_class"/> <input type="text" name="painting_price[]" id="painting_price" style="display:none;" required/> <div class="opa-show-registration__note">This includes both the painting and frame price in US Dollars.</div> </div>';
                    if (<?php echo(is_online_showcase() ? 'true' : 'false') ?>) priceInput = '';
                    var notForSaleInput = '<div class="opa-show-registration__not_for_sale submission_not_for_sale"> <label for="not_for_sale_display"><?php _e('Not For Sale', OPA_DOMAIN) ?></label> <input type="checkbox" name="" id="not_for_sale_display" class="not_for_sale_display_class"/> <input type="text" name="not_for_sale[]" id="not_for_sale" style="display:none;"/> </div>';
                    if (<?php echo(is_student_art_competition() ? 'false' : 'true') ?>) notForSaleInput = '';
                    var html = '<div class="main-painting-sec main-painting-sec_' + rel + '"><h2 class="paintingtitle">Painting</h2><i class="fa fa-times crossBtn" rel="' + rel + '"></i> <div class="opa-show-registration__painting-name"> <label for="painting_name"><?php _e('Painting Title', OPA_DOMAIN) ?></label> <input type="text" name="painting_name[]" id="painting_name"/> </div><div class="opa-show-registration__painting-description"> <label for="painting_description"><?php _e('Painting Medium and Substrate', OPA_DOMAIN) ?></label> <input type="text" name="painting_description[]" id="painting_description" required></input> </div><div class="opa-show-registration__painting-file"> <label for="painting_file"><?php _e('Painting', OPA_DOMAIN) ?></label> <input type="file" id="painting_file" class="painting_file_class validate-image-file" required name="painting_file[]" accept="<?php echo get_supported_image_formats() ?>"/><img style="float:right;display:none;width: 2em;position: relative; bottom: 3em; right: 1em;" class="loading_img" src="' + site_url + '/wp-content/uploads/2022/02/kOnzy.gif"><img style="float:right;display:none;width: 2em;position: relative; bottom: 3em; right: 1em;" class="check_img" id="check_img" src="' + site_url + '/wp-content/uploads/2022/02/tutorial-preview-large.png"><div class="opa-show-registration__note">Maximum file size is 5mb.</div><div class="opa-show-registration__note">Filenames must only contain letters, numbers, and underscores.</div> <div class="opa-show-registration__note"><?php _e('Photo must be high resolution. Please upload a JPG, JPEG, PNG or HEIC with a minimum of 1400px longest dimension and a maximum of 4000px longest dimension.', OPA_DOMAIN) ?></div><div class="opa-show-registration__note">Suggested DPI 300.</div></div>' + notForSaleInput + '<div class="lower-submission-fields"> ' +
                        priceInput + ' <div class="opa-show-registration__painting-height"> <label for="painting_height"><?php _e('Unframed Painting Height', OPA_DOMAIN) ?></label> <input type="text" name="painting_height[]" id="painting_height"  class="submission_painting_height" required/> <div class="opa-show-registration__note">Please round up to the nearest whole number.</div> </div> <div class="opa-show-registration__painting-width"> <label for="painting_width"><?php _e('Unframed Painting Width', OPA_DOMAIN) ?></label> <input type="text" name="painting_width[]" id="painting_width" class="submission_painting_width" required/> <div class="opa-show-registration__note">Please round up to the nearest whole number.</div> </div> </div></div>';


                    return html;
                }


                onScrollPriceShow();

                function onScrollPriceShow() {
                    if (!jQuery("#payment-form")[0]) return;
                    var offsetForm = jQuery("#payment-form").offset().top;
                    var offsetFooter = jQuery(".flexible-widgets").offset().top;
                    jQuery(document).on('scroll', function () {
                        if (jQuery(window).scrollTop() >= offsetForm && jQuery(window).scrollTop() <= offsetFooter - 400) {
                            jQuery(".opa_show_registration_price").show();

                        } else {
                            jQuery(".opa_show_registration_price").hide();

                        }
                    });
                }

                function registerEvents() {
                    <?php
                    $minimum_sq_inches = intval(get_field('opa_show_painting_min_sq_inches', $show_id));
                    $maximum_sq_inches = intval(get_field('opa_show_painting_max_sq_inches', $show_id));
                    ?>

                    var minimum_painting_sq_inches = <?php echo $minimum_sq_inches ?>;
                    var maximum_painting_sq_inches = <?php echo $maximum_sq_inches ?>;

                    var $show_registration = $('.opa-show-registration');

                    // Painting size enforcement
                    $show_registration.on('change', '.submission_painting_width, .submission_painting_height', function (e) {
                        var $widthHeightContainer = jQuery(this).parent().parent();
                        console.log($show_registration.find('.submission_painting_width'), $show_registration.find('.submission_painting_height'));
                        var $error_placement = $widthHeightContainer.find('.opa-show-registration__painting-height');

                        // Error
                        var errors = [];
                        $error_placement.find('.opa-show-registration__error').remove();

                        // Get painting size
                        var painting_width = parseInt($widthHeightContainer.find('.submission_painting_width').val()) || 0;
                        var painting_height = parseInt($widthHeightContainer.find('.submission_painting_height').val()) || 0;
                        var painting_sq_in_size = painting_width * painting_height;

                        // If min is enforced
                        console.log(minimum_painting_sq_inches, painting_sq_in_size);
                        if (minimum_painting_sq_inches !== 0 && minimum_painting_sq_inches > painting_sq_in_size) {
                            errors.push('<?php printf(__('Painting must be a minimum of %d square inches', OPA_DOMAIN), $minimum_sq_inches) ?>');
                        }

                        // If max is enforced
                        if (maximum_painting_sq_inches !== 0 && maximum_painting_sq_inches < painting_sq_in_size) {
                            errors.push('<?php printf(__('Painting must not exceed %d square inches', OPA_DOMAIN), $maximum_sq_inches) ?>');
                        }

                        console.log(errors);

                        // Show Error
                        if (errors.length > 0) {
                            $error_placement.append('<div class="opa-show-registration__error">' + errors.join('<br />') + '</div>');
                        }
                    });

                    jQuery(".painting_file_class").change(function () {
                        var fileInput = this;
                        $(".check_img").fadeOut()
                        if (fileInput.files[0].size > 5242880) {
                            alert('The file is too big!');
                            fileInput.value = "";
                        }
                        ;
                        var filename = fileInput.files[0].name.split('.')

                        var regex = /[$&+,:;=?[\]@#|{}'<>^*()%!-/]/;
                        if (regex.test(filename[0])) {
                            alert('Filenames must only contain letters, numbers, and underscores.');
                            fileInput.value = "";
                        }
                        var fileNameExt = fileInput.files[0].name.substr(fileInput.files[0].name.lastIndexOf('.') + 1);

                        if (fileNameExt == "heic") {
                            $(".check_img").fadeOut()
                            $(this).next().show();
                            convertHeicToJpg(this);
                        }
                        var reader = new FileReader();

                        //Read the contents of Image File.
                        reader.readAsDataURL(fileInput.files[0]);
                        reader.onload = function (e) {

                            //Initiate the JavaScript Image object.
                            var image = new Image();

                            //Set the Base64 string return from FileReader as source.
                            image.src = e.target.result;

                            //Validate the File Height and Width.
                            image.onload = function () {
                                let height = this.height;
                                let width = this.width;
                                let longestSide = Math.max(width, height);
                                if (longestSide < 1400) {
                                    alert("Please upload an image with the longest dimension between 1400px and 4000px");
                                    fileInput.value = "";
                                    return false;
                                }
                                if (longestSide > 4000) {
                                    alert("Please upload an image with the longest dimension between 1400px and 4000px");
                                    fileInput.value = "";
                                    return false;
                                }
                                $(".check_img").fadeIn()
                                return true;
                            };
                        };
                    });

                    function convertHeicToJpg(input) {


                        var blob = $(input)[0].files[0]; //ev.target.files[0];
                        heic2any({
                            blob: blob,
                            toType: "image/jpeg",
                        })
                            .then(function (resultBlob) {

                                var url = URL.createObjectURL(resultBlob);
                                $(input).parent().find(".upload-file").css("background-image", "url(" + url + ")"); //previewing the uploaded picture
                                //adding converted picture to the original <input type="file">
                                let fileInputElement = $(input)[0];
                                let container = new DataTransfer();
                                let filename = input.files[0].name.split(".");

                                let file = new File([resultBlob], filename[0] + ".jpg", {
                                    type: "image/jpeg",
                                    lastModified: new Date().getTime()
                                });
                                container.items.add(file);

                                fileInputElement.files = container.files;
                                $('#frame').attr('src', URL.createObjectURL(fileInputElement.files[0]));
                                console.log(fileInputElement.files);
                                $(input).next().hide();
                                // green check
                                $(".check_img").fadeIn()
                            })
                            .catch(function (x) {
                                console.log(x.code);
                                console.log(x.message);
                            });

                    }

                    /*

                    var $paintingPrice = jQuery(".painting_price_display_class");

                    console.log($paintingPrice);
                    
                    var $paintingHeight = $show_registration.find('[name=painting_height]');
                    
                    
                    $paintingHeight.val(isNaN(parseInt($paintingHeight.val())) ? "" : parseInt($paintingHeight.val()));*/

                    jQuery(".painting_price_display_class").change(function () {
                        var $paintingPrice = jQuery(this);
                        if (isNaN(parseInt($paintingPrice.val().replace("$", "").replace(",", "")))) {
                            $paintingPrice.val("");
                        } else {
                            var numericVal = parseInt($paintingPrice.val().replace("$", "").replace(",", ""));
                            $paintingPrice.parent().find("#painting_price").val(numericVal);
                            $paintingPrice.val("$" + numericVal.toLocaleString('en'));
                        }
                    });

                    jQuery('.submission_painting_width').change(function () {
                        var $paintingWidth = jQuery(this);
                        $paintingWidth.val(isNaN(parseInt($paintingWidth.val())) ? "" : parseInt($paintingWidth.val()));
                    });

                    jQuery('.submission_painting_height').change(function () {
                        var $paintingHeight = jQuery(this);
                        $paintingHeight.val(isNaN(parseInt($paintingHeight.val())) ? "" : parseInt($paintingHeight.val()));
                    });

                    jQuery('.submission_not_for_sale').change(function () {
                        var $paintingNFS = jQuery(this);
                        var $checkbox = $paintingNFS.find("#not_for_sale_display");
                        var $input = $paintingNFS.find("#not_for_sale");
                        var checked = $checkbox.is(":checked");

                        $input.val(checked ? 1 : 0);
                    });
                }

                registerEvents();

                // Row Update
                $(document).on('click', '.opa-toggle-terms', function (e) {
                    <?php
                    $file_id = get_field("terms_agreement_pdf", $show_id);
                    $url = wp_get_attachment_url($file_id);
                    ?>
                    var url = "<?php echo $url; ?>";

                    if (url != "") {
                        window.open(url, "_blank", "resizable=yes", "scrollbars=yes", "titlebar=yes", "width=800", "height=600");
                    } else {
                        window.open("/wp-content/uploads/2021/09/SUBMISSIONRULESFINALUPDATEDSEPTEMBER232021.pdf", "_blank", "resizable=yes", "scrollbars=yes", "titlebar=yes", "width=800", "height=600");
                    }
                    //$(this).parent().find('.opa-show-registration__terms-src').toggleClass('opa-show-registration__terms-src--show');

                });
                //                 $(".opa-show-registration__submit").click(function(){
                //                     $(".opa-show-registration__submit").hide();
                //                     var countStart = 0;
                // var x = setInterval(function() {
                //     countStart++;
                //     jQuery("#countDown").show();
                //     jQuery("#countDown").text(5-countStart);
                //      if (countStart == 5) {
                //         clearInterval(x);
                //         $(".opa-show-registration__submit").show();
                //         jQuery("#countDown").hide();
                // }

                // }, 1000);
                //                 })
                $(".opa_show_registration_free_entry").click(function (e) {
                    // var self = this; // Insert the token ID into the form so it gets submitted to the server

//var stripeToken = document.getElementById('stripeToken');
//stripeToken.value = token.id; // Collect Form info

                    var $form = $('#payment-form');
                    var formData = new FormData($form.get(0)); // Submit the form to the server
                    var form = $('#payment-form')[0];
                    jQuery(".validation-error-message").remove();

                    var paymentFormValid = true;


                    if (form.checkValidity() === true && paymentFormValid) {
                        jQuery("#card-errors").html("");

                        $(this).hide();
                        $(this).after('<div class="loading-message">Please wait while your files are being uploaded...</div>');

                        setTimeout(() => {
                            if (jQuery("#card-errors").html()) {
                                jQuery('html, body').animate({scrollTop: (jQuery("#card").offset().top - 275)}, 500);
                                $(this).prop('disabled', false);
                                $(".loading-message").remove();
                            }
                        }, 500);
                        $.ajax({
                            url: localized.ajax_url,
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false
                        }).then(function (data, textStatus, jqXHR) {
                            if (data.success === true) {
                                window.location.href = '<?php echo get_site_url() ?>/thankyou/?orderId=' + data.registration;
                                StripePaymentForm.paymentSuccessful(data);
                            } else if (data.success === false) {
                                StripePaymentForm.paymentFailed(data);
                            }
                        })["catch"](function (jqXHR) {
                            self.paymentFailed(null, "Server error.  Please try again later.");
                        });

                    } else {


                        e.preventDefault();
                        e.stopPropagation();
                        var $requiredFields = jQuery('#payment-form input,textarea,select').filter('[required]:visible');
                        // $requiredFields = $requiredFields.toArray().concat($requiredPaymentInputs);

                        $requiredFields.sort(function (a, b) {
                            return jQuery(a).offset().top > jQuery(b).offset().top ? 1 : -1;
                        });
                        var hasScrolled = false;

                        for (var i = 0; i < $requiredFields.length; i++) {
                            if (!$requiredFields[i].checkValidity() || !$($requiredFields[i]).val()) {
                                if (!hasScrolled) jQuery('html, body').animate({scrollTop: (jQuery($requiredFields[i]).offset().top - 275)}, 500);

                                jQuery($requiredFields[i]).parent().append('<div class="validation-error-message opa-show-registration__error">Required</div>');
                                hasScrolled = true;
                            }

                        }


                    }
                })

            });


        </script> <?php
    }
}

if(is_user_logged_in()) {
    add_action('wp_footer', 'opa_show_registration_scripts');
}



function get_show_error_html($message)
{
    return '<div class="opa-show-error"><p>' . __($message, OPA_DOMAIN) . '</p></div>';
}