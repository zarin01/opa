<?php
$more = array_key_exists( 'more', $_GET ) ? $_GET['more'] : null;
switch ( $more ) {
	case 'round-art':
		return require_once( OPA_PATH . 'views/admin-management-show-jurying-rounds-art.php' );
	case 'round-jurors':
		return require_once( OPA_PATH . 'views/admin-management-show-jurying-rounds-jurors.php' );
	case 'round-judging':
		return require_once( OPA_PATH . 'views/admin-management-show-jurying-rounds-judging.php' );
	default:
		return require_once( OPA_PATH . 'views/admin-management-show-jurying-rounds-list.php' );
}
?>
