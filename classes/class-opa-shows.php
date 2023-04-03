<?php
class OPA_Shows {

    static function init() {
        add_action( 'init', __CLASS__ . '::show_cpt' );
        add_filter( 'body_class', __CLASS__ . '::body_class', 10, 1 );
        add_filter( 'the_content', __CLASS__ . '::show_detail_content', 10, 1 );
        add_action( 'wp_ajax_opa_show_registration', __CLASS__ . '::register' );
        add_action( 'wp_ajax_opa_juror_search', __CLASS__ . '::juror_search' );
        add_action( 'wp_ajax_opa_juror_add_to_show', __CLASS__ . '::juror_add_to_show' );
        add_action( 'wp_ajax_opa_jury_round_add_to_show', __CLASS__ . '::jury_round_add_to_show' );
        add_action( 'wp_ajax_opa_artwork_to_round', __CLASS__ . '::artwork_to_round' );
        add_action( 'wp_ajax_opa_artwork_activate_deactivate', __CLASS__ . '::artwork_activate_deactivate' );
        add_action( 'wp_ajax_opa_artwork_remove_from_round', __CLASS__ . '::artwork_remove_from_round' );
        add_action( 'wp_ajax_opa_juror_add_to_round', __CLASS__ . '::juror_add_to_round' );
        add_action( 'wp_ajax_opa_juror_activate_deactivate', __CLASS__ . '::juror_activate_deactivate' );
        add_action( 'wp_ajax_opa_artwork_activate_deactive_next_round', __CLASS__ . '::artwork_activate_deactive_next_round' );
        add_action( 'wp_ajax_opa_jury_round_close', __CLASS__ . '::jury_round_close' );
        add_action( 'wp_ajax_opa_jury_round_delete', __CLASS__ . '::jury_round_delete' );
        add_action( 'wp_ajax_opa_juror_delete', __CLASS__ . '::juror_delete' );
        add_action( 'wp_ajax_opa_juror_delete_int', __CLASS__ . '::juror_delete_int' );
        add_action( 'wp_ajax_opa_edit_image', __CLASS__ . '::edit_image' );
        add_action( 'wp_ajax_opa_artwork_add_remove_acceptance', __CLASS__ . '::artwork_add_remove_acceptance' );
        add_action( 'wp_ajax_opa_rate_artwork', __CLASS__ . '::rate_artwork' );
        add_action( 'wp_ajax_opa_award_add_to_show', __CLASS__ . '::award_add_to_show' );
        add_action( 'wp_ajax_opa_award_add_to_show_update', __CLASS__ . '::award_add_to_show_update' );
        add_action( 'wp_ajax_opa_award_add_to_art', __CLASS__ . '::award_add_to_art' );
        add_shortcode( 'opa-shows', __CLASS__ . '::show_shows' );
        add_shortcode( 'opa-show-registration', __CLASS__ . '::show_registration' );
        add_shortcode( 'opa-judge-button', __CLASS__ . '::judge_button' );
        add_shortcode( 'opa-list-jurors', __CLASS__ . '::list_jurors' );
        add_shortcode( 'opa-list-artists', __CLASS__ . '::list_artists' );
        add_shortcode( 'opa-list-artwork', __CLASS__ . '::list_artwork' );

        add_shortcode( 'opa-shows-category', __CLASS__ . '::show_shows_based_on_category' );

    }

    static function show_shows( $atts ) {
        $GLOBALS['shows_display'] = array_key_exists( 'display', $atts ) ? $atts['display'] : '';
        ob_start();
        require( OPA_PATH . 'views/shortcode-shows.php' );
        return ob_get_clean();
    }

    static function show_shows_based_on_category( $atts ) {
        $GLOBALS['shows_display'] = array_key_exists( 'display', $atts ) ? $atts['display'] : '';
        $GLOBALS['heading_style'] = array_key_exists( 'heading_style', $atts ) ? $atts['heading_style'] : '';
        ob_start();
        require( OPA_PATH . 'views/shortcode-show-based-on-category.php' );
        return ob_get_clean();
    }

    static function show_registration( $atts ) {
        require_once( OPA_PATH . 'views/shortcode-show-registration.php' );
    }

    static function judge_button( $atts ) {
        require_once( OPA_PATH . 'views/shortcode-judge-button.php' );
    }

    static function list_jurors( $atts ) {
        require_once( OPA_PATH . 'views/shortcode-list-jurors.php' );
    }

    static function list_artists( $atts ) {
        require_once( OPA_PATH . 'views/shortcode-list-artists.php' );
    }

    static function list_artwork( $atts ) {
        require_once( OPA_PATH . 'views/shortcode-list-artwork.php' );
    }

    static function show_detail_content( $content ) {

        if ( is_singular( 'opa_show' ) ) {
            require_once( OPA_PATH . 'views/single-shows.php' );

        } else {
            return $content;
        }
    }

    static function dynamic_show_time_query( $where ) {
        $where = str_replace("meta_key = 'opa_show_event_time_$", "meta_key LIKE 'opa_show_event_time_%", $where);
        return $where;
    }

