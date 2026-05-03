<?php
session_start();


if (!isset($_SESSION['customer_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'A') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);

    if (empty($first_name) || empty($last_name)) {
        $error = "Both first name and last name are required.";
    } else {
        $insert_query = "INSERT INTO Authors (first_name, last_name) VALUES (?, ?)";
        
        if ($stmt = $conn->prepare($insert_query)) {
            $stmt->bind_param("ss", $first_name, $last_name);
            
            if ($stmt->execute()) {
                $message = "Author successfully added!";
            } else {
                $error = "Error adding author: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Database preparation error.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Author - Admin</title>
    <style>
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { display: flex; height: 100vh; background-color: #f4f7f6; }
        
        .sidebar { width: 250px; background-color: #2c3e50; color: white; display: flex; flex-direction: column; flex-shrink: 0;}
        .sidebar h2 { text-align: center; padding: 20px; background-color: #1a252f; margin-bottom: 20px; font-size: 1.2rem;}
        .sidebar a { padding: 15px 20px; color: #ecf0f1; text-decoration: none; border-left: 4px solid transparent; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; border-left: 4px solid #3498db; }
        .sidebar .logout { margin-top: auto; background-color: #c0392b; border-left: none; text-align: center;}
        
        .main-content { flex-grow: 1; padding: 30px; overflow-y: auto; }
        .header { margin-bottom: 20px; }
        
        .form-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 500px;}
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #333;}
        input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        
        button { background-color: #27ae60; color: white; border: none; padding: 12px 20px; cursor: pointer; border-radius: 4px; font-size: 1rem; width: 100%; margin-top: 10px;}
        button:hover { background-color: #2ecc71; }
        .back-link { display: inline-block; margin-bottom: 15px; color: #3498db; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }

        .success-msg { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_books.php">Manage Books</a>
        <a href="manage_authors.php" class="active">Manage Authors</a>
        <a href="manage_publishers.php">Manage Publishers</a>
        <a href="manage_genres.php">Manage Genres</a>
        <a href="manage_customers.php">Manage Customers</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="../logout.php" class="logout">Log Out</a>
    </div>

    <div class="main-content">
        <div class="header">
            <a href="manage_authors.php" class="back-link">&larr; Back to Manage Authors</a>
            <h1>Add New Author</h1>
        </div>

        <div class="form-container">
            <?php if ($message) echo "<div class='success-msg'>$message</div>"; ?>
            <?php if ($error) echo "<div class='error-msg'>$error</div>"; ?>

            <form action="add_author.php" method="POST">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" required>
                </div>

                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" required>
                </div>

                <button type="submit">Save Author</button>
            </form>
        </div>
    </div>

</body>
</html>