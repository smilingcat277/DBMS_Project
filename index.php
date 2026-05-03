<?php
include 'db.php';

$sql = "SELECT * FROM book_details";
$result = $conn->query($sql);
?>

<?php
session_start();

// If the session variable is NOT set, redirect them to login page
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
// ... rest of your code ...
?>

<!-- Somewhere in your HTML Navigation Bar -->
<div class="navbar">
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</p>
    <a href="logout.php">Log Out</a>
</div>

<!DOCTYPE html>
<html>
<head>
    <title>Online Bookstore</title>
</head>
<body>

    <h1>Available Books</h1>

    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<div>";
            echo "<h3>" . $row['book_name'] . "</h3>";
            echo "<p>Author Name: " . $row['author_name'] . "</p>";
            echo "<p>Price: Rs. " . $row['price'] . "</p>";
            echo "<p>Stock: " . $row['stock'] . "</p>";
            echo "</div><hr>";
        }
    } else {
        echo "No books found.";
    }
    ?>

</body>
</html>