    /**
     * AJAX: Register an artist for a show
     */
    static function register() {

        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_show_registration', 'opa_show_registration_nonce' );

        $user = wp_get_current_user();
        $all_memberships = wc_memberships_get_user_memberships(get_current_user_id()); // Here
        $current_membership_name = $all_memberships[0]->get_plan()->name;
        $stripe_token = OPA_Functions::clean_input( $_POST["stripeToken"] );
//        $painting_name = OPA_Functions::clean_input( $_POST["painting_name"] );
//        $painting_description = OPA_Functions::clean_input( $_POST["painting_description"] );
//        $painting_price = OPA_Functions::clean_input( $_POST["painting_price"] );
//        $painting_width = OPA_Functions::clean_input( $_POST["painting_width"] );
//        $painting_height = OPA_Functions::clean_input( $_POST["painting_height"] );
        //$free_entry = OPA_Functions::clean_input( $_POST["free_entry"] );

       // $painting_check = getimagesize($_FILES["painting_file"]["tmp_name"]);
        $show_id = intval( $_POST["show_id"] );
        $free_entry = get_field('free_entry_for', $show_id);

        $secondary_membership_data = get_secondary_membership_data(get_current_user_id());
        $secondary_membership_grants_free_entry = get_secondary_membership_free_entry_into_show($secondary_membership_data, $show_id);

        //print_r($free_entry);
        $stripe_processed_date = current_time( 'mysql' );
        //$paintingPrice = (count($_POST['painting_name'])*14);
        try {

            // How many registrations do the user have for this event?
            $num_of_registrations = OPA_Model_Art::get_user_registrations( $user, $show_id );
            if(is_online_showcase($show_id)){
                $max_registrations = 1000;
            } else {
                $max_registrations = count( OPA_SHOW_REGISTRATION_FEE );
            }

            //Ensure the user does not submit too many submissions
            if ( $num_of_registrations >= $max_registrations ) {
                wp_send_json_error( array(
                    'message' => __( 'Max number of registrations reached for this event', OPA_DOMAIN )
                ));
                return;
            }

            // Ensure the user submitted a piece of art
//            if ( $painting_check === false ) {
//                wp_send_json_error( array(
//                    'message' => __( 'You must provide a painting!', OPA_DOMAIN )
//                ));
//                return;
//            }

            // Make price $15 for shows with a taxonomy that includes online showcase
            // @TODO increase the limit of shows that can be entered for online showcase.  There is no limit.
            if(is_online_showcase($show_id)){
                $submission_prices = 15;
            } else {
                $submission_prices = array(35, 15, 10);
            }

            $total_price = 0;
            if(is_online_showcase($show_id)){
                for ($p = $num_of_registrations; $p < $num_of_registrations + count($_POST['painting_name']); $p++ ) {
                    $total_price += $submission_prices;
                }
            } else {
                for ($p = $num_of_registrations; $p < $num_of_registrations + count($_POST['painting_name']); $p++ ) {
                    $total_price += $submission_prices[$p];
                }
            }

            // Process Payment
            if (in_array($current_membership_name,$free_entry) || $secondary_membership_grants_free_entry) {
                $payment_response['success'] = true;
                $payment_response['stripe_charge_id'] = 'Free Entry';

            }else{
                $payment_response = OPA_Payment::process_payment($user, $stripe_token, intval(number_format($total_price, 2) * 100), get_safe_title($show_id) );
            }

            // echo $payment_response;
            // Send response

            if ( $payment_response['success'] === true ) {
                for ($i=0;$i<count($_POST['painting_name']);$i++) {
                    $painting_name = OPA_Functions::clean_input( $_POST["painting_name"][$i]);
                    $painting_description = OPA_Functions::clean_input( $_POST["painting_description"][$i] );

                    $painting_price = OPA_Functions::clean_input( $_POST["painting_price"][$i] );
                    $painting_width = OPA_Functions::clean_input( $_POST["painting_width"][$i] );
                    $painting_height = OPA_Functions::clean_input( $_POST["painting_height"][$i] );
                    $painting_not_for_sale = OPA_Functions::clean_input( $_POST["not_for_sale"][$i] );
                    if (in_array($current_membership_name,$free_entry) || $secondary_membership_grants_free_entry) {
                        $individual_art_price = 0;
                    }else{
                        $individual_art_price = $submission_prices[$num_of_registrations + $i];
                        if (is_online_showcase($show_id)) $individual_art_price = $submission_prices;
                    }

                    $registration = OPA_Model_Art::create_user_registration(
                        $user,
                        $show_id,
                        $painting_name,
                        $painting_description,
                        $painting_price,
                        $painting_width,
                        $painting_height,
                        base64_encode(file_get_contents($_FILES["painting_file"]["tmp_name"][$i])),
                        $_FILES["painting_file"]["tmp_name"][$i],
                        $_FILES["painting_file"]["name"][$i],
                        $payment_response['stripe_charge_id'],
                        number_format($individual_art_price, 2),
                        $stripe_processed_date,
                        $painting_not_for_sale
                    );

                }


                // OPA_Functions::confirmation_email($registration,'show_registration');
                //die;
                wp_send_json_success( array(
                    'message' => 'Payment Successful',
                    'registration' => $registration
                ));
            } else {
                wp_send_json_error( array(
                    'message' => $payment_response['error']
                ));
            }
        } catch( Exception $e ) {
            wp_send_json_error( array(
                'message' => 'Server Error!'
            ));
        }
        die();
    }

    /**
     * AJAX: Search for a Juror with Search Query
     */
    static function juror_search() {
        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_juror_search', 'opa_juror_search_nonce' );

        $search = OPA_Functions::clean_input( $_POST["search"] );
        $include = array();

        try {

            // segmented for shows/rounds
            if ( array_key_exists( 'round_id', $_POST ) && array_key_exists( 'show_id', $_POST ) ) {
                $show_id = intval( $_POST["show_id"] );
                $round_id = intval( $_POST["round_id"] );
                $jurors_in_show = OPA_Model_Show::get_jurors( $show_id );
                $include = wp_list_pluck( $jurors_in_show, 'ID' );
            }

            // $users
            $users = new WP_User_Query( array(
                'include' => $include,
                'search'         => '*'.esc_attr( $search ).'*',
                'search_columns' => array(
                    'user_login',
                    'user_nicename',
                    'user_email',
                    'user_url',
                    'user_firstname',
                    'user_lastname',
                ),
            ) );
            $users_found = $users->get_results();


            // Send response
            wp_send_json_success( array(
                'message' => 'Search Successful',
                'users' => $users_found
            ));
        } catch( Exception $e ) {
            wp_send_json_error( array(
                'message' => 'Server Error!'
            ));
        }
        die();
    }


