# TÀI LIỆU 06: HỆ THỐNG PUSH NOTIFICATION THỜI GIAN THỰC

Tài liệu này giải thích toàn diện cơ chế hoạt động, kiến trúc, API và quy trình xử lý của hệ thống **Push Notification** trong dự án Quản lý nhà trọ, kết hợp giữa `expo-notifications`, Expo Push Service và Backend PHP/MySQL.

---

## 1. Cơ Chế Hoạt Động Tổng Thể

Hệ thống thông báo đẩy được xây dựng theo mô hình **Server-Triggered Push Notifications**:

```text
[Khách thuê đăng nhập Mobile App]
             │
             ▼
Xin quyền & lấy Expo Push Token (ExponentPushToken[xxxxxxxxxxxx])
             │
             ▼ Gửi qua POST /api/push-token
[Backend PHP API] ──> Lưu vào bảng `push_tokens` trong MySQL
             │
             │ (Phát sinh sự kiện nghiệp vụ: Lập hóa đơn / Xác nhận thanh toán)
             ▼
Helper PushNotification::createAndSend()
  ├── 1. Lưu lịch sử vào bảng `notifications` trong MySQL
  └── 2. Gửi HTTP POST JSON payload đến Expo Push Service (https://exp.host/--/api/v2/push/send)
             │
             ▼
[Expo Push Server] ──> Điều phối qua Apple APNs (iOS) / Google FCM (Android)
             │
             ▼
[Thiết bị Di động Khách thuê]
  ├── Foreground: Hiện banner thông báo nổi trong ứng dụng
  ├── Background: Hiện trên thanh thông báo hệ thống (Notification Tray)
  └── Terminated: Nhấn thông báo -> Tự động khởi động app và điều hướng đến Hóa Đơn!
```

---

## 2. Quy Trình Xin Quyền và Đăng Ký Token

Mã nguồn triển khai tại: `QuanLyPhongTroMobile/src/services/notificationService.ts`

### Bước 1: Thiết lập Channel thông báo trên Android
Android 8.0+ yêu cầu thiết lập Notification Channel để cấu hình mức độ ưu tiên và âm thanh:
```typescript
await Notifications.setNotificationChannelAsync('default', {
  name: 'Thông báo Quản lý phòng trọ',
  importance: Notifications.AndroidImportance.MAX,
  vibrationPattern: [0, 250, 250, 250],
  lightColor: '#208AEF',
  sound: 'default',
  enableLights: true,
  enableVibrate: true
});
```

### Bước 2: Kiểm tra quyền và xin quyền người dùng
```typescript
const { status: existingStatus } = await Notifications.getPermissionsAsync();
let finalStatus = existingStatus;

if (existingStatus !== 'granted') {
  const { status } = await Notifications.requestPermissionsAsync();
  finalStatus = status;
}

if (finalStatus !== 'granted') {
  console.log('Người dùng từ chối cấp quyền thông báo');
  return null;
}
```

### Bước 3: Lấy Expo Push Token và gửi lên Backend
```typescript
const tokenResponse = await Notifications.getExpoPushTokenAsync();
const token = tokenResponse.data; // Định dạng: ExponentPushToken[xxxxxxx]

// Gửi lên backend PHP để liên kết với tài khoản
await apiClient('push-token', 'POST', { token, device_type: Platform.OS });
```

---

## 3. Cấu Trúc Bảng Dữ Liệu Lưu Trữ (Database Schema)

### 3.1. Bảng `push_tokens` (Lưu token thiết bị)
```sql
CREATE TABLE `push_tokens` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `token` VARCHAR(255) NOT NULL UNIQUE,
    `device_type` VARCHAR(50) DEFAULT 'mobile',
    `device_id` VARCHAR(100) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_push_user` (`user_id`),
    CONSTRAINT `fk_push_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3.2. Bảng `notifications` (Lưu lịch sử thông báo)
```sql
CREATE TABLE `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `type` VARCHAR(50) NOT NULL DEFAULT 'general',
    `target_tenant_id` INT DEFAULT NULL,
    `target_user_id` INT DEFAULT NULL,
    `created_by` INT DEFAULT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `reference_type` VARCHAR(50) DEFAULT NULL,
    `reference_id` INT DEFAULT NULL,
    `data_payload` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3.3. Bảng `notification_preferences` (Tùy chọn nhận thông báo)
```sql
CREATE TABLE `notification_preferences` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_user_category` (`user_id`, `category`),
    CONSTRAINT `fk_notif_pref_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 4. Hai Sự Kiện Nghiệp Vụ Bắt Buộc Kích Hoạt Push Từ Server

