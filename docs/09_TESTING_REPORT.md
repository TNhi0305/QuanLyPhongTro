# TÀI LIỆU 09: BÁO CÁO KIỂM THỬ HỆ THỐNG (TESTING REPORT)

Báo cáo này ghi nhận toàn bộ kết quả kiểm thử thực tế của dự án, bao gồm **Kiểm thử Đơn vị Tự động (Unit Test)**, **Kiểm thử Tĩnh (Static Analysis)**, **Kiểm thử API Endpoint**, **Kiểm thử Giao diện & Kiến trúc MVVM**, và **Kiểm thử Hệ thống Thông báo Đẩy (Push Notification)**.

---

## 1. Môi Trường Thực Hiện Kiểm Thử

| Thành phần | Thông số môi trường kiểm thử thực tế |
| :--- | :--- |
| **Hệ điều hành** | Windows 10/11 Pro (64-bit) |
| **Máy chủ Web & PHP** | Apache 2.4 (Cổng 8080), PHP 8.2.29 CLI |
| **Cơ sở dữ liệu** | MySQL 8.0.31 (Cổng 3306), 18 bảng dữ liệu quan hệ |
| **Node.js & npm** | Node.js v20+, npm 11.6.1 |
| **Frontend Framework** | React Native 0.86.3, Expo SDK ~57.0.28 |
| **Testing Framework** | Jest 30.5.2, ts-jest 29.4.15 |

---

## 2. Kết Quả Kiểm Thử Đơn Vị Tự Động (Jest Unit Test)

Lệnh thực thi trong PowerShell:
```powershell
cd c:\wamp64\www\QuanLyPhongTro\QuanLyPhongTroMobile
npm test
```

### 2.1. Nhật Ký Thực Thi Thực Tế (Execution Log)
```text
> quanlyphongtromobile@1.0.0 test
> jest

PASS __tests__/NotificationsViewModel.test.ts (8.884 s)
PASS __tests__/RoomsViewModel.test.ts (8.793 s)
PASS __tests__/InvoicesViewModel.test.ts (9.374 s)

Test Suites: 3 passed, 3 total
Tests:       12 passed, 12 total
Snapshots:   0 total
Time:        10.799 s
Ran all test suites.
```

### 2.2. Bảng Chi Tiết 12 Test Cases Độc Lập

| Mã Test | Bộ Test (Suite) | Tình huống kiểm thử | Kết quả mong đợi | Kết quả thực tế | Trạng thái |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **TC-01** | `RoomsViewModel` | Tải danh sách phòng thành công | Trả về 3 phòng, `loading` = false, `stats.total` = 3, `stats.rented` = 1 | Khớp 100% dữ liệu mock | **PASS** |
| **TC-02** | `RoomsViewModel` | Xử lý lỗi API khi repository hỏng | `error` lưu đúng thông báo lỗi, `loading` = false, `isEmpty` = true | Bắt đúng exception | **PASS** |
| **TC-03** | `RoomsViewModel` | Validation dữ liệu khi thêm phòng mới | Chặn khi thiếu số phòng; Thêm thành công khi đủ dữ liệu, tăng số lượng phòng | Validation chặt chẽ | **PASS** |
| **TC-04** | `RoomsViewModel` | Lọc phòng theo trạng thái (`statusFilter`) | Lọc `empty` ra 1 phòng P102; Lọc `rented` ra 1 phòng P101 | Bộ lọc hoạt động chính xác | **PASS** |
| **TC-05** | `InvoicesViewModel` | Tải hóa đơn & tính tổng doanh thu (`summary`) | Tổng số hóa đơn = 2, tổng đã thu = 3.050.000 đ, tổng chưa thu = 3.090.000 đ | Tính toán chính xác | **PASS** |
| **TC-06** | `InvoicesViewModel` | Ghi nhận thanh toán hóa đơn (`payInvoice`) | `remaining_amount` về 0, trạng thái hóa đơn đổi thành `paid`, `unpaidCount` về 0 | Cập nhật state chuẩn xác | **PASS** |
| **TC-07** | `InvoicesViewModel` | Bắt lỗi lập hóa đơn khi thiếu thông tin | Chặn khi thiếu `contract_id` hoặc thiếu `billing_month` | Báo lỗi tiếng Việt rõ ràng | **PASS** |
| **TC-08** | `NotificationsViewModel` | Tải thông báo & đếm số lượng chưa đọc | Danh sách có 2 thông báo, đếm được `unreadCount` = 1 | Đếm đúng số lượng | **PASS** |
| **TC-09** | `NotificationsViewModel` | Đánh dấu 1 thông báo là đã đọc (`markAsRead`) | `is_read` chuyển sang 1, `unreadCount` giảm từ 1 về 0 | Optimistic update mượt mà | **PASS** |
| **TC-10** | `NotificationsViewModel` | Đánh dấu đọc tất cả (`markAllAsRead`) | Tất cả thông báo có `is_read = 1`, `unreadCount` = 0 | Toàn bộ chuyển đã đọc | **PASS** |
| **TC-11** | `NotificationsViewModel` | Lọc thông báo chưa đọc (`filter = unread`) | Danh sách lọc chỉ còn lại các thông báo có `is_read = 0` | Lọc đúng danh sách | **PASS** |
| **TC-12** | `NotificationsViewModel` | Bật/tắt tùy chọn nhận thông báo (`Preferences`) | Tùy chọn `invoice` chuyển từ `true` sang `false` và lưu thành công | Cập nhật cấu hình chuẩn | **PASS** |

