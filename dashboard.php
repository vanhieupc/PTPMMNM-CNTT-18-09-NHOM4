<?php
// dashboard.php
session_start();

// Cấu hình múi giờ chuẩn Việt Nam (Asia/Ho_Chi_Minh)
date_default_timezone_set('Asia/Ho_Chi_Minh');

require_once 'config/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'overview';

if (!isset($_SESSION['login_time']) || $page == 'permissions') {
    $_SESSION['login_time'] = date('Y-m-d H:i:s');
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Quân Anh';
$message = '';
$p = isset($_GET['p']) ? intval($_GET['p']) : 1;
if ($p < 1) $p = 1;
$limit = 10;
$offset = ($p - 1) * $limit;

// Hàm định dạng dung lượng tệp thông minh (B, KB, MB, GB, TB)
function formatFileSize($bytes) {
    if ($bytes >= 1099511627776) {
        return round($bytes / 1099511627776, 2) . ' TB';
    } elseif ($bytes >= 1073741824) {
        return round($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' B';
    }
}

// Hàm nhận diện thiết bị/trình duyệt
function getClientBrowser() {
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (strpos($agent, 'Chrome') !== false) return 'Google Chrome / Windows';
    if (strpos($agent, 'Firefox') !== false) return 'Mozilla Firefox / Windows';
    if (strpos($agent, 'Safari') !== false) return 'Apple Safari / macOS';
    if (strpos($agent, 'Edge') !== false) return 'Microsoft Edge / Windows';
    return 'Trình duyệt Web (Windows)';
}

// Hàm lấy chính xác IP của máy truy cập (Hỗ trợ Localhost, Mạng LAN và Proxy)
function getClientIP() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    // Nếu là IPv6 localhost thì chuẩn hóa về dạng dễ nhìn hoặc giữ nguyên tùy ý
    if ($ip === '::1') {
        $ip = '127.0.0.1 (Localhost)';
    }
    return $ip;
}

// Hàm nhận diện Icon tệp theo phần mở rộng sau dấu .
function getFileIcon($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'pdf':
            return ['icon' => 'fa-file-pdf', 'color' => '#dc2626', 'bg' => 'rgba(220, 38, 38, 0.1)', 'type' => 'PDF'];
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
        case 'webp':
        case 'svg':
            return ['icon' => 'fa-file-image', 'color' => '#059669', 'bg' => 'rgba(5, 150, 105, 0.1)', 'type' => 'Hình ảnh'];
        case 'doc':
        case 'docx':
            return ['icon' => 'fa-file-word', 'color' => '#2563eb', 'bg' => 'rgba(37, 99, 235, 0.1)', 'type' => 'Tài liệu Word'];
        case 'xls':
        case 'xlsx':
        case 'csv':
            return ['icon' => 'fa-file-excel', 'color' => '#16a34a', 'bg' => 'rgba(22, 163, 74, 0.1)', 'type' => 'Bảng tính Excel'];
        case 'ppt':
        case 'pptx':
            return ['icon' => 'fa-file-powerpoint', 'color' => '#ea580c', 'bg' => 'rgba(234, 88, 12, 0.1)', 'type' => 'PowerPoint'];
        case 'html':
        case 'htm':
        case 'css':
        case 'js':
        case 'php':
        case 'py':
        case 'json':
        case 'sql':
            return ['icon' => 'fa-file-code', 'color' => '#7c3aed', 'bg' => 'rgba(124, 58, 237, 0.1)', 'type' => 'Mã nguồn'];
        case 'zip':
        case 'rar':
        case '7z':
        case 'tar':
        case 'gz':
            return ['icon' => 'fa-file-zipper', 'color' => '#d97706', 'bg' => 'rgba(217, 119, 6, 0.1)', 'type' => 'Tệp nén'];
        case 'txt':
            return ['icon' => 'fa-file-lines', 'color' => '#4b5563', 'bg' => 'rgba(75, 85, 99, 0.1)', 'type' => 'Văn bản'];
        case 'mp3':
        case 'wav':
        case 'flac':
            return ['icon' => 'fa-file-audio', 'color' => '#db2777', 'bg' => 'rgba(219, 39, 119, 0.1)', 'type' => 'Âm thanh'];
        case 'mp4':
        case 'mkv':
        case 'avi':
        case 'mov':
            return ['icon' => 'fa-file-video', 'color' => '#9333ea', 'bg' => 'rgba(147, 51, 234, 0.1)', 'type' => 'Video'];
        default:
            return ['icon' => 'fa-file', 'color' => '#2563eb', 'bg' => 'rgba(37, 99, 235, 0.1)', 'type' => 'Khác'];
    }
}

// Xử lý upload file có kiểm tra dung lượng tối đa (20MB)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $filename = basename($file['name']);
        $file_size = $file['size'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $blacklist_ext = ['exe', 'bat', 'sh', 'cmd', 'msi', 'php', 'js'];
        $max_size = 20 * 1024 * 1024; // 20 MB
        
        if (in_array($file_ext, $blacklist_ext)) {
            $message = "Hệ thống từ chối nhận các loại tệp thực thi hoặc mã nguồn nguy hiểm!";
        } elseif ($file_size > $max_size) {
            $size_mb = round($file_size / (1024 * 1024), 2);
            $message = "⚠️ Tệp quá nặng ($size_mb MB)! Vui lòng nén file lại (ZIP/RAR) để dung lượng nhẹ hơn trước khi tải lên (Tối đa 20MB).";
        } else {
            $stored_name = uniqid() . '.' . $file_ext;
            $upload_dir = 'uploads/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $destination = $upload_dir . $stored_name;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt = $conn->prepare("INSERT INTO files (user_id, filename, stored_name, file_size, is_deleted) VALUES (?, ?, ?, ?, 0)");
                $stmt->execute([$user_id, $filename, $stored_name, $file_size]);
                $message = "Tải tệp lên thành công!";
            } else {
                $message = "Không thể lưu tệp vào thư mục uploads!";
            }
        }
    } else {
        $message = "Lỗi khi tải tệp lên!";
    }
}

// Xử lý xóa tệp (Soft Delete)
if (isset($_GET['delete_file'])) {
    $del_id = intval($_GET['delete_file']);
    $stmt_del = $conn->prepare("UPDATE files SET is_deleted = 1 WHERE id = ? AND user_id = ?");
    $stmt_del->execute([$del_id, $user_id]);
    $message = "Đã chuyển tệp vào thùng rác!";
}

// Khôi phục tệp
if (isset($_GET['restore_file'])) {
    $res_id = intval($_GET['restore_file']);
    $stmt_res = $conn->prepare("UPDATE files SET is_deleted = 0 WHERE id = ? AND user_id = ?");
    $stmt_res->execute([$res_id, $user_id]);
    $message = "Đã khôi phục tệp thành công!";
}

