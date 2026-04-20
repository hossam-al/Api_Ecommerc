<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول المالك</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .login-card {
            max-width: 450px;
            width: 100%;
            padding: 2rem;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            padding: 5px;
            background-color: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .login-title {
            margin-top: 1rem;
            font-weight: 700;
            color: #333;
        }

        .login-subtitle {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-control:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.25rem rgba(118, 75, 162, 0.25);
        }

        .btn-primary {
            background-color: #764ba2;
            border-color: #764ba2;
            padding: 0.6rem;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: #663d91;
            border-color: #663d91;
        }

        .alert {
            margin-bottom: 1.5rem;
        }

        .login-footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8 col-xl-6">
                <div class="login-card">
                    <div class="login-header">
                        <img src="https://via.placeholder.com/80" alt="Owner Logo">
                        <h2 class="login-title">لوحة تحكم المالك</h2>
                        <p class="login-subtitle">قم بتسجيل الدخول للوصول إلى لوحة التحكم الخاصة بك</p>
                    </div>

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('dashboard.owner.login') }}">
                        @csrf
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="البريد الإلكتروني" required>
                            <label for="email">البريد الإلكتروني</label>
                        </div>
                        <div class="form-floating mb-4">
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="كلمة المرور" required>
                            <label for="password">كلمة المرور</label>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">تسجيل الدخول</button>
                        </div>
                    </form>

                    <div class="login-footer">
                        <p class="mb-0">نظام API الخاص بك © {{ date('Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
