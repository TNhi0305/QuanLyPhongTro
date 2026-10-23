# KỊCH BẢN TRÌNH DIỄN (DEMO SCENARIO) ĐỒ ÁN QUẢN LÝ NHÀ TRỌ

Tài liệu này hướng dẫn chi tiết quy trình trình diễn 12 bước với 2 thiết bị độc lập (Thiết bị A - Khách thuê, Thiết bị B - Chủ trọ/Quản trị viên) để minh họa trọn vẹn nghiệp vụ quản lý nhà trọ, Push Notification và Realtime Socket.IO.

---

## Chuẩn bị môi trường trình diễn

- **Thiết bị A (Khách thuê):** Điện thoại di động mở Expo Go (hoặc giả lập Android).
- **Thiết bị B (Chủ trọ):** Trình duyệt Web Mobile / Máy tính thứ 2 / Thiết bị điện thoại khác.
- Đảm bảo WampServer đang chạy và dịch vụ Realtime `node src/server.js` đang lắng nghe tại cổng `3001`.

---

## Trình tự 12 bước Demo chi tiết

| Bước | Người thực hiện | Thao tác trên giao diện | Hiện tượng & Phản hồi thực tế |
| :---: | :--- | :--- | :--- |
| **1** | **Thiết bị A (Khách thuê)** | Chọn tab `Khách thuê` trên màn hình đăng nhập. Bấm vào tài khoản mẫu: `Nguyễn Văn An (Phòng 101)`. Bấm **Đăng nhập**. | Đăng nhập thành công, token được cấp và lưu an toàn. Giao diện hiển thị đúng thông tin: *Phòng 101 (Tầng 1)*, diện tích, giá thuê, hợp đồng và hóa đơn. Phía trên hiển thị badge: `🟢 Realtime`. |
| **2** | **Thiết bị B (Chủ trọ)** | Mở ứng dụng trên thiết bị B. Chọn tab `Chủ trọ / Admin`. Nhập tài khoản: `admin` / Mật khẩu: `NhaTro@2026`. Bấm **Đăng nhập**. | Đăng nhập thành công với vai trò Quản trị viên. Giao diện chuyển sang Bảng điều khiển quản trị: Hiển thị 6 phòng, số phòng trống, số hóa đơn nợ và sự cố chờ xử lý. Phía trên hiển thị badge: `🟢 Realtime`. |
| **3** | **Thiết bị B (Chủ trọ)** | Trên giao diện Admin, mở Tab `Hóa đơn` (hoặc qua giao diện quản trị web). Tạo hóa đơn kỳ mới cho Phòng 101 với số tiền điện nước tương ứng. | Hóa đơn được lưu vào MySQL. Backend xác định tài khoản liên kết của Nguyễn Văn An và tự động gọi `PushNotification::createAndSend`. |
| **4** | **Thiết bị A (Khách thuê)** | Quan sát màn hình của khách thuê (Thiết bị A). | Thiết bị A nhận được **Push Notification**: *Tiêu đề: Hóa đơn tháng mới. Nội dung: Bạn có hóa đơn tiền phòng mới kỳ... Vui lòng kiểm tra và thanh toán*. Đồng thời banner in-app thông báo xuất hiện. |
| **5** | **Thiết bị A (Khách thuê)** | Chạm vào thông báo vừa nhận được. | Ứng dụng tự động điều hướng trực tiếp đến tab `Hóa đơn`. Màn hình hiển thị chi tiết hóa đơn vừa lập kèm mã **VietQR** để quét mã thanh toán bằng ngân hàng. |
| **6** | **Thiết bị B (Chủ trọ)** | Tại tab `Tổng quan` của Admin, bấm nút: **⚡ Quét & Gửi nhắc hạn thanh toán (Server Push)**. Bấm lại lần thứ 2. | - Lần 1: Hệ thống hiển thị hộp thoại thông báo đã quét và gửi thành công lời nhắc nợ đến các hóa đơn sắp đến hạn.<br>- Lần 2: Hệ thống thông báo: *0 hóa đơn cần gửi* (Chứng minh cơ chế chống gửi trùng Idempotency hoạt động hoàn hảo). |
| **7** | **Thiết bị A (Khách thuê)** | Chuyển sang tab `Báo sự cố`. Nhập: *Tiêu đề: "Vòi xịt bồn cầu bị rò rỉ"*. Mức độ: *Khẩn cấp*. Mô tả: *"Nước rò rỉ liên tục dưới sàn vệ sinh"*. Bấm **Gửi yêu cầu sửa chữa**. | Dữ liệu được lưu vào MySQL. Server lập tức phát sự kiện `incident:created` sang room `admin` của Socket.IO và gửi Push Notification cho chủ trọ. |
| **8** | **Thiết bị B (Chủ trọ)** | Quan sát Thiết bị B của chủ trọ mà **không chạm vào màn hình / không cần tải lại trang**. | **Realtime tức thì:** Banner trên đầu thiết bị B hiện lên: *"🔔 Có sự cố mới từ Phòng 101: Vòi xịt bồn cầu bị rò rỉ"*. Mục *Sự cố cần tiếp nhận* tự động xuất hiện bản ghi mới ngay lập tức mà không cần bấm F5 hay kéo reload! |
| **9** | **Thiết bị B (Chủ trọ)** | Chạm vào sự cố *"Vòi xịt bồn cầu bị rò rỉ"*. Hộp thoại xử lý mở ra. Chọn trạng thái: `Đang xử lý`. Ghi chú: *"Đã hẹn thợ nước qua sửa lúc 15h00"*. Bấm **Lưu & Gửi thông báo**. | API `POST /api/maintenance/update` được kích hoạt. Backend lưu dữ liệu, đẩy push notification và phát sự kiện realtime `incident:status:changed`. |
| **10** | **Thiết bị A (Khách thuê)** | Quan sát màn hình của Khách thuê (Thiết bị A). | **Realtime tức thì:** Thẻ sự cố của Nguyễn Văn An trên Thiết bị A ngay lập tức chuyển từ màu vàng `Chờ tiếp nhận` sang màu xanh `Đang xử lý`, hiển thị phản hồi: *"💬 Phản hồi chủ trọ: Đã hẹn thợ nước qua sửa lúc 15h00"*. Đồng thời nhận push notification cập nhật tiến độ. |
| **11** | **Cả 2 Thiết bị** | Bấm sang tab **Thông báo (🔔)** ở thanh điều hướng. | Màn hình hiển thị toàn bộ lịch sử thông báo: Hóa đơn mới, Cập nhật sự cố, Lời nhắc thanh toán kèm thời gian và dấu chấm xanh chưa đọc. Chạm vào thông báo để đánh dấu đã đọc hoặc bấm **Đọc tất cả**. |
| **12** | **Kiểm tra Mất kết nối & Khôi phục** | - Tắt tạm thời dịch vụ realtime ở terminal máy tính (`Ctrl + C` tại `realtime-server`).<br>- Quan sát thanh trạng thái trên app.<br>- Bật lại dịch vụ (`npm start`). | - Khi ngắt dịch vụ: Badge lập tức đổi sang `🔴 Mất kết nối`. Ứng dụng vẫn thực hiện được các chức năng CRUD bình thường qua REST API dự phòng.<br>- Khi bật lại dịch vụ: Badge tự động khôi phục sang `🟢 Realtime` mà không cần người dùng thao tác. |

---

## Kết luận kịch bản

Kịch bản chứng minh 100% tính năng hoạt động thực tế trên dữ liệu thật, đáp ứng trọn vẹn yêu cầu kiểm thử 2 thiết bị song song, cơ chế phối hợp PHP REST API - Node.js Socket.IO và Push Notification.
