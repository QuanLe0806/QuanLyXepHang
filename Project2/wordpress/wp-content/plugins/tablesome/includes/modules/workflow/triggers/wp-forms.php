<?php

namespace Tablesome\Includes\Modules\Workflow\Integrations\Triggers;

use Tablesome\Includes\Modules\Workflow\Trigger;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Integrations\Triggers\WP_Forms')) {
    class WP_Forms extends Trigger {
        public $unsupported_formats = array(
            'mailchimp',
            'password',
            'file-upload',
            'divider',
            'html',
            'pagebreak',
            'signature',
            'captcha',
            'net_promoter_score',
            'captcha_recaptcha',
        );

        public static $instance = null;

        public static function get_instance() {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function get_config() {
            $is_active = class_exists('WPForms') ? true : false;
            return array(
                'integration' => 'wpforms',
                'integration_label' => __('WPForms', 'tablesome'),
                'trigger' => 'tablesome_wpforms_form_submit',
                'trigger_id' => 2,
                'trigger_label' => __('On Form Submit', 'tablesome'),
                'trigger_type' => 'forms',
                'is_active' => $is_active,
                'is_premium' => "no",
                'wp_hook' => array(
                    'priority' => 10,
                    'accepted_args' => 4,
                    'name' => 'wpforms_process_entry_save',
                ),
            );
        }

        public function get_collection() {
            $forms = $this->get_forms();
            if (empty($forms)) {
                return [];
            }

            foreach ($forms as $index => $form) {
                $forms[$index]['fields'] = $this->get_form_fields($form);

                // Remove the form-content property.
                if (isset($forms[$index]['form_content'])) {
                    unset($forms[$index]['form_content']);
                }
            }
            return $forms;
        }

        public function get_forms() {
            $forms = array();
            $exists = function_exists('wpforms') && method_exists(wpforms()->form, 'get');
            if (!$exists) {
                return $forms;
            }

            $posts = wpforms()->form->get('');

            if (empty($posts)) {
                return $posts;
            }
            foreach ($posts as $post) {
                $forms[] = array(
                    'form_id' => $post->ID,
                    'form_title' => $post->post_title . " (ID: " . $post->ID . ")",
                    'form_type' => 'wpforms',
                    'form_content' => $post->post_content,
                );
            }
            return $forms;
        }

        public function get_form_fields($form, array $args = array()) {
            $form_data = !empty($form['form_content']) ? wpforms_decode($form['form_content']) : '';
            $form_fields = isset($form_data['fields']) ? $form_data['fields'] : [];

            if (empty($form_fields)) {
                return [];
            }
            $fields = array();
            foreach ($form_fields as $form_field) {
                $type = isset($form_field['type']) ? $form_field['type'] : '';
                $label = isset($form_field['label']) && !empty($form_field['label']) ? $form_field['label'] : 'label-' . $form_field['id'];
                if (!in_array($type, $this->unsupported_formats)) {

                    $field = [
                        "id" => $form_field['id'],
                        "name" => $label,
                        "type" => $type,
                    ];

                    $have_options = (isset($form_field['choices']) && !empty($form_field['choices']));
                    if ($have_options) {
                        $field['options'] = $this->get_formatted_options($form_field);
                    }
                    $fields[] = $field;
                }
            }
            return $fields;
        }

        public function trigger_callback($fields, $entry, $form_id, $form_data) {

            // error_log('$fields : ' . print_r($fields, true));
            // error_log('$entry : ' . print_r($entry, true));
            // error_log('$form_data : ' . print_r($form_data, true));

            $submission_data = $this->get_formatted_posted_data($fields, $entry, $form_id, $form_data);

            $this->trigger_source_id = $form_id;
            $this->trigger_source_data = array(
                'integration' => $this->get_config()['integration'],
                'form_title' => $form_data['settings']['form_title'],
                'form_id' => $form_id,
                'data' => $submission_data,
            );

            // Can use this prop when its need. form-settings, fields-settings, meta-info and the conditional fields
            $this->wpforms_data = $form_data;

            $this->run_triggers($this, $this->trigger_source_data);
        }

        public function conditions($trigger_meta, $trigger_data) {
            $integration = isset($trigger_meta['integration']) ? $trigger_meta['integration'] : '';
            $trigger_id = isset($trigger_meta['trigger_id']) ? $trigger_meta['trigger_id'] : '';

            if ($integration != $this->get_config()['integration'] || $trigger_id != $this->get_config()['trigger_id']) {
                return false;
            }

            $trigger_source_id = isset($trigger_meta['form_id']) ? $trigger_meta['form_id'] : 0;
            if (isset($trigger_data['form_id']) && $trigger_data['form_id'] == $trigger_source_id) {
                return true;
            }
            return false;
        }

        public function get_formatted_posted_data($fields, $entry, $form_id, $form_data) {
            $data = array();
            if (empty($fields)) {
                return $data;
            }
            foreach ($fields as $key => $field) {
                $value = isset($field['value']) ? $field['value'] : '';
                $type = isset($field['type']) ? $field['type'] : '';

                if ($type == 'date-time') {
                    /**
                     *  Issue #1093 - For supporting the date field
                     *  In WPForms, they also give the user-submitted date-time value in unix format.
                     *
                     *  Ref: https://stackoverflow.com/questions/4676195/why-do-i-need-to-multiply-unix-timestamps-by-1000-in-javascript
                     */
                    $unix_timestamp = $field['unix'];
                    $unix_timestamp = $unix_timestamp * 1000;
                } else if ($type == 'checkbox' || $type == 'select') {
                    $value = explode("\n", $value);
                    $value = is_array($value) && !empty($value) ? implode(',', $value) : $value;
                }

                $data[$key] = array(
                    'label' => isset($field['name']) ? $field['name'] : '',
                    'value' => $value,
                    'type' => $type,
                    'unix_timestamp' => isset($unix_timestamp) ? $unix_timestamp : '', // use this prop when the column format type is date
                );
            }
            return $data;
        }

        public function get_formatted_options($form_field) {
            $options = array();
            foreach ($form_field['choices'] as $id => $choice_data) {
                $options[] = array(
                    'value' => $id,
                    'label' => $choice_data['label'],
                );
            }
            return $options;
        }

        public function get_field_option_id_by_value($props) {
            $selected_option_id = $props['trigger_value'];
            $field_id = $props['field'];
            $field = isset($this->wpforms_data['fields'][$field_id]) ? $this->wpforms_data['fields'][$field_id] : [];
            $choices = isset($field['choices']) ? $field['choices'] : [];

            if (empty($field) || empty($choices)) {
                return $selected_option_id;
            }

            foreach ($choices as $id => $choice) {
                if ($choice['label'] == $props['trigger_value']) {
                    $selected_option_id = $id;
                    break;
                }
            }

            return $selected_option_id;
        }
    }
}