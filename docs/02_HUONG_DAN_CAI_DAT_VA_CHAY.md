# TÀI LIỆU 02: HƯỚNG DẪN CÀI ĐẶT VÀ VẬN HÀNH HỆ THỐNG TRÊN WINDOWS

Tài liệu này cung cấp hướng dẫn chi tiết từng bước bằng PowerShell để triển khai trọn vẹn cả **Backend PHP/MySQL (WAMP Server)** và **Frontend Mobile App (React Native Expo)** trên hệ điều hành Windows.

---

## 1. Yêu Cầu Môi Trường Cài Đặt

| Phần mềm / Môi trường | Phiên bản khuyến nghị | Kiểm tra trong máy hiện tại |
| :--- | :--- | :--- |
| **Hệ điều hành** | Windows 10 / Windows 11 (64-bit) | Đã cài đặt |
| **WAMP Server** | WampServer 3.x trở lên | Đã cài tại `C:\wamp64\` |
| **PHP** | PHP 8.1 trở lên (khuyến nghị PHP 8.2) | `C:\wamp64\bin\php\php8.2.29\php.exe` |
| **MySQL** | MySQL 8.0 hoặc MariaDB 10.x | Cổng mặc định `3306` |
| **Node.js** | Node.js 18.x hoặc 20.x trở lên | Đã cài đặt |
| **npm** | npm 10.x trở lên | `11.6.1` |
| **Expo CLI** | Expo SDK ~57 (đi kèm dự án) | Đã cài đặt trong `node_modules` |

---

## 2. Chuẩn Bị & Khởi Động Máy Chủ WAMP

### Bước 2.1: Mở WampServer
1. Nhấn nút Windows, tìm và mở ứng dụng **WampServer**.
2. Quan sát biểu tượng WAMP ở góc phải thanh Taskbar (khay hệ thống):
   - 🔴 **Màu đỏ:** Apache và MySQL chưa khởi động.
   - 🟡 **Màu vàng:** Một trong hai dịch vụ chưa chạy.
   - 🟢 **Màu xanh lá cây:** Hệ thống đã sẵn sàng hoạt động.

### Bước 2.2: Kiểm tra cổng hoạt động của Apache và MySQL
Mở PowerShell và kiểm tra các cổng đang lắng nghe:
```powershell
Get-NetTCPConnection -State Listen | Where-Object { $_.LocalPort -in 80, 8080, 3306, 3001 } | Select-Object LocalAddress, LocalPort, OwningProcess
```
*Ghi chú quan trọng về cổng:*
- Cổng Apache của WAMP trong dự án này đang cấu hình là cổng **`8080`**.
- Cổng MySQL là cổng **`3306`**.

---

## 3. Cấu Hình Cơ Sở Dữ Liệu MySQL

### Bước 3.1: Vị trí file cấu hình database
File cấu hình kết nối database nằm tại:
`c:\wamp64\www\QuanLyPhongTro\config\database.php`

Nội dung cấu hình mặc định:
```php
return [
    'host'     => 'localhost',
    'port'     => 3306,
    'database' => 'quanlyphongtro',
    'username' => 'root',
    'password' => '', // Mặc định WAMP là rỗng
    'charset'  => 'utf8mb4'
];
```

### Bước 3.2: Thực thi file Migration cơ sở dữ liệu
Để đảm bảo tất cả các bảng dữ liệu nghiệp vụ, bảng push notification (`push_tokens`), bảng log nhắc nợ (`payment_reminder_logs`) và bảng tùy chọn (`notification_preferences`) được tạo đầy đủ mà **không làm mất bất kỳ dữ liệu phòng trọ hoặc người thuê hiện có**, chạy lệnh sau:

```powershell
cd c:\wamp64\www\QuanLyPhongTro
& "C:\wamp64\bin\php\php8.2.29\php.exe" scripts/migrate_mobile_push.php
```

Nếu thành công, màn hình sẽ hiển thị:
```text
Connecting to MySQL...
Checked/created push_tokens table.
Checked/created payment_reminder_logs table.
Checked/created notification_preferences table.
Migration completed successfully!
```

### Bước 3.3: Kiểm tra các bảng đã tồn tại
Chạy lệnh PowerShell sau để kiểm tra 18 bảng dữ liệu:
```powershell
& "C:\wamp64\bin\php\php8.2.29\php.exe" -r 'require "app/Helpers/Database.php"; $pdo = App\Helpers\Database::getConnection(); $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN); echo implode(", ", $tables);'
```

---

## 4. Kiểm Tra Hoạt Động của Backend REST API

Mở PowerShell và kiểm tra endpoint kiểm tra trạng thái máy chủ (Health Check):
```powershell
Invoke-RestMethod -Uri "http://localhost:8080/QuanLyPhongTro/api/health" -Method Get
```
Kết quả trả về thành công:
```powershell
success : True
message : API và MySQL đang hoạt động.
```

Thử gọi API lấy danh sách tài khoản demo:
```powershell
Invoke-RestMethod -Uri "http://localhost:8080/QuanLyPhongTro/api/demo-tenants" -Method Get
```

---

## 5. Xác Định Địa Chỉ IP Mạng LAN Để Chạy Trên Điện Thoại Thật

> [!WARNING]
> Khi chạy ứng dụng trên điện thoại thật hoặc máy ảo Android, **tuyệt đối không sử dụng địa chỉ `localhost` hay `127.0.0.1`**, vì trên điện thoại `localhost` sẽ trỏ về chính điện thoại đó, dẫn đến lỗi "Network request failed" hoặc "Connection refused".

### Bước 5.1: Tìm IP Wi-Fi của máy tính
Mở PowerShell và chạy:
```powershell
ipconfig
```
Tìm đến mục **Wireless LAN adapter Wi-Fi** (hoặc Ethernet), quan sát dòng `IPv4 Address`.
Ví dụ: `172.23.4.219` hoặc `192.168.1.15`.

### Bước 5.2: Địa chỉ API Base URL chuẩn
Định dạng địa chỉ API Base URL sẽ là:
`http://<IP_MAY_TINH>:8080/QuanLyPhongTro/api/`

