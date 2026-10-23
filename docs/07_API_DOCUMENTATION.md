# TÀI LIỆU 07: ĐẶC TẢ TÀI LIỆU RESTFUL API BACKEND

Tài liệu này đặc tả toàn bộ các REST API thực tế đang hoạt động trong hệ thống, đối chiếu trực tiếp với Router (`routes/web.php`) và Controller (`app/Controllers/MobileApiController.php`).

---

## 1. Thông Tin Chung & Quy Chuẩn API

- **Base URL Mặc định (Local):** `http://localhost:8080/QuanLyPhongTro/api/`
- **Base URL Wi-Fi LAN (Điện thoại thật):** `http://<IP_MAY_TINH>:8080/QuanLyPhongTro/api/` (Ví dụ: `http://172.23.4.219:8080/QuanLyPhongTro/api/`)
- **Định dạng dữ liệu:** `Content-Type: application/json; charset=utf-8`
- **Cơ chế xác thực:** Header `Authorization: Bearer <TOKEN>` (hoặc `X-Auth-Token: <TOKEN>`)

### Quy ước Mã Trả về (HTTP Status Codes)
- `200 OK`: Yêu cầu thành công, trả về dữ liệu.
- `201 Created`: Tạo mới bản ghi thành công (Phòng trọ, Hóa đơn).
- `400 Bad Request`: Dữ liệu không hợp lệ hoặc vi phạm nghiệp vụ (Xóa phòng đang thuê).
- `401 Unauthorized`: Chưa đăng nhập, token không hợp lệ hoặc phiên đã hết hạn.
- `403 Forbidden`: Người dùng không có quyền thực hiện thao tác (Ví dụ: Khách thuê gọi API của Admin).
- `404 Not Found`: Không tìm thấy tài nguyên yêu cầu (Hóa đơn, phòng không tồn tại).
- `409 Conflict`: Dữ liệu đã tồn tại (Hóa đơn kỳ này của phòng đã lập rồi).
- `422 Unprocessable Entity`: Dữ liệu đầu vào bị thiếu hoặc sai định dạng.
- `500 Internal Server Error`: Lỗi phát sinh từ máy chủ hoặc cơ sở dữ liệu.

---

## 2. Danh Sách Endpoints Chi Tiết

### 2.1. Nhóm API Hệ Thống & Kiểm Tra
#### `GET /api/health`
- **Mục đích:** Kiểm tra trạng thái máy chủ Apache và kết nối MySQL.
- **Quyền yêu cầu:** Không yêu cầu (Public).
- **Response mẫu (200 OK):**
```json
{
  "success": true,
  "message": "API và MySQL đang hoạt động.",
  "server_time": "23/10/2026 20:14:16",
  "time": "2026-10-23T20:14:16+07:00"
}
```

#### `GET /api/demo-tenants`
- **Mục đích:** Lấy danh sách tài khoản khách thuê demo phục vụ đăng nhập nhanh khi kiểm thử.
- **Quyền yêu cầu:** Không yêu cầu (Public).
- **Response mẫu (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "fullname": "Nguyễn Văn An",
      "phone": "0912345678",
      "id_card": "079201008899",
      "username": "nguyenvanan",
      "room_name": "Phòng 101"
    }
  ]
}
```

---

### 2.2. Nhóm API Xác Thực (Authentication)

#### `POST /api/login`
- **Mục đích:** Đăng nhập tài khoản bằng số điện thoại hoặc tên đăng nhập và mật khẩu.
- **Quyền yêu cầu:** Không yêu cầu (Public).
- **Request Body mẫu:**
```json
{
  "username": "0912345678",
  "password": "NhaTro@2026"
}
```
- **Response mẫu (200 OK):**
```json
{
  "success": true,
  "message": "Đăng nhập thành công!",
  "token": "MS50ZW5hbnQuMTc5MzM2OTgwNQ.78a2fbc84992bb1...",
  "user": {
    "id": 2,
    "username": "0912345678",
    "fullname": "Nguyễn Văn An",
    "email": "nguyenvanan@gmail.com",
    "phone": "0912345678",
    "role": "tenant",
    "status": 1,
    "tenant_id": 1
  }
}
```

#### `POST /api/register`
- **Mục đích:** Đăng ký tài khoản người thuê mới.
- **Quyền yêu cầu:** Không yêu cầu (Public).
- **Request Body mẫu:**
```json
{
  "fullname": "Trần Thị Mai",
  "phone": "0987654321",
  "password": "MatKhau@2026",
  "id_card": "079302001122"
}
```
- **Response mẫu (200 OK):**
```json
{
  "success": true,
  "message": "Đăng ký tài khoản thành công!",
  "token": "...",
  "user": { ... }
}
```

#### `GET /api/dashboard`
- **Mục đích:** Lấy thông tin tổng quan, số liệu thống kê Dashboard và xác minh tính hợp lệ của phiên đăng nhập.
- **Quyền yêu cầu:** Đăng nhập (`Authorization: Bearer <TOKEN>`).
- **Response mẫu (200 OK):**
```json
{
  "success": true,
  "role": "admin",
  "user": { "id": 1, "username": "admin", "role": "admin" },
  "stats": {
    "total_rooms": 12,
    "rented_rooms": 10,
    "empty_rooms": 2,
    "total_revenue_month": 32500000,
    "pending_invoices_count": 3,
    "pending_maintenance_count": 1
  }
}
```

---

### 2.3. Nhóm API Quản Lý Phòng Trọ (Rooms CRUD)

#### `GET /api/rooms`
- **Mục đích:** Lấy danh sách phòng trọ. Admin xem toàn bộ phòng; Khách thuê xem thông tin phòng của mình.
- **Quyền yêu cầu:** Đăng nhập.
- **Response mẫu (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "room_code": "P101",
      "room_number": "Phòng 101",
      "area_name": "Khu Trọ An Bình",
      "area_size": "25.00",
      "price": "2500000.00",
      "deposit": "2500000.00",
      "status": "rented",
      "current_tenant_name": "Nguyễn Văn An",
      "current_tenant_phone": "0912345678"
    }
  ]
}
```

