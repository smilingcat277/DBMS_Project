<?php
session_start();



if (!isset($_SESSION['customer_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'A') {
    header("Location: ../login.php");
    exit();
}


require_once '../db.php';



$books_query = $conn->query("SELECT COUNT(*) as total FROM Books");
$total_books = $books_query->fetch_assoc()['total'];


$customers_query = $conn->query("SELECT COUNT(*) as total FROM Customers WHERE role = 'C'");
$total_customers = $customers_query->fetch_assoc()['total'];


$orders_query = $conn->query("SELECT COUNT(*) as total FROM Orders");
$total_orders = $orders_query->fetch_assoc()['total'];


$revenue_query = $conn->query("SELECT SUM(total_price) as total FROM Orders");
$total_revenue = $revenue_query->fetch_assoc()['total'] ?? 0.00; 


$recent_orders = $conn->query("
    SELECT o.order_id, c.first_name, c.last_name, o.total_price, o.status, o.creation_time 
    FROM Orders o 
    JOIN Customers c ON o.customer_id = c.customer_id 
    ORDER BY o.creation_time DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bookstore</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { display: flex; height: 100vh; background-color: #f4f7f6; }
        
        
        .sidebar { width: 250px; background-color: #2c3e50; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px; background-color: #1a252f; margin-bottom: 20px; font-size: 1.2rem;}
        .sidebar a { padding: 15px 20px; color: #ecf0f1; text-decoration: none; border-left: 4px solid transparent; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; border-left: 4px solid #3498db; }
        .sidebar .logout { margin-top: auto; background-color: #c0392b; border-left: none; text-align: center;}
        .sidebar .logout:hover { background-color: #e74c3c; }

        
        .main-content { flex-grow: 1; padding: 30px; overflow-y: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h1 { color: #333; }
        
        
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; }
        .card h3 { color: #7f8c8d; font-size: 1rem; margin-bottom: 10px; }
        .card p { font-size: 2rem; font-weight: bold; color: #2c3e50; }
        .card.revenue p { color: #27ae60; }

        
        .section-title { margin-bottom: 15px; color: #333; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #34495e; color: white; }
        tr:hover { background-color: #f9f9f9; }
        .status { padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; }
        .status.pending { background: #f39c12; color: white; }
        .status.completed { background: #27ae60; color: white; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="manage_books.php">Manage Books</a>
        <a href="manage_authors.php">Manage Authors</a>
        <a href="manage_publishers.php">Manage Publishers</a>
        <a href="manage_genre.php">Manage Genres</a>
        <a href="manage_customers.php">Manage Customers</a>
        <a href="manage_orders.php">Manage Orders</a>
        
        <a href="../logout.php" class="logout">Log Out</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Overview Dashboard</h1>
            <p>Welcome back, Admin <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</p>
        </div>

        <div class="cards-grid">
            <div class="card">
                <h3>Total Books</h3>
                <p><?php echo $total_books; ?></p>
            </div>
            <div class="card">
                <h3>Total Orders</h3>
                <p><?php echo $total_orders; ?></p>
            </div>
            <div class="card">
                <h3>Total Customers</h3>
                <p><?php echo $total_customers; ?></p>
            </div>
            <div class="card revenue">
                <h3>Total Revenue</h3>
                <p>$<?php echo number_format($total_revenue, 2); ?></p>
            </div>
        </div>

        <h2 class="section-title">Recent Orders</h2>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Date</th>
                    <th>Total Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recent_orders->num_rows > 0): ?>
                    <?php while($order = $recent_orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($order['first_name'] . " " . $order['last_name']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['creation_time'])); ?></td>
                            <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                            <td>
                                <span class="status <?php echo strtolower($order['status'] ?? 'pending'); ?>">
                                    <?php echo htmlspecialchars($order['status'] ?? 'Pending'); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No recent orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>