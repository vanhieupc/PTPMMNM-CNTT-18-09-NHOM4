# 📂 FileShare - Hệ thống Chia sẻ tệp & Kiểm soát phân quyền an toàn

Dự án phát triển phần mềm quản lý, chia sẻ tệp tin trực tuyến tích hợp cơ chế kiểm soát quyền tải xuống, giới hạn lượt tải, đặt mật khẩu bảo vệ và ghi log lịch sử truy cập.

---

## 🚀 Tính năng nổi bật của hệ thống

1. **Quản lý Tệp tin Cá nhân:**
   - Hỗ trợ tải lên đa dạng định dạng tệp (PDF, hình ảnh, Word, Excel, PowerPoint, mã nguồn, tệp nén...).
   - Cơ chế kiểm soát kích thước tệp thông minh (hỗ trợ file nặng lên đến 20MB, tự động cảnh báo nén file `.zip` hoặc `.rar` nếu vượt quá giới hạn).
   - Thùng rác thông minh hỗ trợ chuyển tệp vào thùng rác, khôi phục hoặc xem trước danh sách dọn dẹp vĩnh viễn trên máy chủ.
   - Sắp xếp và tìm kiếm trực tiếp (Live Search) theo tên tệp nhanh chóng.

2. **Chia sẻ & Liên kết Bảo mật:**
   - Tạo liên kết chia sẻ (URL Token) riêng biệt cho từng tệp.
   - Thiết lập giới hạn số lượt tải tối đa, đặt mật khẩu bảo vệ liên kết hoặc thiết lập thời gian hết hạn.
   - Theo dõi chi tiết số lượt đã tải của từng liên kết chia sẻ.

3. **Kiểm soát Quyền truy cập & Bảo mật:**
   - Quản lý thông tin tài khoản đang đăng nhập và mốc thời gian phiên làm việc.
   - Tự động nhận diện thiết bị/trình duyệt và ghi nhận chính xác địa chỉ IP của máy truy cập.
   - Xuất báo cáo hoạt động truy cập dưới định dạng tệp Excel/CSV tiện lợi.

---

## 🛠️ Công nghệ sử dụng

* **Front-end:** HTML5, CSS3, Bootstrap 5, Font Awesome 6, JavaScript.
* **Back-end:** PHP (Session, Date/Time Management, File System Handling).
* **Database:** MySQL / MariaDB (Quản lý qua phpMyAdmin).

---

## 📁 Cấu trúc thư mục dự án

```text
file-sharing/
│
├── config/
│   └── db.php                  # Kết nối cơ sở dữ liệu PDO
│   └── file_sharing_db.sql     # Tệp cơ sở dữ liệu mẫu của toàn bộ hệ thống     
├── img/                        # Thư mục chứa hình nền, ảnh giao diện
├── uploads/                    # Thư mục lưu trữ vật lý các tệp người dùng tải lên
│
├── dashboard.php               # Trang quản trị chính (Trang chủ, Tệp, Thùng rác, Quyền truy cập)
├── download.php                # Xử lý logic tải tệp qua token chia sẻ
├── export_report.php           # Xuất báo cáo truy cập dạng CSV
├── login.php                   # Trang đăng nhập hệ thống
├── logout.php                  # Xử lý hủy phiên đăng nhập (Session)
├── register.php                # Trang đăng ký tài khoản mới
├── share.php                   # Giao diện và cấu hình liên kết chia sẻ tệp
└── test.php                    # File kiểm tra hệ thống

```

⚙️ Hướng dẫn cài đặt và chạy dự án
1. Yêu cầu hệ thống
- Đã cài đặt XAMPP hỗ trợ PHP 8.x và MySQL

2. Cài đặt mã nguồn
- Clone hoặc tải mã nguồn dự án về đặt vào thư mục gốc của XAMPP: C:\xampp\htdocs\file-sharing\.

3. Cấu hình Cơ sở dữ liệu (Database)
Khởi động Apache và MySQL trong ứng dụng XAMPP Control Panel.

- Mở trình duyệt và truy cập vào phpMyAdmin: http://localhost/phpmyadmin/.

- Tạo một cơ sở dữ liệu mới hoàn toàn (Database) với tên: file_sharing_db.

- Click vào database vừa tạo, chọn tab Import (Nhập), chọn file file_sharing_db.sql nằm sẵn trong thư mục dự án và nhấn Go để hoàn tất việc nhập cấu trúc bảng cùng dữ liệu mẫu.

4. Trải nghiệm hệ thống
Mở trình duyệt web và truy cập đường dẫn:

Plaintext
http://localhost/file-sharing/login.php
