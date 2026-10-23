# TÀI LIỆU 01: GIỚI THIỆU TỔNG QUAN DỰ ÁN QUẢN LÝ NHÀ TRỌ

---

## 1. Tên Đề Tài
**HỆ THỐNG QUẢN LÝ NHÀ TRỌ THÔNG MINH ĐA NỀN TẢNG (BACKEND PHP MVC & REACT NATIVE EXPO MOBILE VỚI KIẾN TRÚC MVVM VÀ PUSH NOTIFICATION)**

---

## 2. Bối Cảnh và Lý Do Chọn Đề Tài
Tại các thành phố lớn và các khu công nghiệp, nhu cầu thuê nhà trọ, căn hộ dịch vụ của sinh viên và người lao động là vô cùng lớn. Tuy nhiên, phần lớn các chủ nhà trọ quy mô vừa và nhỏ (từ 5 đến 50 phòng) vẫn đang quản lý theo phương thức truyền thống:
- Ghi chép sổ sách hoặc file Excel rời rạc, dễ thất lạc dữ liệu.
- Chốt chỉ số điện nước thủ công vào cuối tháng, dễ nhầm lẫn khi tính tiền.
- Thu tiền mặt hoặc yêu cầu người thuê chuyển khoản nhưng không có đối soát tự động, dẫn đến tình trạng quên nợ, nợ đọng kéo dài.
- Khách thuê gặp sự cố (hỏng bóng đèn, tắc bồn cầu, hỏng máy lạnh) phải gọi điện hoặc nhắn tin Zalo phân tán, khiến chủ trọ bỏ sót yêu cầu sửa chữa.
- Khách thuê không nhận được thông báo kịp thời khi có hóa đơn mới hoặc khi chủ trọ đã xác nhận nhận tiền.

Xuất phát từ thực tế đó, đề tài xây dựng một hệ thống phần mềm toàn diện gồm **Cổng quản lý Web** dành cho chủ trọ và **Ứng dụng di động (Mobile App)** dành cho cả chủ trọ và khách thuê, tích hợp công nghệ **Push Notification** và kiến trúc phần mềm chuẩn mực **MVVM (Model - View - ViewModel)**.

---

## 3. Vấn Đề Cần Giải Quyết
1. **Minh bạch thông tin tài chính:** Tự động tính toán tiền phòng, tiền điện (theo số kWh), tiền nước (theo m³) và phí dịch vụ; cung cấp mã thanh toán QR động (VietQR) với cú pháp chính xác.
2. **Số hóa quy trình vận hành:** Quản lý tập trung từ phòng trọ, người thuê, hợp đồng thuê phòng, chỉ số điện nước định kỳ đến hóa đơn và thanh toán.
3. **Kênh liên lạc tức thì:** Phát sinh thông báo tự động (Push Notification) đến điện thoại của khách thuê ngay khi chủ trọ phát hành hóa đơn hoặc xác nhận thanh toán.
4. **Tiếp nhận & xử lý sự cố:** Khách thuê báo hỏng hóc kèm mô tả và mức độ ưu tiên; chủ trọ tiếp nhận và cập nhật trạng thái xử lý theo thời gian thực.
5. **Kiến trúc mã nguồn chuẩn mực:** Ứng dụng di động được tổ chức theo mô hình MVVM, phân tách rõ ràng trách nhiệm giữa View, ViewModel, Repository và Model; hỗ trợ Unit Test với Mock Repository.

---

## 4. Mục Tiêu của Dự Án
- Xây dựng Backend REST API bằng PHP thuần theo mô hình MVC, chạy trên nền tảng WAMP Server (Apache + MySQL), tối ưu hiệu năng và dễ dàng triển khai.
- Phát triển Mobile App bằng React Native và Expo (SDK 57) hỗ trợ cả Android, iOS và Web với giao diện hiện đại, trực quan, tốc độ phản hồi cao.
- Triển khai dịch vụ **Push Notification** qua Expo Push Service, kích hoạt tự động từ sự kiện nghiệp vụ thực tế phía server.
- Triển khai kiến trúc **MVVM** cho tối thiểu 3 màn hình cốt lõi: Quản lý phòng trọ, Quản lý hóa đơn và Quản lý thông báo.
- Đạt 100% tỷ lệ vượt qua (PASS) cho bộ Jest Unit Test kiểm thử logic nghiệp vụ của các ViewModel độc lập với môi trường mạng.

---

## 5. Đối Tượng Sử Dụng
1. **Chủ nhà trọ / Quản lý tòa nhà (Admin):**
   - Quản lý khu trọ, danh sách phòng và trạng thái từng phòng.
   - Quản lý danh sách khách thuê và hợp đồng thuê phòng.
   - Nhập chỉ số điện nước, lập và phát hành hóa đơn hàng tháng.
   - Ghi nhận thanh toán và theo dõi công nợ.
   - Tiếp nhận và xử lý yêu cầu sửa chữa/bảo trì.
   - Bắn thông báo và kiểm tra lịch sử thông báo.
