<?php
// About Us page
require "config.php";
$isLoggedIn = isset($_SESSION['user']);
$user = $_SESSION['user'] ?? null;
?>
<link rel="stylesheet" href="assets/login-page-styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="login-page-wrapper">
    <!-- Top Navigation -->
    <div class="top-nav">
        <div class="brand-logo">Flarify</div>
        <div class="nav-menu">
            <a href="index.php?page=<?= $isLoggedIn ? 'dashboard' : 'login' ?>" class="nav-link">HOME</a>
            <a href="index.php?page=about" class="nav-link active">ABOUT US</a>
            <a href="index.php?page=contact" class="nav-link">CONTACT</a>
            <?php if ($isLoggedIn): ?>
                <a href="index.php?page=profile" class="nav-link"><i class="fas fa-user"></i> PROFILE</a>
            <?php else: ?>
                <a href="index.php?page=login" class="nav-link">LOGIN</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="about-content">
        <div class="about-container">
            <h1 class="about-title">About <span class="brand-highlight">Flarify</span></h1>
            <p class="about-subtitle">#GameItUp - Where Ideas Meet Innovation</p>
            
            <div class="about-sections">
                <div class="about-card">
                    <div class="card-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3>Our Mission</h3>
                    <p>Flarify is dedicated to empowering game developers, testers, and investors to collaborate seamlessly. We provide a platform where innovative game ideas come to life through the power of community and funding.</p>
                </div>
                
                <div class="about-card">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>For Developers</h3>
                    <p>Upload your games, showcase your creativity, and connect with testers who can provide valuable feedback and investors who believe in your vision.</p>
                </div>
                
                <div class="about-card">
                    <div class="card-icon">
                        <i class="fas fa-flask"></i>
                    </div>
                    <h3>For Testers</h3>
                    <p>Discover exciting new games, play exclusive demos, and help shape the future of gaming by providing crucial feedback to developers.</p>
                </div>
                
                <div class="about-card">
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>For Investors</h3>
                    <p>Find promising game projects, connect directly with talented developers, and invest in the next big hit in the gaming industry.</p>
                </div>
            </div>
            
            <div class="about-footer">
                <h2>Join Our Community Today</h2>
                <p>Whether you're a developer, tester, or investor, Flarify is your platform to build, innovate, and manage your games with ease.</p>
                <div class="cta-buttons">
                    <button onclick="window.location.href='index.php?page=signup'" class="btn-login">Get Started</button>
                    <button onclick="window.location.href='index.php?page=contact'" class="btn-signup">Contact Us</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.about-content {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 50px 80px;
    color: white;
}

.about-container {
    max-width: 1200px;
    width: 100%;
}

.about-title {
    font-size: 3.5rem;
    font-weight: 400;
    margin: 0 0 10px 0;
    text-align: center;
}

.about-subtitle {
    font-size: 1.2rem;
    text-align: center;
    margin: 0 0 50px 0;
    opacity: 0.9;
}

.about-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-bottom: 50px;
}

.about-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 30px;
    border-radius: 20px;
    text-align: center;
    transition: transform 0.3s, background 0.3s;
}

.about-card:hover {
    transform: translateY(-10px);
    background: rgba(255, 255, 255, 0.15);
}

.card-icon {
    font-size: 3rem;
    margin-bottom: 20px;
    color: white;
}

.about-card h3 {
    font-size: 1.5rem;
    margin: 0 0 15px 0;
}

.about-card p {
    font-size: 1rem;
    line-height: 1.6;
    margin: 0;
    opacity: 0.9;
}

.about-footer {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 40px;
    border-radius: 20px;
    text-align: center;
}

.about-footer h2 {
    font-size: 2rem;
    margin: 0 0 15px 0;
}

.about-footer p {
    font-size: 1.1rem;
    margin: 0 0 30px 0;
    opacity: 0.9;
}

.cta-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
}

.cta-buttons .btn-login,
.cta-buttons .btn-signup {
    padding: 15px 40px;
    font-size: 1rem;
    border-radius: 25px;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
}

.cta-buttons .btn-login {
    background: white;
    color: #9B59FF;
}

.cta-buttons .btn-login:hover {
    background: #f0f0f0;
}

.cta-buttons .btn-signup {
    background: transparent;
    color: white;
    border: 2px solid white;
}

.cta-buttons .btn-signup:hover {
    background: rgba(255, 255, 255, 0.1);
}

@media (max-width: 768px) {
    .about-title {
        font-size: 2.5rem;
    }
    
    .about-sections {
        grid-template-columns: 1fr;
    }
    
    .cta-buttons {
        flex-direction: column;
    }
    
    .cta-buttons .btn-login,
    .cta-buttons .btn-signup {
        width: 100%;
    }
}
</style>
