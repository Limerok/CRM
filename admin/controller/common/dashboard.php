<?php
class ControllerCommonDashboard extends Controller
{
    public function index()
    {
        require_login();

        $stockTotal = $this->db->fetch('SELECT SUM(quantity) as total FROM stock_items');
        $weeklyOrders = $this->db->fetch(
            'SELECT COUNT(*) AS total
             FROM sale_items si
             LEFT JOIN sales s ON si.sale_id = s.id
             WHERE COALESCE(si.order_date, s.sale_date) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)'
        );
        $monthlyOrders = $this->db->fetch(
            'SELECT COUNT(*) AS total
             FROM sale_items si
             LEFT JOIN sales s ON si.sale_id = s.id
             WHERE COALESCE(si.order_date, s.sale_date) >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)'
        );

        $stats = array(
            'stock_items' => isset($stockTotal['total']) && $stockTotal['total'] !== null ? (int)$stockTotal['total'] : 0,
            'weekly_orders' => isset($weeklyOrders['total']) ? (int)$weeklyOrders['total'] : 0,
            'monthly_orders' => isset($monthlyOrders['total']) ? (int)$monthlyOrders['total'] : 0,
        );

        $this->render('common/dashboard', array(
            'stats' => $stats,
        ));
    }
}
