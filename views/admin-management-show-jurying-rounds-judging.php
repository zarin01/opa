<?php
$show_id = intval( $_GET[ 'show_id' ] );
$round_id = intval( $_GET[ 'round_id' ] );
$score_val = floatval($_GET['score']);
$score_val_operator = $_GET['operator'];
$artist_name_val = $_GET['name'];
$view_type = $_GET['view'];
$filterField = sanitize_text_field($_GET['filter_fields']);
$filterOrder = sanitize_text_field($_GET['filter_order']);
$filterAcceptance = sanitize_text_field($_GET['filter_order_acceptance']);
$round = OPA_Model_Jury_Rounds::get_round( $round_id );
?><!-- Start opa-show-admin-controls-wrapper --><div class="opa-show-admin-controls-wrapper jury-rounds">
<h3><?php echo __( 'Artwork Jurying Status for ', OPA_DOMAIN ) . '"' . $round[ 0 ][ 'jury_round_name' ] . '"'; ?></h3>

<form id="artwork-activate-deactivate-next-round-form" class="opa-artwork-activate-deactivate-next-round-form" method="POST" style="display: none;">
    <input type="hidden" name="active" value="" />
    <input type="hidden" name="art_id" value="" />
    <input type="hidden" name="round_id" value="<?php echo $round_id ?>" />
    <input type="hidden" name="action" value="opa_artwork_activate_deactive_next_round" />
    <?php echo wp_nonce_field( 'opa_artwork_activate_deactive_next_round', 'opa_artwork_activate_deactive_next_round_nonce', true, false ); ?>
    <input type="submit" class="button button-primary button-large" value="<?php _e( 'Submit', OPA_DOMAIN ) ?>">
</form>
<form id="artwork-add-remove-acceptance-form" class="opa-add-remove-acceptance-form" method="POST" style="display: none;">
    <input type="hidden" name="active" value="" />
    <input type="hidden" name="art_id" value="" />
    <input type="hidden" name="action" value="opa_artwork_add_remove_acceptance" />
    <?php echo wp_nonce_field( 'opa_artwork_add_remove_acceptance', 'opa_artwork_add_remove_acceptance_nonce', true, false ); ?>
    <input type="submit" class="button button-primary button-large" value="<?php _e( 'Submit', OPA_DOMAIN ) ?>">
</form>

<?php
$art_duplicate = OPA_Model_Jury_Round_Art::get_dulicate_artwork( $round_id,$score_val.'-'.$score_val_operator );

if(isset($view_type) && $view_type=='duplicates'){
 $art_this_round = OPA_Model_Jury_Round_Art::get_dulicate_artwork( $round_id,$score_val.'-'.$score_val_operator );
}else{
    $art_this_round = OPA_Model_Jury_Round_Art::get_artwork($round_id, $score_val.'-'.$score_val_operator, $artist_name_val, $filterField, $filterOrder);
}



$art_this_round_formatted = [];

$advancing_art_count = 0;

foreach ( $art_this_round as $art ) {
   
  
        $art_this_round_formatted[] = array(
        'Painting Name' => $art['painting_name'],
        'Painting Description' => $art['painting_description'],
        'Painting' => '<img src="' . wp_get_attachment_image_src($art['painting_file_original'],'thumbnail')[0] . '" style="width: 120px;" />',
        'Total Scores' => $art['amount'],
        'Average Score' => $art['average'],
        'Artist Name' => get_member_full_name($art['artist_id'], true),
        'Prepare for Next Round' => (
            intval($art['move_to_next_round']) === 1 ? (
                __('Yes', OPA_DOMAIN) . '<br /><a href="#" style="color: #a00;" data-art-remove-next-round="' . $art['art_id'] . '">' . __('Remove', OPA_DOMAIN) . '</a><br /><input type="checkbox" data-art-checkbox="' . $art['art_id'] . '" name="art_checkbox" value="move">'
            ) : (
                __('No', OPA_DOMAIN) . '<br /><a href="#" data-art-move-next-round="' . $art['art_id'] . '">' . __('Move to Next Round', OPA_DOMAIN) . '</a><br /><input type="checkbox" data-art-checkbox="' . $art['art_id'] . '" name="art_checkbox" value="remove">'
            )
        ),
        'Acceptance' => (
            intval($art['accepted']) === 1 ? (
                __('Accepted!', OPA_DOMAIN) . '<br /><a href="#" style="color: #a00;" data-art-remove-acceptance="' . $art['art_id'] . '">' . __('Remove', OPA_DOMAIN) . '</a>'
            ) : (
                '<a href="#" data-art-add-acceptance="' . $art['art_id'] . '">' . __('Accept Art into Show', OPA_DOMAIN) . '</a>'
            )
        ),
    );
    

        if (intval($art['move_to_next_round']) === 1) {
            $advancing_art_count++;
        }
    
}

