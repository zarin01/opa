<?php
$juror                  = wp_get_current_user();
$show_id               = isset( $atts ) && is_array( $atts ) && array_key_exists( 'show', $atts ) ? $atts['show'] : get_the_ID();
$current_jury_round_id = OPA_Model_Show::get_current_jury_round_id( $show_id );
$juror_has_access       = OPA_Model_Jurors::has_round_access( $current_jury_round_id, $juror->ID );
$juror_membership_active = OPA_Registration::user_is_paying_member();
$round_artwork = OPA_Model_Jury_Scores::get_artwork( $current_jury_round_id, $juror->ID );
$is_online = is_online_showcase($show_id);

if ( $juror_has_access) {

    echo '<div id="judge-gallery" class="opa-judge-gallery">';
        echo '<div class="opa-judge__top-bar">';
            // Jump To button disabled
            /*
            echo '<div class="opa-judge__art-auto-advance-wrapper" style="left: 0; position: absolute; padding: 0px 0px 0px 20px;">';
                echo '<button class="opa-judge__art-jump-to-button reset-this opa-judge-button" style="">Jump to</button>';
                echo '<input step="1" type="number" value="0" class="opa-judge__art-jump-to-number reset-this" style="margin-left: 10px; width: 55px; font-size: 21px;"/>';
                echo ' of ' . '<div class="opa-judge__art-jump-to-number-count">' . count($round_artwork) . '</div>';
            echo '</div>';*/
            /*
            echo '<div class="opa-judge__art-title">';
                echo 'Judgement View';
            echo '</div>';*/
            echo '<div class="opa-judge__close-button opa-judge-button">';
                echo '<a href="' . get_permalink( $show_id ) . '" class="opa-judge-close__link">Exit</a>';
            echo '</div>';
            echo '<div class="opa-judge__completion">0%</div>';
        echo '</div>';

        echo '<div class="opa-judge-gallery-filter-wrapper">';
            if (is_online_showcase($show_id)) {
                echo '<label>Division:</label>';
                echo '<select class="opa-judge__art-division-filter opa-judge-filter" style="width: 200px;">';
                    echo '<option value="none" selected>All</option>';
                    echo '<option value="associate-membership">Associate</option>';
                    echo '<option value="signature-membership">Signature</option>';
                    echo '<option value="master-signature-membership">Master Signature</option>';
                echo '</select>';
            }
            echo '<label>Score:</label>';
            echo '<select class="opa-judge__art-score-filter opa-judge-filter" style="width: 200px;">';
                echo '<option value="none" selected>Any</option>';
                echo '<option value="0.00">Unrated</option>';
                echo '<option value="1.00">1 Star</option>';
                echo '<option value="2.00">2 Stars</option>';
                echo '<option value="3.00">3 Stars</option>';
                echo '<option value="4.00">4 Stars</option>';
                echo '<option value="5.00">5 Stars</option>';
                echo '<option value="6.00">6 Stars</option>';
                echo '<option value="7.00">7 Stars</option>';
            echo '</select>';
            echo '<button class="opa-judge__art-filter-button reset-this opa-judge-button" style="">Filter</button>';
        echo '</div>';

        echo '<div class="opa-judge-gallery-wrapper">';
        foreach ( $round_artwork as $artwork ) {
            $memberships = wc_memberships_get_user_memberships($artwork["artist_id"]);
            $membership_slug = count($memberships) > 0 ? $memberships[0]->get_plan()->slug : "";

            echo '<div class="opa-judge-gallery-submission" art-id="' . $artwork['id'] . '" division="' . $membership_slug . '" score="' . ($artwork['score'] == "" ? "0.00" : $artwork['score']) . '">';
                echo '<img src="' . wp_get_attachment_image_url($artwork['painting_file_original'], 'thumbnail', 'loading="lazy"') . '" loading="lazy"/>';
                echo '<div class="opa-judge-gallery-ranking"></div>';
            echo '</div>';
        }
        echo '</div>';
    echo '</div>';

    ?>
    <form id="judge-form" class="opa-judge-form" method="POST" style="display: none;">
        <input type="hidden" name="show_id" value="<?php echo intval( $show_id ) ?>" />
        <input type="hidden" name="painting_id" value="" />
        <input type="hidden" name="rating" value="" />
        <input type="hidden" name="action" value="opa_rate_artwork" />
		<?php echo wp_nonce_field( 'opa_rate_artwork', 'opa_rate_artwork_nonce', true, false ); ?>
        <input type="submit" class="button button-primary button-large" value="<?php _e( 'Submit', OPA_DOMAIN ) ?>">
    </form>
	<div class="opa-judge" style="display: none;">

        <?php if ( !$juror_membership_active ) { ?>
            <div class="opa-judge__error"><?php _e( 'You need to renew your membership before you will be able to judge artwork.', OPA_DOMAIN ) ?></div><?php
        } else if ( empty( $round_artwork ) ) { ?>
	        <div class="opa-judge__error"><?php _e( 'No artwork has been added to this jury round yet.  Please contact support.', OPA_DOMAIN ) ?></div><?php
        } else { ?>

            <div class="opa-judge__top-bar">
                <div class="opa-judge__art-auto-advance-wrapper" style="left: 0; position: absolute; padding: 0px 0px 0px 20px;">
                    Auto Advance 
                    <input type="checkbox" name="auto advance" value="auto advance" class="opa-judge__art-auto-advance"/>
                </div>
                <div class="opa-judge__art-title">
                    <?php echo esc_html( $round_artwork[0]['painting_name'] ) ?>
                    <?php echo esc_html( $round_artwork[0]['painting_height'] ) . '" by ' . esc_html( $round_artwork[0]['painting_width'] . '"' ) ?>
                </div>
                <div class="opa-judge__art-info">
                    i
                    <div class="opa-judge__art-description">
	                    <?php echo esc_html( $round_artwork[0]['painting_description'] ) ?>
                        
                    </div>
                </div>
                <div class="opa-judge__completion">0%</div>
                <div class="opa-judge__back-button">
                    <a href="#" class="opa-judge-back" style="margin-left: 3px;font-size: 1.7rem;color:blanchedalmond;"><?php _e( 'Home', OPA_DOMAIN ) ?></a>
                </div>
            </div>

            <div class="opa-judge__artwork">
                <?php
                    foreach ( $round_artwork as $k => $artwork ) {?>
                        <div class="opa-judge__art <?php echo $k === 0 ? 'opa-judge__art--active' : '' ?>"
                             data-art-id="<?php echo esc_html( $artwork['id'] ) ?>"
                             data-art-title="<?php echo esc_html( $artwork['painting_name'] ) ?>"
                             data-art-description="<?php echo esc_html( $artwork['painting_description'] ) ?>"
                             data-art-rating="<?php echo esc_html( $artwork['score'] ) ?>"
                             data-art-dimensions="<?php echo esc_html( $artwork['painting_width'] ) . ':' . esc_html( $artwork['painting_height'] ) ?>"
                             data-art-artist-name="<?php echo $is_online ? esc_html( get_member_full_name($artwork['artist_id']) ) : "" ?>"
                        >
                            <img src="" class="zoom" img-src="<?php echo esc_html( wp_get_attachment_url($artwork['painting_file_original']) ) ?>"/>
                        </div><?php
                    }
                ?>
            </div>

            <div class="opa-judge__bottom-bar">
                <div class="opa-judge__previous opa-left-arrow-fix"><div>Previous</div></div>
                <div class="opa-judge__rating">
                    <div class="opa-rating-wrapper">
                        <div class="opa-rating" data-rate-value=6></div>
                    </div>
                    <div class="opa-judge__pagination">
                        <div class="opa-judge__pagination-current">1</div>
                        <div class="opa-judge__pagination-of"><?php _e( 'of', OPA_DOMAIN ) ?></div>
                        <div class="opa-judge__pagination-total"><?php echo count( $round_artwork ) ?></div>
                    </div>
                </div>
                <div class="opa-judge__next opa-right-arrow-fix"><div>Next</div></div>
            </div><?php
        } ?>
	</div><?php
} else {
	echo("<script>location.href = '". parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) ."'</script>");
}

