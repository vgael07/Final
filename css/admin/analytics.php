<?php
require_once("../includes/connect.php");

// Get date range from request or default to last 30 days
$end_date = date('Y-m-d');
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));

// Sales Analytics
$sales_query = "SELECT 
    DATE(created_at) as date,
    COUNT(*) as total_orders,
    SUM(total_amount) as total_sales,
    AVG(total_amount) as average_order_value
FROM orders 
WHERE created_at BETWEEN :start_date AND :end_date
GROUP BY DATE(created_at)
ORDER BY date";
$sales_stmt = $conn->prepare($sales_query);
$sales_stmt->bindParam(':start_date', $start_date);
$sales_stmt->bindParam(':end_date', $end_date);
$sales_stmt->execute();
$sales_data = $sales_stmt->fetchAll(PDO::FETCH_ASSOC);

// Product Performance
$product_query = "SELECT 
    p.name,
    COUNT(oi.id) as total_orders,
    SUM(oi.quantity) as total_quantity,
    SUM(oi.quantity * oi.price) as total_revenue
FROM products p
LEFT JOIN order_items oi ON p.id = oi.product_id
LEFT JOIN orders o ON oi.order_id = o.id
WHERE o.created_at BETWEEN :start_date AND :end_date
GROUP BY p.id
ORDER BY total_revenue DESC
LIMIT 10";
$product_stmt = $conn->prepare($product_query);
$product_stmt->bindParam(':start_date', $start_date);
$product_stmt->bindParam(':end_date', $end_date);
$product_stmt->execute();
$product_data = $product_stmt->fetchAll(PDO::FETCH_ASSOC);

// Category Performance
$category_query = "SELECT 
    c.name,
    COUNT(DISTINCT o.id) as total_orders,
    SUM(oi.quantity) as total_quantity,
    SUM(oi.quantity * oi.price) as total_revenue
FROM categories c
LEFT JOIN products p ON c.id = p.category_id
LEFT JOIN order_items oi ON p.id = oi.product_id
LEFT JOIN orders o ON oi.order_id = o.id
WHERE o.created_at BETWEEN :start_date AND :end_date
GROUP BY c.id
ORDER BY total_revenue DESC";
$category_stmt = $conn->prepare($category_query);
$category_stmt->bindParam(':start_date', $start_date);
$category_stmt->bindParam(':end_date', $end_date);
$category_stmt->execute();
$category_data = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

// Customer Analytics
$customer_query = "SELECT 
    COUNT(DISTINCT user_id) as total_customers,
    COUNT(DISTINCT CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN user_id END) as new_customers,
    AVG(total_amount) as average_order_value
FROM orders
WHERE created_at BETWEEN :start_date AND :end_date";
$customer_stmt = $conn->prepare($customer_query);
$customer_stmt->bindParam(':start_date', $start_date);
$customer_stmt->bindParam(':end_date', $end_date);
$customer_stmt->execute();
$customer_data = $customer_stmt->fetch(PDO::FETCH_ASSOC);

// Calculate trends
$total_sales = array_sum(array_column($sales_data, 'total_sales'));
$total_orders = array_sum(array_column($sales_data, 'total_orders'));
$average_order_value = $total_orders > 0 ? $total_sales / $total_orders : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Business Analytics - Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .metric-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .metric-card:hover {
            transform: translateY(-5px);
        }
        .metric-icon {
            font-size: 2rem;
            color: #dc3545;
        }
        .metric-value {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .metric-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <?php require_once("admin_nav.php"); ?>

    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2>Business Analytics Dashboard</h2>
                <form class="row g-3 align-items-center mb-4">
                    <div class="col-auto">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                    </div>
                    <div class="col-auto">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                    </div>
                    <div class="col-auto">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block">Update</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card metric-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="metric-label">Total Sales</h6>
                                <h3 class="metric-value">₱<?php echo number_format($total_sales, 2); ?></h3>
                            </div>
                            <i class="bi bi-currency-dollar metric-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="metric-label">Total Orders</h6>
                                <h3 class="metric-value"><?php echo number_format($total_orders); ?></h3>
                            </div>
                            <i class="bi bi-cart metric-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="metric-label">Average Order Value</h6>
                                <h3 class="metric-value">₱<?php echo number_format($average_order_value, 2); ?></h3>
                            </div>
                            <i class="bi bi-graph-up metric-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="metric-label">Total Customers</h6>
                                <h3 class="metric-value"><?php echo number_format($customer_data['total_customers']); ?></h3>
                            </div>
                            <i class="bi bi-people metric-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sales Trend</h5>
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Category Performance</h5>
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Products -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top Performing Products</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Orders</th>
                                        <th>Quantity Sold</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($product_data as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo number_format($product['total_orders']); ?></td>
                                        <td><?php echo number_format($product['total_quantity']); ?></td>
                                        <td>₱<?php echo number_format($product['total_revenue'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Charts Initialization -->
    <script>
        // Sales Trend Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($sales_data, 'date')); ?>,
                datasets: [{
                    label: 'Daily Sales',
                    data: <?php echo json_encode(array_column($sales_data, 'total_sales')); ?>,
                    borderColor: '#dc3545',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Category Performance Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($category_data, 'name')); ?>,
                datasets: [{
                    label: 'Revenue by Category',
                    data: <?php echo json_encode(array_column($category_data, 'total_revenue')); ?>,
                    backgroundColor: '#dc3545'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 