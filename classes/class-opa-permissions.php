<?php
class OPA_Permissions {

	static function init() {
		self::create_user_roles();

		add_action( 'show_user_profile', __CLASS__ . '::create_user_fields' );
		add_action( 'edit_user_profile', __CLASS__ . '::create_user_fields' );

		add_action( 'personal_options_update', __CLASS__ . '::update_user_fields' );
		add_action( 'edit_user_profile_update', __CLASS__ . '::update_user_fields' );
	}

	static function create_user_roles() {
		if ( ! wp_roles()->is_role('member') ) {
			add_role(
				'member',
				__( 'Member', OPA_DOMAIN ),
				array(
					'read' => true
				)
			);
		}

		if ( ! wp_roles()->is_role('inactive_member') ) {
			add_role(
				'inactive_member',
				__( 'Inactive Member', OPA_DOMAIN ),
				array(
					'read' => true
				)
			);
		}
	}

	static function create_user_fields( $user ) {
		require_once( OPA_PATH . 'views/admin-user-fields.php' );
	}

	static function update_user_fields( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		if ( ! empty( $_POST['address_line_1'] ) ) {
			update_user_meta( $user_id, 'address_line_1', $_POST['address_line_1'] );
		}

		if ( ! empty( $_POST['address_city'] ) ) {
			update_user_meta( $user_id, 'address_city', $_POST['address_city'] );
		}

		if ( ! empty( $_POST['address_state'] ) ) {
			update_user_meta( $user_id, 'address_state', $_POST['address_state'] );
		}

		if ( ! empty( $_POST['address_zip'] ) ) {
			update_user_meta( $user_id, 'address_zip', $_POST['address_zip'] );
		}
	}

}