Toàn bộ thông báo được kích hoạt từ **Backend Service sau khi thao tác Database thành công**:

### Sự kiện 1: Phát hành Hóa đơn tiền phòng mới
- **Thời điểm:** Khi chủ trọ lập hóa đơn cho phòng trọ qua API `POST /api/invoices` hoặc qua Cổng Web `InvoiceController::store`.
- **Mã nguồn kích hoạt (`app/Controllers/MobileApiController.php` dòng 780):**
```php
$dueDateFormatted = date('d/m/Y', strtotime($dueDate));
PushNotification::createAndSend(
    'Hóa đơn tháng mới',
    "Bạn có hóa đơn tiền phòng mới kỳ {$billingMonth} với tổng số tiền " . number_format($totalAmount, 0, ',', '.') . " đ. Hạn thanh toán: {$dueDateFormatted}.",
    'payment',
    (int)$contract['tenant_id'],
    null,
    'invoice',
    $invId,
    [
        'invoice_id'    => $invId,
        'invoice_code'  => $invCode,
        'total_amount'  => $totalAmount,
        'due_date'      => $dueDate,
        'type'          => 'new_invoice'
    ],
    $u['id']
);
```

### Sự kiện 2: Xác nhận Thanh toán tiền phòng thành công
- **Thời điểm:** Khi chủ trọ ghi nhận tiền phòng của khách thuê qua API `POST /api/payments` hoặc qua Cổng Web `PaymentController::store`.
- **Mã nguồn kích hoạt (`app/Controllers/MobileApiController.php` dòng 864):**
```php
$paidFormatted = number_format($amount, 0, ',', '.') . ' đ';
PushNotification::createAndSend(
    'Xác nhận thanh toán tiền phòng',
    "Chủ trọ đã xác nhận thanh toán {$paidFormatted} cho hóa đơn {$invoice['invoice_code']}. Trạng thái: " . ($newStatus === 'paid' ? 'Đã thanh toán đủ.' : 'Thanh toán một phần.'),
    'payment',
    (int)$invoice['tenant_id'],
    null,
    'invoice',
    $invoiceId,
    [
        'invoice_id'  => $invoiceId,
        'paid_amount' => $amount,
        'status'      => $newStatus,
        'type'        => 'payment_received'
    ],
    $u['id']
);
```

---

## 5. Xử Lý Ba Trạng Thái Nhận Thông Báo & Điều Hướng

### 5.1. Trạng thái Ứng dụng Đang Mở (Foreground)
- Khi đang mở ứng dụng, listener `Notifications.addNotificationReceivedListener` sẽ nhận được thông báo.
- Ứng dụng hiển thị banner thông báo nổi phía trên màn hình kèm âm thanh mà không làm gián đoạn thao tác người dùng.

### 5.2. Trạng thái Ứng dụng Chạy Nền (Background)
- Hệ điều hành Android và iOS tự động hiển thị banner trên thanh thông báo.
- Khi người dùng chạm vào thông báo, listener `Notifications.addNotificationResponseReceivedListener` kích hoạt, đọc dữ liệu `payload.notification.request.content.data`.

### 5.3. Trạng thái Ứng dụng Đã Đóng Hoàn Toàn (Terminated / Cold Start)
- Khi người dùng nhấn vào thông báo từ khay hệ thống trong lúc ứng dụng chưa mở, hàm `Notifications.getLastNotificationResponseAsync()` sẽ được gọi ngay khi ứng dụng vừa khởi động:
```typescript
export async function checkInitialNotification(): Promise<NotificationPayload | null> {
  try {
    const response = await Notifications.getLastNotificationResponseAsync();
    if (response) {
      return response.notification.request.content.data as NotificationPayload;
    }
  } catch {}
  return null;
}
```