---

## 3. Kết Quả Kiểm Thử Tĩnh & Biên Dịch (Static & Typecheck)

| Nội dung kiểm tra | Lệnh thực hiện | Kết quả thực tế | Trạng thái |
| :--- | :--- | :--- | :---: |
| **Kiểm tra kiểu dữ liệu TypeScript** | `npx tsc --noEmit` | Không có lỗi biên dịch nào (0 errors) | **PASS** |
| **Kiểm tra cú pháp PHP Backend** | `php.exe -l routes/web.php`<br/>`php.exe -l app/Controllers/MobileApiController.php`<br/>`php.exe -l app/Controllers/PaymentController.php` | Cú pháp hoàn hảo, không có lỗi syntax | **PASS** |
| **Kiểm tra tính toàn vẹn Database** | `php scripts/migrate_mobile_push.php` | 18 bảng kết nối thành công, không xung đột | **PASS** |

---

## 4. Kết Quả Kiểm Thử API Endpoint

| Endpoint | Method | Dữ liệu đầu vào | Kết quả mong đợi | Kết quả thực tế | Trạng thái |
| :--- | :---: | :--- | :--- | :--- | :---: |
| `/api/health` | GET | Không | Trả về `{ success: true, message: ... }` | 200 OK, JSON chuẩn | **PASS** |
| `/api/demo-tenants` | GET | Không | Trả về danh sách khách thuê demo | 200 OK, có thông tin phòng | **PASS** |
| `/api/login` | POST | `{ username: "0912345678", password: "..." }` | Cấp token HMAC và dữ liệu `user` | 200 OK, token hợp lệ | **PASS** |
| `/api/login` (Sai pass) | POST | `{ username: "0912345678", password: "sai" }` | Báo lỗi 401 Unauthorized | 401 Unauthorized | **PASS** |
| `/api/dashboard` | GET | Header Bearer Token | Trả về số liệu thống kê Dashboard | 200 OK, số liệu chuẩn | **PASS** |
| `/api/rooms` | GET | Header Bearer Token | Trả về danh sách phòng trọ | 200 OK, đầy đủ diện tích, giá | **PASS** |
| `/api/invoices` | GET | Header Bearer Token | Trả về danh sách hóa đơn kèm link VietQR | 200 OK, link VietQR hợp lệ | **PASS** |
| `/api/push-token` | POST | `{ token: "ExponentPushToken[...]" }` | Lưu token vào bảng `push_tokens` | 200 OK, token được lưu DB | **PASS** |
| `/api/notifications/unread-count`| GET | Header Bearer Token | Trả về `{ success: true, unread_count: N }` | 200 OK, số lượng đúng | **PASS** |
| `/api/notifications/preferences` | GET | Header Bearer Token | Trả về các tùy chọn thông báo | 200 OK, đầy đủ 4 danh mục | **PASS** |

---

## 5. Kết Quả Kiểm Thử Kiến Trúc MVVM trên Giao Diện

