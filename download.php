<?php
// download.php
session_start();
require_once 'config/db.php';

// Hàm hiển thị trang lỗi đẹp mắt kèm nút quay lại
function showError($message) {
    echo '<!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <title>Thông báo - FileShare</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            body { background-color: #0f172a; font-family: "Segoe UI", sans-serif; }
            .card-error { max-width: 450px; border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
            .btn-primary-custom { background-color: #2563eb; border-color: #2563eb; color: #fff; border-radius: 8px; font-weight: 500; }
            .btn-primary-custom:hover { background-color: #1d4ed8; border-color: #1d4ed8; color: #fff; }
        </style>
    </head>
    <body class="d-flex align-items-center justify-content-center vh-100">
        <div class="card card-error p-4 bg-white text-center">
            <div class="text-danger fs-1 mb-3"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <h4 class="fw-bold text-dark mb-2">Thông báo liên kết</h4>
            <p class="text-muted mb-4">' . htmlspecialchars($message) . '</p>
            <a href="dashboard.php" class="btn btn-primary-custom py-2 w-100"><i class="fa-solid fa-house me-2"></i>Quay lại trang chủ</a>
        </div>
    </body>
    </html>';
    exit();
}

$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    showError("Liên kết tải xuống không hợp lệ!");
}

// Lấy thông tin share và file tương ứng từ CSDL
$stmt = $conn->prepare("
    SELECT shares.*, files.stored_name, files.filename, files.file_size 
    FROM shares 
    JOIN files ON shares.file_id = files.id 
    WHERE shares.token = ?
");
$stmt->execute([$token]);
$share = $stmt->fetch();

if (!$share) {
    showError("Liên kết chia sẻ không tồn tại hoặc đã bị xóa!");
}

// 1. Kiểm tra phân quyền truy cập (permission_type)
$permission = $share['permission_type'] ?? 'download';

// Trường hợp: KHÔNG CHO PHÉP TRUY CẬP / BỊ CHẶN (blocked)
if ($permission == 'blocked') {
    showError("Truy cập bị từ chối! Chủ sở hữu đã vô hiệu hóa hoặc chặn liên kết này.");
}

// Kiểm tra thời gian hết hạn (nếu có cấu hình)
if (!empty($share['expires_at']) && strtotime($share['expires_at']) < time()) {
    showError("Liên kết chia sẻ này đã hết hạn!");
}

// Kiểm tra mật khẩu bảo vệ (nếu có cấu hình)
$error = '';
if (!empty($share['password'])) {
    $session_key = 'auth_token_' . $token;
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password'])) {
        if (password_verify($_POST['password'], $share['password'])) {
            $_SESSION[$session_key] = true;
        } else {
            $error = "Mật khẩu không chính xác!";
        }
    }

    // Nếu chưa xác thực mật khẩu, hiển thị giao diện nhập mật khẩu
    if (!isset($_SESSION[$session_key]) || $_SESSION[$session_key] !== true) {
        ?>
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <title>Yêu cầu mật khẩu - FileShare</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                body { background-color: #0f172a; font-family: 'Segoe UI', sans-serif; }
                .card-lock { max-width: 400px; border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
                .btn-primary-custom { background-color: #2563eb; border-color: #2563eb; color: #fff; border-radius: 8px; font-weight: 500; }
                .btn-primary-custom:hover { background-color: #1d4ed8; border-color: #1d4ed8; color: #fff; }
            </style>
        </head>
        <body class="d-flex align-items-center justify-content-center vh-100">
            <div class="card card-lock p-4 bg-white">
                <div class="text-center mb-3">
                    <div class="text-primary fs-1 mb-2"><i class="fa-solid fa-lock"></i></div>
                    <h4 class="fw-bold text-dark">Tệp được bảo vệ</h4>
                    <p class="text-muted small">Tệp <strong><?php echo htmlspecialchars($share['filename']); ?></strong> yêu cầu mật khẩu để truy cập.</p>
                </div>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small text-center"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu..." required autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary-custom w-100 py-2">Tiếp tục</button>
                </form>
                <div class="text-center mt-3">
                    <a href="dashboard.php" class="text-decoration-none small text-muted"><i class="fa-solid fa-arrow-left me-1"></i> Quay lại trang chủ</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
}

// Đường dẫn thực tế của file trên server
$file_path = 'uploads/' . $share['stored_name'];

if (!file_exists($file_path)) {
    showError("Tệp thực tế không tồn tại trên hệ thống lưu trữ!");
}

// 2. Trường hợp: CHỈ XEM, KHÔNG CHO TẢI (view_only)
if ($permission == 'view_only') {
    $fileExtension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    
    if ($fileExtension == 'pdf') {
        header('Content-Type: application/pdf');
    } elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        header('Content-Type: image/' . $fileExtension);
    } else {
        showError("Tệp này được thiết lập ở chế độ chỉ xem, không hỗ trợ hiển thị định dạng này trực tiếp trên trình duyệt.");
    }
    
    header('Content-Disposition: inline; filename="' . basename($share['filename']) . '"');
    readfile($file_path);
    exit();
}

// 3. Trường hợp: CHO PHÉP TẢI XUỐNG (download) - ÁP DỤNG TRANSACTION CHỐNG TRANH CHẤP ĐỒNG THỜI
$conn->beginTransaction();

try {
    // Khóa dòng dữ liệu lại (FOR UPDATE) để ngăn các request đồng thời vượt quá giới hạn lượt tải
    $stmt_lock = $conn->prepare("SELECT * FROM shares WHERE id = ? FOR UPDATE");
    $stmt_lock->execute([$share['id']]);
    $current_share = $stmt_lock->fetch();

    // Kiểm tra giới hạn số lượt tải tối đa
    if (!empty($current_share['max_downloads']) && $current_share['download_count'] >= $current_share['max_downloads']) {
        $conn->rollBack();
        showError("Liên kết này đã đạt giới hạn số lượt tải tối đa!");
    }

    // Tăng số lượt tải trong CSDL lên 1
    $update_count = $conn->prepare("UPDATE shares SET download_count = download_count + 1 WHERE id = ?");
    $update_count->execute([$share['id']]);

    // Xác nhận hoàn tất giao dịch an toàn
    $conn->commit();
} catch (Exception $e) {
    $conn->rollBack();
    showError("Đã xảy ra lỗi hệ thống khi xử lý lượt tải!");
}

// Gửi HTTP Headers để trình duyệt tải file xuống an toàn
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($share['filename']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));
flush();
readfile($file_path);
exit();
?>