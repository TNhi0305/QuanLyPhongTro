<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    /**
     * Đăng ký route GET
     */
    public function get(string $path, array|string|callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Đăng ký route POST
     */
    public function post(string $path, array|string|callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Thêm route vào danh sách
     */
    private function addRoute(string $method, string $path, array|string|callable $handler): void
    {
        $cleanPath = '/' . trim($path, '/');
        if ($cleanPath === '//') {
            $cleanPath = '/';
        }
        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => $cleanPath,
            'handler' => $handler
        ];
    }

    /**
     * Xác định route hiện tại từ request
     */
    public function getCurrentRoute(): string
    {
        // 1. Kiểm tra qua query parameter ?route=... (hoạt động kể cả khi máy chủ không bật RewriteRule)
        if (!empty($_GET['route'])) {
            return '/' . trim($_GET['route'], '/');
        }

        // 2. Kiểm tra qua PATH_INFO
        if (!empty($_SERVER['PATH_INFO'])) {
            return '/' . trim($_SERVER['PATH_INFO'], '/');
        }

        // 3. Phân tích REQUEST_URI dựa theo thư mục chứa script
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        if (strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = dirname($scriptName);
        $baseDir = str_replace('\\', '/', $baseDir);

        if ($baseDir !== '/' && strpos($uri, $baseDir) === 0) {
            $uri = substr($uri, strlen($baseDir));
        }

        $clean = '/' . trim($uri, '/');
        // Nếu truy cập trực tiếp vào index.php thì coi như route gốc
        if ($clean === '/index.php' || $clean === '//') {
            return '/';
        }

        return $clean;
    }

    /**
     * Điều phối (Dispatch) request đến controller thích hợp
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            http_response_code(200);
            exit;
        }

        $currentRoute = $this->getCurrentRoute();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            // Chuyển đổi path thành biểu thức Regex để bắt tham số động {id}, {code}, ...
            // Ví dụ: /rooms/edit/{id} -> #^/rooms/edit/(?P<id>[^/]+)$#
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $currentRoute, $matches)) {
                // Lọc bỏ các tham số đánh số, chỉ giữ lại tham số có tên
                $params = [];
                foreach ($matches as $key => $val) {
                    if (is_string($key)) {
                        $params[$key] = $val;
                    }
                }

                $this->executeHandler($route['handler'], $params);
                return;
            }
        }

        // Không tìm thấy route -> 404
        http_response_code(404);
        view('errors.404', ['route' => $currentRoute], 'none');
    }

    /**
     * Thực thi handler của route
     */
    private function executeHandler(array|string|callable $handler, array $params = []): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        if (is_array($handler)) {
            [$class, $method] = $handler;
            if (!class_exists($class)) {
                die("Controller class '{$class}' không tồn tại.");
            }
            $controller = new $class();
            if (!method_exists($controller, $method)) {
                die("Method '{$method}' không tồn tại trong controller '{$class}'.");
            }
            call_user_func_array([$controller, $method], $params);
            return;
        }

        die("Handler không hợp lệ cho route.");
    }
}
