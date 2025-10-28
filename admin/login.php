<?php

/**
 * Admin Login Page
 * NC-Admission System
 */

session_start();

// ถ้า Login แล้ว -> Redirect ไป Dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

// Include Database
require_once '../config/database.php';
require_once '../includes/functions.php';

$error_message = '';
$success_message = '';

// ตรวจสอบ Session Timeout
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $error_message = 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่';
}

// Process Login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_message = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    } else {
        // Query Admin User
        $sql = "SELECT * FROM admin_users WHERE username = ? AND is_active = 1 LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();

            // Verify Password
            if (password_verify($password, $admin['password'])) {
                // Login Success
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_fullname'] = $admin['fullname'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['last_activity'] = time();

                // Update Last Login
                $update_sql = "UPDATE admin_users SET last_login = NOW() WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("i", $admin['id']);
                $update_stmt->execute();

                // Redirect to Dashboard
                header("Location: index.php?page=dashboard");
                exit();
            } else {
                $error_message = 'รหัสผ่านไม่ถูกต้อง';
            }
        } else {
            $error_message = 'ไม่พบชื่อผู้ใช้นี้ในระบบ หรือบัญชีถูกระงับ';
        }
    }
}

$site_name = get_setting('site_name', 'NC-Admission');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - E-Pers</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/logo.png">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/custom.css">

    <style>
        body {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            max-width: 600px;
            width: 100%;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .login-header img {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }

        .login-header h4 {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
        }

        .form-control:focus {
            border-color: #4facfe;
            box-shadow: 0 0 0 0.2rem rgba(79, 172, 254, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
            color: white;
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        .back-home {
            text-align: center;
            margin-top: 20px;
        }

        .back-home a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-home a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <img src="../assets/images/logo.png" alt="Logo">
                <h4>E-Pers NC-Admission</h4>
                <p class="mb-0 small">ระบบจัดการหลังบ้าน</p>
            </div>

            <!-- Body -->
            <div class="login-body">
                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <!-- Username -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-person me-1"></i> ชื่อผู้ใช้
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person-circle"></i>
                            </span>
                            <input type="text"
                                class="form-control"
                                name="username"
                                placeholder="กรอกชื่อผู้ใช้"
                                required
                                autofocus>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-lock me-1"></i> รหัสผ่าน
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-shield-lock"></i>
                            </span>
                            <input type="password"
                                class="form-control"
                                name="password"
                                id="password"
                                placeholder="กรอกรหัสผ่าน"
                                required>
                            <button class="btn btn-outline-secondary"
                                type="button"
                                id="togglePassword">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">
                            จดจำการเข้าสู่ระบบ
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-login w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        เข้าสู่ระบบ
                    </button>
                </form>

                <!-- Divider -->
                <div class="text-center my-3">
                    <small class="text-muted">หรือ</small>
                </div>

                <!-- Back to Home -->
                <a href="../index.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-house me-2"></i>
                    กลับสู่หน้าหลัก
                </a>
            </div>
        </div>

        <!-- Back Home Link -->
        <div class="back-home">
            <p class="mb-0">
                <i class="bi bi-shield-check me-2"></i>
                <strong>ระบบปลอดภัย</strong> | ข้อมูลถูกเข้ารหัส
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>

    <!-- Toggle Password Visibility -->
    <script>
        document.getElementById('togglePassword')?.addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        });

        // Auto-dismiss alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>

</body>

</html>