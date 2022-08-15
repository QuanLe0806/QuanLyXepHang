<?php

namespace Tablesome\Includes\Modules\Workflow\Integrations\Triggers;

use Tablesome\Includes\Modules\Workflow\Trigger;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Integrations\Triggers\Cf7')) {
    class Cf7 extends Trigger {
        /**
         * Define the un-supported fields in CF7
         *
         */
        public $unsupported_formats = array(
            'submit',
            'file',
        );

        public function get_config() {
            $is_active = class_exists('WPCF7') ? true : false;

            return array(
                'integration' => 'cf7',
                'integration_label' => __('Contact Form 7', 'tablesome'),
                'trigger' => 'tablesome_cf7_form_submit',
                'trigger_id' => 1,
                'trigger_label' => __('On Form Submit', 'tablesome'),
                'trigger_type' => 'forms',
                'is_active' => $is_active,
                'is_premium' => "no",
                'wp_hook' => array(
                    'priority' => 10,
                    'accepted_args' => 1,
                    'name' => 'wpcf7_before_send_mail',
                ),
            );
        }

        public function get_forms() {
            $forms = array();

            $posts = get_posts(array(
                'post_type' => 'wpcf7_contact_form',
                'numberposts' => -1,
            ));

            if (empty($posts)) {
                return $forms;
            }

            foreach ($posts as $post) {
                $forms[] = array(
                    'form_id' => $post->ID,
                    'form_title' => $post->post_title . " (ID: " . $post->ID . ")",
                    'form_type' => 'cf7',
                );
            }

            return $forms;
        }

        public function get_collection() {
            $forms = $this->get_forms();
            if (empty($forms)) {
                return [];
            }

            foreach ($forms as $index => $form) {
                $form_id = $form['form_id'];
                // Get form fields
                $forms[$index]['fields'] = $this->get_form_fields($form_id);
            }

            return $forms;
        }

        public function get_form_fields($form_id, array $args = array()) {
            if (!is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
                return [];
            }

            $form = \WPCF7_ContactForm::get_instance($form_id);
            $fields_object = $form->scan_form_tags();
            $fields = $this->get_fields($fields_object);
            return $fields;
        }

        public function get_fields($fields_object) {
            $fields = array();
            if (empty($fields_object)) {
                return $fields;
            }
            foreach ($fields_object as $field_object) {
                $basetype = isset($field_object['basetype']) ? $field_object['basetype'] : '';
                $name = isset($field_object['name']) ? $field_object['name'] : '';
                if (!empty($name) && !in_array($basetype, $this->unsupported_formats)) {

                    $field = [
                        "id" => $name,
                        "name" => $name,
                        "type" => $basetype,
                    ];

                    $have_options = (isset($field_object['values']) && !empty($field_object['values']));
                    if (in_array($basetype, ['select', 'checkbox', 'radio']) && $have_options) {
                        $field['options'] = $this->get_formatted_options($field_object);
                    }

                    $fields[] = $field;

                }
            }
            return $fields;
        }

        public function trigger_callback($wpcf7) {
            error_log('*** CF7 Form Submitted ***');

            $submission = \WPCF7_Submission::get_instance();
            if (!$submission) {
                return $wpcf7;
            }

            $form_tags = $wpcf7->scan_form_tags();
            // Get all the fields types
            $fields_types = array_column($form_tags, 'basetype', 'name');

            $posted_data = $submission->get_posted_data();
            $submission_data = $this->get_formatted_posted_data($posted_data, $fields_types);

            $this->trigger_source_id = $wpcf7->id();
            $this->trigger_source_data = array(
                'integration' => $this->get_config()['integration'],
                'form_title' => $wpcf7->title(),
                'form_id' => $wpcf7->id(),
                'data' => $submission_data,
            );

            $this->run_triggers($this, $this->trigger_source_data);
        }

        /**
         * Current: Check the current trigger have a single instance.
         * Later: will add more trigger specific conditions.
         */
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

        public function get_formatted_posted_data($posted_data, $fields_types = array()) {
            $data = array();
            foreach ($posted_data as $key => $value) {
                $field_type = isset($fields_types[$key]) ? $fields_types[$key] : '';

                if (is_array($value) && !empty($value)) {
                    $value = implode(',', $value);
                } else if (is_valid_tablesome_date($value, 'Y-m-d')) {
                    $unix_timestamp = convert_tablesome_date_to_unix_timestamp($value, 'Y-m-d');
                    $unix_timestamp = $unix_timestamp * 1000; // convert to milliseconds
                }

                $data[$key] = array(
                    'label' => $key,
                    'value' => $value,
                    'type' => $field_type,
                    'unix_timestamp' => isset($unix_timestamp) ? $unix_timestamp : '', // use this prop when the column format type is date
                );
            }
            return $data;
        }

        public function get_formatted_options($field_object) {
            $options = array();
            foreach ($field_object['values'] as $value) {
                $options[] = array(
                    'value' => $value,
                    'label' => $value,
                );
            }
            return $options;
        }
    }
}