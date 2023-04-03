<?php
global $wpdb;
$show_id = intval( $_GET[ 'show_id' ] );

$totalPainting = $wpdb->get_results('SELECT COUNT(show_id) AS total from '.$wpdb->prefix.'opa_art where show_id='.$show_id);
$total =  $totalPainting[0]->total;

?><h3><?php echo __( 'Search for Jurors ', OPA_DOMAIN ); ?></h3>

<!-- Autocomplete Look for Juror -->
<form id="juror-search-form" class="opa-juror-search-form" method="POST">
    <label for="opa-search"></label>
    <input type="text" name="search" id="opa-search" />
    <input type="hidden" name="action" value="opa_juror_search" />
	<?php echo wp_nonce_field( 'opa_juror_search', 'opa_juror_search_nonce', true, false ); ?>
    <input type="submit" class="button button-primary button-large" value="<?php _e( 'Search', OPA_DOMAIN ) ?>">
</form>

<div class="juror-search-results">
    <ul class="juror-search-results__results">
        <li class="juror-search-results__noresult"><?php _e( 'Use Search to Find Jurors', OPA_DOMAIN ) ?></li>
    </ul>
    <h3><span><?php echo __( 'Click on a Juror to add to show', OPA_DOMAIN ); ?></span></h3>
</div>

<form id="juror-add-form" class="opa-juror-add-form" method="POST" style="display: none;">
    <input type="hidden" name="show_id" value="<?php echo $show_id ?>" />
    <input type="hidden" name="juror_id" value="" />
    <input type="hidden" name="action" value="opa_juror_add_to_show" />
	<?php echo wp_nonce_field( 'opa_juror_add_to_show', 'opa_juror_add_to_show_nonce', true, false ); ?>
    <input type="submit" class="button button-primary button-large" value="<?php _e( 'Submit', OPA_DOMAIN ) ?>">
</form>

<!-- Delete Juror task - August 5 - Ryan -->
<form id="juror-delete-form" class="opa-juror-delete-form" method="POST" style="display: none;">
    <input type="hidden" name="show_id" value="<?php echo $show_id ?>" />
    <input type="hidden" id="juror_id" name="juror_id" value="" />
    <input type="hidden" name="action" value="opa_juror_delete" />
    <?php echo wp_nonce_field( 'opa_juror_delete', 'opa_juror_delete_nonce', true, false ); ?>
    <input type="submit" class="button button-primary button-large" value="<?php _e( 'Submit', OPA_DOMAIN ) ?>">
</form>
<!-- Delete Juror task - August 5 - Ryan -->

<br><br>
<h3>Total Number Of Paintings (<?php echo $total;?>)
<br><br>

<h3><?php echo __( 'Jurors Connected to this Show', OPA_DOMAIN ); ?></h3><?php

echo OPA_Exports::export_button( __( 'Export Jurors' ), array(
	'opa_export_type' => 'export-jurors-in-show-csv',
	'show_id' => $show_id,
) );

$jurors = OPA_Model_Show::get_jurors( $show_id );

// Delete Juror task - August 5 - Ryan
$table = $wpdb->prefix.'opa_jurors';
$jurors_formatted = [];
foreach ( $jurors as $juror ) {
    $jurorID = $juror['ID'];
    $listStatus = $wpdb->get_results("SELECT show_in_list FROM $table WHERE juror_id = $jurorID AND show_id = $show_id ");
    if($listStatus[0]->show_in_list == 1)
    {
    $jurors_formatted[] = array(
    'ID' => $juror['ID'],
    'First Name' => $juror['First Name'],
    'Last Name' => $juror['Last Name'],
    'Email' => $juror['Email'],
    'Address' => $juror['Address'],
    'City' => $juror['City'],
    'State' => $juror['State'],
    'Zip' => $juror['Zip'],
    'Delete' => '<a href="#" class="delete_juror" data-delete-juror="'. $juror['ID'] . '" juror_id="'. $juror['ID'] .'">' . __( 'Delete Juror', OPA_DOMAIN ) . '</a> '
    );
    }
}

// Delete Juror task - August 5 - Ryan

echo OPA_Functions::build_table( $jurors_formatted, 'judging-detail', __( 'See Judging', OPA_DOMAIN ) );

function opa_juror_scripts() { ?>
    <script type="text/javascript" >
        jQuery(document).ready(function($) {

            var $juror_results = $('.juror-search-results ul');

            // Juror Detail
            jQuery(document).ready(function($) {
                $(document).on('click', '.opa-table__expand--judging-detail', function() {
                    var user_id = $(this).closest('tr').find('td:first-child').text();
                    window.location.href = window.location.href + "&more=juror-detail&user_id=" + user_id;
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
                var $juror_add_form = $('.opa-juror-add-form');
                var juror_id = parseInt( $(this).attr('data-add-juror') );
                $juror_add_form.find('[name="juror_id"]').val( juror_id );
                $juror_add_form.submit();
            });

            // Add Juror to Show - form
            $(document).on('submit', '.opa-juror-add-form', function(e) {
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
                var juror_id = $(this).attr('juror_id');
                $('.opa-juror-delete-form [name="juror_id"]').val( juror_id );
                var formData = new FormData($('#juror-delete-form').get(0));

                // Submit the form to the server
                $.ajax({
                    url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ) ?>',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false
                }).then((result, textStatus, jqXHR) => {                    
                    if ( result.status === true ) {
                        console.log(result);
                        location.reload();
                    } else if ( result.status === false ) {
                        location.reload();
                    }
                }).fail((jqXHR) => {
                    console.log( "Server error.  Please try again later." );
                });
            });
        });
// Delete Juror task - August 5 - Ryan
    </script> <?php
}
add_action( 'admin_footer', 'opa_juror_scripts' );
