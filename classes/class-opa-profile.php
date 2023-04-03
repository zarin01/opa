<?php
class OPA_Profile
{
    public static function init()
    {
        add_shortcode('opa-profile', __CLASS__ . '::show_profile');
        add_action('wp_ajax_opa_artist_update_art', __CLASS__ . '::artist_update_art');
        // opa_artist_update_art
    }

    public static function show_profile($atts)
    {
        require_once(OPA_PATH . 'views/shortcode-profile.php');
    }

    public static function artist_update_art()
    {
        // This is a secure process to validate if this request comes from a valid source.
        check_ajax_referer('opa_artist_update_art', 'opa_artist_update_art_nonce');

        $user = wp_get_current_user();
        $painting_id = OPA_Functions::clean_input($_POST["painting_id"]);
        $painting_name = OPA_Functions::clean_input($_POST["painting_name"]);
        $painting_description = OPA_Functions::clean_input($_POST["painting_description"]);
        $painting_width = OPA_Functions::clean_input($_POST["painting_width"]);
        $painting_height = OPA_Functions::clean_input($_POST["painting_height"]);
        $painting_price = OPA_Functions::clean_input($_POST["painting_price"]);
       // $painting_check = getimagesize($_FILES["painting_file"]["tmp_name"]);
        $painting_file = null;

        //if ($painting_check !== false) {
            $painting_file_original = OPA_Profile::upload_image_to_media($painting_name, $_FILES["painting_file"]["tmp_name"],$_FILES["painting_file"]["name"]);
            $painting_file = $painting_file_original; /*base64_encode(file_get_contents($_FILES["painting_file"]["tmp_name"]));*/
        //}

        try {
            OPA_Model_Artist::update_artwork($painting_id, $painting_name, $painting_description, $painting_price, $painting_width, $painting_height, $painting_file,$painting_file_original);
            wp_send_json_success(array(
                'message' => 'Update Successful',
                'painting_file' => $painting_file
            ));
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Server Error!'
            ));
        }
        die();
    }


    public static function upload_image_to_media($painting_name, $painting_file,$ext)
    {
        $painting_name = Clean_string($painting_name);
        if ($ext=='') {
            $image_url = $painting_name." ".rand(10, 100).".jpg";
        }else{
            $temp = explode(".", $ext);
            $image_url  = current($temp).'-'.round(microtime(true)) . '.' . end($temp);
        }


        $image_url = Clean_Image_Filename($image_url);
        $upload_dir = wp_upload_dir();

        $image_data = file_get_contents($painting_file);

        $filename = basename($image_url);

        if (wp_mkdir_p($upload_dir['path'])) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        file_put_contents($file, $image_data);

        $wp_filetype = wp_check_filetype($filename, null);

        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $file);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        wp_update_attachment_metadata($attach_id, $attach_data);
        update_post_meta($attach_id, 'entries', 'yes');
        return $attach_id;
    }


    public static function upload_artist_headshot($painting_name, $painting_file,$ext)
    {
        $painting_name = Clean_string($painting_name);
        if ($ext=='') {
            $image_url = $painting_name." ".round(microtime(true)).".jpg";
        }else{
            $temp = explode(".", $ext);
            $image_url  = current($temp).'-'.round(microtime(true)) . '.' . end($temp);
        }


        $image_url = Clean_Image_Filename($image_url);
        $upload_dir = wp_upload_dir();

        $image_data = file_get_contents($painting_file);

        $filename = basename($image_url);

        if (wp_mkdir_p($upload_dir['path'])) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        file_put_contents($file, $image_data);

        $wp_filetype = wp_check_filetype($filename, null);

        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $file);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        wp_update_attachment_metadata($attach_id, $attach_data);
        update_post_meta($attach_id, 'headshot', 'yes');
        return $attach_id;
    }


}
function Clean_string($string) {
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

function Clean_Image_Filename($file_name) {
    $string = str_replace(' ', '-', $file_name); // Replaces all spaces with hyphens.

    $pieces = explode(".", $string);
    $file_type = array_pop($pieces);

    if (count($pieces) > 0) {
        return preg_replace('/[^A-Za-z0-9\-]/', '', implode(".", $pieces)) . "." . $file_type;
    }

    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}