    static function shows_query( $display = '', $custom_atts = array() ) {

        add_filter( 'posts_where', __CLASS__ . '::dynamic_show_time_query' );

        $date_now = date('Y-m-d');
        $six_months_ago = date("Y-m-d", strtotime($date_now . "-6 months"));

        $standard_query_atts = array_merge(
            array(
                'post_type' => 'opa_show',
                'posts_per_page' => -1,
                'order' => 'ASC',
            ),
            $custom_atts
        );

        switch ( $display ) {
            case 'open_registration':
                // Get all shows that are open currently for registration
                $opa_shows = new WP_Query(
                    array_merge(
                        $standard_query_atts,
                        array(
                            'meta_query' => array(
                                'relation' => 'AND',
                                array(
                                    'key' => 'opa_start_registration_date',
                                    'compare' => '<=',
                                    'value' => $date_now .' 00:00:00',
                                ),
                                array(
                                    'key' => 'opa_end_registration_date',
                                    'compare' => '>=',
                                    'value' => $date_now.' 00:00:00',
                                )
                            ),
                            'orderby' => 'meta_value'
                        )
                    )
                );

                break;
            case 'future':
                // Get all shows that have a future start registration date
                $opa_shows = new WP_Query(
                    array_merge(
                        $standard_query_atts,
                        array(
                            'meta_query' => array(
                                array(
                                    'key' => 'opa_start_registration_date',
                                    'compare' => '>=',
                                    'value' => $date_now.' 00:00:00',
                                )
                            ),
                            'orderby' => 'meta_value',
                        )
                    )
                );
                break;
            case 'past':
                // Get all shows that are completely in the past based on end_time repeater field
                $opa_shows = new WP_Query(
                    array_merge(
                        $standard_query_atts,
                        array(
                            'meta_query' => array(
                                'relation' => 'AND',
                                array(
                                    'key'		=> 'opa_end_registration_date',
                                    //'key'		=> 'opa_show_event_time_$_end_time',
                                    'compare'	=> '<=',
                                    'value'		=> $date_now.' 00:00:00',
                                ),
                            ),
                            'orderby' => 'meta_value',
                        )
                    )
                );
                break;
            case 'recent':
                // Get last 6 months of shows that are completely in the past based on end_time repeater field
                $opa_shows = new WP_Query(
                    array_merge(
                        $standard_query_atts,
                        array(
                            'meta_query' => array(
                                'relation' => 'AND',
                                array(
                                    'key'		=> 'opa_end_registration_date',
                                    'compare'	=> '<=',
                                    'value'		=> $date_now.' 00:00:00',
                                ),
                                array(
                                    'key'		=> 'opa_end_registration_date',
                                    'compare'	=> '>=',
                                    'value'		=> $six_months_ago.' 00:00:00',
                                ),
                            ),
                            'orderby' => 'meta_value',
                        )
                    )
                );

                break;
            case 'upcoming':
                // Includes both future shows and shows that are not in the past based on repeater end_date field
                $opa_shows = new WP_Query(
                    array_merge(
                        $standard_query_atts,
                        array(
                            'meta_query' => array(
                                'relation' => 'OR',
                                array(
                                    'key'		=> 'opa_end_registration_date',
                                    'compare'	=> '>=',
                                    'value'		=> $date_now.' 00:00:00',
                                ),
                                array(
                                    'key' => 'opa_start_registration_date',
                                    'compare' => '>=',
                                    'value' => $date_now.' 00:00:00',
                                )
                            ),
                            'orderby' => 'meta_value',
                        )
                    )
                );
                break;
            case 'open_registration_new':
                $opa_shows = new WP_Query(
                    $standard_query_atts
                );
                if( $opa_shows && $opa_shows->have_posts() ) :
                    while($opa_shows->have_posts()) :
                        $opa_shows->the_post();
                        $post_id = get_the_ID();
                        $opa_show_event_time = get_field( 'opa_show_event_time' , $post_id);
                        $start_dates =array();
                        $end_dates = array();
                        foreach($opa_show_event_time as $opa_show_event_times){
                            $start_dates[] = $opa_show_event_times['start_time'];
                            $end_dates[] =   $opa_show_event_times['end_time'];
                        }
                        usort($start_dates, function ($a, $b) {
                            $dateTimestamp1 = strtotime($a);
                            $dateTimestamp2 = strtotime($b);
                            return $dateTimestamp1 < $dateTimestamp2 ? -1 : 1;
                        });

                        $startdate_min = date("Y-m-d", strtotime($start_dates[0]));

                        usort($end_dates, function ($a, $b) {
                            $dateTimestamp1 = strtotime($a);
                            $dateTimestamp2 = strtotime($b);
                            return $dateTimestamp1 < $dateTimestamp2 ? -1 : 1;
                        });
                        $enddate_max =  date("Y-m-d", strtotime($end_dates[count($end_dates) - 1]));
                        $date_now = date('Y-m-d');

                        if($startdate_min < $date_now &&  $enddate_max > $date_now){
                            $post_ids[] = get_the_ID();
                        }
                    endwhile;
                endif;
                $opa_shows = $post_ids;
                break;
            case 'future_new':

                $opa_shows = new WP_Query(
                    $standard_query_atts
                );
                if( $opa_shows && $opa_shows->have_posts() ) :
                    while($opa_shows->have_posts()) :
                        $opa_shows->the_post();
                        $post_id = get_the_ID();
                        $opa_show_event_time = get_field( 'opa_show_event_time' , $post_id);
                        $start_dates =array();
                        $end_dates = array();
                        foreach($opa_show_event_time as $opa_show_event_times){
                            $start_dates[] = $opa_show_event_times['start_time'];
                            $end_dates[] =   $opa_show_event_times['end_time'];
                        }
                        usort($start_dates, function ($a, $b) {
                            $dateTimestamp1 = strtotime($a);
                            $dateTimestamp2 = strtotime($b);

                            return $dateTimestamp1 < $dateTimestamp2 ? -1 : 1;
                        });
                        $startdate_min = date("Y-m-d", strtotime($start_dates[0]));
                        usort($end_dates, function ($a, $b) {
                            $dateTimestamp1 = strtotime($a);
                            $dateTimestamp2 = strtotime($b);

                            return $dateTimestamp1 < $dateTimestamp2 ? -1 : 1;
                        });
                        $enddate_max =  date("Y-m-d", strtotime($end_dates[count($end_dates) - 1]));
                        $date_now = date('Y-m-d');
                        if($startdate_min > $date_now ){
                            $post_ids[] = get_the_ID();
                        }
                    endwhile;
                endif;
                $opa_shows = $post_ids;
                break;
            case 'past_new':
                $opa_shows = new WP_Query(
                    $standard_query_atts
                );
                if( $opa_shows && $opa_shows->have_posts() ) :
                    while($opa_shows->have_posts()) :
                        $opa_shows->the_post();
                        $post_id = get_the_ID();
                        $opa_show_event_time = get_field( 'opa_show_event_time' , $post_id);
                        $start_dates =array();
                        $end_dates = array();
                        foreach($opa_show_event_time as $opa_show_event_times){
                            $start_dates[] = $opa_show_event_times['start_time'];
                            $end_dates[] =   $opa_show_event_times['end_time'];
                        }
                        usort($start_dates, function ($a, $b) {
                            $dateTimestamp1 = strtotime($a);
                            $dateTimestamp2 = strtotime($b);

                            return $dateTimestamp1 < $dateTimestamp2 ? -1 : 1;
                        });
                        $startdate_min = date("Y-m-d", strtotime($start_dates[0]));
                        usort($end_dates, function ($a, $b) {
                            $dateTimestamp1 = strtotime($a);
                            $dateTimestamp2 = strtotime($b);

                            return $dateTimestamp1 < $dateTimestamp2 ? -1 : 1;
                        });
                        $enddate_max =  date("Y-m-d", strtotime($end_dates[count($end_dates) - 1]));
                        $date_now = date('Y-m-d');
                        if($enddate_max < $date_now ){
                            $post_ids[] = get_the_ID();
                        }
                    endwhile;
                endif;
                $opa_shows = $post_ids;
                break;
            case 'recent_new':
                // Includes both future shows and shows that are not in the past based on repeater end_date field
                $opa_shows = new WP_Query(
                    $standard_query_atts
                );
                if( $opa_shows && $opa_shows->have_posts() ) :
                    while($opa_shows->have_posts()) :
                        $opa_shows->the_post();
                        $post_id = get_the_ID();
                        $opa_show_event_time = get_field( 'opa_show_event_time' , $post_id);
                        $start_dates =array();
                        $end_dates = array();
                        foreach($opa_show_event_time as $opa_show_event_times){
                            $start_dates[] = $opa_show_event_times['start_time'];
                            $end_dates[] =   $opa_show_event_times['end_time'];
                        }
                        usort($start_dates, function ($a, $b) {
                            $dateTimestamp1 = strtotime($a);
                            $dateTimestamp2 = strtotime($b);

                            return $dateTimestamp1 < $dateTimestamp2 ? -1 : 1;
                        });
                        $startdate_min = date("Y-m-d", strtotime($start_dates[0]));
                        usort($end_dates, function ($a, $b) {
                            $dateTimestamp1 = strtotime($a);
                            $dateTimestamp2 = strtotime($b);

                            return $dateTimestamp1 < $dateTimestamp2 ? -1 : 1;
                        });
                        $enddate_max =  date("Y-m-d", strtotime($end_dates[count($end_dates) - 1]));
                        $date_now = date('Y-m-d');
                        $six_months_ago = date("Y-m-d", strtotime($date_now . "-6 months"));

                        if($enddate_max < $date_now  &&  $enddate_max > $six_months_ago ){
                            $post_ids[] = get_the_ID();
                        }
                    endwhile;
                endif;
                $opa_shows = $post_ids;
                break;
            case 'upcoming_new':
                $opa_shows = new WP_Query(
                    $standard_query_atts
                );
                if( $opa_shows && $opa_shows->have_posts() ) :
                    while($opa_shows->have_posts()) :
                        $opa_shows->the_post();
                        $post_id = get_the_ID();
                        $opa_show_event_time = get_field( 'opa_show_event_time' , $post_id);
                        $start_dates =array();
                        $end_dates = array();
                        foreach($opa_show_event_time as $opa_show_event_times){
                            $start_dates[] = $opa_show_event_times['start_time'];
                            $end_dates[] =   $opa_show_event_times['end_time'];
                        }
                        usort($start_dates, function ($a, $b) {
                            $dateTimestamp1 = strtotime($a);
                            $dateTimestamp2 = strtotime($b);
                            return $dateTimestamp1 < $dateTimestamp2 ? -1 : 1;
                        });
                        $startdate_min = date("Y-m-d", strtotime($start_dates[0]));
                        usort($end_dates, function ($a, $b) {
                            $dateTimestamp1 = strtotime($a);
                            $dateTimestamp2 = strtotime($b);
                            return $dateTimestamp1 < $dateTimestamp2 ? -1 : 1;
                        });
                        $enddate_max =  date("Y-m-d", strtotime($end_dates[count($end_dates) - 1]));
                        $date_now = date('Y-m-d');
                        $six_months_ago = date("Y-m-d", strtotime($date_now . "-6 months"));
                        if($enddate_max > $date_now  &&  $startdate_min > $date_now){
                            $post_ids[] = get_the_ID();
                        }
                    endwhile;
                endif;
                $opa_shows = $post_ids;

                break;
            default:
                $opa_shows = new WP_Query(
                    $standard_query_atts
                );
                break;
        }

        remove_filter( 'posts_where', __CLASS__ . '::dynamic_show_time_query' );

        return $opa_shows;
    }

