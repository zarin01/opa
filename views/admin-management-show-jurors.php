<?php
$more = array_key_exists( 'more', $_GET ) ? $_GET['more'] : null;
switch ( $more ) {
	case 'juror-detail':
		return require_once( OPA_PATH . 'views/admin-management-show-jurors-detail.php' );
	default:
		return require_once( OPA_PATH . 'views/admin-management-show-jurors-list.php' );
}
?>