echo '<h1 class="advancing-art-count">Advancing Art Pieces: ' . $advancing_art_count . '</h1>';
if($view_type=='duplicates'){
    echo '<h1 class="advancing-art-count">Duplicates art by artist: ' . count($art_duplicate) . ' <a href="javascript:void(0)" id="view_type_all">View All</a></h1>';
}else{
    echo '<h1 class="advancing-art-count">Duplicates art by artist: ' . count($art_duplicate) . ' <a href="javascript:void(0)" id="view_duplicates">View Duplicates</a></h1>';
}

echo '<span class="score-search-wrapper">';
    if($view_type=='duplicate_score'){
        echo '<h1 class="advancing-art-count">Filter Paintings By Score: ' . count($art_this_round) . ' <a href="javascript:void(0)" id="view_type_all_scores">View All</a></h1>';
    }else{
        echo '<h1 class="advancing-art-count">Filter Paintings By Score: ' . count($art_this_round) . ' <a href="javascript:void(0)" id="view_duplicate_score">View Duplicate Scores</a></h1>';
    }
    echo '<br><input id="average-score-filter" value="'.$score_val.'" type="number" maxlength="7" />'; 
    // echo '<select id="average-score-filter" value="'.$score_val.'">';
    // echo '<option '.$selected.' value="">Select Score</option>';
    // for($i=1;$i<8;$i++){
    //     $selected  = '';
    //     if($i==$score_val){
    //         $selected = 'selected=selected';
    //     }
    //     echo '<option '.$selected.' value='.$i.'>'.$i.'</option>';
    // }
    // echo '</select>';
     echo '<select id="average-score-filter-by" default-value="'.$score_val_operator.'">';
   
        $selected  = '';
        if($i==$score_val_operator){ $selected = 'selected=selected';
        }
        ?>
          <option <?php echo ('>'==$score_val_operator)?'selected=selected':''?>  value=">">></option>
            <option <?php echo ('<'==$score_val_operator)?'selected=selected':''?>  value="<"><</option>
            <option <?php echo ('='==$score_val_operator)?'selected=selected':''?>  value="=">=</option>
            <option <?php echo ('<='==$score_val_operator)?'selected=selected':''?>  value="<="><=</option>
            <option <?php echo ('>='==$score_val_operator)?'selected=selected':''?>  value=">=">>=</option>
    <?php
    echo '</select>';
    echo '<input id="artist-name-filter" style="float: left; margin-right: 20px;" placeholder="Artist Last Name" value="' . $artist_name_val . '"/>';
    echo '</span>';

echo OPA_Exports::export_button( __( 'Export CSV Round Judging' ), array(
    'opa_export_type' => 'export-csv-round-judging',
    'show_id' => $show_id,
    'round_id' => $round_id
) );
echo OPA_Exports::export_button( __( 'Export Round Judging Images' ), array(
    'opa_export_type' => 'export-images-round-judging',
    'show_id' => $show_id,
    'round_id' => $round_id
));
echo OPA_Exports::export_button( __( 'Export Accepted Art CSV' ), array(
	'opa_export_type' => 'export-accepted-artwork-csv',
	'show_id' => $show_id
) );
echo "<button class='button button-large' id='select_all_button'>Select All</button>";