    static function shows_query_category( $display = '', $custom_atts = array() ) {
        $standard_query_atts = array_merge(
            array(
                'post_type' => array('opa_show','opa-event'),
                'posts_per_page' => -1,
                'order' => 'ASC',
            ),
            $custom_atts
        );

        $opa_shows_category = new WP_Query(
            array_merge(
                $standard_query_atts,
                array(
                    'meta_query' => array(
                        array(
                            'key' => 'events_show_category',
                            'compare' => '==',
                            'value' => $display,
                        ),
                    ),
                    'orderby' => 'meta_value'
                )
            )
        );
        return $opa_shows_category;
    }


    /**
     * AJAX: Add Juror to a Show
     */
    static function juror_add_to_show() {
        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_juror_add_to_show', 'opa_juror_add_to_show_nonce' );
        $show_id = intval( $_POST["show_id"] );
        $juror_id = intval( $_POST["juror_id"] );
        $existing_jurors = OPA_Model_Show::get_jurors( $show_id );
        $listStatus = OPA_Model_show::get_show_in_list_status($show_id,$juror_id);
        $existing_juror_ids = wp_list_pluck( $existing_jurors, 'ID' );
        if ( in_array( $juror_id, $existing_juror_ids ) && $listStatus[0]->show_in_list) {
            wp_send_json_error( array(
                'message' => 'Juror already exists on this show.',
            ));
        }
        elseif( in_array( $juror_id, $existing_juror_ids ) && $listStatus[0]->show_in_list==0)
        {
            global $wpdb;
            $table = $wpdb->prefix.'opa_jurors';
            $updateStatus = $wpdb->update( $table, array('show_in_list' => 1), array( 'juror_id' => $juror_id , 'show_id' => $show_id  ) );    
        }

        try {
            // Add Juror to Show
            if(!in_array( $juror_id, $existing_juror_ids ))
            {
            $id = OPA_Model_Jurors::add_juror( $juror_id, $show_id );

            if ( $id ) {
                wp_send_json_success( array(
                    'message' => 'Juror Added'
                ));
            } else {
                wp_send_json_error( array(
                    'message' => 'Database issue adding Juror.'
                ));
            }
        }

            // Send response
        } catch( Exception $e ) {
            wp_send_json_error( array(
                'message' => 'Server Error!'
            ));
        }
        die();
    }

