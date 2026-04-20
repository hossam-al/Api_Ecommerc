<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الطلبات</title>
    <meta name="api-base-url" content="{{ url('/api/v1') }}">
    <meta name="api-token" content="{{ $apiToken }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body style="background-color:#f5f7fa;font-family: 'Tajawal', sans-serif;">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard.owner') }}">لوحة التحكم</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">{{ $user->name }}</span>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>إدارة الطلبات</h3>
            <a href="{{ route('dashboard.owner') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-right"></i> العودة للداشبورد
            </a>
        </div>

        <div class="card mb-3">
            <div class="card-body d-flex flex-wrap gap-2 align-items-center">
                <div class="me-2">
                    <label class="form-label mb-0 small">حالة الطلب</label>
                    <select id="status-filter" class="form-select form-select-sm">
                        <option value="">الكل</option>
                        <option value="pending">قيد الانتظار</option>
                        <option value="processing">قيد المعالجة</option>
                        <option value="completed">مكتمل</option>
                        <option value="cancelled">ملغي</option>
                    </select>
                </div>
                <button id="refresh-orders" class="btn btn-sm btn-primary mt-3 mt-md-0">
                    <i class="bi bi-arrow-clockwise"></i> تحديث
                </button>
                <span id="orders-count" class="ms-auto small text-muted"></span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">قائمة الطلبات</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="orders-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>رقم الطلب</th>
                                <th>العميل</th>
                                <th>الإجمالي</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th>تغيير الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    جاري تحميل الطلبات...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Order details modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تفاصيل الطلب</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="order-details-content" class="small">
                        تحميل...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const apiBase = document.querySelector('meta[name="api-base-url"]').content;
        const apiToken = document.querySelector('meta[name="api-token"]').content;

        function apiFetch(path, options = {}) {
            options.method = options.method || 'GET';
            options.headers = options.headers || {};
            options.headers['Authorization'] = 'Bearer ' + apiToken;
            options.headers['Accept'] = 'application/json';
            return fetch(apiBase + path, options).then(async (res) => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    throw data;
                }
                return data;
            });
        }

        const ordersTableBody = document.querySelector('#orders-table tbody');
        const statusFilter = document.getElementById('status-filter');
        const refreshBtn = document.getElementById('refresh-orders');
        const ordersCount = document.getElementById('orders-count');

        function statusBadge(status) {
            switch (status) {
                case 'completed':
                    return '<span class="badge bg-success">مكتمل</span>';
                case 'processing':
                    return '<span class="badge bg-warning text-dark">قيد المعالجة</span>';
                case 'cancelled':
                    return '<span class="badge bg-danger">ملغي</span>';
                default:
                    return '<span class="badge bg-secondary">قيد الانتظار</span>';
            }
        }

        function loadOrders() {
            ordersTableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted">جارٍ تحميل الطلبات...</td>
                </tr>`;

            apiFetch('/orders/Admin/GetAll')
                .then(res => {
                    const list = res.data || res; // OrderResource collection
                    const filtered = list.filter(o => {
                        if (!statusFilter.value) return true;
                        return o.status === statusFilter.value;
                    });

                    ordersCount.textContent = `عدد الطلبات: ${filtered.length}`;

                    if (!filtered.length) {
                        ordersTableBody.innerHTML = `
                            <tr><td colspan="8" class="text-center text-muted">لا توجد طلبات مطابقة</td></tr>`;
                        return;
                    }

                    ordersTableBody.innerHTML = '';
                    filtered.forEach((order, index) => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${index + 1}</td>
                            <td>${order.order_number || 'ORD-' + order.id}</td>
                            <td>${order.user?.name || 'مستخدم'}</td>
                            <td>${Number(order.total_amount).toFixed(2)} ج.م</td>
                            <td>${statusBadge(order.status)}</td>
                            <td>${order.created_at ? order.created_at.substring(0, 10) : ''}</td>
                            <td>
                                <select class="form-select form-select-sm order-status-select" data-id="${order.id}">
                                    <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>قيد الانتظار</option>
                                    <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>قيد المعالجة</option>
                                    <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>مكتمل</option>
                                    <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>ملغي</option>
                                </select>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-info order-view-btn" data-id="${order.id}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success order-update-status-btn" data-id="${order.id}">
                                        <i class="bi bi-check2-circle"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger order-delete-btn" data-id="${order.id}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        `;
                        ordersTableBody.appendChild(tr);
                    });
                })
                .catch(err => {
                    console.error(err);
                    ordersTableBody.innerHTML = `
                        <tr><td colspan="8" class="text-center text-danger">فشل تحميل الطلبات</td></tr>`;
                });
        }

        // Initial load
        loadOrders();

        refreshBtn.addEventListener('click', loadOrders);
        statusFilter.addEventListener('change', loadOrders);

        // Delegate actions (view / update status / delete)
        ordersTableBody.addEventListener('click', function(e) {
            const viewBtn = e.target.closest('.order-view-btn');
            const updateBtn = e.target.closest('.order-update-status-btn');
            const deleteBtn = e.target.closest('.order-delete-btn');

            if (viewBtn) {
                const id = viewBtn.dataset.id;
                const modalBody = document.getElementById('order-details-content');
                modalBody.innerHTML = 'تحميل...';
                apiFetch('/orders/Admin/' + id)
                    .then(order => {
                        const items = order.items || [];
                        let itemsHtml = '';
                        if (items.length) {
                            itemsHtml = `
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>المنتج</th>
                                                <th>اللون</th>
                                                <th>المقاس</th>
                                                <th>الكمية</th>
                                                <th>السعر</th>
                                                <th>الإجمالي</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${items.map(it => `
                                                                <tr>
                                                                    <td>${it.product?.name_ar || it.product?.name_en || ''}</td>
                                                                    <td>${it.variant?.color || '-'}</td>
                                                                    <td>${it.variant?.size || '-'}</td>
                                                                    <td>${it.quantity}</td>
                                                                    <td>${Number(it.price).toFixed(2)}</td>
                                                                    <td>${Number(it.subtotal).toFixed(2)}</td>
                                                                </tr>
                                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>`;
                        } else {
                            itemsHtml = '<div class="mt-2 text-muted">لا توجد عناصر للطلب.</div>';
                        }

                        modalBody.innerHTML = `
                            <div><strong>رقم الطلب:</strong> ${order.order_number || 'ORD-' + order.id}</div>
                            <div><strong>العميل:</strong> ${order.user?.name || ''}</div>
                            <div><strong>العنوان:</strong> ${order.address_title || ''} - ${order.address_details || ''}</div>
                            <div><strong>المحافظة:</strong> ${order.governorate_name || ''}</div>
                            <div><strong>الإجمالي:</strong> ${Number(order.total_amount).toFixed(2)} ج.م</div>
                            <div><strong>حالة الطلب:</strong> ${statusBadge(order.status)}</div>
                            <div><strong>ملاحظات:</strong> ${order.notes || '-'}</div>
                            ${itemsHtml}
                        `;

                        const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
                        modal.show();
                    })
                    .catch(() => {
                        modalBody.innerHTML = '<div class="text-danger">فشل تحميل تفاصيل الطلب.</div>';
                    });
            }

            if (updateBtn) {
                const id = updateBtn.dataset.id;
                const select = document.querySelector(`.order-status-select[data-id="${id}"]`);
                const newStatus = select.value;
                apiFetch('/orders/Admin/UpdateStatus/' + id, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        status: newStatus
                    }),
                }).then(() => loadOrders()).catch(console.error);
            }

            if (deleteBtn) {
                const id = deleteBtn.dataset.id;
                if (!confirm('هل أنت متأكد من حذف هذا الطلب (يجب أن يكون قيد الانتظار)؟')) return;
                apiFetch('/orders/Admin/Delete/' + id, {
                    method: 'DELETE',
                }).then(() => loadOrders()).catch(console.error);
            }
        });
    </script>
</body>

</html>
