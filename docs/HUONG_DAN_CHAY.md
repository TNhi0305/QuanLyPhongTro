# HƯỚNG DẪN KHỞI CHẠY VÀ VẬN HÀNH DỰ ÁN

Tài liệu này cung cấp các bước chi tiết để khởi chạy toàn bộ hệ thống gồm WampServer (Apache, MySQL), Dịch vụ Realtime Socket.IO, Ứng dụng Mobile Expo, cũng như cấu hình IP mạng LAN.

---

## 1. Yêu cầu môi trường

- **Hệ điều hành:** Windows 10 / 11 (64-bit)
- **WampServer:** Đã cài đặt tại `C:\wamp64` với Apache (Cổng 8080) và MySQL (Cổng 3306).
- **Node.js:** Phiên bản `>= 20.x` (Khuyến nghị `v24.x` hoặc `v22.x LTS`).
- **Điện thoại di động thật:** Cài đặt ứng dụng **Expo Go** (Android từ CH Play, iOS từ App Store) hoặc máy ảo Android Emulator / iOS Simulator.
- **Mạng kết nối:** Máy tính và điện thoại di động phải kết nối vào **cùng một mạng Wi-Fi**.

---

## 2. Khởi động WampServer (Apache & MySQL)

1. Mở WampServer từ màn hình Desktop hoặc Start Menu (`wampmanager.exe`).
2. Quan sát biểu tượng Wamp ở khay hệ thống (System Tray): Biểu tượng chuyển sang **màu xanh lá (Green)**.
3. Kiểm tra cổng lắng nghe:
   - Apache đang mở cổng: `8080`
   - MySQL đang mở cổng: `3306`
4. Kiểm tra qua trình duyệt máy tính:
   - Truy cập: `http://localhost:8080/QuanLyPhongTro/public/`
   - Kiểm tra API Health: `http://localhost:8080/QuanLyPhongTro/api/health`
   - Kết quả trả về JSON: `{"success": true, "message": "API và MySQL đang hoạt động."}`.

---

## 3. Khởi chạy Dịch vụ Realtime Socket.IO

Dịch vụ Realtime xử lý kết nối trực tiếp tại cổng `3001`:

Mở một cửa sổ Terminal (PowerShell hoặc Command Prompt) mới tại thư mục dự án:

```powershell
cd c:\wamp64\www\QuanLyPhongTro\realtime-server
npm start
```

Khi chạy thành công, console sẽ hiển thị:
```text
====================================================
🚀 QuanLyPhongTro Realtime Server listening on port 3001
   Host: 0.0.0.0
   Health check: http://localhost:3001/health
====================================================
```

*Lưu ý:* Để kiểm tra dịch vụ Socket.IO đang hoạt động, truy cập `http://localhost:3001/health` trên trình duyệt.

---

## 4. Xác định địa chỉ IP Wi-Fi của máy tính

Để điện thoại kết nối được với máy tính chạy WAMP và Socket.IO:

1. Mở PowerShell trên máy tính và chạy lệnh:
   ```powershell
   ipconfig
   ```
2. Tìm mục **Wireless LAN adapter Wi-Fi** (hoặc Ethernet nếu dùng cáp), ghi nhớ dòng **IPv4 Address**.
   *(Ví dụ trong môi trường hiện tại: `172.23.4.219`)*.
3. Địa chỉ API sẽ là:
   ```text
   http://172.23.4.219:8080/QuanLyPhongTro/api/
   ```
4. Địa chỉ Socket.IO Realtime sẽ là:
   ```text
   http://172.23.4.219:3001
   ```

*Lưu ý Tường lửa Windows (Windows Firewall):* Nếu điện thoại không thể kết nối tới cổng 8080 hoặc 3001, mở Windows Defender Firewall -> Advanced Settings -> Inbound Rules -> Cho phép cổng 8080 và 3001 (TCP).

---

## 5. Khởi chạy Ứng dụng Di động Expo

Mở thêm một cửa sổ Terminal khác tại thư mục mobile:

```powershell
cd c:\wamp64\www\QuanLyPhongTro\QuanLyPhongTroMobile
npx expo start
```

Giao diện Metro Bundler sẽ hiện ra kèm một mã **QR Code**.

### 5.1. Chạy trên điện thoại di động thật
- **Thiết bị Android:** Mở ứng dụng **Expo Go**, chọn *Scan QR Code* và quét mã QR trên màn hình Terminal.
- **Thiết bị iOS:** Mở ứng dụng **Camera** mặc định, quét mã QR, sau đó chạm vào thông báo mở bằng *Expo Go*.

### 5.2. Chạy trên Android Emulator
- Nhấn phím `a` trên bàn phím Terminal để tự động kết nối với trình giả lập Android đang chạy.

### 5.3. Chạy trên Web Browser (Dành cho kiểm thử giao diện nhanh)
- Nhấn phím `w` trên bàn phím Terminal để mở ứng dụng trên trình duyệt web.

---

## 6. Cấu hình IP trên ứng dụng Mobile

1. Khi mở ứng dụng lần đầu, thanh trạng thái trên cùng sẽ hiển thị trạng thái kết nối máy chủ.
2. Nếu trạng thái hiển thị màu đỏ (chưa kết nối), bấm nút **Đổi IP**.
3. Nhập địa chỉ IP Wi-Fi của máy tính:
   ```text
   http://172.23.4.219:8080/QuanLyPhongTro/api/
   ```
4. Bấm **Thử kết nối** để kiểm tra, sau đó bấm **Lưu & Áp dụng**. Ứng dụng sẽ tự động lưu vào bộ nhớ đệm `AsyncStorage` và dùng địa chỉ này cho toàn bộ các lần mở sau.

---

## 7. Chạy kiểm thử tự động toàn diện (Automated E2E Test)

Dự án đã tích hợp kịch bản kiểm thử tự động 10 bước mô phỏng đồng thời 2 thiết bị (Chủ trọ và Người thuê), kiểm tra API, Push Notification, gửi/nhận Realtime Socket.IO và quét nhắc nợ:

Mở Terminal và chạy:

```powershell
cd c:\wamp64\www\QuanLyPhongTro\realtime-server
node test_e2e_integration.js
```

Kết quả hiển thị:
```text
🎉 TẤT CẢ 10 BƯỚC KIỂM THỬ ĐÃ THÀNH CÔNG 100%!
```

---

## 8. Thiết lập tác vụ nhắc hạn thanh toán định kỳ trên Windows (Windows Task Scheduler)

Để máy chủ tự động quét các hóa đơn đến hạn và gửi thông báo nhắc nhở mỗi ngày lúc 08:00 sáng:

1. Mở **Task Scheduler** trên Windows (`taskschd.msc`).
2. Chọn **Create Basic Task...**
   - **Name:** `QuanLyPhongTro_Payment_Reminder`
   - **Trigger:** Daily (Lặp lại hàng ngày lúc 08:00 AM)
   - **Action:** Start a program
   - **Program/script:** `C:\wamp64\bin\php\php8.4.15\php.exe`
   - **Add arguments:** `scripts\send_payment_reminders.php`
   - **Start in:** `C:\wamp64\www\QuanLyPhongTro\`
3. Hoàn tất tạo Task. Task này sẽ tự động chạy ngầm, quét dữ liệu và đẩy push notification đến khách thuê.
