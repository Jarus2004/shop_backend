@extends('admin.layouts.app')

@section('title', 'จัดการสินค้า (Products Management)')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-box text-primary me-2"></i>จัดการสินค้า (Products)
        </h2>
        <p class="text-muted mb-0">ระบบแสดงรายการสินค้าด้วย DataTables, เพิ่ม/แก้ไขด้วย Modal AJAX, และจัดการ Soft Delete/Restore</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <button class="btn btn-primary" id="btnOpenAddModal">
            <i class="fa-solid fa-plus-circle me-1"></i> เพิ่มสินค้าใหม่
        </button>
    </div>
</div>

<!-- Tabs for Active Products and Trashed Products -->
<div class="card mb-4">
    <div class="card-header bg-white border-bottom-0 pb-0">
        <ul class="nav nav-tabs card-header-tabs" id="productTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-semibold" id="active-tab" data-bs-toggle="tab" data-bs-target="#tab-active" type="button" role="tab">
                    <i class="fa-solid fa-boxes-stacked me-1 text-success"></i> สินค้าทั้งหมด (Active & Inactive)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="trashed-tab" data-bs-toggle="tab" data-bs-target="#tab-trashed" type="button" role="tab">
                    <i class="fa-solid fa-trash-can me-1 text-danger"></i> สินค้าที่ถูกลบ (ถังขยะ / Trashed)
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <!-- Active Products Tab -->
            <div class="tab-pane fade show active" id="tab-active" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="productsTable">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th>ชื่อสินค้า</th>
                                <th>หมวดหมู่</th>
                                <th>ราคา (บาท)</th>
                                <th>สต็อก</th>
                                <th>สถานะเปิดขาย</th>
                                <th>วันที่สร้าง</th>
                                <th style="width: 130px;" class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- Trashed Products Tab -->
            <div class="tab-pane fade" id="tab-trashed" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="trashedTable">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th>ชื่อสินค้า</th>
                                <th>หมวดหมู่</th>
                                <th>ราคา (บาท)</th>
                                <th>สต็อก</th>
                                <th>วันที่ลบ (Soft Deleted)</th>
                                <th style="width: 100px;" class="text-center">กู้คืน</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: เพิ่ม / แก้ไขสินค้า -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="productModalLabel">
                    <i class="fa-solid fa-box text-primary me-2"></i>เพิ่มสินค้าใหม่
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="productForm">
                <input type="hidden" name="product_id" id="product_id">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">หมวดหมู่สินค้า <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" id="category_id" required>
                                <option value="">-- เลือกหมวดหมู่ --</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ชื่อสินค้า <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="name" required placeholder="เช่น สมาร์ตโฟน 5G">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">รายละเอียดสินค้า</label>
                            <textarea class="form-control" name="description" id="description" rows="3" placeholder="ระบุรายละเอียดคุณสมบัติสินค้า..."></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ราคา (บาท) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control" name="price" id="price" required placeholder="0.00">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">จำนวนสต็อก <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control" name="stock" id="stock" required placeholder="0">
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    เปิดใช้งานสินค้านี้ (แสดงในหน้าลูกค้า / is_active = true)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveProduct">
                        <i class="fa-solid fa-floppy-disk me-1"></i> บันทึกข้อมูล
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    let productsTable = null;
    let trashedTable = null;
    let categoriesList = [];

    // โหลดรายชื่อหมวดหมู่สำหรับใส่ใน Dropdown
    function loadCategoriesDropdown() {
        $.ajax({
            url: '/api/categories',
            type: 'GET',
            success: function (res) {
                if (res.success && res.data) {
                    categoriesList = res.data;
                    let options = '<option value="">-- เลือกหมวดหมู่ --</option>';
                    $.each(res.data, function (i, cat) {
                        options += `<option value="${cat.id}">${cat.name}</option>`;
                    });
                    $('#category_id').html(options);
                }
            }
        });
    }
    loadCategoriesDropdown();

    // DataTables: ตารางสินค้าปกติ
    productsTable = $('#productsTable').DataTable({
        ajax: {
            url: '/api/admin/products',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            {
                data: 'name',
                render: function (data, type, row) {
                    return `<span class="fw-bold">${data}</span><br><small class="text-muted">${row.description || '-'}</small>`;
                }
            },
            {
                data: 'category',
                render: function (data) {
                    return data ? `<span class="badge bg-secondary">${data.name}</span>` : '-';
                }
            },
            {
                data: 'price',
                render: function (data) {
                    return Number(data).toLocaleString('th-TH', { minimumFractionDigits: 2 }) + ' ฿';
                }
            },
            {
                data: 'stock',
                render: function (data) {
                    if (data <= 5) {
                        return `<span class="badge bg-danger">${data} (เหลือน้อย)</span>`;
                    }
                    return `<span class="badge bg-success">${data} ชิ้น</span>`;
                }
            },
            {
                data: 'is_active',
                render: function (data) {
                    return data
                        ? '<span class="badge bg-primary badge-status"><i class="fa-solid fa-check me-1"></i>วางขาย</span>'
                        : '<span class="badge bg-secondary badge-status"><i class="fa-solid fa-ban me-1"></i>ปิดขาย</span>';
                }
            },
            {
                data: 'created_at',
                render: function (data) {
                    return data ? new Date(data).toLocaleDateString('th-TH') : '-';
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-outline-warning btn-edit me-1" data-id="${row.id}" title="แก้ไข">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${row.id}" data-name="${row.name}" title="ลบแบบ Soft Delete">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        order: [[0, 'desc']]
    });

    // DataTables: ตารางสินค้าที่ถูกลบ (ถังขยะ)
    trashedTable = $('#trashedTable').DataTable({
        ajax: {
            url: '/api/admin/products/trashed',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            { data: 'name', render: (data) => `<span class="text-decoration-line-through text-muted">${data}</span>` },
            {
                data: 'category',
                render: function (data) {
                    return data ? `<span class="badge bg-light text-dark border">${data.name}</span>` : '-';
                }
            },
            {
                data: 'price',
                render: function (data) {
                    return Number(data).toLocaleString('th-TH', { minimumFractionDigits: 2 }) + ' ฿';
                }
            },
            { data: 'stock' },
            {
                data: 'deleted_at',
                render: function (data) {
                    return data ? new Date(data).toLocaleString('th-TH') : '-';
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-success btn-restore" data-id="${row.id}" data-name="${row.name}">
                            <i class="fa-solid fa-rotate-left me-1"></i> กู้คืน
                        </button>
                    `;
                }
            }
        ],
        order: [[5, 'desc']]
    });

    // รีโหลด Trashed Table เมื่อคลิกแท็บ
    $('#trashed-tab').on('shown.bs.tab', function () {
        trashedTable.ajax.reload(null, false);
    });

    // เปิด Modal เพิ่มสินค้าใหม่
    $('#btnOpenAddModal').on('click', function () {
        clearValidationErrors('#productForm');
        $('#productForm')[0].reset();
        $('#product_id').val('');
        $('#is_active').prop('checked', true);
        $('#productModalLabel').html('<i class="fa-solid fa-plus-circle text-primary me-2"></i>เพิ่มสินค้าใหม่');
        $('#productModal').modal('show');
    });

    // เปิด Modal แก้ไขสินค้า
    $('#productsTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        clearValidationErrors('#productForm');
        $('#productForm')[0].reset();

        $.ajax({
            url: `/api/products/${id}`,
            type: 'GET',
            success: function (res) {
                if (res.success && res.data) {
                    const prod = res.data;
                    $('#product_id').val(prod.id);
                    $('#category_id').val(prod.category_id);
                    $('#name').val(prod.name);
                    $('#description').val(prod.description);
                    $('#price').val(prod.price);
                    $('#stock').val(prod.stock);
                    $('#is_active').prop('checked', prod.is_active);

                    $('#productModalLabel').html(`<i class="fa-solid fa-pen-to-square text-warning me-2"></i>แก้ไขสินค้า: #${prod.id} ${prod.name}`);
                    $('#productModal').modal('show');
                }
            }
        });
    });

    // บันทึกสินค้า (เพิ่ม หรือ แก้ไข) ผ่าน AJAX
    $('#productForm').on('submit', function (e) {
        e.preventDefault();
        clearValidationErrors('#productForm');

        const btn = $('#btnSaveProduct');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> กำลังบันทึก...');

        const id = $('#product_id').val();
        const isEdit = id ? true : false;
        const url = isEdit ? `/api/admin/products/${id}` : '/api/admin/products';
        const method = isEdit ? 'PUT' : 'POST';

        const payload = {
            category_id: $('#category_id').val(),
            name: $('#name').val(),
            description: $('#description').val(),
            price: $('#price').val(),
            stock: $('#stock').val(),
            is_active: $('#is_active').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: url,
            type: method,
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function (res) {
                btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> บันทึกข้อมูล');
                $('#productModal').modal('hide');
                productsTable.ajax.reload(null, false);

                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ!',
                    text: res.message || 'บันทึกข้อมูลสินค้าเรียบร้อยแล้ว',
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> บันทึกข้อมูล');
                if (xhr.status === 422) {
                    // แสดง Validation Error 422 ใต้แต่ละช่อง Input
                    showValidationErrors('#productForm', xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: xhr.responseJSON?.message || 'ไม่สามารถบันทึกข้อมูลได้'
                    });
                }
            }
        });
    });

    // ลบสินค้าแบบ Soft Delete
    $('#productsTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');

        Swal.fire({
            title: 'ยืนยันการลบสินค้า?',
            html: `คุณต้องการลบสินค้า <strong>"${name}"</strong> ใช่หรือไม่?<br><small class="text-muted">(เป็นการลบแบบ Soft Delete ข้อมูลจะถูกย้ายไปถังขยะและสามารถกู้คืนได้)</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ใช่, ลบสินค้า',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/admin/products/${id}`,
                    type: 'DELETE',
                    success: function (res) {
                        productsTable.ajax.reload(null, false);
                        trashedTable.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'ลบสำเร็จ!',
                            text: res.message || 'ลบสินค้าเรียบร้อยแล้ว',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function (xhr) {
                        Swal.fire('ข้อผิดพลาด', xhr.responseJSON?.message || 'ไม่สามารถลบสินค้าได้', 'error');
                    }
                });
            }
        });
    });

    // กู้คืนสินค้า (Restore)
    $('#trashedTable').on('click', '.btn-restore', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');

        Swal.fire({
            title: 'ยืนยันการกู้คืนสินค้า?',
            html: `ต้องการกู้คืนสินค้า <strong>"${name}"</strong> กลับสู่รายการขายปกติหรือไม่?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'กู้คืนสินค้า',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/admin/products/${id}/restore`,
                    type: 'POST',
                    success: function (res) {
                        productsTable.ajax.reload(null, false);
                        trashedTable.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'กู้คืนสำเร็จ!',
                            text: res.message || 'กู้คืนสินค้าเรียบร้อยแล้ว',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function (xhr) {
                        Swal.fire('ข้อผิดพลาด', xhr.responseJSON?.message || 'ไม่สามารถกู้คืนสินค้าได้', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
