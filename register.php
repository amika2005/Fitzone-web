<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $membership = $_POST['membership'];
    $password = $_POST['password'];

    // Validate inputs
    $errors = [];
    if (empty($full_name)) $errors[] = "Full name is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";

    // If no errors, proceed
    if (empty($errors)) {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "Email already registered";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, membership_type, password) 
                                      VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$full_name, $email, $phone, $membership, $hashed_password]);
                
                // Success - redirect to login
                header("Location: login.html?registration=success");
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
    
    // If we got here, there were errors
    $error_string = implode("<br>", $errors);
    header("Location: register.html?error=" . urlencode($error_string));
    exit();
}
?>