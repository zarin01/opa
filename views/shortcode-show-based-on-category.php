<?php
$display_type = $GLOBALS['shows_display'];
$heading_style = $GLOBALS['heading_style'];

$opa_shows = OPA_Shows::shows_query_category( $display_type );

$keys_category = array(
    'current_event'=>'Current Events',
    'future_event'   => 'Future Events',
    'past_event'     => 'Past Events',
    'recent_event'  => 'Recent Events',
    'upcoming_event'=> 'Upcoming Events'
);

if( $opa_shows && $opa_shows->have_posts() ) :
?>
<div class="show-wrapper current-shows <?php echo $display_type ?>">
    <?php
    if($heading_style != 'hide'){
    ?>
    <h2 class="entry-title">
        <?php
        if (array_key_exists($display_type, $keys_category)) {
        echo $keys_category[$display_type];
        }
        ?>
    </h2>
    <?php } ?>
    <div class="opa-show-list">
    <?php
    while($opa_shows->have_posts()) :


        $opa_shows->the_post();
        $post_id = get_the_ID();
        $opa_show_featured_image = get_the_post_thumbnail_url( $post_id, 'full' );
        $opa_show_short_description = get_field( 'opa_show_short_description' );
        $opa_show_location = get_field( 'opa_show_location' );
        $opa_show_event_time = get_field( 'opa_show_event_time' );
        $opa_show_start_time = parse_acf_date(get_field('opa_start_registration_date'));
        $opa_show_end_time = parse_acf_date(get_field('opa_end_registration_date'));

        ?>
        <div class="opa-show <?php echo 'opa-show-' . $post_id; ?>">

        <?php if ( $opa_show_featured_image ) : ?>
        <div class="opa-show__image">
        <img src="<?php echo $opa_show_featured_image ?>" alt="<?php echo get_the_title() ?> Featured Image" />
        </div><?php
    endif; ?>

        <div class="opa-show__info">
            <div class="opa-show__info-title">
                <?php esc_html_e( get_the_title() ) ?>
            </div>
            <div class="opa-show__info-description">
                <?php esc_html_e( wp_strip_all_tags( $opa_show_short_description ) ) ?>
            </div>
            <div class="opa-show__info-location">
                <?php _e( 'Location', OPA_DOMAIN ) ?>:
                <?php

                if(!empty( $opa_show_location )){
                    esc_html_e( $opa_show_location );
                }else{
                    echo "Location To Be Determined";
                }

                ?>
            </div>
            <div class="opa-show__info-days">
                <?php
                foreach( $opa_show_event_time as $day ) {

                    if(!empty($day['event_area_name'])) {
                        echo "" . $day['event_area_name'];
                    }
                    printf(
                        '<div class="opa-show__info-day">
                                            %s - %s
                                        </div>',
                        $day['start_time'],
                        $day['end_time']
                    );
                }
                ?>
            </div>
            <div class="opa-show__register">
                <?php
                // Event CPT "Learn More" Custom Button
                if (get_post_type( $post_id ) == 'opa-event'){
                    $opa_event_button_url = get_field('opa_events_button_url');
                    $opa_event_button_text = get_field('opa_events_button_text');

                    // Set default values for variables if not set via ACF Pro
                    if(empty($opa_event_button_url) || is_null($opa_event_button_url)){
                        $opa_event_button_url = get_permalink($post_id);
                    }
                    if(empty($opa_event_button_text) || is_null($opa_event_button_text)){
                        $opa_event_button_text = "Learn More";
                    }

                    echo '<a href="'. $opa_event_button_url .'" class="opa-event opa-event-button button">'.$opa_event_button_text.'</a>';
                } else {

                    // Info Button for Shows
                    echo '<a href="'. esc_url( get_permalink( $post_id ) ) .'" class="button opa-show-learn-more-button">Info</a>';

                      //Load Convention Registration for National Shows
                    $publish_convention_registration = get_field("convention_registration");
                    if(isset($publish_convention_registration) && !empty($publish_convention_registration)){
                        if(is_national_show($post_id)){
                        echo '<a href="/product/2023-national-exhibition-convention-event-registration/" class="button opa-show-learn-more-button">Registration</a>';
                        }
                    }
                  

                    // Register Button for Shows
                    while( have_rows('show-info-opa') ) : the_row();
                    $show_reg_link_field_array = get_sub_field_object('opa-show-reg-link');
                    if (isset($show_reg_link_field_array['value']['url'])){
                        $show_reg_link = $show_reg_link_field_array['value']['url'];?>
                        <a href="<?php echo esc_url( $show_reg_link ) ?>" class="opa-show__convention-registration-cta button opa-show-register-button"><?php echo __("Register", OPA_DOMAIN); ?></a><?php
                    }
                    endwhile;

                    // Submit button for Shows - Only showing "Submit Here" button if registration is open
                    $show_start_timestamp = strtotime($opa_show_start_time);
                    $show_end_timestamp = strtotime($opa_show_end_time) .'<br >';
                    $current_timestamp = strtotime(convert_date_to_chicago_time("now"));
                    if ($show_end_timestamp > $current_timestamp && $show_start_timestamp < $current_timestamp){
                        ?>
                        <a href="<?php echo esc_url( get_permalink( $post_id ) ) . '#js-payment-form-widget' ?>" class="opa-show__register-cta button opa-show-submit-art-button"><?php echo __("Submit Art", OPA_DOMAIN); ?></a>
                        <?php
                    }

                    // View Winners Button
                    $publish_winners = get_field("publish_winners");
                    if(isset($publish_winners) && !empty($publish_winners)){
                        echo '<a href="/awardees?show_id=' . $post_id .'" class="button">View Winners</a>';
                    }

                    if (is_online_showcase($post_id) || is_student_art_competition($post_id)) echo '<a class="opa-show__register-cta button opa-show-submit-art-button" href="/show-gallery/?show=' . $post_id .'">View Entries</a>';

                    if (!is_online_showcase($post_id) && !is_student_art_competition($post_id) && get_field("display_acceptance_list")) echo '<a class="opa-show__register-cta button opa-show-submit-art-button" href="/show-gallery/?show=' . $post_id .'&display=list">Acceptance List</a>';

                    if (get_field("display_gallery_view")) echo '<a class="opa-show__register-cta button opa-show-submit-art-button" href="/show-gallery/?show=' . $post_id .'">Gallery</a>';
                }
                ?>
            </div>
        </div>
        </div><?php
    endwhile; ?>
    </div>
    <div style="clear: both;"></div>
</div>
<?php
else : ?>
    <div class="show-wrapper current-shows opa-show-list opa-show-list--no-results">

    <?php
    if($heading_style != 'hide'){
        ?>
        <h2 class="entry-title">
            <?php
            if (array_key_exists($display_type, $keys_category)) {
                echo $keys_category[$display_type];
            }
            ?>
        </h2>
    <?php } ?>

    <?php _e( 'No shows currently found', OPA_DOMAIN ) ?>
    <div style="clear: both;"></div>
    </div>
<?php
endif;

wp_reset_query();

?>


