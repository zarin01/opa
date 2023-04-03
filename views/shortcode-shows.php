<?php
    $display_type = $GLOBALS['shows_display'];
    $opa_shows = OPA_Shows::shows_query( $display_type );
if($display_type == "open_registration_new" || $display_type == "future_new" ||$display_type == "past_new" ||$display_type == "recent_new" ||$display_type == "upcoming_new" ){
  foreach ($opa_shows as $opa_shows_ids){
      $opa_show_featured_image = get_the_post_thumbnail_url( $opa_shows_ids, 'full' );
      $opa_show_short_description = get_field( 'opa_show_short_description',$opa_shows_ids );
      $opa_show_location = get_field( 'opa_show_location' ,$opa_shows_ids);
      $opa_show_event_time = get_field( 'opa_show_event_time' ,$opa_shows_ids);
      $opa_show_start_time = get_field('opa_start_registration_date',$opa_shows_ids);
      ?><div class="opa-show">

                    <?php if ( $opa_show_featured_image ) : ?>
                        <div class="opa-show__image">
                            <img src="<?php echo $opa_show_featured_image ?>" />
                        </div><?php
                    endif; ?>

                    <div class="opa-show__info">
                        <div class="opa-show__info-title">
                            <?php esc_html_e( get_the_title($opa_shows_ids) ) ?>
                        </div>
                        <div class="opa-show__info-description">
		                    <?php esc_html_e( wp_strip_all_tags( $opa_show_short_description ) ) ?>
                        </div>
                        <div class="opa-show__info-location">
		                    <?php echo '<span class="show-location-label">';
		                    _e( 'Location', OPA_DOMAIN );
                            echo ':</span> ';

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
                                        $show_sub_event_name = "<span class='show-sub-event-name'>" . $day['event_area_name'] . "</span>: ";
                                    }
                                    printf(
                                        '<div class="opa-show__info-day">
                                            %s%s - %s
                                        </div>',
                                        $show_sub_event_name,
                                        $day['start_time'],
                                        $day['end_time']
                                    );
                                }
                            ?>
                        </div>
                        <div class="opa-show__register">
                            <a href="<?php echo esc_url( get_permalink( $opa_shows_ids ) ) ?>" class="opa-show__register-cta button">
                                <?php echo $display_type === 'current' ? __("Register Now", OPA_DOMAIN) : __("Learn More", OPA_DOMAIN); ?>
                            </a>
                        </div>
                    </div>
                </div>
      <?php
  }
}else{
    if( $opa_shows && $opa_shows->have_posts() ) : ?>
        <div class="opa-show-list"><?php
            while($opa_shows->have_posts()) :
                $opa_shows->the_post();
                $post_id = get_the_ID();
	            $opa_show_featured_image = get_the_post_thumbnail_url( $post_id, 'full' );
	            $opa_show_short_description = get_field( 'opa_show_short_description' );
	            $opa_show_location = get_field( 'opa_show_location' );
	            $opa_show_event_time = get_field( 'opa_show_event_time' );
                $opa_show_start_time = get_field('opa_start_registration_date');

                ?>
                <div class="opa-show">

                    <?php if ( $opa_show_featured_image ) : ?>
                        <div class="opa-show__image">
                            <img src="<?php echo $opa_show_featured_image ?>" />
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
                                        echo "Name : " . $day['event_area_name'];
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
                            <a href="<?php echo esc_url( get_permalink( $post_id ) ) ?>" class="opa-show__register-cta button">
                                <?php echo $display_type === 'current' ? __("Register Now", OPA_DOMAIN) : __("Learn More", OPA_DOMAIN); ?>
                            </a>
                        </div>
                    </div>
                </div><?php
            endwhile; ?>
        </div><?php
    else : ?>
        <div class="opa-show-list opa-show-list--no-results">
            <?php _e( 'No shows currently found', OPA_DOMAIN ) ?>
        </div><?php
    endif;

    wp_reset_query();
}
?>