#### `POST /api/rooms`
- **Mục đích:** Thêm phòng trọ mới vào hệ thống.
- **Quyền yêu cầu:** Admin.
- **Request Body mẫu:**
```json
{
  "room_number": "Phòng 105",
  "room_code": "P105",
  "area_id": 1,
  "area_size": 28,
  "price": 2800000,
  "deposit": 2800000,
  "max_occupants": 3,
  "description": "Phòng có gác lửng, ban công thoáng"
}
```
- **Response mẫu (201 Created):**
```json
{
  "success": true,
  "message": "Tạo phòng trọ mới thành công!",
  "room_id": 13
}
```

#### `PUT /api/rooms/{id}` hoặc `POST /api/rooms/update/{id}`
- **Mục đích:** Cập nhật thông tin phòng trọ.
- **Quyền yêu cầu:** Admin.
- **Request Body mẫu:**
```json
{
  "room_number": "Phòng 105 (Đã sửa)",
  "price": 2900000,
  "area_size": 28,
  "status": "empty",
  "description": "Đã sơn mới toàn bộ phòng"
}
```
- **Response mẫu (200 OK):**
```json
{
  "success": true,
  "message": "Cập nhật phòng trọ thành công!"
}
```

#### `DELETE /api/rooms/{id}` hoặc `POST /api/rooms/delete/{id}`
- **Mục đích:** Xóa phòng trọ (Chỉ cho phép xóa phòng không có hợp đồng đang hoạt động).
- **Quyền yêu cầu:** Admin.
- **Response mẫu khi thành công (200 OK):**
```json
{
  "success": true,
  "message": "Đã xóa phòng trọ thành công!"
}
```
- **Response mẫu khi phòng đang thuê (400 Bad Request):**
```json
{
  "success": false,
  "message": "Không thể xóa phòng đang có hợp đồng thuê hoạt động."
}
```

---

### 2.4. Nhóm API Quản Lý Khách Thuê (Tenants)

#### `GET /api/tenants`
- **Mục đích:** Lấy danh sách toàn bộ khách thuê và phòng trọ tương ứng.
- **Quyền yêu cầu:** Admin.
- **Response mẫu (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 2,
      "fullname": "Nguyễn Văn An",
      "phone": "0912345678",
      "email": "nguyenvanan@gmail.com",
      "id_card": "079201008899",
      "contract_code": "HD-2026-001",
      "room_number": "Phòng 101"
    }
  ]
}
```

---

### 2.5. Nhóm API Hóa Đơn & Thanh Toán (Invoices & Payments)

#### `GET /api/invoices`
- **Mục đích:** Lấy danh sách hóa đơn (kèm link ảnh mã VietQR tự động cho từng hóa đơn chưa nộp).
- **Quyền yêu cầu:** Đăng nhập (Admin xem tất cả; Người thuê xem hóa đơn của mình).
- **Response mẫu (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "invoice_code": "HDN-202610-001",
      "billing_month": "2026-10",
      "room_number": "Phòng 101",
      "tenant_name": "Nguyễn Văn An",
      "room_price": "2500000.00",
      "electricity_amount": "350000.00",
      "water_amount": "100000.00",
      "service_amount": "140000.00",
      "total_amount": "3090000.00",
      "paid_amount": "0.00",
      "remaining_amount": "3090000.00",
      "due_date": "2026-10-30",
      "status": "unpaid",
      "vietqr_url": "https://img.vietqr.io/image/MB-090123456789-compact.png?amount=3090000&addInfo=HDN-202610-001&accountName=CHU%20NHA%20TRO%20AN%20BINH"
    }
  ]
}
```

