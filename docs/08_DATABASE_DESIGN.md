# TÀI LIỆU 08: THIẾT KẾ CƠ SỞ DỮ LIỆU (DATABASE DESIGN)

Tài liệu này mô tả chi tiết lược đồ cơ sở dữ liệu MySQL của hệ thống Quản lý nhà trọ, danh sách 18 bảng dữ liệu quan hệ, các khóa chính, khóa ngoại, chỉ mục (Index) và quy trình sao lưu / phục hồi dữ liệu an toàn.

---

## 1. Tổng Quan Cơ Sở Dữ Liệu

- **Hệ quản trị CSDL:** MySQL 8.0 / MariaDB 10.x (WAMP Server).
- **Bộ mã ký tự (Charset):** `utf8mb4`
- **Tập thứ tự đối chiếu (Collation):** `utf8mb4_unicode_ci` (Hỗ trợ tiếng Việt có dấu đầy đủ và Emoji).
- **Storage Engine:** `InnoDB` (Hỗ trợ giao dịch Transaction ACID và toàn vẹn khóa ngoại Foreign Keys).
- **Tổng số bảng hiện có:** 18 bảng.

---

## 2. Danh Sách 18 Bảng Cơ Sở Dữ Liệu Thực Tế

| STT | Tên bảng | Chức năng nghiệp vụ |
| :---: | :--- | :--- |
| **1** | `users` | Tài khoản đăng nhập, mật khẩu mã hóa, vai trò (`admin`/`tenant`), trạng thái |
| **2** | `rental_areas` | Thông tin các khu nhà trọ, địa chỉ, mô tả khu |
| **3** | `rooms` | Danh mục phòng trọ, diện tích, giá thuê, tiền cọc, trạng thái phòng |
| **4** | `room_images` | Danh sách hình ảnh thực tế của từng phòng trọ |
| **5** | `tenants` | Hồ sơ cá nhân khách thuê (họ tên, CCCD, ngày sinh, quê quán) |
| **6** | `contracts` | Hợp đồng thuê phòng giữa người thuê và phòng trọ, ngày bắt đầu/kết thúc |
| **7** | `services` | Đơn giá các dịch vụ tính phí (điện, nước, rác, wifi, vệ sinh) |
| **8** | `meter_readings` | Bảng ghi nhận chỉ số công tơ điện và đồng hồ nước định kỳ hàng tháng |
| **9** | `invoices` | Hóa đơn tiền phòng hàng tháng, tổng tiền, tiền đã trả, tiền nợ, hạn nộp |
| **10** | `invoice_details` | Chi tiết từng khoản phí tạo nên hóa đơn (dòng tiền phòng, số kWh điện, m³ nước) |
| **11** | `payments` | Lịch sử các lần thu tiền, mã giao dịch, phương thức nộp (tiền mặt/ngân hàng) |
| **12** | `maintenance_requests` | Yêu cầu báo hỏng, sửa chữa thiết bị từ khách thuê kèm mức độ ưu tiên |
| **13** | `expenses` | Sổ tay ghi chép chi phí vận hành khu trọ của chủ trọ (sửa chữa, thay bóng đèn) |
| **14** | `activity_logs` | Nhật ký hoạt động của các tài khoản (Audit trail) |
| **15** | `system_settings` | Cài đặt hệ thống (Tên nhà trọ, thông tin STK ngân hàng thụ hưởng VietQR) |
| **16** | `push_tokens` *(Bổ sung)* | Lưu trữ mã Expo Push Token của từng thiết bị di động |
| **17** | `payment_reminder_logs` *(Bổ sung)* | Nhật ký quét hạn nợ, chống gửi trùng lặp thông báo |
| **18** | `notification_preferences` *(Bổ sung)* | Tùy chọn bật/tắt nhận từng loại thông báo của người dùng |

---

## 3. Chi Tiết Các Bảng Cốt Lõi

### 3.1. Bảng `users` (Tài khoản)
- `id` (INT, PK, Auto Increment)
- `username` (VARCHAR(50), Unique): Tên đăng nhập hoặc Số điện thoại
- `password` (VARCHAR(255)): Mật khẩu đã được băm an toàn qua hàm `password_hash()`
- `fullname` (VARCHAR(100)): Họ và tên hiển thị
- `email` (VARCHAR(100), Nullable)
- `phone` (VARCHAR(20)): Số điện thoại liên hệ
- `role` (ENUM('admin', 'tenant')): Phân quyền truy cập
- `status` (TINYINT(1), Default 1): 1 là hoạt động, 0 là bị khóa

### 3.2. Bảng `rooms` (Phòng trọ)
- `id` (INT, PK, Auto Increment)
- `area_id` (INT, FK -> `rental_areas.id`): Khu trọ
- `room_code` (VARCHAR(20), Unique): Mã định danh phòng (Ví dụ: `P101`)
- `room_number` (VARCHAR(50)): Tên hiển thị (Ví dụ: `Phòng 101`)
- `area_size` (DECIMAL(6,2)): Diện tích phòng (m²)
- `price` (DECIMAL(12,2)): Giá thuê niêm yết (VNĐ/tháng)
- `deposit` (DECIMAL(12,2)): Tiền đặt cọc phòng
- `max_occupants` (INT, Default 2): Số người ở tối đa
- `status` (ENUM('empty', 'rented', 'maintenance')): Trạng thái phòng

