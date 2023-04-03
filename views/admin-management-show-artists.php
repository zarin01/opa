<?php
    $more = array_key_exists( 'more', $_GET ) ? $_GET['more'] : null;
    switch ( $more ) {
        case 'payment':
            return require_once( OPA_PATH . 'views/admin-management-show-artists-payment.php' );
        default:
            return require_once( OPA_PATH . 'views/admin-management-show-artists-list.php' );
    }
?>

