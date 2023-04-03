<?php
global $wpdb;
$show_id = intval( $_GET[ 'show_id' ] );

?><h3><?php echo __( 'Create Jury Round', OPA_DOMAIN ); ?></h3>
    <form id="jury-round-add-form" class="opa-jury-round-add-form" method="POST">
        <label for="opa-round-name">Round Name</label>
        <input type="text" id="opa-round-name" name="round_name" />
        <input type="hidden" name="show_id" value="<?php echo $show_id ?>" />
        <input type="hidden" name="action" value="opa_jury_round_add_to_show" />
        <?php echo wp_nonce_field( 'opa_jury_round_add_to_show', 'opa_jury_round_add_to_show_nonce', true, false ); ?>
        <input type="submit" class="button button-primary button-large" value="<?php _e( 'Submit', OPA_DOMAIN ) ?>">
    </form>
    <form id="jury-round-close-form" class="opa-jury-round-close-form opa-jury-round-close-delete-form" method="POST" style="display: none;">
        <input type="hidden" name="round_id" value="" />
        <input type="hidden" name="action" value="opa_jury_round_close" />
        <?php echo wp_nonce_field( 'opa_jury_round_close', 'opa_jury_round_close_nonce', true, false ); ?>
        <input type="submit" class="button button-primary button-large" value="<?php _e( 'Submit', OPA_DOMAIN ) ?>">
    </form>

    <!-- Delete Jury round task -->
    <form id="jury-round-delete-form" class="opa-jury-round-delete-form" method="POST" style="display: none;">
        <input type="hidden" id="round_del_id" name="round_del_id" value="" />
        <input type="hidden" name="action" value="opa_jury_round_delete" />
        <?php echo wp_nonce_field( 'opa_jury_round_delete', 'opa_jury_round_delete_nonce', true, false ); ?>
        <input type="submit" class="button button-primary button-large" value="<?php _e( 'Submit', OPA_DOMAIN ) ?>">
    </form>
    <!-- Delete Jury round task-->

    <br /><br />

    <h3><?php echo __( 'Current Jury Rounds ', OPA_DOMAIN ); ?></h3><?php

$jury_rounds = OPA_Model_Show::get_jury_rounds( $show_id );
$jury_rounds_formatted = [];
$table = $wpdb->prefix.'opa_jury_rounds';
foreach ( $jury_rounds as $jury_round ) {
    global $wpdb;
    $roundID = $jury_round['id'];
    $listStatus = $wpdb->get_results("SELECT show_in_list FROM $table WHERE id = $roundID AND show_id = $show_id ");
    if($listStatus[0]->show_in_list == 1)
    {
    $jury_rounds_formatted[] = array(
        'ID' => $jury_round['id'],
        'Round Name' => $jury_round['jury_round_name'],
        'Status' => (
        intval( $jury_round['jury_round_active'] ) === 1 ? (
        __( 'Active', OPA_DOMAIN )
        ) : (
        __( 'Closed', OPA_DOMAIN )
        )
        ),
        'Art in Round' => '<a href="#" class="jury-round-more-art">' . __( 'View', OPA_DOMAIN ) .  '</a>',
        'Jurors in Round' => '<a href="#" class="jury-round-more-jurors">' . __( 'Jurors', OPA_DOMAIN ) .  '</a>',
        'Round Overview' => '<a href="#" class="jury-round-more-judging">' . __( 'Artwork Rankings', OPA_DOMAIN ) .  '</a>',
        'Finalize' => (
        intval( $jury_round['jury_round_active'] ) === 1 ? (
            '<a href="#" style="color: #a00;" data-close-round="' . $jury_round['id'] . '">' . __( 'Close Round', OPA_DOMAIN ) . '</a>'
        ) : '-'
        ),

        'Delete' => '<a href="#" class="delete_jury_round" data-close-delete-round="' . $jury_round['id'] . '"  round_id="'. $jury_round['id'] .'" data-delete-round="'. $jury_round['id'] . '">' . __( 'Delete Round', OPA_DOMAIN ) . '</a>'

    );
    }
}

echo OPA_Functions::build_table( $jury_rounds_formatted, 'hide' );


function opa_jury_rounds_scripts() { ?>
    <script type="text/javascript" >
        jQuery(document).ready(function($) {
            //elete jury round
            $(document).on('click', '.delete_jury_round', function(e) {
                e.preventDefault();
                if (confirm("Are you Sure ?") == false) {
                    e.preventDefault();
                    return;
                }

                var round_id = $(this).attr('data-close-delete-round');
                $('.opa-jury-round-close-delete-form [name="round_id"]').val( round_id );
                $('.opa-jury-round-close-delete-form').submit();


                var round_id = $(this).attr('round_id');
                $('.opa-jury-round-delete-form [name="round_del_id"]').val( round_id );
                var formData = new FormData($('#jury-round-delete-form').get(0));

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
            // Art - Add it to the round
            $(document).on('click', '.jury-round-more-art', function(e) {
                e.preventDefault();
                var round_id = $(this).closest('tr').find('td:first-child').text();
                window.location.href = window.location.href + "&more=round-art&round_id=" + round_id;
            });

            // Jurors - Add them to the round
            $(document).on('click', '.jury-round-more-jurors', function(e) {
                e.preventDefault();
                var round_id = $(this).closest('tr').find('td:first-child').text();
                window.location.href = window.location.href + "&more=round-jurors&round_id=" + round_id;
            });

            // Judging - Make admin decisions
            $(document).on('click', '.jury-round-more-judging', function(e) {
                e.preventDefault();
                var round_id = $(this).closest('tr').find('td:first-child').text();
                window.location.href = window.location.href + "&more=round-judging&round_id=" + round_id;
            });

            // Judging - Close the Round
            $(document).on('click', '[data-close-round]', function(e) {
                e.preventDefault();
                var round_id = $(this).attr('data-close-round');
                $('.opa-jury-round-close-form [name="round_id"]').val( round_id );
                $('.opa-jury-round-close-form').submit();
            });

            // Add Jury Round
            $(document).on('submit', '.opa-jury-round-add-form, .opa-jury-round-close-form', function(e) {
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

// Delete Jury round task - August 4 - Ryan
            
        });

        // Delete Jury round task - August 4 - Ryan
    </script> <?php
}
add_action( 'admin_footer', 'opa_jury_rounds_scripts' );