    /**
     * AJAX: Add Jury Round to Show
     */
    static function jury_round_add_to_show() {
        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_jury_round_add_to_show', 'opa_jury_round_add_to_show_nonce' );

        $show_id = intval( $_POST["show_id"] );
        $round_name = OPA_Functions::clean_input( $_POST["round_name"] );

        $jury_rounds = OPA_Model_Show::get_jury_rounds( $show_id );
        $existing_jury_rounds = wp_list_pluck( $jury_rounds, 'jury_round_active' );

        if ( in_array( 1, $existing_jury_rounds ) ) {
            wp_send_json_error( array(
                'message' => 'You cannot create a new jurying round until all existing rounds have been completed'
            ));
        }

        try {
            // Get Artwork for Next Round First
            $artwork_for_next_round = OPA_Model_Jury_Round_Art::get_artwork_for_next_round( $show_id );

            // Add Round to Show
            $new_round_id = OPA_Model_Jury_Rounds::add_round( $show_id, $round_name );

            if ( $new_round_id ) {

                foreach ( $artwork_for_next_round as $art ) {
                    OPA_Model_Jury_Round_Art::add_artwork( intval( $new_round_id ), intval( $art['art_id'] ) );
                }

                wp_send_json_success( array(
                    'message' => 'Jury Round Added'
                ));
            } else {
                wp_send_json_error( array(
                    'message' => 'Database issue adding Jury Round.'
                ));
            }

            // Send response
        } catch( Exception $e ) {
            wp_send_json_error( array(
                'message' => 'Server Error!'
            ));
        }
        die();
    }

    /**
     * AJAX: Add Artwork to Round
     */
    static function artwork_to_round() {
        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_artwork_to_round', 'opa_artwork_to_round_nonce' );

        $round_id = intval( $_POST["round_id"] );
        $art_ids =  $_POST["art_to_add"];

        $existing_art = OPA_Model_Jury_Round_Art::get_artwork( $round_id,'' );
        $existing_art_ids = wp_list_pluck( $existing_art, 'art_id' );

        try {
            if ( is_array( $art_ids ) && !empty( $art_ids ) ) {
                foreach ( $art_ids as $art_id ) {
                    if ( ! in_array( intval( $art_id ), $existing_art_ids ) ) {
                        OPA_Model_Jury_Round_Art::add_artwork( $round_id, $art_id );
                    }
                }
            }

            // Send response
            wp_send_json_success( array(
                'message' => 'Success'
            ));

        } catch( Exception $e ) {
            wp_send_json_error( array(
                'message' => 'Server Error!'
            ));
        }
        die();
    }

