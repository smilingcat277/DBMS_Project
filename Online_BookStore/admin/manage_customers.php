<?php
session_start();


if (!isset($_SESSION['customer_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'A') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';
$message = "";
$error = "";


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_customer'])) {
    $customer_id_to_delete = intval($_POST['customer_id']);
    
    
    if ($customer_id_to_delete === intval($_SESSION['customer_id'])) {
        $error = "Safety restriction: You cannot delete your own admin account.";
    } else {
        $delete_stmt = $conn->prepare("DELETE FROM Customers WHERE customer_id = ?");
        $delete_stmt->bind_param("i", $customer_id_to_delete);
        
        if ($delete_stmt->execute()) {
            $message = "Customer deleted successfully!";
        } else {
            
            $error = "Cannot delete this customer because they have an existing order history.";
        }
        $delete_stmt->close();
    }
}


$query = "SELECT customer_id, first_name, last_name, email, phone_no, role, created_at FROM Customers ORDER BY created_at DESC";
$customers_result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { display: flex; height: 100vh; background-color: #f4f7f6; }
        
        
        .sidebar { width: 250px; background-color: #2c3e50; color: white; display: flex; flex-direction: column; flex-shrink: 0;}
        .sidebar h2 { text-align: center; padding: 20px; background-color: #1a252f; margin-bottom: 20px; font-size: 1.2rem;}
        .sidebar a { padding: 15px 20px; color: #ecf0f1; text-decoration: none; border-left: 4px solid transparent; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; border-left: 4px solid #3498db; }
        .sidebar .logout { margin-top: auto; background-color: #c0392b; border-left: none; text-align: center;}
        .sidebar .logout:hover { background-color: #e74c3c; }

        
        .main-content { flex-grow: 1; padding: 30px; overflow-y: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header h1 { color: #333; }
        
        .success-msg { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; }

        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #34495e; color: white; }
        tr:hover { background-color: #f9f9f9; }
        
        .role-badge { padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; color: white;}
        .role-admin { background-color: #e74c3c; }
        .role-customer { background-color: #3498db; }

        .actions { display: flex; gap: 10px; }
        .btn-delete { background-color: #e74c3c; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem; }
        .btn-delete:hover { background-color: #c0392b; }
        .btn-delete:disabled { background-color: #bdc3c7; cursor: not-allowed; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_books.php">Manage Books</a>
        <a href="manage_authors.php">Manage Authors</a>
        <a href="manage_publishers.php">Manage Publishers</a>
        <a href="manage_genre.php">Manage Genres</a>
        <a href="manage_customers.php" class="active">Manage Customers</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="../logout.php" class="logout">Log Out</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Manage Customers</h1>
        </div>

        <?php if ($message) echo "<div class='success-msg'>$message</div>"; ?>
        <?php if ($error) echo "<div class='error-msg'>$error</div>"; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Joined Date</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($customers_result && $customers_result->num_rows > 0): ?>
                    <?php while($customer = $customers_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $customer['customer_id']; ?></td>
                            <td><?php echo htmlspecialchars($customer['first_name'] . " " . $customer['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone_no']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                            <td>
                                <?php if ($customer['role'] === 'A'): ?>
                                    <span class="role-badge role-admin">Admin</span>
                                <?php else: ?>
                                    <span class="role-badge role-customer">Customer</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <form action="manage_customers.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="customer_id" value="<?php echo $customer['customer_id']; ?>">
                                    <button type="submit" name="delete_customer" class="btn-delete" 
                                        <?php echo ($customer['customer_id'] == $_SESSION['customer_id']) ? 'disabled title="Cannot delete yourself"' : ''; ?>>
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No customers found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>