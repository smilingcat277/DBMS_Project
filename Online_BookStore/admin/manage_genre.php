<?php
session_start();


if (!isset($_SESSION['customer_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'A') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';
$message = "";
$error = "";


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_genre'])) {
    $genre_id_to_delete = intval($_POST['genre_id']);
    
    $delete_stmt = $conn->prepare("DELETE FROM Genre WHERE genre_id = ?");
    $delete_stmt->bind_param("i", $genre_id_to_delete);
    
    if ($delete_stmt->execute()) {
        $message = "Genre deleted successfully!";
    } else {
        
        $error = "Cannot delete genre. They are currently linked to books in the database. Please delete or reassign their books first.";
    }
    $delete_stmt->close();
}


$query = "SELECT genre_id, genre_name FROM genre order by genre_ID ASC";
$genre_result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Genre - Admin</title>
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
        <a href="manage_publishers.php">Manage Publishers</a>
        <a href="manage_genre.php" class="active">Manage Genres</a>
        <a href="manage_customers.php">Manage Customers</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="../logout.php" class="logout">Log Out</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Manage Genre</h1>
            <a href="add_genre.php" class="btn-add">+ Add New Genre</a>
        </div>

        <?php if ($message) echo "<div class='success-msg'>$message</div>"; ?>
        <?php if ($error) echo "<div class='error-msg'>$error</div>"; ?>

        <table>
            <thead>
                <tr>
                    <th>Genre ID</th>
                    <th>Genre Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($genre_result && $genre_result->num_rows > 0): ?>
                    <?php while($genre = $genre_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $genre['genre_id']; ?></td>
                            <td><?php echo htmlspecialchars($genre['genre_name']); ?></td>
                            <td class="actions">
                                <a href="edit_genre.php?id=<?php echo $genre['genre_id']; ?>" class="btn-edit">Edit</a>
                                
                                <form action="manage_genre.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this genre?');">
                                    <input type="hidden" name="genre_id" value="<?php echo $genre['genre_id']; ?>">
                                    <button type="submit" name="delete_genre" class="btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No genre found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>