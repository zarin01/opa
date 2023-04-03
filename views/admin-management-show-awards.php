<h3><?php echo __('Create an Award ', OPA_DOMAIN); ?></h3>

<form id="award-add-form" class="opa-award-add-form" method="POST">

<div class="opa-award-add-form__title">
<label for="award_name"><?php _e('Award Name', OPA_DOMAIN) ?></label>
<input type="text" name="award_name" id="award_name" />
</div>
<div class="opa-award-add-form__description">
<label for="award_description"><?php _e('Award Description', OPA_DOMAIN) ?></label>
<textarea name="award_description" id="award_description"></textarea>
</div>
<div class="opa-award-add-form__value">
<label for="award_value"><?php _e('Award Value', OPA_DOMAIN) ?></label>
<input type="text" name="award_value" id="award_value" />
</div>

<input type="hidden" name="show_id" value="<?php echo $show_id ?>" />
<input type="hidden" name="action" value="opa_award_add_to_show" />
<?php echo wp_nonce_field('opa_award_add_to_show', 'opa_award_add_to_show_nonce', true, false); ?>
<input type="submit" class="button button-primary button-large" value="<?php _e('Submit', OPA_DOMAIN) ?>">
</form>
<?php echo wp_nonce_field('opa_award_add_to_show_update', 'opa_award_add_to_show_update_nonce', true, false); ?>
<br><br>
<h3><?php echo __('Awards Connected to this Show', OPA_DOMAIN); ?></h3><?php

$awards = OPA_Model_Show::get_awards($show_id);
$awards_formatted = [];

foreach ($awards as $key=>$award) {
    $awards_formatted[] = array(
        'Title' => $award['title'],
        'Description' => $award['description'],
        'Value' => $award['value'],
        'Edit' => '<a style="cursor: pointer;" award_id=' . $award['id'] . ' class="editbutton" role="edit" key='.$key.'><i class="fa fa-edit"></i> Edit</a>',
        'Delete' => '<a style="cursor: pointer;color:red;" award_id=' . $award['id'] . ' class="deletebutton" role="delete" key='.$key.'><i class="fa fa-trash"></i> Delete</a>',
       
    );
}


echo OPA_Functions::build_table($awards_formatted, 'hide');


function opa_award_scripts()
{ ?>
<script type="text/javascript">
jQuery(document).ready(function($) {
$(document).on('submit', '.opa-award-add-form', function(e) {
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
$(".editbutton, .deletebutton").click(function(){
   
    var show_id = $('input[name=show_id]').val();
    var role = $(this).attr('role');
    var award_id = $(this).attr('award_id');
    var key = $(this).attr('key');
 if(role=='delete'){
    var r = confirm("Are you Sure want to delete then press OK! ");
  if (r != true) {
    return;
  } 
 }
    if(role=='edit'){
  var title =  $(".opa-title_"+key+" .opa-title_Title").text();
  var desc =  $(".opa-title_"+key+" .opa-title_Description").text();
  var valueP =  $(".opa-title_"+key+" .opa-title_Value").text();
  $(".opa-title_"+key+" .opa-title_Title").html('<input type="text" value="'+title+'">');
  $(".opa-title_"+key+" .opa-title_Description").html('<input type="text" value="'+desc+'">');
  $(".opa-title_"+key+" .opa-title_Value").html('<input type="text" value="'+valueP+'">');
  $(this).attr('role','save');
  $(this).html('<i class="fa fa-save"></i> Save');
    }else {
        var formData = new FormData();
        if(role=='save'){
       
    var title =  $(".opa-title_"+key+" .opa-title_Title input").val();
  var desc =  $(".opa-title_"+key+" .opa-title_Description input").val();
  var valueP =  $(".opa-title_"+key+" .opa-title_Value input").val();
   formData.set('title', title);
    formData.set('desc', desc);
    formData.set('valueP', valueP);
        }
     var opa_award_add_to_show_nonce = $("#opa_award_add_to_show_update_nonce").val();
     var _wp_http_referer = $("input[name=_wp_http_referer]").val();
    formData.set('show_id', show_id);
    formData.set('role', role);
    formData.set('award_id', award_id);
   
    formData.set('_wp_http_referer', _wp_http_referer);
    formData.set('opa_award_add_to_show_update_nonce', opa_award_add_to_show_nonce);
    formData.set('action', 'opa_award_add_to_show_update');
    $(this).html('<i class="fa fa-spinner"></i> Wait...').attr('style','color:#959ca1 !important');
   
    $.ajax({
        url: '<?php echo esc_url(admin_url('admin-ajax.php')) ?>',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
    }).then((result, textStatus, jqXHR) => {
        result = JSON.parse(result);
        if (result.success === true) {
            location.reload();
        } else if (result.success === false) {
            alert(result.data.message);
        }
    }).fail((jqXHR) => {
        alert("Server error.  Please try again later.");
    });

    }



})
// $(".editbutton").click(function(e) {
//     e.preventDefault();
//     var show_id = $('input[name=show_id]').val();
//     var role = $(this).attr('role');
//     var award_id = $(this).attr('award_id');


});
</script> <?php
 }

 add_action('admin_footer', 'opa_award_scripts');
