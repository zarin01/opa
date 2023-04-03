<?php

class OPA_Activation {

	static function init() {
		self::check_db_tables();
	}

	/**
	 * Database Tables
	 */
	static function check_db_tables() {

		global $wpdb;

		// op_art table creation
		$table_opa_art = $wpdb->prefix . 'opa_art';
		if($wpdb->get_var("show tables like '$table_opa_art'") != $table_opa_art)
		{
			$sql = "CREATE TABLE " . $table_opa_art . " (
				`id` mediumint(9) NOT NULL AUTO_INCREMENT,
				`show_id` bigint NOT NULL,
				`artist_id` bigint NOT NULL,
				`division_id` bigint NOT NULL,
				`award_id` bigint NOT NULL DEFAULT 0,
				`painting_name` varchar(255) NOT NULL,
				`painting_description` TEXT NOT NULL,
				`painting_file` MEDIUMBLOB NOT NULL,
				`painting_file_original` MEDIUMBLOB NOT NULL,
				`painting_price` DECIMAL(10,2) NOT NULL DEFAULT 0,
				`painting_not_for_sale` TINYINT NOT NULL DEFAULT 0,
				`painting_width` varchar(255) NOT NULL,
				`painting_height` varchar(255) NOT NULL,
				`accepted` TINYINT NOT NULL DEFAULT 0,
				`stripe_charge_id` varchar(255) NOT NULL,
				`stripe_payment_amount` DECIMAL(10,2) NOT NULL,
				`stripe_payment_date` DATETIME,
				`stripe_refunded_amount` DECIMAL(10,2) NOT NULL,
				UNIQUE KEY id (id)
			);";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		};

		// opa_jurors table creation
		$table_opa_jurors = $wpdb->prefix . 'opa_jurors';
		if($wpdb->get_var("show tables like '$table_opa_jurors'") != $table_opa_jurors)
		{
			$sql = "CREATE TABLE " . $table_opa_jurors . " (
				`id` mediumint(9) NOT NULL AUTO_INCREMENT,
				`show_id` bigint NOT NULL,
				`juror_id` bigint NOT NULL,
				`show_in_list` TINYINT NOT NULL DEFAULT 1,
				UNIQUE KEY id (id)
			);";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		};

		// opa_jury_rounds table creation
		$table_opa_jury_rounds = $wpdb->prefix . 'opa_jury_rounds';
		if($wpdb->get_var("show tables like '$table_opa_jury_rounds'") != $table_opa_jury_rounds)
		{
			$sql = "CREATE TABLE " . $table_opa_jury_rounds . " (
				`id` mediumint(9) NOT NULL AUTO_INCREMENT,
				`show_id` bigint NOT NULL,
				`jury_round_name` varchar(255) NOT NULL,
				`jury_round_active` TINYINT NOT NULL DEFAULT 1,
				`created_at` DATETIME,
				`show_in_list` TINYINT NOT NULL DEFAULT 1,
				UNIQUE KEY id (id)
			);";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		};

		// op_jury_round_jurors table creation
		$table_opa_jury_round_jurors = $wpdb->prefix . 'opa_jury_round_jurors';
		if($wpdb->get_var("show tables like '$table_opa_jury_round_jurors'") != $table_opa_jury_round_jurors)
		{
			$sql = "CREATE TABLE " . $table_opa_jury_round_jurors . " (
				`id` mediumint(9) NOT NULL AUTO_INCREMENT,
				`jury_round_id` bigint NOT NULL,
				`juror_id` bigint NOT NULL,
				`juror_active` TINYINT NOT NULL DEFAULT 1,
				`show_in_list` TINYINT NOT NULL DEFAULT 1,
				UNIQUE KEY id (id)
			);";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		};

		// opa_jury_scores table creation
		$table_opa_jury_scores = $wpdb->prefix . 'opa_jury_scores';
		if($wpdb->get_var("show tables like '$table_opa_jury_scores'") != $table_opa_jury_scores)
		{
			$sql = "CREATE TABLE " . $table_opa_jury_scores . " (
				`id` mediumint(9) NOT NULL AUTO_INCREMENT,
				`jury_round_id` bigint NOT NULL,
				`juror_id` bigint NOT NULL,
				`art_id` bigint NOT NULL,
				`score` DECIMAL(10,2) NOT NULL,
				`count_scores_for_average` TINYINT NOT NULL DEFAULT 1,
				UNIQUE KEY id (id)
			);";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		};

		// opa_jury_round_art table creation
		$table_opa_jury_found_art = $wpdb->prefix . 'opa_jury_round_art';
		if($wpdb->get_var("show tables like '$table_opa_jury_found_art'") != $table_opa_jury_found_art)
		{
			$sql = "CREATE TABLE " . $table_opa_jury_found_art . " (
				`id` mediumint(9) NOT NULL AUTO_INCREMENT,
				`jury_round_id` bigint NOT NULL,
				`art_id` bigint NOT NULL,
				`art_active` TINYINT NOT NULL DEFAULT 1,
				`move_to_next_round` TINYINT NOT NULL DEFAULT 0,
				UNIQUE KEY id (id)
			);";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		};

		// opa_awards table creation
		$table_opa_awards = $wpdb->prefix . 'opa_awards';
		if($wpdb->get_var("show tables like '$table_opa_awards'") != $table_opa_awards)
		{
			$sql = "CREATE TABLE " . $table_opa_awards . " (
				`id` mediumint(9) NOT NULL AUTO_INCREMENT,
				`show_id` bigint NOT NULL,
				`title` varchar(255) NOT NULL,
				`description` TEXT NOT NULL,
				`value` TEXT NOT NULL,
				UNIQUE KEY id (id)
			);";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		};

	}

}