### 5.4. Cơ chế Điều hướng thông minh từ Payload
```typescript
const handleNotificationNavigation = (payload: NotificationPayload) => {
  if (payload.reference_type === 'invoice' || payload.type === 'new_invoice' || payload.type === 'payment_received') {
    // Tự động mở tab Hóa đơn
    openPage('invoices');
  } else if (payload.reference_type === 'maintenance') {
    // Tự động mở tab Bảo trì
    openPage('maintenance');
  }
};
```

---

## 6. Lập Lịch Thông Báo Cục Bộ (Local Scheduled Notifications)

Hệ thống cung cấp tính năng nhắc nhở độc lập không cần Internet:
```typescript
export async function scheduleLocalReminder(params: {
  title: string;
  body: string;
  seconds?: number;
  data?: NotificationPayload;
}): Promise<string | null> {
  const id = await Notifications.scheduleNotificationAsync({
    content: {
      title: params.title,
      body: params.body,
      data: params.data || {},
      sound: 'default'
    },
    trigger: {
      type: Notifications.SchedulableTriggerInputTypes.TIME_INTERVAL,
      seconds: params.seconds || 5,
      repeats: false
    }
  });
  return id;
}
```
Khách thuê có thể bấm nút **"🔔 THỬ LÊN LỊCH THÔNG BÁO CỤC BỘ (5 GIÂY)"** trong tab Thông báo để trải nghiệm thông báo xuất hiện sau 5 giây ngay cả khi màn hình đang khóa.

---

## 7. Các API Backend Phục Vụ Thông Báo

| Method | Endpoint | Quyền hạn | Mô tả |
| :--- | :--- | :---: | :--- |
| `POST` | `/api/push-token` / `/api/push-tokens` | Đăng nhập | Đăng ký hoặc cập nhật Expo Push Token |
| `POST` / `DELETE` | `/api/push-token/remove` | Đăng nhập | Hủy đăng ký token khi đăng xuất |
| `GET` | `/api/notifications` | Đăng nhập | Lấy danh sách lịch sử thông báo của tài khoản |
| `GET` | `/api/notifications/unread-count` | Đăng nhập | Lấy số lượng thông báo chưa đọc |
| `POST` / `PATCH` | `/api/notifications/read/{id}` | Đăng nhập | Đánh dấu 1 thông báo là đã đọc |
| `POST` / `PATCH` | `/api/notifications/read-all` | Đăng nhập | Đánh dấu tất cả thông báo là đã đọc |
| `GET` | `/api/notifications/preferences` | Đăng nhập | Lấy tùy chọn bật/tắt nhận thông báo |
| `POST` | `/api/notifications/preferences` | Đăng nhập | Cập nhật tùy chọn nhận thông báo |
| `POST` | `/api/reminders/run` | Admin | Kích hoạt quét và gửi nhắc nợ định kỳ |

---

## 8. Hướng Dẫn Kiểm Thử Push Notification Thực Tế

1. **Chuẩn bị:** Máy tính chạy WAMP Server và ứng dụng Expo. Điện thoại thật kết nối cùng mạng Wi-Fi.
2. **Đăng nhập Khách thuê:** Trên điện thoại, chọn khách thuê demo `Nguyễn Văn An`, nhấn Đăng nhập và chọn **"Cho phép"** khi được hỏi quyền thông báo.
3. **Kiểm tra Token trên Backend:** Mở trình duyệt máy tính, kiểm tra bảng `push_tokens` trong MySQL, sẽ thấy token thiết bị `ExponentPushToken[...]` được lưu kèm `user_id = 2`.
4. **Kích hoạt Sự kiện 1 (Lập hóa đơn):** Đăng nhập tài khoản Admin trên web hoặc mobile, tạo hóa đơn mới cho Phòng 101.
5. **Xác nhận kết quả:** Điện thoại khách thuê phát chuông và hiển thị banner thông báo *"Hóa đơn tháng mới: Bạn có hóa đơn tiền phòng mới kỳ..."*.
6. **Kích hoạt Sự kiện 2 (Xác nhận thu tiền):** Tại tài khoản Admin, ghi nhận thanh toán cho hóa đơn vừa tạo. Điện thoại khách thuê nhận ngay thông báo *"Xác nhận thanh toán tiền phòng: Chủ trọ đã xác nhận thanh toán..."*.
7. **Kiểm tra Điều hướng:** Chạm vào thông báo trên điện thoại -> Ứng dụng tự động mở và chuyển thẳng đến tab Hóa đơn.
