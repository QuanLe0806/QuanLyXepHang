<?php

namespace Tablesome\Includes\Modules\Workflow\Integrations\Triggers;

use Tablesome\Includes\Modules\Workflow\Trigger;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Integrations\Triggers\Forminator')) {
    class Forminator extends Trigger {

        public $unsupported_formats = array(
            'stripe',
            'paypal',
            'captcha',
        );

        public static $instance = null;

        public static function get_instance() {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function get_config() {
            $is_active = class_exists('Forminator') ? true : false;
            return array(
                'integration' => 'forminator',
                'integration_label' => __('Forminator', 'tablesome'),
                'trigger' => 'tablesome_forminator_form_submit',
                'trigger_id' => 4,
                'trigger_label' => __('On Form Submit', 'tablesome'),
                'trigger_type' => 'forms',
                'is_active' => $is_active,
                'is_premium' => "no",
                'wp_hook' => array(
                    'priority' => 10,
                    'accepted_args' => 3,
                    'name' => 'forminator_custom_form_submit_before_set_fields',
                ),
            );
        }

        public function trigger_callback($entry, $form_id, $fields_data) {
            $entry_id = $entry->entry_id;

            $form = \Forminator_API::get_form($form_id);
            $submission_data = $this->get_formatted_posted_data($fields_data, $form);

            $this->trigger_source_id = $form_id;
            $this->trigger_source_data = array(
                'integration' => $this->get_config()['integration'],
                'form_title' => isset($form->settings['formName']) ? $form->settings['formName'] : 'Untitled Table - ' . $form_id,
                'form_id' => $form_id,
                'data' => $submission_data,
            );

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

        public function get_collection() {
            $collection = array();
            if (!class_exists('Forminator')) {
                return $collection;
            }
            $forms = \Forminator_API::get_forms();

            if (empty($forms)) {
                return $collection;
            }

            foreach ($forms as $form) {

                $collection[] = array(
                    'form_id' => $form->id,
                    'form_title' => $form->settings['formName'] . " (ID: " . $form->id . ")",
                    'form_type' => 'forminator',
                    'fields' => $this->get_form_fields($form),
                );
            }
            return $collection;
        }

        public function get_form_fields($form) {
            $form_fields = array();
            $fields = isset($form->fields) ? $form->fields : [];
            if (empty($fields)) {
                return [];
            }

            foreach ($fields as $field_obj) {

                $type = $field_obj->__get('type');
                $id = $field_obj->__get('element_id');
                $label = $field_obj->__get('field_label');
                if (in_array($type, $this->unsupported_formats)) {
                    continue;
                }

                $field = array(
                    'id' => $id,
                    'name' => !empty($label) ? $label : $id,
                    'type' => $type,
                );

                $options = $field_obj->__get('options');

                if (!empty($options)) {
                    $field['options'] = $options;
                }
                $form_fields[] = $field;
            }
            return $form_fields;
        }

        public function get_formatted_posted_data($fields_data, $form_obj) {
            $data = array();
            foreach ($fields_data as $field_data) {
                $name = $field_data['name'];

                if (in_array($name, ['_forminator_user_ip'])) {
                    continue;
                }

                $field = \Forminator_API::get_form_field($form_obj->id, $name, true);

                if (is_wp_error($field)) {
                    continue;
                }

                $type = $field['type'];

                if (in_array($type, $this->unsupported_formats)) {
                    continue;
                }

                $value = isset($field_data['value']) ? $field_data['value'] : '';

                if ($type == 'date') {
                    $date_obj = date_parse($value);

                    if (isset($date_obj) && !empty($date_obj)) {
                        $date = $date_obj['year'] . '-' . $date_obj['month'] . '-' . $date_obj['day'];
                        $unix_timestamp = convert_tablesome_date_to_unix_timestamp($date, 'Y-m-d');
                        $unix_timestamp = $unix_timestamp * 1000; // convert to milliseconds
                    }

                } else if ($type == 'postdata') {
                    /** Get the Post ID */
                    $value = isset($value['postdata']) ? $value['postdata'] : 0;
                } else if ($type == 'upload') {
                    $file = isset($value['file']) ? $value['file'] : '';
                    $value = '';
                    if (isset($file['success']) && $file['success'] == 1 && isset($file['file_url'])) {
                        $value = is_array($file['file_url']) ? implode(',', $file['file_url']) : $file['file_url'];
                    }
                } else {
                    if (is_array($value) && !empty($value)) {
                        if ($type == 'time') {
                            $value = sprintf('%02d', $value['hours']) . ':' . sprintf('%02d', $value['minutes']) . ' ' . $value['ampm'];
                        } else if ($type == 'calculation') {
                            $value = isset($value['result']) ? $value['result'] : '';
                        } else if ($type == 'name') {
                            $value = implode(' ', $value);
                        } else {
                            $value = implode(', ', $value);
                        }
                    }
                }

                $data[$name] = array(
                    'label' => isset($field['field_label']) && !empty($field['field_label']) ? $field['field_label'] : $name,
                    'value' => $value,
                    'type' => $type,
                    'unix_timestamp' => isset($unix_timestamp) ? $unix_timestamp : '', // use this prop when the column format type is date
                );
            }

            return $data;
        }

    }
}