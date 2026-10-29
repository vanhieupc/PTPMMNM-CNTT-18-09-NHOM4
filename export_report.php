<?php
// export_report.php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// Hàm định dạng dung lượng thông minh
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

$filename = "Bao_Cao_Tat_Ca_Tep_" . preg_replace('/[^a-zA-Z0-9]/', '_', $username) . "_" . date('Y-m-d_H-i-s') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, [
    'ID Tệp', 
    'Tên Tệp', 
    'Kích Thước', 
    'Ngày Tải Lên', 
    'Token Chia Sẻ', 
    'Tổng Lượt Tải', 
    'Giới Hạn Tải', 
    'Ngày Hết Hạn'
]);

$stmt = $conn->prepare("
    SELECT 
        files.id AS file_id, 
        files.filename, 
        files.file_size, 
        files.created_at, 
        shares.token, 
        shares.download_count, 
        shares.max_downloads, 
        shares.expires_at 
    FROM files 
    LEFT JOIN shares ON files.id = shares.file_id 
    WHERE files.user_id = ? AND (files.is_deleted = 0 OR files.is_deleted IS NULL)
    ORDER BY files.created_at DESC
");
$stmt->execute([$user_id]);
$files_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($files_list as $row) {
    // Gọi hàm quy đổi thông minh (tự động sang KB, MB, GB, TB...)
    $size_formatted = formatFileSize($row['file_size']);
    
    fputcsv($output, [
        $row['file_id'],
        $row['filename'],
        $size_formatted,
        $row['created_at'],
        $row['token'] ?? 'Chưa chia sẻ',
        $row['download_count'] ?? 0,
        $row['max_downloads'] ? $row['max_downloads'] . ' lần' : 'Không giới hạn',
        $row['expires_at'] ? $row['expires_at'] : 'Không thời hạn'
    ]);
}

fclose($output);
exit();
?>