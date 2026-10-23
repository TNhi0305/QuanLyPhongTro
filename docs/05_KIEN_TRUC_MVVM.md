# TÀI LIỆU 05: PHÂN TÍCH CHUYÊN SÂU KIẾN TRÚC PHẦN MỀM MVVM (MODEL - VIEW - VIEWMODEL)

Tài liệu này giải thích chi tiết kiến trúc phần mềm **MVVM** đã được triển khai trong ứng dụng di động React Native Expo của dự án Quản lý nhà trọ, bao gồm cấu trúc thư mục, trách nhiệm của từng tầng, Repository Pattern, cơ chế Mocking và Unit Testing bằng Jest.

---

## 1. Tổng Quan về Kiến Trúc MVVM

Kiến trúc **Model - View - ViewModel (MVVM)** là một mẫu kiến trúc phần mềm được thiết kế nhằm phân tách triệt để giữa giao diện người dùng (User Interface) và logic nghiệp vụ / dữ liệu (Business Logic & State).

```text
+-------------------+        Observes & Dispatches         +-----------------------+
|       VIEW        | <==================================> |       VIEWMODEL       |
|  (RoomsView.tsx)  |                                      |  (RoomsViewModel.ts)  |
+-------------------+                                      +-----------------------+
                                                                       |
                                                                       | Calls Interface
                                                                       v
                                                           +-----------------------+
                                                           | REPOSITORY INTERFACE  |
                                                           |  (IRoomRepository.ts) |
                                                           +-----------------------+
                                                                  /         \
                                                      Implements /           \ Implements (Test)
                                                                v             v
                                                     +--------------+     +--------------------+
                                                     | ApiRoomRepo  |     | MockRoomRepository |
                                                     | (Real API)   |     | (Jest Unit Test)   |
                                                     +--------------+     +--------------------+
```

---

## 2. Cấu Trúc Thư Mục Thực Tế trong Dự Án

Toàn bộ mã nguồn MVVM được tổ chức trong thư mục `QuanLyPhongTroMobile/src/`:

```text
QuanLyPhongTroMobile/src/
├── models/
│   └── types.ts                    # TẦNG MODEL: Định nghĩa các kiểu dữ liệu thực thể (Room, Invoice, NotificationItem, User, ApiResponse)
├── repositories/                   # TẦNG DATA ACCESS / REPOSITORY:
│   ├── IRoomRepository.ts          # Interface hợp đồng truy cập dữ liệu Phòng trọ
│   ├── IInvoiceRepository.ts        # Interface hợp đồng truy cập dữ liệu Hóa đơn
│   ├── INotificationRepository.ts  # Interface hợp đồng truy cập dữ liệu Thông báo
│   ├── IAuthRepository.ts          # Interface hợp đồng truy cập Xác thực & Phiên
│   ├── ApiRoomRepository.ts        # Triển khai gọi REST API PHP thật
│   ├── ApiInvoiceRepository.ts     # Triển khai gọi REST API PHP thật
│   ├── ApiNotificationRepository.ts# Triển khai gọi REST API PHP thật
│   ├── ApiAuthRepository.ts        # Triển khai gọi REST API PHP thật
│   ├── MockRoomRepository.ts       # Triển khai Giả lập dùng cho Unit Test / Offline
│   ├── MockInvoiceRepository.ts    # Triển khai Giả lập dùng cho Unit Test / Offline
│   ├── MockNotificationRepository.ts
│   └── MockAuthRepository.ts
├── viewmodels/                     # TẦNG VIEWMODEL (State & Logic nghiệp vụ):
│   ├── useRoomsViewModel.ts        # Quản lý danh sách phòng, lọc trạng thái, thêm, xóa phòng
│   ├── useInvoicesViewModel.ts     # Quản lý hóa đơn, tổng hợp tài chính, ghi nhận thu tiền
│   ├── useNotificationsViewModel.ts# Quản lý thông báo, đếm chưa đọc, đánh dấu đã đọc, tùy chọn
│   └── useAuthViewModel.ts         # Quản lý phiên đăng nhập, khôi phục phiên tự động
└── views/                          # TẦNG VIEW (Thuần hiển thị giao diện UI):
    ├── RoomsView.tsx               # Màn hình quản lý phòng trọ (FlatList, Badges, Modal)
    ├── InvoicesView.tsx            # Màn hình quản lý hóa đơn (Chi tiết phí, QR VietQR, Modal thu tiền)
    └── NotificationsView.tsx       # Màn hình trung tâm thông báo (Lọc chưa đọc, Cài đặt tùy chọn)
```

