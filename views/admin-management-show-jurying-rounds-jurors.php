<?php
global $wpdb;
$show_id = intval( $_GET[ 'show_id' ] );
$round_id = intval( $_GET[ 'round_id' ] );
$round = OPA_Model_Jury_Rounds::get_round( $round_id );

?><h3><?php echo __( 'Search for Jurors for ', OPA_DOMAIN ) . '"' . $round[ 0 ][ 'jury_round_name' ] . '"'; ?></h3>

    <!-- Autocomplete Look for Juror -->
    <form id="juror-search-form" class="opa-juror-search-form" method="POST">
        <label for="opa-search"></label>
        <input type="text" name="search" id="opa-search" />
        <input type="hidden" name="show_id" value="<?php echo $show_id ?>" />
        <input type="hidden" name="round_id" value="<?php echo $round_id ?>" />
        <input type="hidden" name="action" value="opa_juror_search" />
		<?php echo wp_nonce_field( 'opa_juror_search', 'opa_juror_search_nonce', true, false ); ?>
        <input type="submit" class="button button-primary button-large" value="<?php _e( 'Search', OPA_DOMAIN ) ?>">
    </form>

    <div class="juror-search-results">
        <ul class="juror-search-results__results">
            <li class="juror-search-results__noresult"><?php _e( 'Use Search to Find Jurors', OPA_DOMAIN ) ?></li>
        </ul>
        <h3><span><?php echo __( 'Click on a Juror to add to this round', OPA_DOMAIN ); ?></span></h3>
    </div>

    <form id="juror-add-round-form" class="opa-juror-add-round-form" method="POST" style="display: none;">
        <input type="hidden" name="show_id" value="<?php echo $show_id ?>" />
        <input type="hidden" name="round_id" value="<?php echo $round_id ?>" />
        <input type="hidden" name="juror_id" value="" />
        <input type="hidden" name="action" value="opa_juror_add_to_round" />
		<?php echo wp_nonce_field( 'opa_juror_add_to_round', 'opa_juror_add_to_round_nonce', true, false ); ?>
        <input type="submit" class="button button-primary button-large" value="<?php _e( 'Submit', OPA_DOMAIN ) ?>">
    </form> 
    <form id="juror-activate-deactivate-form" class="opa-juror-activate-deactivate-form" method="POST" style="display: none;">
        <input type="hidden" name="active" value="" />
        <input type="hidden" name="juror_id" value="" />
        <input type="hidden" name="round_id" value="<?php echo $round_id ?>" />
        <input type="hidden" name="action" value="opa_juror_activate_deactivate" />
		<?php echo wp_nonce_field( 'opa_juror_activate_deactivate', 'opa_juror_activate_deactivate_nonce', true, false ); ?>
        <input type="submit" class="button button-primary button-large" value="<?php _e( 'Submit', OPA_DOMAIN ) ?>">
    </form>

    <!-- Delete Juror task - August 5 - Ryan -->
    <form id="juror-delete-form-int" class="opa-juror-delete-form-int" method="POST" style="display: none;">
        <input type="hidden" name="round_id" value="<?php echo $round_id ?>" />
        <input type="hidden" id="juror_id_int" name="juror_id_int" value="" />
        <input type="hidden" name="action" value="opa_juror_delete_int" />
        <?php echo wp_nonce_field( 'opa_juror_delete_int', 'opa_juror_delete_int_nonce', true, false ); ?>
        <input type="submit" class="button button-primary button-large" value="<?php _e( 'Submit', OPA_DOMAIN ) ?>">
    </form>
    <!-- Delete Juror task - August 5 - Ryan -->

    <br><br>
    <h3><?php echo __( 'Jurors Connected to this Round', OPA_DOMAIN ); ?></h3><?php

//echo OPA_Exports::export_button( __( 'Export Jurors' ), array(
//	'opa_export_type' => 'export-jurors-in-show-csv',
//	'show_id' => $show_id,
//) );

$total_art = count( OPA_Model_Jury_Round_Art::get_artwork( $round_id,'' ) );
$get_round_id = $wpdb->get_results("select * from ".$wpdb->prefix."opa_jury_rounds where show_id=".$show_id);
if(!$round_id){
    $round_id = $get_round_id[0]->id;
}
$jurors = OPA_Model_Jury_Round_Jurors::get_jurors($round_id );

$jurors_formatted = [];
$table = $wpdb->prefix.'opa_jury_round_jurors';
 $tablescores = $wpdb->prefix.'opa_jury_scores';
foreach ( $jurors as $juror ) {
    $jurorID = $juror['ID'];
    $listStatus = $wpdb->get_results("SELECT show_in_list FROM $table WHERE juror_id = $jurorID AND jury_round_id = $round_id");
    if($listStatus[0]->show_in_list == 1)
    {
    $juror_status =$wpdb->update($tablescores,array('count_scores_for_average' => 1),array('juror_id' => $jurorID, 'jury_round_id' => $round_id));
    $jurors_formatted[] = array (
        'First Name' => $juror['First Name'],
        'Last Name' => $juror['Last Name'],
        'Email' => $juror['Email'],
        'Status' => (
            intval( $juror['Status'] ) === 1 ? (
                __( 'Enabled', OPA_DOMAIN ) . '<br /><a href="#" style="color: #a00;" data-deactivate-juror="' . $juror['ID'] . '">' . __( 'Deactivate', OPA_DOMAIN ) . '</a>'
            ) : (
                __( 'Disabled', OPA_DOMAIN ) . '<br /><a href="#" data-activate-juror="' . $juror['ID'] . '">' . __( 'Activate', OPA_DOMAIN ) . '</a>'
            )
        ),
        'Completions' => ( $juror['Completions'] ),
        'Completion Percent' => ( $juror['Completions'] / max( $total_art, 1 ) * 100 ) . '%',
        'Average Score' => $juror['Average Score'],
	    'Scoring' => (
            '<a href="' . OPA_Menu::helper_url(array(
	            'show_id' => $show_id,
                'section' => 'jurors',
                'more' => 'juror-detail',
                'user_id' => $juror['ID']
            )) . '">View Scores</a>'
        ),
        'Delete' => '<a href="#" class="delete_juror" data-delete-juror="'. $juror['ID'] . '" juror_id_int="'. $juror['ID'] .'">' . __( 'Delete Juror', OPA_DOMAIN ) . '</a>'
    );
}
else
{
    $juror_status =$wpdb->update($tablescores,array('count_scores_for_average' => 0),array('juror_id' => $jurorID, 'jury_round_id' => $round_id));
}
}

