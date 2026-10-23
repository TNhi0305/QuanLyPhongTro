# HỆ THỐNG QUẢN LÝ NHÀ TRỌ THÔNG MINH (QUAN LY PHONG TRO)
## Tài liệu Kỹ thuật, Báo cáo Đồ án & Hướng dẫn Vận hành

Chào mừng bạn đến với bộ tài liệu hoàn chỉnh của dự án **Hệ thống Quản lý Nhà trọ Thông minh**. Dự án kết hợp giữa **Backend PHP MVC / MySQL (WAMP Server)** và **Ứng dụng Di động Đa nền tảng React Native / Expo**, áp dụng kiến trúc **MVVM (Model - View - ViewModel)** và cơ chế **Push Notification thời gian thực qua Expo Push Service**.

---

## 📑 Danh mục Tài liệu Chi tiết

| STT | Tài liệu | Nội dung chính |
| :---: | :--- | :--- |
| **01** | [01_GIOI_THIEU_PROJECT.md](./01_GIOI_THIEU_PROJECT.md) | Tổng quan bài toán, mục tiêu, đối tượng sử dụng, phạm vi và công nghệ |
| **02** | [02_HUONG_DAN_CAI_DAT_VA_CHAY.md](./02_HUONG_DAN_CAI_DAT_VA_CHAY.md) | Hướng dẫn chi tiết từng bước cài đặt WAMP, MySQL, Node.js, Expo, LAN IP |
| **03** | [03_HUONG_DAN_SU_DUNG.md](./03_HUONG_DAN_SU_DUNG.md) | Hướng dẫn thao tác thực tế dành cho Chủ trọ (Admin) và Khách thuê (Tenant) |
| **04** | [04_BAO_CAO_DO_AN.md](./04_BAO_CAO_DO_AN.md) | Báo cáo đồ án môn học chuẩn chỉnh học thuật (9 chương, kết quả, đánh giá) |
| **05** | [05_KIEN_TRUC_MVVM.md](./05_KIEN_TRUC_MVVM.md) | Phân tích chuyên sâu kiến trúc MVVM, Repository Interface, Mocking & Unit Test |
| **06** | [06_PUSH_NOTIFICATION.md](./06_PUSH_NOTIFICATION.md) | Toàn diện về Expo Push Notification, 3 trạng thái nhận tin, lập lịch cục bộ |
| **07** | [07_API_DOCUMENTATION.md](./07_API_DOCUMENTATION.md) | Đặc tả 100% REST API endpoints thật của hệ thống, Request/Response JSON mẫu |
| **08** | [08_DATABASE_DESIGN.md](./08_DATABASE_DESIGN.md) | Thiết kế 18 bảng MySQL, khóa chính, khóa ngoại, migrations và ER Diagram |
| **09** | [09_TESTING_REPORT.md](./09_TESTING_REPORT.md) | Biên bản kiểm thử Jest Unit Test (12/12 PASS), kiểm thử API, UI và Push |
| **10** | [10_HUONG_DAN_BAO_VE.md](./10_HUONG_DAN_BAO_VE.md) | Kịch bản demo 5-10 phút, câu hỏi phản biện của Hội đồng và câu trả lời mẫu |

---

## 🖼️ Sơ đồ Kiến trúc & Luồng Dữ liệu (Mermaid)

- [Sơ đồ Kiến trúc Tổng thể Hệ thống](./diagrams/system_architecture.mmd)
- [Sơ đồ Luồng Kiến trúc MVVM](./diagrams/mvvm_architecture.mmd)
- [Sơ đồ Luồng Push Notification](./diagrams/notification_flow.mmd)
- [Sơ đồ Xác thực & Khôi phục Phiên Đăng nhập](./diagrams/login_flow.mmd)
- [Sơ đồ Thực thể Quan hệ Database (ERD)](./diagrams/er_diagram.mmd)

---

## ⚡ Bắt đầu Nhanh (Quick Start)

### 1. Khởi động Backend & Database (WAMP)
- Đảm bảo WAMP Server đang bật (Apache cổng `8080`, MySQL cổng `3306`).
- Chạy script kiểm tra database & migration:
```powershell
& "C:\wamp64\bin\php\php8.2.29\php.exe" scripts/migrate_mobile_push.php
```

### 2. Kiểm tra Unit Test Jest (MVVM ViewModels)
```powershell
cd c:\wamp64\www\QuanLyPhongTro\QuanLyPhongTroMobile
npm test
```
*Kết quả xác minh:* **3 test suites passed, 12 tests passed (100% PASS)**.

### 3. Chạy Ứng dụng Di động Expo
```powershell
cd c:\wamp64\www\QuanLyPhongTro\QuanLyPhongTroMobile
npx expo start
```
- Nhấn `w` để mở ngay trên trình duyệt web.
- Quét mã QR bằng ứng dụng Expo Go trên điện thoại (đảm bảo điện thoại cùng mạng Wi-Fi LAN).
