<?php

namespace WPDataAccess\API;

use WPDataAccess\Connection\WPDADB;
use WPDataAccess\Plugin_Table_Models\WPDA_Media_Model;
use WPDataAccess\Plugin_Table_Models\WPDA_Table_Settings_Model;
use WPDataAccess\Plugin_Table_Models\WPDA_User_Menus_Model;
use WPDataAccess\WPDA;
class WPDA_Actions extends WPDA_API_Core {
    protected $file_pointer;

    protected $file_content;

    public function register_rest_routes() {
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'action/rename', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'action_rename'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'dbs'      => $this->get_param( 'dbs', __( 'Local database name or remote connection string (does not accept system schemas)', 'wp-data-access' ) ),
                'from_tbl' => $this->get_param( 'tbl', __( 'Source table name (does not rename WordPress tables)', 'wp-data-access' ) ),
                'to_tbl'   => $this->get_param( 'tbl', __( 'Destination table name (cannot overwrite existing table)', 'wp-data-access' ) ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'action/copy', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'action_copy'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'from_dbs'  => $this->get_param( 'dbs', __( 'Source database name or remote connection string', 'wp-data-access' ) ),
                'to_dbs'    => $this->get_param( 'dbs', __( 'Destination database name or remote connection string', 'wp-data-access' ) ),
                'from_tbl'  => $this->get_param( 'tbl', __( 'Source table name', 'wp-data-access' ) ),
                'to_tbl'    => $this->get_param( 'tbl', __( 'Destination table name', 'wp-data-access' ) ),
                'copy_data' => array(
                    'required'          => true,
                    'type'              => 'boolean',
                    'description'       => __( 'Copy data from source to destination table', 'wp-data-access' ),
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'action/truncate', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'action_truncate'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'dbs' => $this->get_param( 'dbs', __( 'Local database name or remote connection string (does not accept system schemas)', 'wp-data-access' ) ),
                'tbl' => $this->get_param( 'tbl', __( 'Source table name (does not truncate WordPress tables)', 'wp-data-access' ) ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'action/drop', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'action_drop'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'dbs' => $this->get_param( 'dbs', __( 'Local database name or remote connection string (does not accept system schemas)', 'wp-data-access' ) ),
                'tbl' => $this->get_param( 'tbl', __( 'Source table name (does not drop WordPress tables)', 'wp-data-access' ) ),
                'typ' => $this->get_param( 'typ' ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'action/import', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'action_import'),
            'permission_callback' => '__return_true',
        ) );
    }

    public function action_import( $request ) {
        if ( !$this->current_user_can_access( true ) ) {
            return $this->unauthorized();
        }
        if ( !$this->current_user_token_valid( $request, true ) ) {
            return $this->invalid_nonce();
        }
        $dbs = $this->sanitize_db_identifier( $request->get_param( 'dbs' ) );
        $files = $request->get_file_params();
        $response = array();
        $errors = false;
        if ( 0 === count( $files ) || '' === trim( $dbs ) ) {
            return $this->bad_request();
        } else {
            foreach ( $files as $file ) {
                // phpcs:disable
                $temp_file_name = sanitize_text_field( $file['tmp_name'] );
                // For Windows: do NOT unslash!
                // phpcs:enable
                $temp_file_type = sanitize_text_field( wp_unslash( $file['type'] ) );
                $orig_file_name = sanitize_text_field( wp_unslash( $file['name'] ) );
                if ( 0 === $file['error'] && is_uploaded_file( $temp_file_name ) ) {
                    if ( 'application/zip' === $temp_file_type || 'application/x-zip' === $temp_file_type || 'application/x-zip-compressed' === $temp_file_type ) {
                        // Process ZIP file.
                        if ( class_exists( '\\ZipArchive' ) ) {
                            $zip = new \ZipArchive();
                            if ( $zip->open( $temp_file_name ) ) {
                                for ($i = 0; $i < $zip->numFiles; $i++) {
                                    $this->file_pointer = $zip->getStream( $zip->getNameIndex( $i ) );
                                    $status = $this->import( $zip->getNameIndex( $i ), $dbs );
                                    if ( isset( $status['status'], $status['msg'] ) ) {
                                        $errors = $errors || 'error' === $status['status'];
                                        $response[] = array(
                                            $zip->getNameIndex( $i ) => array(
                                                'status' => $status['status'],
                                                'msg'    => $status['msg'],
                                                'errors' => $status['errors'],
                                            ),
                                        );
                                    }
                                }
                            } else {
                                // Error reading ZIP file.
                                $errors = true;
                                $response[] = array(
                                    $orig_file_name => array(
                                        'status' => 'error',
                                        'msg'    => sprintf( __( 'Import failed [error reading ZIP file `%s`]', 'wp-data-access' ), $orig_file_name ),
                                    ),
                                );
                            }
                        } else {
                            // ZipArchive not installed.
                            $errors = true;
                            $response[] = array(
                                $orig_file_name => array(
                                    'status' => 'error',
                                    'msg'    => sprintf( __( 'Import failed - ZipArchive not installed %s', 'wp-data-access' ) ),
                                ),
                            );
                        }
                    } else {
                        // Process plain file.
                        $this->file_pointer = fopen( $temp_file_name, 'rb' );
                        $status = $this->import( $orig_file_name, $dbs );
                        if ( isset( $status['status'], $status['msg'] ) ) {
                            $errors = $errors || 'error' === $status['status'];
                            $response[] = array(
                                $orig_file_name => array(
                                    'status' => $status['status'],
                                    'msg'    => $status['msg'],
                                    'errors' => $status['errors'],
                                ),
                            );
                        }
                    }
                }
            }
        }
        if ( $errors ) {
            $msg = __( 'File(s) imported with errors', 'wp-data-access' );
        } else {
            $msg = __( 'File(s) successfully imported', 'wp-data-access' );
        }
        return $this->WPDA_Rest_Response( $msg, null, array(
            'imported' => $response,
        ) );
    }

    public function action_drop( $request ) {
        if ( !$this->current_user_can_access( true ) ) {
            return $this->unauthorized();
        }
        if ( !$this->current_user_token_valid( $request, true ) ) {
            return $this->invalid_nonce();
        }
        $dbs = $request->get_param( 'dbs' );
        $tbl = $request->get_param( 'tbl' );
        $typ = $request->get_param( 'typ' );
        if ( '' === $dbs || '' === $tbl ) {
            return $this->bad_request();
        }
        global $wpdb;
        if ( $wpdb->dbname === $dbs && in_array( $tbl, $wpdb->tables() ) ) {
            return $this->unauthorized();
        }
        $msg = $this->drop( $dbs, $tbl, $typ );
        if ( '' === $msg ) {
            if ( 1 === $typ ) {
                return $this->WPDA_Rest_Response( __( 'View successfully dropped', 'wp-data-access' ) );
            } else {
                return $this->WPDA_Rest_Response( __( 'Table successfully dropped', 'wp-data-access' ) );
            }
        } else {
            return new \WP_Error('error', $msg, array(
                'status' => 403,
            ));
        }
    }

    public function action_truncate( $request ) {
        if ( !$this->current_user_can_access( true ) ) {
            return $this->unauthorized();
        }
        if ( !$this->current_user_token_valid( $request, true ) ) {
            return $this->invalid_nonce();
        }
        $dbs = $request->get_param( 'dbs' );
        $tbl = $request->get_param( 'tbl' );
        if ( '' === $dbs || '' === $tbl ) {
            return $this->bad_request();
        }
        global $wpdb;
        if ( $wpdb->dbname === $dbs && in_array( $tbl, $wpdb->tables() ) ) {
            return $this->unauthorized();
        }
        $msg = $this->truncate( $dbs, $tbl );
        if ( '' === $msg ) {
            return $this->WPDA_Rest_Response( __( 'Table successfully truncated', 'wp-data-access' ) );
        } else {
            return new \WP_Error('error', $msg, array(
                'status' => 403,
            ));
        }
    }

    public function action_copy( $request ) {
        if ( !$this->current_user_can_access( true ) ) {
            return $this->unauthorized();
        }
        if ( !$this->current_user_token_valid( $request, true ) ) {
            return $this->invalid_nonce();
        }
        $from_dbs = $request->get_param( 'from_dbs' );
        $to_dbs = $request->get_param( 'to_dbs' );
        $from_tbl = $request->get_param( 'from_tbl' );
        $to_tbl = $request->get_param( 'to_tbl' );
        $copy_data = $request->get_param( 'copy_data' );
        if ( '' === $from_dbs || '' === $to_dbs || '' === $from_tbl || '' === $to_tbl ) {
            return $this->bad_request();
        }
        $msg = $this->copy(
            $from_dbs,
            $to_dbs,
            $from_tbl,
            $to_tbl,
            $copy_data
        );
        if ( '' === $msg ) {
            return $this->WPDA_Rest_Response( __( 'Table successfully copied', 'wp-data-access' ) );
        } else {
            return new \WP_Error('error', $msg, array(
                'status' => 403,
            ));
        }
    }

    public function action_rename( $request ) {
        if ( !$this->current_user_can_access( true ) ) {
            return $this->unauthorized();
        }
        if ( !$this->current_user_token_valid( $request, true ) ) {
            return $this->invalid_nonce();
        }
        $dbs = $request->get_param( 'dbs' );
        $from_tbl = $request->get_param( 'from_tbl' );
        $to_tbl = $request->get_param( 'to_tbl' );
        if ( '' === $dbs || '' === $from_tbl || '' === $to_tbl ) {
            return $this->bad_request();
        }
        if ( 'information_schema' === $dbs || 'mysql' === $dbs || 'performance_schema' === $dbs || 'sys' === $dbs || '' === $dbs ) {
            return $this->unauthorized();
        }
        global $wpdb;
        if ( $wpdb->dbname === $dbs && in_array( $from_tbl, $wpdb->tables() ) ) {
            return $this->unauthorized();
        }
        $msg = $this->rename( $dbs, $from_tbl, $to_tbl );
        if ( '' === $msg ) {
            if ( 1 === $typ ) {
                return $this->WPDA_Rest_Response( __( 'View successfully renamed', 'wp-data-access' ) );
            } else {
                return $this->WPDA_Rest_Response( __( 'Table successfully renamed', 'wp-data-access' ) );
            }
        } else {
            return new \WP_Error('error', $msg, array(
                'status' => 403,
            ));
        }
    }

    private function rename( $dbs, $from_tbl, $to_tbl ) {
        // All values have already been validated and sanitized in the rest route registration.
        if ( !current_user_can( 'manage_options' ) ) {
            return 'Unauthorized';
        }
        $wpdadb = WPDADB::get_db_connection( $dbs );
        if ( null === $wpdadb ) {
            return sprintf( __( 'Remote database %s not available', 'wp-data-access' ), esc_attr( $dbs ) );
        }
        $suppress_errors = $wpdadb->suppress_errors;
        $wpdadb->suppress_errors = true;
        $wpdadb->query( $wpdadb->prepare( 'rename table `%1s` to `%1s`', array($from_tbl, $to_tbl) ) );
        $wpdadb->suppress_errors = $suppress_errors;
        return $wpdadb->last_error;
    }

    private function copy(
        $from_dbs,
        $to_dbs,
        $from_tbl,
        $to_tbl,
        $copy_data
    ) {
        // All values have already been validated and sanitized in the rest route registration.
        if ( !current_user_can( 'manage_options' ) ) {
            return 'Unauthorized';
        }
        $wpdadb_from = WPDADB::get_db_connection( $from_dbs );
        if ( null === $wpdadb_from ) {
            return sprintf( __( 'Remote database %s not available', 'wp-data-access' ), esc_attr( $from_dbs ) );
        }
        $wpdadb_to = WPDADB::get_db_connection( $to_dbs );
        if ( null === $wpdadb_to ) {
            return sprintf( __( 'Remote database %s not available', 'wp-data-access' ), esc_attr( $to_dbs ) );
        }
        $suppress_errors_from = $wpdadb_from->suppress_errors;
        $wpdadb_from->suppress_errors = true;
        $suppress_errors_to = $wpdadb_to->suppress_errors;
        $wpdadb_to->suppress_errors = true;
        // Get create table statement.
        $wpdadb_from->query( "SET sql_mode = 'NO_TABLE_OPTIONS'" );
        $sql_cmd = $wpdadb_from->get_results( $wpdadb_from->prepare( 'show create table `%1s`', array($from_tbl) ), 'ARRAY_A' );
        // Check for errors.
        if ( '' !== $wpdadb_from->last_error ) {
            $wpdadb_from->suppress_errors = $suppress_errors_from;
            $wpdadb_to->suppress_errors = $suppress_errors_to;
            return $wpdadb_from->last_error;
        }
        if ( !isset( $sql_cmd[0]['Create Table'] ) ) {
            $wpdadb_from->suppress_errors = $suppress_errors_from;
            $wpdadb_to->suppress_errors = $suppress_errors_to;
            return 'Create command table failed';
        }
        // Update destination table name if applicable.
        $create_table_statement = $sql_cmd[0]['Create Table'];
        if ( $from_tbl !== $to_tbl ) {
            // Modify create table statement
            $pos = strpos( $create_table_statement, $from_tbl );
            if ( $pos !== false ) {
                $create_table_statement = substr_replace(
                    $create_table_statement,
                    $to_tbl,
                    $pos,
                    strlen( $from_tbl )
                );
            }
        }
        // Create new table.
        $wpdadb_to->query( $create_table_statement );
        // Check for errors.
        if ( '' !== $wpdadb_to->last_error ) {
            $wpdadb_from->suppress_errors = $suppress_errors_from;
            $wpdadb_to->suppress_errors = $suppress_errors_to;
            return $wpdadb_to->last_error;
        }
        if ( '1' === $copy_data ) {
            // Copy data from source to destination table.
            set_time_limit( 0 );
            // Prevent time out.
            // Use a cursor to process all rows and prevent exhausting memory.
            // Process 100 rows per batch to prevent exhausting memory.
            $buffer_size = 100;
            $index = 0;
            $loop_done = false;
            while ( !$loop_done ) {
                // Get rows.
                $rows = $wpdadb_from->get_results( $wpdadb_from->prepare( 'select * from `%1s` limit %1s offset %1s', array($from_tbl, $buffer_size, $index * $buffer_size) ), 'ARRAY_A' );
                // Process rows.
                foreach ( $rows as $row ) {
                    $wpdadb_to->insert( $to_tbl, $row );
                }
                if ( 100 > count( $rows ) ) {
                    // No more rows to process.
                    $loop_done = true;
                }
                $index++;
            }
        }
        $wpdadb_from->suppress_errors = $suppress_errors_from;
        $wpdadb_to->suppress_errors = $suppress_errors_to;
        return '';
    }

    private function truncate( $dbs, $tbl ) {
        // All values have already been validated and sanitized in the rest route registration.
        if ( !current_user_can( 'manage_options' ) ) {
            return 'Unauthorized';
        }
        $wpdadb = WPDADB::get_db_connection( $dbs );
        if ( null === $wpdadb ) {
            return sprintf( __( 'Remote database %s not available', 'wp-data-access' ), esc_attr( $dbs ) );
        }
        $suppress_errors = $wpdadb->suppress_errors;
        $wpdadb->suppress_errors = true;
        $wpdadb->query( $wpdadb->prepare( 'truncate table `%1s`', array($tbl) ) );
        $wpdadb->suppress_errors = $suppress_errors;
        return $wpdadb->last_error;
    }

    private function drop( $dbs, $tbl, $typ ) {
        // All values have already been validated and sanitized in the rest route registration.
        if ( !current_user_can( 'manage_options' ) ) {
            return 'Unauthorized';
        }
        $wpdadb = WPDADB::get_db_connection( $dbs );
        if ( null === $wpdadb ) {
            return sprintf( __( 'Remote database %s not available', 'wp-data-access' ), esc_attr( $dbs ) );
        }
        $suppress_errors = $wpdadb->suppress_errors;
        $wpdadb->suppress_errors = true;
        if ( 1 === $typ ) {
            $wpdadb->query( $wpdadb->prepare( 'drop view `%1s`', array($tbl) ) );
        } else {
            $wpdadb->query( $wpdadb->prepare( 'drop table `%1s`', array($tbl) ) );
        }
        $this->post_drop_table( $dbs, $tbl );
        $wpdadb->suppress_errors = $suppress_errors;
        return $wpdadb->last_error;
    }

    private function post_drop_table( $dbs, $tbl ) {
        global $wpdb;
        $suppress = $wpdb->suppress_errors( true );
        // Table settings...
        $wpdb->query( $wpdb->prepare( 
            'delete from `%1s` where wpda_schema_name = %s and wpda_table_name = %s ',
            // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
            array(WPDA::remove_backticks( WPDA_Table_Settings_Model::get_base_table_name() ), $dbs, $tbl)
         ) );
        // WordPress media library columns...
        $wpdb->query( $wpdb->prepare( 
            'delete from `%1s` where media_schema_name = %s and media_table_name = %s ',
            // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
            array(WPDA::remove_backticks( WPDA_Media_Model::get_base_table_name() ), $dbs, $tbl)
         ) );
        // Data menus...
        $wpdb->query( $wpdb->prepare( 
            'delete from `%1s` where menu_schema_name = %s and menu_table_name = %s ',
            // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
            array(WPDA::remove_backticks( WPDA_User_Menus_Model::get_base_table_name() ), $dbs, $tbl)
         ) );
        $wpdb->suppress_errors( $suppress );
    }

    private function import( $file_name, $dbs ) {
        if ( !current_user_can( 'manage_options' ) ) {
            return array(
                'status' => 'error',
                'msg'    => 'Unauthorized',
            );
        }
        $errors = array();
        global $wpdb;
        $wpdadb = WPDADB::get_db_connection( $dbs );
        if ( null === $wpdadb ) {
            return array(
                'status' => 'error',
                'msg'    => sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), esc_attr( $dbs ) ),
            );
        }
        $suppress = $wpdadb->suppress_errors( true );
        if ( false !== $this->file_pointer ) {
            while ( !feof( $this->file_pointer ) ) {
                $this->file_content .= fread( $this->file_pointer, 4096 );
                // Replace WP prefix and WPDA prefix.
                $this->file_content = str_replace( '{wp_schema}', $wpdb->dbname, $this->file_content );
                $this->file_content = str_replace( '{wp_prefix}', $wpdb->prefix, $this->file_content );
                $this->file_content = str_replace( '{wpda_prefix}', 'wpda', $this->file_content );
                // for backward compatibility
                // Find and process SQL statements.
                $sql_end_unix = strpos( $this->file_content, ";\n" );
                $sql_end_windows = strpos( $this->file_content, ";\r\n" );
                while ( false !== $sql_end_unix || false !== $sql_end_windows ) {
                    if ( false === $sql_end_unix ) {
                        $sql_end = $sql_end_windows;
                    } elseif ( false === $sql_end_windows ) {
                        $sql_end = $sql_end_unix;
                    } else {
                        $sql_end = min( $sql_end_unix, $sql_end_windows );
                    }
                    $sql = rtrim( substr( $this->file_content, 0, $sql_end ) );
                    $this->file_content = substr( $this->file_content, strpos( $this->file_content, $sql ) + strlen( $sql ) + 1 );
                    if ( false === $wpdadb->query( $sql ) ) {
                        $errors[] = $wpdadb->last_error;
                    }
                    // Find next SQL statement.
                    $sql_end_unix = strpos( $this->file_content, ";\n" );
                    $sql_end_windows = strpos( $this->file_content, ";\r\n" );
                }
            }
        }
        $wpdadb->suppress_errors( $suppress );
        // Process file content.
        if ( 0 < count( $errors ) ) {
            return array(
                'status' => 'error',
                'msg'    => sprintf( __( 'Import `%s` failed [check import file]', 'wp-data-access' ), $file_name ),
                'errors' => $errors,
            );
        } else {
            // Import succeeded.
            return array(
                'status' => 'ok',
                'msg'    => sprintf( __( 'Import `%s` completed succesfully', 'wp-data-access' ), $file_name ),
                'errors' => $errors,
            );
        }
    }

}
