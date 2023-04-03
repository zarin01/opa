<?php
$show_id = intval($_GET['show_id']);
$page_number = intval($_GET['page_number']);
$filter = $_GET['filter'];
$show_awards = OPA_Model_Show::get_awards($show_id);
$all_artwork = OPA_Model_Show::get_artwork_without_image_code($show_id, $filter);
$artwork = OPA_Model_Show::get_artwork_pagination($all_artwork, $page_number, OPA_SHOW_ART_PAGE_LENGTH);
// echo "<pre>";
// print_r($all_artwork);
// die;
$formatted_artwork = [];

?><h3><?php echo __('Artwork', OPA_DOMAIN); ?></h3>

    <form id="award-add-to-art-form" class="opa-award-add-to-art-form" method="POST" style="display: none;">
        <input type="hidden" id="award_id" name="award_id" value="<?php echo $show_id ?>"/>
        <input type="hidden" name="div_id" value="0">
        <input type="hidden" name="art_id" value=""/>
        <input type="hidden" name="action" value="opa_award_add_to_art"/>
        <?php echo wp_nonce_field('opa_award_add_to_art', 'opa_award_add_to_art_nonce', true, false); ?>
        <input type="submit" class="button button-primary button-large" value="<?php _e('Submit', OPA_DOMAIN) ?>">
    </form>
    <form id="artwork-add-remove-acceptance-form" class="opa-add-remove-acceptance-form" method="POST"
          style="display: none;">
        <input type="hidden" name="active" value=""/>
        <input type="hidden" name="art_id" value=""/>
        <input type="hidden" name="action" value="opa_artwork_add_remove_acceptance"/>
        <?php echo wp_nonce_field('opa_artwork_add_remove_acceptance', 'opa_artwork_add_remove_acceptance_nonce', true, false); ?>
        <input type="submit" class="button button-primary button-large" value="<?php _e('Submit', OPA_DOMAIN) ?>">
    </form><?php

/**
 * Dynamically show award status for each piece of art
 * @param $show_awards
 * @param $current_value
 *
 * @return string
 */
function opa_award_select_render($show_awards, $current_value, $art_id)
{
    $select_options = '';
    $current_value = explode(',', $current_value);
    foreach ($current_value as $key => $current_award) {
        $select_options .= '<div class="main-award-class main-award-class-' . $art_id . '" rel="' . $art_id . '">';

        $select_options .= '<select name="art-award" class="art-award-class" rel="' . $art_id . '"><option value="select award">' . __('Select Award', OPA_DOMAIN) . '</option>';
        foreach ($show_awards as $award) {
            $select_options .= sprintf(
                '<option value="%s" %s>%s</option>',
                $award['id'],
                $award['id'] === $current_award ? 'selected' : '',
                $award['title']
            );
        }
        $select_options .= '</select>';
        if ($key != 0) {
            $select_options .= '<button class="remove-award" rel=' . $art_id . ' reltype="remove">-</button>';
        }
        $select_options .= '</div>';
    }
    $select_options .= '<button class="add-more-awards" rel="' . $art_id . '">+</button>';

    return $select_options;

}





//divition select box
function get_division_data($art_id)
{
    global $wpdb;
    $art_data = $wpdb->get_results('select * from ' . $wpdb->prefix . 'opa_art where id=' . $art_id);
    $show_id = intval($_GET['show_id']);
    $Division = wp_get_post_terms($show_id, array('Division'));
    $html .= '<div class="art_division" rel="' . $art_id . '">';
    $html .= "<select name='art_division' class='art-divison-".$art_id."' rel='".$art_id."'><option value='select division'>Select Division</option>";

    foreach ($Division as $div) {
        if ($art_data[0]->division_id == $div->term_id) {
            $selected = "selected='selected'";
        } else {
            $selected = '';
        }
        $html .= '<option ' . $selected . ' value=' . $div->term_id . '>' . $div->name . '</option>';
    
    if ($selected == false) {
        $html .= '<button class="remove-division" rel=' . $div->term_id . ' reltype="remove">-</button>';
    }
    }
        $html .= "</select>";
        $html .= '</div>';

    $html .= '<button class="add-more-divisions" rel="' . $div->term_id . '">+</button>';
    return $html;
    }








