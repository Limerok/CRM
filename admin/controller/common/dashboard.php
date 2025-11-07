<?php
class ControllerCommonDashboard extends Controller
{
    public function index()
    {
        require_login();

        $productsTotal = $this->db->fetch('SELECT COUNT(*) as total FROM products');
        $categoriesTotal = $this->db->fetch('SELECT COUNT(*) as total FROM categories');
        $manufacturersTotal = $this->db->fetch('SELECT COUNT(*) as total FROM manufacturers');
        $stockTotal = $this->db->fetch('SELECT SUM(quantity) as total FROM stock_items');

        $stats = array(
            'products' => isset($productsTotal['total']) ? $productsTotal['total'] : 0,
            'categories' => isset($categoriesTotal['total']) ? $categoriesTotal['total'] : 0,
            'manufacturers' => isset($manufacturersTotal['total']) ? $manufacturersTotal['total'] : 0,
            'stock_items' => isset($stockTotal['total']) ? $stockTotal['total'] : 0,
        );

        $this->render('common/dashboard', array(
            'stats' => $stats,
        ));
    }
}
