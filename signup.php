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
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        .form-group input:focus, .form-group select:focus {
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
        
        .form-group.error input, .form-group.error select {
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
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 42px;
            cursor: pointer;
            color: #777;
        }
        
        .user-type-info {
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            margin-top: 8px;
            font-size: 14px;
            color: #495057;
            display: none;
        }
        
        .user-type-info i {
            color: #4e54c8;
            margin-right: 5px;
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
                
                <div class="form-group" id="user-type-group">
                    <label for="user-type">I am a...</label>
                    <select id="user-type" name="user-type">
                        <option value="">Select user type</option>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                        <option value="administrator">Administrator</option>
                    </select>
                    <div class="error">Please select your user type</div>
                    
                    <div class="user-type-info" id="student-info">
                        <i class="fas fa-info-circle"></i>
                        Students can join classes, view assignments, and track progress.
                    </div>
                    <div class="user-type-info" id="teacher-info">
                        <i class="fas fa-info-circle"></i>
                        Teachers can create classes, assign work, and manage students.
                    </div>
                    <div class="user-type-info" id="administrator-info">
                        <i class="fas fa-info-circle"></i>
                        Administrators can manage users, courses, and system settings.
                    </div>
                </div>
                
                <div class="form-group" id="password-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a password">
                    <span class="password-toggle" id="password-toggle">
                        <i class="far fa-eye"></i>
                    </span>
                    <div class="error">Password must be at least 8 characters</div>
                </div>
                
                <div class="form-group" id="confirm-password-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password">
                    <span class="password-toggle" id="confirm-password-toggle">
                        <i class="far fa-eye"></i>
                    </span>
                    <div class="error">Passwords do not match</div>
                </div>
                
                <button type="submit" class="btn">Sign Up</button>
                
                <div class="success-message" id="success-message">
                    <i class="fas fa-check-circle"></i> Account created successfully! Redirecting to login...
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
            const userTypeSelect = document.getElementById('user-type');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm-password');
            const successMessage = document.getElementById('success-message');
            const passwordToggle = document.getElementById('password-toggle');
            const confirmPasswordToggle = document.getElementById('confirm-password-toggle');
            
            // Validate on input change
            nameInput.addEventListener('input', () => validateName());
            emailInput.addEventListener('input', () => validateEmail());
            userTypeSelect.addEventListener('change', () => {
                validateUserType();
                showUserTypeInfo();
            });
            passwordInput.addEventListener('input', () => validatePassword());
            confirmPasswordInput.addEventListener('input', () => validateConfirmPassword());
            
            // Toggle password visibility
            passwordToggle.addEventListener('click', function() {
                togglePasswordVisibility(passwordInput, this);
            });
            
            confirmPasswordToggle.addEventListener('click', function() {
                togglePasswordVisibility(confirmPasswordInput, this);
            });
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const isNameValid = validateName();
                const isEmailValid = validateEmail();
                const isUserTypeValid = validateUserType();
                const isPasswordValid = validatePassword();
                const isConfirmPasswordValid = validateConfirmPassword();
                
                if (isNameValid && isEmailValid && isUserTypeValid && isPasswordValid && isConfirmPasswordValid) {
                    // Form is valid - show success message
                    successMessage.style.display = 'block';
                    
                    // In a real application, you would send data to the server here
                    console.log('Form submitted successfully');
                    console.log('Name:', nameInput.value);
                    console.log('Email:', emailInput.value);
                    console.log('User Type:', userTypeSelect.value);
                    
                    // Reset form after successful submission
                    setTimeout(() => {
                        form.reset();
                        successMessage.style.display = 'none';
                        hideAllUserTypeInfo();
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
            
            function validateUserType() {
                const userTypeGroup = document.getElementById('user-type-group');
                if (userTypeSelect.value === '') {
                    userTypeGroup.classList.add('error');
                    return false;
                } else {
                    userTypeGroup.classList.remove('error');
                    return true;
                }
            }
            
            function showUserTypeInfo() {
                // Hide all info first
                hideAllUserTypeInfo();
                
                // Show the selected one
                const selectedValue = userTypeSelect.value;
                if (selectedValue) {
                    document.getElementById(`${selectedValue}-info`).style.display = 'block';
                }
            }
            
            function hideAllUserTypeInfo() {
                document.querySelectorAll('.user-type-info').forEach(info => {
                    info.style.display = 'none';
                });
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