foreach ($artwork as $art) {
    $artist_id = $art['artist_id'];
    $artist_obj = get_user_by('ID', $artist_id);
    $artist_name = $artist_obj->display_name;
    $artist_membership_obj = wc_memberships_get_user_active_memberships($artist_id);
    foreach ($artist_membership_obj as $membership) {
        $membership_plan_name = $membership->plan->name;
    }
    if ($membership_plan_name == 'Signature Membership') {
        $member_designation = ' OPA';
    } else {
        // Do nothing
    }

    $formatted_artwork[] = array(
        'ID' => $art['id'],
        'Painting Details' => '<strong>' . $art['painting_name'] . '</strong><p>' . esc_html($art['painting_description']) . '</p><p><em>' . get_member_full_name($artist_id) . '</em></p>',
        'Painting' => (
            '<div class="croppie-image-wrapper"> 
                <img src="' . wp_get_attachment_thumb_url($art['painting_file_original']) . '" style="width: 250px;" />
                <div class="croppie-image-wrapper__actions">
                    <div class="croppie-image-wrapper__edit" data-art-id="' . $art['id'] . '"><a target="_blank" href="/wp-admin/post.php?post=' . $art['painting_file_original'] . '&action=edit">' . __('Adjust', OPA_DOMAIN) . '</a></div>, 
                    <a class="croppie-image-wrapper__download" download="download.png" href="' . wp_get_attachment_thumb_url($art['painting_file_original']) . '">' . __('Download', OPA_DOMAIN) . '</a>
                </div>
            </div>'
        ),
        'Award' => opa_award_select_render($show_awards, $art['award_id'], $art['id']),
        'Division' => get_division_data($art['id']),
        'Acceptance' => (
        intval($art['accepted']) === 1 ? (
            __('Accepted!', OPA_DOMAIN) . '<br /><a href="#" style="color: #a00;" data-art-remove-acceptance="' . $art['id'] . '">' . __('Remove', OPA_DOMAIN) . '</a>'
        ) : (
            '<a href="#" data-art-add-acceptance="' . $art['id'] . '">' . __('Accept Art into Show', OPA_DOMAIN) . '</a>'
        )
        ),
        'Edit' => (
            '<a href="javascript:void(0)" data-edit-artwork="' . $art['id'] . '">' . __('Edit', OPA_DOMAIN) . '</a>'
        ),
    );

}
echo '<div style="margin-bottom:10px;"><input type="text" value="' . $filter . '" placeholder="Painting Name/ID & Artist Id" name="filter" id="filter_input"><button id="filter_button" class="button button-large">Filter</button></div>';

echo '<div style="margin-bottom:10px;"><button id="duplicate_entries_button" class="button button-large">Duplicate Entries</button></div>';

echo OPA_Exports::export_button(__('Export CSV'), array(
    'opa_export_type' => 'export-artwork-csv',
    'show_id' => $show_id
));
echo OPA_Exports::export_button(__('Export Art Images'), array(
    'opa_export_type' => 'export-artwork-zip',
    'show_id' => $show_id
));

echo OPA_Exports::export_button(__('Export Accepted Art Images'), array(
    'opa_export_type' => 'export-accepted-images',
    'show_id' => $show_id
));

echo '<a href="?page=opa&show_id=' . $show_id . '&section=add-art-image" class="button button-large">Add Art Image</a>';
echo OPA_Functions::build_table($formatted_artwork, 'hide');

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
echo OPA_Functions::build_show_pagination($all_artwork, $actual_link, $page_number, OPA_SHOW_ART_PAGE_LENGTH);

