<?php

namespace App\Core;

class Controller
{
    /**
     * Render view
     */
    protected function view(string $viewPath, array $data = [], string $layout = 'admin'): void
    {
        view($viewPath, $data, $layout);
    }

    /**
     * Phản hồi dạng JSON cho AJAX request
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Chuyển hướng
     */
    protected function redirect(string $url): void
    {
        redirect($url);
    }

    /**
     * Xác thực CSRF Token, nếu không hợp lệ sẽ dừng lại
     */
    protected function validateCsrf(): void
    {
        if (!verify_csrf_token()) {
            set_flash('danger', 'Phiên làm việc hết hạn hoặc yêu cầu không hợp lệ (Lỗi CSRF Token). Vui lòng thử lại.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }
    }
}
