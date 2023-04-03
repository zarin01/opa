<?php
/*
Plugin Name: Opa
Plugin URI: https://www.steckinsights.com/
Description: Custom Plugin for Oil Painters of America
Version: 1.0
Author: Travis Hoglund
Author URI: https://www.travishoglund.com
License: A "Slug" license name e.g. GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

define( 'OPA_DEBUG', false );
define( 'OPA_VERSION', '1.0' );
define( 'OPA_DOMAIN', 'oil-painters-america' );
define( 'OPA_PATH', plugin_dir_path( __FILE__ ) );
if (DIRECTORY_SEPARATOR === '\\') {
	define( 'OPA_RELATIVE_WP_PATH', str_replace( '\\', '/', str_replace( str_replace('/', '\\', ABSPATH), '/', OPA_PATH ) ) );
}else{
	define( 'OPA_RELATIVE_WP_PATH', str_replace( ABSPATH, '/', OPA_PATH ) );
}
define( 'OPA_BASENAME', plugin_basename( __FILE__ ) );

if($_SERVER['HTTP_HOST']=='www.oilpaintersofamerica.com'){
    // Live stripe keys
    define( 'OPA_STRIPE_PUBLISHABLE_KEY', 'pk_live_51JN5rgEzwBT44sHCBOcL3iRlIfIEchXBcaWjVKWaB83L5AhVAs0FVzlYPxle1mMim8hd1JRtwHE2JAHXlvYhBcOs008bxJKXfo' );
    define( 'OPA_STRIPE_SECRET_KEY', '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' );
} else {
    // Test stripe keys
    define( 'OPA_STRIPE_PUBLISHABLE_KEY', 'pk_test_51JN5rgEzwBT44sHCfa7KLN3kN477PLX8QhAhzAbZfMA34mLo7uHjuqmgziyc7kdx6QugWl60Wj5uvDtwoBuPfL4r00iZ2vAiHQ' );
    define( 'OPA_STRIPE_SECRET_KEY', 'sk_test_51JN5rgEzwBT44sHCBEz8kGWoLivDPcn6JjRkdOXOCZMGljgq2oivtFAzl59i7yqCTWdEMvX7dQejw1dCPfB9LDty00EpLvt5Kp' );
}


define( 'OPA_REGISTRATION_FEE', get_option( 'opa_registration_fee' ) ?: 70.00 );
define( 'OPA_SHOW_REGISTRATION_FEE', array( 30, 20, 10 ));
define( 'OPA_SHOW_ART_PAGE_LENGTH', 20);

require_once( dirname( __FILE__ ) . '/classes/class-opa.php' );

/**
 * Temporary Debug Functionality
 * @param $data
 */
//add_action('wp_head', 'hide_php_errors');
function hide_php_errors() {
	echo '<style>font, .xe-warning { display: none !important; }</style>';
}
function console_log( $data ){
	echo '<script>';
	echo 'console.log('. json_encode( $data ) .')';
	echo '</script>';
}

function write_log($log) {
	if (true === WP_DEBUG) {
		if (is_array($log) || is_object($log)) {
			error_log(print_r($log, true));
		} else {
			error_log($log);
		}
	}
}

