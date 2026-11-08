<?php
// register.php
session_start();
require_once 'config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Kiểm tra định dạng bắt buộc phải kết thúc bằng @gmail.com
    if (!preg_match('/@gmail\.com$/i', $email)) {
        $error = "Hệ thống chỉ cho phép đăng ký tài khoản bằng email có đuôi @gmail.com!";
    } else {
        // Kiểm tra email đã tồn tại chưa
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_check->execute([$email]);
        if ($stmt_check->rowCount() > 0) {
            $error = "Email này đã được đăng ký trên hệ thống!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt_insert->execute([$username, $email, $hashed_password])) {
                $success = "Đăng ký thành công! Đang chuyển hướng đến trang đăng nhập...";
                header("refresh:2;url=login.php");
            } else {
                $error = "Đã xảy ra lỗi khi đăng ký, vui lòng thử lại!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - FileShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background-color: #070d1b; 
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: linear-gradient(135deg, rgba(11, 19, 43, 0.9), rgba(7, 13, 27, 0.95)), url('img/backgroud.png');
            background-size: cover;
            background-position: center;
        }
        .register-card {
            max-width: 440px;
            width: 100%;
            background: #ffffff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }
        .btn-custom-blue {
            background-color: #3b82f6;
            border: none;
            color: #fff;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            transition: 0.2s;
        }
        .btn-custom-blue:hover {
            background-color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="text-center mb-4">
            <div class="text-primary fs-1 mb-2"><i class="fa-solid fa-cloud"></i></div>
            <h3 class="fw-bold text-dark mb-1">Tạo Tài Khoản</h3>
            <p class="text-muted small">Chỉ chấp nhận đăng ký bằng tài khoản @gmail.com</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small text-center">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success py-2 small text-center">
                <i class="fa-solid fa-check-circle me-1"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div class="alert alert-danger py-2 small text-center" id="jsErrorAlert" style="display: none;">
            <i class="fa-solid fa-triangle-exclamation me-1"></i> Email bắt buộc phải có đuôi @gmail.com!
        </div>

        <form action="register.php" method="POST" id="registerForm">
            <div class="mb-3">
                <label class="form-label text-muted small fw-semibold">Họ và tên</label>
                <input type="text" name="username" class="form-control py-2.5" placeholder="Phạm Anh Quân" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small fw-semibold">Email (@gmail.com)</label>
                <input type="email" id="emailInput" name="email" class="form-control py-2.5" placeholder="quan@gmail.com" required>
            </div>
            <div class="mb-4">
                <label class="form-label text-muted small fw-semibold">Mật khẩu</label>
                <input type="password" name="password" class="form-control py-2.5" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-custom-blue shadow-sm">Đăng Ký Tài Khoản</button>
        </form>

        <div class="text-center mt-4">
            <span class="text-muted small">Đã có tài khoản?</span> 
            <a href="login.php" class="text-decoration-none fw-semibold small text-primary">Đăng nhập ngay</a>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const email = document.getElementById('emailInput').value.trim();
            const jsError = document.getElementById('jsErrorAlert');
            
            // Kiểm tra bắt buộc kết thúc bằng @gmail.com
            if (!email.toLowerCase().endsWith('@gmail.com')) {
                e.preventDefault();
                jsError.style.display = 'block';
                document.getElementById('emailInput').focus();
            } else {
                jsError.style.display = 'none';
            }
        });
    </script>
</body>
</html>