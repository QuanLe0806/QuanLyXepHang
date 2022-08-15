<?php

use Tablesome\Includes\Modules\Workflow\Workflow_Manager;

if (!function_exists('set_tablesome_data')) {
    function set_tablesome_data($table_id, $props) {
        $options = isset($props['options']) ? $props['options'] : [];
        $columns = isset($props['columns']) ? $props['columns'] : [];
        $rows = isset($props['rows']) ? $props['rows'] : [];
        $last_column_id = isset($props['meta']['last_column_id']) ? $props['meta']['last_column_id'] : 0;

        // already inserted to db
        $data = get_tablesome_data($table_id);
        $update_data = [
            'options' => $options,
            'columns' => $columns,
            'meta' => [
                'last_column_id' => $last_column_id,
            ],
        ];

        $tablesome_data = apply_filters('tablesome_data', $data, $update_data);
        // error_log('tablesome_data : ' . print_r($tablesome_data, true));
        update_post_meta($table_id, 'tablesome_data', $tablesome_data);

        return $tablesome_data;
    }
}

if (!function_exists('get_tablesome_data')) {
    function get_tablesome_data($table_id) {
        $table_data = \get_post_meta($table_id, 'tablesome_data');
        $table_data = isset($table_data[0]) && !empty($table_data[0]) ? $table_data[0] : [];

        return $table_data;
    }
}

if (!function_exists('get_tablesome_cell_type')) {
    function get_tablesome_cell_type($column_id, $columns = []) {
        $cell_type = 'text';

        if (!empty($columns)) {
            foreach ($columns as $column) {
                if ($column['id'] == $column_id) {
                    $cell_type = $column['format'];
                    break;
                }
            }
        }

        return $cell_type;
    }
}

if (!function_exists('get_tablesome_string')) {
    function get_tablesome_string($stringName) {
        // only set one time
        if (!isset($strings) || empty($strings)) {
            $translations = new \Tablesome\Includes\Translations();
            $strings = $translations->get_strings();
        }

        // Searched string is not exist display error for Developer insights
        if (!isset($strings[$stringName]) && empty($strings[$stringName])) {
            wp_die('"' . $stringName . '" translation string is not exist, Please add the given string in the translations.php file.');
        }

        return $strings[$stringName];
    }
}

if (!function_exists('get_tablesome_table_edit_url')) {
    function get_tablesome_table_edit_url($table_id) {
        $url = admin_url('edit.php?post_type=' . TABLESOME_CPT . '&action=edit&post=' . $table_id . '&page=tablesome_admin_page');
        return $url;
    }
}

if (!function_exists('splice_associative_array')) {
    function splice_associative_array($original_data, $position, $replacement_array) {
        /**
         *  Appending the $replacement_array array in $original_data array, set the $position ad -1.
         *  Add the $replacement_array array in top of the $original_data array, then set the $position as 0.
         *
         */

        $data = array_slice($original_data, 0, $position, true) +
        $replacement_array +
        array_slice($original_data, 0, count($original_data), true);

        return $data;
    }
}
if (!function_exists('get_app_memory_usage')) {
    function get_app_memory_usage() {
        $mem_usage = memory_get_usage(true);
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($mem_usage / pow(1024, ($i = floor(log($mem_usage, 1024)))), 2) . ' ' . $unit[$i];
    }
}

if (!function_exists('pauple_is_feature_active')) {
    function pauple_is_feature_active($feature_name) {
        $json = file_get_contents(__DIR__ . '/data/features.json');

        $features_array = json_decode($json);

        // error_log("json: " . print_r($json, true));
        // error_log("features_array: " . print_r($features_array, true));

        return isset($features_array->$feature_name) ? $features_array->$feature_name : false;
    }
}

if (!function_exists('set_tablesome_table_triggers')) {
    function set_tablesome_table_triggers($table_id, $triggers_data) {
        if (empty($table_id)) {return [];}

        \update_post_meta($table_id, 'tablesome_table_triggers', $triggers_data);

        return get_tablesome_table_triggers($table_id);
    }
}

if (!function_exists('get_tablesome_table_triggers')) {
    function get_tablesome_table_triggers($table_id) {
        if (empty($table_id)) {return [];}
        $table_triggers_data = \get_post_meta($table_id, 'tablesome_table_triggers', true);
        // $table_triggers_data = isset($table_triggers_data[0]) && !empty($table_triggers_data[0]) ? $table_triggers_data[0] : [];
        // error_log(' table_triggers_data : ' . print_r($table_triggers_data, true));
        return $table_triggers_data;
    }
}
// A Callback function for csf field
if (!function_exists('tablesome_mailchimp_settings_callback')) {
    function tablesome_mailchimp_settings_callback() {
        echo '<div id="tablesome-mailchimp-settings"></div>';
    }
}

// A Callback function for csf field
if (!function_exists('tablesome_notion_settings_callback')) {
    function tablesome_notion_settings_callback() {
        echo '<div id="tablesome-notion-settings"></div>';
    }
}

if (!function_exists('get_tablesome_insights_data')) {
    function get_tablesome_insights_data() {
        $insights_data = get_option(TABLESOME_INSIGHTS_DATA_OPTION);
        $insights_data = isset($insights_data) && !empty($insights_data) && is_array($insights_data) ? $insights_data : [];
        return $insights_data;
    }
}

