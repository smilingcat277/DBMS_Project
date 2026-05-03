<?php
// Start the session at the very top of the file
session_start();

// Real-world practice: If the user is already logged in, redirect them to the homepage
if (isset($_SESSION['customer_id'])) {
    header("Location: books.php");
    exit();
}

// Include database connection
require_once 'db.php';

$email = "";
$error = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = trim($_POST['email']);
    $password = $_POST['password']; // Do not trim passwords

    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Prepare a statement to get the user's data based on email
        // Note: Column name in your DDL is 'pass_word'
        $query = "SELECT customer_id, first_name, pass_word FROM customers WHERE email = ?";
        
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            
            // Check if email exists
            if ($stmt->num_rows == 1) {
                // Bind result variables
                $stmt->bind_result($customer_id, $first_name, $hashed_password);
                $stmt->fetch();
                
                // Verify the password against the hash
                if (password_verify($password, $hashed_password)) {
                    
                    // REAL-WORLD SECURITY: Prevent Session Fixation attacks
                    session_regenerate_id(true);
                    
                    // Store data in session variables
                    $_SESSION['customer_id'] = $customer_id;
                    $_SESSION['first_name'] = $first_name;
                    $_SESSION['email'] = $email;
                    
                    // Redirect user to the main books page or homepage
                    header("Location: books.php");
                    exit();
                } else {
                    // Real-world practice: Generic error message
                    $error = "Invalid email or password.";
                }
            } else {
                // Don't tell the user "Email not found" for security reasons!
                $error = "Invalid email or password.";
            }
            $stmt->close();
        } else {
            $error = "Something went wrong. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Bookstore</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 400px; background: white; padding: 20px; margin: 100px auto; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="email"], input[type="password"] { width: 100%; padding: 8px; box-sizing: border-box; }
        .error { color: red; background: #ffe6e6; padding: 10px; border-left: 4px solid red; margin-bottom: 15px;}
        button { background: #007bff; color: white; border: none; padding: 10px 15px; cursor: pointer; width: 100%; font-size: 16px;}
        button:hover { background: #0056b3; }
    </style>
</head>
<body>

<div class="container">
    <h2>Customer Login</h2>

    <!-- Display Error Message -->
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">Login</button>
    </form>
    
    <p style="text-align:center; margin-top: 15px;">
        New customer? <a href="register.php">Register here</a>
    </p>
</div>

</body>
</html>