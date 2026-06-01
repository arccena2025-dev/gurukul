<?php
/**
 * ========================================================
 * SECURE ADMIN LOGIN PORTAL (GURUKUL)
 * ========================================================
 */

require_once '../config/db.php';
require_once 'includes/auth.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

$error_msg = "";

// Handle POST authentication request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error_msg = "Please fill in all security fields.";
    } else {
        try {
            // Prepared statement query to fetch admin account details via username or email
            $stmt = $pdo->prepare("SELECT * FROM `admins` WHERE `username` = :username OR `email` = :email LIMIT 1");
            $stmt->execute([
                ':username' => $username,
                ':email'    => $username
            ]);
            $admin = $stmt->fetch();
            
            // Password verification using secure blow-fish bcrypt hashes
            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Initialize secure session records
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['is_first_login'] = $admin['is_first_login'];
                
                // Track client credentials to prevent hijacking
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['session_created'] = time();
                
                // Redirect to main admin dashboard
                header("Location: index.php");
                exit();
            } else {
                // Generic error message to prevent account enumeration
                $error_msg = "Invalid username or password credentials.";
            }
        } catch (PDOException $e) {
            $error_msg = "A database error occurred. Contact the technical team.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Gurukul Academy</title>
    
    <!-- Outfit & Plus Jakarta Sans typography -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Favicon Branding -->
    <link rel="icon" type="image/png" href="../images/Logo PNG.png">
    
    <!-- Login Page specific premium custom CSS styling -->
    <style>
        :root {
            --primary: #0f172a;
            --primary-light: #1e293b;
            --secondary: #0f766e;
            --secondary-light: #0d9488;
            --accent: #d97706;
            --accent-light: #f59e0b;
            --bg-dark: #090d16;
            --bg-white: #ffffff;
            --text-muted: #64748b;
            --glass-bg: rgba(15, 23, 42, 0.75);
            --glass-border: rgba(255, 255, 255, 0.08);
            --border-radius-md: 16px;
            --border-radius-sm: 8px;
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.25);
            --font-heading: 'Outfit', sans-serif;
            --font-body: 'Plus Jakarta Sans', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-body);
            background-color: var(--bg-dark);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient floating graphic glows */
        body::before {
            content: '';
            position: absolute;
            top: -10%;
            right: -10%;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(13, 148, 136, 0.15) 0%, transparent 60%);
            filter: blur(80px);
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -10%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(217, 119, 6, 0.1) 0%, transparent 60%);
            filter: blur(80px);
            pointer-events: none;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius-md);
            padding: 40px;
            box-shadow: var(--shadow-lg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            position: relative;
            z-index: 5;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-box {
            display: inline-flex;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--accent) 100%);
            color: #ffffff;
            font-family: var(--font-heading);
            font-size: 1.6rem;
            font-weight: 800;
            border-radius: var(--border-radius-sm);
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
        }

        .login-header h1 {
            font-family: var(--font-heading);
            font-size: 1.8rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 6px;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .alert {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            padding: 12px 16px;
            border-radius: var(--border-radius-sm);
            font-size: 0.9rem;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 8px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .input-control {
            width: 100%;
            height: 48px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius-sm);
            padding: 0 16px;
            font-family: inherit;
            color: #ffffff;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .input-control:focus {
            border-color: var(--secondary-light);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 12px rgba(13, 148, 136, 0.2);
            outline: none;
        }

        .btn-submit {
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
            color: #ffffff;
            border: none;
            border-radius: 30px;
            font-family: var(--font-heading);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(13, 148, 136, 0.3);
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 148, 136, 0.45);
        }

        .login-footer {
            margin-top: 28px;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .login-footer a {
            color: var(--accent-light);
            text-decoration: none;
            transition: color 0.2s ease;
            font-weight: 500;
        }

        .login-footer a:hover {
            color: #ffffff;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <div class="logo-box">G</div>
            <h1>Gurukul CMS</h1>
            <p>Authorized Administrator Access</p>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span><?php echo $error_msg; ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['err']) && $_GET['err'] === 'hijack'): ?>
            <div class="alert" style="background: rgba(217, 119, 6, 0.15); border-color: rgba(217, 119, 6, 0.3); color: #fbbf24;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <span>Session reset for security. Please re-login.</span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" autocomplete="off">
            <div class="form-group">
                <label for="username">Admin Username</label>
                <input type="text" name="username" id="username" class="input-control" required placeholder="Enter username">
            </div>

            <div class="form-group">
                <label for="password">Security Password</label>
                <input type="password" name="password" id="password" class="input-control" required placeholder="Enter password">
            </div>

            <button type="submit" class="btn-submit">
                <span>Secure Log In</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </button>
        </form>

        <div class="login-footer">
            <p>Forgot password? Contact <a href="mailto:support@gurukul.edu">Web Administration</a></p>
            <p style="margin-top: 12px;"><a href="../index.php">&larr; Return to Public Website</a></p>
        </div>
    </div>

</body>
</html>
