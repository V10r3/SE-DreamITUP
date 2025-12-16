<?php
// Contact page
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    if ($name && $email && $subject && $message) {
        // Log the contact message
        error_log("Contact Form - Name: $name, Email: $email, Subject: $subject");
        $success = "Thank you for contacting us! We'll get back to you soon.";
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<link rel="stylesheet" href="assets/login-page-styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<?php if ($error): ?>
<script>console.error('Contact Error: <?= addslashes($error) ?>');</script>
<?php endif; ?>
<?php if ($success): ?>
<script>console.log('Contact Success: <?= addslashes($success) ?>');</script>
<?php endif; ?>

<div class="login-page-wrapper">
    <!-- Top Navigation -->
    <div class="top-nav">
        <div class="brand-logo">Flarify</div>
        <div class="nav-menu">
            <a href="index.php?page=login" class="nav-link">HOME</a>
            <a href="index.php?page=about" class="nav-link">ABOUT US</a>
            <a href="index.php?page=contact" class="nav-link active">CONTACT</a>
            <a href="index.php?page=login" class="nav-link">LOGIN</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="login-content">
        <!-- Left Side - Contact Info -->
        <div class="welcome-section">
            <h1 class="welcome-title">Get in <span class="brand-highlight">Touch</span></h1>
            <p class="welcome-subtitle">Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
            
            <div class="contact-info">
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h4>Email</h4>
                        <p>support@flarify.com</p>
                    </div>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <div>
                        <h4>Phone</h4>
                        <p>+1 (555) 123-4567</p>
                    </div>
                </div>
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h4>Address</h4>
                        <p>123 Game Street, Tech City, TC 12345</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Contact Form -->
        <div class="login-form-container">
            <h2 class="form-title">Contact Us</h2>
            <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <p><?= htmlspecialchars($success) ?></p>
            </div>
            <?php endif; ?>
            <form method="POST" class="login-form">
                <div class="input-group">
                    <i class="fas fa-user input-icon"></i>
                    <input name="name" type="text" placeholder="Your Name" class="form-input" required />
                </div>
                <div class="input-group">
                    <i class="fas fa-envelope input-icon"></i>
                    <input name="email" type="email" placeholder="Your Email" class="form-input" required />
                </div>
                <div class="input-group">
                    <i class="fas fa-tag input-icon"></i>
                    <input name="subject" type="text" placeholder="Subject" class="form-input" required />
                </div>
                <div class="input-group textarea-group">
                    <i class="fas fa-comment input-icon"></i>
                    <textarea name="message" placeholder="Your Message" class="form-input" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn-login">Send Message</button>
                <div class="divider">Or</div>
                <button type="button" onclick="window.location.href='index.php?page=login'" class="btn-signup">Back to Home</button>
            </form>
        </div>
    </div>
</div>

<style>
.contact-info {
    margin-top: 40px;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 30px;
    color: white;
}

.contact-item i {
    font-size: 1.5rem;
    margin-top: 5px;
}

.contact-item h4 {
    margin: 0 0 5px 0;
    font-size: 1.2rem;
}

.contact-item p {
    margin: 0;
    opacity: 0.9;
}

.textarea-group {
    align-items: flex-start;
    padding-top: 15px;
}

.textarea-group .input-icon {
    margin-top: 5px;
}

.textarea-group textarea {
    resize: vertical;
    min-height: 100px;
    text-indent: 0;
    padding: 10px 0;
}

.success-message {
    background: #4CAF50;
    color: white;
    padding: 15px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.success-message i {
    font-size: 1.5rem;
}

.success-message p {
    margin: 0;
}
</style>
