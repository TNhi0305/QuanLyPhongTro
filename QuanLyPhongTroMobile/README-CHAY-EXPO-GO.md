# Chạy ứng dụng khách thuê trên điện thoại thật (Android & iOS) — Expo Go + WAMP PHP

Ứng dụng điện thoại dành riêng cho **khách thuê phòng (Tenant)** xây dựng trên Expo / React Native; kết nối trực tiếp với backend PHP + MySQL đang chạy trên máy tính qua mạng Wi-Fi LAN. Hỗ trợ mượt mà trên cả **Android** và **iOS (iPhone)**.

---

## 1. Thông số kết nối hiện tại trên máy tính

- **IPv4 máy tính:** `192.168.100.36`
- **Cổng Apache WAMP:** `8080` (URL: `http://192.168.100.36:8080/QuanLyPhongTro/public/index.php?route=`)
- **Kiểm tra API trên trình duyệt:** `http://192.168.100.36:8080/QuanLyPhongTro/public/index.php?route=api/health`
- **Kết quả đúng:** `{"success": true, "message": "API và MySQL đang hoạt động.", ...}`

---

## 2. Tài khoản khách thuê sẵn sàng để đăng nhập

Hệ thống hỗ trợ đăng nhập bằng **Số điện thoại** hoặc **Tên đăng nhập**:

| Khách thuê | Số điện thoại | Tên đăng nhập | Phòng thuê | Mật khẩu |
| --- | --- | --- | --- | --- |
| **Nguyễn Văn An** | `0912345678` | `nguyenvanan` | Phòng 101 (Tầng 1) | `NhaTro@2026` |
| **Trần Thị Bình** | `0987654321` | `tranthib` | Phòng 102 (Tầng 1) | `NhaTro@2026` |

> **Mẹo trên ứng dụng mobile:** Tại màn hình đăng nhập có sẵn mục **"⚡ Đăng nhập mẫu khách hàng (1 chạm)"**, chỉ cần chạm vào tên khách thuê là hệ thống tự điền và đăng nhập ngay, không cần gõ bàn phím!

---

## 3. Cách chạy ứng dụng trên điện thoại thật

### A. Dành cho điện thoại Android

1. Mở **Google Play Store (CH Play)** trên Android, tìm và cài đặt ứng dụng **Expo Go**.
2. Kết nối Android vào **chung mạng Wi-Fi** với máy tính (hoặc bật Hotspot).
3. Chạy máy chủ Realtime WebSocket và ứng dụng Expo:

   - **Terminal 1 (Realtime Server WebSocket):**
     ```powershell
     cd C:\wamp64\www\QuanLyPhongTro\realtime-server
     npm start
     ```
   - **Terminal 2 (Expo Metro):**
     ```powershell
     cd C:\wamp64\www\QuanLyPhongTro\QuanLyPhongTroMobile
     npx expo start -c
     ```

4. Mở app **Expo Go** trên Android -> Bấm **"Scan QR code"** ngay trong app -> Quét mã QR hiển thị ở terminal máy tính.
   - Hoặc chọn mục nhập link: `exp://192.168.100.36:8081`

### B. Dành cho điện thoại iPhone (iOS)

1. Mở **App Store** trên iPhone, tìm và cài đặt ứng dụng **Expo Go**.
2. Kết nối iPhone vào **chung mạng Wi-Fi** với máy tính.
3. Mở ứng dụng **Camera (Máy ảnh)** mặc định của iPhone -> Hướng vào mã QR ở terminal -> Bấm vào thông báo vàng *"Open in Expo Go"*.
   - Hoặc mở app **Expo Go** -> Chọn **Enter URL manually** -> Nhập: `exp://192.168.100.36:8081`

### C. Dành cho máy ảo Android Studio (Emulator)

- Mở máy ảo Android Studio.
- Trong ứng dụng, bấm nút **"Đổi IP"** -> Chọn nút mẫu **"Giả lập Android (10.0.2.2:8080)"** -> Bấm **"Lưu & Áp dụng"**.

---

