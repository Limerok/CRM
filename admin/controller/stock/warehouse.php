<?php
class ControllerStockWarehouse extends Controller
{
    public function index()
    {
        require_login();
        $items = $this->db->fetchAll('SELECT s.product_id, s.quantity, p.name, p.model FROM stock_items s
            LEFT JOIN products p ON s.product_id = p.id
            ORDER BY p.name ASC');

        $this->render('stock/warehouse_list', array(
            'items' => $items,
        ));
    }
}
