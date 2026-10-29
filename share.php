<?php
// share.php
session_start();
require_once 'config/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$file_id = isset($_GET['file_id']) ? intval($_GET['file_id']) : 0;

// Kiểm tra xem file có tồn tại và thuộc quyền sở hữu của user không
$stmt = $conn->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
$stmt->execute([$file_id, $user_id]);
$file = $stmt->fetch();

if (!$file) {
    die("Tệp không tồn tại hoặc bạn không có quyền chia sẻ tệp này!");
}

$message = '';
$share_link = '';

// Kiểm tra xem file này đã có link chia sẻ trước đó chưa
$stmt_check = $conn->prepare("SELECT * FROM shares WHERE file_id = ?");
$stmt_check->execute([$file_id]);
$share = $stmt_check->fetch();

// Nếu người dùng bấm nút tạo/cập nhật link chia sẻ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $max_downloads = !empty($_POST['max_downloads']) ? intval($_POST['max_downloads']) : null;
    $permission_type = isset($_POST['permission_type']) ? trim($_POST['permission_type']) : 'download';
    
    // Xử lý chuỗi ngày tháng dạng d/m/Y thành Y-m-d để lưu vào CSDL
    $expires_at = null;
    if (!empty($_POST['expires_at'])) {
        $input_date = trim($_POST['expires_at']);
        $dt = DateTime::createFromFormat('d/m/Y', $input_date);
        if ($dt) {
            $expires_at = $dt->format('Y-m-d 23:59:59');
        }
    }

    if ($share) {
        // Cập nhật thông tin chia sẻ cũ (bao gồm permission_type)
        $update = $conn->prepare("UPDATE shares SET password = ?, expires_at = ?, max_downloads = ?, permission_type = ? WHERE file_id = ?");
        $update->execute([$password, $expires_at, $max_downloads, $permission_type, $file_id]);
        $message = "Đã cập nhật quyền chia sẻ thành công!";
    } else {
        // Tạo token ngẫu nhiên mới cho link chia sẻ
        $token = bin2hex(random_bytes(16));
        $insert = $conn->prepare("INSERT INTO shares (file_id, token, password, expires_at, max_downloads, permission_type) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->execute([$file_id, $token, $password, $expires_at, $max_downloads, $permission_type]);
        
        // Lấy lại thông tin share vừa tạo
        $stmt_check->execute([$file_id]);
        $share = $stmt_check->fetch();
        $message = "Tạo liên kết chia sẻ thành công!";
    }
}

if ($share) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $share_link = "$protocol://$host$path/download.php?token=" . $share['token'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cấu hình chia sẻ tệp - Cloudora</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', sans-serif; color: #334155; }
        .card { border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .btn-primary-custom { background-color: #2563eb; border-color: #2563eb; color: #fff; border-radius: 8px; font-weight: 500; }
        .btn-primary-custom:hover { background-color: #1d4ed8; border-color: #1d4ed8; color: #fff; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark px-4" style="background-color: #0f172a !important;">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php"><i class="fa-solid fa-cloud me-2"></i>Cloudora</a>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Quay Lại Trang</a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card p-4 bg-white">
                    <h4 class="mb-3 fw-bold text-dark">Chia sẻ tệp: <span class="text-primary"><?php echo htmlspecialchars($file['filename']); ?></span></h4>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($share_link): ?>
                        <div class="mb-4 p-3 bg-light rounded border">
                            <label class="form-label fw-bold text-dark">Liên kết chia sẻ của bạn:</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-white" id="shareLinkInput" value="<?php echo $share_link; ?>" readonly>
                                <button class="btn btn-outline-primary" type="button" onclick="copyLink()"><i class="fa-regular fa-copy me-1"></i> Sao chép</button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form action="share.php?file_id=<?php echo $file_id; ?>" method="POST">
                        <hr class="my-4">
                        <h5 class="mb-3 fw-semibold text-dark"><i class="fa-solid fa-shield-halved me-2 text-primary"></i>Cài đặt phân quyền & bảo mật</h5>
                        
                        <!-- PHẦN CHỌN PHÂN QUYỀN MỚI THÊM -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Quyền truy cập liên kết</label>
                            <?php 
                                $current_perm = isset($share['permission_type']) ? $share['permission_type'] : 'download'; 
                            ?>
                            <select name="permission_type" class="form-select">
                                <option value="download" <?php echo ($current_perm == 'download') ? 'selected' : ''; ?>>📥 Cho phép tải xuống (Mặc định)</option>
                                <option value="view_only" <?php echo ($current_perm == 'view_only') ? 'selected' : ''; ?>>👁️ Chỉ xem trực tuyến (Không cho phép tải)</option>
                                <option value="blocked" <?php echo ($current_perm == 'blocked') ? 'selected' : ''; ?>>🚫 Không cho phép truy cập (Chặn liên kết)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Mật khẩu bảo vệ</label>
                            <input type="password" name="password" class="form-control" placeholder="Bỏ trống nếu không cần mật khẩu">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Thời gian hết hạn</label>
                            <input type="text" name="expires_at" id="expires_at" class="form-control" placeholder="DD/MM/YYYY (Ví dụ: 25/12/2026)" maxlength="10" value="<?php echo (!empty($share['expires_at'])) ? date('d/m/Y', strtotime($share['expires_at'])) : ''; ?>">
                            <div class="form-text text-muted">Hệ thống sẽ tự động thêm dấu gạch chéo khi bạn gõ số (Ngày / Tháng / Năm).</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium">Giới hạn số lượt tải tối đa</label>
                            <input type="number" name="max_downloads" class="form-control" placeholder="Ví dụ: 10 lần" value="<?php echo isset($share['max_downloads']) ? $share['max_downloads'] : ''; ?>">
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100 py-2">Lưu cấu hình & Tạo liên kết</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('expires_at').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2);
            }
            if (value.length > 5) {
                value = value.substring(0, 5) + '/' + value.substring(5, 9);
            }
            e.target.value = value;
        });

        function copyLink() {
            var copyText = document.getElementById("shareLinkInput");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            alert("Đã sao chép liên kết thành công!");
        }
    </script>
</body>
</html>