function get_grid_view_rating_html($rating) {
    
}

function opa_judging_view() { ?>
    <script type="text/javascript" >
        jQuery(document).ready(function($) {

            // Store Rating
            var rateYo = $('.opa-rating').rateYo({ rating: 0 });

            // Magnification for Juror
            $('.zoom').okzoom({
                width: 600,
                height: 600,
                /*scaleWidth: 3600,*/
                round: true,
                background: "#fff",
                backgroundRepeat: "no-repeat",
                shadow: "0 0 5px #000",
                border: "1px solid black",
            });

            // Add gallery grid stars
            $('.opa-judge-gallery-submission').each(function() {
                updateSubmissionGalleryRanking(jQuery(this));
            });

            /**
             * Update the UI
             */
            function updateScreen() {
                var $currentArt = $('.opa-judge__art--active');
                var currentArtId = parseInt( $currentArt.attr('data-art-id') ) || 0;
                var currentRating = ($currentArt.attr('data-art-rating') / 100) || 0;

                $extra = "";
                if ($currentArt.attr("data-art-artist-name") != "") {
                    $extra = " by " + $currentArt.attr("data-art-artist-name") + ", Painting ID: " + currentArtId;
                }
                
                $('.opa-judge__pagination-current').text( $currentArt.index() + 1 );
                $('.opa-judge__art-title').text( $currentArt.attr('data-art-title') + ' ' + $currentArt.attr('data-art-dimensions').split(':')[1] + '" by ' + $currentArt.attr('data-art-dimensions').split(':')[0] + '"' + $extra);
                $('.opa-judge__art-description').text( $currentArt.attr('data-art-description') );

                // Destroy the Rating
                rateYo.rateYo("destroy");

                // Make sure the rating is in the correct bounds
                if (currentRating > 0.07) {
                    currentRating = 0.07;
                }

                // Reinitialize Rating
                rateYo = $('.opa-rating').rateYo({
                    precision: 2,
                    rating: currentRating,
                    numStars: 7,
                    maxValue: 0.07,
                    spacing: "15px",
                    fullStar: true,
                    starWidth: "40px",
                    onSet: function( rating, rateYoInstance ) {
                        
                        var $submission = $('.opa-judge-gallery-submission[art-id="' + currentArtId + '"]');
                        $submission.attr("score", rating * 100);

                        updateSubmissionGalleryRanking($submission);
                        
                        var $form = $('.opa-judge-form' );
                        $form.find('[name="painting_id"]').val( currentArtId );
                        $form.find('[name="rating"]').val( rating * 100);
                        $form.submit();
                    }
                });

                // Update the percentage
                var percentage = ( $('.opa-judge__art[data-art-rating!=""]').length / $('.opa-judge__art').length * 100 ).toFixed(5);
                $('.opa-judge__completion').text( percentage + '%' );
            }

            /**
             * Submit Rating to Backend
             */
            $(document).on('submit', '.opa-judge-form', function(e) {
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
                        $('.opa-judge__art[data-art-id="' + result.data.art_id + '"]').attr('data-art-rating', result.data.score);
                        updateScreen();
                        // Auto Advance
                        if ($('.opa-judge__art-auto-advance')[0].checked) {
                            $('.opa-judge__next').click();
                        }
                    } else if ( result.success === false ) {
                        alert( result.data.message );
                    }
                }).fail((jqXHR) => {
                    alert( "Server error.  Please try again later." );
                });
            });

            /**
             * Navigate to Previous Images
             */
            $(document).on('click', '.opa-judge__previous', function() {
               var $currentArt = $('.opa-judge__art--active');
               var $previous = $currentArt.prevAll('.opa-judge__art').not('.opa-judge__art--active').not(".hidden-artwork").first();

               if ( $previous.length > 0 ) {
                   $currentArt.removeClass('opa-judge__art--active');
                   $previous.addClass('opa-judge__art--active');

                    $img = $previous.find("img");
                    if ($img.attr("src") == "") {
                        $img.attr("src", $img.attr("img-src"));
                    }
               }

               updateScreen();
            });

            /**
             * Navigation to Next Images
             */
            $(document).on('click', '.opa-judge__next', function() {
                var $currentArt = $('.opa-judge__art--active');
                var $next = $currentArt.nextAll('.opa-judge__art').not('.opa-judge__art--active').not(".hidden-artwork").first();

                if ( $next.length > 0 ) {
                    $currentArt.removeClass('opa-judge__art--active');
                    $next.addClass('opa-judge__art--active');

                    $img = $next.find("img");
                    if ($img.attr("src") == "") {
                        $img.attr("src", $img.attr("img-src"));
                    }
                }

                updateScreen();
            });
            
            /**
            On gallery image click
            */
            $(".opa-judge-gallery-submission").click(function() {
                $('.opa-judge').show();
                $('.opa-judge-gallery').hide();
                $('.opa-judge__art--active').removeClass("opa-judge__art--active");
                $art = $('.opa-judge__art[data-art-id="' + $(this).attr("art-id") + '"]');
                $art.addClass('opa-judge__art--active');
                $img = $art.find("img");
                if ($img.attr("src") == "") {
                    $img.attr("src", $img.attr("img-src"));
                }
                updateScreen();
            });

            /**
            Back to gallery view
            */
            $(".opa-judge-back").click(function(e) {
                $('.opa-judge').hide();
                $('.opa-judge-gallery').show();
            });

            /**
            Jump to button
            */
            $(".opa-judge__art-jump-to-button").click(function() {

                var $input = $(".opa-judge__art-jump-to-number");

                if (isNaN($input.val())) {
                    alert("Input is not a number!");
                    return;
                }

                var $subissions = $(".opa-judge-gallery-submission").not(".hidden-submission");
                
                var $jumpToArt = $($subissions[parseInt($input.val())]);

                if ($jumpToArt[0]) {

                    $('.opa-judge').show();
                    $('.opa-judge-gallery').hide();
                    $('.opa-judge__art--active').removeClass("opa-judge__art--active");
                    $art = $('.opa-judge__art[data-art-id="' + $jumpToArt.attr("art-id") + '"]');
                    $art.addClass('opa-judge__art--active');
                    $img = $art.find("img");
                    if ($img.attr("src") == "") {
                        $img.attr("src", $img.attr("img-src"));
                    }
                    updateScreen();
                }else{
                    alert("That is an invalid input!");
                }

            });

            var isLoading = false;
            $(".opa-judge__art-filter-button").click(function() {

                if (isLoading) return;

                var atts = [];

                $(this).html("Loading");
                $(this).attr("style", "background: gray !important;");
                $(this).prop("disabled", "true");
                isLoading = true;

                setTimeout(() => {
                    
                    var division = $(".opa-judge__art-division-filter").val();
                    if (division && division != "none") atts.push(createAtt("division", division));

                    var score = $(".opa-judge__art-score-filter").val();
                    if (score && score != "none") atts.push(createAtt("score", score));
                    
                    filterSubmissions(atts);
                    updateJumpToLimits();

                    $(this).html("Filter");
                    $(this).attr("style", "");
                    $(this).removeAttr("disabled");
                    isLoading = false;
                }, 20);
            });

            // Set defaults
            $(".opa-judge__art-division-filter").val("none");
            $(".opa-judge__art-jump-to-number").val(0);

            /**
            Filter all submissions by the attributes and hides them

            atts = array of attribute objects to compare against the submissions

            attribute object = {
                key = "some attribute on an element like class for example",
                value = "value of the element attribute"
            }

            */
            function filterSubmissions(atts) {
                $submissions = $(".opa-judge-gallery-submission");
                $submissions.removeClass("hidden-submission");

                $(".opa-judge__art").removeClass("hidden-artwork");

                for (var i = 0; i < $submissions.length; i++) {
                    var $sub = $($submissions[i]);
                    var valid = true;

                    for (var a = 0; a < atts.length; a++) {
                        var att = atts[a];
                        var elAtt = $sub.attr(att.key);
                        
                        if (typeof elAtt === 'undefined' || elAtt === false) {
                            valid = false;
                            break;
                        }else if (elAtt != att.value) {
                            valid = false;
                            break;
                        }
                    }

                    if (!valid) {
                        $sub.addClass("hidden-submission");
                        $('.opa-judge__art[data-art-id="' + $sub.attr("art-id") + '"]').addClass("hidden-artwork");
                    }
                }
            }

            function createAtt(key, value) {
                return {
                    key: key,
                    value: value,
                };
            }

            function updateJumpToLimits() {
                var $input = $(".opa-judge__art-jump-to-number");
                var $subissions = $(".opa-judge-gallery-submission").not(".hidden-submission");

                $input.attr("min", "0");
                $input.attr("max", $subissions.length);
                $input.val(0);

                $(".opa-judge__art-jump-to-number-count").html($subissions.length);
            }

            function updateSubmissionGalleryRanking($submission) {
                var $ranking = $submission.find(".opa-judge-gallery-ranking");

                if ($ranking.rateYo() != null) $ranking.rateYo("destroy");
                $ranking.rateYo({
                    rating: parseInt($submission.attr("score")) / 100,
                    precision: 2,
                    spacing: "3px",
                    starWidth: "20px",
                    readOnly: true,
                    numStars: 7,
                    maxValue: 0.07,
                });
            }
            
            // Update the Screen on Page Load
            updateScreen();

            window.onbeforeunload = function(e) {
                var unJudgedArtAmount = $('.opa-judge-gallery-submission[score="0.00"]').length;
                var artAmount = $('.opa-judge-gallery-submission').length;

                if (unJudgedArtAmount > 0) {
                    $('.opa-judge__art-division-filter').val("none");
                    $('.opa-judge__art-score-filter').val("0.00");
                    $('.opa-judge__art-filter-button').click();
                    $('.opa-judge-gallery-submission[score="0.00"]').attr("style", "filter: drop-shadow(0px 0px 12px red);");
                    e.returnValue = 'There are still ' + unJudgedArtAmount + 'unjudged art pieces out of ' + artAmount + ' total art pieces!';
                }
            };

        });
    </script> <?php
}
add_action( 'wp_footer', 'opa_judging_view' );

?>