function opa_expand_to_artist_payment()
{ ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $("#filter_button").click(function () {
                var filter_input = $("#filter_input").val();
                var href = new URL(window.location.href);
                href.searchParams.set('filter', filter_input);
                window.location.href = href.toString();
            })

            $("#duplicate_entries_button").click(function () {
                $.ajax({
                    url: 'admin.php?opa_export_type=export-duplicate-entries&show_id=' + $("#award_id").val() + '&page=opa',
                    method: 'GET',
                    success: (data) => {
                        console.log(JSON.parse(data, true))

                        duplicate_entries_data = JSON.parse(data, true)
                        var heading = document.createElement("h3")
                        heading.innerHTML = "Duplicate Entries"

                        var table_ele = document.createElement("table")
                        table_ele.classList = ['opa-table']
                        table_ele.style.marginBottom = '2em'
                        document.getElementsByClassName("opa-table-wrapper")[0].insertBefore(table_ele, document.getElementsByClassName("opa-table-wrapper")[0].children[0])

                        document.getElementsByClassName("opa-table-wrapper")[0].insertBefore(heading, document.getElementsByClassName("opa-table-wrapper")[0].children[0])

                        var table_tr = document.createElement("tr")
                        table_tr.classList = ['opa-table__tr']
                        table_ele.appendChild(table_tr)

                        for (var i = 0; i < duplicate_entries_data.length; i++) {
                            var tr_td = document.createElement("td")
                            tr_td.classList = ['opa-table__th']
                            tr_td.innerHTML = "<b>Artist ID: </b>" + duplicate_entries_data[i]
                            table_tr.appendChild(tr_td)
                        }

                    }
                })
            })
            $(document).on('click', '[data-edit-artwork]', function () {
                var artwork_id = parseInt($(this).attr('data-edit-artwork'));
                window.location.href = window.location.href + "&more=edit&artwork_id=" + artwork_id;
            })







            // !!Remove Artwork  !!//
            $(document).on('click', '.remove-award ', function () {
                let rel = $(this).attr('rel');
                $(".main-award-class-" + rel).find('select').attr('disabled','disabled');
                $(".main-award-class-" + rel).find('button').attr('disabled','disabled');
                let reltype = $(this).attr('reltype');
                if (reltype == 'remove') {
                    $(this).closest('.main-award-class').remove();
                }
                let awardvalue = '';
                $(".main-award-class-" + rel).find('select').each(function (val) {
                    awardvalue += $(this).val() + ',';
                })
                awardvalue = awardvalue.slice(0, -1);

                //  var award_id = $(this).val();
                var art_division = $('.art-divison-'+rel).val();

                var artwork_id = parseInt(rel);
                $('.opa-award-add-to-art-form').find('[name="award_id"]').val(awardvalue);
                $('.opa-award-add-to-art-form').find('[name="div_id"]').val(art_division);
                $('.opa-award-add-to-art-form').find('[name="art_id"]').val(artwork_id);

                $('.opa-award-add-to-art-form').submit();
            })











            // !!Remove Division  !!//
           $(document).on('click', '.remove-division ', function () {
               let rel = $(this).attr('rel');
               $(".art-divison-" + rel).find('select').attr('disabled','disabled');
               $(".art-divison-" + rel).find('button').attr('disabled','disabled');
               let reltype = $(this).attr('reltype');
               if (reltype == 'remove') {
                   $(this).closest('.art_division').remove();
               }
               let awardvalue = '';
               $(".art-divison-" + rel).find('select').each(function (val) {
                   awardvalue += $(this).val() + ',';
               })
               awardvalue = awardvalue.slice(0, -1);
               //  var award_id = $(this).val();
               var art_division = $('.art-divison-'+rel).val();
               var artwork_id = parseInt(rel);
               $('.opa-award-add-to-art-form').find('[name="award_id"]').val(awardvalue);
               $('.opa-award-add-to-art-form').find('[name="div_id"]').val(art_division);
               $('.opa-award-add-to-art-form').find('[name="art_id"]').val(artwork_id);
               $('.opa-award-add-to-art-form').submit();
           })

            






              //! Button Award Function !!//
            $(document).on('change', '.art-award-class ', function () {

            let rel = $(this).attr('rel');
            $(".main-award-class-" + rel).find('select').attr('disabled','disabled');
            $(".main-award-class-" + rel).find('button').attr('disabled','disabled');
            let awardvalue = '';
            $(".main-award-class-" + rel).find('select').each(function (val) {
                awardvalue += $(this).val() + ',';
            })
            awardvalue = awardvalue.slice(0, -1);

            //  var award_id = $(this).val();
            var art_division = $('.art-divison-'+rel).val();

            var artwork_id = parseInt(rel);
            $('.opa-award-add-to-art-form').find('[name="award_id"]').val(awardvalue);
            $('.opa-award-add-to-art-form').find('[name="div_id"]').val(art_division);
            $('.opa-award-add-to-art-form').find('[name="art_id"]').val(artwork_id);

            $('.opa-award-add-to-art-form').submit();
            });






            //! Button Division Function !!//
            $(document).on('change', '.art_division', function () {

                let rel = $(this).attr('rel');
                $(".art-divison-" + rel).find('select').attr('disabled','disabled');
                $(".art-divison-" + rel).find('button').attr('disabled','disabled');
                let awardvalue = '';
                $(".art-divison-" + rel).find('select').each(function (val) {
                    awardvalue += $(this).val() + ',';
                })
                awardvalue = awardvalue.slice(0, -1);

                //  var award_id = $(this).val();
                var art_division = $('.art-divison-'+rel).val();

                var artwork_id = parseInt(rel);
                $('.opa-award-add-to-art-form').find('[name="award_id"]').val(awardvalue);
                $('.opa-award-add-to-art-form').find('[name="div_id"]').val(art_division);
                $('.opa-award-add-to-art-form').find('[name="art_id"]').val(artwork_id);

                $('.opa-award-add-to-art-form').submit();
            });







            //!!    add award   !!//
            let i = 0;
            $('.add-more-awards').click(function () {
                var rel = $(this).attr('rel');
                if (i < 4) {
                    $(this).closest('.opa-title_Award').find('.main-award-class:first').clone().insertBefore(this);
                    $(this).closest('.opa-title_Award').find('.main-award-class:last').append('<button class="remove-award" rel=' + rel + ' reltype="remove">-</button>')
                }

                $('.remove-award').click(function () {
                    i--;
                })
                i++;
            })










            //!!    add division   !!//

            $('.add-more-divisions').click(function () {
                var rel = $(this).attr('rel');
                if (i < 4) {
                    $(this).closest('.opa-title_Division').find('.art_division:first').clone().insertBefore(this);
                    $(this).closest('.opa-title_Division').find('.art_division:last').append('<button class="remove-division" rel=' + rel + ' reltype="remove">-</button>')
                }

                $('.remove-division').click(function () {
                    i--;
                })
                i++;
            })

            $(document).on('change', '[name="art_division"]', function () {
                let rel = $(this).attr('rel');
                let awardvalue = '';
                $(".main-award-class-" + rel).find('select').each(function (val) {
                    awardvalue += $(this).val() + ',';
                })
                awardvalue = awardvalue.slice(0, -1);
                var art_division = $(this).val();
                var award_id = $(this).closest('.opa-table__tr').find('select[name=art-award]').val();

                var artwork_id = parseInt($(this).closest('.opa-table__tr').find('td:first').text());
                $('.opa-award-add-to-art-form').find('[name="award_id"]').val(awardvalue);
                $('.opa-award-add-to-art-form').find('[name="div_id"]').val(art_division);
                $('.opa-award-add-to-art-form').find('[name="art_id"]').val(artwork_id);
                $('.opa-award-add-to-art-form').submit();
            });













            $(document).on('submit', '.opa-award-add-to-art-form, .opa-add-remove-acceptance-form', function (e) {
                e.preventDefault();

                var formData = new FormData($(this).get(0));

                // Submit the form to the server
                $.ajax({
                    url: '<?php echo esc_url(admin_url('admin-ajax.php')) ?>',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false
                }).then((result, textStatus, jqXHR) => {
                    if (result.success === true) {
                        location.reload();
                    } else if (result.success === false) {
                        alert(result.data.message);
                    }
                }).fail((jqXHR) => {
                    alert("Server error.  Please try again later.");
                });
            });

            // Remove Winning Artwork
            $(document).on('click', '[data-art-remove-acceptance]', function (e) {
                e.preventDefault();
                var art_id = parseInt($(this).attr('data-art-remove-acceptance'));
                $('.opa-add-remove-acceptance-form [name="art_id"]').val(art_id);
                $('.opa-add-remove-acceptance-form [name="active"]').val(0);
                $('.opa-add-remove-acceptance-form').submit();
            });

            // Add Winning Artwork
            $(document).on('click', '[data-art-add-acceptance]', function (e) {
                e.preventDefault();
                var art_id = parseInt($(this).attr('data-art-add-acceptance'));
                $('.opa-add-remove-acceptance-form [name="art_id"]').val(art_id);
                $('.opa-add-remove-acceptance-form [name="active"]').val(1);
                console.log($('.opa-add-remove-acceptance-form [name="art_id"]').val(), $('.opa-add-remove-acceptance-form [name="active"]').val())
                $('.opa-add-remove-acceptance-form').submit();
            });

        });
    </script> <?php
}

add_action('admin_footer', 'opa_expand_to_artist_payment');
