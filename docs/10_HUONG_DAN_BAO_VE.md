# TÀI LIỆU 10: HƯỚNG DẪN BẢO VỆ ĐỒ ÁN VÀ KỊCH BẢN DEMO TRƯỚC HỘI ĐỒNG

Tài liệu này được biên soạn để hỗ trợ sinh viên tự tin trình bày, bảo vệ đề tài trước Giảng viên và Hội đồng chấm đồ án, bao gồm: **Kịch bản Demo chuẩn từ 5 - 10 phút**, **Các câu hỏi phản biện thường gặp và câu trả lời mẫu xuất sắc**, cùng **Những lưu ý về các hạn chế cần trình bày trung thực**.

---

## 1. Kịch Bản Trình Bày & Demo Chuẩn (Thời Lượng 5 - 10 Phút)

### Phút 1 - 2: Giới thiệu Tổng quan Đề tài & Kiến trúc Hệ thống
1. **Chào Hội đồng và nêu lý do chọn đề tài:**
   - *"Kính thưa Quý Thầy/Cô, đề tài của em là Hệ thống Quản lý Nhà trọ Thông minh kết hợp giữa Backend PHP REST API và Mobile App React Native Expo, áp dụng kiến trúc chuẩn MVVM và hệ thống thông báo đẩy Push Notification thời gian thực."*
2. **Trình bày sơ đồ kiến trúc:**
   - Mở sơ đồ `docs/diagrams/system_architecture.mmd` hoặc slide.
   - Nêu rõ 4 tầng: Client (Mobile & Web) -> MVVM Layer trên di động -> Backend PHP MVC trên WAMP Server -> Cơ sở dữ liệu MySQL 18 bảng.

### Phút 3 - 4: Demo Luồng Khách Thuê (Tenant)
1. **Đăng nhập:**
   - Mở ứng dụng di động trên Web hoặc điện thoại. Chọn tài khoản demo `Nguyễn Văn An - Phòng 101`.
   - Giải thích: *"Ứng dụng tự động khôi phục phiên khi mở lại app nhờ cơ chế kiểm tra Token HMAC với endpoint `/api/dashboard`"*.
2. **Tra cứu Hóa đơn & Quét mã VietQR:**
   - Chuyển sang tab **Hóa đơn** (`InvoicesView`).
   - Chỉ vào hóa đơn chưa thanh toán: *"Hệ thống tự động kết xuất mã QR VietQR động. Khi khách thuê mở app ngân hàng quét mã, toàn bộ số tài khoản chủ trọ, số tiền chính xác và mã hóa đơn làm nội dung chuyển khoản được điền tự động"*.
3. **Báo sự cố bảo trì:**
   - Chuyển sang mục **Bảo trì**, gửi một sự cố mới (Ví dụ: *"Vòi nước bị rỉ"* - Mức độ Khẩn cấp 🚨).

### Phút 5 - 6: Demo Luồng Chủ Trọ (Admin) & Hai Sự Kiện Push Notification
1. **Đăng nhập Admin:** Đăng nhập tài khoản `admin`.
2. **Demo CRUD Phòng trọ (Áp dụng MVVM `RoomsView`):**
   - Chỉ vào các bộ lọc trạng thái: Còn trống, Đang thuê, Bảo trì.
   - Nhấn **"+ THÊM PHÒNG TRỌ MỚI"**, thêm một phòng ví dụ `Phòng 106`, giá `3.000.000 VNĐ`. Nhấn Lưu phòng -> Thẻ phòng xuất hiện ngay trên danh sách.
3. **Demo Sự Kiện Nghiệp Vụ 1 (Lập hóa đơn -> Gửi Push Notification):**
   - Lập hóa đơn mới cho phòng của khách thuê.
   - Giải thích: *"Sau khi lưu hóa đơn thành công vào database, backend tự động kích hoạt helper `PushNotification::createAndSend()`, gửi payload lên Expo Push Server. Điện thoại của khách thuê nhận ngay Push Notification: 'Hóa đơn tháng mới...'"*.
4. **Demo Sự Kiện Nghiệp Vụ 2 (Xác nhận thu tiền -> Gửi Push Notification):**
   - Nhấn **"GHI NHẬN THANH TOÁN"** cho hóa đơn vừa lập.
   - Ngay lập tức, server cập nhật trạng thái hóa đơn sang `paid` và gửi Push Notification: *"Chủ trọ đã xác nhận thanh toán [Số tiền] cho hóa đơn..."*.