    /**
     * AJAX: Activate/Deactivate Art in Round
     */
    static function artwork_activate_deactivate() {
        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_artwork_activate_deactivate', 'opa_artwork_activate_deactivate_nonce' );

        $round_id = intval( $_POST["round_id"] );
        $art_id = intval( $_POST["art_id"] );
        $active = intval( $_POST["active"] );

        try {
            // Add Juror to Show
            OPA_Model_Jury_Round_Art::activate_or_deactivate_art( $round_id, $art_id, $active );

            wp_send_json_success( array(
                'message' => 'Updated'
            ));

            // Send response
        } catch( Exception $e ) {
            wp_send_json_error( array(
                'message' => 'Server Error!'
            ));
        }
        die();
    }

    /**
     * AJAX: Artwork Remove From Round
     */
    static function artwork_remove_from_round() {
        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_artwork_remove_from_round', 'opa_artwork_remove_from_round_nonce' );

        $round_id = intval( $_POST["round_id"] );
        $art_id = intval( $_POST["art_id"] );

        try {
            // Add Juror to Show
            OPA_Model_Jury_Round_Art::remove_artwork( $round_id, $art_id );

            wp_send_json_success( array(
                'message' => 'Removed Artwork'
            ));

            // Send response
        } catch( Exception $e ) {
            wp_send_json_error( array(
                'message' => 'Server Error!'
            ));
        }
        die();
    }

    /**
     * AJAX: Add Juror to a Round
     */
    static function juror_add_to_round() {
        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_juror_add_to_round', 'opa_juror_add_to_round_nonce' );

        $show_id = intval( $_POST["show_id"] );
        $round_id = intval( $_POST["round_id"] );
        $juror_id = intval( $_POST["juror_id"] );
        $existing_jurors = OPA_Model_Jury_Round_Jurors::get_jurors( $round_id );
        $listStatus = OPA_Model_Jury_Round_Jurors::get_show_in_list_status($juror_id,$round_id);
        $existing_juror_ids = wp_list_pluck( $existing_jurors, 'ID' );

        if ( in_array( $juror_id, $existing_juror_ids ) && $listStatus[0]->show_in_list){
            wp_send_json_error( array(
                'message' => 'Juror already exists on this round.'
            ));
        }
        elseif( in_array( $juror_id, $existing_juror_ids ) && $listStatus[0]->show_in_list==0)
        {
            global $wpdb;
            $table = $wpdb->prefix.'opa_jury_round_jurors';
            $updateStatus = $wpdb->update( $table, array('show_in_list' => 1), array( 'juror_id' => $juror_id , 'jury_round_id' => $round_id ) );    
        }
  
        try {
            // Add Juror to Round
            if(!in_array( $juror_id, $existing_juror_ids ))
            {
            $id = OPA_Model_Jury_Round_Jurors::add_juror( $juror_id, $round_id );
            // $id2 = OPA_Model_Jury_Scores::submit_rating();
            if ( $id ) {
                wp_send_json_success( array(
                    'message' => 'Juror Added'
                ));
            } else {
                wp_send_json_error( array(
                    'message' => 'Database issue adding Juror.'
                ));
            }
        }

            // Send response
        } catch( Exception $e ) {
            wp_send_json_error( array(
                'message' => 'Server Error!'
            ));
        }
        die();
    }

    /**
     * AJAX: Activate or Deactivate Juror on a Round
     */
    static function juror_activate_deactivate() {
        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_juror_activate_deactivate', 'opa_juror_activate_deactivate_nonce' );

        $round_id = intval( $_POST["round_id"] );
        $juror_id = intval( $_POST["juror_id"] );
        $active = intval( $_POST["active"] );

        try {
            // Add Juror to Show
            OPA_Model_Jury_Round_Jurors::activate_or_deactivate_juror( $round_id, $juror_id, $active );

            wp_send_json_success( array(
                'message' => 'Updated'
            ));

            // Send response
        } catch( Exception $e ) {
            wp_send_json_error( array(
                'message' => 'Server Error!'
            ));
        }
        die();
    }

    /**
     * AJAX: Artwork Activate/Deactivate Next Round
     */
    static function artwork_activate_deactive_next_round() {
        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_artwork_activate_deactive_next_round', 'opa_artwork_activate_deactive_next_round_nonce' );

        $round_id = intval( $_POST["round_id"] );
        $art_ids = explode(",", $_POST["art_id"] );
        $actives = explode(",", $_POST["active"] );

        for ($i = 0; $i < count($art_ids); $i++) {
            $art_id = intval( $art_ids[$i] );
            $active = intval( $actives[$i] );

            OPA_Model_Jury_Round_Art::activate_or_deactivate_art_next_round( $round_id, $art_id, $active );
        }

        // Send response
        wp_send_json_success( array(
            'message' => 'Updated'
        ));

        die();
    }

    /**
     * AJAX: Close a Round of Judging
     */
    static function jury_round_close() {
        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_jury_round_close', 'opa_jury_round_close_nonce' );

        $round_id = intval( $_POST["round_id"] );

        try {
            // Add Juror to Show
            OPA_Model_Jury_Rounds::close_round( $round_id );

            wp_send_json_success( array(
                'message' => 'Updated'
            ));

            // Send response
        } catch( Exception $e ) {
            wp_send_json_error( array(
                'message' => 'Server Error!'
            ));
        }
        die();
    }

// Delete Jury round task - August 4 - Ryan
    /**
     * AJAX: Close a Round of Judging
     */
    static function jury_round_delete() {
        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_jury_round_delete', 'opa_jury_round_delete_nonce' );
        global $wpdb;

        $round_id = intval( $_POST["round_del_id"] );
        //$show_id = intval($_POST["show_id"]);
        // $table1 = 'wp_opa_jury_round_art';
        // $q1 = $wpdb->delete( $table1, array( 'jury_round_id' => $round_id ) );
        //$table2 = $wpdb->prefix.'opa_jury_round_jurors';
        //$q2 = $wpdb->update( $table2, array( 'jury_round_id' => $round_id ) );
        $table3 = $wpdb->prefix.'opa_jury_rounds';
        $q3 = $wpdb->update( $table3, array( 'show_in_list' => 0 ),array('id'=>$round_id));
        // $table4 = $wpdb->prefix.'opa_jury_scores';
        // $q4 = $wpdb->update( $table4, array("count_round_scores" => 0),array( 'jury_round_id' => $round_id ) );

        if($q3 !== TRUE){

            $error ='data not delete form all tables';
            wp_send_json(['status'=>false,'response'=>$error]);

        }else{
            wp_send_json(['status'=>true,'response'=>'delete successfully']);
        }
        
        die();
    }

