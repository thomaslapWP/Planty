<?php

namespace WPDataAccess\Plugin_Table_Models;

use WPDataAccess\WPDA;
class WPDA_App_Container_Model extends WPDA_Plugin_Table_Base_Model {
    const BASE_TABLE_NAME = 'wpda_app_container';

    public static function select( $app_id, $cnt_seq_nr ) {
        global $wpdb;
        if ( 0 === $cnt_seq_nr ) {
            return $wpdb->get_results( 
                $wpdb->prepare( 
                    'SELECT * FROM `%1s` WHERE app_id = %d and cnt_seq_nr = 0 order by cnt_id',
                    // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
                    array(WPDA::remove_backticks( self::get_base_table_name() ), $app_id)
                 ),
                // db call ok; no-cache ok.
                'ARRAY_A'
             );
            // phpcs:ignore Standard.Category.SniffName.ErrorCode
        } else {
            return $wpdb->get_results( 
                $wpdb->prepare( 
                    'SELECT * FROM `%1s` WHERE app_id = %d and cnt_seq_nr > 0 order by cnt_seq_nr, cnt_id',
                    // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
                    array(WPDA::remove_backticks( self::get_base_table_name() ), $app_id)
                 ),
                // db call ok; no-cache ok.
                'ARRAY_A'
             );
            // phpcs:ignore Standard.Category.SniffName.ErrorCode
        }
    }

    public static function select_all( $app_id ) {
        global $wpdb;
        return $wpdb->get_results( 
            $wpdb->prepare( 
                'SELECT * FROM `%1s` WHERE app_id = %d order by cnt_seq_nr',
                // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
                array(WPDA::remove_backticks( self::get_base_table_name() ), $app_id)
             ),
            // db call ok; no-cache ok.
            'ARRAY_A'
         );
        // phpcs:ignore Standard.Category.SniffName.ErrorCode
    }

    public static function get_container( $cnt_id ) {
        global $wpdb;
        return $wpdb->get_results( 
            $wpdb->prepare( 
                'SELECT * FROM `%1s` WHERE cnt_id = %d',
                // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
                array(WPDA::remove_backticks( self::get_base_table_name() ), $cnt_id)
             ),
            // db call ok; no-cache ok.
            'ARRAY_A'
         );
        // phpcs:ignore Standard.Category.SniffName.ErrorCode
    }

    public static function create(
        $app_id,
        $app_dbs,
        $app_tbl,
        $app_cls,
        $cnt_title,
        $cnt_seq_nr,
        $cnt_table,
        $cnt_relation = null
    ) {
        global $wpdb;
        if ( 1 === $cnt_seq_nr ) {
            $max_seq_nr = $wpdb->get_results( 
                $wpdb->prepare( 
                    'SELECT max(cnt_seq_nr) FROM `%1s` WHERE app_id = %d',
                    // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
                    array(WPDA::remove_backticks( self::get_base_table_name() ), $app_id)
                 ),
                // db call ok; no-cache ok.
                'ARRAY_N'
             );
            // phpcs:ignore Standard.Category.SniffName.ErrorCode
            if ( isset( $max_seq_nr[0][0] ) ) {
                $cnt_seq_nr = $max_seq_nr[0][0] + 1;
            }
        }
        if ( 1 === $wpdb->insert( static::get_base_table_name(), array(
            'cnt_dbs'      => $app_dbs,
            'cnt_tbl'      => $app_tbl,
            'cnt_cls'      => $app_cls,
            'cnt_title'    => $cnt_title,
            'app_id'       => $app_id,
            'cnt_seq_nr'   => $cnt_seq_nr,
            'cnt_table'    => $cnt_table,
            'cnt_relation' => $cnt_relation,
        ) ) ) {
            // Return new container
            $cnt_id = $wpdb->insert_id;
            return array(
                'cnt_id' => $wpdb->insert_id,
                'msg'    => '',
            );
        } else {
            return array(
                'cnt_id' => false,
                'msg'    => $wpdb->last_error,
            );
        }
    }

