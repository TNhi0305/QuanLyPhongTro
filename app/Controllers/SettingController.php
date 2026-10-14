<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Setting;

class SettingController extends Controller
{
    private Setting $settingModel;

    public function __construct()
    {
        AuthMiddleware::requireAdmin();
        $this->settingModel = new Setting();
    }

    public function index(): void
    {
        $settings = $this->settingModel->getAll();
        $this->view('settings.index', ['settings' => $settings], 'admin');
    }

    public function update(): void
    {
        $this->validateCsrf();

        $keys = [
            'app_name',
            'hotline',
            'address',
            'bank_name',
            'bank_account_number',
            'bank_account_name',
            'payment_instruction'
        ];

        foreach ($keys as $k) {
            if (isset($_POST[$k])) {
                $this->settingModel->set($k, trim($_POST[$k]));
            }
        }

        log_activity('Cập nhật cài đặt', 'system_settings', null, 'Cập nhật cấu hình hệ thống & thông tin ngân hàng');
        set_flash('success', 'Cập nhật thông tin cài đặt hệ thống thành công!');
        $this->redirect('/settings');
    }
}