2. **Khách thuê phòng (Tenant):**
   - Đăng ký và đăng nhập tài khoản khách thuê.
   - Xem thông tin phòng đang thuê, thời hạn hợp đồng.
   - Tra cứu chi tiết hóa đơn hàng tháng, xem chi tiết điện nước.
   - Quét mã VietQR chuyển khoản thanh toán với đúng số tiền và nội dung.
   - Gửi yêu cầu sửa chữa sự cố kèm mức độ ưu tiên.
   - Nhận thông báo đẩy (Push Notification) khi có hóa đơn mới hoặc xác nhận thu tiền.
   - Bật/tắt tùy chọn nhận thông báo và nhận lời nhắc cục bộ định kỳ.

---

## 6. Phạm Vi Đề Tài
- Tập trung vào nghiệp vụ quản lý nhà trọ, phòng trọ, chung cư mini cho thuê dài hạn.
- Không áp dụng cho nghiệp vụ đặt phòng khách sạn, homestay ngắn ngày theo giờ.
- Cơ chế gửi Push Notification sử dụng Expo Push Service (tương thích cả máy ảo, thiết bị thật qua Development Build hoặc Expo Go).
- Hệ thống hỗ trợ kết nối mạng nội bộ Wi-Fi (LAN) giữa máy chủ WAMP và thiết bị di động thật.

---

## 7. Chi Tiết Tính Năng Theo Vai Trò

### 7.1. Chức năng dành cho Chủ trọ (Admin)
- **Dashboard điều hành:** Thống kê tổng số phòng, phòng đang thuê, phòng trống, tỷ lệ lấp đầy, số tiền còn phải thu và số yêu cầu bảo trì đang chờ xử lý.
- **Quản lý phòng trọ (CRUD):** Xem danh sách phòng (lọc theo trạng thái: Còn trống, Đang thuê, Bảo trì), xem chi tiết, thêm phòng mới, cập nhật giá thuê, diện tích và xóa phòng khi không có hợp đồng hoạt động.
- **Quản lý khách thuê:** Tra cứu hồ sơ người thuê (họ tên, CCCD, số điện thoại, ngày sinh, phòng đang thuê).
- **Quản lý hợp đồng:** Lập hợp đồng mới, xem thời hạn hợp đồng, gia hạn hợp đồng, thanh lý hợp đồng.
- **Quản lý chỉ số điện nước:** Ghi nhận chỉ số điện/nước đầu kỳ và cuối kỳ; tự động tính lượng tiêu thụ và chi phí theo đơn giá cài đặt.
- **Quản lý hóa đơn:** Lập hóa đơn lẻ cho từng phòng hoặc phát hành hóa đơn tự động hàng loạt; hiển thị chi tiết tiền phòng, tiền điện, tiền nước, phí dịch vụ (rác, wifi), chi phí khác và giảm giá.
- **Quản lý thanh toán:** Ghi nhận tiền phòng khách nộp (tiền mặt / chuyển khoản), tự động cập nhật số tiền còn lại và trạng thái hóa đơn (`unpaid` -> `partially_paid` -> `paid`).
- **Xử lý sự cố & bảo trì:** Xem danh sách sự cố từ tất cả phòng trọ; cập nhật trạng thái (`pending` -> `in_progress` -> `completed` / `rejected`), ghi chú phản hồi cho khách thuê và ghi nhận chi phí sửa chữa.
- **Quản lý thông báo:** Xem lịch sử thông báo hệ thống; gửi thông báo thủ công hoặc kích hoạt tiến trình quét và gửi lời nhắc nợ định kỳ (`runReminders`).

### 7.2. Chức năng dành cho Khách thuê (Tenant)
- **Đăng ký / Đăng nhập:** Tạo tài khoản bằng số điện thoại và CCCD; tự động liên kết với hồ sơ người thuê tương ứng trong hệ thống.
- **Trang chủ khách thuê:** Hiển thị thông tin phòng hiện tại, giá thuê, hợp đồng và hóa đơn mới nhất cần thanh toán.
- **Tra cứu hóa đơn & Thanh toán VietQR:** Xem toàn bộ lịch sử hóa đơn tiền phòng; đối với hóa đơn chưa thanh toán, ứng dụng hiển thị mã QR động chuẩn VietQR chứa số tài khoản chủ trọ, số tiền chính xác và mã hóa đơn làm nội dung chuyển khoản.
- **Báo cáo sự cố bảo trì:** Gửi yêu cầu hỏng hóc với tiêu đề, mô tả chi tiết vị trí và mức độ ưu tiên (Thấp, Bình thường, Cao, Khẩn cấp 🚨); theo dõi phản hồi từ chủ trọ.
- **Trung tâm thông báo (Push & History):** Nhận thông báo đẩy từ server; xem danh sách thông báo đã nhận, số lượng chưa đọc, đánh dấu đã đọc hoặc đọc tất cả; chạm vào thông báo để chuyển ngay đến hóa đơn hoặc sự cố tương ứng.
- **Cài đặt & Nhắc nhở:** Bật/tắt tùy chọn từng loại thông báo (Hóa đơn, Thanh toán, Bảo trì, Lời nhắc); lên lịch thông báo cục bộ (Local Notification) nhắc kiểm tra hóa đơn sau khoảng thời gian cài đặt.