// Xóa vĩnh viễn 1 tệp
if (isset($_GET['perm_delete'])) {
    $pdel_id = intval($_GET['perm_delete']);
    $stmt_f = $conn->prepare("SELECT * FROM files WHERE id = ? AND user_id = ? AND is_deleted = 1");
    $stmt_f->execute([$pdel_id, $user_id]);
    $file_to_pdel = $stmt_f->fetch();
    
    if ($file_to_pdel) {
        @unlink('uploads/' . $file_to_pdel['stored_name']);
        $conn->prepare("DELETE FROM files WHERE id = ?")->execute([$pdel_id]);
        $message = "Đã xóa vĩnh viễn tệp khỏi hệ thống!";
    }
}

// XỬ LÝ DỌN DẸP HẾT HẠN CÓ XÁC NHẬN VÀ XÓA VẬT LÝ TRONG THÙNG RÁC
if (isset($_POST['confirm_cleanup'])) {
    $stmt_all_trash = $conn->prepare("SELECT * FROM files WHERE user_id = ? AND is_deleted = 1");
    $stmt_all_trash->execute([$user_id]);
    $trash_files_list = $stmt_all_trash->fetchAll();
    
    $cleaned_count = 0;
    foreach ($trash_files_list as $tf) {
        $file_path = 'uploads/' . $tf['stored_name'];
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
        $conn->prepare("DELETE FROM files WHERE id = ?")->execute([$tf['id']]);
        $cleaned_count++;
    }
    $message = "Đã dọn dẹp vĩnh viễn thành công $cleaned_count tệp từ thùng rác!";
}