    // Delete Juror - August 4 - Ryan
    /**
     * AJAX: Close a Round of Judging
     */
    static function juror_delete() {

        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_juror_delete', 'opa_juror_delete_nonce' );
        global $wpdb;

        $show_id = intval( $_POST["show_id"] );
        $juror_id = intval( $_POST["juror_id"] );

        $table1 = 'wp_opa_jurors';
        $q1 = $wpdb->update( $table1, array('show_in_list' => 0), array( 'juror_id' => $juror_id , 'show_id' => $show_id  ) );
        $listStatus = $wpdb->get_results("SELECT show_in_list FROM wp_opa_jurors WHERE 'juror_id' => $juror_id AND 'show_id' => $show_id ");
        if( $q1 !== TRUE ){

            $error ='data not delete form all tables';
            wp_send_json(['status'=>false,'response'=>$error]);

        }else{
            wp_send_json_success(array(
                'show_in_list' => $listStatus[0]->show_in_list,
                'status'=>true,
                'response'=>'delete successfully'
            ));
        }
        die();
    }

    static function juror_delete_int() {

        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_juror_delete_int', 'opa_juror_delete_int_nonce' );
        global $wpdb;

        $round_id = intval( $_POST["round_id"] );
        $juror_id = intval( $_POST["juror_id_int"] );

        $table2 = 'wp_opa_jury_round_jurors';
        $q2 = $wpdb->update( $table2, array('show_in_list' => 0),array( 'juror_id' => $juror_id , 'jury_round_id' => $round_id ) );
        $table3 = $wpdb->prefix.'opa_jury_scores';
        $q3 = $wpdb->update( $table3, array('count_scores_for_average' => 0),array( 'juror_id' => $juror_id , 'jury_round_id' => $round_id ) );

        if( $q2 !== TRUE && $q3!= TRUE){

            $error ='data not delete form all tables';
            wp_send_json(['status'=>false,'response'=>$error]);

        }else{
            wp_send_json(['status'=>true,'response'=>'delete successfully']);
        }
        die();
    }
    /**
     * AJAX: Edit Image
     */
    static function edit_image() {
        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_edit_image', 'opa_edit_image_nonce' );

        $art_id = intval( $_POST["art_id"] );
        $blob = OPA_Functions::clean_input( $_POST["blob"] );

        try {
            // Add Juror to Show
            OPA_Model_Art::update_image( $art_id, $blob );

            wp_send_json_success( array(
                'message' => 'Updated'
            ));

            // Send response
        } catch( Exception $e ) {
            wp_send_json_error( array(
                'message' => 'Server Error!'
            ));
        }
        die();
    }

    /**
     * AJAX: Artwork - Declare or Relinquish Acceptance
     */
    static function artwork_add_remove_acceptance() {
        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_artwork_add_remove_acceptance', 'opa_artwork_add_remove_acceptance_nonce' );

        $art_id = intval( $_POST["art_id"] );
        $active = intval( $_POST["active"] );

        try {
            // Add Juror to Show
            OPA_Model_Art::update_acceptance( $art_id, $active );

            wp_send_json_success( array(
                'message' => 'Updated'
            ));

            // Send response
        } catch( Exception $e ) {
            wp_send_json_error( array(
                'message' => 'Server Error!'
            ));
        }
        die();
    }

    /**
     * AJAX: Rate Artwork
     */
    static function rate_artwork() {
        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_rate_artwork', 'opa_rate_artwork_nonce' );

        try {

            $juror                  = wp_get_current_user();
            $show_id               = intval( $_POST["show_id"] );
            $current_jury_round_id = OPA_Model_Show::get_current_jury_round_id( $show_id );
            $juror_has_access       = OPA_Model_Jurors::has_round_access( $current_jury_round_id, $juror->ID );

            if ( !$juror_has_access ) {
                wp_send_json_error( array(
                    'message' => __( 'You do not have access to judge this artwork!', OPA_DOMAIN )
                ));
            }

            $art_id = intval( $_POST["painting_id"] );
            $rating = number_format( floatval( $_POST["rating"] ), 2 );

            OPA_Model_Jury_Scores::submit_rating( $juror->ID, $current_jury_round_id, $art_id, $rating );

            wp_send_json_success( array(
                'message' => 'Updated Score',
                'art_id' => $art_id,
                'score' => $rating
            ));

            // Send response
        } catch( Exception $e ) {
            wp_send_json_error( array(
                'message' => 'Server Error!'
            ));
        }
        die();
    }

    /**
     * AJAX: Add Award to Show
     */
    static function award_add_to_show() {
        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_award_add_to_show', 'opa_award_add_to_show_nonce' );

        try {

            $show_id               = intval( $_POST["show_id"] );
            $award_title = OPA_Functions::clean_input( $_POST["award_name"] );
            $award_description = OPA_Functions::clean_input( $_POST["award_description"] );
            $award_value = OPA_Functions::clean_input( $_POST["award_value"] );

            $award_id = OPA_Model_Award::add_award( $show_id, $award_title, $award_description, $award_value );

            wp_send_json_success( array(
                'message' => 'Added Award',
                'award_id' => $award_id
            ));

            // Send response
        } catch( Exception $e ) {
            wp_send_json_error( array(
                'message' => 'Server Error!'
            ));
        }
        die();
    }

