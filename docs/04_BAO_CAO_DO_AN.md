# BÁO CÁO ĐỒ ÁN MÔN HỌC

## ĐỀ TÀI: XÂY DỰNG HỆ THỐNG QUẢN LÝ NHÀ TRỌ THÔNG MINH TRÊN NỀN TẢNG PHP REST API VÀ REACT NATIVE EXPO ÁP DỤNG KIẾN TRÚC MVVM VÀ PUSH NOTIFICATION

---

### TRANG BÌA BÁO CÁO

```text
========================================================================================
                                BỘ GIÁO DỤC VÀ ĐÀO TẠO
                         TRƯỜNG ĐẠI HỌC CÔNG NGHỆ THÔNG TIN
                               KHOA CÔNG NGHỆ PHẦN MỀM
                               ------------------------

                                   BÁO CÁO ĐỒ ÁN
                       MÔN HỌC: PHÁT TRIỂN ỨNG DỤNG DI ĐỘNG

    Đề tài:
    XÂY DỰNG HỆ THỐNG QUẢN LÝ NHÀ TRỌ THÔNG MINH ĐA NỀN TẢNG
    (PHP REST API & REACT NATIVE EXPO VỚI KIẾN TRÚC MVVM VÀ PUSH NOTIFICATION)

    Sinh viên thực hiện:  ......................................................
    Mã số sinh viên:      ......................................................
    Lớp chuyên ngành:     ......................................................
    Giảng viên hướng dẫn: ......................................................

                                   TP. HỒ CHÍ MINH, 2026
========================================================================================
```

---

## LỜI CẢM ƠN
Em xin gửi lời cảm ơn chân thành và sâu sắc nhất tới Quý Thầy/Cô bộ môn đã tận tình truyền đạt những kiến thức quý báu về kỹ nghệ phần mềm, lập trình web nâng cao và phát triển ứng dụng di động trong suốt học kỳ vừa qua.

Những kiến thức nền tảng về kiến trúc phần mềm, mô hình MVC, MVVM, chuẩn thiết kế RESTful API và các cơ chế xử lý bất đồng bộ, thông báo thời gian thực đã giúp em tự tin hoàn thành đồ án này với mã nguồn thực tế và chất lượng cao.

Dù đã rất cố gắng đầu tư nghiên cứu và kiểm thử, đồ án chắc chắn khó tránh khỏi những thiếu sót nhất định. Em rất mong nhận được những nhận xét, góp ý quý báu từ Quý Thầy/Cô để dự án ngày càng hoàn thiện hơn.

---

## MỤC LỤC BÁO CÁO