echo "</div><!-- End opa-show-admin-controls-wrapper -->";
?>
<div class="sort_columns">
    <?php
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
    $url = "https://";   
    else  
    $url = "http://";   
    $url.= $_SERVER['HTTP_HOST'];   
    $url.= $_SERVER['REQUEST_URI'];    
    ?>
    <form action="<?php echo $url;?>" method="get">
    <input type="hidden" name="page" value="opa">
    <input type="hidden" name="show_id" value="<?php echo $show_id;?>">
    <input type="hidden" name="section" value="jury-rounds">
    <input type="hidden" name="more" value="round-judging">
    <input type="hidden" name="round_id" value="<?php echo $round_id;?>">

    <?php _e('<h1><u>Apply filters to sort columns</u></h1>');?>
    <label for="filter_fields"><?php _e("<strong style='font-size: 15px;'>Select column from the table according to which you want to sort to:</strong>");?></label>
<select name="filter_fields" id="filter_fields" style="margin-left: 10px;">
        <option value="">Select Column</option>
        <option value="painting_name" <?php echo ($_GET['filter_fields']=='painting_name')?'selected=selected':''?>>Painting Name</option>
        <option value="painting_description" <?php echo ($_GET['filter_fields']=='painting_description')?'selected=selected':''?>>Painting Description</option>
        <option value="amount" <?php echo ($_GET['filter_fields']=='amount')?'selected=selected':''?>>Total Scores</option>
        <option value="average" <?php echo ($_GET['filter_fields']=='average')?'selected=selected':''?>>Average Score</option>
        <option value="lastname" <?php echo ($_GET['filter_fields']=='lastname')?'selected=selected':''?>>Artist Last Name</option>
        <option value="accepted" <?php echo ($_GET['filter_fields']=='accepted')?'selected=selected':''?>>Acceptance</option>
    </select>

    <label for="filter_order" id="label_filter_order" style="margin-left:25px;"><?php _e("<strong style='font-size: 15px;'>Select sorting order:</strong>");?></label>
    <select name="filter_order" id="filter_order" style= "margin-left:10px;">
        <option value="">Select Order</option>
        <option value="ASC" <?php echo ($_GET['filter_order']=='ASC')?'selected=selected':''?>>Ascending</option>
        <option value="DESC" <?php echo ($_GET['filter_order']=='DESC')?'selected=selected':''?>>Descending</option>
    </select>

    <button class="button button-large" type="submit" style="margin-left: 10px;"><?php _e("Sort Results");?></button>
    </form>
</div>
<br>
<?php

     echo OPA_Functions::build_table( $art_this_round_formatted, 'hide' );

echo '<a id="move-all-artwork-button" class="button button-large" href="#">Move Selected Artwork</a>';
// Removed so people don't get confused. It removes all artwork from the next round regardless of selection
//echo '<a id="remove-all-artwork-button" class="button button-large" href="#">Remove All Artwork</a>';

