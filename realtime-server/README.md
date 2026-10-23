# Dịch vụ Realtime Socket.IO - Quản Lý Nhà Trọ

Dịch vụ Node.js + Socket.IO độc lập phục vụ trao đổi dữ liệu hai chiều tức thời (Realtime) giữa ứng dụng di động React Native và hệ thống backend PHP WAMP.

## Cấu trúc thư mục

```text
realtime-server/
├── src/
│   ├── server.js        # Điểm khởi chạy Express + Socket.IO
│   ├── auth/            # Xác thực handshake token bằng HMAC-SHA256
│   ├── sockets/         # Đăng ký sự kiện socket & lắng nghe kết nối
│   ├── rooms/           # Quản lý và phân quyền Room
│   └── config/          # Cấu hình cổng, secret và CORS
├── package.json
└── README.md
```

## Các cổng và biến môi trường

- Cổng mặc định: `3001`
- Biến môi trường:
  - `PORT`: Cổng dịch vụ (mặc định 3001)
  - `HOST`: 0.0.0.0
  - `QLPT_API_SECRET`: Khóa bí mật dùng để xác thực Bearer token được cấp bởi PHP
  - `QLPT_REALTIME_SECRET`: Khóa bí mật nội bộ để PHP gửi webhook sang Node.js (`POST /api/emit`)

## Khởi chạy dịch vụ

```bash
cd realtime-server
npm install
npm start
```
