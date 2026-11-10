<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>เข้าสู่ระบบผู้ดูแลระบบ (Admin Login) - Mini Shop</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
            background: #ffffff;
        }
        .login-header {
            background-color: #0d6efd;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            padding: 2rem;
            color: #fff;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="fa-solid fa-lock fa-3x mb-2 text-warning"></i>
            <h3 class="fw-bold mb-1">Admin Portal</h3>
            <p class="mb-0 text-white-50 small">ระบบจัดการร้านค้าออนไลน์ (Mini Shop)</p>
        </div>
        <div class="card-body p-4">
            <div id="loginAlert" class="alert alert-danger d-none"></div>

            <form id="loginForm">
                <div class="mb-3">
                    <label class="form-label fw-semibold">อีเมล (Admin Email)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control" name="email" id="email" value="admin@shop.test" required placeholder="admin@shop.test">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">รหัสผ่าน (Password)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-key text-muted"></i></span>
                        <input type="password" class="form-control" name="password" id="password" value="password123" required placeholder="รหัสผ่าน">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-full py-2 fw-bold w-100" id="btnLogin">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> เข้าสู่ระบบ
                </button>
            </form>

            <div class="mt-4 p-3 bg-light rounded text-muted small">
                <i class="fa-solid fa-circle-info text-primary me-1"></i> <strong>ข้อมูลทดสอบสำหรับ Admin:</strong><br>
                Email: <code>admin@shop.test</code><br>
                Password: <code>password123</code>
            </div>
        </div>
    </div>

    <!-- jQuery & Bootstrap 5 JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            // หากมี Token อยู่แล้ว ลองตรวจสอบว่ายังใช้งานได้หรือไม่
            const existingToken = localStorage.getItem('admin_token');
            if (existingToken) {
                $.ajax({
                    url: '/api/profile',
                    type: 'GET',
                    headers: { 'Authorization': 'Bearer ' + existingToken },
                    success: function (res) {
                        if (res.data && res.data.role === 'admin') {
                            window.location.replace("{{ route('admin.products') }}");
                        }
                    }
                });
            }

            $('#loginForm').on('submit', function (e) {
                e.preventDefault();
                $('#loginAlert').addClass('d-none').text('');
                const btn = $('#btnLogin');
                btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> กำลังตรวจสอบ...');

                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                const email = $('#email').val();
                const password = $('#password').val();

                $.ajax({
                    url: '/api/login',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ email: email, password: password }),
                    success: function (res) {
                        if (res.success && res.data) {
                            const user = res.data.user;
                            const token = res.data.token;

                            // ตรวจสอบว่ามี role เป็น admin หรือไม่
                            if (user.role !== 'admin') {
                                $('#loginAlert').removeClass('d-none').html(
                                    '<i class="fa-solid fa-triangle-exclamation me-1"></i> <strong>ปฏิเสธการเข้าถึง:</strong> บัญชีนี้มีสิทธิ์ <code>' + user.role + '</code> ไม่สามารถเข้าใช้งานระบบ Admin Web ได้'
                                );
                                btn.prop('disabled', false).html('<i class="fa-solid fa-right-to-bracket me-1"></i> เข้าสู่ระบบ');
                                return;
                            }

                            // บันทึก Token และ User ลงใน localStorage
                            localStorage.setItem('admin_token', token);
                            localStorage.setItem('admin_user', JSON.stringify(user));

                            Swal.fire({
                                icon: 'success',
                                title: 'เข้าสู่ระบบสำเร็จ',
                                text: 'ยินดีต้อนรับ ' + user.name,
                                timer: 1200,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.replace("{{ route('admin.products') }}");
                            });
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i class="fa-solid fa-right-to-bracket me-1"></i> เข้าสู่ระบบ');
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function (field, messages) {
                                const input = $(`#${field}`);
                                input.addClass('is-invalid');
                                input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
                            });
                        } else {
                            const message = xhr.responseJSON?.message || 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
                            $('#loginAlert').removeClass('d-none').text(message);
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