Ví dụ thực tế:
`http://172.23.4.219:8080/QuanLyPhongTro/api/`

---

## 6. Chạy Bộ Kiểm Thử Tự Động (Jest Unit Test)

Trước khi khởi động ứng dụng di động, hãy chạy bộ kiểm thử đơn vị để xác nhận toàn bộ các ViewModel kiến trúc MVVM hoạt động hoàn hảo:

```powershell
cd c:\wamp64\www\QuanLyPhongTro\QuanLyPhongTroMobile
npm test
```

Kết quả thực tế đã được xác minh:
```text
PASS __tests__/NotificationsViewModel.test.ts
PASS __tests__/RoomsViewModel.test.ts
PASS __tests__/InvoicesViewModel.test.ts

Test Suites: 3 passed, 3 total
Tests:       12 passed, 12 total
Snapshots:   0 total
Time:        10.798 s
Ran all test suites.
```

---

## 7. Khởi Động Ứng Dụng Di Động React Native Expo

Di chuyển vào đúng thư mục chứa `package.json` của mobile app:
```powershell
cd c:\wamp64\www\QuanLyPhongTro\QuanLyPhongTroMobile
```

### Cách 1: Chạy trên Web Browser (Dễ demo nhất)
```powershell
npx expo start --web
```
Trình duyệt sẽ tự động mở trang web tại địa chỉ `http://localhost:8081`. Ứng dụng hoạt động đầy đủ giao diện, xác thực, danh sách phòng, hóa đơn và thông báo.

### Cách 2: Chạy trên Điện thoại thật qua Expo Go (Quét mã QR)
1. Kết nối điện thoại và máy tính vào **cùng một mạng Wi-Fi**.
2. Trên máy tính, khởi động Expo:
```powershell
npx expo start
```
3. Mở ứng dụng **Expo Go** trên điện thoại (tải miễn phí từ Google Play Store hoặc Apple App Store).
4. **Android:** Nhấn "Scan QR code" và quét mã QR hiển thị trên màn hình máy tính.
5. **iOS:** Mở ứng dụng Camera mặc định của iPhone và quét mã QR, sau đó nhấn mở trong Expo Go.

