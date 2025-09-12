<?php
/**
 * Admin-only page to add new users (Lecturer or Timetabler).
 * This page replaces the need for public signups for privileged roles.
 */
require_once __DIR__.'/helper.php';

// Check for admin role
if (!is_logged_in() || current_role() !== ROLE_ADMIN) {
    header('Location: /login.php');
    exit;
}

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = clean($_POST["name"] ?? "");
    $email = clean($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $role = (int)($_POST["role"] ?? ROLE_STUDENT);

    // Basic server-side validation for admin form
    if (empty($name) || empty($email) || empty($password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (!in_array($role, [ROLE_LECTURER, ROLE_TIMETABLER, ROLE_ADMIN])) {
        // Only allow admin to create lecturers, timetablers, or other admins
        $error_message = "Invalid role selected.";
    } else {
        $conn = db_connect();

        // 1. Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error_message = "An account with this email already exists.";
        } else {
            // 2. Hash password and insert user
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, username) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssis', $name, $email, $password_hash, $role, $email);

            if ($stmt->execute()) {
                $success_message = "New user successfully created.";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Class Plan - Add User</title>
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
            max-width: 500px;
            overflow: hidden;
        }

        .header {
            background: #4e54c8;
            color: white;
            padding: 25px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .form-body {
            padding: 40px;
        }

        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }

        .input-group input, .input-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .input-group input:focus, .input-group select:focus {
            outline: none;
            border-color: #4e54c8;
            box-shadow: 0 0 0 3px rgba(78, 84, 200, 0.2);
        }

        .btn {
            display: block;
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg, #4e54c8 0%, #8f94fb 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .message {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Add New User</h1>
            <p>For Lecturers, Timetablers, and Admins only</p>
        </div>
        <div class="form-body">
            <?php if (!empty($success_message)): ?>
                <div class="message success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
                <div class="message error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            <form action="admin_user_management.php" method="POST">
                <div class="input-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="password">Initial Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="input-group">
                    <label for="role">User Role</label>
                    <select id="role" name="role" required>
                        <option value="<?= ROLE_LECTURER ?>">Lecturer</option>
                        <option value="<?= ROLE_TIMETABLER ?>">Timetabler</option>
                        <option value="<?= ROLE_ADMIN ?>">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn">Add User</button>
            </form>
        </div>
    </div>
</body>
</html>