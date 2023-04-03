<?php
$show_id = intval( $_GET[ 'show_id' ] );
$user_id = intval( $_GET[ 'user_id' ] );

$juror = OPA_Model_Jurors::get_juror( $user_id );

?><h3><?php echo __( 'Juror Detail', OPA_DOMAIN ); ?>: <?php echo $juror[0]['First Name'] ?> <?php echo $juror[0]['Last Name'] ?></h3>
<hr />
<br />

<?php
    $rounds = OPA_Model_Show::get_jury_rounds( $show_id );
    foreach( $rounds as $round ) {
	    $scores = OPA_Model_Jurors::get_round_scores( $user_id, $round['id'] );
	    $scores_formatted = [];
	    foreach ( $scores as $score ) {
	        $scores_formatted[] = array(
                'ID' => $score['art_id'],
                'Painting Name' => $score['painting_name'],
                'Painting Description' => $score['painting_description'],
                'Painting' => '<img src="' .wp_get_attachment_image_url($score['painting_file_original'], 'thumbnail', 'loading="lazy"') . '" style="width: 100px;" />',
                'Score' => $score['score'],
            );
        }
	    printf(
            '<h3>%s: %s</h3>',
		    __( 'Round', OPA_DOMAIN ),
		    $round['jury_round_name']
        );
	    echo OPA_Functions::build_table( $scores_formatted, 'hide' );
	    echo '<br /><br />';
    }
?>
