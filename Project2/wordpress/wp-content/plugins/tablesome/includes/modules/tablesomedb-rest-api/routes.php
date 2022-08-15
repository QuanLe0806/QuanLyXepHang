<?php

namespace Tablesome\Includes\Modules\TablesomeDB_Rest_Api;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Ref:
 * https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/#arguments
 */

if (!class_exists('\Tablesome\Includes\Modules\TablesomeDB_Rest_Api\Routes')) {
    class Routes
    {

        public function get_routes()
        {
            $rest = new \Tablesome\Includes\Modules\TablesomeDB_Rest_Api\TablesomeDB_Rest_Api();

            return array(

                /** Import Records */
                array(
                    'url' => '/tables/import',
                    'args' => array(
                        'methods' => \WP_REST_Server::EDITABLE,
                        'callback' => array(new \Tablesome\Includes\Modules\TablesomeDB_Rest_Api\Import(), 'import_records'),
                        'permission_callback' => array($rest, 'api_access_permission'),
                    ),
                ),

                // get export table
                array(
                    'url' => '/tables/(?P<table_id>\d+)/export',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array(new \Tablesome\Components\Export(), 'get_export_table_props'),
                        'args' => array(
                            'table_id' => array(
                                'required' => true,
                                'type' => 'number',
                            ),
                        ),
                        'permission_callback' => '__return_true',
                    ),
                ),

                /** Get all Tables */
                array(
                    'url' => '/tables',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($rest, 'get_tables'),
                        'permission_callback' => '__return_true',
                    ),
                ),

                /*** Get table by table_id  */
                array(
                    'url' => '/tables/(?P<table_id>\d+)',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($rest, 'get_table_data'),
                        'args' => array(
                            'table_id' => array(
                                'required' => true,
                                'type' => 'number',
                            ),
                        ),
                        'permission_callback' => '__return_true',
                    ),
                ),

                /** create (or) update the table */
                array(
                    'url' => '/tables',
                    'args' => array(
                        'methods' => \WP_REST_Server::EDITABLE,
                        'callback' => array($rest, 'create_or_update_table'),
                        'permission_callback' => array($rest, 'api_access_permission'),
                    ),
                ),

                /*** Delete Table */
                array(
                    'url' => '/tables',
                    'args' => array(
                        'methods' => \WP_REST_Server::DELETABLE,
                        'callback' => array($rest, 'delete'),
                        'permission_callback' => array($rest, 'api_access_permission'),
                    ),
                ),

                /** Get Records From table */
                array(
                    'url' => '/tables/(?P<table_id>\d+)/records',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($rest, 'get_table_records'),
                        'permission_callback' => '__return_true',
                        'args' => array(
                            'table_id' => array(
                                'required' => true,
                                'type' => 'number',
                            ),
                        ),
                    ),
                ),

                /** Save & update records */
                array(
                    'url' => '/tables/(?P<table_id>\d+)/records',
                    'args' => array(
                        'methods' => \WP_REST_Server::EDITABLE,
                        'callback' => array($rest, 'modified_records'),
                        'permission_callback' => array($rest, 'api_access_permission'),
                        'args' => array(
                            'table_id' => array(
                                'required' => true,
                                'type' => 'number',
                            ),
                        ),
                    ),
                ),

                /*** Delete Table Records */
                array(
                    'url' => '/tables/(?P<table_id>\d+)/records',
                    'args' => array(
                        'methods' => \WP_REST_Server::DELETABLE,
                        'callback' => array($rest, 'delete_records'),
                        'permission_callback' => array($rest, 'api_access_permission'),
                        'args' => array(
                            'table_id' => array(
                                'required' => true,
                                'type' => 'number',
                            ),
                        ),
                    ),
                ),
                array(
                    'url' => '/tablesome-api-keys',
                    'args' => array(
                        'methods' => \WP_REST_Server::EDITABLE,
                        'callback' => array(new \Tablesome\Includes\Modules\Workflow\External_Apis\Api_Connect(), 'add_or_update_api_keys'),
                        'permission_callback' => array($rest, 'api_access_permission'),
                    ),
                ),
            );
        }

    }
}