## 4. Xử lý khi đổi mạng Wi-Fi hoặc IP máy tính thay đổi

Ứng dụng trên điện thoại đã tích hợp thanh cấu hình máy chủ thông minh:

1. Nhìn lên thanh thông báo trên cùng của ứng dụng:
   - 🟢 **Màu xanh:** Đã kết nối thành công với máy tính.
   - 🔴 **Màu đỏ:** Chưa kết nối được.
2. Chạm vào chữ **"Đổi IP"** -> Nhập IPv4 mới của máy tính -> Bấm **"Thử kết nối"** -> Bấm **"Lưu & Áp dụng"**.
3. Cấu hình IP và token phiên đăng nhập được lưu tự động trên điện thoại bằng `AsyncStorage`.

---

## 5. Danh sách các tính năng dành cho khách thuê

1. **Tổng quan (Home):**
   - Xem thông tin phòng ở: Mã phòng, diện tích, địa chỉ, giá thuê, hợp đồng và thời hạn.
   - Xem các hóa đơn chưa thanh toán và số tiền còn nợ.
   - Xem thông báo mới nhất từ chủ trọ.
2. **Hóa đơn (Invoices):**
   - Tra cứu tiền phòng, tiền điện (chỉ số & thành tiền), tiền nước, internet, rác, gửi xe.
   - Xem trạng thái thanh toán (Đã thanh toán, Chưa thanh toán, Thanh toán một phần).
3. **Báo sửa chữa (Maintenance):**
   - Gửi yêu cầu sửa chữa hỏng hóc thiết bị (vòi nước, điều hòa, khóa cửa...).
   - Đặt mức độ ưu tiên: Thấp, Bình thường, Khẩn cấp.
   - Theo dõi tiến độ xử lý và phản hồi của chủ trọ.
4. **Cài đặt & Máy chủ (Settings):**
   - Xem thông tin cá nhân khách thuê (Họ tên, SĐT, Email).
   - Kiểm tra và đổi IP máy chủ kết nối.
   - Đăng xuất an toàn.

---

## 6. Xử lý sự cố thường gặp

- **Lỗi `Network request failed`:**
  1. Kiểm tra điện thoại và máy tính đã cùng một mạng Wi-Fi chưa.
  2. Kiểm tra WAMP Apache đang chạy (biểu tượng WAMP xanh lá, port 8080).
  3. Mở trình duyệt trên điện thoại (Chrome trên Android hoặc Safari trên iPhone) gõ thử: `http://192.168.100.36:8080/QuanLyPhongTro/public/index.php?route=api/health`. Nếu thấy JSON `{"success": true}` là kết nối bình thường.
- **Router bật tính năng Wi-Fi Client Isolation (chặn các máy trong LAN thấy nhau):**
  - Chạy lệnh tunnel của Expo:

    ```powershell
    npx expo start --tunnel
    ```

  - Quét mã QR tunnel trên điện thoại.
- **Nếu cắm cáp USB cho Android:**
  - Có thể chuyển tiếp cổng qua lệnh ADB:

    ```bash
    adb reverse tcp:8080 tcp:8080
    adb reverse tcp:8081 tcp:8081
    ```

  - Khi đó trên Android có thể trỏ thẳng vào `http://localhost:8080/QuanLyPhongTro/public/index.php?route=` cực kỳ ổn định.
- **Lỗi `Cannot find native module 'ExpoTopicSubscriptionModule'`:**
  - Android Expo Go không có native module mà phiên bản `expo-notifications` của dự án yêu cầu. Ứng dụng vẫn mở được trong Expo Go, nhưng tính năng thông báo sẽ không hoạt động.
  - Để thử thông báo, cần tạo và cài development build trên thiết bị Android (máy cần Android SDK và ADB):

    ```powershell
    cd C:\wamp64\www\QuanLyPhongTro\QuanLyPhongTroMobile
    npx expo run:android
    npx expo start --dev-client
    ```

  - Push notification từ xa trên Android cũng cần development build; Expo Go không hỗ trợ tính năng này.
