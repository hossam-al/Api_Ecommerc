<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم المالك</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f7fa;
        }

        img {
            max-width: 20%;

        }

        .sidebar {
            background-color: #2c3e50;
            min-height: 100vh;
            color: white;
        }

        .sidebar-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            transition: all 0.3s;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            border-right: 4px solid #e74c3c;
        }

        .card-stats {
            transition: transform 0.3s;
            border-right: 4px solid transparent;
        }

        .card-stats:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .card-stats.orders {
            border-right-color: #3498db;
        }

        .card-stats.products {
            border-right-color: #2ecc71;
        }

        .card-stats.users {
            border-right-color: #e74c3c;
        }

        .card-stats.categories {
            border-right-color: #f39c12;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 24px;
        }

        .bg-orders {
            background-color: rgba(52, 152, 219, 0.2);
            color: #3498db;
        }

        .bg-products {
            background-color: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
        }

        .bg-users {
            background-color: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
        }

        .bg-categories {
            background-color: rgba(243, 156, 18, 0.2);
            color: #f39c12;
        }

        .table-action-btn {
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .small-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2 col-md-3 px-0 sidebar">
                <div class="d-flex justify-content-center py-4">
                    <h4 class="fw-bold">لوحة تحكم المالك</h4>
                </div>
                <div class="mt-2">
                    <a href="{{ route('dashboard.owner') }}" class="sidebar-link active">
                        <i class="bi bi-speedometer2 ms-2"></i> الرئيسية
                    </a>
                    <a href="#users-section" class="sidebar-link">
                        <i class="bi bi-people ms-2"></i> المستخدمين
                    </a>
                    <a href="#admins-section" class="sidebar-link">
                        <i class="bi bi-person-badge ms-2"></i> المديرين
                    </a>
                    <a href="{{ route('dashboard.products.index') }}" class="sidebar-link">
                        <i class="bi bi-box ms-2"></i> المنتجات
                    </a>
                    <a href="{{ route('dashboard.categories.index') }}" class="sidebar-link">
                        <i class="bi bi-folder ms-2"></i> الفئات
                    </a>
                    <a href="{{ route('dashboard.brands.index') }}" class="sidebar-link">
                        <i class="bi bi-tags ms-2"></i> البراندات
                    </a>
                    <a href="{{ route('dashboard.orders.index') }}" class="sidebar-link">
                        <i class="bi bi-cart3 ms-2"></i> الطلبات
                    </a>
                    <a href="{{ route('dashboard.coupons.index') }}" class="sidebar-link">
                        <i class="bi bi-ticket-perforated ms-2"></i> الكوبونات
                    </a>
                    <a href="{{ route('dashboard.governorates.index') }}" class="sidebar-link">
                        <i class="bi bi-geo-alt ms-2"></i> المحافظات والشحن
                    </a>
                    <a href="#roles-section" class="sidebar-link">
                        <i class="bi bi-shield-lock ms-2"></i> الأدوار
                    </a>
                    <a href="#settings-section" class="sidebar-link">
                        <i class="bi bi-gear ms-2"></i> الإعدادات
                    </a>
                    <form action="{{ route('dashboard.owner.logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit"
                            class="sidebar-link mt-4 text-danger border-0 bg-transparent w-100 text-start">
                            <i class="bi bi-box-arrow-right ms-2"></i> تسجيل خروج
                        </button>
                    </form>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 col-md-9 ms-auto px-4 py-4" id="dashboard">
                <!-- Top Nav -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">لوحة التحكم</h2>
                    <div class="d-flex align-items-center">
                        <span class="me-3">مرحبًا، {{ $user->name }}</span>
                        <div class="dropdown">
                            <img src="{{ $user->image_url ?? 'https://via.placeholder.com/40' }}" alt="Profile"
                                class="rounded-circle"
                                style="width: 40px; height: 40px; object-fit: cover; cursor: pointer;"
                                data-bs-toggle="dropdown">
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> الملف
                                        الشخصي</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>
                                        الإعدادات</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form action="{{ route('dashboard.owner.logout') }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i> تسجيل خروج
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card card-stats orders h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title mb-1">الطلبات</h5>
                                        <h3 class="mb-0">{{ $stats['orders_count'] ?? 0 }}</h3>
                                        <p class="small text-muted mb-0">
                                            @if (isset($stats['orders_growth']) && $stats['orders_growth'] > 0)
                                                <span class="text-success">+{{ $stats['orders_growth'] }}%</span>
                                            @elseif(isset($stats['orders_growth']) && $stats['orders_growth'] < 0)
                                                <span class="text-danger">{{ $stats['orders_growth'] }}%</span>
                                            @else
                                                <span>{{ $stats['orders_growth'] ?? 0 }}%</span>
                                            @endif
                                            من الشهر الماضي
                                        </p>
                                    </div>
                                    <div class="stat-icon bg-orders">
                                        <i class="bi bi-cart3"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card card-stats products h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title mb-1">المنتجات</h5>
                                        <h3 class="mb-0">{{ $stats['products_count'] ?? 0 }}</h3>
                                        <p class="small text-muted mb-0">
                                            @if (isset($stats['products_growth']) && $stats['products_growth'] > 0)
                                                <span class="text-success">+{{ $stats['products_growth'] }}%</span>
                                            @elseif(isset($stats['products_growth']) && $stats['products_growth'] < 0)
                                                <span class="text-danger">{{ $stats['products_growth'] }}%</span>
                                            @else
                                                <span>{{ $stats['products_growth'] ?? 0 }}%</span>
                                            @endif
                                            من الشهر الماضي
                                        </p>
                                    </div>
                                    <div class="stat-icon bg-products">
                                        <i class="bi bi-box"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card card-stats users h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title mb-1">المستخدمين</h5>
                                        <h3 class="mb-0">{{ $stats['users_count'] ?? 0 }}</h3>
                                        <p class="small text-muted mb-0">
                                            @if (isset($stats['users_growth']) && $stats['users_growth'] > 0)
                                                <span class="text-success">+{{ $stats['users_growth'] }}%</span>
                                            @elseif(isset($stats['users_growth']) && $stats['users_growth'] < 0)
                                                <span class="text-danger">{{ $stats['users_growth'] }}%</span>
                                            @else
                                                <span>{{ $stats['users_growth'] ?? 0 }}%</span>
                                            @endif
                                            من الشهر الماضي
                                        </p>
                                    </div>
                                    <div class="stat-icon bg-users">
                                        <i class="bi bi-people"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card card-stats categories h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title mb-1">الفئات</h5>
                                        <h3 class="mb-0">{{ $stats['categories_count'] ?? 0 }}</h3>
                                        <p class="small text-muted mb-0">
                                            @if (isset($stats['categories_growth']) && $stats['categories_growth'] > 0)
                                                <span class="text-success">+{{ $stats['categories_growth'] }}%</span>
                                            @elseif(isset($stats['categories_growth']) && $stats['categories_growth'] < 0)
                                                <span class="text-danger">{{ $stats['categories_growth'] }}%</span>
                                            @else
                                                نفس العدد
                                            @endif
                                        </p>
                                    </div>
                                    <div class="stat-icon bg-categories">
                                        <i class="bi bi-folder"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="card mb-4" id="orders-section">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">أحدث الطلبات</h5>
                            <a href="#orders-section" class="btn btn-sm btn-primary">عرض الكل</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>رقم الطلب</th>
                                        <th>العميل</th>
                                        <th>المبلغ</th>
                                        <th>الحالة</th>
                                        <th>التاريخ</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recent_orders as $order)
                                        <tr>
                                            <td>{{ $order->order_number ?? 'ORD-' . $order->id }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $order->user->image_url ?? 'https://via.placeholder.com/35' }}"
                                                        class="small-avatar me-2">
                                                    <span>{{ $order->user->name ?? 'مستخدم' }}</span>
                                                </div>
                                            </td>
                                            <td>{{ number_format($order->total_amount, 2) }} ج.م</td>
                                            <td>
                                                @if ($order->status == 'completed')
                                                    <span class="badge bg-success status-badge">مكتمل</span>
                                                @elseif($order->status == 'processing')
                                                    <span class="badge bg-warning status-badge">قيد المعالجة</span>
                                                @elseif($order->status == 'cancelled')
                                                    <span class="badge bg-danger status-badge">ملغي</span>
                                                @else
                                                    <span class="badge bg-secondary status-badge">في الانتظار</span>
                                                @endif
                                            </td>
                                            <td>{{ $order->created_at ? $order->created_at->format('Y-m-d') : 'N/A' }}
                                            </td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-info table-action-btn me-1">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @if ($order->status != 'completed' && $order->status != 'cancelled')
                                                    <a href="#"
                                                        class="btn btn-sm btn-success table-action-btn me-1">
                                                        <i class="bi bi-check2"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">لا توجد طلبات حالياً</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Latest Users and Products -->
                <div class="row">
                    <!-- Latest Users -->
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header" id="users-section">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">أحدث المستخدمين</h5>
                                    <a href="#users-section" class="btn btn-sm btn-primary">عرض الكل</a>
                                </div>
                            </div>
                            <div class="card-body">
                                @forelse($recent_users as $recent_user)
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="{{ $recent_user->image_url ?? 'https://via.placeholder.com/40' }}"
                                            class="rounded-circle me-3">
                                        <div>
                                            <h6 class="mb-0">{{ $recent_user->name }}</h6>
                                            <small class="text-muted">{{ $recent_user->email }}</small>
                                        </div>
                                        <span
                                            class="badge ms-auto {{ $recent_user->role->slug == 'admin' ? 'bg-success' : ($recent_user->role->slug == 'super-admin' ? 'bg-danger' : 'bg-primary') }}">
                                            {{ $recent_user->role->name ?? 'عميل' }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="text-center py-3">
                                        لا يوجد مستخدمين جدد
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Latest Products -->
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header" id="products-section">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">أحدث المنتجات</h5>
                                    <a href="#products-section" class="btn btn-sm btn-primary">عرض الكل</a>
                                </div>
                            </div>
                            <div class="card-body">
                                @forelse($recent_products as $product)
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="{{ $product->image_url ?? 'https://via.placeholder.com/50x50' }}"
                                            class="rounded me-3"
                                            style="width: 50px; height: 50px; object-fit: cover;">
                                        <div>
                                            <h6 class="mb-0">{{ $product->name }}</h6>
                                            <small class="text-muted">الفئة:
                                                {{ $product->category->name ?? 'غير مصنف' }}</small>
                                        </div>
                                        <span class="ms-auto fw-bold">{{ number_format($product->price, 2) }}
                                            ج.م</span>
                                    </div>
                                @empty
                                    <div class="text-center py-3">
                                        لا توجد منتجات جديدة
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admins & Customers Overview -->
                <div class="row mt-4">
                    <!-- Admins -->
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100" id="admins-section">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">المديرين</h5>
                                <span class="badge bg-success">
                                    {{ $admins->count() }} من أصل {{ $stats['users_count'] ?? 0 }} مستخدم
                                </span>
                            </div>
                            <div class="card-body">
                                @forelse ($admins as $admin)
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="{{ $admin->image_url ?? 'https://via.placeholder.com/40' }}"
                                             class="rounded-circle me-3 small-avatar">
                                        <div>
                                            <h6 class="mb-0">{{ $admin->name }}</h6>
                                            <small class="text-muted">{{ $admin->email }}</small>
                                        </div>
                                        <span class="badge ms-auto bg-danger">
                                            {{ $admin->role->name ?? 'مدير' }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="text-center py-3">
                                        لا يوجد مديرين حاليًا
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Customers -->
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">العملاء</h5>
                                <span class="badge bg-primary">
                                    {{ $customers->count() }} عميل نشط
                                </span>
                            </div>
                            <div class="card-body">
                                @forelse ($customers as $customer)
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="{{ $customer->image_url ?? 'https://via.placeholder.com/40' }}"
                                             class="rounded-circle me-3 small-avatar">
                                        <div>
                                            <h6 class="mb-0">{{ $customer->name }}</h6>
                                            <small class="text-muted">{{ $customer->email }}</small>
                                        </div>
                                        <span class="badge ms-auto bg-secondary">
                                            عميل
                                        </span>
                                    </div>
                                @empty
                                    <div class="text-center py-3">
                                        لا يوجد عملاء مسجلين حتى الآن
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories Overview -->
                <div class="row mt-2" id="categories-section">
                    <div class="col-12 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">أهم الفئات</h5>
                                <span class="badge bg-warning text-dark">
                                    إجمالي الفئات: {{ $stats['categories_count'] ?? 0 }}
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>اسم الفئة</th>
                                                <th class="text-center">عدد المنتجات</th>
                                                <th class="text-center">تاريخ الإنشاء</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($top_categories as $cat)
                                                <tr>
                                                    <td>{{ $cat->name }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-info text-dark">
                                                            {{ $cat->products_count }} منتج
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        {{ $cat->created_at ? $cat->created_at->format('Y-m-d') : 'N/A' }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center">
                                                        لا توجد فئات مضافة حتى الآن
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Placeholder sections for roles & settings (for future expansion) -->
                <div class="row mt-2">
                    <div class="col-lg-6 mb-4" id="roles-section">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">إدارة الأدوار</h5>
                            </div>
                            <div class="card-body text-muted">
                                سيتم هنا لاحقاً إدارة الأدوار والصلاحيات (Super Admin, Admin, Customer وغيرها).
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4" id="settings-section">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">إعدادات النظام</h5>
                            </div>
                            <div class="card-body text-muted">
                                يمكن إضافة إعدادات عامة للمتجر، طرق الشحن، الضرائب، وغيرها في هذا القسم لاحقًا.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        console.log(@json($recent_users));

        // Make sidebar links active based on click
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.sidebar-link').forEach(el => {
                    el.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
    </script>
</body>

</html>
