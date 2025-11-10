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

        $replenishments = $this->db->fetchAll(
            'SELECT p.id, p.name,
                    COALESCE(prs.recommended_quantity, 0) AS recommended_quantity,
                    COALESCE(s.quantity, 0) AS stock_quantity,
                    GREATEST(COALESCE(prs.recommended_quantity, 0) - COALESCE(s.quantity, 0), 0) AS to_deliver
             FROM product_recommended_stock prs
             LEFT JOIN products p ON prs.product_id = p.id
             LEFT JOIN stock_items s ON s.product_id = prs.product_id
             WHERE prs.recommended_quantity IS NOT NULL
               AND COALESCE(prs.recommended_quantity, 0) > COALESCE(s.quantity, 0)
               AND p.id IS NOT NULL
             ORDER BY to_deliver DESC, p.name ASC
             LIMIT 10'
        );

        $this->render('common/dashboard', array(
            'stats' => $stats,
            'replenishments' => $replenishments,
        ));
    }
}
