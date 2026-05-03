<?php
session_start();
require_once 'db.php';

$first_name = $last_name = $email = $password = $house_no = $city = $postal_code = $country = $phone_no = "";
$errors = [];
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; 
    $house_no = trim($_POST['house_no']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    $country = trim($_POST['country']);
    $phone_no = trim($_POST['phone_no']);

    if (empty($first_name)) $errors[] = "First name is required.";
    if (empty($last_name)) $errors[] = "Last name is required.";
    if (empty($house_no)) $errors[] = "House/Flat number is required.";
    if (empty($city)) $errors[] = "City is required.";
    if (empty($country)) $errors[] = "Country is required.";
    if (empty($phone_no)) $errors[] = "Phone number is required.";
    if (empty($password)) $errors[] = "Password is required.";
    elseif (strlen($password) < 6) $errors[] = "Password must be at least 6 characters long.";

    if (empty($postal_code)) $postal_code = '0';

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } else {
        $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors[] = "This email is already registered. Please log in.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO customers (first_name, last_name, email, pass_word, house_no, city, postal_code, country, phone_no) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($insert_query)) {
            $stmt->bind_param("sssssssss", $first_name, $last_name, $email, $hashed_password, $house_no, $city, $postal_code, $country, $phone_no);
            
            if ($stmt->execute()) {
                $success_message = "Registration successful! You can now <a href='login.php'>log in here</a>.";
                $first_name = $last_name = $email = $house_no = $city = $postal_code = $country = $phone_no = "";
            } else {
                $errors[] = "Something went wrong. Please try again later.";
            }
            $stmt->close();
        } else {
            $errors[] = "Database preparation error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Bookstore</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 500px; background: white; padding: 30px; margin: auto; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; border-left: 4px solid #f5c6cb; margin-bottom: 15px; border-radius: 4px;}
        .success { color: #155724; background: #d4edda; padding: 10px; border-left: 4px solid #c3e6cb; margin-bottom: 15px; border-radius: 4px;}
        button { background: #28a745; color: white; border: none; padding: 12px 15px; cursor: pointer; width: 100%; font-size: 16px; border-radius: 4px;}
        button:hover { background: #218838; }
        .helper-text { color: #666; font-size: 0.85em; margin-top: 4px; display: block; }
    </style>
</head>
<body>

<div class="container">
    <h2 style="text-align: center;">Register an Account</h2>

    <?php if (!empty($success_message)) echo "<div class='success'>$success_message</div>"; ?>

    <?php 
    if (!empty($errors)) {
        echo "<div class='error'><ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul></div>";
    }
    ?>

    <form action="register.php" method="POST">
        <div class="form-group">
            <label>First Name *</label>
            <input type="text" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Last Name *</label>
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
        </div>

        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>

        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" required>
        </div>

        <!-- UPDATED HOUSE/FLAT NO SECTION -->
        <div class="form-group">
            <label>House No / Flat No *</label>
            <input type="text" name="house_no" value="<?php echo htmlspecialchars($house_no); ?>" placeholder="e.g., House 12 or Flat 4B" required>
            <span class="helper-text">Please specify if it is a House or a Flat.</span>
        </div>

        <div class="form-group">
            <label>City *</label>
            <input type="text" name="city" value="<?php echo htmlspecialchars($city); ?>" required>
        </div>

        <div class="form-group">
            <label>Postal Code *</label>
            <input type="text" name="postal_code" value="<?php echo htmlspecialchars($postal_code); ?>" required>
        </div>

        <div class="form-group">
            <label>Country *</label>
            <input type="text" name="country" value="<?php echo htmlspecialchars($country); ?>" required>
        </div>

        <div class="form-group">
            <label>Phone Number *</label>
            <input type="text" name="phone_no" value="<?php echo htmlspecialchars($phone_no); ?>" placeholder="e.g., +1 234 567 890" required>
        </div>

        <button type="submit">Register</button>
    </form>
    <p style="text-align:center; margin-top: 20px;">Already have an account? <a href="login.php">Login here</a></p>
</div>

</body>
</html>