---

## 3. Vai Trò và Trách Nhiệm Chi Tiết của Từng Tầng

### 3.1. Tầng Model (`src/models/types.ts`)
- Đại diện cho các đối tượng nghiệp vụ cốt lõi trong hệ thống: `Room`, `Invoice`, `NotificationItem`, `User`, `MaintenanceRequest`.
- Không chứa logic hiển thị giao diện.
- Đảm bảo tính nhất quán về kiểu dữ liệu giữa Backend PHP REST API và Frontend React Native.

### 3.2. Tầng Repository Interface & Implementations (`src/repositories/`)
- **Repository Interface (Hợp đồng dữ liệu):** Định nghĩa các phương thức cần thiết để lấy hoặc cập nhật dữ liệu mà không quan tâm dữ liệu đến từ đâu (API, SQLite, Firebase hay Mock).
- **ApiRepository (Triển khai thật):** Sử dụng `apiClient` gọi REST API backend PHP (`http://localhost:8080/QuanLyPhongTro/api/...`), chuyển đổi kết quả JSON thành Entity Model.
- **MockRepository (Triển khai giả lập):** Chứa sẵn tập dữ liệu mẫu trong bộ nhớ (In-memory arrays), cho phép cấu hình cờ giả lập lỗi `shouldFail = true` để kiểm thử khả năng chịu lỗi của hệ thống.

### 3.3. Tầng ViewModel (`src/viewmodels/`)
Mỗi ViewModel được thiết kế gồm 2 phần bổ trợ cho nhau:
1. **Lớp ViewModel (Class):** Chứa toàn bộ state (`rooms`, `loading`, `error`, `statusFilter`), các phương thức nghiệp vụ (`loadRooms()`, `addRoom()`, `removeRoom()`) và cơ chế Observer Pattern (`subscribe`, `notify`).
   - *Lợi ích:* Hoạt động hoàn toàn độc lập với React DOM, có thể kiểm thử đơn vị cực nhanh và chính xác với Jest.
2. **Custom Hook (`useXxxViewModel`):** Bao bọc lớp ViewModel trên, kết nối với React State để kích hoạt re-render UI mỗi khi dữ liệu trong ViewModel thay đổi.
   - *Lợi ích:* Cung cấp API trực quan, dễ dùng cho các lập trình viên giao diện.

### 3.4. Tầng View (`src/views/`)
- Đảm nhận duy nhất một nhiệm vụ: **Hiển thị dữ liệu lên màn hình và đón nhận tương tác của người dùng**.
- **Nguyên tắc bắt buộc:** Tuyệt đối không gọi `fetch()`, `axios`, API URL hoặc `AsyncStorage` trực tiếp trong View.
- View chỉ nhận dữ liệu từ ViewModel (`vm.rooms`, `vm.loading`, `vm.error`) và gọi các action do ViewModel cung cấp (`vm.loadRooms()`, `vm.addRoom()`).

---

## 4. Ba Màn Hình Chính Được Áp Dụng MVVM

### Màn hình 1: Quản lý Phòng trọ (`RoomsView` & `useRoomsViewModel`)
- **Dữ liệu hiển thị:** Danh sách phòng trọ, diện tích, giá thuê, trạng thái (Trống / Đang thuê / Bảo trì), tên và SĐT khách thuê hiện tại.
- **Thống kê:** Thẻ tóm tắt tổng số phòng, số phòng trống, số phòng đang thuê.
- **Action nghiệp vụ:** Lọc theo trạng thái, thêm phòng mới, xóa phòng trống.
- **Xử lý trạng thái:** Hiển thị ActivityIndicator khi đang tải, hiển thị banner lỗi đỏ khi mất mạng và hộp thông báo rỗng khi chưa có phòng.

### Màn hình 2: Quản lý Hóa đơn (`InvoicesView` & `useInvoicesViewModel`)
- **Dữ liệu hiển thị:** Danh sách hóa đơn, chi tiết tiền phòng, điện, nước, dịch vụ chung, số tiền còn thiếu, hạn thanh toán.
- **Mã VietQR:** Tự động kết xuất mã QR thanh toán động cho người thuê.
- **Thống kê tài chính:** Tổng tiền đã thu, tổng tiền chưa thu, số hóa đơn chưa nộp.
- **Action nghiệp vụ:** Ghi nhận thu tiền (Admin), lọc trạng thái hóa đơn.

