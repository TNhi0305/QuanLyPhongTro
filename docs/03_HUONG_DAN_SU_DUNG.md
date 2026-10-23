# TÀI LIỆU 03: HƯỚNG DẪN SỬ DỤNG HỆ THỐNG QUẢN LÝ NHÀ TRỌ

Tài liệu này hướng dẫn chi tiết quy trình sử dụng các tính năng thực tế của hệ thống dành cho hai nhóm đối tượng: **Chủ nhà trọ (Admin)** và **Khách thuê phòng (Tenant)**.

---

## PHẦN I: DÀNH CHO CHỦ NHÀ TRỌ (ADMIN)

### 1. Đăng nhập tài khoản Quản trị
- **Tài khoản mặc định:** `admin` (hoặc số điện thoại quản trị viên)
- **Mật khẩu mặc định:** `Admin@2026` (hoặc `123456`)
- **Quy trình:**
  1. Mở ứng dụng di động, chọn tab vai trò **"Chủ trọ / Admin"**.
  2. Nhập tên tài khoản và mật khẩu.
  3. Nhấn nút **"ĐĂNG NHẬP"**.
  4. Hệ thống kiểm tra quyền trên server, cấp phát mã bảo mật HMAC-SHA256 Token và chuyển thẳng vào giao diện Điều hành Quản trị viên.

### 2. Theo dõi Bảng điều khiển (Dashboard Điều hành)
Sau khi đăng nhập thành công, màn hình Dashboard cung cấp cái nhìn toàn diện:
- **Tổng số phòng:** Số lượng phòng hiện có trong hệ thống.
- **Phòng đang thuê:** Số phòng đã ký hợp đồng hoạt động (màu xanh lá cây).
- **Phòng còn trống:** Số phòng sẵn sàng đón khách thuê mới (màu xanh dương).
- **Tiền còn phải thu:** Tổng công nợ tiền phòng chưa thu trong tháng hiện tại.
- **Sự cố chờ xử lý:** Số lượng yêu cầu báo hỏng hóc từ khách thuê chưa được tiếp nhận.

### 3. Quản lý Phòng trọ (Áp dụng Kiến trúc MVVM `RoomsView`)
1. Chuyển sang mục **"Phòng trọ"** trên thanh điều hướng nội bộ.
2. **Xem danh sách & Lọc trạng thái:**
   - Sử dụng các chip lọc nhanh: `Tất cả`, `Còn trống`, `Đang thuê`, `Bảo trì`.
   - Mỗi thẻ phòng hiển thị: Tên phòng, diện tích, giá thuê hàng tháng, trạng thái và thông tin khách thuê hiện tại.
3. **Thêm phòng trọ mới:**
   - Nhấn nút **"+ THÊM PHÒNG TRỌ MỚI"**.
   - Nhập số phòng (ví dụ: `Phòng 105`), giá thuê (ví dụ: `2.500.000 VNĐ`), diện tích (ví dụ: `25 m²`) và tiện ích mô tả.
   - Nhấn **"LƯU PHÒNG"**. Dữ liệu được gửi qua REST API và danh sách tự động làm mới tức thì.
4. **Xóa phòng trọ:**
   - Đối với các phòng chưa có hợp đồng thuê hoạt động, chủ trọ có thể nhấn **"Xóa phòng"**.
   - Nếu phòng đang có người thuê, hệ thống sẽ từ chối để bảo vệ tính toàn vẹn dữ liệu.

### 4. Quản lý Hóa đơn & Thu tiền (Áp dụng Kiến trúc MVVM `InvoicesView`)
1. Chuyển sang mục **"Hóa đơn"**.
2. **Tra cứu & Lọc hóa đơn:**
   - Thống kê tóm tắt: `Tổng tiền đã thu`, `Tổng tiền chưa thu`, `Số hóa đơn chưa nộp`.
   - Lọc theo trạng thái: `Tất cả`, `Chưa nộp`, `Thanh toán một phần`, `Đã nộp đủ`.
