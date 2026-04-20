@php
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
    } catch (\Exception $e) {
        throw new \Exception('Database connection failed: ' . $e->getMessage());
    }

    $page = trans('welcome');
    $featureCards = $page['feature_cards'];
    $latestUpdates = $page['latest_updates']['items'];
    $dashboardSections = $page['dashboard']['items'];
    $apiGroups = $page['api_groups']['groups'];
    $quickStartSteps = $page['quick_start']['items'];
    $resourceCards = $page['resources']['items'];
    $spotlightCards = $page['spotlight']['cards'] ?? [];
    $spotlightExample = $page['spotlight']['example'] ?? null;
    $resourceTargets = [
        ['href' => asset('API-DOCUMENTATION.md'), 'download' => true],
        ['href' => asset('E-Commerce-API.postman_collection.json'), 'download' => true],
        ['href' => route('dashboard.login'), 'download' => false],
    ];

    foreach ($resourceCards as $index => $resourceCard) {
        $resourceCards[$index] = array_merge($resourceCard, $resourceTargets[$index] ?? []);
    }

    $currentLocale = $currentLocale ?? app()->getLocale();
    $isRtl = $isRtl ?? $currentLocale === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page['meta']['title'] }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --navy: #2c3e50;
            --navy-soft: #34495e;
            --purple: #764ba2;
            --purple-soft: #667eea;
            --surface: #ffffff;
            --surface-soft: #f5f7fa;
            --border: rgba(44, 62, 80, 0.10);
            --text: #203040;
            --muted: #667085;
            --danger: #e74c3c;
            --success: #2ecc71;
            --warning: #f39c12;
            --info: #3498db;
            --shadow: 0 18px 50px rgba(28, 37, 54, 0.12);
            --font-ltr: 'Inter', 'Segoe UI', sans-serif;
            --font-rtl: 'Tajawal', sans-serif;
            --card-gap: clamp(1rem, 1vw + 0.85rem, 1.5rem);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            max-width: 100%;
            overflow-x: hidden;
        }

        body {
            margin: 0;
            min-width: 320px;
            font-family: var(--font-ltr);
            color: var(--text);
            text-align: start;
            background:
                radial-gradient(circle at top right, rgba(118, 75, 162, 0.18), transparent 26%),
                radial-gradient(circle at bottom left, rgba(102, 126, 234, 0.18), transparent 24%),
                var(--surface-soft);
        }

        html[dir="rtl"] body {
            font-family: var(--font-rtl);
        }

        img,
        svg {
            max-width: 100%;
            height: auto;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        code,
        pre {
            font-family: Consolas, Monaco, monospace;
        }

        .topbar {
            background: rgba(44, 62, 80, 0.96);
            backdrop-filter: blur(14px);
            box-shadow: 0 10px 25px rgba(19, 28, 40, 0.16);
            direction: ltr;
        }

        .topbar .navbar-brand {
            font-weight: 800;
            letter-spacing: 0;
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
        }

        .topbar .nav-link {
            color: rgba(255, 255, 255, 0.88);
            font-weight: 600;
        }

        .topbar .nav-link:hover,
        .topbar .nav-link:focus {
            color: #fff;
        }

        .topbar .nav-wrapper {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            width: 100%;
        }

        .topbar .container,
        .topbar .navbar-collapse,
        .topbar .navbar-nav {
            direction: ltr;
        }

        .topbar .navbar-nav {
            gap: 0.35rem;
        }

        .topbar .navbar-collapse {
            flex-basis: 100%;
            width: 100%;
            order: 4;
            margin-top: 0.9rem;
            padding: 0.9rem;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.10);
        }

        .topbar .navbar-collapse .nav-link {
            padding: 0.8rem 1rem;
            border-radius: 12px;
            text-align: start;
        }

        .menu-toggle {
            width: 48px;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .menu-toggle:hover,
        .menu-toggle:focus {
            background: rgba(255, 255, 255, 0.14);
            color: #fff;
        }

        .menu-toggle .bi-x-lg {
            display: none;
        }

        .menu-toggle.is-open .bi-list {
            display: none;
        }

        .menu-toggle.is-open .bi-x-lg {
            display: inline-block;
        }

        .locale-switcher {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            margin-inline-start: auto;
        }

        .locale-option {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 84px;
            min-height: 40px;
            padding: 0.55rem 0.9rem;
            border-radius: 999px;
            color: rgba(255, 255, 255, 0.82);
            font-size: 0.92rem;
            font-weight: 700;
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .locale-option:hover {
            color: #fff;
        }

        .locale-option.active {
            background: #fff;
            color: var(--navy);
            box-shadow: 0 10px 18px rgba(18, 24, 38, 0.14);
        }

        .page-shell {
            padding: 32px 0 56px;
        }

        .surface-card,
        .hero-card,
        .hero-panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 28px;
            box-shadow: var(--shadow);
        }

        .hero-card {
            background:
                linear-gradient(135deg, rgba(44, 62, 80, 0.98), rgba(52, 73, 94, 0.96)),
                linear-gradient(135deg, rgba(118, 75, 162, 0.12), rgba(102, 126, 234, 0.12));
            color: #fff;
            overflow: hidden;
            position: relative;
            padding: clamp(1.75rem, 2vw + 1.15rem, 3.4rem);
        }

        .hero-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 15% 20%, rgba(118, 75, 162, 0.35), transparent 24%),
                radial-gradient(circle at 85% 25%, rgba(52, 152, 219, 0.22), transparent 18%);
            pointer-events: none;
        }

        .hero-content,
        .hero-panel {
            position: relative;
            z-index: 1;
        }

        .hero-title {
            font-size: clamp(2.1rem, 2.5vw + 1.2rem, 4rem);
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 1.15rem;
            overflow-wrap: anywhere;
        }

        .hero-text {
            color: rgba(255, 255, 255, 0.86);
            font-size: clamp(1rem, 0.7vw + 0.9rem, 1.18rem);
            line-height: 1.95;
            max-width: 680px;
            margin: 0;
            overflow-wrap: anywhere;
        }

        .hero-badges,
        .tag-list,
        .access-badges,
        .quick-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.625rem;
        }

        .hero-badge,
        .tag,
        .access-chip,
        .quick-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            max-width: 100%;
            padding: 0.65rem 0.9rem;
            border-radius: 999px;
            line-height: 1.1;
            font-weight: 700;
            font-size: 0.92rem;
            white-space: normal;
            word-break: break-word;
        }

        .hero-badge {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: #fff;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-main,
        .btn-soft {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-height: 48px;
            padding: 0.95rem 1.25rem;
            border-radius: 14px;
            font-weight: 700;
            border: 0;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
            text-align: center;
        }

        .btn-main {
            color: #fff;
            background: linear-gradient(135deg, var(--purple-soft), var(--purple));
            box-shadow: 0 14px 26px rgba(118, 75, 162, 0.28);
        }

        .btn-soft {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.16);
        }

        .btn-main:hover,
        .btn-soft:hover {
            color: #fff;
            transform: translateY(-2px);
        }

        .hero-panel {
            height: 100%;
            padding: 1.6rem;
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.12);
            color: #fff;
        }

        .hero-panel-title {
            font-size: 1.05rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .quick-list {
            display: grid;
            gap: 0.85rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .quick-list li {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            color: rgba(255, 255, 255, 0.88);
        }

        .quick-list i {
            color: #fff;
            font-size: 1rem;
            margin-top: 0.15rem;
        }

        .section-block {
            margin-top: 30px;
        }

        .section-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .section-kicker {
            color: var(--purple);
            font-weight: 800;
            font-size: 0.9rem;
            margin-bottom: 0.35rem;
        }

        .section-title {
            margin: 0;
            font-size: clamp(1.4rem, 1.4vw + 1rem, 2rem);
            font-weight: 800;
        }

        .section-description {
            margin: 0.45rem 0 0;
            color: var(--muted);
            line-height: 1.8;
            max-width: 780px;
        }

        .stats-grid,
        .cards-grid,
        .resource-grid,
        .endpoint-grid,
        .quickstart-grid {
            display: grid;
            gap: 1.25rem;
            align-items: stretch;
        }

        .stats-grid,
        .cards-grid,
        .resource-grid,
        .quickstart-grid {
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 22rem), 1fr));
        }

        .endpoint-grid {
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 24rem), 1fr));
        }

        .surface-card {
            padding: clamp(1.4rem, 1vw + 1rem, 1.95rem);
            height: 100%;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            overflow: hidden;
        }

        .stat-card {
            border-inline-start: 4px solid transparent;
        }

        .accent-products {
            border-inline-start-color: var(--success);
        }

        .accent-orders {
            border-inline-start-color: var(--info);
        }

        .accent-users {
            border-inline-start-color: var(--danger);
        }

        .accent-analytics {
            border-inline-start-color: var(--warning);
        }

        .icon-pill {
            width: 60px;
            height: 60px;
            flex: 0 0 60px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            background: rgba(118, 75, 162, 0.08);
            color: var(--purple);
            font-size: 1.45rem;
        }

        .card-row {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 1rem;
            align-items: start;
            min-width: 0;
        }

        .card-copy {
            min-width: 0;
            display: grid;
            align-content: start;
        }

        .card-title {
            font-size: clamp(1.1rem, 0.3vw + 1rem, 1.35rem);
            font-weight: 800;
            margin: 0 0 0.75rem;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .card-text {
            margin: 0;
            color: var(--muted);
            line-height: 1.9;
            overflow-wrap: anywhere;
        }

        .toolbar-card {
            padding: 1.2rem;
        }

        .search-input {
            min-height: 48px;
            border-radius: 14px;
            border: 1px solid rgba(44, 62, 80, 0.16);
            padding: 0.85rem 1rem;
            font-size: 1rem;
        }

        .search-input:focus {
            border-color: rgba(118, 75, 162, 0.45);
            box-shadow: 0 0 0 0.25rem rgba(118, 75, 162, 0.12);
        }

        .tag {
            background: rgba(44, 62, 80, 0.06);
            color: var(--navy);
            font-size: 0.88rem;
            padding-inline: 0.85rem;
        }

        .access-chip {
            background: rgba(102, 126, 234, 0.10);
            color: var(--purple);
            font-size: 0.84rem;
            padding-block: 0.55rem;
            padding-inline: 0.8rem;
        }

        .endpoint-card {
            display: flex;
            flex-direction: column;
            gap: 1.15rem;
        }

        .endpoint-list {
            display: grid;
            gap: 0.9rem;
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .endpoint-item {
            display: flex;
            gap: 0.95rem;
            align-items: flex-start;
            padding: 1rem 1.05rem;
            border-radius: 18px;
            background: rgba(245, 247, 250, 0.95);
            border: 1px solid rgba(44, 62, 80, 0.08);
        }

        .endpoint-copy {
            min-width: 0;
            display: grid;
            align-content: start;
        }

        .endpoint-path {
            display: inline-block;
            max-width: 100%;
            font-size: 0.94rem;
            font-weight: 700;
            color: var(--navy);
            white-space: normal;
            overflow-wrap: anywhere;
            background: rgba(44, 62, 80, 0.05);
            border-radius: 10px;
            padding: 0.35rem 0.55rem;
        }

        .endpoint-note {
            margin: 0.6rem 0 0;
            color: var(--muted);
            line-height: 1.8;
        }

        .method-badge {
            min-width: 72px;
            flex: 0 0 auto;
            text-align: center;
            border-radius: 999px;
            color: #fff;
            padding: 0.55rem 0.75rem;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.4px;
        }

        .method-get {
            background: var(--success);
        }

        .method-post {
            background: var(--info);
        }

        .method-put,
        .method-patch {
            background: var(--warning);
        }

        .method-delete {
            background: var(--danger);
        }

        .quick-card pre {
            margin: 0;
            padding: 1.1rem 1.15rem;
            border-radius: 18px;
            background: #182230;
            color: #eef3f8;
            font-size: 0.89rem;
            line-height: 1.75;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            overflow-x: auto;
        }

        .resource-card {
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
        }

        .resource-action {
            margin-top: auto;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 800;
            color: var(--purple);
            padding-top: 0.35rem;
        }

        html[dir="rtl"] .resource-action i {
            transform: rotate(180deg);
        }

        .empty-state {
            text-align: center;
            padding: 1.4rem;
            color: var(--muted);
        }

        footer {
            margin-top: 28px;
        }

        .footer-card {
            padding: 1.35rem 1.5rem;
            text-align: center;
            color: var(--muted);
            line-height: 1.8;
        }

        .footer-card strong {
            color: var(--navy);
        }

        @media (min-width: 992px) {
            .topbar {
                position: sticky;
                top: 0;
                z-index: 1030;
            }

            .topbar .nav-wrapper {
                flex-wrap: nowrap;
            }

            .topbar .navbar-collapse {
                flex-basis: auto;
                width: auto;
                order: 0;
                margin-top: 0;
                padding: 0;
                border: 0;
                background: transparent;
            }

            .topbar .navbar-nav {
                align-items: center;
            }

            .locale-switcher {
                margin-inline-start: auto;
            }
        }

        @media (max-width: 991.98px) {
            .locale-switcher {
                margin-inline-start: 0;
            }

            .hero-card {
                padding: 1.6rem;
            }

            .hero-panel {
                margin-top: 1rem;
            }
        }

        @media (max-width: 767.98px) {
            .page-shell {
                padding-top: 18px;
            }

            .section-head {
                flex-direction: column;
                align-items: stretch;
            }

            .hero-actions {
                flex-direction: column;
            }

            .hero-actions .btn-main,
            .hero-actions .btn-soft {
                width: 100%;
            }

            .locale-switcher {
                width: 100%;
                justify-content: center;
                order: 4;
            }

            .locale-option {
                flex: 1 1 0;
                min-width: 0;
            }

            .topbar .navbar-collapse {
                order: 5;
            }

            .endpoint-item {
                flex-direction: column;
            }

            .method-badge {
                min-width: 0;
                width: fit-content;
            }
        }

        @media (max-width: 575.98px) {
            .container {
                padding-inline: 14px;
            }

            .hero-card,
            .hero-panel,
            .surface-card {
                border-radius: 22px;
            }

            .hero-card,
            .surface-card {
                padding: 1.05rem;
            }

            .hero-badge,
            .tag,
            .access-chip,
            .quick-chip {
                font-size: 0.82rem;
                padding: 0.55rem 0.75rem;
            }

            .hero-title {
                font-size: 1.95rem;
            }

            .section-title {
                font-size: 1.35rem;
            }

            .search-input {
                font-size: 0.96rem;
            }

            .icon-pill {
                width: 52px;
                height: 52px;
                flex-basis: 52px;
            }
        }

        @media (max-width: 414px) {
            .hero-title {
                font-size: 1.8rem;
            }

            .icon-pill {
                width: 46px;
                height: 46px;
                flex-basis: 46px;
                border-radius: 15px;
            }

            .surface-card {
                gap: 0.85rem;
            }
        }

        @media (max-width: 360px) {
            .container {
                padding-inline: 12px;
            }

            .hero-title {
                font-size: 1.65rem;
            }

            .endpoint-path {
                font-size: 0.88rem;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark topbar">
        <div class="container">
            <div class="nav-wrapper">
                <a class="navbar-brand" href="#overview">
                    <i class="bi bi-bag-heart"></i> E-Commerce API
                </a>
                <button class="navbar-toggler menu-toggle collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#mainNavbar"
                    aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="bi bi-list fs-4"></i>
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link"
                                href="#overview">{{ $page['nav']['overview'] }}</a></li>
                        <li class="nav-item"><a class="nav-link"
                                href="#latest-updates">{{ $page['nav']['updates'] }}</a></li>
                        <li class="nav-item"><a class="nav-link"
                                href="#api-groups">{{ $page['nav']['api_groups'] }}</a></li>
                        <li class="nav-item"><a class="nav-link"
                                href="#resources">{{ $page['nav']['resources'] }}</a></li>
                    </ul>
                </div>
                <div class="locale-switcher" aria-label="{{ $page['meta']['locale_label'] }}">
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}"
                        class="locale-option {{ $currentLocale === 'en' ? 'active' : '' }}">
                        {{ $page['meta']['english'] }}
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'ar']) }}"
                        class="locale-option {{ $currentLocale === 'ar' ? 'active' : '' }}">
                        {{ $page['meta']['arabic'] }}
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="page-shell">
        <div class="container">
            <section id="overview">
                <div class="hero-card">
                    <div class="row g-4 align-items-center">
                        <div class="col-xl-7 col-lg-7">
                            <div class="hero-content">
                                <div class="hero-badges mb-3">
                                    <span class="hero-badge"><i class="bi bi-code-slash"></i>
                                        {{ $page['hero']['badges']['api'] }}</span>
                                    <span class="hero-badge"><i class="bi bi-shield-check"></i>
                                        {{ $page['hero']['badges']['sanctum'] }}</span>
                                    <span class="hero-badge"><i class="bi bi-layout-sidebar-inset"></i>
                                        {{ $page['hero']['badges']['dashboard'] }}</span>
                                </div>

                                <h1 class="hero-title">{{ $page['hero']['title'] }}</h1>
                                <p class="hero-text">{{ $page['hero']['text'] }}</p>

                                <div class="quick-meta mt-3">
                                    <span class="quick-chip hero-badge"><i class="bi bi-link-45deg"></i>
                                        {{ $page['hero']['meta']['base'] }}</span>
                                    <span class="quick-chip hero-badge"><i class="bi bi-person-lock"></i>
                                        {{ $page['hero']['meta']['access'] }}</span>
                                    <span class="quick-chip hero-badge"><i class="bi bi-phone"></i>
                                        {{ $page['hero']['meta']['mobile'] }}</span>
                                </div>

                                <div class="hero-actions">
                                    <a href="#api-groups" class="btn-main">
                                        <i class="bi bi-journal-code"></i> {{ $page['hero']['actions']['api_groups'] }}
                                    </a>
                                    <a href="{{ route('dashboard.login') }}" class="btn-soft">
                                        <i class="bi bi-box-arrow-in-right"></i>
                                        {{ $page['hero']['actions']['dashboard'] }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-5 col-lg-5">
                            <aside class="hero-panel">
                                <h2 class="hero-panel-title">{{ $page['hero']['panel_title'] }}</h2>
                                <ul class="quick-list">
                                    @foreach ($page['hero']['panel_items'] as $panelItem)
                                        <li>
                                            <i class="bi bi-check2-circle"></i>
                                            <span>{{ $panelItem }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </aside>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section-block">
                <div class="stats-grid">
                    @foreach ($featureCards as $feature)
                        <article class="surface-card stat-card accent-{{ $feature['accent'] }}">
                            <div class="card-row">
                                <span class="icon-pill">
                                    <i class="bi {{ $feature['icon'] }}"></i>
                                </span>
                                <div class="card-copy">
                                    <h2 class="card-title">{{ $feature['title'] }}</h2>
                                    <p class="card-text">{{ $feature['description'] }}</p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="latest-updates" class="section-block">
                <div class="section-head">
                    <div>
                        <div class="section-kicker">{{ $page['section_kickers']['latest_shape'] }}</div>
                        <h2 class="section-title">{{ $page['latest_updates']['title'] }}</h2>
                        <p class="section-description">{{ $page['latest_updates']['description'] }}</p>
                    </div>
                </div>

                <div class="cards-grid">
                    @foreach ($latestUpdates as $update)
                        <article class="surface-card">
                            <div class="card-row">
                                <span class="icon-pill">
                                    <i class="bi {{ $update['icon'] }}"></i>
                                </span>
                                <div class="card-copy">
                                    <h3 class="card-title">{{ $update['title'] }}</h3>
                                    <p class="card-text">{{ $update['description'] }}</p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            @if (!empty($spotlightCards))
                <section class="section-block">
                    <div class="section-head">
                        <div>
                            <div class="section-kicker">{{ $page['spotlight']['kicker'] }}</div>
                            <h2 class="section-title">{{ $page['spotlight']['title'] }}</h2>
                            <p class="section-description">{{ $page['spotlight']['description'] }}</p>
                        </div>
                    </div>

                    <div class="quickstart-grid">
                        @foreach ($spotlightCards as $card)
                            <article class="surface-card quick-card">
                                <div class="card-row">
                                    <span class="icon-pill">
                                        <i class="bi {{ $card['icon'] }}"></i>
                                    </span>
                                    <div class="card-copy">
                                        <h3 class="card-title">{{ $card['title'] }}</h3>
                                        <p class="card-text">{{ $card['description'] }}</p>
                                    </div>
                                </div>
                                @if (!empty($card['snippet']))
                                    <pre>{{ $card['snippet'] }}</pre>
                                @endif
                            </article>
                        @endforeach
                    </div>

                    @if ($spotlightExample)
                        <div class="quickstart-grid mt-3">
                            <article class="surface-card quick-card">
                                <h3 class="card-title">{{ $spotlightExample['title'] }}</h3>
                                <pre>{{ $spotlightExample['snippet'] }}</pre>
                            </article>
                        </div>
                    @endif
                </section>
            @endif

            <section class="section-block">
                <div class="section-head">
                    <div>
                        <div class="section-kicker">{{ $page['section_kickers']['dashboard_flow'] }}</div>
                        <h2 class="section-title">{{ $page['dashboard']['title'] }}</h2>
                        <p class="section-description">{{ $page['dashboard']['description'] }}</p>
                    </div>
                </div>

                <div class="cards-grid">
                    @foreach ($dashboardSections as $section)
                        <article class="surface-card">
                            <div class="card-row">
                                <span class="icon-pill">
                                    <i class="bi {{ $section['icon'] }}"></i>
                                </span>
                                <div class="card-copy">
                                    <h3 class="card-title">{{ $section['title'] }}</h3>
                                    <p class="card-text">{{ $section['description'] }}</p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="api-groups" class="section-block">
                <div class="section-head">
                    <div>
                        <div class="section-kicker">{{ $page['section_kickers']['api_groups'] }}</div>
                        <h2 class="section-title">{{ $page['api_groups']['title'] }}</h2>
                        <p class="section-description">{{ $page['api_groups']['description'] }}</p>
                    </div>
                </div>

                <div class="surface-card toolbar-card mb-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-6">
                            <input id="endpointSearch" type="search" class="form-control search-input"
                                placeholder="{{ $page['api_groups']['search_placeholder'] }}">
                        </div>
                        <div class="col-lg-6">
                            <div class="tag-list">
                                @foreach ($page['api_groups']['tags'] as $tag)
                                    <span class="tag"><i class="bi bi-stars"></i> {{ $tag }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="endpoint-grid" id="endpointCards">
                    @foreach ($apiGroups as $group)
                        @php
                            $searchText = $group['title'] . ' ' . $group['description'] . ' ' . implode(' ', array_map(fn($item) => $item['path'] . ' ' . $item['note'], $group['items']));
                        @endphp
                        <article class="surface-card endpoint-card searchable-card"
                            data-search="{{ strtolower($searchText) }}">
                            <div class="card-row">
                                <span class="icon-pill">
                                    <i class="bi {{ $group['icon'] }}"></i>
                                </span>
                                <div class="card-copy">
                                    <h3 class="card-title">{{ $group['title'] }}</h3>
                                    <p class="card-text">{{ $group['description'] }}</p>
                                </div>
                            </div>

                            <div class="access-badges">
                                @foreach ($group['access'] as $access)
                                    <span class="access-chip">{{ $access }}</span>
                                @endforeach
                            </div>

                            <ul class="endpoint-list">
                                @foreach ($group['items'] as $item)
                                    <li class="endpoint-item">
                                        <span
                                            class="method-badge method-{{ strtolower($item['method']) }}">{{ $item['method'] }}</span>
                                        <div class="endpoint-copy">
                                            <span class="endpoint-path">{{ $item['path'] }}</span>
                                            <p class="endpoint-note">{{ $item['note'] }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </article>
                    @endforeach
                </div>

                <div id="emptySearchState" class="surface-card empty-state d-none">
                    {{ $page['api_groups']['empty'] }}
                </div>
            </section>

            <section class="section-block">
                <div class="section-head">
                    <div>
                        <div class="section-kicker">{{ $page['section_kickers']['quick_start'] }}</div>
                        <h2 class="section-title">{{ $page['quick_start']['title'] }}</h2>
                        <p class="section-description">{{ $page['quick_start']['description'] }}</p>
                    </div>
                </div>

                <div class="quickstart-grid">
                    @foreach ($quickStartSteps as $step)
                        <article class="surface-card quick-card">
                            <h3 class="card-title">{{ $step['title'] }}</h3>
                            <pre>{{ $step['snippet'] }}</pre>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="resources" class="section-block">
                <div class="section-head">
                    <div>
                        <div class="section-kicker">{{ $page['section_kickers']['resources'] }}</div>
                        <h2 class="section-title">{{ $page['resources']['title'] }}</h2>
                        <p class="section-description">{{ $page['resources']['description'] }}</p>
                    </div>
                </div>

                <div class="resource-grid">
                    @foreach ($resourceCards as $resource)
                        <a class="surface-card resource-card" href="{{ $resource['href'] }}"
                            @if (!empty($resource['download'])) download @endif>
                            <div class="card-row">
                                <span class="icon-pill">
                                    <i class="bi {{ $resource['icon'] }}"></i>
                                </span>
                                <div class="card-copy">
                                    <h3 class="card-title">{{ $resource['title'] }}</h3>
                                    <p class="card-text">{{ $resource['description'] }}</p>
                                </div>
                            </div>
                            <span class="resource-action">
                                <i class="bi bi-arrow-right-short"></i> {{ $resource['action'] }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>

            <footer>
                <div class="surface-card footer-card">
                    <strong>E-Commerce API</strong> | {{ $page['footer'] }} | {{ date('Y') }}
                </div>
            </footer>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const menuToggle = document.querySelector('.menu-toggle');
        const mainNavbar = document.getElementById('mainNavbar');

        if (menuToggle && mainNavbar) {
            mainNavbar.addEventListener('show.bs.collapse', () => {
                menuToggle.classList.add('is-open');
            });

            mainNavbar.addEventListener('hide.bs.collapse', () => {
                menuToggle.classList.remove('is-open');
            });
        }

        document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
            anchor.addEventListener('click', function(event) {
                const target = document.querySelector(this.getAttribute('href'));
                if (!target) {
                    return;
                }

                event.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                });
            });
        });

        const searchInput = document.getElementById('endpointSearch');
        const searchableCards = Array.from(document.querySelectorAll('.searchable-card'));
        const emptyState = document.getElementById('emptySearchState');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const term = this.value.trim().toLowerCase();
                let visibleCount = 0;

                searchableCards.forEach((card) => {
                    const haystack = card.dataset.search || '';
                    const isVisible = haystack.includes(term);
                    card.classList.toggle('d-none', !isVisible);

                    if (isVisible) {
                        visibleCount += 1;
                    }
                });

                emptyState.classList.toggle('d-none', visibleCount !== 0);
            });
        }
    </script>
</body>

</html>