### Màn hình 3: Lịch sử Thông báo (`NotificationsView` & `useNotificationsViewModel`)
- **Dữ liệu hiển thị:** Danh sách thông báo đẩy từ server, thời gian tạo, phân loại (Hóa đơn / Bảo trì / Chung).
- **Badge chưa đọc:** Đếm số lượng thông báo chưa đọc trong thời gian thực.
- **Action nghiệp vụ:** Đánh dấu đã đọc từng tin, đánh dấu đọc tất cả, bật/tắt tùy chọn từng loại thông báo, thử lập lịch thông báo cục bộ 5 giây.
- **Điều hướng thông minh:** Chạm vào thông báo để chuyển hướng sang màn hình chi tiết tương ứng.

---

## 5. Ví Dụ Luồng Dữ Liệu Thực Tế

### 5.1. Luồng Tải Danh sách Phòng Thành Công
```text
[Người dùng mở mục Phòng trọ]
        │
        ▼
RoomsView được render
        │
        ▼
useRoomsViewModel gọi vm.loadRooms()
        │
        ▼
ViewModel đặt loading = true, error = null -> Thông báo View hiển thị Spinner Loading
        │
        ▼
ViewModel gọi repo.getRooms() (Ví dụ: ApiRoomRepository)
        │
        ▼
ApiRoomRepository gửi GET /api/rooms kèm Bearer Token
        │
        ▼
Server trả về danh sách 10 phòng JSON (200 OK)
        │
        ▼
ViewModel gán this.rooms = data, đặt loading = false -> Thông báo View
        │
        ▼
RoomsView tự động re-render, hiển thị 10 thẻ phòng trọ và cập nhật thống kê!
```

### 5.2. Luồng Xử Lý Lỗi (Error Handling)
```text
[Máy chủ bị ngắt kết nối hoặc sai địa chỉ IP]
        │
        ▼
ApiRoomRepository gọi API bị Timeout / Network Error
        │
        ▼
ApiRoomRepository ném ngoại lệ: throw new Error("Kết nối máy chủ quá hạn...")
        │
        ▼
ViewModel bắt ngoại lệ trong khối catch (err)
        │
        ▼
ViewModel gán this.error = err.message, đặt loading = false -> Thông báo View
        │
        ▼
RoomsView hiển thị Error Banner: "⚠️ Kết nối máy chủ quá hạn..." kèm nút "Thử lại"
        │
        ▼
Người dùng nhấn "Thử lại" -> Gọi lại vm.loadRooms() mà không làm crash ứng dụng!
```

---

## 6. Kiểm Thử Tự Động (Unit Testing) với Mock Repository

Một ưu điểm vượt trội của kiến trúc MVVM là khả năng kiểm thử độc lập (Dependency Injection). Trong môi trường Unit Test, chúng ta truyền `MockRoomRepository` vào `RoomsViewModel`:

```typescript
// __tests__/RoomsViewModel.test.ts
import { RoomsViewModel } from '../src/viewmodels/useRoomsViewModel';
import { MockRoomRepository } from '../src/repositories/MockRoomRepository';

describe('RoomsViewModel (MVVM Pattern)', () => {
  it('Tải danh sách phòng thành công', async () => {
    const mockRepo = new MockRoomRepository();
    const vm = new RoomsViewModel(mockRepo);

    await vm.loadRooms();

    expect(vm.loading).toBe(false);
    expect(vm.error).toBeNull();
    expect(vm.rooms).toHaveLength(3);
    expect(vm.stats.total).toBe(3);
  });

  it('Xử lý lỗi API chính xác', async () => {
    const mockRepo = new MockRoomRepository();
    mockRepo.shouldFail = true;
    mockRepo.errorMessage = 'Mất kết nối máy chủ WAMP';

    const vm = new RoomsViewModel(mockRepo);
    await vm.loadRooms();

    expect(vm.loading).toBe(false);
    expect(vm.error).toBe('Mất kết nối máy chủ WAMP');
  });
});
```

### Cách Chạy Bộ Unit Test:
Mở PowerShell tại thư mục `QuanLyPhongTroMobile` và chạy:
```powershell
npm test
```
**Kết quả thực tế:** 12/12 test cases PASS thành công 100%.