function opa_judging_round_scripts() { ?>
    <script type="text/javascript" >
        jQuery(document).ready(function($) {

            // Deactivate Artwork Next Round
            $(document).on('click', '[data-art-remove-next-round]', function(e) {
                e.preventDefault();
                var art_id = parseInt( $(this).attr('data-art-remove-next-round') );
                $('.opa-artwork-activate-deactivate-next-round-form [name="art_id"]').val( art_id );
                $('.opa-artwork-activate-deactivate-next-round-form [name="active"]').val( 0 );
                $('.opa-artwork-activate-deactivate-next-round-form').submit();
            });

            // Activate Artwork Next Round
            $(document).on('click', '[data-art-move-next-round]', function(e) {
                e.preventDefault();
                var art_id = parseInt( $(this).attr('data-art-move-next-round') );
                $('.opa-artwork-activate-deactivate-next-round-form [name="art_id"]').val( art_id );
                $('.opa-artwork-activate-deactivate-next-round-form [name="active"]').val( 1 );
                $('.opa-artwork-activate-deactivate-next-round-form').submit();
            });

            // Remove Winning Artwork
            $(document).on('click', '[data-art-remove-acceptance]', function(e) {
                e.preventDefault();
                var art_id = parseInt( $(this).attr('data-art-remove-acceptance') );
                $('.opa-add-remove-acceptance-form [name="art_id"]').val( art_id );
                $('.opa-add-remove-acceptance-form [name="active"]').val( 0 );
                $('.opa-add-remove-acceptance-form').submit();
            });

            // Add Winning Artwork
            $(document).on('click', '[data-art-add-acceptance]', function(e) {
                e.preventDefault();
                var art_id = parseInt( $(this).attr('data-art-add-acceptance') );
                $('.opa-add-remove-acceptance-form [name="art_id"]').val( art_id );
                $('.opa-add-remove-acceptance-form [name="active"]').val( 1 );
                $('.opa-add-remove-acceptance-form').submit();
            });

            // Click All Artwork With Checkboxes
            $(document).on('click', '#move-all-artwork-button', function(e) {
                e.preventDefault();

                moveArtwork($("[name='art_checkbox']:checked"));
            });

            $(document).on('click', '#remove-all-artwork-button', function(e) {
                e.preventDefault();

                moveArtwork($("[name='art_checkbox'][value='move']"));
            });

            function moveArtwork($artCheckboxes) {
                var movingData = [];
                $artCheckboxes.each(function() {
                    var $artCheckbox = $(this);
                    movingData.push({
                        id: $artCheckbox.attr("data-art-checkbox"),
                        active: $artCheckbox.attr("value") == "move" ? 0 : 1,
                    });
                });
                
                if (movingData.length > 0) {
                    var ids = "";
                    var actives = "";
                    for (var i = 0; i < movingData.length; i++) {
                        if (i == 0) {
                            ids += movingData[i].id;
                            actives += movingData[i].active;
                        }else{
                            ids += "," + movingData[i].id;
                            actives += "," + movingData[i].active;
                        }
                    }

                    $('.opa-artwork-activate-deactivate-next-round-form [name="art_id"]').val( ids );
                    $('.opa-artwork-activate-deactivate-next-round-form [name="active"]').val( actives );
                    $('.opa-artwork-activate-deactivate-next-round-form').submit();
                }
            }

            // Enable or Disable Art
            $(document).on('submit', '.opa-artwork-activate-deactivate-next-round-form, .opa-add-remove-acceptance-form', function(e) {
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
                    // Removed because 99.99% of the time there will be no useful error
                    //alert( "Server error.  Please try again later." );
                });
            });

            // Filter art by average score
            $("#view_type_all_scores").click(function(){
                var href = new URL(window.location.href);
                href.searchParams.delete('view');
     
        window.location.href = href.toString();   
            })
            $("#view_duplicate_score").click(function(){
                var href = new URL(window.location.href);
                href.searchParams.set('view', 'duplicate_score');

        window.location.href = href.toString();   
            })

            // Filter duplicate art
            $("#view_type_all").click(function(){
                var href = new URL(window.location.href);
                href.searchParams.delete('view');
     
       window.location.href = href.toString();   
            })
            $("#view_duplicates").click(function(){
                var href = new URL(window.location.href);
                href.searchParams.set('view', 'duplicates');
     
       window.location.href = href.toString();   
            })

            $('#average-score-filter, #average-score-filter-by').change(function() {
                $("input[name='art_checkbox']").removeAttr("checked")
                var searchScore = $('#average-score-filter').val();
                var score_operator = $("#average-score-filter-by").val();
                var href = new URL(window.location.href);
       href.searchParams.set('score', searchScore);
       href.searchParams.set('operator', score_operator);
       window.location.href = href.toString();
      
                // $('#average-score-filter-error').html("");
                // $('.opa-table__tr').each(function() {
                //     var averageScore = parseFloat($(this).children().eq(4).html());
                //     if (averageScore < searchScore) $(this).hide();
                //     else $(this).show();
                // });
            });
            $('#artist-name-filter').change(function() {
                $("input[name='art_checkbox']").removeAttr("checked")
                var searchName = $('#artist-name-filter').val();
                var href = new URL(window.location.href);
                href.searchParams.set('name', searchName);
                window.location.href = href.toString();
            });
            $("#select_all_button").click(function(){
                $("input[name='art_checkbox']").attr("checked",'checked')
            })
        });
    </script> <?php
}
add_action( 'admin_footer', 'opa_judging_round_scripts' );
