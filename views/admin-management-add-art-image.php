<?php
$artwork_id = intval($_GET['show_id']);
// $artwork = (array) OPA_Model_Art::get_registrations_by('', 'id', $artwork_id )[0];
global $wpdb;
if ($artwork_id) { ?>
  
	<div class="opa-artwork-edit">
		<form action="#" method="post" enctype='multipart/form-data'>
			
		<div id="PaintingData">
			<div class="main-painting-sec main-painting-sec_1">
				
				<h2 class="paintingtitle">Painting</h2><i class="fa fa-times crossBtn" rel="1"></i>
				<div class="opa-show-registration__painting-name"> <label for="painting_name">Painting User</label>
				<?php
			// $all_user = get_users();
			// echo '<select name="user_id">';
			// foreach($all_user as $user){
			// 	$u = $user->data;
			// 	if(get_current_user_id()==$u->ID){
			// 		$selected = 'selected=selected';
			// 	}else{
			// 		$selected = '';
			// 	}
            //  echo '<option '.$selected.'  value='.$u->ID.'>'.$u->user_email.'</option>';
			// }
			// echo '</select>';
			wp_dropdown_users(array('selected'=>get_current_user_id()));
			?>
				<div class="opa-show-registration__painting-name"> <label for="painting_name">Painting Title</label> <input type="text" name="painting_name" id="painting_name" required=""> </div>
				<div class="opa-show-registration__painting-description"> <label for="painting_description">Painting Medium and Substrate</label> <input type="text" name="painting_description" id="painting_description" required> </div>
				<div class="opa-show-registration__painting-file"> <label for="painting_file">Painting</label> <input type="file" id="painting_file" class="painting_file_class validate-image-file" required="" name="painting_file" accept=".png, .jpg, .jpeg">
					<div class="opa-show-registration__note">Maximum file size is 5mb.</div>
					<div class="opa-show-registration__note">Photo must be high resolution. Please upload a JPG, JPEG ,PNG or HEIC with a maximum width dimension of 4000px and a DPI between 300-350.</div>
				</div>
				<div class="lower-submission-fields">
					<div class="opa-show-registration__painting-height"> <label for="painting_height">Unframed Painting Height</label> <input type="text" name="painting_height" id="painting_height" class="submission_painting_height" required="">
						<div class="opa-show-registration__note">Please round up to the nearest whole number.</div>
					</div>
					<div class="opa-show-registration__painting-width"> <label for="painting_width">Unframed Painting Width</label> <input type="text" name="painting_width" id="painting_width" class="submission_painting_width" required="">
						<div class="opa-show-registration__note">Please round up to the nearest whole number.</div>
					</div>
					<div class="opa-show-registration__painting-price"> <label for="painting_price">Painting Price</label> <input type="number" name="painting_price" id="painting_price" class="submission_painting_price" required="">
						<div class="opa-show-registration__note">Please round up to the nearest whole number.</div>
					</div>
				</div>
			</div>
		</div>
		<button class="opa-show-registration__submit button button-large" name="submit">Submit</button>
</form>
	</div>
<?php
}



if(isset($_POST['submit'])){
	
	$show_id = $_GET['show_id'];
	$user_id = $_POST['user'];
	$painting_name = $_POST['painting_name'];
	$painting_description = $_POST['painting_description'];
	$painting_file_original = OPA_Profile::upload_image_to_media($painting_name,$_FILES["painting_file"]["tmp_name"],$_FILES["painting_file"]["name"]);
	$painting_height = $_POST['painting_height'];
	$painting_width = $_POST['painting_width'];
	$painting_price = $_POST['painting_price'];
	$current_time = current_time( 'mysql' );
	$query = $wpdb->query('insert into '.$wpdb->prefix.'opa_art  set show_id="'.$show_id.'", artist_id="'.$user_id.'", painting_name="'.$painting_name.'", painting_description="'.$painting_description.'", painting_file_original="'.$painting_file_original.'", painting_height="'.$painting_height.'", painting_width="'.$painting_width.'", painting_price="' . $painting_price . '", painting_file="", stripe_charge_id="",stripe_payment_amount=0,stripe_refunded_amount=0,stripe_payment_date="'.$current_time.'"' );
	if($query){
		echo "Painting uploaded successfully";
	}else{
		echo "Painting uploaded failed";	
	}

}

				?>