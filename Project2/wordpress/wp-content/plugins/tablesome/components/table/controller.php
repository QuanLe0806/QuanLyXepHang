<?php

namespace Tablesome\Components\Table;

if (!class_exists('\Tablesome\Components\Table\Controller')) {
    class Controller
    {
        public function __construct()
        {
            $this->model = new \Tablesome\Components\Table\Model();
            $this->view = new \Tablesome\Components\Table\View();
            new \Tablesome\Components\CellTypes\File\Controller();
            new \Tablesome\Components\CellTypes\Text();
            new \Tablesome\Components\CellTypes\Textarea();
            new \Tablesome\Components\CellTypes\Number();
            new \Tablesome\Components\CellTypes\Email();
            new \Tablesome\Components\CellTypes\URL();
            new \Tablesome\Components\CellTypes\Email();
            new \Tablesome\Components\CellTypes\Date();
            new \Tablesome\Components\CellTypes\Button();
        }

        public function get_view($args = [])
        {
            $viewProps = $this->get_table_viewProps($args);
            return $this->view->get_table($viewProps);
        }

        public function get_table_level_settings()
        {
            return [
                "display" => $this->model->get_display_settings(),
                "style" => $this->model->get_style_settings(),
            ];
        }

        public function get_table_viewProps($args = [])
        {
            global $tablesome_tables_collection;
            $viewProps = $this->model->get_viewProps($args);

            $args["pagination"] = false;
            $tablesome_tables_collection[] = $this->model->get_viewProps($args);

            return $viewProps;
        }
    } // END CLASS
}