function is_active_opa_member()
{
    $user = wp_get_current_user();
    $user_id = $user->ID;

    $all_memberships = wc_memberships_get_user_memberships($user_id);

    if(is_page_template('page-member-choice.php')) {
        // Show Variables
        $show_object = get_field('selected_show');
        // Check if show has been selected in page template
        if (is_null($show_object) || $show_object == '') {
            // Display notice if no show has been selected
            console_log('Please select a show in the page settings.');
        } else {
            // Set show post variables
            foreach ($show_object as $show_key => $show_value) {
                global $show_id;
                $show_id = $show_value->ID;
                $show_title = $show_value->post_title;
                $show_link = get_permalink($show_id);
            }
            console_log('The current selected_show field id in our page template is: ' . $show_id);
        }
    } else {
        $show_id = get_the_ID();
    }
    $show_membership = get_field_object('opa_show_membership', $show_id);

    $show_membership_value = $show_membership['value'];
    $show_membership_label = '';
    foreach($show_membership_value as $show_membership_names){
        $show_membership_label .= $show_membership['choices'][$show_membership_names]." ";
    }
    //$show_membership_label = $show_membership['choices'][$show_membership_value];

    $secondary_membership_data = get_secondary_membership_data($user_id);
    $secondary_membership_grants_free_entry = get_secondary_membership_free_entry_into_show($secondary_membership_data, $show_id);

    $current_membership_name = $all_memberships[0]->get_plan()->name;

    $current_membership_slug = '';
    $current_membership_status = '';
    $current_membership_end_date_year = 0;
    $next_year = intval(date('Y')) + 1;
    foreach ($all_memberships as $plan) {
        $current_membership_slug = $plan->get_plan()->slug;
        $current_membership_status = $plan->get_status();
        $current_membership_end_date_year = intval(explode('-', $plan->get_end_date())[0]);
    }

    if (count($all_memberships) <= 0 && $current_membership_status != "active" && $current_membership_status != "delayed" && !$secondary_membership_data["can_enter_shows_with_any_membership"]) {
        echo '<div class="opa-show-error"><p>You are not eligible for this show because you need a membership. <a href="https://www.oilpaintersofamerica.com/membership/">View Memberships.</a></p></div>';
        return false;
    }

    $memberships = wc_memberships_get_user_memberships($user_id);
    $allPlans = array();
    foreach ($memberships as $plan) {
        $allPlans[] = $plan->plan->slug;
    }
    $active_student_membership = wc_memberships_is_user_active_member($user->ID, 'student-membership') || wc_memberships_is_user_active_member($user->ID, 'student-non-member');

    // @TODO Simplify this statement to be more "beautiful"
//    echo "show_membership_value: ";
//    var_dump($show_membership_value);
//
//    echo "allPlans: ";
//    var_dump($allPlans);
//
//    echo "active_student_membership: ";
//    var_dump($active_student_membership);

    $showMembership = true;
    if ($active_student_membership) {
        $showMembership  =  false;
    }

    foreach($show_membership_value as $membership) {

        if ($membership == "all" && wc_memberships_is_user_active_member()) {
            $showMembership  =  false;
        } elseif (in_array($membership, $allPlans)) {
            $showMembership  =  false;
        }
       }

if ($show_membership_value[0] == "all") {
$show_membership_label = "membership";
}
    if($showMembership){
        echo '<p>' . __('You must have an active', OPA_DOMAIN) . ' ' . $show_membership_label . ' ' . __('to register for this show.', OPA_DOMAIN) . ' ' . '<a href="/membership/">' . __('Please upgrade your membership here.', OPA_DOMAIN) . '</a>' . '</p>';
        return false;
    }else{
        return true;
    }
}



function wpse_add_custom_meta_box_2()
{
    add_meta_box('custom_meta_box', 'Links', 'post_slug_meta_box2', 'opa_show', 'normal', 'high', array('__back_compat_meta_box' => true));
}
add_action('add_meta_boxes', 'wpse_add_custom_meta_box_2');
function post_slug_meta_box2( $post ) {

    /** This filter is documented in wp-admin/edit-tag-form.php */
   // $editable_slug = apply_filters( 'editable_slug', $post->post_name, $post );

    $share_link = site_url().'/awardees?show_id='.$post->ID;
    $preview_award  = site_url().'/awardees?show_id='.$post->ID.'&preview=true';
    $preview_gallery = site_url().'/show-gallery/?show='.$post->ID;
    $preview_acceptance_list = site_url().'/show-gallery/?show='.$post->ID.'&display=list';

    ?>
    <h4>Preview</h4>
        <div>
   <strong>Preview Awards: </strong> <a target="_blank" href="<?php echo $preview_award?>"><?php echo $preview_award;?></a>
        </div>
    <div>
    <strong>Preview Gallery: </strong> <a target="_blank" href="<?php echo $preview_gallery?>"><?php echo $preview_gallery;?></a>
    </div>
    <div>
        <strong>Preview Acceptance List: </strong> <a target="_blank" href="<?php echo $preview_acceptance_list?>"><?php echo $preview_acceptance_list;?></a>
    </div>

    <div>
        <h4>Share</h4>
        <strong>Sharable Awards Link: </strong> <a target="_blank" href="<?php echo $share_link?>"><?php echo $share_link;?></a>
    </div>
    <?php
}