### Cách 3: Chạy trên Android Emulator (Máy ảo Android Studio)
1. Mở Android Studio và khởi động một thiết bị máy ảo (AVD).
2. Chạy lệnh:
```powershell
npx expo start --android
```
*(Nếu dùng Android Emulator, có thể cấu hình IP server là `http://10.0.2.2:8080/QuanLyPhongTro/api/`).*

---

## 8. Hướng Dẫn Cấu Hình Địa Chỉ IP Server Ngay Trên Ứng Dụng

Ứng dụng di động đã tích hợp sẵn công cụ **"Cấu hình máy chủ"** trực quan ngay tại màn hình đăng nhập:

1. Mở ứng dụng trên điện thoại.
2. Tại màn hình đăng nhập, nhấn vào nút **"⚙️ Cấu hình máy chủ"**.
3. Nhập địa chỉ IP Wi-Fi của máy tính bạn vừa tìm được ở Bước 5:
   `http://172.23.4.219:8080/QuanLyPhongTro/api/`
4. Nhấn **"KIỂM TRA KẾT NỐI"**:
   - Nếu hiển thị thông báo xanh: *"Kết nối máy chủ thành công!"* -> Nhấn **"LƯU CẤU HÌNH"**.
   - Nếu báo lỗi kết nối -> Xem mục xử lý sự cố ở phần 9 dưới đây.

---

## 9. Khắc Phục Các Sự Cố Thường Gặp (Troubleshooting)

### Sự cố 1: Điện thoại không kết nối được WAMP ("Network request failed")
- **Nguyên nhân:** Windows Defender Firewall chặn cổng 8080 của Apache đối với các kết nối từ thiết bị mạng LAN bên ngoài.
- **Cách xử lý:** Mở PowerShell bằng quyền Administrator và thêm rule mở cổng 8080:
```powershell
New-NetFirewallRule -DisplayName "Cho phep WAMP Apache 8080" -Direction Inbound -LocalPort 8080 -Protocol TCP -Action Allow
```
- Đảm bảo trong file `httpd.conf` của Apache có dòng `Require all granted` thay vì `Require local`.

### Sự cố 2: Cổng 8080 hoặc 3001 bị chiếm dụng
- Tìm tiến trình đang chiếm cổng:
```powershell
Get-Process -Id (Get-NetTCPConnection -LocalPort 8080).OwningProcess
```
- Tắt tiến trình chiếm dụng nếu không cần thiết:
```powershell
Stop-Process -Id <PID> -Force
```

### Sự cố 3: MySQL báo lỗi "Access denied for user 'root'@'localhost'"
- Mở file `c:\wamp64\www\QuanLyPhongTro\config\database.php`.
- Kiểm tra lại mật khẩu `password`. Trong WAMP mặc định mật khẩu của `root` là chuỗi rỗng `''`.

### Sự cố 4: Push Notification trên thiết bị thật trong môi trường Expo Go
- Khi đăng nhập trên điện thoại thật, ứng dụng sẽ tự động yêu cầu cấp quyền thông báo. Hãy nhấn **"Allow" (Cho phép)**.
- Từ Expo SDK 53+, remote push notification trên một số máy Android yêu cầu thiết lập FCM credentials. Ứng dụng đã được tích hợp cơ chế tự động nhận diện và cung cấp tính năng **Lập lịch thông báo cục bộ (Local Notification)** với đầy đủ âm thanh và banner thông báo để demo thực tế 100% không phụ thuộc dịch vụ bên ngoài.

---

## 10. Danh Sách Lệnh Tóm Tắt (Cheatsheet)

```powershell
# 1. Kiểm tra WAMP Apache đang chạy:
Test-NetConnection -ComputerName localhost -Port 8080

# 2. Chạy migration database:
& "C:\wamp64\bin\php\php8.2.29\php.exe" scripts/migrate_mobile_push.php

# 3. Chạy Jest Unit Test ViewModel:
cd c:\wamp64\www\QuanLyPhongTro\QuanLyPhongTroMobile
npm test

# 4. Chạy Mobile App Expo:
npx expo start
```
