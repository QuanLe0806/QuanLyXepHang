<?php

namespace GSWPTS\includes\classes\controller;

use GSWPTS\includes\classes\controller\ajax\FetchProducts;
use GSWPTS\includes\classes\controller\ajax\ManageNotices;
use GSWPTS\includes\classes\controller\ajax\RemoteClass;
use GSWPTS\includes\classes\controller\ajax\SheetCreation;
use GSWPTS\includes\classes\controller\ajax\SheetFetching;
use GSWPTS\includes\classes\controller\ajax\TabChanges;
use GSWPTS\includes\classes\controller\ajax\TabFetch;
use GSWPTS\includes\classes\controller\ajax\TableFetch;
use GSWPTS\includes\classes\controller\ajax\TabNameToggle;
use GSWPTS\includes\classes\controller\ajax\UdTables;

defined('ABSPATH') || wp_die(__('You can\'t access this page', 'sheetstowptable'));

class AjaxHandler {

    public function __construct() {
        $this->events();
    }

    public function events() {
        /* Sheet Creation */
        add_action('wp_ajax_gswpts_sheet_create', [$this, 'sheet_creation']);
        add_action('wp_ajax_nopriv_gswpts_sheet_create', [$this, 'sheet_creation']);

        /* Sheet Fetching */
        add_action('wp_ajax_gswpts_sheet_fetch', [$this, 'sheet_fetch']);
        add_action('wp_ajax_nopriv_gswpts_sheet_fetch', [$this, 'sheet_fetch']);

        /* Table Fetching */
        add_action('wp_ajax_gswpts_table_fetch', [$this, 'table_fetch']);
        add_action('wp_ajax_nopriv_gswpts_table_fetch', [$this, 'table_fetch']);

        /* Update and delete table */
        add_action('wp_ajax_gswpts_ud_table', [$this, 'ud_tables']);

        /* Other product fetching */
        add_action('wp_ajax_gswpts_product_fetch', [$this, 'fetch_products']);
        add_action('wp_ajax_nopriv_gswpts_product_fetch', [$this, 'fetch_products']);

        /* User subscription */
        add_action('wp_ajax_gswpts_user_subscribe', [$this, 'userSubscribe']);
        add_action('wp_ajax_nopriv_gswpts_user_subscribe', [$this, 'userSubscribe']);

        /* Ger wppool post */
        add_action('wp_ajax_gswpts_get_posts', [$this, 'getPosts']);
        add_action('wp_ajax_nopriv_gswpts_get_posts', [$this, 'getPosts']);

        /* Ger wppool post */
        add_action('wp_ajax_gswpts_notice_action', [$this, 'manageNoticeActions']);
        add_action('wp_ajax_nopriv_gswpts_notice_action', [$this, 'manageNoticeActions']);

        /* Manage table tabs */
        add_action('wp_ajax_gswpts_manage_tab', [$this, 'manageTab']);

        /* Manage table tabs */
        add_action('wp_ajax_gswpts_tab_changes', [$this, 'tabChanges']);

        /* Update and delete table */
        add_action('wp_ajax_gswpts_ud_tab', [$this, 'ud_tables']);

        /* Update and delete table */
        add_action('wp_ajax_gswpts_manage_tab_toggle', [$this, 'manageTabToggleValue']);
    }

    public function sheet_creation() {
        $sheet_creation = new SheetCreation();
        $sheet_creation->sheet_creation();
    }

    public function sheet_fetch() {
        $sheet_fetching = new SheetFetching();
        $sheet_fetching->sheet_fetch();
    }

    public function table_fetch() {
        $table_fetching = new TableFetch();
        $table_fetching->table_fetch();
    }

    public function ud_tables() {
        $ud_tables = new UdTables();
        $ud_tables->ud_tables();
    }

    public function fetch_products() {
        $ud_tables = new FetchProducts();
        $ud_tables->fetch_products();
    }

    public function userSubscribe() {
        $remoteClass = new RemoteClass();
        $remoteClass->subscriptionRequest();
    }

    public function getPosts() {
        $remoteClass = new RemoteClass();
        $remoteClass->getPostRequest();
    }

    public function manageNoticeActions() {
        $remoteClass = new ManageNotices();
        $remoteClass->manageNotices();
    }

    public function manageTab() {
        $remoteClass = new TabFetch();
        $remoteClass->manageTabs();
    }

    public function tabChanges() {
        $remoteClass = new TabChanges();
        $remoteClass->tabChanges();
    }

    public function manageTabToggleValue() {
        $remoteClass = new TabNameToggle();
        $remoteClass->tabNameToggle();
    }
}