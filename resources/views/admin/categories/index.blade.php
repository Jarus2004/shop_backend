@extends('admin.layouts.app')

@section('title', 'จัดการหมวดหมู่สินค้า (Categories Management)')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-tags text-primary me-2"></i>จัดการหมวดหมู่ (Categories)
        </h2>
        <p class="text-muted mb-0">เพิ่ม แก้ไข ลบหมวดหมู่สินค้า พร้อมระบบตรวจสอบป้องกันการลบหมวดหมู่ที่ยังมีสินค้าอยู่</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <button class="btn btn-primary" id="btnOpenAddCategoryModal">
            <i class="fa-solid fa-plus-circle me-1"></i> เพิ่มหมวดหมู่ใหม่
        </button>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="categoriesTable">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>ชื่อหมวดหมู่สินค้า</th>
                        <th style="width: 160px;" class="text-center">จำนวนสินค้าในหมวดหมู่</th>
                        <th style="width: 180px;">วันที่สร้าง</th>
                        <th style="width: 140px;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: เพิ่ม / แก้ไขหมวดหมู่ -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="categoryModalLabel">
                    <i class="fa-solid fa-tags text-primary me-2"></i>เพิ่มหมวดหมู่ใหม่
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="categoryForm">
                <input type="hidden" name="category_id" id="category_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ชื่อหมวดหมู่ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="category_name" required placeholder="เช่น Electronics, เสื้อผ้าแฟชั่น">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveCategory">
                        <i class="fa-solid fa-floppy-disk me-1"></i> บันทึก
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
    let categoriesTable = $('#categoriesTable').DataTable({
        ajax: {
            url: '/api/categories',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            {
                data: 'name',
                render: function (data) {
                    return `<span class="fw-bold fs-6 text-primary">${data}</span>`;
                }
            },
            {
                data: 'products_count',
                className: 'text-center',
                render: function (data) {
                    const count = data || 0;
                    if (count > 0) {
                        return `<span class="badge bg-info text-dark fs-6">${count} รายการ</span>`;
                    }
                    return `<span class="badge bg-secondary">0 รายการ</span>`;
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
                        <button class="btn btn-sm btn-outline-warning btn-edit-category me-1" data-id="${row.id}" data-name="${row.name}" title="แก้ไข">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-delete-category" data-id="${row.id}" data-name="${row.name}" data-count="${row.products_count || 0}" title="ลบ">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        order: [[0, 'asc']]
    });

    // เปิด Modal เพิ่มหมวดหมู่
    $('#btnOpenAddCategoryModal').on('click', function () {
        clearValidationErrors('#categoryForm');
        $('#categoryForm')[0].reset();
        $('#category_id').val('');
        $('#categoryModalLabel').html('<i class="fa-solid fa-plus-circle text-primary me-2"></i>เพิ่มหมวดหมู่ใหม่');
        $('#categoryModal').modal('show');
    });

    // เปิด Modal แก้ไขหมวดหมู่
    $('#categoriesTable').on('click', '.btn-edit-category', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        clearValidationErrors('#categoryForm');
        $('#category_id').val(id);
        $('#category_name').val(name);
        $('#categoryModalLabel').html(`<i class="fa-solid fa-pen-to-square text-warning me-2"></i>แก้ไขหมวดหมู่: #${id} ${name}`);
        $('#categoryModal').modal('show');
    });

    // บันทึกหมวดหมู่ (เพิ่ม / แก้ไข)
    $('#categoryForm').on('submit', function (e) {
        e.preventDefault();
        clearValidationErrors('#categoryForm');

        const btn = $('#btnSaveCategory');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> กำลังบันทึก...');

        const id = $('#category_id').val();
        const isEdit = id ? true : false;
        const url = isEdit ? `/api/admin/categories/${id}` : '/api/admin/categories';
        const method = isEdit ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            contentType: 'application/json',
            data: JSON.stringify({ name: $('#category_name').val() }),
            success: function (res) {
                btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> บันทึก');
                $('#categoryModal').modal('hide');
                categoriesTable.ajax.reload(null, false);

                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ!',
                    text: res.message || 'บันทึกข้อมูลหมวดหมู่เรียบร้อยแล้ว',
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> บันทึก');
                if (xhr.status === 422) {
                    showValidationErrors('#categoryForm', xhr.responseJSON.errors);
                } else {
                    Swal.fire('ข้อผิดพลาด', xhr.responseJSON?.message || 'ไม่สามารถบันทึกข้อมูลได้', 'error');
                }
            }
        });
    });

    // ลบหมวดหมู่ (พร้อมตรวจสอบว่ามีสินค้าหรือไม่)
    $('#categoriesTable').on('click', '.btn-delete-category', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const count = $(this).data('count');

        // หากมีสินค้าอยู่ ให้แจ้งเตือนก่อนทันที
        if (count > 0) {
            Swal.fire({
                icon: 'error',
                title: 'ไม่สามารถลบหมวดหมู่นี้ได้!',
                html: `หมวดหมู่ <strong>"${name}"</strong> มีสินค้าผูกอยู่ทั้งหมด <strong>${count} รายการ</strong><br><span class="text-danger">กรุณาย้ายหรือลบสินค้าในหมวดหมู่นี้ออกให้หมดก่อนลบหมวดหมู่</span>`,
                confirmButtonText: 'รับทราบ'
            });
            return;
        }

        Swal.fire({
            title: 'ยืนยันการลบหมวดหมู่?',
            html: `ต้องการลบหมวดหมู่ <strong>"${name}"</strong> ใช่หรือไม่?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ใช่, ลบเลย',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/admin/categories/${id}`,
                    type: 'DELETE',
                    success: function (res) {
                        categoriesTable.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'ลบสำเร็จ!',
                            text: res.message || 'ลบหมวดหมู่เรียบร้อยแล้ว',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'ไม่สามารถลบได้',
                            text: xhr.responseJSON?.message || 'เกิดข้อผิดพลาดในการลบหมวดหมู่'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush
