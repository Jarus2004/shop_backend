@extends('admin.layouts.app')

@section('title', 'จัดการคำสั่งซื้อ (Orders Management)')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-cart-shopping text-primary me-2"></i>จัดการคำสั่งซื้อ (Orders)
        </h2>
        <p class="text-muted mb-0">แสดงรายการคำสั่งซื้อทั้งหมด, ดูรายละเอียดและสินค้าในออเดอร์, และอัปเดตสถานะคำสั่งซื้อแบบ Seamless AJAX</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <button class="btn btn-outline-secondary" id="btnRefreshOrders">
            <i class="fa-solid fa-arrows-rotate me-1"></i> รีเฟรชข้อมูล
        </button>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="ordersTable">
                <thead>
                    <tr>
                        <th style="width: 70px;">Order #</th>
                        <th>ลูกค้า (Customer)</th>
                        <th>ยอดรวมสุทธิ</th>
                        <th style="width: 170px;">สถานะคำสั่งซื้อ</th>
                        <th>วันที่สั่งซื้อ</th>
                        <th style="width: 110px;" class="text-center">รายละเอียด</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: ดูรายละเอียด Order & Items -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="orderDetailModalLabel">
                    <i class="fa-solid fa-receipt text-primary me-2"></i>รายละเอียดคำสั่งซื้อ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4 p-3 bg-light rounded">
                    <div class="col-sm-6 col-md-3">
                        <small class="text-muted d-block">รหัสคำสั่งซื้อ</small>
                        <strong class="fs-5 text-primary" id="modalOrderId">-</strong>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <small class="text-muted d-block">ลูกค้า</small>
                        <strong id="modalOrderCustomer">-</strong>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <small class="text-muted d-block">วันที่สั่งซื้อ</small>
                        <span id="modalOrderDate">-</span>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <small class="text-muted d-block">สถานะปัจจุบัน</small>
                        <span id="modalOrderStatusBadge" class="badge bg-secondary">-</span>
                    </div>
                </div>

                <h6 class="fw-bold mb-3">
                    <i class="fa-solid fa-list-check me-1 text-secondary"></i> รายการสินค้าที่สั่งซื้อ (Order Items)
                </h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>ชื่อสินค้า</th>
                                <th style="width: 130px;" class="text-end">ราคา ณ ตอนสั่งซื้อ</th>
                                <th style="width: 100px;" class="text-center">จำนวน</th>
                                <th style="width: 140px;" class="text-end">รวมย่อย (บาท)</th>
                            </tr>
                        </thead>
                        <tbody id="modalOrderItemsBody">
                            <tr><td colspan="5" class="text-center text-muted">กำลังโหลดข้อมูล...</td></tr>
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <td colspan="4" class="text-end fw-bold fs-6">ยอดรวมสุทธิ (Grand Total):</td>
                                <td class="text-end fw-bold fs-5 text-primary" id="modalOrderGrandTotal">0.00 ฿</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    const statusBadges = {
        'pending': 'bg-warning text-dark',
        'paid': 'bg-info text-dark',
        'shipped': 'bg-primary text-white',
        'completed': 'bg-success text-white',
        'cancelled': 'bg-danger text-white'
    };

    let ordersTable = $('#ordersTable').DataTable({
        ajax: {
            url: '/api/admin/orders',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'id',
                render: (data) => `<span class="badge bg-dark fs-6">#${data}</span>`
            },
            {
                data: null,
                render: function (data, type, row) {
                    return `<div><strong>${row.user_name || 'Customer'}</strong><br><small class="text-muted">${row.user_email || '-'}</small></div>`;
                }
            },
            {
                data: 'total',
                render: function (data) {
                    return `<span class="fw-bold fs-6 text-success">${Number(data).toLocaleString('th-TH', { minimumFractionDigits: 2 })} ฿</span>`;
                }
            },
            {
                data: 'status',
                render: function (data, type, row) {
                    return `
                        <select class="form-select form-select-sm fw-bold order-status-select ${statusBadges[data] || 'bg-secondary'}" data-id="${row.id}" data-current="${data}">
                            <option value="pending" ${data === 'pending' ? 'selected' : ''}>⏳ Pending (รอชำระ)</option>
                            <option value="paid" ${data === 'paid' ? 'selected' : ''}>💳 Paid (ชำระแล้ว)</option>
                            <option value="shipped" ${data === 'shipped' ? 'selected' : ''}>🚚 Shipped (จัดส่งแล้ว)</option>
                            <option value="completed" ${data === 'completed' ? 'selected' : ''}>✅ Completed (สำเร็จ)</option>
                            <option value="cancelled" ${data === 'cancelled' ? 'selected' : ''}>❌ Cancelled (ยกเลิก)</option>
                        </select>
                    `;
                }
            },
            {
                data: 'created_at',
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
                        <button class="btn btn-sm btn-outline-primary btn-view-order" data-id="${row.id}">
                            <i class="fa-solid fa-eye me-1"></i> ดูสินค้า
                        </button>
                    `;
                }
            }
        ],
        order: [[0, 'desc']]
    });

    $('#btnRefreshOrders').on('click', function () {
        ordersTable.ajax.reload(null, false);
    });

    // อัปเดตสถานะ Order แบบ Seamless Update ผ่าน AJAX
    $('#ordersTable').on('change', '.order-status-select', function () {
        const select = $(this);
        const orderId = select.data('id');
        const prevStatus = select.data('current');
        const newStatus = select.val();

        select.prop('disabled', true);

        $.ajax({
            url: `/api/admin/orders/${orderId}/status`,
            type: 'PATCH',
            contentType: 'application/json',
            data: JSON.stringify({ status: newStatus }),
            success: function (res) {
                select.prop('disabled', false);
                select.data('current', newStatus);

                // เปลี่ยนสี Dropdown ทันทีตามสถานะใหม่
                select.removeClass('bg-warning bg-info bg-primary bg-success bg-danger bg-secondary text-dark text-white');
                select.addClass(statusBadges[newStatus] || 'bg-secondary');

                // แจ้งเตือน Toast ขนาดเล็ก
                Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                }).fire({
                    icon: 'success',
                    title: `อัปเดต Order #${orderId} เป็น "${newStatus}" แล้ว`
                });
            },
            error: function (xhr) {
                select.prop('disabled', false);
                select.val(prevStatus); // rollback ค่าเดิม
                Swal.fire({
                    icon: 'error',
                    title: 'อัปเดตสถานะไม่สำเร็จ',
                    text: xhr.responseJSON?.message || 'เกิดข้อผิดพลาดในการเปลี่ยนสถานะคำสั่งซื้อ'
                });
            }
        });
    });

    // กดดูรายละเอียดคำสั่งซื้อใน Modal
    $('#ordersTable').on('click', '.btn-view-order', function () {
        const orderId = $(this).data('id');
        $('#modalOrderId').text(`#${orderId}`);
        $('#modalOrderCustomer').text('กำลังโหลด...');
        $('#modalOrderDate').text('-');
        $('#modalOrderStatusBadge').text('-').attr('class', 'badge bg-secondary');
        $('#modalOrderItemsBody').html('<tr><td colspan="5" class="text-center text-muted"><i class="fa-solid fa-spinner fa-spin me-1"></i> กำลังโหลดรายการสินค้า...</td></tr>');
        $('#modalOrderGrandTotal').text('0.00 ฿');

        $('#orderDetailModal').modal('show');

        $.ajax({
            url: `/api/orders/${orderId}`,
            type: 'GET',
            success: function (res) {
                if (res.success && res.data) {
                    const order = res.data;
                    $('#modalOrderCustomer').html(`<strong>${order.user_name}</strong><br><small class="text-muted">${order.user_email || ''}</small>`);
                    $('#modalOrderDate').text(order.created_at ? new Date(order.created_at).toLocaleString('th-TH') : '-');
                    $('#modalOrderStatusBadge').text(order.status).attr('class', `badge ${statusBadges[order.status] || 'bg-secondary'}`);
                    $('#modalOrderGrandTotal').text(Number(order.total).toLocaleString('th-TH', { minimumFractionDigits: 2 }) + ' ฿');

                    let itemsHtml = '';
                    if (order.items && order.items.length > 0) {
                        $.each(order.items, function (index, item) {
                            itemsHtml += `
                                <tr>
                                    <td class="text-center">${index + 1}</td>
                                    <td><strong>${item.product_name}</strong></td>
                                    <td class="text-end">${Number(item.price).toLocaleString('th-TH', { minimumFractionDigits: 2 })} ฿</td>
                                    <td class="text-center"><span class="badge bg-secondary fs-6">${item.quantity}</span></td>
                                    <td class="text-end fw-bold">${Number(item.subtotal).toLocaleString('th-TH', { minimumFractionDigits: 2 })} ฿</td>
                                </tr>
                            `;
                        });
                    } else {
                        itemsHtml = '<tr><td colspan="5" class="text-center text-muted">ไม่พบรายการสินค้าในคำสั่งซื้อนี้</td></tr>';
                    }
                    $('#modalOrderItemsBody').html(itemsHtml);
                }
            },
            error: function (xhr) {
                $('#modalOrderItemsBody').html('<tr><td colspan="5" class="text-center text-danger">ไม่สามารถโหลดรายละเอียดคำสั่งซื้อได้</td></tr>');
            }
        });
    });
});
</script>
@endpush
