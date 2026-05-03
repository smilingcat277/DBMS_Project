<?php
session_start();


if (!isset($_SESSION['customer_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'A') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';
$message = "";
$error = "";


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_publisher'])) {
    $publisher_id_to_delete = intval($_POST['publisher_id']);
    
    $delete_stmt = $conn->prepare("DELETE FROM Publishers WHERE publisher_id = ?");
    $delete_stmt->bind_param("i", $publisher_id_to_delete);
    
    if ($delete_stmt->execute()) {
        $message = "Publisher deleted successfully!";
    } else {
        
        $error = "Cannot delete Publisher. They are currently linked to books in the database. Please delete or reassign their books first.";
    }
    $delete_stmt->close();
}


$query = "SELECT publisher_id, publisher_name FROM Publishers order by publisher_ID ASC";
$publishers_result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Publishers - Admin</title>
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
        
        .btn-add { background-color: #27ae60; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .btn-add:hover { background-color: #2ecc71; }
        
        .success-msg { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; }

        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #34495e; color: white; }
        tr:hover { background-color: #f9f9f9; }
        
        .actions { display: flex; gap: 10px; }
        .btn-edit { background-color: #f39c12; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 0.9rem; }
        .btn-edit:hover { background-color: #e67e22; }
        .btn-delete { background-color: #e74c3c; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem; }
        .btn-delete:hover { background-color: #c0392b; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_books.php">Manage Books</a>
        <a href="manage_authors.php">Manage Authors</a>
        <a href="manage_publishers.php" class="active">Manage Publishers</a>
        <a href="manage_genre.php">Manage Genres</a>
        <a href="manage_customers.php">Manage Customers</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="../logout.php" class="logout">Log Out</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Manage Publishers</h1>
            <a href="add_publisher.php" class="btn-add">+ Add New Publisher</a>
        </div>

        <?php if ($message) echo "<div class='success-msg'>$message</div>"; ?>
        <?php if ($error) echo "<div class='error-msg'>$error</div>"; ?>

        <table>
            <thead>
                <tr>
                    <th>Publisher ID</th>
                    <th>Publisher Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($publishers_result && $publishers_result->num_rows > 0): ?>
                    <?php while($publisher = $publishers_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $publisher['publisher_id']; ?></td>
                            <td><?php echo htmlspecialchars($publisher['publisher_name']); ?></td>
                            <td class="actions">
                                <a href="edit_publisher.php?id=<?php echo $publisher['publisher_id']; ?>" class="btn-edit">Edit</a>
                                
                                <form action="manage_publishers.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this publisher?');">
                                    <input type="hidden" name="publisher_id" value="<?php echo $publisher['publisher_id']; ?>">
                                    <button type="submit" name="delete_publisher" class="btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No publishers found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>