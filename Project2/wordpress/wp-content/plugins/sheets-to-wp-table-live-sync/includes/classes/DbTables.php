<?php

namespace GSWPTS\includes\classes;

defined('ABSPATH') || wp_die(__('You can\'t access this page', 'sheetstowptable'));

final class DbTables {
    /**
     * @var mixed
     */
    private $connection;
    /**
     * @var mixed
     */
    private $sql;
    public function __construct() {
        $this->connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        global $wpdb;
        $collate = $wpdb->get_charset_collate();

        /* This will create this pluign main table */
        $table = $wpdb->prefix . 'gswpts_tables';
        $this->sql = "CREATE TABLE " . $table . " (
                                    `id` INT(255) NOT NULL AUTO_INCREMENT,
                                    `table_name` VARCHAR(255) DEFAULT NULL,
                                    `source_url` LONGTEXT,
                                    `source_type` VARCHAR(255),
                                    `table_settings` LONGTEXT,
                                    PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB " . $collate . "";
        $this->create_tables();

        /* This will create a table that will manage the plugins user created tab */
        $table = $wpdb->prefix . 'gswpts_tabs';
        $this->sql = "CREATE TABLE " . $table . " (
                                     `id` INT(255) NOT NULL AUTO_INCREMENT,
                                     `tab_name` VARCHAR(255) NOT NULL,
                                     `show_name` BOOLEAN,
                                     `reverse_mode` BOOLEAN,
                                     `tab_settings` LONGTEXT NOT NULL,
                                     PRIMARY KEY (`id`)
                                 ) ENGINE=InnoDB " . $collate . "";
        $this->create_tables();
    }

    public function create_tables() {
        $this->connection->query($this->sql);
    }
}