<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة البراندات</title>
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
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>إدارة البراندات</h3>
            <a href="{{ route('dashboard.owner') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-right"></i> العودة للداشبورد
            </a>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">إضافة براند جديد</h5>
                    </div>
                    <div class="card-body">
                        <form id="brand-create-form">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">الاسم بالعربية</label>
                                <input type="text" name="name_ar" class="form-control" required
                                    value="{{ old('name_ar') }}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">الاسم بالإنجليزية</label>
                                <input type="text" name="name_en" class="form-control" required
                                    value="{{ old('name_en') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">رابط اللوجو (اختياري)</label>
                                <input type="text" name="logo" class="form-control"
                                    value="{{ old('logo') }}">
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input"
                                    checked>
                                <label for="is_active" class="form-check-label">مفعل</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">حفظ البراند</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">قائمة البراندات</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>اللوجو</th>
                                        <th>الاسم</th>
                                        <th>الحالة</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($brands as $brand)
                                        <tr>
                                            <td>
                                                @if ($brand->logo)
                                                    <img src="{{ $brand->logo }}" alt=""
                                                        style="width:50px;height:50px;object-fit:contain;">
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $brand->name_ar }}</div>
                                                <small class="text-muted">{{ $brand->name_en }}</small>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge {{ $brand->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $brand->is_active ? 'مفعل' : 'غير مفعل' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editBrandModal{{ $brand->id }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form class="brand-delete-form" data-id="{{ $brand->id }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>

                                                <!-- Edit Modal -->
                                                <div class="modal fade" id="editBrandModal{{ $brand->id }}"
                                                    tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">تعديل البراند</h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"
                                                                    aria-label="Close"></button>
                                                            </div>
                                                            <form class="brand-update-form"
                                                                data-id="{{ $brand->id }}">
                                                                @csrf
                                                                <div class="modal-body">
                                                                    <div class="mb-2">
                                                                        <label class="form-label">الاسم
                                                                            بالعربية</label>
                                                                        <input type="text" name="name_ar"
                                                                            class="form-control"
                                                                            value="{{ $brand->name_ar }}">
                                                                    </div>
                                                                    <div class="mb-2">
                                                                        <label class="form-label">الاسم
                                                                            بالإنجليزية</label>
                                                                        <input type="text" name="name_en"
                                                                            class="form-control"
                                                                            value="{{ $brand->name_en }}">
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">رابط
                                                                            اللوجو</label>
                                                                        <input type="text" name="logo"
                                                                            class="form-control"
                                                                            value="{{ $brand->logo }}">
                                                                    </div>
                                                                    <div class="mb-3 form-check">
                                                                        <input type="checkbox" name="is_active"
                                                                            id="is_active_{{ $brand->id }}"
                                                                            class="form-check-input"
                                                                            {{ $brand->is_active ? 'checked' : '' }}>
                                                                        <label
                                                                            for="is_active_{{ $brand->id }}"
                                                                            class="form-check-label">مفعل</label>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">إلغاء</button>
                                                                    <button type="submit"
                                                                        class="btn btn-primary">حفظ
                                                                        التعديلات</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">
                                                لا توجد براندات حالياً
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div>
                            {{ $brands->links() }}
                        </div>
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

        // Create brand
        const brandCreateForm = document.getElementById('brand-create-form');
        brandCreateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(brandCreateForm);
            const payload = {
                name_ar: formData.get('name_ar'),
                name_en: formData.get('name_en'),
                logo: formData.get('logo') || null,
                is_active: formData.get('is_active') ? true : false,
            };
            apiFetch('/brands', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            }).then(() => location.reload()).catch(console.error);
        });

        // Delete brand
        document.querySelectorAll('.brand-delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (!confirm('هل أنت متأكد من حذف البراند؟')) return;
                const id = this.dataset.id;
                apiFetch('/brands/' + id, {
                    method: 'DELETE',
                }).then(() => location.reload()).catch(console.error);
            });
        });

        // Update brand
        document.querySelectorAll('.brand-update-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const id = this.dataset.id;
                const formData = new FormData(this);
                const payload = {
                    name_ar: formData.get('name_ar'),
                    name_en: formData.get('name_en'),
                    logo: formData.get('logo') || null,
                    is_active: formData.get('is_active') ? true : false,
                };
                apiFetch('/brands/' + id, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                }).then(() => location.reload()).catch(console.error);
            });
        });
    </script>
</body>

</html>

