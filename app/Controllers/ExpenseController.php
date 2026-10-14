<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Expense;
use Exception;

class ExpenseController extends Controller
{
    private Expense $expenseModel;

    public function __construct()
    {
        AuthMiddleware::requireAdmin();
        $this->expenseModel = new Expense();
    }

    public function index(): void
    {
        $month = $_GET['month'] ?? '';
        $category = $_GET['category'] ?? '';

        $expenses = $this->expenseModel->allWithFilters($month, $category);
        $totalAmount = array_sum(array_column($expenses, 'amount'));

        $this->view('expenses.index', [
            'expenses'    => $expenses,
            'totalAmount' => $totalAmount,
            'month'       => $month,
            'category'    => $category
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('expenses.create', [], 'admin');
    }

    public function store(): void
    {
        $this->validateCsrf();

        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'Bảo trì');
        $amount = (float)($_POST['amount'] ?? 0);
        $expenseDate = $_POST['expense_date'] ?? date('Y-m-d');
        $recipient = trim($_POST['recipient'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($title) || $amount <= 0 || empty($expenseDate)) {
            set_flash('danger', 'Vui lòng nhập tên chi phí, số tiền hợp lệ (> 0) và ngày chi.');
            $this->redirect('/expenses/create');
        }

        try {
            $user = auth_user();
            $id = $this->expenseModel->createExpense([
                'title'        => $title,
                'category'     => $category,
                'amount'       => $amount,
                'expense_date' => $expenseDate,
                'recipient'    => $recipient,
                'description'  => $description,
                'created_by'   => $user['id'] ?? null
            ]);

            log_activity('Thêm chi phí', 'expenses', $id, "Ghi nhận khoản chi {$title}: " . format_currency($amount));
            set_flash('success', "Ghi nhận khoản chi thành công!");
            $this->redirect('/expenses');
        } catch (Exception $e) {
            set_flash('danger', 'Lỗi: ' . $e->getMessage());
            $this->redirect('/expenses/create');
        }
    }

    public function edit(int|string $id): void
    {
        $id = (int)$id;
        $expense = $this->expenseModel->find($id);
        if (!$expense) {
            set_flash('danger', 'Không tìm thấy khoản chi.');
            $this->redirect('/expenses');
        }

        $this->view('expenses.edit', ['expense' => $expense], 'admin');
    }

    public function update(int|string $id): void
    {
        $this->validateCsrf();
        $id = (int)$id;

        $expense = $this->expenseModel->find($id);
        if (!$expense) {
            set_flash('danger', 'Không tìm thấy khoản chi.');
            $this->redirect('/expenses');
        }

        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'Bảo trì');
        $amount = (float)($_POST['amount'] ?? 0);
        $expenseDate = $_POST['expense_date'] ?? date('Y-m-d');
        $recipient = trim($_POST['recipient'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($title) || $amount <= 0 || empty($expenseDate)) {
            set_flash('danger', 'Vui lòng nhập tên chi phí, số tiền hợp lệ (> 0) và ngày chi.');
            $this->redirect('/expenses/edit/' . $id);
        }

        $this->expenseModel->update($id, [
            'title'        => $title,
            'category'     => $category,
            'amount'       => $amount,
            'expense_date' => $expenseDate,
            'recipient'    => $recipient,
            'description'  => $description
        ]);

        log_activity('Cập nhật chi phí', 'expenses', $id, "Cập nhật khoản chi {$title}");
        set_flash('success', "Cập nhật khoản chi thành công!");
        $this->redirect('/expenses');
    }

    public function delete(int|string $id): void
    {
        $this->validateCsrf();
        $id = (int)$id;

        $expense = $this->expenseModel->find($id);
        if ($expense) {
            $this->expenseModel->delete($id);
            log_activity('Xóa chi phí', 'expenses', $id, "Xóa khoản chi {$expense['title']}");
            set_flash('success', 'Đã xóa khoản chi.');
        }

        $this->redirect('/expenses');
    }
}