    /**
     * AJAX: Add Award to Show update
     */
    static function award_add_to_show_update() {
        global $wpdb;
        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_award_add_to_show_update', 'opa_award_add_to_show_update_nonce' );

        try {
            $show_id = $_POST['show_id'];
            $role = $_POST['role'];
            $award_id = $_POST['award_id'];
            if($role=='save'){
                $title = $_POST['title'];
                $desc = $_POST['desc'];
                $valueP = $_POST['valueP'];
                $query = $wpdb->query("update wp_opa_awards set title='".$title."', description='".$desc."', value='".$valueP."' where id=".$award_id." and show_id=".$show_id);
                $message = 'Award Edited';
            }else if($role=='delete'){
                $query = $wpdb->query("DELETE FROM  wp_opa_awards where id=".$award_id." and show_id=".$show_id);
                $message = 'Award Deleted';
            }
            if($query){
                $data = array('success'=>true,'data'=>array('message'=>$message));
                echo json_encode($data);
            }else{
                $data = array('success'=>true,'data'=>array('message'=>'Update faild'));
                echo json_encode($data);
            }



            // Send response
        } catch( Exception $e ) {
            wp_send_json_error( array(
                'message' => 'Server Error!'
            ));
        }
        die();
    }

    /**
     * AJAX: Add Award to Art piece
     * @param $award_id
     * @param $art_id
     */
    static function award_add_to_art() {
        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer( 'opa_award_add_to_art', 'opa_award_add_to_art_nonce' );

        try {

            $award_id =  $_POST["award_id"] ;
            $art_id = intval( $_POST["art_id"] );
            $div_id = intval( $_POST["div_id"] );

            OPA_Model_Art::link_award( $award_id, $art_id,$div_id );

            wp_send_json_success( array(
                'message' => 'Award Updated',
                'award_id' => $award_id
            ));


            // Send response
        } catch( Exception $e ) {
            wp_send_json_error( array(
                'message' => 'Server Error!'
            ));
        }
        die();
    }

    /**
     * Create Show Custom Post type
     */
    static function show_cpt() {
        $labels = array(
            'name'                  => _x( 'OPA Shows', 'Post Type General Name', OPA_DOMAIN ),
            'singular_name'         => _x( 'OPA Show', 'Post Type Singular Name', OPA_DOMAIN ),
            'menu_name'             => __( 'OPA Shows', OPA_DOMAIN ),
            'name_admin_bar'        => __( 'OPA Show', OPA_DOMAIN ),
            'archives'              => __( 'Show Archives', OPA_DOMAIN ),
            'attributes'            => __( 'Show Attributes', OPA_DOMAIN ),
            'parent_item_colon'     => __( 'Parent Show:', OPA_DOMAIN ),
            'all_items'             => __( 'All Shows', OPA_DOMAIN ),
            'add_new_item'          => __( 'Add New Show', OPA_DOMAIN ),
            'add_new'               => __( 'Add New', OPA_DOMAIN ),
            'new_item'              => __( 'New Show', OPA_DOMAIN ),
            'edit_item'             => __( 'Edit Show', OPA_DOMAIN ),
            'update_item'           => __( 'Update Show', OPA_DOMAIN ),
            'view_item'             => __( 'View Show', OPA_DOMAIN ),
            'view_items'            => __( 'View Shows', OPA_DOMAIN ),
            'search_items'          => __( 'Search Show', OPA_DOMAIN ),
            'not_found'             => __( 'Not found', OPA_DOMAIN ),
            'not_found_in_trash'    => __( 'Not found in Trash', OPA_DOMAIN ),
            'featured_image'        => __( 'Featured Image', OPA_DOMAIN ),
            'set_featured_image'    => __( 'Set featured image', OPA_DOMAIN ),
            'remove_featured_image' => __( 'Remove featured image', OPA_DOMAIN ),
            'use_featured_image'    => __( 'Use as featured image', OPA_DOMAIN ),
            'insert_into_item'      => __( 'Add to Show', OPA_DOMAIN ),
            'uploaded_to_this_item' => __( 'Uploaded to this show', OPA_DOMAIN ),
            'items_list'            => __( 'Shows list', OPA_DOMAIN ),
            'items_list_navigation' => __( 'Shows list navigation', OPA_DOMAIN ),
            'filter_items_list'     => __( 'Filter shows list', OPA_DOMAIN ),
        );
        $args = array(
            'label'                 => __( 'OPA Show', OPA_DOMAIN ),
            'description'           => __( 'Post Type Description', OPA_DOMAIN ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor', 'thumbnail' ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'     => 'post_opa_shows',
            'capabilities' => array(
                'read_post' => 'read_post_opa_shows',
                'publish_posts' => 'publish_post_opa_shows',
                'edit_posts' => 'edit_post_opa_shows',
                'edit_others_posts' => 'edit_others_post_opa_shows',
                'delete_posts' => 'delete_post_opa_shows',
                'delete_others_posts' => 'delete_others_post_opa_shows',
                'read_private_posts' => 'read_private_post_opa_shows',
                'edit_post' => 'edit_post_opa_shows',
                'delete_post' => 'delete_post_opa_shows',

            ),
            'menu_icon'   => 'dashicons-cover-image',
        );
        register_post_type( 'opa_show', $args );
    }

    static function body_class( $classes ) {
        if ( array_key_exists( 'judge', $_GET ) && is_singular( 'opa_show' ) ) {
            $classes[] = 'opa-overflow-hidden';
        }
        return $classes;
    }


}
