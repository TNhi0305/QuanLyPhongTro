<?php
// ========================================================
// CẤU HÌNH HỆ THỐNG (Application Settings)
// ========================================================

return [
    'app_name'    => 'Quản Lý Phòng Trọ',
    'app_version' => '1.0.0',
    'timezone'    => 'Asia/Ho_Chi_Minh',
    'locale'      => 'vi_VN',
    'currency'    => 'VNĐ',
    // Đường dẫn cơ sở tự động phát hiện theo môi trường (WAMP, localhost, hosting, IP LAN)
    'base_url'    => '',
    'session_lifetime' => 86400, // 24 giờ
    'upload_max_size'  => 5 * 1024 * 1024, // 5MB
    'allowed_image_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
    'allowed_image_exts'  => ['jpg', 'jpeg', 'png', 'webp', 'gif']
];
