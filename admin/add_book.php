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
    $book_name = trim($_POST['book_name']);
    $price = floatval($_POST['price']);
    $rating = floatval($_POST['rating']); 
    $stock = intval($_POST['stock']);
    $cover_type = trim($_POST['cover_type']);
    $book_type = trim($_POST['book_type']);
    $author_id = intval($_POST['author_id']);
    $publisher_id = intval($_POST['publisher_id']);
    $genre_id = intval($_POST['genre_id']);

    
    if (empty($book_name) || empty($cover_type) || empty($book_type)) {
        $error = "Please fill in all text fields.";
    } else {
        
        $insert_query = "INSERT INTO Books (book_name, price, rating, stock, cover_type, book_type, author_id, publisher_id, genre_id) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($insert_query)) {
            
            $stmt->bind_param("sddissiii", $book_name, $price, $rating, $stock, $cover_type, $book_type, $author_id, $publisher_id, $genre_id);
            
            if ($stmt->execute()) {
                $message = "Book successfully added!";
            } else {
                $error = "Error adding book: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Database preparation error.";
        }
    }
}


$authors_result = $conn->query("SELECT author_id, first_name, last_name FROM Authors ORDER BY first_name");
$publishers_result = $conn->query("SELECT publisher_id, publisher_name FROM Publishers ORDER BY publisher_name");
$genres_result = $conn->query("SELECT genre_id, genre_name FROM Genre ORDER BY genre_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Book - Admin</title>
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
        
        
        .form-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 800px;}
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #333;}
        input[type="text"], input[type="number"], select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        
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
        <a href="manage_books.php" class="active">Manage Books</a>
        <a href="manage_authors.php">Manage Authors</a>
        <a href="manage_publishers.php">Manage Publishers</a>
        <a href="manage_genres.php">Manage Genres</a>
        <a href="manage_customers.php">Manage Customers</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="../logout.php" class="logout">Log Out</a>
    </div>

    <div class="main-content">
        <div class="header">
            <a href="manage_books.php" class="back-link">&larr; Back to Manage Books</a>
            <h1>Add New Book</h1>
        </div>

        <div class="form-container">
            <?php if ($message) echo "<div class='success-msg'>$message</div>"; ?>
            <?php if ($error) echo "<div class='error-msg'>$error</div>"; ?>

            <form action="add_book.php" method="POST">
                <div class="form-group">
                    <label>Book Title</label>
                    <input type="text" name="book_name" required>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>Price ($)</label>
                        <input type="number" step="0.01" name="price" required>
                    </div>
                    <div class="form-group">
                        <label>Initial Stock</label>
                        <input type="number" name="stock" required>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>Cover Type</label>
                        <select name="cover_type" required>
                            <option value="Hard cover">Hard cover</option>
                            <option value="Paper back">Paper back</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Book Type</label>
                        <!-- Adjust these options based on your DB ENUM or text needs -->
                        <select name="book_type" required>
                            <option value="Fiction">Fiction</option>
                            <option value="Non-Fiction">Non-Fiction</option>
                            <option value="Academic">Academic</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Rating (0.0 to 5.0)</label>
                    <input type="number" step="0.1" min="0" max="5" name="rating" value="0.0">
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>Author</label>
                        <select name="author_id" required>
                            <option value="">-- Select Author --</option>
                            <?php while($author = $authors_result->fetch_assoc()): ?>
                                <option value="<?php echo $author['author_id']; ?>">
                                    <?php echo htmlspecialchars($author['first_name'] . ' ' . $author['last_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Publisher</label>
                        <select name="publisher_id" required>
                            <option value="">-- Select Publisher --</option>
                            <?php while($publisher = $publishers_result->fetch_assoc()): ?>
                                <option value="<?php echo $publisher['publisher_id']; ?>">
                                    <?php echo htmlspecialchars($publisher['publisher_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Genre</label>
                    <select name="genre_id" required>
                        <option value="">-- Select Genre --</option>
                        <?php while($genre = $genres_result->fetch_assoc()): ?>
                            <option value="<?php echo $genre['genre_id']; ?>">
                                <?php echo htmlspecialchars($genre['genre_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <button type="submit">Add Book</button>
            </form>
        </div>
    </div>

</body>
</html>