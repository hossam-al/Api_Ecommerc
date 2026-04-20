<?php

return [
    'meta' => [
        'title' => 'واجهة API للتجارة الإلكترونية',
        'locale_label' => 'اللغة',
        'english' => 'English',
        'arabic' => 'العربية',
    ],
    'nav' => [
        'overview' => 'نظرة عامة',
        'updates' => 'آخر التحديثات',
        'api_groups' => 'مجموعات الـ API',
        'resources' => 'الموارد',
    ],
    'hero' => [
        'badges' => [
            'api' => 'API v1',
            'sanctum' => 'Laravel Sanctum',
            'dashboard' => 'لوحة التحكم',
        ],
        'title' => 'API واحد يدير متجرك من أول منتج لحد آخر طلب',
        'text' => 'هذا الـ API مخصص لإدارة متجر إلكتروني متكامل، بداية من المصادقة والمستخدمين، مرورًا بالمنتجات والفئات والبراندات، وحتى السلة والعناوين والكوبونات والطلبات، مع صلاحيات واضحة للإدارة والبائعين.',
        'meta' => [
            'base' => 'الأساس: /api/v1',
            'access' => 'حماية بالصلاحيات',
            'mobile' => 'مخصص للموبايل أولاً',
        ],
        'actions' => [
            'api_groups' => 'استعراض مجموعات الـ API',
            'dashboard' => 'فتح صفحة دخول لوحة التحكم',
        ],
        'panel_title' => 'ماذا يقدم الـ API الآن',
        'panel_items' => [
            'يوفر تسجيل الدخول والتسجيل وإدارة المستخدمين باستخدام Laravel Sanctum وحماية للمسارات المحمية.',
            'يدعم إدارة الكتالوج الكامل: المنتجات، الفئات، البراندات، الصور، وحالات التفعيل والتمييز.',
            'يشمل دورة شراء كاملة تبدأ من السلة والعناوين والمحافظات والكوبونات وتنتهي بإنشاء الطلبات ومتابعتها.',
            'يفصل بين صلاحيات المستخدم العادي والبائع و Super Admin، مع مسارات خاصة بالتحليلات والإدارة المتقدمة.',
        ],
    ],
    'section_kickers' => [
        'latest_shape' => 'أحدث شكل',
        'dashboard_flow' => 'منطق لوحة التحكم',
        'api_groups' => 'مجموعات الـ API',
        'quick_start' => 'بداية سريعة',
        'resources' => 'الموارد',
    ],
    'feature_cards' => [
        [
            'icon' => 'bi-box-seam',
            'title' => 'كتالوج متعدد اللغات',
            'description' => 'المنتجات والفئات والبراندات تدعم العربية والإنجليزية مع حالات التفعيل والتمييز والخصم وملخص التقييمات.',
            'accent' => 'products',
        ],
        [
            'icon' => 'bi-cart3',
            'title' => 'مسار جاهز للشراء',
            'description' => 'تجربة الـ checkout الحالية تشمل السلة والعناوين والمحافظات والكوبونات وبيانات الطلب بتفاصيل أوضح.',
            'accent' => 'orders',
        ],
        [
            'icon' => 'bi-person-badge',
            'title' => 'صلاحيات أوضح',
            'description' => 'الهيكل الحالي يفصل بشكل واضح بين المستخدم والبائع و Super Admin مع حماية الحظر.',
            'accent' => 'users',
        ],
        [
            'icon' => 'bi-graph-up-arrow',
            'title' => 'تحليلات وإدارة',
            'description' => 'لوحة الإدارة ومسارات التحليلات تعكس الآن أحدث منطق لإدارة المتجر والعمليات.',
            'accent' => 'analytics',
        ],
    ],
    'latest_updates' => [
        'title' => 'ما الذي تم عكسه داخل الصفحة',
        'description' => 'بدل الصفحة القديمة المحدودة، أصبح المحتوى الحالي مبنيًا على البنية الفعلية للمشروع والواجهات المضافة حديثًا.',
        'items' => [
            [
                'icon' => 'bi-tags',
                'title' => 'دعم كامل للبراندات',
                'description' => 'الكتالوج الحالي أصبح يشمل إدارة البراندات بالكامل داخل الـ API ولوحة التحكم.',
            ],
            [
                'icon' => 'bi-pin-map',
                'title' => 'المحافظات والعناوين',
                'description' => 'الطلبات تدعم الآن المحافظات والعناوين، وهذا يجعل تجربة الشراء أقرب لسلوك الإنتاج الفعلي.',
            ],
            [
                'icon' => 'bi-ticket-perforated',
                'title' => 'منظومة الكوبونات',
                'description' => 'أصبحت الصفحة تعكس وجود التحقق من الكوبونات وإدارتها ضمن تدفق الشراء.',
            ],
            [
                'icon' => 'bi-bag-check',
                'title' => 'واجهات البائع',
                'description' => 'تم تمثيل مسارات البائع بشكل مستقل بدون خلطها مع إجراءات المشرف الأعلى.',
            ],
            [
                'icon' => 'bi-star-half',
                'title' => 'التقييمات والمراجعة',
                'description' => 'تمت إضافة تقييمات الشراء المؤكد مع دورة اعتماد ورفض قبل إظهارها بشكل عام داخل الكتالوج.',
            ],
            [
                'icon' => 'bi-percent',
                'title' => 'تسعير الخصومات',
                'description' => 'استجابات المنتجات أصبحت تعرض السعر الأساسي وقيمة الخصم والسعر النهائي ومنطق صلاحية الخصم الزمني.',
            ],
            [
                'icon' => 'bi-shield-lock',
                'title' => 'حماية أقوى للمستخدمين',
                'description' => 'أصبحت الصفحة تعكس middleware الحظر والصلاحيات بشكل أدق داخل المنظومة الحالية.',
            ],
            [
                'icon' => 'bi-bar-chart',
                'title' => 'التحليلات الإدارية',
                'description' => 'تم إدراج مسارات الملخصات والتحليلات الخاصة بالمستخدمين والمنتجات والطلبات والفئات.',
            ],
        ],
    ],
    'spotlight' => [
        'kicker' => 'أبرز الإضافات',
        'title' => 'التقييمات والخصومات أصبحت جزءًا من استجابة المنتج',
        'description' => 'الإضافات الأخيرة داخل الـ backend تسمح للعميل بإرسال تقييم موثّق بعد الشراء، وتسمح للمنتج بعرض التسعير بعد الخصم بدون كسر المسارات الحالية.',
        'cards' => [
            [
                'icon' => 'bi-chat-square-text',
                'title' => 'تقييمات موثقة',
                'description' => 'يمكن للعميل إنشاء تقييم واحد لكل منتج بعد طلب مكتمل، مع إمكانية ربط Variant واختيار تعليق اختياري، ثم انتظار اعتماد الإدارة قبل الظهور العام.',
                'snippet' => "POST /api/v1/products/{id}/reviews\nPUT /api/v1/products/{id}/reviews\nDELETE /api/v1/products/{id}/reviews\nGET /api/v1/products/{id}/reviews",
            ],
            [
                'icon' => 'bi-cash-stack',
                'title' => 'تسعير آمن للخصومات',
                'description' => 'المنتج يحسب has_discount و discount_amount و final_price داخل فترة الخصم الصحيحة مع منع نزول السعر إلى قيمة سالبة.',
                'snippet' => "discount_type: percentage | fixed\ndiscount_value: رقم\ndiscount_start_at: datetime اختياري\ndiscount_end_at: datetime اختياري",
            ],
        ],
        'example' => [
            'title' => 'مثال من استجابة المنتج',
            'snippet' => "{\n  \"price\": 500,\n  \"final_price\": 450,\n  \"has_discount\": true,\n  \"average_rating\": 4.5,\n  \"reviews_count\": 12\n}",
        ],
    ],
    'dashboard' => [
        'title' => 'منطق الواجهة الحالية في لوحة التحكم',
        'description' => 'الصفحة الرئيسية أصبحت أقرب لروح لوحة التحكم الموجودة فعليًا داخل المشروع، ولم تعد تبدو منفصلة عنها.',
        'items' => [
            [
                'icon' => 'bi-speedometer2',
                'title' => 'لوحة المالك',
                'description' => 'إحصائيات سريعة وطلبات حديثة ومستخدمون ومنتجات وفئات داخل شاشة موحدة.',
            ],
            [
                'icon' => 'bi-box',
                'title' => 'إدارة المنتجات',
                'description' => 'المنتجات أصبحت تشمل البراند والصورة الرئيسية وحالة التمييز وحالة التفعيل.',
            ],
            [
                'icon' => 'bi-folder',
                'title' => 'إدارة الفئات',
                'description' => 'الفئات العربية والإنجليزية أصبحت تظهر بوضوح مع الوصف وحالة النشر الحالية.',
            ],
            [
                'icon' => 'bi-card-checklist',
                'title' => 'إدارة الطلبات',
                'description' => 'اللوحة الحالية تتيح متابعة أسهل للطلبات وتغيير الحالة وقراءة التفاصيل بشكل أفضل.',
            ],
        ],
    ],
    'api_groups' => [
        'title' => 'مجموعات الـ API الحالية',
        'description' => 'هذه البطاقات مبنية على المسارات الحقيقية داخل routes/api.php وليس على التوثيق القديم، لذلك فهي تعكس البنية الأحدث للمشروع.',
        'search_placeholder' => 'ابحث داخل مجموعات الـ API أو المسارات...',
        'tags' => [
            'مسارات محدثة',
            'Sanctum + Middleware',
            'بطاقات مناسبة للموبايل',
        ],
        'empty' => 'لا توجد نتائج مطابقة لعبارة البحث الحالية.',
        'groups' => [
            [
                'title' => 'المصادقة والمستخدم',
                'icon' => 'bi-shield-check',
                'description' => 'مسارات التسجيل وتسجيل الدخول وتحديث الملف الشخصي وتسجيل الخروج وحذف الحساب.',
                'access' => ['عام', 'Sanctum', 'not_banned'],
                'items' => [
                    ['method' => 'POST', 'path' => '/api/v1/register', 'note' => 'إنشاء حساب جديد'],
                    ['method' => 'POST', 'path' => '/api/v1/login', 'note' => 'تسجيل الدخول مع تقييد المعدل'],
                    ['method' => 'POST', 'path' => '/api/v1/logout', 'note' => 'إنهاء الجلسة الحالية'],
                    ['method' => 'POST', 'path' => '/api/v1/update', 'note' => 'تحديث ملف المستخدم الحالي'],
                    ['method' => 'DELETE', 'path' => '/api/v1/deleteUser', 'note' => 'حذف الحساب الحالي'],
                    ['method' => 'GET', 'path' => '/api/v1/users', 'note' => 'عرض المستخدمين للمشرف الأعلى فقط'],
                ],
            ],
            [
                'title' => 'الكتالوج العام',
                'icon' => 'bi-grid',
                'description' => 'عرض المنتجات والفئات والبراندات والمحافظات ضمن طبقة الكتالوج الحالية.',
                'access' => ['مستخدم مسجل', 'Super Admin للكتابة'],
                'items' => [
                    ['method' => 'GET', 'path' => '/api/v1/products', 'note' => 'عرض المنتجات العامة'],
                    ['method' => 'GET', 'path' => '/api/v1/products/{id}/reviews', 'note' => 'عرض التقييمات المعتمدة الخاصة بالمنتج'],
                    ['method' => 'POST', 'path' => '/api/v1/products/{id}/reviews', 'note' => 'إرسال تقييم مرتبط بشراء مؤكد'],
                    ['method' => 'GET', 'path' => '/api/v1/categories', 'note' => 'عرض الفئات الحالية'],
                    ['method' => 'GET', 'path' => '/api/v1/brands', 'note' => 'عرض البراندات المتاحة'],
                    ['method' => 'GET', 'path' => '/api/v1/governorates', 'note' => 'عرض المحافظات ومناطق الشحن'],
                    ['method' => 'POST', 'path' => '/api/v1/products', 'note' => 'إنشاء منتج رئيسي مع حقول خصم اختيارية'],
                    ['method' => 'PUT', 'path' => '/api/v1/products/{id}', 'note' => 'تعديل منتج رئيسي وقواعد التسعير الخاصة به'],
                ],
            ],
            [
                'title' => 'السلة والشراء',
                'icon' => 'bi-basket2',
                'description' => 'السلة والعناوين والكوبونات والطلبات أصبحت ممثلة كمسار شراء أوضح داخل الصفحة.',
                'access' => ['Sanctum', 'not_banned'],
                'items' => [
                    ['method' => 'GET', 'path' => '/api/v1/cart', 'note' => 'جلب عناصر السلة الحالية'],
                    ['method' => 'POST', 'path' => '/api/v1/cart', 'note' => 'إضافة منتج أو variant إلى السلة'],
                    ['method' => 'PUT', 'path' => '/api/v1/cart/{id}', 'note' => 'تعديل الكمية أو حالة العنصر'],
                    ['method' => 'GET', 'path' => '/api/v1/addresses', 'note' => 'إدارة عناوين الشحن'],
                    ['method' => 'POST', 'path' => '/api/v1/coupons/validate', 'note' => 'التحقق من كود الكوبون'],
                    ['method' => 'POST', 'path' => '/api/v1/orders', 'note' => 'إنشاء طلب جديد'],
                ],
            ],
            [
                'title' => 'منطقة البائع',
                'icon' => 'bi-shop',
                'description' => 'مسارات مخصصة للبائع لإدارة المنتجات والطلبات بدون تعارض مع إدارة المشرف الأعلى.',
                'access' => ['seller middleware'],
                'items' => [
                    ['method' => 'GET', 'path' => '/api/v1/seller/products', 'note' => 'عرض منتجات البائع'],
                    ['method' => 'POST', 'path' => '/api/v1/seller/products', 'note' => 'إنشاء منتج جديد للبائع'],
                    ['method' => 'PUT', 'path' => '/api/v1/seller/products/{id}', 'note' => 'تحديث منتج البائع'],
                    ['method' => 'DELETE', 'path' => '/api/v1/seller/products/{id}', 'note' => 'حذف منتج البائع'],
                    ['method' => 'GET', 'path' => '/api/v1/seller/orders', 'note' => 'متابعة طلبات البائع'],
                ],
            ],
            [
                'title' => 'الإدارة والتحليلات',
                'icon' => 'bi-kanban',
                'description' => 'تشمل صلاحيات المشرف الأعلى التحليلات وإسناد الأدوار وإدارة المنتجات المعلقة والتحكم المتقدم في الطلبات.',
                'access' => ['super_admin'],
                'items' => [
                    ['method' => 'GET', 'path' => '/api/v1/admin/analytics/summary', 'note' => 'تحميل ملخص النظام'],
                    ['method' => 'GET', 'path' => '/api/v1/admin/products/pending', 'note' => 'عرض المنتجات المعلقة'],
                    ['method' => 'PUT', 'path' => '/api/v1/admin/products/{id}/activate', 'note' => 'تفعيل منتج معلق'],
                    ['method' => 'PATCH', 'path' => '/api/v1/admin/products/{productId}/reviews/{reviewId}/status', 'note' => 'اعتماد أو رفض تقييم منتج'],
                    ['method' => 'POST', 'path' => '/api/v1/roles/assign', 'note' => 'إسناد دور لمستخدم'],
                    ['method' => 'GET', 'path' => '/api/v1/orders/Admin/GetAll', 'note' => 'عرض جميع الطلبات للمراجعة الإدارية'],
                    ['method' => 'POST', 'path' => '/api/v1/users/{id}/ban', 'note' => 'حظر مستخدم من الوصول للمسارات المحمية'],
                ],
            ],
        ],
    ],
    'quick_start' => [
        'title' => 'بداية سريعة',
        'description' => 'مقتطفات قصيرة تساعد على بدء اختبار الـ API بسرعة مع بقاء الصفحة منظمة وسهلة القراءة.',
        'items' => [
            [
                'title' => '1. اضبط الـ Base URL',
                'snippet' => "BASE_URL=http://127.0.0.1:8000\nAPI_PREFIX=/api/v1",
            ],
            [
                'title' => '2. سجّل الدخول وخذ التوكن',
                'snippet' => "POST /api/v1/login\n{\n  \"email\": \"owner@example.com\",\n  \"password\": \"secret\"\n}",
            ],
            [
                'title' => '3. استخدم التوكن في الطلبات المحمية',
                'snippet' => "Authorization: Bearer {token}\nAccept: application/json",
            ],
        ],
    ],
    'resources' => [
        'title' => 'روابط سريعة وملفات جاهزة',
        'description' => 'روابط مباشرة للملفات المتاحة حاليًا داخل المشروع ونقطة الدخول إلى لوحة التحكم.',
        'items' => [
            [
                'icon' => 'bi-file-earmark-text',
                'title' => 'توثيق الـ API',
                'description' => 'تحميل نسخة التوثيق الحالية الموجودة داخل المشروع بصيغة markdown.',
                'action' => 'تنزيل التوثيق',
            ],
            [
                'icon' => 'bi-collection',
                'title' => 'مجموعة Postman',
                'description' => 'تحميل مجموعة Postman الجاهزة لتجربة المسارات بسرعة.',
                'action' => 'تنزيل مجموعة Postman',
            ],
            [
                'icon' => 'bi-box-arrow-in-right',
                'title' => 'لوحة التحكم',
                'description' => 'فتح شاشة تسجيل الدخول الحالية الخاصة بلوحة التحكم المستخدمة في المشروع.',
                'action' => 'فتح صفحة الدخول',
            ],
        ],
    ],
    'footer' => 'صفحة البداية أصبحت أقرب لتجربة Production من حيث الترتيب، والاتساق مع الستايل الحالي، والاستجابة على الموبايل والتابلت والديسكتوب.',
];
