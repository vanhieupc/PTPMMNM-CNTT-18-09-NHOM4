<?php
// login.php
session_start();
require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Kiểm tra định dạng bắt buộc phải kết thúc bằng @gmail.com
    if (!preg_match('/@gmail\.com$/i', $email)) {
        $error = "Hệ thống chỉ chấp nhận tài khoản có đuôi @gmail.com để đăng nhập!";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'] ?? explode('@', $email)[0];
            $_SESSION['login_time'] = date('Y-m-d H:i:s');
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Email hoặc mật khẩu không chính xác!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - FileShare</title>
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

            background-image: linear-gradient(135deg, rgba(11, 19, 43, 0.25), rgba(7, 13, 27, 0.75)), url('img/backgroud.png');
            
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .login-card {
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
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="text-primary fs-1 mb-2"><i class="fa-solid fa-cloud"></i></div>
            <h3 class="fw-bold text-dark mb-1">Đăng Nhập</h3>
            <p class="text-muted small">Vui lòng sử dụng tài khoản @gmail.com</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small text-center" id="errorAlert">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="alert alert-danger py-2 small text-center" id="jsErrorAlert" style="display: none;">
            <i class="fa-solid fa-triangle-exclamation me-1"></i> Hệ thống bắt buộc phải sử dụng đuôi @gmail.com!
        </div>

        <form action="login.php" method="POST" id="loginForm">
            <div class="mb-3">
                <label class="form-label text-muted small fw-semibold">Email (@gmail.com)</label>
                <input type="email" id="emailInput" name="email" class="form-control py-2.5" placeholder="nhap_tai_khoan@gmail.com" required>
            </div>
            <div class="mb-4">
                <label class="form-label text-muted small fw-semibold">Mật khẩu</label>
                <input type="password" name="password" class="form-control py-2.5" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-custom-blue shadow-sm">Đăng Nhập</button>
        </form>

        <div class="text-center mt-4">
            <span class="text-muted small">Chưa có tài khoản?</span> 
            <a href="register.php" class="text-decoration-none fw-semibold small text-primary">Đăng ký ngay</a>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('emailInput').value.trim();
            const jsError = document.getElementById('jsErrorAlert');
            
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