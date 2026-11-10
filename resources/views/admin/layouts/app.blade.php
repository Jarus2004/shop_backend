<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Mini Shop</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.08);
        }
        .table th {
            background-color: #f1f3f5;
            color: #495057;
            font-weight: 600;
        }
        .badge-status {
            font-size: 0.85rem;
            padding: 0.4em 0.7em;
        }
        .main-content {
            flex: 1;
        }
        footer {
            background: #fff;
            border-top: 1px solid #e9ecef;
            padding: 1rem 0;
            font-size: 0.875rem;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="{{ route('admin.products') }}">
                <i class="fa-solid fa-store me-2 text-warning"></i>Mini Shop Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.products') ? 'active fw-bold' : '' }}" href="{{ route('admin.products') }}">
                            <i class="fa-solid fa-box me-1"></i> สินค้า (Products)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.categories') ? 'active fw-bold' : '' }}" href="{{ route('admin.categories') }}">
                            <i class="fa-solid fa-tags me-1"></i> หมวดหมู่ (Categories)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.orders') ? 'active fw-bold' : '' }}" href="{{ route('admin.orders') }}">
                            <i class="fa-solid fa-shopping-cart me-1"></i> คำสั่งซื้อ (Orders)
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center text-white">
                    <span class="me-3" id="adminUserBadge">
                        <i class="fa-solid fa-user-shield text-info me-1"></i>
                        <span id="adminUserName">Admin</span>
                    </span>
                    <button class="btn btn-outline-danger btn-sm" id="btnLogout">
                        <i class="fa-solid fa-right-from-bracket me-1"></i> ออกจากระบบ
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content py-4">
        <div class="container-fluid px-4">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="text-center text-muted">
        <div class="container">
            <span>&copy; {{ date('Y') }} Mini Shop Admin Web &bull; Laravel 12 & DataTables & Sanctum</span>
        </div>
    </footer>

    <!-- jQuery, Bootstrap 5 JS, DataTables, SweetAlert2 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Global Auth & AJAX Setup -->
    <script>
        const adminToken = localStorage.getItem('admin_token');
        const adminUser = JSON.parse(localStorage.getItem('admin_user') || '{}');

        // Client-side Guard: ตรวจสอบว่ามี Token หรือไม่
        if (!adminToken) {
            window.location.replace("{{ route('admin.login') }}");
        } else {
            if (adminUser && adminUser.name) {
                $('#adminUserName').text(adminUser.name + ' (' + (adminUser.role || 'admin') + ')');
            }
        }

        // ตั้งค่า Headers สำหรับทุก AJAX Request
        $.ajaxSetup({
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Authorization': adminToken ? 'Bearer ' + adminToken : ''
            }
        });

        // ตั้งค่าภาษาไทยเริ่มต้นให้กับ DataTables ทุกตารางในระบบ (ไม่ต้องโหลดไฟล์จาก CDN)
        const dtThaiLanguage = {
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
            infoEmpty: "แสดง 0 ถึง 0 จากทั้งหมด 0 รายการ",
            infoFiltered: "(กรองจากทั้งหมด _MAX_ รายการ)",
            zeroRecords: "ไม่พบข้อมูลที่ตรงกัน",
            emptyTable: "ไม่มีข้อมูลในตาราง",
            loadingRecords: "กำลังโหลดข้อมูล...",
            paginate: {
                first: "หน้าแรก",
                previous: "ก่อนหน้า",
                next: "ถัดไป",
                last: "หน้าสุดท้าย"
            }
        };
        $.extend(true, $.fn.dataTable.defaults, {
            language: dtThaiLanguage
        });

        // Global Error Interceptor (ดักจับ 401 Unauthorized / 403 Forbidden)
        $(document).ajaxError(function (event, jqXHR) {
            if (jqXHR.status === 401) {
                localStorage.removeItem('admin_token');
                localStorage.removeItem('admin_user');
                Swal.fire({
                    icon: 'warning',
                    title: 'เซสชันหมดอายุ',
                    text: 'กรุณาเข้าสู่ระบบใหม่อีกครั้ง',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    window.location.replace("{{ route('admin.login') }}");
                });
            } else if (jqXHR.status === 403) {
                Swal.fire({
                    icon: 'error',
                    title: 'สิทธิ์การเข้าถึงไม่ถูกต้อง',
                    text: jqXHR.responseJSON?.message || 'คุณไม่มีสิทธิ์ในการดำเนินการนี้ (ต้องเป็น Admin)',
                    confirmButtonText: 'ตกลง'
                });
            }
        });

        // Helper: แสดง Validation Error 422 ใต้ Input
        function showValidationErrors(formSelector, errors) {
            clearValidationErrors(formSelector);
            if (!errors) return;

            $.each(errors, function (field, messages) {
                const input = $(formSelector).find(`[name="${field}"]`);
                if (input.length) {
                    input.addClass('is-invalid');
                    const errorMsg = messages.join(', ');
                    input.after(`<div class="invalid-feedback d-block">${errorMsg}</div>`);
                }
            });
        }

        // Helper: เคลียร์ Validation Error
        function clearValidationErrors(formSelector) {
            $(formSelector).find('.is-invalid').removeClass('is-invalid');
            $(formSelector).find('.invalid-feedback').remove();
        }

        // ออกจากระบบ
        $('#btnLogout').on('click', function () {
            Swal.fire({
                title: 'ยืนยันออกจากระบบ?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ออกจากระบบ',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/api/logout',
                        type: 'POST',
                        complete: function () {
                            localStorage.removeItem('admin_token');
                            localStorage.removeItem('admin_user');
                            window.location.replace("{{ route('admin.login') }}");
                        }
                    });
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