#### `POST /api/invoices`
- **Mục đích:** Lập và phát hành hóa đơn mới. **Kích hoạt Push Notification Sự kiện 1**.
- **Quyền yêu cầu:** Admin.
- **Request Body mẫu:**
```json
{
  "contract_id": 1,
  "billing_month": "2026-10",
  "due_date": "2026-10-30",
  "room_price": 2500000,
  "electricity_usage": 100,
  "electricity_rate": 3500,
  "water_usage": 5,
  "water_rate": 20000,
  "service_amount": 140000,
  "other_fees": 0,
  "discount": 0
}
```
- **Response mẫu (201 Created):**
```json
{
  "success": true,
  "message": "Lập và phát hành hóa đơn thành công!",
  "invoice_id": 15,
  "invoice_code": "HDN-202610-002"
}
```

#### `POST /api/payments`
- **Mục đích:** Ghi nhận thanh toán tiền phòng. **Kích hoạt Push Notification Sự kiện 2**.
- **Quyền yêu cầu:** Admin.
- **Request Body mẫu:**
```json
{
  "invoice_id": 15,
  "amount": 3090000,
  "payment_method": "bank_transfer",
  "transaction_ref": "FT2610238899",
  "notes": "Khách chuyển khoản MB Bank"
}
```
- **Response mẫu (200 OK):**
```json
{
  "success": true,
  "message": "Đã ghi nhận thanh toán thành công!",
  "new_status": "paid",
  "remaining_amount": 0
}
```

---

### 2.6. Nhóm API Push Notification & Lịch Sử Thông Báo

#### `POST /api/push-token` / `POST /api/push-tokens`
- **Mục đích:** Đăng ký hoặc làm mới Expo Push Token của thiết bị.
- **Quyền yêu cầu:** Đăng nhập.
- **Request Body mẫu:**
```json
{
  "token": "ExponentPushToken[AbCdEf1234567890]",
  "device_type": "android",
  "device_id": "pixel_7_pro"
}
```
- **Response mẫu (200 OK):**
```json
{
  "success": true,
  "message": "Đã đăng ký push token thành công cho tài khoản #2"
}
```

#### `POST /api/push-token/remove` hoặc `DELETE /api/push-token`
- **Mục đích:** Hủy liên kết push token khi người dùng đăng xuất.
- **Request Body:** `{ "token": "ExponentPushToken[...]" }`
- **Response mẫu (200 OK):**
```json
{
  "success": true,
  "message": "Đã hủy đăng ký token thiết bị."
}
```

#### `GET /api/notifications`
- **Mục đích:** Lấy 50 thông báo gần nhất của người dùng.
- **Response mẫu (200 OK):**
```json
{
  "success": true,
  "unread_count": 1,
  "data": [
    {
      "id": 8,
      "title": "Hóa đơn tháng mới",
      "content": "Bạn có hóa đơn tiền phòng mới kỳ 2026-10 với số tiền 3.090.000 đ. Hạn thanh toán: 30/10/2026.",
      "type": "payment",
      "is_read": 0,
      "reference_type": "invoice",
      "reference_id": 15,
      "data": {
        "invoice_id": 15,
        "type": "new_invoice"
      },
      "created_at": "2026-10-23 20:25:00"
    }
  ]
}
```

#### `GET /api/notifications/unread-count`
- **Mục đích:** Lấy nhanh số lượng thông báo chưa đọc phục vụ hiển thị badge.
- **Response mẫu (200 OK):**
```json
{
  "success": true,
  "unread_count": 1
}
```

#### `POST /api/notifications/read/{id}` hoặc `PATCH /api/notifications/{id}/read`
- **Mục đích:** Đánh dấu một thông báo cụ thể là đã đọc.
- **Response mẫu (200 OK):**
```json
{
  "success": true,
  "message": "Đã đánh dấu thông báo là đã đọc."
}
```

#### `POST /api/notifications/read-all` hoặc `PATCH /api/notifications/read-all`
- **Mục đích:** Đánh dấu toàn bộ thông báo của người dùng là đã đọc.
- **Response mẫu (200 OK):**
```json
{
  "success": true,
  "message": "Đã đánh dấu tất cả thông báo là đã đọc."
}
```

#### `GET /api/notifications/preferences` & `POST /api/notifications/preferences`
- **Mục đích:** Lấy và cập nhật tùy chọn bật/tắt từng loại thông báo.
- **POST Request Body mẫu:**
```json
{
  "category": "invoice",
  "is_enabled": 1
}
```
- **Response mẫu (200 OK):**
```json
{
  "success": true,
  "message": "Đã lưu tùy chọn thông báo."
}
```
