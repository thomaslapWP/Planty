<?php

namespace WPDataAccess\Plugin_Table_Models {

	use WPDataAccess\WPDA;

	class WPDA_App_Apps_Model extends WPDA_Plugin_Table_Base_Model {

		const BASE_TABLE_NAME = 'wpda_app_apps';

		public static function select_all( $app_id ) {

			global $wpdb;
			return $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM `%1s` WHERE app_id = %d', // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
					array(
						WPDA::remove_backticks( self::get_base_table_name() ),
						$app_id,
					)
				), // db call ok; no-cache ok.
				'ARRAY_A'
			); // phpcs:ignore Standard.Category.SniffName.ErrorCode

		}

	}

}