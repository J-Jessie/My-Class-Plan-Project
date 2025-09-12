<?php
require_once __DIR__.'/helper.php';

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = clean($_POST["first_name"] ?? "");
    $last_name = clean($_POST["last_name"] ?? "");
    $username = clean($_POST["username"] ?? "");
    $email = clean($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    
    // Simple server-side validation
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } else {
        $conn = db_connect();
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error_message = "An account with this email already exists.";
        } else {
            // Hash password and insert user (Default role: Student - ROLE_STUDENT = 2)
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, email, password, role) VALUES (?, ?, ?, ?, ?, ?)");
            $role_student = ROLE_STUDENT;
            $stmt->bind_param('sssssi', $first_name, $last_name, $username, $email, $password_hash, $role_student);
            
            if ($stmt->execute()) {
                $success_message = "Registration successful! You can now log in.";
                // Clear the form fields on success
                unset($_POST);
            } else {
                $error_message = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Class Plan - Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }
        
        .header {
            background: #4e54c8;
            color: white;
            padding: 25px;
            text-align: center;
        }
        
        .header h2 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .form-section {
            padding: 40px;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 25px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #4e54c8;
            box-shadow: 0 0 0 3px rgba(78, 84, 200, 0.2);
        }
        
        .form-group .icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        
        .form-group .password-toggle {
            cursor: pointer;
            color: #999;
        }
        
        .form-group.error input {
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.2);
        }
        
        .form-group.error .icon {
            color: #e74c3c;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-actions {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-primary {
            background-color: #4e54c8;
        }
        
        .btn-primary:hover {
            background-color: #3b42a8;
            transform: translateY(-2px);
        }
        
        .btn-link {
            background: none;
            color: #4e54c8;
            font-weight: 400;
        }
        
        .btn-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Create Your Account</h2>
            <p>Join our community to manage your classes effortlessly.</p>
        </div>
        <div class="form-section">
            <?php if (!empty($success_message)): ?>
                <div class="message success">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="message error">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <form action="signup.php" method="POST">
                <div class="form-group">
                    <input type="text" name="first_name" id="first_name" placeholder="First Name" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                    <span class="icon"><i class="fas fa-user"></i></span>
                </div>
                <div class="form-group">
                    <input type="text" name="last_name" id="last_name" placeholder="Last Name" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                    <span class="icon"><i class="fas fa-user"></i></span>
                </div>
                <div class="form-group">
                    <input type="text" name="username" id="username" placeholder="Username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                    <span class="icon"><i class="fas fa-user-tag"></i></span>
                </div>
                <div class="form-group">
                    <input type="email" name="email" id="email" placeholder="Email Address" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    <span class="icon"><i class="fas fa-envelope"></i></span>
                </div>
                <div class="form-group" id="password-group">
                    <input type="password" name="password" id="password" placeholder="Password (min. 8 characters)" required>
                    <span class="icon password-toggle"><i class="far fa-eye"></i></span>
                </div>
                <div class="form-group" id="confirm-password-group">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                    <span class="icon password-toggle"><i class="far fa-eye"></i></span>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Sign Up</button>
                    <a href="login.php" class="btn btn-link">Already have an account? Log In</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordToggles = document.querySelectorAll('.password-toggle');
            
            // Toggle password visibility
            passwordToggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const input = this.previousElementSibling;
                    togglePasswordVisibility(input, this);
                });
            });

            // Client-side validation on blur and input
            passwordInput.addEventListener('input', validatePassword);
            confirmPasswordInput.addEventListener('input', validateConfirmPassword);
            
            function validatePassword() {
                const passwordGroup = document.getElementById('password-group');
                if (passwordInput.value.length < 8) {
                    passwordGroup.classList.add('error');
                    return false;
                } else {
                    passwordGroup.classList.remove('error');
                    
                    // Also validate confirm password when password changes
                    validateConfirmPassword();
                    return true;
                }
            }
            
            function validateConfirmPassword() {
                const confirmPasswordGroup = document.getElementById('confirm-password-group');
                if (confirmPasswordInput.value !== passwordInput.value) {
                    confirmPasswordGroup.classList.add('error');
                    return false;
                } else {
                    confirmPasswordGroup.classList.remove('error');
                    return true;
                }
            }
            
            function togglePasswordVisibility(input, toggle) {
                if (input.type === 'password') {
                    input.type = 'text';
                    toggle.innerHTML = '<i class="far fa-eye-slash"></i>';
                } else {
                    input.type = 'password';
                    toggle.innerHTML = '<i class="far fa-eye"></i>';
                }
            }
        });
    </script>
</body>
</html>