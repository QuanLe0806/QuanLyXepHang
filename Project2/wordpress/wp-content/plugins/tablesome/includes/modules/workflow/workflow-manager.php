<?php

namespace Tablesome\Includes\Modules\Workflow;

use Tablesome\Includes\Modules\Workflow\Actions\Mailchimp_Add_Contact;
use Tablesome\Includes\Modules\Workflow\Actions\Notion_Database;
use Tablesome\Includes\Modules\Workflow\Actions\Tablesome_Add_Row;
use Tablesome\Includes\Modules\Workflow\Event_Log\Event_Log;
use Tablesome\Includes\Modules\Workflow\Integrations\Mailchimp;
use Tablesome\Includes\Modules\Workflow\Integrations\Notion;
use Tablesome\Includes\Modules\Workflow\Integrations\Tablesome;
use Tablesome\Includes\Modules\Workflow\Integrations\Triggers\Cf7;
use Tablesome\Includes\Modules\Workflow\Integrations\Triggers\Elementor;
use Tablesome\Includes\Modules\Workflow\Integrations\Triggers\Forminator;
use Tablesome\Includes\Modules\Workflow\Integrations\Triggers\WP_Forms;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Workflow_Manager')) {
    class Workflow_Manager {

        public static $instance = null;

        public static function get_instance() {
            if (is_null(self::$instance)) {
                self::$instance = new self();
                self::$instance->init();
            }
            return self::$instance;
        }

        public function init() {

            $this->triggers = array(
                'cf7' => new Cf7(),
                'wpforms' => new WP_Forms(),
                'elementor' => new Elementor(),
                'forminator' => new Forminator(),
            );

            $this->integrations = array(
                'tablesome' => new Tablesome(),
                'mailchimp' => new Mailchimp(),
                'notion' => new Notion(),
            );

            $this->actions = array(
                'add_row' => new Tablesome_Add_Row(),
                'add_contact' => new Mailchimp_Add_Contact(),
                'add_page' => new Notion_Database(),
            );

            $this->register_trigger_hooks();
            // add_action("load_editor");

            Event_Log::get_instance();
        }

        public function register_trigger_hooks() {

            foreach ($this->triggers as $key => $trigger) {
                $trigger->init($this->actions);
                $config = $trigger->get_config();

                $name = $config['wp_hook']['name'];
                $priority = $config['wp_hook']['priority'];
                $accepted_args = $config['wp_hook']['accepted_args'];

                add_action($name, array($trigger, 'trigger_callback'), $priority, $accepted_args);
            }
        }

        public function get_triggers_config() {
            $configs = [];
            foreach ($this->triggers as $trigger) {
                $configs[] = $trigger->get_config();
            }
            return $configs;
        }

        public function get_actions_config() {
            $configs = [];
            foreach ($this->integrations as $name => $integration_instance) {
                $config = $integration_instance->get_config();

                foreach ($this->actions as $action_name => $action_instance) {
                    $action_config = $action_instance->get_config();
                    if ($config['integration'] == $action_config['integration']) {
                        $config['actions'][] = $action_config;
                    }
                }
                $configs[] = $config;
            }
            return $configs;
        }

        public function get_collection() {
            $collection = [];

            foreach ($this->triggers as $trigger_name => $trigger) {
                $trigger_type = $trigger->get_config()['trigger_type'];
                $collection[$trigger_type][$trigger_name] = method_exists($trigger, 'get_collection') ? $trigger->get_collection() : [];
            }

            foreach ($this->integrations as $integration_name => $integration_instance) {
                $collection['integrations'][$integration_name] = method_exists($integration_instance, 'get_collection') ? $integration_instance->get_collection() : [];
            }

            // foreach ($this->actions as $action_name => $action) {
            //     $collection[$action_name] = $action->get_collection();
            // }

            return $collection;
        }

        public function get_trigger_prop_value_by_id($trigger_id, $prop_name) {
            $value = '';
            foreach ($this->triggers as $trigger) {
                $config = $trigger->get_config();
                if (isset($config['trigger_id']) && $config['trigger_id'] == $trigger_id) {
                    $value = isset($config[$prop_name]) ? $config[$prop_name] : '';
                    break;
                }
            }
            return $value;
        }

        public function get_action_prop_value_by_id($action_id, $prop_name) {
            $value = '';
            foreach ($this->actions as $action) {
                $config = $action->get_config();
                if (isset($config['id']) && $config['id'] == $action_id) {
                    $value = isset($config[$prop_name]) ? $config[$prop_name] : '';
                    break;
                }
            }
            return $value;
        }

        public function get_action_integration_label_by_id($action_id) {
            $label = '';
            foreach ($this->actions as $action) {
                $config = $action->get_config();
                if (isset($config['id']) && $config['id'] == $action_id) {
                    $integration = $config['integration'];
                    $label = $this->integrations[$integration]->get_config()['integration_label'];
                    break;
                }
            }
            return $label;
        }

        public function get_all_forms($collection) {
            $forms = [];

            if (isset($collection['forms']) && count($collection['forms']) > 0) {
                foreach ($collection['forms'] as $data) {
                    if (!empty($data)) {
                        $forms = array_merge($forms, $data);
                    }
                }
            }

            return $forms;
        }

    }
}