// Lấy thống kê chung
$stmt_count = $conn->prepare("SELECT COUNT(*) as total, SUM(file_size) as total_size FROM files WHERE user_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
$stmt_count->execute([$user_id]);
$stats = $stmt_count->fetch();
$total_files = $stats['total'] ?? 0;
$total_size_bytes = $stats['total_size'] ?? 0;

$storage_mb = round($total_size_bytes / (1024 * 1024), 2);
$storage_gb = round($storage_mb / 1024, 2);
$storage_display = ($storage_mb < 1024) ? "$storage_mb MB" : "$storage_gb GB";
$storage_percent_exact = ($storage_mb / 10240) * 100;
$storage_percent_display = ($storage_percent_exact > 0 && $storage_percent_exact < 1) ? number_format($storage_percent_exact, 1) : round($storage_percent_exact);
$storage_bar_width = max(($total_files > 0 ? 2 : 0), min(100, $storage_percent_exact));

$stmt_shares_count = $conn->prepare("SELECT COUNT(*) as total_shares FROM shares JOIN files ON shares.file_id = files.id WHERE files.user_id = ? AND (files.is_deleted = 0 OR files.is_deleted IS NULL)");
$stmt_shares_count->execute([$user_id]);
$total_shares = $stmt_shares_count->fetch()['total_shares'] ?? 0;

$stmt_recent_acts = $conn->prepare("SELECT filename, created_at FROM files WHERE user_id = ? ORDER BY created_at DESC LIMIT 4");
$stmt_recent_acts->execute([$user_id]);
$recent_activities = $stmt_recent_acts->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FileShare - Chia sẻ an toàn & Kiểm soát linh hoạt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-dark: #070d1b;
            --bg-card-dark: #0b132b;
            --border-dark: rgba(255, 255, 255, 0.12);
            
            --bg-center: #f8fafc;
            --bg-card-center: #ffffff;
            --border-center: #e2e8f0;
            --text-dark: #1e293b;
            --text-muted-dark: #64748b;
            
            --primary-blue: #3b82f6;
            --primary-hover: #2563eb;
        }

        body { 
            background-color: var(--bg-dark);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #ffffff;
            min-height: 100vh;
            margin: 0;
            font-size: 0.95rem;
        }

        .sidebar { width: 270px; height: 100vh; background-color: var(--bg-card-dark); position: fixed; top: 0; left: 0; display: flex; flex-direction: column; z-index: 100; border-right: 1px solid var(--border-dark); }
        .sidebar-brand { padding: 25px 22px; font-size: 1.35rem; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid var(--border-dark); }
        .sidebar-brand i { color: #60a5fa; font-size: 1.5rem; }
        .sidebar-brand span { display: block; font-size: 0.75rem; color: #cbd5e1; font-weight: 400; margin-top: 3px; }
        
        .sidebar-menu { padding: 25px 15px; flex-grow: 1; overflow-y: auto; }
        .sidebar-menu a { display: flex; align-items: center; gap: 14px; padding: 14px 18px; color: #cbd5e1; text-decoration: none; border-radius: 12px; margin-bottom: 8px; font-weight: 500; font-size: 0.95rem; transition: all 0.2s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: var(--primary-blue); color: #fff; }

        .sidebar-banner { margin: 15px; padding: 18px; background: linear-gradient(135deg, #1e3a8a, #0f172a); border-radius: 14px; border: 1px solid rgba(59, 130, 246, 0.3); font-size: 0.85rem; color: #cbd5e1; }
        .sidebar-banner h6 { color: #fff; font-size: 0.9rem; font-weight: 600; margin-bottom: 8px; }

        .main-content { 
            margin-left: 270px; 
            margin-right: 360px; 
            padding: 30px 40px; 
            background-color: var(--bg-center); 
            min-height: 100vh;
            color: var(--text-dark);
        }

        .right-sidebar { 
            width: 360px; 
            height: 100vh; 
            background-image: 
                linear-gradient(180deg, rgba(11, 19, 43, 0.6) 0%, rgba(11, 19, 43, 0.2) 35%, rgba(7, 13, 27, 0.05) 100%), 
                url('img/nen02.png'); 
            background-repeat: no-repeat;
            background-position: center; 
            background-size: cover; 
            position: fixed; 
            top: 0; 
            right: 0; 
            border-left: 1px solid var(--border-dark);  
            padding: 30px 24px; 
            overflow-y: auto; 
            z-index: 99; 
            color: #ffffff; 
        }
        
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .search-box { position: relative; width: 480px; }
        .search-box input { background: #ffffff; border: 1px solid var(--border-center); border-radius: 12px; padding: 12px 18px 12px 45px; width: 100%; font-size: 0.95rem; color: var(--text-dark); box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
        .search-box input:focus { background: #ffffff; border-color: var(--primary-blue); color: var(--text-dark); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
        .search-box i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted-dark); font-size: 1rem; }

        .search-suggestions {
            position: absolute; top: 100%; left: 0; right: 0;
            background: #ffffff; border: 1px solid var(--border-center); border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08); z-index: 1000;
            max-height: 250px; overflow-y: auto; display: none; margin-top: 6px;
        }
        .suggestion-item { padding: 12px 18px; display: flex; align-items: center; gap: 12px; text-decoration: none; color: var(--text-dark); border-bottom: 1px solid var(--border-center); font-size: 0.9rem; }
        .suggestion-item:hover { background-color: #f1f5f9; color: var(--primary-blue); }

        .hero-banner { 
            background-image: 
                linear-gradient(90deg, rgba(11, 19, 43, 0.95) 0%, rgba(11, 19, 43, 0.82) 42%, rgba(11, 19, 43, 0.2) 65%, rgba(11, 19, 43, 0.05) 100%), 
                url('img/nen01.png'); 
            background-repeat: no-repeat;
            background-position: center; 
            background-size: cover; 
            background-color: #0b132b;
            border: none; 
            border-radius: 18px; 
            padding: 40px 35px; 
            position: relative; 
            overflow: hidden; 
            margin-bottom: 30px; 
            color: #ffffff; 
            box-shadow: 0 10px 25px rgba(11, 19, 43, 0.3); 
            min-height: 220px;
            display: flex;
            align-items: center;
        }
        
        .hero-banner-content { max-width: 60%; position: relative; z-index: 2; }
        .hero-banner h1 { font-size: 1.8rem; font-weight: 700; color: #fff; margin-bottom: 10px; text-shadow: 0 2px 4px rgba(0,0,0,0.4); }
        .hero-banner p { color: #e2e8f0; font-size: 0.95rem; margin-bottom: 22px; line-height: 1.5; text-shadow: 0 1px 3px rgba(0,0,0,0.4); }
        .btn-custom-blue { background-color: var(--primary-blue); border: none; color: #fff; border-radius: 10px; padding: 10px 20px; font-weight: 500; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); }
        .btn-custom-blue:hover { background-color: var(--primary-hover); color: #fff; }

        .custom-card { background-color: var(--bg-card-center); border: 1px solid var(--border-center); border-radius: 16px; padding: 24px; margin-bottom: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        
        .table-custom { color: var(--text-dark); margin: 0; }
        .table-custom th { color: var(--text-muted-dark); font-weight: 600; font-size: 0.8rem; text-transform: uppercase; background-color: #f1f5f9 !important; border-bottom: 1px solid var(--border-center); padding: 14px 18px; letter-spacing: 0.05em; }
        .table-custom td { padding: 16px 18px; vertical-align: middle; border-bottom: 1px solid var(--border-center); background: transparent !important; font-size: 0.9rem; color: var(--text-dark); }
        .file-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 1.2rem; flex-shrink: 0; }

        .feature-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 30px; }
        .feature-box { background-color: var(--bg-card-center); border: 1px solid var(--border-center); border-radius: 14px; padding: 20px; transition: 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .feature-box:hover { border-color: #93c5fd; transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        
        .feature-icon-box { 
            width: 48px; height: 48px; border-radius: 14px; 
            display: flex; align-items: center; justify-content: center; 
            font-size: 1.3rem; margin-bottom: 16px; 
            background: linear-gradient(135deg, #3b82f6, #1d4ed8); 
            color: #ffffff; 
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4); 
        }
        .feature-box h6 { font-size: 0.95rem; font-weight: 700; color: var(--text-dark); margin-bottom: 8px; }
        .feature-box p { font-size: 0.8rem; color: var(--text-muted-dark); margin: 0; line-height: 1.5; }
        .feature-box .stat-number { font-size: 1.5rem; font-weight: 700; color: #2563eb; margin-top: 6px; display: block; }

        .widget-title { font-size: 0.95rem; font-weight: 700; color: #ffffff; text-transform: uppercase; margin-bottom: 16px; letter-spacing: 0.05em; display: flex; justify-content: space-between; align-items: center; text-shadow: 0 2px 6px rgba(0,0,0,0.9); }
        
        .storage-card-pro {
            background: rgba(11, 19, 43, 0.55);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(59, 130, 246, 0.4);
            border-radius: 16px;
            padding: 24px 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
            margin-bottom: 30px;
        }

        .activity-item { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 18px; font-size: 0.88rem; color: #e2e8f0; text-shadow: 0 1px 3px rgba(0,0,0,0.9); }
        .activity-item i { color: #60a5fa; margin-top: 4px; font-size: 1.1rem; }
        .activity-item .time { font-size: 0.78rem; color: #cbd5e1; display: block; margin-top: 3px; }
        
        .avatar-initial { width: 38px; height: 38px; background: var(--primary-blue); color: white; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: 600; font-size: 0.9rem; }
        
        .footer-bar { display: flex; justify-content: space-between; align-items: center; padding-top: 25px; border-top: 1px solid var(--border-center); font-size: 0.8rem; color: var(--text-muted-dark); }
        .footer-bar a { color: var(--text-muted-dark); text-decoration: none; margin-left: 14px; }
        .footer-bar a:hover { color: var(--text-dark); }

        .activity-toggle-btn {
            background: rgba(11, 19, 43, 0.65);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(59, 130, 246, 0.4);
            color: #ffffff;
            padding: 12px 16px;
            border-radius: 12px;
            width: 100%;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            text-shadow: 0 1px 3px rgba(0,0,0,0.9);
        }
        .activity-toggle-btn:hover {
            background: rgba(59, 130, 246, 0.3);
            border-color: rgba(59, 130, 246, 0.7);
        }
        #activityContentContainer { display: none; margin-top: 12px; }
    </style>
</head>
<body>

    <!-- LEFT SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-cloud"></i>
            <div>
                FileShare
                <span>Chia sẻ an toàn - Kiểm soát linh hoạt</span>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php?page=overview" class="<?php echo ($page == 'overview') ? 'active' : ''; ?>"><i class="fa-solid fa-house"></i> Trang chủ</a>
            <a href="dashboard.php?page=my_files" class="<?php echo ($page == 'my_files') ? 'active' : ''; ?>"><i class="fa-solid fa-folder"></i> Tệp của tôi</a>
            <a href="dashboard.php?page=shared" class="<?php echo ($page == 'shared') ? 'active' : ''; ?>"><i class="fa-solid fa-share-nodes"></i> Đã chia sẻ</a>
            <a href="dashboard.php?page=links" class="<?php echo ($page == 'links') ? 'active' : ''; ?>"><i class="fa-solid fa-link"></i> Liên kết</a>
            <a href="dashboard.php?page=recent" class="<?php echo ($page == 'recent') ? 'active' : ''; ?>"><i class="fa-solid fa-clock-rotate-left"></i> Gần đây</a>
            <a href="dashboard.php?page=trash" class="<?php echo ($page == 'trash') ? 'active' : ''; ?>"><i class="fa-solid fa-trash-can"></i> Thùng rác</a>
            <a href="dashboard.php?page=permissions" class="<?php echo ($page == 'permissions') ? 'active' : ''; ?>"><i class="fa-solid fa-shield-halved"></i> Quyền truy cập</a>
            <a href="logout.php" style="color: #f87171 !important;"><i class="fa-solid fa-right-from-bracket" style="color: #f87171;"></i> Đăng xuất</a>
        </div>

        <div class="sidebar-banner">
            <h6><i class="fa-solid fa-shield-halved text-success me-1"></i> Bảo mật hàng đầu</h6>
            Tất cả tệp của bạn đều được mã hóa và bảo vệ an toàn tuyệt đối.
        </div>
    </div>

    <!-- RIGHT SIDEBAR (WIDGETS) -->
    <div class="right-sidebar">
        <!-- Dung lượng lưu trữ -->
        <div class="mb-4">
            <div class="widget-title mb-3" style="cursor: default;"><i class="fa-solid fa-hard-drive text-primary me-2"></i> Dung lượng lưu trữ</div>
            <div class="storage-card-pro">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div style="width: 60px; height: 60px; border-radius: 50%; border: 4px solid #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; font-weight: 700; color: #ffffff; background: rgba(11, 19, 43, 0.7); backdrop-filter: blur(6px);">
                        <?php echo $storage_percent_display; ?>%
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-white fs-5" style="text-shadow: 0 2px 4px rgba(0,0,0,0.9);"><?php echo $storage_display; ?></div>
                        <div class="text-muted" style="font-size: 0.8rem; color: #e2e8f0 !important; text-shadow: 0 1px 3px rgba(0,0,0,0.9);">Dung lượng tối đa: 10 GB</div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1" style="font-size: 0.78rem; color: #f1f5f9; text-shadow: 0 1px 3px rgba(0,0,0,0.9);">
                        <span>Đã sử dụng</span>
                        <span class="text-white fw-semibold"><?php echo $total_files; ?> tệp trên hệ thống</span>
                    </div>
                    <div class="progress" style="height: 10px; background-color: rgba(15, 23, 42, 0.8); border-radius: 6px; border: 1px solid rgba(255,255,255,0.2);">
                        <div class="progress-bar bg-primary rounded" role="progressbar" style="width: <?php echo $storage_bar_width; ?>%;"></div>
                    </div>
                </div>
                <button class="btn btn-sm btn-primary w-100 py-2.5 fw-semibold shadow" style="font-size: 0.9rem; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border: none; text-shadow: 0 1px 2px rgba(0,0,0,0.6);">
                    <i class="fa-solid fa-crown text-warning me-1"></i> Nâng cấp gói dung lượng
                </button>
            </div>
        </div>

        <!-- Hoạt động gần đây -->
        <div>
            <button class="activity-toggle-btn" onclick="toggleActivityDropdown()">
                <span><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Hoạt động gần đây</span>
                <i class="fa-solid fa-chevron-down" id="activityChevron" style="transition: transform 0.3s;"></i>
            </button>
            
            <div id="activityContentContainer">
                <div class="custom-card p-3 mb-0 mt-2" style="background: rgba(11, 19, 43, 0.75); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2);">
                    <?php if (count($recent_activities) > 0): ?>
                        <?php foreach ($recent_activities as $act): ?>
                            <div class="activity-item">
                                <i class="fa-solid fa-file-arrow-up"></i>
                                <div>
                                    <span class="text-white fw-medium">Đã tải lên tệp: <?php echo htmlspecialchars(substr($act['filename'], 0, 22)) . (strlen($act['filename']) > 22 ? '...' : ''); ?></span>
                                    <span class="time"><?php echo date('d/m/Y H:i', strtotime($act['created_at'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="activity-item">
                            <i class="fa-solid fa-shield-halved text-success"></i>
                            <div>
                                <span class="text-white fw-medium">Hệ thống bảo mật hoạt động ổn định</span>
                                <span class="time">Hôm nay</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT AREA -->
    <div class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="liveSearchInput" placeholder="Tìm kiếm tệp, thư mục..." autocomplete="off">
                <div id="searchSuggestions" class="search-suggestions"></div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- NÚT XUẤT BÁO CÁO TRUY CẬP (CSV) -->
                <a href="export_report.php" class="btn btn-outline-success btn-sm py-2 px-3 fw-semibold bg-white" title="Xuất báo cáo truy cập dạng CSV">
                    <i class="fa-solid fa-file-excel me-1 text-success"></i> Xuất báo cáo
                </a>

                <button class="btn btn-primary rounded-circle p-2 shadow-sm d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border: none;">
                    <i class="fa-solid fa-bell text-white" style="font-size: 1rem;"></i>
                </button>
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-initial"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
                    <span class="fw-semibold text-dark" style="font-size: 0.95rem;"><?php echo htmlspecialchars($username); ?></span>
                </div>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-warning alert-dismissible fade show shadow-sm text-dark py-2 small" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- TRANG CHỦ (OVERVIEW) -->
        <?php if ($page == 'overview'): ?>
            <div class="hero-banner">
                <div class="hero-banner-content">
                    <h1>Chia sẻ tệp dễ dàng<br><span style="color: #93c5fd;">Kiểm soát quyền tải</span></h1>
                    <p>FileShare giúp bạn chia sẻ tệp, thư mục một cách nhanh chóng, đồng thời kiểm soát quyền truy cập và quyền tải xuống một cách linh hoạt, an toàn và hiệu quả.</p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-custom-blue" data-bs-toggle="modal" data-bs-target="#uploadModal" style="background: #ffffff; color: #1e3a8a; font-weight: 600;">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Tải lên tệp
                        </button>
                    </div>
                </div>
            </div>

            <!-- 4 Ô TÍNH NĂNG -->
            <div class="feature-grid">
                <div class="feature-box">
                    <div class="feature-icon-box"><i class="fa-solid fa-folder"></i></div>
                    <h6>Tổng số tệp</h6>
                    <span class="stat-number"><?php echo $total_files; ?></span>
                    <p class="mt-1">Tệp trong hệ thống.</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon-box"><i class="fa-solid fa-lock"></i></div>
                    <h6>Kiểm soát quyền truy cập</h6>
                    <p>Quản lý ai có thể xem, tải xuống hoặc chỉnh sửa.</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon-box"><i class="fa-solid fa-share-nodes"></i></div>
                    <h6>Đã chia sẻ</h6>
                    <span class="stat-number"><?php echo $total_shares; ?></span>
                    <p class="mt-1">Liên kết hoạt động.</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon-box"><i class="fa-solid fa-shield-halved"></i></div>
                    <h6>Bảo mật cao</h6>
                    <p>Mã hóa dữ liệu và bảo vệ tệp của bạn an toàn.</p>
                </div>
            </div>

            <!-- TỆP GẦN ĐÂY -->
            <div class="custom-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold m-0 text-dark" style="font-size: 1.1rem;">Tệp gần đây</h5>
                        <small style="color: var(--text-muted-dark); font-size: 0.8rem;">Những tệp được cập nhật mới nhất</small>
                    </div>
                    <button class="btn btn-sm btn-outline-primary py-2 px-3" data-bs-toggle="modal" data-bs-target="#uploadModal" style="font-size: 0.85rem;">
                        <i class="fa-solid fa-upload me-1"></i> Tải lên tệp mới
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom align-middle" id="fileTable">
                        <thead>
                            <tr>
                                <th>TÊN TỆP / THƯ MỤC</th>
                                <th>LOẠI</th>
                                <th>KÍCH THƯỚC</th>
                                <th>NGƯỜI CHIA SẺ</th>
                                <th>THỜI GIAN</th>
                                <th class="text-end">HÀNH ĐỘNG</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt_ov_total = $conn->prepare("SELECT COUNT(*) as total FROM files WHERE user_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                            $stmt_ov_total->execute([$user_id]);
                            $ov_records = $stmt_ov_total->fetch()['total'] ?? 0;
                            $ov_pages = ceil($ov_records / $limit);

                            $stmt_recent = $conn->prepare("SELECT * FROM files WHERE user_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
                            $stmt_recent->execute([$user_id]);
                            $recent_files = $stmt_recent->fetchAll();
                            ?>
                            <?php if (count($recent_files) > 0): ?>
                                <?php foreach ($recent_files as $file): ?>
                                    <?php 
                                        $fInfo = getFileIcon($file['filename']); 
                                        $size_formatted = formatFileSize($file['file_size']);
                                    ?>
                                    <tr class="file-row">
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="file-icon" style="background-color: <?php echo $fInfo['bg']; ?>; color: <?php echo $fInfo['color']; ?>;">
                                                    <i class="fa-solid <?php echo $fInfo['icon']; ?>"></i>
                                                </div>
                                                <div class="fw-semibold text-dark file-title"><?php echo htmlspecialchars($file['filename']); ?></div>
                                            </div>
                                        </td>
                                        <td><span style="color: var(--text-muted-dark);"><?php echo $fInfo['type']; ?></span></td>
                                        <td><?php echo $size_formatted; ?></td>
                                        <td><span style="color: var(--text-muted-dark);">Bạn</span></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($file['created_at'])); ?></td>
                                        <td class="text-end">
                                            <a href="share.php?file_id=<?php echo $file['id']; ?>" class="btn btn-sm btn-custom-blue px-0 py-0 d-inline-flex align-items-center justify-content-center" style="font-size: 0.85rem; border-radius: 8px; height: 32px; width: 34px;" title="Chia sẻ">
                                                <i class="fa-solid fa-share-nodes"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">Chưa có tệp nào gần đây.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($ov_pages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center m-0 pagination-sm">
                            <li class="page-item <?php echo ($p <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link bg-white border text-dark" href="dashboard.php?page=overview&p=<?php echo $p - 1; ?>">Trước</a>
                            </li>
                            <?php for ($i = 1; $i <= $ov_pages; $i++): ?>
                                <li class="page-item <?php echo ($p == $i) ? 'active' : ''; ?>">
                                    <a class="page-link border <?php echo ($p == $i) ? 'bg-primary text-white' : 'bg-white text-dark'; ?>" href="dashboard.php?page=overview&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($p >= $ov_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link bg-white border text-dark" href="dashboard.php?page=overview&p=<?php echo $p + 1; ?>">Sau</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>

        <!-- TỆP CỦA TÔI -->
        <?php elseif ($page == 'my_files'): ?>
            <?php
                $sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
                $order_by = "created_at DESC";
                if ($sort == 'oldest') $order_by = "created_at ASC";
                elseif ($sort == 'name_asc') $order_by = "filename ASC";
                elseif ($sort == 'name_desc') $order_by = "filename DESC";
                elseif ($sort == 'size_desc') $order_by = "file_size DESC";

                $stmt_total_files = $conn->prepare("SELECT COUNT(*) as total FROM files WHERE user_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmt_total_files->execute([$user_id]);
                $total_records = $stmt_total_files->fetch()['total'] ?? 0;
                $total_pages = ceil($total_records / $limit);

                $stmt_all = $conn->prepare("SELECT * FROM files WHERE user_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY $order_by LIMIT $limit OFFSET $offset");
                $stmt_all->execute([$user_id]);
                $all_files = $stmt_all->fetchAll();
            ?>
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-dark mb-1" style="font-size: 1.5rem;">Tệp của tôi</h2>
                    <p style="color: var(--text-muted-dark); font-size: 0.9rem;">Quản lý toàn bộ tệp dữ liệu cá nhân.</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <form method="GET" action="dashboard.php">
                        <input type="hidden" name="page" value="my_files">
                        <select name="sort" class="form-select bg-white text-dark border" onchange="this.form.submit()">
                            <option value="newest" <?php echo ($sort == 'newest') ? 'selected' : ''; ?>>📅 Mới nhất</option>
                            <option value="oldest" <?php echo ($sort == 'oldest') ? 'selected' : ''; ?>>📅 Cũ nhất</option>
                            <option value="name_asc" <?php echo ($sort == 'name_asc') ? 'selected' : ''; ?>>🔤 Tên (A - Z)</option>
                            <option value="name_desc" <?php echo ($sort == 'name_desc') ? 'selected' : ''; ?>>🔤 Tên (Z - A)</option>
                            <option value="size_desc" <?php echo ($sort == 'size_desc') ? 'selected' : ''; ?>>📊 Kích thước lớn nhất</option>
                        </select>
                    </form>
                    <button class="btn btn-custom-blue py-2" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="fa-solid fa-plus me-1"></i> Tải tệp lên
                    </button>
                </div>
            </div>

            <div class="custom-card">
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>Tên tệp</th>
                                <th>Kích thước</th>
                                <th>Ngày tải lên</th>
                                <th class="text-end">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($all_files) > 0): ?>
                                <?php foreach ($all_files as $file): ?>
                                    <?php $fInfo = getFileIcon($file['filename']); ?>
                                    <tr class="file-row">
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="file-icon" style="background-color: <?php echo $fInfo['bg']; ?>; color: <?php echo $fInfo['color']; ?>;">
                                                    <i class="fa-solid <?php echo $fInfo['icon']; ?>"></i>
                                                </div>
                                                <div class="fw-semibold text-dark file-title"><?php echo htmlspecialchars($file['filename']); ?></div>
                                            </div>
                                        </td>
                                        <td><?php echo formatFileSize($file['file_size']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($file['created_at'])); ?></td>
                                        <td class="text-end">
                                            <!-- Container ép nút Chia sẻ và Thùng rác thẳng hàng tuyệt đối -->
                                            <div class="d-inline-flex align-items-center gap-2">
                                                <a href="share.php?file_id=<?php echo $file['id']; ?>" class="btn btn-sm btn-custom-blue px-0 py-0 d-inline-flex align-items-center justify-content-center" style="font-size: 0.85rem; border-radius: 8px; height: 32px; width: 34px;" title="Chia sẻ">
                                                    <i class="fa-solid fa-share-nodes"></i>
                                                </a>
                                                <a href="dashboard.php?page=my_files&sort=<?php echo $sort; ?>&delete_file=<?php echo $file['id']; ?>" class="btn btn-sm btn-outline-danger px-0 py-0 d-inline-flex align-items-center justify-content-center" style="font-size: 0.85rem; border-radius: 8px; height: 32px; width: 34px;" onclick="return confirm('Bạn có chắc muốn chuyển tệp này vào thùng rác?');" title="Chuyển vào thùng rác">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-5">Không có tệp nào trong hệ thống.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center m-0 pagination-sm">
                            <li class="page-item <?php echo ($p <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link bg-white border text-dark" href="dashboard.php?page=my_files&sort=<?php echo $sort; ?>&p=<?php echo $p - 1; ?>">Trước</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($p == $i) ? 'active' : ''; ?>">
                                    <a class="page-link border <?php echo ($p == $i) ? 'bg-primary text-white' : 'bg-white text-dark'; ?>" href="dashboard.php?page=my_files&sort=<?php echo $sort; ?>&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($p >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link bg-white border text-dark" href="dashboard.php?page=my_files&sort=<?php echo $sort; ?>&p=<?php echo $p + 1; ?>">Sau</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>

        <!-- TỆP ĐÃ CHIA SẺ -->
        <?php elseif ($page == 'shared'): ?>
            <?php
                $stmt_sh_total = $conn->prepare("SELECT COUNT(*) as total FROM shares JOIN files ON shares.file_id = files.id WHERE files.user_id = ? AND (files.is_deleted = 0 OR files.is_deleted IS NULL)");
                $stmt_sh_total->execute([$user_id]);
                $sh_records = $stmt_sh_total->fetch()['total'] ?? 0;
                $sh_pages = ceil($sh_records / $limit);

                $stmt_sh = $conn->prepare("
                    SELECT shares.*, files.filename, files.id as file_id 
                    FROM shares 
                    JOIN files ON shares.file_id = files.id 
                    WHERE files.user_id = ? AND (files.is_deleted = 0 OR files.is_deleted IS NULL)
                    LIMIT $limit OFFSET $offset
                ");
                $stmt_sh->execute([$user_id]);
                $shared_files = $stmt_sh->fetchAll();
            ?>
            <div class="mb-4">
                <h2 class="fw-bold text-dark mb-1" style="font-size: 1.5rem;">Tệp đã chia sẻ</h2>
                <p style="color: var(--text-muted-dark); font-size: 0.9rem;">Danh sách các tệp đang được thiết lập liên kết chia sẻ.</p>
            </div>

            <div class="custom-card">
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>Tên tệp</th>
                                <th>Lượt tải</th>
                                <th>Giới hạn tải</th>
                                <th>Hết hạn</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($shared_files) > 0): ?>
                                <?php foreach ($shared_files as $sf): ?>
                                    <?php $fInfo = getFileIcon($sf['filename']); ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="file-icon" style="background-color: <?php echo $fInfo['bg']; ?>; color: <?php echo $fInfo['color']; ?>;">
                                                    <i class="fa-solid <?php echo $fInfo['icon']; ?>"></i>
                                                </div>
                                                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($sf['filename']); ?></span>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-primary px-2.5 py-1"><?php echo $sf['download_count']; ?> lượt</span></td>
                                        <td><?php echo $sf['max_downloads'] ? $sf['max_downloads'] . ' lần' : 'Không giới hạn'; ?></td>
                                        <td><?php echo $sf['expires_at'] ? date('d/m/Y', strtotime($sf['expires_at'])) : 'Không thời hạn'; ?></td>
                                        <td class="text-end">
                                            <a href="share.php?file_id=<?php echo $sf['file_id']; ?>" class="btn btn-sm btn-outline-primary py-1.5 px-3" style="font-size: 0.8rem;">
                                                <i class="fa-solid fa-gear"></i> Cấu hình link
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-5">Chưa có tệp nào được tạo link chia sẻ.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($sh_pages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center m-0 pagination-sm">
                            <li class="page-item <?php echo ($p <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link bg-white border text-dark" href="dashboard.php?page=shared&p=<?php echo $p - 1; ?>">Trước</a>
                            </li>
                            <?php for ($i = 1; $i <= $sh_pages; $i++): ?>
                                <li class="page-item <?php echo ($p == $i) ? 'active' : ''; ?>">
                                    <a class="page-link border <?php echo ($p == $i) ? 'bg-primary text-white' : 'bg-white text-dark'; ?>" href="dashboard.php?page=shared&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($p >= $sh_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link bg-white border text-dark" href="dashboard.php?page=shared&p=<?php echo $p + 1; ?>">Sau</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>

        <!-- LIÊN KẾT -->
        <?php elseif ($page == 'links'): ?>
            <?php
                $stmt_l_total = $conn->prepare("SELECT COUNT(*) as total FROM shares JOIN files ON shares.file_id = files.id WHERE files.user_id = ? AND (files.is_deleted = 0 OR files.is_deleted IS NULL)");
                $stmt_l_total->execute([$user_id]);
                $l_records = $stmt_l_total->fetch()['total'] ?? 0;
                $l_pages = ceil($l_records / $limit);

                $stmt_links = $conn->prepare("SELECT shares.*, files.filename FROM shares JOIN files ON shares.file_id = files.id WHERE files.user_id = ? AND (files.is_deleted = 0 OR files.is_deleted IS NULL) LIMIT $limit OFFSET $offset");
                $stmt_links->execute([$user_id]);
                $links = $stmt_links->fetchAll();
                
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                $host = $_SERVER['HTTP_HOST'];
                $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            ?>
            <div class="mb-4">
                <h2 class="fw-bold text-dark mb-1" style="font-size: 1.5rem;">Quản lý liên kết</h2>
                <p style="color: var(--text-muted-dark); font-size: 0.9rem;">Danh sách toàn bộ các URL chia sẻ đã phát hành.</p>
            </div>
            <div class="custom-card">
                <ul class="list-group list-group-flush bg-transparent">
                    <?php if (count($links) > 0): ?>
                        <?php foreach ($links as $l): ?>
                            <?php $full_link = "$protocol://$host$path/download.php?token=" . $l['token']; ?>
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-3 border-secondary">
                                <div>
                                    <div class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($l['filename']); ?></div>
                                    <a href="<?php echo $full_link; ?>" target="_blank" class="small text-primary text-decoration-none"><?php echo $full_link; ?></a>
                                </div>
                                <span class="badge bg-secondary px-3 py-2"><?php echo $l['download_count']; ?> lượt tải</span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-4 mb-0">Chưa có liên kết chia sẻ nào được tạo.</p>
                    <?php endif; ?>
                </ul>

                <?php if ($l_pages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center m-0 pagination-sm">
                            <li class="page-item <?php echo ($p <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link bg-white border text-dark" href="dashboard.php?page=links&p=<?php echo $p - 1; ?>">Trước</a>
                            </li>
                            <?php for ($i = 1; $i <= $l_pages; $i++): ?>
                                <li class="page-item <?php echo ($p == $i) ? 'active' : ''; ?>">
                                    <a class="page-link border <?php echo ($p == $i) ? 'bg-primary text-white' : 'bg-white text-dark'; ?>" href="dashboard.php?page=links&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($p >= $l_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link bg-white border text-dark" href="dashboard.php?page=links&p=<?php echo $p + 1; ?>">Sau</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>

        <!-- GẦN ĐÂY -->
        <?php elseif ($page == 'recent'): ?>
            <?php
                $stmt_rec_total = $conn->prepare("SELECT COUNT(*) as total FROM files WHERE user_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmt_rec_total->execute([$user_id]);
                $rec_records = $stmt_rec_total->fetch()['total'] ?? 0;
                $rec_pages = ceil($rec_records / $limit);

                $stmt_rec = $conn->prepare("SELECT * FROM files WHERE user_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
                $stmt_rec->execute([$user_id]);
                $rec_files = $stmt_rec->fetchAll();
            ?>
            <div class="mb-4">
                <h2 class="fw-bold text-dark mb-1" style="font-size: 1.5rem;">Hoạt động gần đây</h2>
                <p style="color: var(--text-muted-dark); font-size: 0.9rem;">Các tệp được tải lên trong thời gian gần đây nhất.</p>
            </div>
            <div class="custom-card">
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>Tên tệp</th>
                                <th>Kích thước</th>
                                <th>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($rec_files) > 0): ?>
                                <?php foreach ($rec_files as $file): ?>
                                    <?php $fInfo = getFileIcon($file['filename']); ?>
                                    <tr class="file-row">
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="file-icon" style="background-color: <?php echo $fInfo['bg']; ?>; color: <?php echo $fInfo['color']; ?>;">
                                                    <i class="fa-solid <?php echo $fInfo['icon']; ?>"></i>
                                                </div>
                                                <div class="fw-semibold text-dark file-title"><?php echo htmlspecialchars($file['filename']); ?></div>
                                            </div>
                                        </td>
                                        <td><?php echo formatFileSize($file['file_size']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($file['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted py-4">Chưa có hoạt động nào.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($rec_pages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center m-0 pagination-sm">
                            <li class="page-item <?php echo ($p <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link bg-white border text-dark" href="dashboard.php?page=recent&p=<?php echo $p - 1; ?>">Trước</a>
                            </li>
                            <?php for ($i = 1; $i <= $rec_pages; $i++): ?>
                                <li class="page-item <?php echo ($p == $i) ? 'active' : ''; ?>">
                                    <a class="page-link border <?php echo ($p == $i) ? 'bg-primary text-white' : 'bg-white text-dark'; ?>" href="dashboard.php?page=recent&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($p >= $rec_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link bg-white border text-dark" href="dashboard.php?page=recent&p=<?php echo $p + 1; ?>">Sau</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>

        <!-- THÙNG RÁC -->
        <?php elseif ($page == 'trash'): ?>
            <?php
                $stmt_tr_total = $conn->prepare("SELECT COUNT(*) as total FROM files WHERE user_id = ? AND is_deleted = 1");
                $stmt_tr_total->execute([$user_id]);
                $tr_records = $stmt_tr_total->fetch()['total'] ?? 0;
                $tr_pages = ceil($tr_records / $limit);

                $stmt_trash = $conn->prepare("SELECT * FROM files WHERE user_id = ? AND is_deleted = 1 ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
                $stmt_trash->execute([$user_id]);
                $trash_files = $stmt_trash->fetchAll();
            ?>
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-dark mb-1" style="font-size: 1.5rem;">Thùng rác</h2>
                    <p style="color: var(--text-muted-dark); font-size: 0.9rem;">Quản lý, khôi phục hoặc xem trước danh sách dọn dẹp tệp.</p>
                </div>
                <?php if ($tr_records > 0): ?>
                    <button class="btn btn-outline-danger btn-sm py-2 px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#previewCleanupModal">
                        <i class="fa-solid fa-broom me-1"></i> Xem trước & Dọn dẹp thùng rác
                    </button>
                <?php endif; ?>
            </div>

            <div class="custom-card">
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>Tên tệp</th>
                                <th>Kích thước</th>
                                <th>Ngày tải lên</th>
                                <th class="text-end">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($trash_files) > 0): ?>
                                <?php foreach ($trash_files as $tf): ?>
                                    <?php $fInfo = getFileIcon($tf['filename']); ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="file-icon" style="background-color: <?php echo $fInfo['bg']; ?>; color: <?php echo $fInfo['color']; ?>;">
                                                    <i class="fa-solid <?php echo $fInfo['icon']; ?>"></i>
                                                </div>
                                                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($tf['filename']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo formatFileSize($tf['file_size']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($tf['created_at'])); ?></td>
                                        <td class="text-end">
                                            <a href="dashboard.php?page=trash&p=<?php echo $p; ?>&restore_file=<?php echo $tf['id']; ?>" class="btn btn-sm btn-success py-1.5 px-3 text-white me-1" style="font-size: 0.8rem;">
                                                <i class="fa-solid fa-rotate-left me-1"></i> Khôi phục
                                            </a>
                                            <a href="dashboard.php?page=trash&p=<?php echo $p; ?>&perm_delete=<?php echo $tf['id']; ?>" class="btn btn-sm btn-outline-danger py-1.5 px-3" style="font-size: 0.8rem;" onclick="return confirm('Bạn có chắc muốn xóa vĩnh viễn tệp này không?');">
                                                <i class="fa-solid fa-trash"></i> Xóa vĩnh viễn
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-5">Thùng rác trống. Không có tệp nào bị xóa.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($tr_pages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center m-0 pagination-sm">
                            <li class="page-item <?php echo ($p <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link bg-white border text-dark" href="dashboard.php?page=trash&p=<?php echo $p - 1; ?>">Trước</a>
                            </li>
                            <?php for ($i = 1; $i <= $tr_pages; $i++): ?>
                                <li class="page-item <?php echo ($p == $i) ? 'active' : ''; ?>">
                                    <a class="page-link border <?php echo ($p == $i) ? 'bg-primary text-white' : 'bg-white text-dark'; ?>" href="dashboard.php?page=trash&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($p >= $tr_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link bg-white border text-dark" href="dashboard.php?page=trash&p=<?php echo $p + 1; ?>">Sau</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>

        <!-- QUYỀN TRUY CẬP -->
        <?php elseif ($page == 'permissions'): ?>
            <div class="mb-4">
                <h2 class="fw-bold text-dark mb-1" style="font-size: 1.5rem;">Quyền truy cập</h2>
                <p style="color: var(--text-muted-dark); font-size: 0.9rem;">Quản lý tài khoản đang đăng nhập, mốc thời gian truy cập và các phiên làm việc.</p>
            </div>

            <div class="custom-card mb-4" style="background: linear-gradient(135deg, #ffffff, #f8fafc); border: 1px solid #bfdbfe;">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-initial" style="width: 56px; height: 56px; font-size: 1.4rem; background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                            <?php echo strtoupper(substr($username, 0, 1)); ?>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1" style="font-size: 1.2rem;"><?php echo htmlspecialchars($username); ?></h5>
                            <span class="badge bg-primary me-2 px-2.5 py-1"><i class="fa-solid fa-user-shield me-1"></i> Chủ sở hữu</span>
                            <span class="badge bg-success px-2.5 py-1"><i class="fa-solid fa-circle me-1" style="font-size: 0.5rem;"></i> Đang hoạt động</span>
                        </div>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block mb-1" style="font-size: 0.8rem;">Thời gian đăng nhập hệ thống</small>
                        <span class="fw-bold text-primary" style="font-size: 1.05rem;">
                            <i class="fa-regular fa-clock me-1"></i> <?php echo date('d/m/Y - H:i:s', strtotime($_SESSION['login_time'])); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="custom-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold m-0 text-dark" style="font-size: 1.1rem;"><i class="fa-solid fa-laptop-code text-primary me-2"></i> Phiên đăng nhập hoạt động</h5>
                        <small style="color: var(--text-muted-dark); font-size: 0.8rem;">Danh sách thiết bị và mốc thời gian truy cập vào tài khoản</small>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>TÀI KHOẢN</th>
                                <th>THIẾT BỊ / TRÌNH DUYỆT</th>
                                <th>ĐỊA CHỈ IP</th>
                                <th>THỜI GIAN TRUY CẬP</th>
                                <th class="text-end">TRẠNG THÁI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-initial" style="width: 34px; height: 34px; font-size: 0.8rem;"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($username); ?></span>
                                    </div>
                                </td>
                                <td><i class="fa-solid fa-desktop text-primary me-2"></i> <?php echo getClientBrowser(); ?></td>
                                <td><code class="text-dark bg-light" style="padding: 4px 10px; border-radius: 6px; border: 1px solid #cbd5e1;"><?php echo getClientIP(); ?></code></td>
                                <td>
                                    <span class="text-dark"><?php echo date('d/m/Y H:i:s', strtotime($_SESSION['login_time'])); ?></span>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-success px-3 py-1.5"><i class="fa-solid fa-check-circle me-1"></i> Phiên hiện tại</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- FOOTER -->
        <div class="footer-bar mt-5">
            <div>FileShare Chia sẻ an toàn - Kiểm soát linh hoạt</div>
            <div>
                <a href="#">Điều khoản sử dụng</a>
                <a href="#">Chính sách bảo mật</a>
                <a href="#">Liên hệ</a>
                <span class="ms-2">v1.0.0</span>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-white border text-dark shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark" style="font-size: 1.1rem;">Tải tệp lên hệ thống</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="uploadForm" action="dashboard.php?page=<?php echo $page; ?>&p=<?php echo $p; ?>" method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Chọn tệp từ máy tính của bạn (Tối đa 20MB)</label>
                            <input type="file" id="fileInput" name="file" class="form-control bg-light border py-2" required>
                            <div id="fileSizeWarning" class="form-text text-danger mt-2 fw-semibold" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-sm btn-secondary px-3 py-2" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" id="submitUploadBtn" class="btn btn-sm btn-custom-blue px-3 py-2">Xác nhận tải lên</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL XEM TRƯỚC DANH SÁCH DỌN DẸP THÙNG RÁC -->
    <div class="modal fade" id="previewCleanupModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-white border text-dark shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i> Xem trước danh sách chuẩn bị dọn dẹp vĩnh viễn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small">Dưới đây là danh sách các tệp đang nằm trong thùng rác chuẩn bị được xóa vĩnh viễn khỏi ổ đĩa máy chủ.</p>
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Tên tệp</th>
                                    <th>Kích thước</th>
                                    <th>Ngày tải lên</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt_preview = $conn->prepare("SELECT * FROM files WHERE user_id = ? AND is_deleted = 1");
                                $stmt_preview->execute([$user_id]);
                                $preview_list = $stmt_preview->fetchAll();
                                ?>
                                <?php if (count($preview_list) > 0): ?>
                                    <?php foreach ($preview_list as $pl): ?>
                                        <tr>
                                            <td><?php echo $pl['id']; ?></td>
                                            <td class="fw-semibold"><?php echo htmlspecialchars($pl['filename']); ?></td>
                                            <td><?php echo formatFileSize($pl['file_size']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($pl['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">Không có tệp nào trong thùng rác.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-sm btn-secondary px-3 py-2" data-bs-dismiss="modal">Hủy bỏ</button>
                    <?php if (count($preview_list) > 0): ?>
                        <form method="POST" action="dashboard.php?page=trash">
                            <button type="submit" name="confirm_cleanup" class="btn btn-sm btn-danger px-3 py-2">
                                <i class="fa-solid fa-trash-can me-1"></i> Xác nhận xóa vĩnh viễn toàn bộ
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('fileInput').addEventListener('change', function() {
            const file = this.files[0];
            const warningBox = document.getElementById('fileSizeWarning');
            const submitBtn = document.getElementById('submitUploadBtn');
            const maxSize = 20 * 1024 * 1024;

            if (file) {
                if (file.size > maxSize) {
                    const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    warningBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i> Tệp bạn chọn quá nặng (${fileSizeMB} MB). Vui lòng nén file thành định dạng <strong>.zip</strong> hoặc <strong>.rar</strong> (Tối đa 20MB).`;
                    warningBox.style.display = 'block';
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-50');
                } else {
                    warningBox.style.display = 'none';
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50');
                }
            }
        });

        function toggleActivityDropdown() {
            const container = document.getElementById('activityContentContainer');
            const chevron = document.getElementById('activityChevron');
            if (container.style.display === 'block') {
                container.style.display = 'none';
                chevron.style.transform = 'rotate(0deg)';
            } else {
                container.style.display = 'block';
                chevron.style.transform = 'rotate(180deg)';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('liveSearchInput');
            const suggestionsBox = document.getElementById('searchSuggestions');
            const fileRows = document.querySelectorAll('.file-row');

            if (!searchInput) return;

            searchInput.addEventListener('input', function() {
                const keyword = searchInput.value.toLowerCase().trim();

                fileRows.forEach(row => {
                    const titleEl = row.querySelector('.file-title');
                    if (titleEl) {
                        const fileName = titleEl.textContent.toLowerCase();
                        if (keyword === '' || fileName.startsWith(keyword)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });

                if (keyword.length > 0) {
                    let suggestionsHTML = '';
                    fileRows.forEach(row => {
                        const titleEl = row.querySelector('.file-title');
                        if (titleEl) {
                            const fileName = titleEl.textContent;
                            if (fileName.toLowerCase().startsWith(keyword)) {
                                suggestionsHTML += `<a href="#" class="suggestion-item" onclick="selectSuggestion('${fileName}')">
                                    <i class="fa-solid fa-file-lines text-primary"></i> ${fileName}
                                </a>`;
                            }
                        }
                    });

                    if (suggestionsHTML !== '') {
                        suggestionsBox.innerHTML = suggestionsHTML;
                        suggestionsBox.style.display = 'block';
                    } else {
                        suggestionsBox.innerHTML = `<div class="p-2 text-muted small text-center">Không tìm thấy tệp bắt đầu bằng "${keyword}"</div>`;
                        suggestionsBox.style.display = 'block';
                    }
                } else {
                    suggestionsBox.style.display = 'none';
                }
            });

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                    suggestionsBox.style.display = 'none';
                }
            });
        });

        function selectSuggestion(fileName) {
            const searchInput = document.getElementById('liveSearchInput');
            searchInput.value = fileName;
            searchInput.dispatchEvent(new Event('input'));
            document.getElementById('searchSuggestions').style.display = 'none';
        }
    </script>
</body>
</html>