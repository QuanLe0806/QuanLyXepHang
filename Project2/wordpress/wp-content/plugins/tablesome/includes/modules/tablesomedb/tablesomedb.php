<?php

namespace Tablesome\Includes\Modules\TablesomeDB;

if (!class_exists('\Tablesome\Includes\Modules\TablesomeDB\TablesomeDB')) {
    class TablesomeDB
    {
        public $table_crud_wp;

        public function __construct()
        {
            $this->table_crud_wp = new \Tablesome\Includes\Lib\Table_Crud_WP\Table_Crud_WP();
        }

        /**
         *  Now, this does not create a table in DB
         * Table is currrently create from includes/core/table.php
         * **/
        public function create_table_instance($table_id, array $table_meta = array(), array $requests = array())
        {
            $table_name = $this->table_crud_wp->get_table_name($table_id, 0);
            if (empty($table_meta)) {
                $table_meta = get_tablesome_data($table_id);
            }
            /** Get current table meta columns */
            $table_columns = $this->table_crud_wp->helper->get_table_columns($table_meta);

            /** Table schema */
            $table_schema = $this->table_crud_wp->schema->get_schema($table_columns);

            $table = new \Tablesome_Table(array(
                'table_name' => $table_name,
                'table_schema' => $table_schema,
            ));

            //TODO Fixes for test-env.
            if (!$table->exists()) {
                $table->install();
            }

            // Modify the table structure if we add/remove the columns
            $table->modify_the_table($table_meta, $table_columns, $requests);

            return $table;
        }

        public function table_exists($table_id)
        {
            $table_name = $this->table_crud_wp->get_table_name($table_id, 0);
            $table = new \Tablesome_Table(array(
                'table_name' => $table_name,
            ));
            return $table->exists() ? true : false;
        }

        public function get_table_schema_columns($table_id)
        {
            /** Get the current table meta columns by table-ID*/
            $table_columns = $this->table_crud_wp->get_table_columns_from_db($table_id);

            /**
             * Generate the table schema
             * Using that schema collection for querying the tablesome table records from DB by using the berlinDB
             */
            $table_schema_generator = new \Tablesome\Includes\Modules\TablesomeDB\Schema_Generator($table_columns);
            $columns = $table_schema_generator->get_columns();
            // $schema = new \Tablesome_Table_Schema($columns);
            return $columns;
        }

        public function query($args)
        {
            $table_id = isset($args['table_id']) ? $args['table_id'] : '';
            $table_name = isset($args['table_name']) ? $args['table_name'] : '';

            if (empty($table_id) || empty($table_name)) {return;}
            $schema_columns = $this->get_table_schema_columns($table_id);

            if (empty($schema_columns)) {return;}
            $args['schema_columns'] = $schema_columns;

            $query = new \Tablesome_Table_Query($args);
            return $query;
        }

        // insert
        public function insert($query, $data)
        {
            $post_id = isset($data['post_id']) ? $data['post_id'] : 0;
            /** Return, if post-id doesn't exists or that value is 0 */
            if (empty($post_id)) {return false;}

            /***
             * Add the default values  (like author_id, created_at, updated_at) to $data array if that array doesn't have.
             */
            $data = $this->get_additional_data($data);

            /** Insert the record using berlinDB */
            $record_id = $query->add_item($data);
            return !empty($record_id) ? $record_id : false;
        }

        // update
        public function update_record($query, $record_id, $data)
        {
            $post_id = isset($data['post_id']) ? $data['post_id'] : 0;
            /** Return, if post-id doesn't exists or the value as 0 */
            if (empty($record_id) || empty($post_id)) {return false;}

            $data = $this->get_additional_data($data);
            /** Don't update the created_at column val when updating the record */
            if (isset($data['created_at'])) {
                unset($data['created_at']);
            }

            /** Update the record using berlinDB */
            $result = $query->update_item($record_id, $data);

            return !empty($result) ? $result : false;
        }

        public function get_additional_data($data)
        {
            $timestamp = current_time('timestamp');
            $datetime = date('Y-m-d H:i:s', $timestamp);

            $data['author_id'] = isset($data['author_id']) && !empty($data['author_id']) ? $data['author_id'] : get_current_user_id();

            $data['created_at'] = isset($data['created_at']) && !empty($data['created_at']) ? $data['created_at'] : $datetime;

            $data['updated_at'] = isset($data['updated_at']) && !empty($data['updated_at']) ? $data['updated_at'] : $datetime;

            $data['rank_order'] = isset($data['rank_order']) && !empty($data['rank_order']) ? $data['rank_order'] : '';

            return $data;
        }

        // delete records
        public function delete_records($query, $record_ids)
        {
            /** Returen if the record_ids array is empty */
            if (empty($record_ids)) {return;}

            foreach ($record_ids as $record_id) {
                $query->delete_item($record_id);
            }
            return true;
        }

        // bulk-inserts
        public function insert_many($table_id, $meta_data, $records)
        {
            $props = [
                'columns' => isset($meta_data['columns']) ? $meta_data['columns'] : [],
                'rows_count' => 0,
                'rows' => array(),
                'meta_data' => $meta_data,
                'records_inserted_count' => 0,
            ];
            $current_batch_no = 1;
            $record_counter = 0;
            foreach ($records as $index => $record) {

                $props["rows"][] = $record;

                $end_row_index = ($current_batch_no * TABLESOME_BATCH_SIZE) - 1;
                if ($index == $end_row_index) {
                    $current_batch_no++;

                    $params = $this->get_inserts_record_values($table_id, $props);
                    $result = $this->table_crud_wp->insert_many($table_id, $params);
                    if ($result) {
                        $records_inserted_count = intval($props['records_inserted_count']) + intval($result);
                        $props['records_inserted_count'] = $records_inserted_count;
                    }
                    unset($props['rows']);

                }

                $record_counter++;

                if ($record_counter == TABLESOME_MAX_RECORDS_TO_READ) {
                    break;
                }
            }

            if (isset($props["rows"]) && !empty($props["rows"]) && $record_counter <= $end_row_index) {
                $params = $this->get_inserts_record_values($table_id, $props);
                $result = $this->table_crud_wp->insert_many($table_id, $params);

                if ($result) {
                    $records_inserted_count = intval($props['records_inserted_count']) + intval($result);
                    $props['records_inserted_count'] = $records_inserted_count;
                }
                unset($props['rows']);
            }

            $props["rows_count"] = $record_counter;

            return $props;
        }

        public function get_inserts_record_values($table_id, $props)
        {
            $timestamp = current_time('timestamp');
            $datetime = date('Y-m-d H:i:s', $timestamp);
            $author_id = get_current_user_id();

            $params = array();

            $defaults = array(
                'post_id' => $table_id,
                'author_id' => $author_id,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            );

            foreach ($props['rows'] as $index => $row) {
                $defaults['rank_order'] = isset($row['rank_order']) ? $row['rank_order'] : '';
                $column_values_args = $this->table_crud_wp->helper->get_column_ided_record($props['meta_data'], $row);
                $params[] = array_merge($defaults, $column_values_args);
            }

            return $params;
        }

        // bulk-updates

        // delete table

        public function delete_table($table)
        {
            $result = $table->drop();
            return $result;
        }

        /**
         * Duplicate the table
         *
         * @param [array] $table -> Source table instance
         * @param [integer] $duplicate_table_id
         * @return void
         */
        public function duplicate_table($table, $duplicate_table_id)
        {
            if (empty($duplicate_table_id)) {return;}
            $duplicate_table_name = $this->table_crud_wp->get_table_name($duplicate_table_id);
            if (empty($duplicate_table_name)) {return;}
            $table_cloned = $table->_clone($duplicate_table_name);
            if (!$table_cloned) {return;}
            $table_records_copied = $table->copy($duplicate_table_name);
            return $table_records_copied;
        }

        public function get_formatted_rows($records, $table_meta, array $collection = array())
        {
            $processed_rows = array();

            if (empty($records)) {
                $date = date('Y-m-d H:i:s');
                array_push($processed_rows, [
                    "record_id" => 0,
                    "content" => [""],
                    "rank_order" => "",
                    "created_at" => $date,
                    "updated_at" => $date,
                ]);
                return $processed_rows;
            }

            foreach ($records as $record) {
                $processed_rows[] = array(
                    'record_id' => $record->id,
                    'rank_order' => $record->rank_order,
                    'content' => $this->get_formatted_row($record, $table_meta, $collection),
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at,
                );
            }

            return $processed_rows;
        }

        public function get_formatted_row($record, $table_meta, $collection)
        {
            $row_content = array();
            /** get exclude column ids */
            $exclude_column_ids = isset($collection['exclude_column_ids']) && !empty($collection['exclude_column_ids']) ? explode(",", $collection['exclude_column_ids']) : [];
            $columns = isset($table_meta['columns']) ? $table_meta['columns'] : [];
            foreach ($columns as $column) {

                $column_id = isset($column['id']) ? $column['id'] : 0;
                $column_format = isset($column['format']) ? $column['format'] : 'text';

                if (in_array($column_id, $exclude_column_ids)) {
                    continue;
                }

                $db_column_name = 'column_' . $column_id;
                $db_meta_column_name = $db_column_name . '_meta';

                $cell_content = isset($record->$db_column_name) ? $record->$db_column_name : '';
                $cell_meta_content = isset($record->$db_meta_column_name) ? $record->$db_meta_column_name : '';

                $cell = [
                    'type' => $column_format,
                    'html' => $cell_content,
                    'value' => $cell_content,
                ];

                $meta_columns = ($column_format == 'url' || $column_format == 'button' || $column_format == 'file');
                if ($meta_columns && !empty($cell_meta_content)) {
                    // $link_cell_data = $this->extract_link_content($column_format, $cell_content);

                    $meta_content = json_decode(stripslashes($cell_meta_content), true);
                    $cell = !empty($meta_content) ? array_merge($cell, $meta_content) : $cell;
                }

                $cell = apply_filters("tablesome_get_cell_data", $cell);

                $row_content[$column_id] = $cell;
            }
            return $row_content;
        }

        public function extract_link_content($column_format, $cell_content)
        {
            $data = array();
            $required_props = array('value', 'html', 'linkText');

            foreach ($required_props as $key) {

                $pattern = '/\[' . $key . '\]';
                $pattern .= '\(';
                $pattern .= '(.*?)';
                $pattern .= '\)/';

                preg_match($pattern, $cell_content, $results);
                $cell_value = isset($results[1]) ? $results[1] : '';

                if (!empty($cell_value)) {
                    $cell_value = str_replace('TS_{', '(', $cell_value);
                    $cell_value = str_replace('TS_}', ')', $cell_value);
                }

                $data[$key] = $cell_value;
            }

            // $cell_data = explode("||", $cell_content);
            // if ($column_format == 'button') {
            //     return array(
            //         'value' => isset($cell_data[0]) ? $cell_data[0] : '',
            //         'linkText' => isset($cell_data[1]) ? $cell_data[1] : '',
            //         'html' => isset($cell_data[2]) ? $cell_data[2] : '',
            //     );
            // }
            // return array(
            //     'value' => isset($cell_data[0]) ? $cell_data[0] : '',
            //     'html' => isset($cell_data[1]) ? $cell_data[1] : '',
            // );

            return $data;
        }

        public function get_tables_records_count($tables)
        {
            if (empty($tables)) {
                return 0;
            }
            $records_count = 0;
            foreach ($tables as $table) {
                $db_table = $this->create_table_instance($table->ID, []);
                $records_count = intval($records_count) + intval($db_table->count());
            }
            return $records_count;
        }

        public function get_max_rank_order_value($table_id)
        {
            $min_rank_order = '0|100000:';
            if (isset($table_id) && $table_id === 0) {
                return $min_rank_order;
            }

            global $wpdb;
            $table_name = $this->table_crud_wp->get_table_name($table_id, 1);
            $query = "select max(rank_order) as rank_order from {$table_name}";
            $rank_order = $wpdb->get_var($query);
            $rank_order = !empty($rank_order) ? $rank_order : $min_rank_order;
            return $rank_order;
        }
    }
}
