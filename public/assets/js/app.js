/**
 * HỆ THỐNG QUẢN LÝ PHÒNG TRỌ - JAVASCRIPT HỖ TRỢ GIAO DIỆN
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Toggle Sidebar trên màn hình di động
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.admin-sidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function (e) {
            e.preventDefault();
            sidebar.classList.toggle('show');
            if (sidebarBackdrop) {
                sidebarBackdrop.classList.toggle('d-none');
            }
        });
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', function () {
            sidebar.classList.remove('show');
            sidebarBackdrop.classList.add('d-none');
        });
    }

    // 2. Xác nhận trước khi xóa (Delete Confirmation Modal / Alert)
    const deleteForms = document.querySelectorAll('.form-delete-confirm');
    deleteForms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const message = form.getAttribute('data-confirm') || 'Bạn có chắc chắn muốn xóa mục này không? Thao tác không thể hoàn tác!';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // 3. Tự động đóng thông báo flash sau 6 giây
    const alertElements = document.querySelectorAll('.alert-dismissible');
    alertElements.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 6000);
    });

    // 4. Xem trước hình ảnh khi upload
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            const previewContainerId = input.getAttribute('data-preview-target');
            if (!previewContainerId) return;

            const previewContainer = document.getElementById(previewContainerId);
            if (!previewContainer) return;

            previewContainer.innerHTML = '';
            if (input.files) {
                Array.from(input.files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'img-thumbnail me-2 mb-2';
                            img.style.width = '90px';
                            img.style.height = '90px';
                            img.style.objectFit = 'cover';
                            previewContainer.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    });
});

/**
 * Định dạng tiền tệ VND cho các input tính toán
 */
function formatVND(number) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(number);
}
