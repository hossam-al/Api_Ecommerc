<?php

return [
    'meta' => [
        'title' => 'E-Commerce API Interface',
        'locale_label' => 'Language',
        'english' => 'English',
        'arabic' => 'Arabic',
    ],
    'nav' => [
        'overview' => 'Overview',
        'updates' => 'Latest Updates',
        'api_groups' => 'API Groups',
        'resources' => 'Resources',
    ],
    'hero' => [
        'badges' => [
            'api' => 'API v1',
            'sanctum' => 'Laravel Sanctum',
            'dashboard' => 'Dashboard',
        ],
        'title' => 'One API to run your store from the first product to the final order',
        'text' => 'This API powers a full e-commerce workflow, covering authentication, users, products, categories, brands, cart, addresses, coupons, and orders, with clear access levels for admins and sellers.',
        'meta' => [
            'base' => 'Base: /api/v1',
            'access' => 'Role-based access',
            'mobile' => 'Mobile-first',
        ],
        'actions' => [
            'api_groups' => 'Browse API groups',
            'dashboard' => 'Open dashboard login',
        ],
        'panel_title' => 'What the API provides now',
        'panel_items' => [
            'It provides authentication, registration, and protected user actions through Laravel Sanctum.',
            'It supports full catalog management for products, categories, brands, images, and activation states.',
            'It includes a complete purchase flow through cart, addresses, governorates, coupons, and orders.',
            'It separates customer, seller, and super admin capabilities, including analytics and advanced management routes.',
        ],
    ],
    'section_kickers' => [
        'latest_shape' => 'Latest Shape',
        'dashboard_flow' => 'Dashboard Flow',
        'api_groups' => 'API Groups',
        'quick_start' => 'Quick Start',
        'resources' => 'Resources',
    ],
    'feature_cards' => [
        [
            'icon' => 'bi-box-seam',
            'title' => 'Multilingual catalog',
            'description' => 'Products, categories, and brands support Arabic and English with active, featured, discount, and rating summary states.',
            'accent' => 'products',
        ],
        [
            'icon' => 'bi-cart3',
            'title' => 'Checkout-ready flow',
            'description' => 'The checkout flow now includes cart, addresses, governorates, coupons, and richer order details.',
            'accent' => 'orders',
        ],
        [
            'icon' => 'bi-person-badge',
            'title' => 'Clearer permissions',
            'description' => 'The current structure clearly separates user, seller, and super admin access with banning protection.',
            'accent' => 'users',
        ],
        [
            'icon' => 'bi-graph-up-arrow',
            'title' => 'Analytics and management',
            'description' => 'The admin dashboard and analytics endpoints now represent the latest management flow in the project.',
            'accent' => 'analytics',
        ],
    ],
    'latest_updates' => [
        'title' => 'What has been reflected in the page',
        'description' => 'Instead of the older limited documentation page, the current content now follows the real project structure and the latest UI additions.',
        'items' => [
            [
                'icon' => 'bi-tags',
                'title' => 'Full brands support',
                'description' => 'The catalog now includes full brand management across both the API and dashboard.',
            ],
            [
                'icon' => 'bi-pin-map',
                'title' => 'Governorates and addresses',
                'description' => 'Orders now support governorates and addresses, making the checkout flow closer to production behavior.',
            ],
            [
                'icon' => 'bi-ticket-perforated',
                'title' => 'Coupon workflow',
                'description' => 'Coupon validation and management endpoints are now part of the flow shown on the page.',
            ],
            [
                'icon' => 'bi-bag-check',
                'title' => 'Seller interfaces',
                'description' => 'Dedicated seller routes are now represented without mixing them with super admin actions.',
            ],
            [
                'icon' => 'bi-star-half',
                'title' => 'Reviews and moderation',
                'description' => 'Verified-purchase reviews, approval workflow, and public review visibility are now part of the documented flow.',
            ],
            [
                'icon' => 'bi-percent',
                'title' => 'Discount-aware pricing',
                'description' => 'Product responses now expose price, discount amount, final price, and active discount timing logic.',
            ],
            [
                'icon' => 'bi-shield-lock',
                'title' => 'Stronger user protection',
                'description' => 'The page now reflects not_banned protection and role-based middleware more accurately.',
            ],
            [
                'icon' => 'bi-bar-chart',
                'title' => 'Admin analytics',
                'description' => 'Summary and analytics endpoints for users, products, orders, and categories are now included.',
            ],
        ],
    ],
    'spotlight' => [
        'kicker' => 'Release Spotlight',
        'title' => 'Reviews and discounts now shape the catalog response',
        'description' => 'The latest backend additions let customers submit verified reviews and let products expose live discount pricing without changing the existing product endpoints.',
        'cards' => [
            [
                'icon' => 'bi-chat-square-text',
                'title' => 'Verified reviews',
                'description' => 'Customers can create one review per product after a completed purchase, optionally attach a variant, and wait for admin moderation before it becomes public.',
                'snippet' => "POST /api/v1/products/{id}/reviews\nPUT /api/v1/products/{id}/reviews\nDELETE /api/v1/products/{id}/reviews\nGET /api/v1/products/{id}/reviews",
            ],
            [
                'icon' => 'bi-cash-stack',
                'title' => 'Discount-safe pricing',
                'description' => 'Products now calculate has_discount, discount_amount, and final_price inside the valid date range while preventing negative prices.',
                'snippet' => "discount_type: percentage | fixed\ndiscount_value: number\ndiscount_start_at: nullable datetime\ndiscount_end_at: nullable datetime",
            ],
        ],
        'example' => [
            'title' => 'Product response snapshot',
            'snippet' => "{\n  \"price\": 500,\n  \"final_price\": 450,\n  \"has_discount\": true,\n  \"average_rating\": 4.5,\n  \"reviews_count\": 12\n}",
        ],
    ],
    'dashboard' => [
        'title' => 'Current dashboard flow',
        'description' => 'The landing page now mirrors the spirit of the dashboard that exists in the project, so the public entry screen no longer feels disconnected.',
        'items' => [
            [
                'icon' => 'bi-speedometer2',
                'title' => 'Owner dashboard',
                'description' => 'Quick stats, recent orders, users, products, and categories appear in one central owner view.',
            ],
            [
                'icon' => 'bi-box',
                'title' => 'Products management',
                'description' => 'Products now include brand, primary image, featured state, and active state management.',
            ],
            [
                'icon' => 'bi-folder',
                'title' => 'Categories management',
                'description' => 'Arabic and English category fields are now reflected with cleaner publishing and description states.',
            ],
            [
                'icon' => 'bi-card-checklist',
                'title' => 'Orders management',
                'description' => 'The current dashboard allows easier tracking, status updates, and richer order detail views.',
            ],
        ],
    ],
    'api_groups' => [
        'title' => 'Current API groups',
        'description' => 'These cards are based on the real routes in routes/api.php, not the old static documentation, so they match the latest project structure.',
        'search_placeholder' => 'Search inside API groups or endpoint paths...',
        'tags' => [
            'Updated routes',
            'Sanctum + Middleware',
            'Cards optimized for mobile',
        ],
        'empty' => 'No matching results were found for the current search.',
        'groups' => [
            [
                'title' => 'Authentication and user',
                'icon' => 'bi-shield-check',
                'description' => 'Register, login, update profile, logout, and account deletion endpoints.',
                'access' => ['Public', 'Sanctum', 'not_banned'],
                'items' => [
                    ['method' => 'POST', 'path' => '/api/v1/register', 'note' => 'Create a new account'],
                    ['method' => 'POST', 'path' => '/api/v1/login', 'note' => 'Login with rate limiting applied'],
                    ['method' => 'POST', 'path' => '/api/v1/logout', 'note' => 'End the active session'],
                    ['method' => 'POST', 'path' => '/api/v1/update', 'note' => 'Update the current user profile'],
                    ['method' => 'DELETE', 'path' => '/api/v1/deleteUser', 'note' => 'Delete the current account'],
                    ['method' => 'GET', 'path' => '/api/v1/users', 'note' => 'List users for super admin only'],
                ],
            ],
            [
                'title' => 'Public catalog',
                'icon' => 'bi-grid',
                'description' => 'Browse products, categories, brands, and governorates through the current public catalog layer.',
                'access' => ['Authenticated user', 'Super Admin for write actions'],
                'items' => [
                    ['method' => 'GET', 'path' => '/api/v1/products', 'note' => 'List public products'],
                    ['method' => 'GET', 'path' => '/api/v1/products/{id}/reviews', 'note' => 'List public approved reviews for a product'],
                    ['method' => 'POST', 'path' => '/api/v1/products/{id}/reviews', 'note' => 'Create a verified-purchase review'],
                    ['method' => 'GET', 'path' => '/api/v1/categories', 'note' => 'List current categories'],
                    ['method' => 'GET', 'path' => '/api/v1/brands', 'note' => 'List available brands'],
                    ['method' => 'GET', 'path' => '/api/v1/governorates', 'note' => 'List governorates and shipping areas'],
                    ['method' => 'POST', 'path' => '/api/v1/products', 'note' => 'Create a main product as super admin with optional discount fields'],
                    ['method' => 'PUT', 'path' => '/api/v1/products/{id}', 'note' => 'Update a main catalog product and pricing rules'],
                ],
            ],
            [
                'title' => 'Cart and checkout',
                'icon' => 'bi-basket2',
                'description' => 'Cart, addresses, coupons, and orders are now represented as one clearer checkout flow.',
                'access' => ['Sanctum', 'not_banned'],
                'items' => [
                    ['method' => 'GET', 'path' => '/api/v1/cart', 'note' => 'Load the current cart items'],
                    ['method' => 'POST', 'path' => '/api/v1/cart', 'note' => 'Add a product or variant to cart'],
                    ['method' => 'PUT', 'path' => '/api/v1/cart/{id}', 'note' => 'Update quantity or item state'],
                    ['method' => 'GET', 'path' => '/api/v1/addresses', 'note' => 'Manage shipping addresses'],
                    ['method' => 'POST', 'path' => '/api/v1/coupons/validate', 'note' => 'Validate a coupon code'],
                    ['method' => 'POST', 'path' => '/api/v1/orders', 'note' => 'Create a new order'],
                ],
            ],
            [
                'title' => 'Seller area',
                'icon' => 'bi-shop',
                'description' => 'Dedicated seller endpoints for products and orders without colliding with admin actions.',
                'access' => ['seller middleware'],
                'items' => [
                    ['method' => 'GET', 'path' => '/api/v1/seller/products', 'note' => 'List seller products'],
                    ['method' => 'POST', 'path' => '/api/v1/seller/products', 'note' => 'Create a seller product'],
                    ['method' => 'PUT', 'path' => '/api/v1/seller/products/{id}', 'note' => 'Update a seller product'],
                    ['method' => 'DELETE', 'path' => '/api/v1/seller/products/{id}', 'note' => 'Delete a seller product'],
                    ['method' => 'GET', 'path' => '/api/v1/seller/orders', 'note' => 'Follow seller orders'],
                ],
            ],
            [
                'title' => 'Admin and analytics',
                'icon' => 'bi-kanban',
                'description' => 'Super admin actions now include analytics, role assignment, pending products, and advanced order control.',
                'access' => ['super_admin'],
                'items' => [
                    ['method' => 'GET', 'path' => '/api/v1/admin/analytics/summary', 'note' => 'Load a system summary'],
                    ['method' => 'GET', 'path' => '/api/v1/admin/products/pending', 'note' => 'List pending products'],
                    ['method' => 'PUT', 'path' => '/api/v1/admin/products/{id}/activate', 'note' => 'Activate a pending product'],
                    ['method' => 'PATCH', 'path' => '/api/v1/admin/products/{productId}/reviews/{reviewId}/status', 'note' => 'Approve or reject a product review'],
                    ['method' => 'POST', 'path' => '/api/v1/roles/assign', 'note' => 'Assign a role to a user'],
                    ['method' => 'GET', 'path' => '/api/v1/orders/Admin/GetAll', 'note' => 'Load all orders for admin review'],
                    ['method' => 'POST', 'path' => '/api/v1/users/{id}/ban', 'note' => 'Ban a user from protected routes'],
                ],
            ],
        ],
    ],
    'quick_start' => [
        'title' => 'Quick start',
        'description' => 'Short snippets to start testing the API quickly while keeping the page clean and readable.',
        'items' => [
            [
                'title' => '1. Set the base URL',
                'snippet' => "BASE_URL=http://127.0.0.1:8000\nAPI_PREFIX=/api/v1",
            ],
            [
                'title' => '2. Login and get the token',
                'snippet' => "POST /api/v1/login\n{\n  \"email\": \"owner@example.com\",\n  \"password\": \"secret\"\n}",
            ],
            [
                'title' => '3. Use the token in protected requests',
                'snippet' => "Authorization: Bearer {token}\nAccept: application/json",
            ],
        ],
    ],
    'resources' => [
        'title' => 'Quick links and assets',
        'description' => 'Direct links to the currently available project files and the dashboard entry point.',
        'items' => [
            [
                'icon' => 'bi-file-earmark-text',
                'title' => 'API Documentation',
                'description' => 'Download the available markdown documentation shipped with the project.',
                'action' => 'Download documentation',
            ],
            [
                'icon' => 'bi-collection',
                'title' => 'Postman Collection',
                'description' => 'Download the ready-to-use Postman collection for quick endpoint testing.',
                'action' => 'Download Postman collection',
            ],
            [
                'icon' => 'bi-box-arrow-in-right',
                'title' => 'Dashboard',
                'description' => 'Open the current dashboard login screen used in the project.',
                'action' => 'Open login page',
            ],
        ],
    ],
    'footer' => 'The landing page is now closer to a production-ready experience in terms of structure, current visual style, and responsiveness across mobile, tablet, and desktop.',
];
