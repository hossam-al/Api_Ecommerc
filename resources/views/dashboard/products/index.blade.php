<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات</title>
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
            <h3>إدارة المنتجات</h3>
            <a href="{{ route('dashboard.owner') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-right"></i> العودة للداشبورد
            </a>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">إضافة منتج جديد</h5>
                    </div>
                    <div class="card-body">
                        <form id="product-create-form" enctype="multipart/form-data">
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
                            <div class="mb-2">
                                <label class="form-label">الفئة</label>
                                <select name="category_id" class="form-select">
                                    <option value="">بدون</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name_ar }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">البراند</label>
                                <select name="brand_id" class="form-select">
                                    <option value="">بدون</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name_ar }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">الصورة الرئيسية</label>
                                <input type="file" name="image_url" class="form-control" required>
                            </div>
                            <hr>
                            <h6 class="mb-2">بيانات المتغير (Variant) الأساسي</h6>
                            <div class="mb-2">
                                <label class="form-label">اللون</label>
                                <input type="text" name="variant_color" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">المقاس</label>
                                <input type="text" name="variant_size" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">السعر</label>
                                <input type="number" step="0.01" name="variant_price" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">الكمية في المخزون</label>
                                <input type="number" name="variant_stock" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">SKU (اختياري)</label>
                                <input type="text" name="variant_sku" class="form-control">
                            </div>
                            <div class="mb-2 form-check">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" checked>
                                <label for="is_active" class="form-check-label">مفعل</label>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_featured" id="is_featured"
                                    class="form-check-input">
                                <label for="is_featured" class="form-check-label">مميز</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">حفظ المنتج</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">قائمة المنتجات</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>الصورة</th>
                                        <th>الاسم</th>
                                        <th>الفئة</th>
                                        <th>البراند</th>
                                        <th>الحالة</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($products as $product)
                                        <tr>
                                            <td>
                                                @if ($product->primary_image)
                                                    <img src="{{ $product->primary_image }}" alt=""
                                                        style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $product->name_ar }}</div>
                                                <small class="text-muted">{{ $product->name_en }}</small>
                                            </td>
                                            <td>{{ $product->category->name_ar ?? '-' }}</td>
                                            <td>{{ $product->brand->name_ar ?? '-' }}</td>
                                            <td>
                                                <span
                                                    class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $product->is_active ? 'مفعل' : 'غير مفعل' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editProductModal{{ $product->id }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form class="product-delete-form"
                                                        data-id="{{ $product->id }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>

                                                <!-- Edit Modal -->
                                                <div class="modal fade"
                                                    id="editProductModal{{ $product->id }}" tabindex="-1"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">تعديل المنتج</h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"
                                                                    aria-label="Close"></button>
                                                            </div>
                                                            <form class="product-update-form"
                                                                data-id="{{ $product->id }}" enctype="multipart/form-data">
                                                                @csrf
                                                                <div class="modal-body">
                                                                    <div class="row">
                                                                        <div class="col-md-6 mb-2">
                                                                            <label class="form-label">الاسم
                                                                                بالعربية</label>
                                                                            <input type="text" name="name_ar"
                                                                                class="form-control"
                                                                                value="{{ $product->name_ar }}">
                                                                        </div>
                                                                        <div class="col-md-6 mb-2">
                                                                            <label class="form-label">الاسم
                                                                                بالإنجليزية</label>
                                                                            <input type="text" name="name_en"
                                                                                class="form-control"
                                                                                value="{{ $product->name_en }}">
                                                                        </div>
                                                                        <div class="col-md-6 mb-2">
                                                                            <label class="form-label">الوصف
                                                                                العربي</label>
                                                                            <textarea name="description_ar" class="form-control" rows="2">{{ $product->description_ar }}</textarea>
                                                                        </div>
                                                                        <div class="col-md-6 mb-2">
                                                                            <label class="form-label">الوصف
                                                                                الإنجليزي</label>
                                                                            <textarea name="description_en" class="form-control" rows="2">{{ $product->description_en }}</textarea>
                                                                        </div>
                                                                        <div class="col-md-6 mb-2">
                                                                            <label class="form-label">الفئة</label>
                                                                            <select name="category_id"
                                                                                class="form-select">
                                                                                <option value="">بدون</option>
                                                                                @foreach ($categories as $cat)
                                                                                    <option
                                                                                        value="{{ $cat->id }}"
                                                                                        @if ($product->category_id == $cat->id) selected @endif>
                                                                                        {{ $cat->name_ar }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-md-6 mb-2">
                                                                            <label class="form-label">البراند</label>
                                                                            <select name="brand_id"
                                                                                class="form-select">
                                                                                <option value="">بدون</option>
                                                                                @foreach ($brands as $brand)
                                                                                    <option
                                                                                        value="{{ $brand->id }}"
                                                                                        @if ($product->brand_id == $brand->id) selected @endif>
                                                                                        {{ $brand->name_ar }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-md-6 mb-2">
                                                                            <label class="form-label">الصورة
                                                                                الرئيسية</label>
                                                                            <input type="file" name="image_url"
                                                                                class="form-control">
                                                                        </div>
                                                                        <div class="col-md-6 mb-2 d-flex align-items-center gap-3 mt-4">
                                                                            <div class="form-check">
                                                                                <input type="checkbox" name="is_active"
                                                                                    id="is_active_{{ $product->id }}"
                                                                                    class="form-check-input"
                                                                                    {{ $product->is_active ? 'checked' : '' }}>
                                                                                <label
                                                                                    for="is_active_{{ $product->id }}"
                                                                                    class="form-check-label">مفعل</label>
                                                                            </div>
                                                                            <div class="form-check">
                                                                                <input type="checkbox"
                                                                                    name="is_featured"
                                                                                    id="is_featured_{{ $product->id }}"
                                                                                    class="form-check-input"
                                                                                    {{ $product->is_featured ? 'checked' : '' }}>
                                                                                <label
                                                                                    for="is_featured_{{ $product->id }}"
                                                                                    class="form-check-label">مميز</label>
                                                                            </div>
                                                                        </div>
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
                                            <td colspan="6" class="text-center">
                                                لا توجد منتجات حالياً
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div>
                            {{ $products->links() }}
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

        // Create product via API
        const productCreateForm = document.getElementById('product-create-form');
        productCreateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(productCreateForm);

            // Normalize boolean fields for Laravel "boolean" rule
            const isActive = formData.get('is_active') ? '1' : '0';
            const isFeatured = formData.get('is_featured') ? '1' : '0';
            formData.set('is_active', isActive);
            formData.set('is_featured', isFeatured);

            // Map variant fields to API-required structure
            formData.append('variants[0][color]', formData.get('variant_color'));
            formData.append('variants[0][size]', formData.get('variant_size'));
            formData.append('variants[0][price]', formData.get('variant_price'));
            formData.append('variants[0][stock]', formData.get('variant_stock'));
            if (formData.get('variant_sku')) {
                formData.append('variants[0][sku]', formData.get('variant_sku'));
            }

            // Clean helper-only fields so they don't conflict
            formData.delete('variant_color');
            formData.delete('variant_size');
            formData.delete('variant_price');
            formData.delete('variant_stock');
            formData.delete('variant_sku');

            apiFetch('/products', {
                method: 'POST',
                body: formData
            }).then(() => location.reload()).catch(console.error);
        });

        // Delete product via API
        document.querySelectorAll('.product-delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (!confirm('هل أنت متأكد من حذف المنتج؟')) return;
                const id = this.dataset.id;
                apiFetch('/products/' + id, {
                    method: 'DELETE',
                }).then(() => location.reload()).catch(console.error);
            });
        });

        // Update product (basic fields + optional image) via API
        document.querySelectorAll('.product-update-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const id = this.dataset.id;
                const formData = new FormData(this);

                // For update, we'll send JSON (without variants) for simplicity
                const payload = {
                    name_ar: formData.get('name_ar'),
                    name_en: formData.get('name_en'),
                    description_ar: formData.get('description_ar') || null,
                    description_en: formData.get('description_en') || null,
                    category_id: formData.get('category_id') || null,
                    brand_id: formData.get('brand_id') || null,
                    is_active: formData.get('is_active') ? true : false,
                    is_featured: formData.get('is_featured') ? true : false,
                };

                // If a new main image is selected, use multipart; otherwise send JSON only
                const hasImage = formData.get('image_url') && formData.get('image_url').name;

                if (hasImage) {
                    const fd = new FormData();
                    Object.keys(payload).forEach(key => {
                        if (payload[key] !== null) {
                            fd.append(key, payload[key]);
                        }
                    });
                    fd.append('image_url', formData.get('image_url'));

                    apiFetch('/products/' + id, {
                        method: 'POST', // fallback allowed in api.php using POST for updateOrder; for products we keep PUT
                        body: fd
                    }).then(() => location.reload()).catch(console.error);
                } else {
                    apiFetch('/products/' + id, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    }).then(() => location.reload()).catch(console.error);
                }
            });
        });
    </script>
</body>

</html>

