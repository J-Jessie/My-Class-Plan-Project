<?php
// We only need the auth file to check the session
require_once __DIR__.'/auth.php';

// Check if a user is already logged in.
if (is_logged_in()) {
    // If they are, redirect them to their dashboard
    redirect_by_role();
}
// If not, let the page load as normal for the unauthenticated user.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Class Plan - Home</title>
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
            flex-direction: column;
            color: white;
        }
        
        header {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 20px;
            backdrop-filter: blur(10px);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 28px;
            font-weight: 700;
            display: flex;
            align-items: center;
        }
        
        .logo i {
            margin-right: 10px;
            color: #ffcc00;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav li {
            margin-left: 25px;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        nav a:hover, nav a.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .btn {
            background: #4e54c8;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #3a3eb3;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid white;
        }
        
        .btn-outline:hover {
            background: white;
            color: #4e54c8;
        }
        
        .hero {
            text-align: center;
            padding: 80px 20px;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .hero p {
            font-size: 20px;
            margin-bottom: 40px;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        
        .features {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            padding: 60px 20px;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 30px;
            width: 300px;
            text-align: center;
            backdrop-filter: blur(10px);
            transition: transform 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-card i {
            font-size: 50px;
            margin-bottom: 20px;
            color: #ffcc00;
        }
        
        .feature-card h3 {
            font-size: 22px;
            margin-bottom: 15px;
        }
        
        .feature-card p {
            opacity: 0.8;
            line-height: 1.5;
        }
        
        footer {
            text-align: center;
            padding: 30px;
            background-color: rgba(0, 0, 0, 0.2);
            margin-top: auto;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
        }
        
        .footer-links a {
            color: white;
            text-decoration: none;
            opacity: 0.7;
            transition: opacity 0.3s;
        }
        
        .footer-links a:hover {
            opacity: 1;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                text-align: center;
            }
            
            nav ul {
                margin-top: 20px;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            nav li {
                margin: 5px;
            }
            
            .hero h1 {
                font-size: 36px;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .features {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <i class="fas fa-graduation-cap"></i>
            <span>My Class Plan</span>
        </div>
        <nav>
            <ul>
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="features.php">Features</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php" class="btn">Sign Up</a></li>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <h1>Organize Your Academic Journey With Ease</h1>
        <p>My Class Plan is the ultimate platform for students, teachers, and administrators to manage courses, assignments, and schedules in one place.</p>
        <div class="hero-buttons">
            <a href="signup.php" class="btn">Get Started</a>
            <a href="features.php" class="btn btn-outline">Learn More</a>
        </div>
    </section>

    <section class="features">
        <div class="feature-card">
            <i class="fas fa-calendar-alt"></i>
            <h3>Schedule Management</h3>
            <p>Plan your classes, assignments, and exams with our intuitive calendar system.</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-tasks"></i>
            <h3>Assignment Tracking</h3>
            <p>Never miss a deadline with our smart assignment tracking and notification system.</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-users"></i>
            <h3>Collaboration Tools</h3>
            <p>Work together with classmates and teachers in a seamless collaborative environment.</p>
        </div>
    </section>

    <footer>
        <p>&copy; 2023 My Class Plan. All rights reserved.</p>
        <div class="footer-links">
            <a href="privacy.php">Privacy Policy</a>
            <a href="terms.php">Terms of Service</a>
            <a href="contact.php">Contact Us</a>
        </div>
    </footer>

    <script>
        // Simple animation for feature cards
        document.addEventListener('DOMContentLoaded', function() {
            const featureCards = document.querySelectorAll('.feature-card');
            
            featureCards.forEach((card, index) => {
                // Add delay for animation
                card.style.opacity = 0;
                card.style.transition = 'opacity 0.5s ease, transform 0.3s ease';
                
                setTimeout(() => {
                    card.style.opacity = 1;
                }, 300 + (index * 200));
            });
        });
    </script>
</body>
</html>