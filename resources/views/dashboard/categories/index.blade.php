<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الفئات</title>
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
            <h3>إدارة الفئات</h3>
            <a href="{{ route('dashboard.owner') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-right"></i> العودة للداشبورد
            </a>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">إضافة فئة جديدة</h5>
                    </div>
                    <div class="card-body">
                        <form id="category-create-form">
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
                            <div class="mb-2">
                                <label class="form-label">الوصف العربي</label>
                                <textarea name="description_ar" class="form-control" rows="2">{{ old('description_ar') }}</textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">الوصف الإنجليزي</label>
                                <textarea name="description_en" class="form-control" rows="2">{{ old('description_en') }}</textarea>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input"
                                    checked>
                                <label for="is_active" class="form-check-label">مفعلة</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">حفظ الفئة</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">قائمة الفئات</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>الاسم</th>
                                        <th>الوصف</th>
                                        <th>الحالة</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($categories as $category)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $category->name_ar }}</div>
                                                <small class="text-muted">{{ $category->name_en }}</small>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $category->description_ar ?? '-' }}
                                                </small>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $category->is_active ? 'مفعلة' : 'غير مفعلة' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editCategoryModal{{ $category->id }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form class="category-delete-form"
                                                        data-id="{{ $category->id }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>

                                                <!-- Edit Modal -->
                                                <div class="modal fade"
                                                    id="editCategoryModal{{ $category->id }}"
                                                    tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">تعديل الفئة</h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"
                                                                    aria-label="Close"></button>
                                                            </div>
                                                            <form class="category-update-form"
                                                                data-id="{{ $category->id }}">
                                                                @csrf
                                                                <div class="modal-body">
                                                                    <div class="mb-2">
                                                                        <label class="form-label">الاسم
                                                                            بالعربية</label>
                                                                        <input type="text" name="name_ar"
                                                                            class="form-control"
                                                                            value="{{ $category->name_ar }}">
                                                                    </div>
                                                                    <div class="mb-2">
                                                                        <label class="form-label">الاسم
                                                                            بالإنجليزية</label>
                                                                        <input type="text" name="name_en"
                                                                            class="form-control"
                                                                            value="{{ $category->name_en }}">
                                                                    </div>
                                                                    <div class="mb-2">
                                                                        <label class="form-label">الوصف
                                                                            العربي</label>
                                                                        <textarea name="description_ar" class="form-control" rows="2">{{ $category->description_ar }}</textarea>
                                                                    </div>
                                                                    <div class="mb-2">
                                                                        <label class="form-label">الوصف
                                                                            الإنجليزي</label>
                                                                        <textarea name="description_en" class="form-control" rows="2">{{ $category->description_en }}</textarea>
                                                                    </div>
                                                                    <div class="mb-3 form-check">
                                                                        <input type="checkbox" name="is_active"
                                                                            id="is_active_{{ $category->id }}"
                                                                            class="form-check-input"
                                                                            {{ $category->is_active ? 'checked' : '' }}>
                                                                        <label
                                                                            for="is_active_{{ $category->id }}"
                                                                            class="form-check-label">مفعلة</label>
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
                                                لا توجد فئات حالياً
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div>
                            {{ $categories->links() }}
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

        // Create
        const createCategoryForm = document.getElementById('category-create-form');
        createCategoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(createCategoryForm);
            const payload = {
                name_ar: formData.get('name_ar'),
                name_en: formData.get('name_en'),
                description_ar: formData.get('description_ar') || null,
                description_en: formData.get('description_en') || null,
                is_active: formData.get('is_active') ? true : false,
            };
            apiFetch('/categories', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            }).then(() => location.reload()).catch(console.error);
        });

        // Delete
        document.querySelectorAll('.category-delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (!confirm('هل أنت متأكد من حذف الفئة؟')) return;
                const id = this.dataset.id;
                apiFetch('/categories/' + id, {
                    method: 'DELETE',
                }).then(() => location.reload()).catch(console.error);
            });
        });

        // Update
        document.querySelectorAll('.category-update-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const id = this.dataset.id;
                const formData = new FormData(this);
                const payload = {
                    name_ar: formData.get('name_ar'),
                    name_en: formData.get('name_en'),
                    description_ar: formData.get('description_ar') || null,
                    description_en: formData.get('description_en') || null,
                    is_active: formData.get('is_active') ? true : false,
                };
                apiFetch('/categories/' + id, {
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