    public static function update(
        $app_id,
        $app_cnt,
        $app_dbs,
        $app_tbl,
        $app_cls,
        $cnt_title,
        $cnt_seq_nr,
        $cnt_table,
        $cnt_relation = null
    ) {
        global $wpdb;
        $wpdb->update( static::get_base_table_name(), array(
            'cnt_dbs'      => $app_dbs,
            'cnt_tbl'      => $app_tbl,
            'cnt_cls'      => $app_cls,
            'cnt_title'    => $cnt_title,
            'cnt_seq_nr'   => $cnt_seq_nr,
            'cnt_table'    => $cnt_table,
            'cnt_relation' => $cnt_relation,
        ), array(
            'app_id' => $app_id,
            'cnt_id' => $app_cnt,
        ) );
        return $wpdb->last_error;
    }

    public static function delete( $app_id ) {
        global $wpdb;
        return $wpdb->delete( static::get_base_table_name(), array(
            'app_id' => $app_id,
        ) );
    }

    public static function delete_container( $cnt_id ) {
        global $wpdb;
        return $wpdb->delete( static::get_base_table_name(), array(
            'cnt_id' => $cnt_id,
        ) );
    }

    public static function update_master(
        $app_id,
        $app_dbs,
        $app_tbl,
        $app_cls
    ) {
        global $wpdb;
        $wpdb->update( static::get_base_table_name(), array(
            'cnt_dbs' => $app_dbs,
            'cnt_tbl' => $app_tbl,
            'cnt_cls' => $app_cls,
        ), array(
            'app_id'     => $app_id,
            'cnt_seq_nr' => 0,
        ) );
        return $wpdb->last_error;
    }

    public static function update_table_settings( $cnt_id, $cnt_table_settings ) {
        global $wpdb;
        $wpdb->update( static::get_base_table_name(), array(
            'cnt_table' => $cnt_table_settings,
        ), array(
            'cnt_id' => $cnt_id,
        ) );
        return $wpdb->last_error;
    }

    public static function update_form_settings( $cnt_id, $cnt_form_settings ) {
        global $wpdb;
        $wpdb->update( static::get_base_table_name(), array(
            'cnt_form' => $cnt_form_settings,
        ), array(
            'cnt_id' => $cnt_id,
        ) );
        return $wpdb->last_error;
    }

    public static function container_move( $cnt_id_from, $cnt_id_to ) {
        // This is a premium feature.
        return '';
    }

    public static function copy( $app_id_old, $app_id_new ) {
        global $wpdb;
        $containers = $wpdb->get_results( 
            $wpdb->prepare( 
                'SELECT * FROM `%1s` WHERE app_id = %d order by cnt_seq_nr',
                // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
                array(WPDA::remove_backticks( self::get_base_table_name() ), $app_id_old)
             ),
            // db call ok; no-cache ok.
            'ARRAY_A'
         );
        // phpcs:ignore Standard.Category.SniffName.ErrorCode
        $cnt_id_master = null;
        foreach ( $containers as $container ) {
            if ( 0 !== $container['cnt_seq_nr'] && '0' !== $container['cnt_seq_nr'] ) {
                $relation = $container['cnt_relation'];
                if ( null !== $relation && '' !== $relation ) {
                    $json = json_decode( $relation, true );
                    if ( isset( $json['cnt_id_master'] ) ) {
                        $json['cnt_id_master'] = strval( $cnt_id_master );
                        $container['cnt_relation'] = json_encode( $json );
                    }
                }
            }
            $container['app_id'] = $app_id_new;
            unset($container['cnt_id']);
            $wpdb->insert( static::get_base_table_name(), $container );
            if ( 0 === $container['cnt_seq_nr'] || '0' === $container['cnt_seq_nr'] ) {
                $cnt_id_master = $wpdb->insert_id;
            }
        }
    }

}