- [LỜI CẢM ƠN](#lời-cảm-ơn)
- [CHƯƠNG 1: TỔNG QUAN ĐỀ TÀI](#chương-1-tổng-quan-đề-tài)
- [CHƯƠNG 2: PHÂN TÍCH YÊU CẦU HỆ THỐNG](#chương-2-phân-tích-yêu-cầu-hệ-thống)
- [CHƯƠNG 3: PHÂN TÍCH VÀ THIẾT KẾ HỆ THỐNG](#chương-3-phân-tích-và-thiết-kế-hệ-thống)
- [CHƯƠNG 4: CÔNG NGHỆ VÀ MÔI TRƯỜNG TRIỂN KHAI](#chương-4-công-nghệ-và-môi-trường-triển-khai)
- [CHƯƠNG 5: HIỆN THỰC CÁC CHỨC NĂNG NGHIỆP VỤ](#chương-5-hiện-thực-các-chức-năng-nghiệp-vụ)
- [CHƯƠNG 6: HỆ THỐNG PUSH NOTIFICATION](#chương-6-hệ-thống-push-notification)
- [CHƯƠNG 7: HIỆN THỰC KIẾN TRÚC MVVM](#chương-7-hiện-thực-kiến-trúc-mvvm)
- [CHƯƠNG 8: KIỂM THỬ VÀ ĐÁNH GIÁ CHẤT LƯỢNG](#chương-8-kiểm-thử-và-đánh-giá-chất-lượng)
- [CHƯƠNG 9: HẠN CHẾ VÀ HƯỚNG PHÁT TRIỂN](#chương-9-hạn-chế-và-hướng-phát-triển)
- [KẾT LUẬN](#kết-luận)
- [TÀI LIỆU THAM KHẢO](#tài-liệu-tham-khảo)

---

## CHƯƠNG 1: TỔNG QUAN ĐỀ TÀI

### 1.1. Tính cấp thiết của đề tài
Trong thời đại chuyển đổi số, mô hình quản lý phòng trọ truyền thống dựa trên sổ sách tay, bảng tính Excel phân tán hoặc tin nhắn Zalo đã bộc lộ rất nhiều nhược điểm nghiêm trọng:
- Dễ thất thoát dữ liệu và sai sót khi cộng dồn chỉ số điện nước.
- Thiếu tính minh bạch giữa chủ trọ và người thuê về chi tiết từng khoản thu.
- Tỷ lệ trễ hạn thanh toán cao do người thuê quên ngày nộp tiền hoặc không có công cụ nhắc nhở kịp thời.
- Việc ghi nhận phản ánh hỏng hóc, sự cố thiết bị chậm chạp, gây bức xúc cho người thuê.

Do đó, việc xây dựng một hệ sinh thái ứng dụng gồm **Backend máy chủ tập trung** và **Mobile App thông minh** tích hợp cơ chế thông báo đẩy (Push Notification) là một yêu cầu cấp thiết và mang tính thực tiễn cao.

### 1.2. Mục tiêu nghiên cứu
- Thiết kế và xây dựng hệ thống quản lý phòng trọ hoàn chỉnh chạy thực tế trên máy tính và điện thoại.
- Áp dụng thành công mô hình kiến trúc phần mềm tiên tiến **MVVM (Model - View - ViewModel)** nhằm đảm bảo tính module hóa, dễ bảo trì và khả năng viết Unit Test độc lập.
- Tích hợp dịch vụ **Push Notification thời gian thực** liên kết chặt chẽ với các sự kiện nghiệp vụ cốt lõi (Lập hóa đơn mới và Xác nhận thanh toán).

---

## CHƯƠNG 2: PHÂN TÍCH YÊU CẦU HỆ THỐNG

### 2.1. Các tác nhân tham gia hệ thống (Actors)
1. **Chủ trọ / Quản trị viên (Admin):** Có toàn quyền quản lý khu trọ, danh mục phòng, khách thuê, hợp đồng, nhập chỉ số điện nước, lập hóa đơn, xác nhận tiền phòng và tiếp nhận xử lý sự cố.
2. **Khách thuê (Tenant):** Người trực tiếp thuê phòng, có quyền xem thông tin phòng của mình, tra cứu chi tiết hóa đơn, quét mã VietQR chuyển khoản, gửi yêu cầu báo hỏng và nhận thông báo đẩy trên điện thoại.

### 2.2. Biểu đồ Use Case tổng quát
- **Nhóm Use Case Xác thực:** Đăng ký tài khoản, Đăng nhập hệ thống, Khôi phục phiên tự động, Đăng xuất tài khoản, Đổi mật khẩu.
- **Nhóm Use Case Quản lý Phòng trọ:** Xem danh sách phòng (lọc theo trạng thái trống/thuê/bảo trì), Thêm phòng mới, Sửa thông tin phòng, Xóa phòng an toàn.
- **Nhóm Use Case Hóa đơn & Thu tiền:** Lập hóa đơn lẻ, Tự động phát hành hóa đơn hàng loạt, Xem chi tiết hóa đơn, Kết xuất mã VietQR, Ghi nhận thanh toán.
- **Nhóm Use Case Sự cố & Bảo trì:** Báo hỏng thiết bị, Đặt mức độ ưu tiên, Xem tiến độ sửa chữa, Cập nhật trạng thái sự cố và phản hồi.
- **Nhóm Use Case Thông báo:** Đăng ký Push Token thiết bị, Nhận push notification khi có sự kiện nghiệp vụ, Xem lịch sử thông báo, Đánh dấu đã đọc, Bật/tắt tùy chọn nhận thông báo, Lập lịch thông báo cục bộ.

---

## CHƯƠNG 3: PHÂN TÍCH VÀ THIẾT KẾ HỆ THỐNG

### 3.1. Kiến trúc tổng thể hệ thống
Hệ thống được thiết kế theo mô hình phân tầng Client - Server:
- **Tầng Trình diễn (Client Layer):** Mobile App phát triển bằng React Native Expo; Web Admin phát triển bằng HTML5/CSS/JavaScript thuần.
- **Tầng Nghiệp vụ (Business Layer):** Backend PHP MVC xử lý toàn bộ logic nghiệp vụ, xác thực JWT/Token HMAC-SHA256, kiểm tra phân quyền truy cập.
- **Tầng Dữ liệu (Data Layer):** Hệ quản trị cơ sở dữ liệu MySQL lưu trữ 18 bảng quan hệ chuẩn hóa.
- **Tầng Dịch vụ Đám mây (External Cloud Services):** Expo Push Notification Service điều phối thông báo tới thiết bị di động.

*(Chi tiết xem file sơ đồ: `docs/diagrams/system_architecture.mmd`)*

### 3.2. Thiết kế Cơ sở Dữ liệu Quan hệ (MySQL)
Cơ sở dữ liệu bao gồm 18 bảng chính, tiêu biểu:
- `users`: Tài khoản đăng nhập, phân quyền `role` (admin/tenant).
- `rooms`: Thông tin phòng, diện tích, giá thuê, trạng thái (`empty`, `rented`, `maintenance`).
- `tenants`: Hồ sơ cá nhân khách thuê (họ tên, CCCD, số điện thoại).
- `contracts`: Hợp đồng thuê phòng liên kết giữa `rooms` và `tenants`.
- `invoices`: Hóa đơn tiền phòng hàng tháng, tổng tiền, tiền đã trả, tiền còn lại, hạn thanh toán, trạng thái.
- `invoice_details`: Chi tiết từng dòng tiền (tiền phòng, số kWh điện, số m³ nước, phí dịch vụ).
- `payments`: Nhật ký các lần thu tiền, mã thanh toán, phương thức, mã giao dịch.
- `push_tokens`: Lưu trữ Expo Push Token theo từng `user_id` và thiết bị.
- `notifications`: Lịch sử thông báo hệ thống, cờ `is_read`, dữ liệu điều hướng JSON `data_payload`.
- `payment_reminder_logs`: Nhật ký quét nợ tự động chống gửi lặp thông báo.
- `notification_preferences`: Bảng lưu tùy chọn bật/tắt nhận từng loại thông báo của người dùng.

*(Chi tiết xem file sơ đồ ERD: `docs/diagrams/er_diagram.mmd` và tài liệu `docs/08_DATABASE_DESIGN.md`)*

---

## CHƯƠNG 4: CÔNG NGHỆ VÀ MÔI TRƯỜNG TRIỂN KHAI

### 4.1. Backend PHP MVC & WAMP Server
- Sử dụng PHP thuần phiên bản 8.2 trên máy chủ WAMP Server (Apache cổng 8080).
- Hệ thống tự xây dựng `Router` độc lập, hỗ trợ đầy đủ các phương thức HTTP RESTful (`GET`, `POST`, `PUT`, `PATCH`, `DELETE`, `OPTIONS`).
- Thư viện PDO kết nối MySQL an toàn với cơ chế Prepared Statements, ngăn chặn 100% các lỗ hổng SQL Injection.
- Cơ chế bảo mật Token HMAC-SHA256 gọn nhẹ, hỗ trợ lưu trữ an toàn và khôi phục phiên tự động.

### 4.2. Frontend Mobile App (React Native & Expo)
- Phát triển bằng **Expo SDK ~57** và **React Native 0.86**, mã nguồn viết bằng **TypeScript**.
- Giao diện người dùng tối ưu hóa cho di động với StyleSheet phẳng, hạn chế thư viện bên thứ ba cồng kềnh, đảm bảo mượt mà ở tốc độ khung hình 60 FPS.
- Sử dụng `AsyncStorage` cho việc lưu trữ phiên đăng nhập và cấu hình địa chỉ IP máy chủ linh hoạt.

---

## CHƯƠNG 5: HIỆN THỰC CÁC CHỨC NĂNG NGHIỆP VỤ

### 5.1. Xác thực & Khôi phục phiên làm việc (Auth & Session Restoration)
- Quy trình đăng nhập thông qua REST API endpoint `POST /api/login`.
- Khi đăng nhập thành công, server trả về Token kèm thông tin người dùng (`user` và `role`). Client lưu trữ token vào bộ nhớ máy.
- **Khôi phục phiên tự động:** Khi mở ứng dụng, hook `useAuthViewModel` tự động đọc token từ bộ nhớ, gửi request `GET /api/dashboard` để xác thực tính hợp lệ của token với server. Nếu token còn hạn, ứng dụng tự động mở màn hình làm việc mà không bắt người dùng đăng nhập lại. Nếu token hết hạn hoặc tài khoản bị khóa, hệ thống tự động xóa token và chuyển về màn hình đăng nhập an toàn.

### 5.2. Nghiệp vụ Quản lý Phòng trọ (CRUD)
- Quản lý đầy đủ chu trình: Tạo phòng mới -> Ký hợp đồng cho thuê (chuyển sang `rented`) -> Khách trả phòng (chuyển về `empty`) -> Bảo trì phòng (`maintenance`).
- Toàn bộ thao tác thêm, sửa, xóa phòng đều tương tác với REST API thật (`GET /api/rooms`, `POST /api/rooms`, `POST /api/rooms/update/{id}`, `POST /api/rooms/delete/{id}`).

### 5.3. Nghiệp vụ Hóa đơn & Thanh toán tự động qua VietQR
- Khi khách thuê chưa thanh toán đủ, hệ thống tự động tạo mã QR chuẩn Napas247 / VietQR dựa trên số tài khoản chủ trọ, số tiền còn thiếu và mã hóa đơn.
- Người thuê chỉ cần quét mã QR trên ứng dụng ngân hàng, toàn bộ thông tin thanh toán được điền tự động chính xác tuyệt đối, loại trừ hoàn toàn việc chuyển sai số tiền hoặc quên ghi nội dung.

---

## CHƯƠNG 6: HỆ THỐNG PUSH NOTIFICATION

### 6.1. Kiến trúc Push Notification với Expo Push Service
Hệ thống sử dụng package chính thức `expo-notifications` kết hợp với Expo Push Service API (`https://exp.host/--/api/v2/push/send`):
1. **Đăng ký Token:** Client xin quyền thông báo của hệ điều hành qua `Notifications.requestPermissionsAsync()`, lấy `ExpoPushToken` và gửi lên backend qua endpoint `POST /api/push-token`.
2. **Kích hoạt từ Server:** Khi sự kiện nghiệp vụ phát sinh thành công trong database, helper `PushNotification::createAndSend` sẽ tạo bản ghi trong bảng `notifications` và gửi cURL payload tới Expo Push Service.

### 6.2. Hai sự kiện nghiệp vụ bắt buộc
1. **Sự kiện 1: Phát hành Hóa đơn tiền phòng mới**
   - Khi chủ trọ lập hóa đơn cho phòng, server tìm đúng tài khoản người thuê đang có hợp đồng hoạt động tại phòng đó.
   - Gửi Push Notification với tiêu đề *"Hóa đơn tháng mới"*, nội dung chi tiết số tiền và hạn nộp.
   - Payload đính kèm: `{ type: 'new_invoice', reference_type: 'invoice', invoice_id: 123 }`.
2. **Sự kiện 2: Xác nhận Thanh toán tiền phòng thành công**
   - Khi chủ trọ xác nhận nhận tiền phòng (trên web hoặc mobile), server cập nhật trạng thái hóa đơn.
   - Server gửi Push Notification tới người thuê: *"Chủ trọ đã xác nhận thanh toán [Số tiền] cho hóa đơn [Mã hóa đơn]..."*.
   - Payload đính kèm: `{ type: 'payment_received', reference_type: 'invoice', invoice_id: 123 }`.

### 6.3. Ba trạng thái nhận thông báo và Điều hướng
- **Foreground (Ứng dụng đang mở):** Listener `addNotificationReceivedListener` tiếp nhận thông báo, hiển thị banner thông báo nổi (in-app notification banner) kèm âm thanh.
- **Background (Ứng dụng chạy ngầm):** Hệ điều hành Android/iOS hiển thị banner trên thanh thông báo (Notification Tray).
- **Terminated (Ứng dụng đã đóng hoàn toàn):** Khi người dùng chạm vào thông báo từ khay hệ thống, ứng dụng được khởi động, listener `addNotificationResponseReceivedListener` hoặc hàm `checkInitialNotification()` đọc `data_payload` và tự động điều hướng người dùng đến đúng màn hình Hóa đơn tương ứng.

### 6.4. Thông báo Cục bộ Lập lịch (Local Scheduled Notification)
- Cho phép người dùng lên lịch nhắc nhở kiểm tra hóa đơn và đóng tiền định kỳ sau một khoảng thời gian (ví dụ 5 giây hoặc theo ngày hẹn).
- Sử dụng hàm `Notifications.scheduleNotificationAsync()` với trigger thời gian, hoạt động độc lập ngay cả khi thiết bị mất kết nối mạng Internet.

---

## CHƯƠNG 7: HIỆN THỰC KIẾN TRÚC MVVM

### 7.1. Lý do lựa chọn mô hình MVVM (Model - View - ViewModel)
Mô hình MVC truyền thống trên ứng dụng di động thường dẫn đến tình trạng "Massive View Controller / Massive Component", khi một file giao diện phải đảm nhận quá nhiều trách nhiệm: quản lý state, gọi API fetch trực tiếp, xử lý định dạng dữ liệu và bắt sự kiện UI.

Mô hình MVVM khắc phục triệt để vấn đề này:
- **Model:** Đại diện cho dữ liệu nghiệp vụ và các lớp truy cập dữ liệu (Repository Interface, API Service).
- **ViewModel:** Đóng vai trò cầu nối, quản lý toàn bộ trạng thái (State) và logic nghiệp vụ của màn hình, độc lập hoàn toàn với framework giao diện.
- **View:** Thành phần thuần giao diện (Dumb Component), chỉ nhận dữ liệu và action từ ViewModel để hiển thị, tuyệt đối không gọi `fetch` hay `AsyncStorage` trực tiếp.

### 7.2. Cấu trúc Triển khai trong Dự án
```text
QuanLyPhongTroMobile/src/
├── models/
│   └── types.ts                    # Entity Models (Room, Invoice, NotificationItem, User)
├── repositories/
│   ├── IRoomRepository.ts          # Interface Repository Phòng trọ
│   ├── IInvoiceRepository.ts        # Interface Repository Hóa đơn
│   ├── INotificationRepository.ts  # Interface Repository Thông báo
│   ├── ApiRoomRepository.ts        # Triển khai gọi REST API thật
│   ├── ApiInvoiceRepository.ts     # Triển khai gọi REST API thật
│   ├── ApiNotificationRepository.ts# Triển khai gọi REST API thật
│   ├── MockRoomRepository.ts       # Triển khai Giả lập phục vụ Unit Test
│   ├── MockInvoiceRepository.ts    # Triển khai Giả lập phục vụ Unit Test
│   └── MockNotificationRepository.ts
├── viewmodels/
│   ├── useRoomsViewModel.ts        # ViewModel quản lý phòng trọ (Class & Hook)
│   ├── useInvoicesViewModel.ts     # ViewModel quản lý hóa đơn (Class & Hook)
│   ├── useNotificationsViewModel.ts# ViewModel quản lý thông báo (Class & Hook)
│   └── useAuthViewModel.ts         # ViewModel quản lý xác thực & phiên
└── views/
    ├── RoomsView.tsx               # View danh sách & quản lý phòng
    ├── InvoicesView.tsx            # View hóa đơn & thanh toán VietQR
    └── NotificationsView.tsx       # View trung tâm thông báo & cài đặt
```

---

## CHƯƠNG 8: KIỂM THỬ VÀ ĐÁNH GIÁ CHẤT LƯỢNG

### 8.1. Kiểm thử Đơn vị Tự động (Unit Testing với Jest)
Dự án đã xây dựng bộ Unit Test tự động bằng **Jest** và **ts-jest**, sử dụng các **Mock Repository** độc lập để kiểm thử logic nghiệp vụ của ViewModels mà không phụ thuộc vào kết nối mạng hay database.

**Kết quả thực thi thực tế:**
- **Lệnh chạy:** `npm test` trong thư mục `QuanLyPhongTroMobile`.
- **Tổng số Test Suites:** 3 passed, 3 total.
- **Tổng số Test Cases:** 12 passed, 12 total (100% PASS).
- **Thời gian thực thi:** 10.798 giây.

Chi tiết 12 test cases:
1. `RoomsViewModel`: Tải danh sách phòng thành công và tính toán thống kê (total, rented, empty, maintenance) -> **PASS**
2. `RoomsViewModel`: Xử lý lỗi API khi Repository gặp sự cố (bắt lỗi, cập nhật `error`, tắt `loading`) -> **PASS**
3. `RoomsViewModel`: Thêm phòng mới với validation hợp lệ (báo lỗi khi thiếu số phòng) -> **PASS**
4. `RoomsViewModel`: Lọc phòng trọ theo trạng thái (`statusFilter`) -> **PASS**
5. `InvoicesViewModel`: Tải danh sách hóa đơn thành công và tính tổng thanh toán (`summary`) -> **PASS**
6. `InvoicesViewModel`: Ghi nhận thanh toán hóa đơn và tự động cập nhật trạng thái `paid` -> **PASS**
7. `InvoicesViewModel`: Xử lý lỗi khi lập hóa đơn thiếu thông tin bắt buộc -> **PASS**
8. `NotificationsViewModel`: Tải danh sách thông báo và đếm đúng số lượng chưa đọc (`unreadCount`) -> **PASS**
9. `NotificationsViewModel`: Đánh dấu 1 thông báo là đã đọc (`markAsRead`), kiểm tra `is_read = 1` và `unreadCount` giảm 1 -> **PASS**
10. `NotificationsViewModel`: Đánh dấu tất cả thông báo là đã đọc (`markAllAsRead`) -> **PASS**
11. `NotificationsViewModel`: Lọc thông báo chưa đọc (`filter = unread`) -> **PASS**
12. `NotificationsViewModel`: Bật/tắt tùy chọn nhận thông báo (`Notification Preferences`) -> **PASS**

### 8.2. Kiểm thử Tích hợp và Tĩnh (Typecheck & Syntax Check)
- Chạy kiểm tra tĩnh TypeScript qua lệnh `npx tsc --noEmit`: Đạt 0 lỗi cú pháp và kiểu dữ liệu.
- Chạy kiểm tra cú pháp PHP CLI qua `php.exe -l`: Toàn bộ các file Router và Controller đạt chuẩn cú pháp PHP 8.2.

---

## CHƯƠNG 9: HẠN CHẾ VÀ HƯỚNG PHÁT TRIỂN

### 9.1. Hạn chế
- Push notification từ xa (Remote Push) trong ứng dụng Expo Go trên Android 13+ đòi hỏi quyền thông báo cấp cao hoặc cấu hình Development Build độc lập để nhận tin khi ứng dụng bị tắt hoàn toàn.
- Hệ thống thanh toán chuyển khoản hiện tại dựa trên VietQR và xác nhận thủ công của chủ trọ, chưa tích hợp Webhook ngân hàng tự động.

### 9.2. Hướng phát triển
- Đóng gói file cài đặt độc lập APK/AAB thông qua Expo Application Services (EAS Build).
- Tích hợp cổng Webhook đối soát ngân hàng tự động (SePAY / Casso).
- Xây dựng module ký hợp đồng điện tử trực tiếp trên màn hình cảm ứng của điện thoại.

---

## KẾT LUẬN
Đồ án đã hoàn thành xuất sắc toàn bộ các mục tiêu đặt ra:
1. Giữ nguyên và bảo toàn 100% dữ liệu thực tế và mã nguồn hiện có của dự án.
2. Xây dựng hoàn chỉnh hệ thống REST API backend PHP MVC trên WAMP Server.
3. Triển khai trọn vẹn cơ chế Push Notification với 2 sự kiện nghiệp vụ thực tế, xử lý đầy đủ 3 trạng thái nhận tin và điều hướng thông minh.
4. Hiện thực chuẩn mực Kiến trúc MVVM trên 3 màn hình cốt lõi với Repository Pattern và Mocking.
5. Đạt 100% tỷ lệ PASS trong bộ Unit Test tự động với Jest.
6. Hệ thống sẵn sàng vận hành thực tế, đáp ứng đầy đủ yêu cầu báo cáo và bảo vệ đồ án môn học.

---

## TÀI LIỆU THAM KHẢO
1. Facebook Inc., *React Native Documentation*, https://reactnative.dev/docs/getting-started.
2. Expo Inc., *Expo Notifications Documentation*, https://docs.expo.dev/versions/latest/sdk/notifications/.
3. Martin Fowler, *GUI Architectures - Model View ViewModel*, https://martinfowler.com/eaaDev/ModelViewViewModel.html.
4. The PHP Group, *PHP 8.2 Manual and PDO Extensions*, https://www.php.net/manual/en/.
5. VietQR Standard, *Hướng dẫn triển khai chuẩn thanh toán VietQR*, https://vietqr.net/.