### Phút 7 - 8: Demo Điều Hướng Thông Báo & Lập Lịch Cục Bộ
1. **Chạm vào thông báo để điều hướng:**
   - Tại màn hình điện thoại khách thuê, mở tab **Thông báo** (`NotificationsView`).
   - Chạm vào thông báo hóa đơn -> Ứng dụng hiển thị thông báo hỏi và tự động chuyển thẳng sang màn hình chi tiết Hóa đơn.
2. **Demo Thông báo cục bộ lập lịch (Local Notification):**
   - Nhấn nút **"🔔 THỬ LÊN LỊCH THÔNG BÁO CỤC BỘ (5 GIÂY)"**.
   - Khóa màn hình hoặc chuyển app. Đúng 5 giây sau chuông reo và thông báo nhắc nhở xuất hiện trên thanh thông báo điện thoại.
3. **Tùy chọn nhận thông báo:**
   - Mở modal cài đặt, bật/tắt công tắc (Switch) từng loại thông báo (Hóa đơn, Bảo trì, Thanh toán, Lời nhắc).

### Phút 9 - 10: Chứng minh Kiến Trúc MVVM & Chạy Unit Test Jest
1. **Mở terminal PowerShell:**
   - Chạy lệnh: `npm test` trong thư mục `QuanLyPhongTroMobile`.
   - Chỉ cho Hội đồng xem:
     - 3 test suites: `RoomsViewModel`, `InvoicesViewModel`, `NotificationsViewModel`.
     - 12/12 test cases PASS thành công 100%.
2. **Giải thích kiến trúc MVVM:**
   - Mở code `RoomsViewModel.ts`, `IRoomRepository.ts`, `ApiRoomRepository.ts` và `MockRoomRepository.ts`.
   - Nêu bật: *"ViewModel nhận Repository qua Dependency Injection. Khi chạy app thật thì dùng `ApiRoomRepository`, khi chạy Unit Test thì dùng `MockRoomRepository`, đảm bảo test chạy độc lập không phụ thuộc mạng hay database"*.

---

## 2. Các Câu Hỏi Phản Biện Thường Gặp và Câu Trả Lời Mẫu

### Câu 1: Tại sao em lại chọn kiến trúc MVVM cho ứng dụng di động thay vì giữ nguyên MVC?
- **Trả lời mẫu:**
  *"Dạ thưa Thầy/Cô, trong các ứng dụng di động React Native quy mô thực tế, mô hình MVC thường khiến các Component (View) trở nên quá nặng nề (Massive View) vì vừa phải render giao diện, vừa gọi `fetch()`, vừa quản lý state và xử lý lỗi.
  Em chọn MVVM vì:
  1. **Tách biệt trách nhiệm rõ ràng:** View chỉ làm nhiệm vụ hiển thị (Dumb UI). Toàn bộ state và logic nghiệp vụ được đóng gói trong ViewModel.
  2. **Khả năng kiểm thử vượt trội (Testability):** Nhờ tách ViewModel thành lớp độc lập và sử dụng Repository Pattern, em có thể viết Unit Test tự động bằng Jest với Mock Repository để kiểm thử 100% logic mà không cần render DOM giả lập hay kết nối mạng thật.
  3. **Khả năng tái sử dụng:** ViewModel có thể phục vụ cho cả giao diện Mobile và Web mà không cần viết lại logic."*

### Câu 2: Cơ chế Push Notification hoạt động như thế nào? Tại sao lại kích hoạt từ server chứ không gửi từ client?
- **Trả lời mẫu:**
  *"Dạ thưa Thầy/Cô, Push Notification bắt buộc phải kích hoạt từ Server sau khi thao tác nghiệp vụ ghi dữ liệu vào database thành công.
  Nếu kích hoạt từ client, khi mạng client bị chập chờn hoặc người dùng tắt app ngay sau khi bấm nút, thông báo sẽ bị mất hoặc gửi sai lệch.
  Cơ chế thực tế trong đồ án của em gồm 3 bước:
  1. Khi người dùng đăng nhập, client lấy `ExpoPushToken` và lưu vào bảng `push_tokens` trong MySQL.
  2. Khi Admin tạo hóa đơn hoặc xác nhận thanh toán, sau khi `commit` transaction database thành công, helper `PushNotification::createAndSend` sẽ gửi HTTP POST request chứa danh sách token và payload điều hướng sang Expo Push Service (`https://exp.host/--/api/v2/push/send`).
  3. Expo Push Service sẽ điều phối thông báo tới dịch vụ APNs của Apple hoặc FCM của Google để đánh thức thiết bị của người thuê."*