---

## 8. Công Nghệ Sử Dụng

| Tầng kiến trúc | Công nghệ chính | Phiên bản | Mô tả vai trò |
| :--- | :--- | :---: | :--- |
| **Mobile Frontend** | React Native / Expo | Expo SDK ~57 / RN 0.86 | Ứng dụng di động đa nền tảng (Android, iOS, Web) |
| **Kiến trúc Mobile** | MVVM Pattern | TypeScript | Tách biệt Model, Repository Interface, ViewModel và View |
| **Testing** | Jest + ts-jest | Jest 30 | Kiểm thử tự động đơn vị (Unit Test) cho ViewModels |
| **Push Notification** | `expo-notifications` | SDK 57 | Đăng ký token, nhận push notification và lên lịch cục bộ |
| **Backend Server** | PHP thuần (OOP & MVC) | PHP 8.2 (WAMP) | Xử lý nghiệp vụ, Router, Controller và REST API JSON |
| **Cơ sở dữ liệu** | MySQL / MariaDB | MySQL 8.0 | Lưu trữ 18 bảng dữ liệu quan hệ, hỗ trợ Foreign Keys & Transaction |
| **Realtime Push Hub** | Expo Push API (exp.host) | HTTP v2 | Trung tâm điều phối push notification tới thiết bị di động |
| **Web Server** | Apache WAMP Server | Port 8080 | Máy chủ web cục bộ phục vụ API và Cổng quản trị Web |

---

## 9. Kết Quả Thực Hiện Được
1. **Giữ nguyên 100% mã nguồn và cơ sở dữ liệu hiện có:** Toàn bộ dữ liệu phòng, người thuê, hợp đồng, hóa đơn sẵn có không bị gián đoạn hay mất mát.
2. **Hoàn thiện đầy đủ REST API backend:** Bổ sung các endpoint CRUD cho phòng, khách thuê, hóa đơn, thanh toán, push token, thông báo và tùy chọn thông báo.
3. **Triển khai thành công Push Notification thực tế:**
   - Đăng ký và liên kết Expo Push Token với tài khoản và thiết bị.
   - Tự động kích hoạt thông báo từ 2 sự kiện nghiệp vụ bắt buộc: **Lập hóa đơn mới** và **Xác nhận thanh toán**.
   - Hỗ trợ đầy đủ 3 trạng thái nhận tin: **Foreground**, **Background** và **Terminated**.
   - Hỗ trợ điều hướng thông minh khi chạm vào thông báo đến đúng màn hình nghiệp vụ.
   - Triển khai chức năng lập lịch thông báo cục bộ có thể hủy và tùy chỉnh.
4. **Triển khai chuẩn mực Kiến trúc MVVM:**
   - Xây dựng tầng Model và Repository Interfaces (`IRoomRepository`, `IInvoiceRepository`, `INotificationRepository`).
   - Xây dựng 2 bộ triển khai: `ApiRepository` (gọi REST API thật) và `MockRepository` (dùng cho Unit Test / Offline).
   - Xây dựng các ViewModel (`useRoomsViewModel`, `useInvoicesViewModel`, `useNotificationsViewModel`).
   - Chuyển đổi thành công 3 màn hình nghiệp vụ chính sang mô hình View độc lập: `RoomsView`, `InvoicesView`, `NotificationsView`.
5. **Đạt 100% PASS trong Kiểm thử Tự động:** Xây dựng và thực thi 12 test cases Jest độc lập, kiểm tra trọn vẹn các luồng thành công, lọc dữ liệu, xử lý lỗi API và cập nhật trạng thái.
6. **Xây dựng bộ tài liệu toàn diện trong `docs/`:** Đầy đủ 10 tài liệu kỹ thuật, hướng dẫn cài đặt, hướng dẫn sử dụng, báo cáo học thuật và sơ đồ Mermaid chi tiết.

---

## 10. Hạn Chế Còn Tồn Tại và Hướng Phát Triển
### Hạn chế hiện tại:
- Do chính sách của Google từ Android 13+ và Expo SDK 53+, remote push notification trong ứng dụng Expo Go trên một số dòng máy Android đòi hỏi cấu hình Development Build (`npx expo run:android`) để nhận thông báo từ xa khi tắt hoàn toàn ứng dụng (terminated state).
- Chưa tích hợp cổng thanh toán trực tuyến tự động IPN (VNPay/Momo webhook) mà hiện tại xác nhận thanh toán qua đối soát mã VietQR hoặc thao tác của chủ trọ.

### Hướng phát triển tiếp theo:
- Đóng gói file APK / AAB cài đặt trực tiếp qua Expo Application Services (EAS Build).
- Tích hợp cổng Webhook ngân hàng tự động (SePAY / Casso) để tự động đổi trạng thái hóa đơn sang "Đã thanh toán" ngay khi tiền về tài khoản.
- Mở rộng chức năng hợp đồng điện tử có chữ ký số (e-Sign) trên điện thoại.
