<?php
$more = array_key_exists( 'more', $_GET ) ? $_GET['more'] : null;
switch ( $more ) {
	case 'edit':
		return require_once( OPA_PATH . 'views/admin-management-show-artwork-edit.php' );
	default:
		return require_once( OPA_PATH . 'views/admin-management-show-artwork-list.php' );
}
?>