| Màn hình View | ViewModel liên kết | Kiểm tra tính năng | Trạng thái |
| :--- | :--- | :--- | :---: |
| **Quản lý Phòng trọ** (`RoomsView`) | `useRoomsViewModel` | - Hiển thị Spinner khi `loading = true`<br/>- Hiển thị danh sách thẻ phòng<br/>- Bộ lọc trạng thái (Trống / Thuê / Bảo trì)<br/>- Kéo xuống làm mới (Pull-to-refresh)<br/>- Hiển thị banner lỗi khi mất mạng | **PASS** |
| **Quản lý Hóa đơn** (`InvoicesView`) | `useInvoicesViewModel` | - Hiển thị 3 thẻ tóm tắt doanh thu<br/>- Hiển thị chi tiết tiền phòng, điện, nước<br/>- Render mã QR VietQR tự động cho người thuê<br/>- Modal thu tiền của Admin cập nhật trạng thái `paid` | **PASS** |
| **Lịch sử Thông báo** (`NotificationsView`) | `useNotificationsViewModel` | - Badge hiển thị số lượng chưa đọc<br/>- Đánh dấu đã đọc khi chạm vào tin<br/>- Nút Đọc tất cả dọn sạch badge<br/>- Modal Switch bật/tắt từng loại thông báo | **PASS** |

---

## 6. Kết Quả Kiểm Thử Push Notification Thực Tế

| Kịch bản kiểm thử | Hành động thực hiện | Kết quả ghi nhận | Trạng thái |
| :--- | :--- | :--- | :---: |
| **Xin quyền thông báo** | Mở app lần đầu trên điện thoại thật | Hộp thoại hệ thống hỏi cấp quyền -> Người dùng nhấn Cho phép | **PASS** |
| **Đăng ký Token** | Đăng nhập tài khoản khách thuê | Token `ExponentPushToken[...]` được gửi và lưu vào bảng `push_tokens` | **PASS** |
| **Sự kiện 1: Hóa đơn mới** | Admin lập hóa đơn cho phòng thuê | Server lưu DB và gửi push payload tới Expo Push Service -> Điện thoại nhận chuông và banner | **PASS** |
| **Sự kiện 2: Xác nhận thanh toán** | Admin ghi nhận thanh toán hóa đơn | Server cập nhật DB và phát sinh push notification tới khách thuê | **PASS** |
| **Thông báo Foreground** | Gửi thông báo khi app đang mở | Banner thông báo nổi trong ứng dụng xuất hiện ở đầu màn hình | **PASS** |
| **Thông báo Background** | Gửi thông báo khi app đang ở màn hình Home điện thoại | Banner hệ thống xuất hiện trên thanh trạng thái | **PASS** |
| **Điều hướng từ thông báo** | Người dùng chạm vào thông báo | Ứng dụng đọc payload và tự động điều hướng sang tab Hóa Đơn | **PASS** |
| **Thông báo cục bộ lập lịch** | Nhấn nút "Thử lên lịch thông báo cục bộ (5s)" | Khóa màn hình -> Đúng 5 giây sau chuông và thông báo nhắc nhở xuất hiện | **PASS** |
| **Remote Push khi Terminated (Android 13+ không dev build)** | Tắt hẳn Expo Go trên Android không có chứng chỉ Google FCM riêng | Giới hạn nền tảng của Expo Go SDK 53+ trên Android 13 | **NOT TESTED (Yêu cầu EAS Dev Build)** |

---

## 7. Các Sự Cố Đã Phát Hiện và Biện Pháp Khắc Phục Trong Quá Trình Phát Triển

1. **Lỗi `TS2593: Cannot find name 'describe'` khi chạy Jest:**
   - *Nguyên nhân:* TypeScript compiler chưa nhận cấu hình type definitions của Jest.
   - *Biện pháp xử lý:* Đã thêm `"types": ["jest", "node"]` vào `jest.config.js` và `tsconfig.json`.

2. **Lỗi `TS2769: No overload matches this call` ở component Switch:**
   - *Nguyên nhân:* Hàm async `updatePreference` trả về `Promise<boolean>` trong khi prop `onValueChange` của React Native Switch yêu cầu `void | Promise<void>`.
   - *Biện pháp xử lý:* Bọc cuộc gọi trong block statement `(val) => { vm.updatePreference('...', val); }` trả về void.

3. **Lỗi CORS và Method Override trong Router PHP:**
   - *Nguyên nhân:* Trình duyệt gửi request preflight `OPTIONS` và các method RESTful `PUT`, `PATCH`, `DELETE` bị chặn.
   - *Biện pháp xử lý:* Đã bổ sung các phương thức `put()`, `patch()`, `delete()` và xử lý header `Access-Control-Allow-Methods` đầy đủ trong `app/Core/Router.php`.
