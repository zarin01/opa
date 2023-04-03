<?php
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
?>
<div class="wrap opa-admin-wrap">
    <h2><?php _e( 'OPA Event Management', OPA_DOMAIN ) ?></h2>
    <?php OPA_Menu::admin_breadcrumb() ?><br />

    <?php
        $show_id = intval($_GET['show_id']);
        if ( ! array_key_exists( 'show_id', $_GET ) ) {
            require_once( OPA_PATH . 'views/admin-management-show-selection.php' );
        } else {
            require_once( OPA_PATH . 'views/admin-management-show-dashboard-options-menu.php' );

            $section = array_key_exists( 'section', $_GET ) ? $_GET['section'] : null;
            switch ( $section ) {
                case 'artists':
                    require_once( OPA_PATH . 'views/admin-management-show-artists.php' );
                    break;
                case 'artwork':
                    require_once( OPA_PATH . 'views/admin-management-show-artwork.php' );
	                break;
                 case 'add-art-image':
                    require_once( OPA_PATH . 'views/admin-management-add-art-image.php' );
                    break;
                case 'jurors':
                    require_once( OPA_PATH . 'views/admin-management-show-jurors.php' );
	                break;
                case 'jury-rounds':
                    require_once( OPA_PATH . 'views/admin-management-show-jurying-rounds.php' );
	                break;
	            case 'awards';
		            require_once( OPA_PATH . 'views/admin-management-show-awards.php' );
		            break;
                case 'reports';
                    require_once( OPA_PATH . 'views/admin-management-show-reports.php' );
	                break;
                default:
                    require_once( OPA_PATH . 'views/admin-management-show-dashboard.php' );
            }
        }
    ?>
    <div class="opa-croppie">
        <div class="opa-croppie__bind" id="opa-croppie"></div>
        <div class="opa-croppie__actions">
            <button class="opa-croppie-result button button-large"><?php _e( 'Save', OPA_DOMAIN ) ?></button>
            <button class="opa-croppie-rotate-left button button-large"><?php _e( 'Rotate Left', OPA_DOMAIN ) ?></button>
            <button class="opa-croppie-rotate-right button button-large"><?php _e( 'Rotate Right', OPA_DOMAIN ) ?></button>
            <button class="opa-croppie-close button button-large"><?php _e( 'Close', OPA_DOMAIN ) ?></button>
        </div>
    </div>
    <form id="edit-image-form" class="opa-edit-image-form" method="POST" style="display: none;">
        <input type="hidden" name="art_id" value="" />
        <input type="hidden" name="blob" value="" />
        <input type="hidden" name="action" value="opa_edit_image" />
		<?php echo wp_nonce_field( 'opa_edit_image', 'opa_edit_image_nonce', true, false ); ?>
        <input type="submit" class="button button-primary button-large" value="<?php _e( 'Submit', OPA_DOMAIN ) ?>">
    </form>
</div>

<?php
function opa_admin_footer_scripts() { ?>
    <script type="text/javascript" >
        jQuery(document).ready(function($) {

            var $opaCroppieWrapper = $('.opa-croppie');
            var vanilla;

            $(document).on('click', '.croppie-image-wrapper__edit_image', function() {

                window.opa_edit_art_id = $(this).attr('data-art-id');
                var $image = $(this).closest('.croppie-image-wrapper').find('img')
                var base64src = $image.attr('src');
                $opaCroppieWrapper.addClass('opa-croppie--visible opa-croppie--edit');

                var el = document.getElementById('opa-croppie');
                vanilla = new Croppie(el, {
                    viewport: { width: 200, height: 130 },
                    boundary: { width: 800, height: 500 },
                    showZoomer: false,
                    enableResize: true,
                    enableOrientation: true,
                    enableZoom: true,
                    mouseWheelZoom: 'ctrl'
                });
                vanilla.bind({
                    url: base64src,
                    orientation: 1
                });
            });

            $(document).on('click', '.opa-croppie-result', function() {
                vanilla.result({
                    type: 'base64'
                }).then(function(base64) {
                    base64 = base64.replace('data:image/png;base64,', '');
                    $('.opa-edit-image-form').find('[name="art_id"]').val( window.opa_edit_art_id );
                    $('.opa-edit-image-form').find('[name="blob"]').val( base64 );
                    $('.opa-edit-image-form').submit();
                });
            });

            // Rotate Left
            $(document).on('click', '.opa-croppie-rotate-left', function() {
                vanilla.rotate(-90);
            });

            // Rotate Right
            $(document).on('click', '.opa-croppie-rotate-right', function() {
                vanilla.rotate(90);
            });

            // Close the Overlay
            $(document).on('click', '.opa-croppie-close', function() {
                vanilla.destroy();
                $opaCroppieWrapper.removeClass('opa-croppie--visible opa-croppie--edit');
            });

            // Edit Image Form
            $(document).on('submit', '.opa-edit-image-form', function(e) {
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
        });
    </script> <?php
}
add_action( 'admin_footer', 'opa_admin_footer_scripts' );