3. **Lập và phát hành hóa đơn mới (Kích hoạt Sự kiện Push Notification 1):**
   - Chủ trọ lập hóa đơn cho phòng với các mục: Tiền thuê phòng, tiền điện tiêu thụ (kWh), tiền nước (m³), phí dịch vụ chung (rác, wifi).
   - Ngay sau khi lưu thành công vào MySQL, hệ thống tự động:
     - Ghi nhận thông báo vào bảng `notifications`.
     - Điều phối gửi **Remote Push Notification** đến điện thoại của khách thuê tương ứng: *"Bạn có hóa đơn tiền phòng mới kỳ 2026-10 với số tiền 3.090.000 đ..."*.
4. **Ghi nhận thanh toán (Kích hoạt Sự kiện Push Notification 2):**
   - Khi nhận được tiền mặt hoặc khách chuyển khoản, nhấn **"GHI NHẬN THANH TOÁN"** tại thẻ hóa đơn.
   - Chọn phương thức (`Tiền mặt` hoặc `Chuyển khoản`), nhập số tiền thực tế và mã giao dịch.
   - Nhấn **"XÁC NHẬN THU TIỀN"**.
   - Hệ thống tự động tính lại số tiền còn lại và cập nhật trạng thái hóa đơn sang `paid` hoặc `partially_paid`.
   - Đồng thời, phát sinh Push Notification tới điện thoại khách thuê: *"Chủ trọ đã xác nhận thanh toán 3.090.000 đ cho hóa đơn HDN-202610-001. Trạng thái: Đã thanh toán đủ."*.

### 5. Xử lý Yêu cầu Báo hỏng / Sự cố Bảo trì
1. Chuyển sang mục **"Bảo trì"**.
2. Danh sách hiển thị toàn bộ sự cố từ các phòng trọ với mã phòng, tên người báo, số điện thoại, tiêu đề sự cố và mức độ khẩn cấp.
3. Chạm vào sự cố để mở bảng xử lý:
   - Chuyển trạng thái: `Đang xử lý` -> `Đã hoàn thành` / `Từ chối`.
   - Nhập ghi chú phản hồi cho khách thuê (ví dụ: *"Thợ điện nước sẽ đến kiểm tra vào lúc 14h chiều nay"*).
   - Nhập chi phí sửa chữa (nếu có) để hạch toán vào chi phí quản lý nhà trọ.
   - Nhấn **"CẬP NHẬT TRẠNG THÁI"**. Khách thuê sẽ nhận được thông báo phản hồi ngay lập tức.

### 6. Quản lý Thông báo & Chạy Lời nhắc Nợ Định kỳ
- Chuyển sang tab **"Thông báo"** trên thanh TabBar phía dưới.
- Xem danh sách thông báo hệ thống, số lượng thông báo chưa đọc.
- Nhấn **"Đọc tất cả"** để đánh dấu đã đọc.
- Nhấn **"🔔 THỬ LÊN LỊCH THÔNG BÁO CỤC BỘ (5 GIÂY)"** để kiểm tra tính năng thông báo theo lịch.

---

## PHẦN II: DÀNH CHO KHÁCH THUÊ PHÒNG (TENANT)

### 1. Đăng ký Tài khoản mới
1. Mở ứng dụng, tại màn hình đăng nhập nhấn vào liên kết **"Chưa có tài khoản? Đăng ký ngay"**.
2. Điền đầy đủ thông tin:
   - Họ và tên (ví dụ: `Nguyễn Văn An`)
   - Số điện thoại (ví dụ: `0912345678`)
   - Số Căn cước công dân (CCCD / CMND)
   - Mật khẩu đăng nhập
3. Nhấn **"ĐĂNG KÝ TÀI KHOẢN"**.
4. Hệ thống sẽ tự động liên kết tài khoản vừa tạo với hồ sơ người thuê và phòng trọ tương ứng trong hệ thống.

### 2. Đăng nhập nhanh bằng Tài khoản Khách thuê Demo
Để tiện cho việc chấm thi và kiểm tra, ứng dụng cung cấp sẵn danh sách khách thuê demo:
- Nhấn vào một khách thuê trong danh sách demo (ví dụ: `Nguyễn Văn An - Phòng 101`).
- Thông tin đăng nhập sẽ tự động được điền.
- Nhấn **"ĐĂNG NHẬP"**.