if (!function_exists('set_tablesome_insights_data')) {
    function set_tablesome_insights_data($data) {
        \update_option(TABLESOME_INSIGHTS_DATA_OPTION, $data);

        return get_tablesome_insights_data();
    }
}

if (!function_exists('tablesome_multi_array_diff_assoc')) {
    function tablesome_multi_array_diff_assoc($array1, $array2) {
        $difference = [];
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!array_key_exists($key, $array2)) {
                    $difference[$key] = $value;
                } elseif (!is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $multidimensionalDiff = tablesome_multi_array_diff_assoc($value, $array2[$key]);
                    if (count($multidimensionalDiff) > 0) {
                        $difference[$key] = $multidimensionalDiff;
                    }
                }
            } else {
                if (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                    $difference[$key] = $value;
                }
            }
        }
        return $difference;
    }
}

if (!function_exists('is_valid_tablesome_date')) {
    function is_valid_tablesome_date($date, $format = 'Y-m-d H:i:s') {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}

if (!function_exists('convert_tablesome_date_to_unix_timestamp')) {
    function convert_tablesome_date_to_unix_timestamp($date, $format = 'Y-m-d H:i:s') {
        $d = \DateTime::createFromFormat($format, $date);
        return $d->getTimestamp();
    }
}

if (!function_exists('get_default_tablesome_smart_fields')) {
    function get_default_tablesome_smart_fields() {
        return [
            [
                'column_id' => 0,
                'column_label' => 'Submission Date',
                'column_format' => 'date',
                'column_status' => 'pending',
                'field_name' => 'created_at',
                'field_type' => 'tablesome_smart_fields',
                'detection_mode' => 'enabled',
            ],
            // [
            //     'column_id' => 0,
            //     'column_label' => 'Created By',
            //     'column_format' => 'number',
            // 'column_status' => 'pending',
            //     'field_name' => 'created_by',
            //     'field_type' => 'tablesome_smart_fields',
            //     'is_enabled' => false,
            // ],
            [
                'column_id' => 0,
                'column_label' => 'IP Address',
                'column_format' => 'text',
                'column_status' => 'pending',
                'field_name' => 'ip_address',
                'field_type' => 'tablesome_smart_fields',
                'detection_mode' => 'disabled',
            ],
            [
                'column_id' => 0,
                'column_label' => 'Page Source URL',
                'column_format' => 'url',
                'column_status' => 'pending',
                'field_name' => 'page_source_url',
                'field_type' => 'tablesome_smart_fields',
                'detection_mode' => 'disabled',
            ],
        ];
    }
}

if (!function_exists('get_tablesome_smart_field_info_by_field_name')) {
    function get_tablesome_smart_field_info_by_field_name($field_name) {
        // Set default values to avoid undefined index error
        $data = [
            'column_label' => 'Undefined Column',
            'column_format' => 'text',
        ];

        foreach (get_default_tablesome_smart_fields() as $smart_field) {
            if ($field_name == $smart_field['field_name']) {
                $data = $smart_field;
                break;
            }
        }
        return $data;
    }
}

if (!function_exists('get_tablesome_request_url')) {
    function get_tablesome_request_url() {
        $home_url = untrailingslashit(home_url());
        $referer = isset($_SERVER['HTTP_REFERER'])
        ? trim($_SERVER['HTTP_REFERER']) : '';

        if ($referer
            && 0 === strpos($referer, $home_url)) {
            return esc_url_raw($referer);
        }

        return esc_url_raw(home_url(add_query_arg(array())));
    }
}

if (!function_exists('get_tablesome_ip_address')) {
    function get_tablesome_ip_address() {
        $ip_addr = '';
        if (isset($_SERVER['REMOTE_ADDR']) && \WP_Http::is_ip_address($_SERVER['REMOTE_ADDR'])) {
            $ip_addr = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip_addr = 'UNKNOWN';
        }
        return $ip_addr;
        // $ipaddress = '';
        // if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        //     $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        // } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //     $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        // } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
        //     $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        // } else if (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
        //     $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        // } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        //     $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        // } else if (isset($_SERVER['HTTP_FORWARDED'])) {
        //     $ipaddress = $_SERVER['HTTP_FORWARDED'];
        // } else if (isset($_SERVER['REMOTE_ADDR'])) {
        //     $ipaddress = $_SERVER['REMOTE_ADDR'];
        // } else {
        //     $ipaddress = 'UNKNOWN';
        // }
        // return $ipaddress;
    }
}

if (!function_exists('is_valid_tablesome_email')) {
    function is_valid_tablesome_email($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return true;
    }
}

if (!function_exists('tablesome_workflow_manager')) {
    function tablesome_workflow_manager() {
        $instance = Workflow_Manager::get_instance();
        return $instance;
    }
}

if (!function_exists('tablesome_json_encode')) {
    function tablesome_json_encode($data) {
        $encoded_data = json_encode($data);

        if (json_last_error()) {
            $encoded_data = json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR);
        }
        if ($encoded_data !== false) {
            return $encoded_data;
        } else {
            wp_die("json_encode fail: " . json_last_error_msg());
        }
    }
}