### 3.3. Bảng `invoices` (Hóa đơn)
- `id` (INT, PK, Auto Increment)
- `invoice_code` (VARCHAR(50), Unique): Mã hóa đơn (Ví dụ: `HDN-202610-001`)
- `contract_id` (INT, FK -> `contracts.id`): Hợp đồng thuê
- `room_id` (INT, FK -> `rooms.id`): Phòng trọ
- `tenant_id` (INT, FK -> `tenants.id`): Người thuê nhận hóa đơn
- `billing_month` (VARCHAR(7)): Kỳ thanh toán định dạng `YYYY-MM` (Ví dụ: `2026-10`)
- `room_price` (DECIMAL(12,2)): Tiền phòng
- `electricity_amount` (DECIMAL(12,2)): Tiền điện tiêu thụ
- `water_amount` (DECIMAL(12,2)): Tiền nước tiêu thụ
- `service_amount` (DECIMAL(12,2)): Phí dịch vụ (rác, wifi)
- `other_fees` (DECIMAL(12,2)): Phụ phí phát sinh
- `discount` (DECIMAL(12,2)): Giảm giá / Khuyến mãi
- `total_amount` (DECIMAL(12,2)): Tổng số tiền phải nộp
- `paid_amount` (DECIMAL(12,2), Default 0): Số tiền đã thanh toán
- `remaining_amount` (DECIMAL(12,2)): Số tiền còn nợ (`total_amount - paid_amount`)
- `due_date` (DATE): Ngày hết hạn nộp tiền
- `status` (ENUM('unpaid', 'partially_paid', 'paid', 'overdue'))

### 3.4. Bảng `push_tokens` (Thiết bị nhận thông báo đẩy)
- `id` (INT, PK, Auto Increment)
- `user_id` (INT, FK -> `users.id` ON DELETE CASCADE)
- `token` (VARCHAR(255), Unique): Chuỗi Expo Push Token (Ví dụ: `ExponentPushToken[...]`)
- `device_type` (VARCHAR(50), Default 'mobile'): Hệ điều hành thiết bị (`android`, `ios`, `web`)
- `device_id` (VARCHAR(100), Nullable): Mã định danh thiết bị
- `is_active` (TINYINT(1), Default 1): Trạng thái hoạt động
- `created_at` (DATETIME), `updated_at` (DATETIME)

### 3.5. Bảng `notifications` (Lịch sử thông báo)
- `id` (INT, PK, Auto Increment)
- `title` (VARCHAR(255)): Tiêu đề thông báo
- `content` (TEXT): Nội dung thông báo
- `type` (VARCHAR(50)): Phân loại (`payment`, `maintenance`, `general`)
- `target_tenant_id` (INT, Nullable): ID người thuê nhận thông báo
- `target_user_id` (INT, Nullable): ID tài khoản nhận thông báo
- `is_read` (TINYINT(1), Default 0): 0 là chưa đọc, 1 là đã đọc
- `reference_type` (VARCHAR(50)): Loại đối tượng nghiệp vụ liên kết (`invoice`, `maintenance`)
- `reference_id` (INT, Nullable): ID đối tượng nghiệp vụ (ID hóa đơn hoặc ID sự cố)
- `data_payload` (TEXT, Nullable): Dữ liệu JSON điều hướng đính kèm

---

## 4. Quy Trình Chạy Migration An Toàn

File SQL migration được lưu tại:
`database/migrations/001_mobile_push_notifications.sql`

Lệnh thực thi an toàn thông qua script PHP CLI (tự động kiểm tra trùng lặp trước khi tạo):
```powershell
& "C:\wamp64\bin\php\php8.2.29\php.exe" scripts/migrate_mobile_push.php
```

---

## 5. Quy Trình Sao Lưu và Phục Hồi Dữ Liệu (Backup & Restore)

### 5.1. Sao lưu Cơ sở Dữ liệu (Backup bằng mysqldump)
Mở PowerShell và chạy lệnh trích xuất database ra file `.sql`:
```powershell
& "C:\wamp64\bin\mysql\mysql8.0.31\bin\mysqldump.exe" -u root -p quanlyphongtro > database/backup_quanlyphongtro.sql
```
*(Nếu MySQL không đặt mật khẩu, chỉ cần nhấn Enter khi được hỏi password).*

### 5.2. Phục hồi Cơ sở Dữ liệu (Restore)
Khi cần khôi phục lại dữ liệu ban đầu:
```powershell
& "C:\wamp64\bin\mysql\mysql8.0.31\bin\mysql.exe" -u root -p quanlyphongtro < database/backup_quanlyphongtro.sql
```