### Câu 3: Làm thế nào ứng dụng xử lý được việc người dùng nhấn vào thông báo khi ứng dụng đang đóng hoàn toàn (Terminated state)?
- **Trả lời mẫu:**
  *"Dạ thưa Thầy/Cô, khi ứng dụng bị đóng hoàn toàn (Cold start), nếu người dùng nhấn vào thông báo từ thanh Notification Tray của hệ điều hành, hàm `Notifications.getLastNotificationResponseAsync()` được gọi ngay trong vòng đời khởi động của app (`useEffect`). Hàm này đọc được `data_payload` gắn kèm trong thông báo (chứa `reference_type: 'invoice'` và `invoice_id`). Sau khi phiên đăng nhập được khôi phục, ứng dụng sẽ tự động gọi router chuyển hướng người dùng đến đúng hóa đơn cần xem."*

### Câu 4: Làm sao em chống việc gửi thông báo trùng lặp (Duplicate notifications)?
- **Trả lời mẫu:**
  *"Dạ thưa Thầy/Cô, em áp dụng 2 cơ chế chống gửi trùng:
  1. **Ở mức nghiệp vụ Hóa đơn:** Backend kiểm tra cặp khóa duy nhất `contract_id` và `billing_month`. Nếu hóa đơn của tháng đó đã tồn tại, server trả về mã lỗi 409 Conflict và từ chối tạo mới, do đó không thể phát sinh thông báo trùng.
  2. **Ở mức tiến trình quét nhắc nợ định kỳ:** Em xây dựng bảng `payment_reminder_logs` với `UNIQUE KEY (invoice_id, reminder_type, reminder_date)`. Trước khi gửi push nhắc nợ, server kiểm tra nếu hôm nay đã gửi nhắc cho hóa đơn này rồi thì sẽ bỏ qua."*

### Câu 5: Bộ Unit Test của em kiểm tra những gì? Em có mock toàn bộ logic không?
- **Trả lời mẫu:**
  *"Dạ thưa Thầy/Cô, em **không mock logic của ViewModel**. Toàn bộ mã nguồn trong các ViewModel (`RoomsViewModel`, `InvoicesViewModel`, `NotificationsViewModel`) được thực thi thật 100%.
  Em chỉ mock **Repository tầng truy cập dữ liệu** (`MockRoomRepository`, `MockInvoiceRepository`, `MockNotificationRepository`) để cung cấp dữ liệu mẫu xác định và giả lập tình huống mạng bị lỗi (`shouldFail = true`).
  Bộ test của em gồm 12 test cases kiểm tra:
  - Tải dữ liệu thành công và tính toán số liệu thống kê.
  - Xử lý ngoại lệ khi API lỗi (ViewModel cập nhật đúng thông báo lỗi và tắt cờ loading).
  - Validation dữ liệu đầu vào khi thêm phòng hoặc lập hóa đơn.
  - Cập nhật trạng thái sau các action (đánh dấu thông báo đã đọc, ghi nhận thanh toán hóa đơn)."*

---

## 3. Những Hạn Chế Cần Trình Bày Trung Thực

Khi Hội đồng hỏi về những điểm chưa hoàn hảo của đồ án, hãy trình bày một cách trung thực, khiêm tốn và chuyên nghiệp:

1. **Về Remote Push Notification trong Expo Go trên Android 13+:**
   - *"Dạ thưa Thầy/Cô, theo chính sách bảo mật mới từ Android 13 và Expo SDK 53+, remote push notification từ xa trong môi trường Expo Go trên một số dòng máy Android cần cấu hình chứng chỉ Google Firebase Cloud Messaging (FCM) riêng biệt trong Development Build (`npx expo run:android`). Tuy nhiên, để đảm bảo việc kiểm thử và demo luôn hoạt động ổn định trên mọi môi trường, em đã tích hợp thêm chức năng **Lập lịch thông báo cục bộ (Local Notification)** với đầy đủ âm thanh và banner thông báo để Thầy/Cô có thể kiểm tra thực tế ngay lập tức."*
2. **Về Thanh toán tự động:**
   - *"Hiện tại hệ thống hỗ trợ thanh toán chuyển khoản nhanh qua mã VietQR chuẩn Napas247 giúp điền sẵn số tiền và nội dung, sau đó chủ trọ bấm xác nhận thanh toán. Trong tương lai em sẽ tích hợp thêm Webhook ngân hàng tự động (SePAY / Casso) để hệ thống tự động gạch nợ ngay khi có biến động số dư."*
