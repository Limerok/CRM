<?php
abstract class Controller
{
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    protected function render($template, $data = array())
    {
        if (!is_array($data)) {
            $data = array();
        }
        extract($data);
        include view_path('common/header');
        include view_path('common/column_left');
        include view_path($template);
        include view_path('common/footer');
    }

    protected function renderSimple($template, $data = array())
    {
        if (!is_array($data)) {
            $data = array();
        }
        extract($data);
        include view_path($template);
    }
}
