<?php
require "config.php";

// If already logged in, redirect
if (isset($_SESSION['user'])) {
    header("Location: index.php?page=dashboard");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $role = $_POST['role'] ?? '';

    // Validate inputs
    if (empty($name)) {
        $error = "Please enter your name.";
    } elseif (empty($email)) {
        $error = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (empty($password)) {
        $error = "Please enter a password.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (empty($role)) {
        $error = "Please select a role.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "An account with this email already exists.";
        } else {
            // Hash with Argon2
            $hash = password_hash($password, PASSWORD_ARGON2ID);

            try {
                $stmt = $pdo->prepare("INSERT INTO users (username,email,userpassword,userrole) VALUES (?,?,?,?)");
                $stmt->execute([$name,$email,$hash,$role]);

                $_SESSION['user'] = [
                    'id' => $pdo->lastInsertId(),
                    'username' => $name,
                    'email' => $email,
                    'userrole' => $role
                ];
                header("Location: index.php?page=dashboard");
                exit;
            } catch (PDOException $e) {
                error_log("Signup failed for $email: " . $e->getMessage());
                $error = "Registration failed. Please try again later.";
            }
        }
    }
}
?>
<link rel="stylesheet" href="assets/login-page-styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
.error-message {
    background: #fee;
    border: 1px solid #fcc;
    color: #c33;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: shakeError 0.3s ease;
}

@keyframes shakeError {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.error-message i {
    font-size: 1.1rem;
}
</style>

<div class="login-page-wrapper">
    <!-- Top Navigation -->
    <div class="top-nav">
        <div class="brand-logo">Flarify</div>
        <div class="nav-menu">
            <a href="index.php?page=login" class="nav-link">HOME</a>
            <a href="index.php?page=about" class="nav-link">ABOUT US</a>
            <a href="index.php?page=contact" class="nav-link">CONTACT</a>
            <a href="index.php?page=login" class="nav-link">LOGIN</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="login-content">
        <!-- Left Side - Welcome Section -->
        <div class="welcome-section">
            <h1 class="welcome-title">Join <span class="brand-highlight">Flarify</span></h1>
            <p class="welcome-subtitle">Choose your path: Developer, Tester, or Investor. #GameItUp</p>
        </div>

        <!-- Right Side - Signup Form -->
        <div class="login-form-container">
            <h2 class="form-title">Sign up</h2>
            <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>
            <form method="POST" class="login-form">
                <div class="input-group">
                    <i class="fas fa-user input-icon"></i>
                    <input name="name" type="text" placeholder="Full Name" class="form-input" required />
                </div>
                <div class="input-group">
                    <i class="fas fa-envelope input-icon"></i>
                    <input name="email" type="email" placeholder="Email Address" class="form-input" required />
                </div>
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input name="password" type="password" placeholder="Password" class="form-input" id="signupPassword" required />
                    <i class="fas fa-eye password-toggle" id="toggleSignupPassword"></i>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input name="confirm" type="password" placeholder="Confirm Password" class="form-input" id="signupConfirm" required />
                    <i class="fas fa-eye password-toggle" id="toggleSignupConfirm"></i>
                </div>
                <div class="role-selection-modern">
                    <label class="role-label">Select Your Role:</label>
                    <div class="role-options-grid">
                        <label class="role-card">
                            <input type="radio" name="role" value="developer" required />
                            <span class="role-name"><i class="fas fa-code"></i> Developer</span>
                        </label>
                        <label class="role-card">
                            <input type="radio" name="role" value="tester" />
                            <span class="role-name"><i class="fas fa-flask"></i> Tester</span>
                        </label>
                        <label class="role-card">
                            <input type="radio" name="role" value="investor" />
                            <span class="role-name"><i class="fas fa-money-bill-wave"></i> Investor</span>
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn-login">Create Account</button>
                <div class="divider">Already have an account?</div>
                <button type="button" onclick="window.location.href='index.php?page=login'" class="btn-signup">Log in</button>
            </form>
        </div>
    </div>
</div>
            </form>
        </div>
    </div>
</div>

<script>
// Password visibility toggle for signup
const toggleSignupPassword = document.getElementById('toggleSignupPassword');
const signupPassword = document.getElementById('signupPassword');
const toggleSignupConfirm = document.getElementById('toggleSignupConfirm');
const signupConfirm = document.getElementById('signupConfirm');

if (toggleSignupPassword && signupPassword) {
    toggleSignupPassword.addEventListener('click', function() {
        const type = signupPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        signupPassword.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
}

if (toggleSignupConfirm && signupConfirm) {
    toggleSignupConfirm.addEventListener('click', function() {
        const type = signupConfirm.getAttribute('type') === 'password' ? 'text' : 'password';
        signupConfirm.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
}
</script>