### 3. Xem Thông tin Phòng trọ & Hợp đồng
- Tại màn hình Trang chủ của khách thuê, thẻ phòng hiển thị:
  - Tên phòng (ví dụ: `Phòng 101`), khu trọ, diện tích.
  - Giá thuê hàng tháng và tiền cọc đã đóng.
  - Mã hợp đồng thuê và ngày hết hạn hợp đồng.

### 4. Tra cứu Hóa đơn & Thanh toán qua Mã VietQR
1. Chuyển sang mục **"Hóa đơn"**.
2. Khách thuê xem được chi tiết từng khoản tiền trong tháng:
   - Tiền phòng cố định theo hợp đồng.
   - Tiền điện: Số lượng kWh tiêu thụ x Đơn giá điện (3.500 đ/kWh).
   - Tiền nước: Số m³ tiêu thụ x Đơn giá nước (20.000 đ/m³).
   - Tiền dịch vụ chung (wifi, rác sinh hoạt).
   - Tổng cộng, số tiền đã đóng và số tiền còn lại phải nộp.
3. **Thanh toán chuyển khoản VietQR tự động:**
   - Nếu hóa đơn chưa thanh toán đủ, ứng dụng tự động kết xuất hình ảnh **Mã QR VietQR**.
   - Khách thuê mở ứng dụng ngân hàng bất kỳ (Vietcombank, MB Bank, Techcombank, Momo, v.v.), quét mã QR trên màn hình.
   - Ứng dụng ngân hàng tự động điền đúng: **Số tài khoản chủ trọ**, **Số tiền chính xác** và **Nội dung chuyển khoản là mã hóa đơn**. Người thuê chỉ cần bấm xác nhận chuyển tiền.

### 5. Gửi Yêu cầu Báo sự cố / Hỏng hóc Thiết bị
1. Chuyển sang mục **"Báo sự cố"**.
2. Điền thông tin vào form:
   - Tiêu đề sự cố: Ví dụ *"Vòi xịt vệ sinh bị rỉ nước liên tục"*.
   - Mức độ ưu tiên: Chọn một trong 4 mức: `Thấp`, `Bình thường`, `Cao`, `Khẩn cấp 🚨`.
   - Mô tả chi tiết: Ví dụ *"Nước rỉ ở chân van vòi xịt trong nhà vệ sinh, chảy lênh láng sàn nhà"*.
3. Nhấn **"GỬI YÊU CẦU SỬA CHỮA"**.
4. Yêu cầu được gửi lên server và gửi tín hiệu cảnh báo tới chủ trọ.
5. Khách thuê có thể theo dõi tiến độ xử lý và đọc ghi chú phản hồi của chủ trọ ngay tại lịch sử sự cố phía dưới.

### 6. Nhận & Quản lý Thông báo (Push Notification)
1. Chuyển sang tab **"Thông báo"** trên thanh dưới cùng:
   - Danh sách thông báo hiển thị theo thời gian mới nhất.
   - Các thông báo chưa đọc có chấm tròn xanh nổi bật.
2. **Tương tác thông báo:**
   - Chạm vào thông báo hóa đơn -> Ứng dụng hỏi: *"Bạn có muốn chuyển sang mục Hóa đơn để kiểm tra không?"* -> Nhấn *"Xem hóa đơn"* để chuyển tab ngay lập tức.
   - Nhấn *"Đánh dấu đã đọc"* hoặc *"Đọc tất cả"* để dọn sạch thông báo.
3. **Tùy chỉnh nhận thông báo:**
   - Nhấn nút **"⚙️ Cài đặt"** ở góc phải màn hình.
   - Có thể chủ động bật/tắt nhận thông báo cho từng danh mục:
     - *Hóa đơn & Tiền phòng*
     - *Xác nhận thanh toán*
     - *Sự cố & Bảo trì*
     - *Nhắc hạn định kỳ*
4. **Lên lịch thông báo nhắc nhở cục bộ:**
   - Nhấn **"🔔 THỬ LÊN LỊCH THÔNG BÁO CỤC BỘ (5 GIÂY)"**.
   - Khóa màn hình điện thoại hoặc chuyển sang ứng dụng khác; đúng 5 giây sau chuông và banner thông báo nhắc nhở sẽ xuất hiện trên màn hình điện thoại!
