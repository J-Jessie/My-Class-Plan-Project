<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Class Plan - Sign Up</title>
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
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .login-link a {
            color: #4e54c8;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Create Account</h1>
        </div>
        
        <div class="form-container">
            <form id="signup-form">
                <div class="form-group" id="name-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name">
                    <div class="error">Please enter your full name</div>
                </div>
                
                <div class="form-group" id="email-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email">
                    <div class="error">Please enter a valid email address</div>
                </div>
                
                <div class="form-group" id="password-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a password">
                    <div class="error">Password must be at least 8 characters</div>
                </div>
                
                <div class="form-group" id="confirm-password-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password">
                    <div class="error">Passwords do not match</div>
                </div>
                
                <button type="submit" class="btn">Sign Up</button>
                
                <div class="success-message" id="success-message">
                    Account created successfully! Redirecting to login...
                </div>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="#">Log In</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('signup-form');
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm-password');
            const successMessage = document.getElementById('success-message');
            
            // Validate on input change
            nameInput.addEventListener('input', () => validateName());
            emailInput.addEventListener('input', () => validateEmail());
            passwordInput.addEventListener('input', () => validatePassword());
            confirmPasswordInput.addEventListener('input', () => validateConfirmPassword());
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const isNameValid = validateName();
                const isEmailValid = validateEmail();
                const isPasswordValid = validatePassword();
                const isConfirmPasswordValid = validateConfirmPassword();
                
                if (isNameValid && isEmailValid && isPasswordValid && isConfirmPasswordValid) {
                    // Form is valid - show success message
                    successMessage.style.display = 'block';
                    
                    // In a real application, you would send data to the server here
                    console.log('Form submitted successfully');
                    console.log('Name:', nameInput.value);
                    console.log('Email:', emailInput.value);
                    
                    // Reset form after successful submission
                    setTimeout(() => {
                        form.reset();
                        successMessage.style.display = 'none';
                    }, 3000);
                }
            });
            
            function validateName() {
                const nameGroup = document.getElementById('name-group');
                if (nameInput.value.trim() === '') {
                    nameGroup.classList.add('error');
                    return false;
                } else {
                    nameGroup.classList.remove('error');
                    return true;
                }
            }
            
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
        });
    </script>
</body>
</html>
