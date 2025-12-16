# Email Setup Guide for Flarify

This guide will help you set up email functionality for password resets, notifications, and 2FA.

## Option 1: Using Gmail (Easiest for Testing)

### Step 1: Enable 2-Step Verification
1. Go to [Google Account Security](https://myaccount.google.com/security)
2. Enable "2-Step Verification"

### Step 2: Generate App Password
1. Go to [App Passwords](https://myaccount.google.com/apppasswords)
2. Select "Mail" and "Windows Computer"
3. Click "Generate"
4. Copy the 16-character password

### Step 3: Install PHPMailer
Open PowerShell in your project folder and run:
```powershell
composer require phpmailer/phpmailer
```

If you don't have Composer, download PHPMailer manually:
1. Go to https://github.com/PHPMailer/PHPMailer/releases
2. Download and extract to `flarify 2/PHPMailer/`
3. Uncomment the manual require lines in `email_helper.php`

### Step 4: Configure email_helper.php
Open `backend/email_helper.php` and update:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-16-char-app-password'); // From Step 2
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'Flarify');
define('SMTP_ENCRYPTION', 'tls');
```

### Step 5: Enable Email in request_reset.php
Uncomment this line:
```php
require "email_helper.php";
```

## Option 2: Using SendGrid (Recommended for Production)

### Step 1: Sign up for SendGrid
1. Go to https://sendgrid.com/
2. Create free account (100 emails/day free)
3. Verify your sender identity

### Step 2: Get API Key
1. Go to Settings > API Keys
2. Create API Key
3. Copy the key

### Step 3: Configure email_helper.php
```php
define('SMTP_HOST', 'smtp.sendgrid.net');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'apikey');
define('SMTP_PASSWORD', 'your-sendgrid-api-key');
define('SMTP_FROM_EMAIL', 'your-verified-email@yourdomain.com');
define('SMTP_FROM_NAME', 'Flarify');
define('SMTP_ENCRYPTION', 'tls');
```

## Option 3: Using Mailgun

### Step 1: Sign up for Mailgun
1. Go to https://www.mailgun.com/
2. Create account
3. Add and verify your domain

### Step 2: Get SMTP Credentials
1. Go to Sending > Domain Settings > SMTP credentials
2. Copy username and password

### Step 3: Configure email_helper.php
```php
define('SMTP_HOST', 'smtp.mailgun.org');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'postmaster@your-domain.mailgun.org');
define('SMTP_PASSWORD', 'your-mailgun-password');
define('SMTP_FROM_EMAIL', 'noreply@your-domain.com');
define('SMTP_FROM_NAME', 'Flarify');
define('SMTP_ENCRYPTION', 'tls');
```

## Testing Email Functionality

### Test Password Reset Email
1. Go to http://localhost:8080/flarify%202/backend/request_reset.php
2. Enter your email
3. Check your inbox for the reset link

### Check if Emails are Working
Look at the console logs in your browser or check:
- `backend/request_reset.php` - Shows if email was sent
- PHP error logs - Shows any SMTP errors

## Using Email Functions in Your Code

### Send Password Reset
```php
require "email_helper.php";
$result = sendPasswordResetEmail($email, $name, $token);
if ($result['success']) {
    echo "Email sent!";
}
```

### Send 2FA Code
```php
require "email_helper.php";
$code = rand(100000, 999999); // Generate 6-digit code
$result = send2FAEmail($email, $name, $code);
```

### Send Custom Notification
```php
require "email_helper.php";
$result = sendNotificationEmail(
    $email, 
    $name, 
    "New Investment", 
    "You received a $5,000 investment in your game!",
    "http://localhost:8080/flarify%202/index.php?page=portfolio",
    "View Portfolio"
);
```

## Implementing 2FA

To implement 2FA, you'll need to:

1. **Create 2FA codes table**:
```sql
CREATE TABLE two_factor_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(6) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);
```

2. **Generate and send code on login**:
```php
// After password verification in login
$code = sprintf("%06d", rand(0, 999999));
$pdo->prepare("INSERT INTO two_factor_codes (user_id, code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))")
    ->execute([$user['id'], $code]);
send2FAEmail($user['email'], $user['name'], $code);
```

3. **Verify code on 2FA page**:
```php
$stmt = $pdo->prepare("SELECT * FROM two_factor_codes WHERE user_id=? AND code=? AND expires_at > NOW()");
$stmt->execute([$user_id, $code]);
if ($stmt->fetch()) {
    // Code valid - complete login
    $_SESSION['user'] = $user;
}
```

## Troubleshooting

### "SMTP connect() failed"
- Check your username and password
- Verify SMTP host and port
- Check if firewall is blocking port 587

### "Could not authenticate"
- For Gmail: Use App Password, not regular password
- Verify credentials are correct
- Check if 2FA is enabled (required for App Passwords)

### Emails go to spam
- Use a verified domain
- Add SPF and DKIM records
- Use a reputable SMTP service (SendGrid, Mailgun)

### Email not received
- Check spam folder
- Verify email address is correct
- Check error logs for SMTP errors

## Current Status

Right now, the system works in "development mode" - it shows the reset link directly on the page instead of emailing it. This lets you test the reset functionality without setting up email.

Once you configure `email_helper.php` and uncomment the require line in `request_reset.php`, it will automatically start sending actual emails.