// show_id=42&section=jurors&more=juror-detail&user_id=1

echo OPA_Functions::build_table( $jurors_formatted, 'hide' );

function opa_juror_round_scripts() { ?>
    <script type="text/javascript" >
        jQuery(document).ready(function($) {

            var $juror_results = $('.juror-search-results ul');

            // Deactivate Juror
            $(document).on('click', '[data-deactivate-juror]', function(e) {
                e.preventDefault();
                var art_id = parseInt( $(this).attr('data-deactivate-juror') );
                $('.opa-juror-activate-deactivate-form [name="juror_id"]').val( art_id );
                $('.opa-juror-activate-deactivate-form [name="active"]').val( 0 );
                $('.opa-juror-activate-deactivate-form').submit();
            });

            // Activate Juror
            $(document).on('click', '[data-activate-juror]', function(e) {
                e.preventDefault();
                var art_id = parseInt( $(this).attr('data-activate-juror') );
                $('.opa-juror-activate-deactivate-form [name="juror_id"]').val( art_id );
                $('.opa-juror-activate-deactivate-form [name="active"]').val( 1 );
                $('.opa-juror-activate-deactivate-form').submit();
            });

            // Enable or Disable Juror
            $(document).on('submit', '.opa-juror-activate-deactivate-form', function(e) {
                e.preventDefault();

                var formData = new FormData($(this).get(0));

                // Submit the form to the server
                $.ajax({
                    url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ) ?>',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false
                }).then((result, textStatus, jqXHR) => {
                    if ( result.success === true ) {
                        location.reload();
                    } else if ( result.success === false ) {
                        alert( result.data.message );
                    }
                }).fail((jqXHR) => {
                    alert( "Server error.  Please try again later." );
                });
            });

            // Search for Juror
            $(document).on('submit', '.opa-juror-search-form', function(e) {
                e.preventDefault();

                var formData = new FormData($(this).get(0));

                // Submit the form to the server
                $.ajax({
                    url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ) ?>',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false
                }).then((result, textStatus, jqXHR) => {
                    if ( result.success === true ) {
                        $juror_results.empty();
                        if ( result.data && result.data.users && result.data.users.length > 0 ) {
                            for ( var i = 0; i < result.data.users.length; i++ ) {
                                $juror_results.append(
                                    '<li class="juror-search-results__result" data-add-juror="' + result.data.users[i].data.ID + '">' + result.data.users[i].data.display_name + ' - ' + result.data.users[i].data.user_email + '</li>'
                                );
                            }
                            $(".juror-search-results__result").on("click",function(){
                                location.reload();
                            });
                        } else {
                            $juror_results.append(
                                '<li class="juror-search-results__noresult"><?php __( 'No results found', OPA_DOMAIN ) ?></li>'
                            );
                            $(".juror-search-results__result").on("click",function(){
                                location.reload();
                            });
                        }
                    } else if ( result.success === false ) {
                        alert( result.data.message );
                    }
                }).fail((jqXHR) => {
                    alert( "Server error.  Please try again later." );
                });
            });

            // Add Juror to Show - click
            $(document).on('click', '[data-add-juror]', function(e) {
                e.stopImmediatePropagation();
                e.preventDefault();
                var $juror_add_form = $('.opa-juror-add-round-form');
                var juror_id = parseInt( $(this).attr('data-add-juror') );
                $juror_add_form.find('[name="juror_id"]').val( juror_id );
                $juror_add_form.submit();
            });

            // Add Juror to Show - form
            $(document).on('submit', '.opa-juror-add-round-form', function(e) {
                e.preventDefault();

                var formData = new FormData($(this).get(0));

                // Submit the form to the server
                $.ajax({
                    url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ) ?>',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false
                }).then((result, textStatus, jqXHR) => {
                    if ( result.success === true ) {
                        location.reload();
                    } else if ( result.success === false ) {
                        alert( result.data.message );
                    }
                }).fail((jqXHR) => {
                    alert( "Server error.  Please try again later." );
                });
            });

            // Delete Juror task - August 5 - Ryan
            $(document).on('click', '.delete_juror', function(e) {
                e.preventDefault();
                if (confirm("Are you Sure ?") == false) {
                    return;
                }
                var juror_id = $(this).attr('juror_id_int');
                $('.opa-juror-delete-form-int [name="juror_id_int"]').val( juror_id );
                var formData = new FormData($('#juror-delete-form-int').get(0));

                // Submit the form to the server
                $.ajax({
                    url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ) ?>',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false
                }).then((result, textStatus, jqXHR) => {
                    if ( result.status === true ) {
                        location.reload();
                    } else if ( result.status === false ) {
                        location.reload();
                    }
                }).fail((jqXHR) => {
                    console.log( "Server error.  Please try again later." );
                });
            });

        });

    </script> <?php
}
add_action( 'admin_footer', 'opa_juror_round_scripts' );
