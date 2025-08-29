<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Class Plan - Login</title>
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
        
        .header h1 {
            font-weight: 600;
            font-size: 28px;
        }
        
        .form-container {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        .form-group input:focus {
            border-color: #4e54c8;
            outline: none;
            box-shadow: 0 0 0 2px rgba(78, 84, 200, 0.2);
        }
        
        .form-group .error {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }
        
        .form-group.error input {
            border-color: #e74c3c;
        }
        
        .form-group.error .error {
            display: block;
        }
        
        .btn {
            background: #4e54c8;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 6px;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #3a3eb3;
        }
        
        .signup-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .signup-link a {
            color: #4e54c8;
            text-decoration: none;
            font-weight: 500;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 42px;
            cursor: pointer;
            color: #777;
        }
        
        .success-message {
            background: #2ecc71;
            color: white;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            margin-top: 20px;
            display: none;
        }
        
        .error-message {
            background: #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            margin-top: 20px;
            display: none;
        }
        
        .back-to-home {
            text-align: center;
            margin-top: 15px;
        }
        
        .back-to-home a {
            color: #4e54c8;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .back-to-home a:hover {
            text-decoration: underline;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
        }
        
        .remember-me input {
            margin-right: 8px;
        }
        
        .forgot-password a {
            color: #4e54c8;
            text-decoration: none;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome Back</h1>
        </div>
        
        <div class="form-container">
            <form id="login-form">
                <div class="form-group" id="email-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email">
                    <div class="error">Please enter a valid email address</div>
                </div>
                
                <div class="form-group" id="password-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password">
                    <span class="password-toggle" id="password-toggle">
                        <i class="far fa-eye"></i>
                    </span>
                    <div class="error">Please enter your password</div>
                </div>
                
                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <div class="forgot-password">
                        <a href="forgot-password.php">Forgot Password?</a>
                    </div>
                </div>
                
                <button type="submit" class="btn">Log In</button>
                
                <div class="success-message" id="success-message">
                    <i class="fas fa-check-circle"></i> Login successful! Redirecting to dashboard...
                </div>
                
                <div class="error-message" id="error-message">
                    <i class="fas fa-exclamation-circle"></i> Invalid email or password. Please try again.
                </div>
            </form>
            
            <div class="signup-link">
                Don't have an account? <a href="signup.php">Sign Up</a>
            </div>
            
            <div class="back-to-home">
                <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('login-form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const successMessage = document.getElementById('success-message');
            const errorMessage = document.getElementById('error-message');
            const passwordToggle = document.getElementById('password-toggle');
            
            // Validate on input change
            emailInput.addEventListener('input', () => validateEmail());
            passwordInput.addEventListener('input', () => validatePassword());
            
            // Toggle password visibility
            passwordToggle.addEventListener('click', function() {
                togglePasswordVisibility(passwordInput, this);
            });
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const isEmailValid = validateEmail();
                const isPasswordValid = validatePassword();
                
                if (isEmailValid && isPasswordValid) {
                    // In a real application, you would send data to the server here
                    // This is a simulation of successful login
                    simulateLogin();
                }
            });
            
            function validateEmail() {
                const emailGroup = document.getElementById('email-group');
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (!emailRegex.test(emailInput.value)) {
                    emailGroup.classList.add('error');
                    return false;
                } else {
                    emailGroup.classList.remove('error');
                    return true;
                }
            }
            
            function validatePassword() {
                const passwordGroup = document.getElementById('password-group');
                if (passwordInput.value.length < 1) {
                    passwordGroup.classList.add('error');
                    return false;
                } else {
                    passwordGroup.classList.remove('error');
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
            
            function simulateLogin() {
                // This is a simulation - in a real application, you would make an API call
                const demoEmail = 'user@example.com';
                const demoPassword = 'password123';
                
                if (emailInput.value === demoEmail && passwordInput.value === demoPassword) {
                    // Successful login
                    successMessage.style.display = 'block';
                    errorMessage.style.display = 'none';
                    
                    // Redirect to dashboard after successful login
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 2000);
                } else {
                    // Failed login
                    errorMessage.style.display = 'block';
                    successMessage.style.display = 'none';
                    
                    // Clear error message after 3 seconds
                    setTimeout(() => {
                        errorMessage.style.display = 'none';
                    }, 3000);
                }
            }
        });
    </script>
</body>
</html>
