<?php

namespace Tablesome\Includes;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Shortcodes')) {
    class Shortcodes
    {
        public function __construct()
        {
            add_shortcode('tablesome', array($this, 'basic'));
        }

        public function basic($atts, $content = null)
        {
            $defaults = $this->default_args();
            $args = array_merge($defaults, $atts);
            // $args = shortcode_atts($defaults, $atts);
            $is_valid_table = $this->validate($args);

            if (!$is_valid_table) {
                return;
            }

            $table = new \Tablesome\Components\Table\Controller();
            return $table->get_view($args);
        }

        private function default_args()
        {
            $args = [
                'table_id' => get_the_ID(),
                'pagination' => true,
                // 'page_limit' => Tablesome_Getter::get('num_of_records_per_page'),
                // 'exclude_column_ids' => '',
                // 'search' => Tablesome_Getter::get('search'),
                // 'hide_table_header' => Tablesome_Getter::get('hide_table_header'),
                // 'show_serial_number_column' => Tablesome_Getter::get('show_serial_number_column'),
                // 'sorting' => Tablesome_Getter::get('sorting'),
                // 'filters' => Tablesome_Getter::get('filters'),
            ];
            return $args;
        }

        private function validate($args)
        {
            $post = get_post($args['table_id']);
            if (empty($post)) {
                return false;
            }

            if (isset($post) && $post->post_type != TABLESOME_CPT) {
                return false;
            }

            if (isset($post) && $post->post_status != 'publish') {
                return false;
            }
            return true;
        }
    }
}
