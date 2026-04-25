<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at top right, rgba(118, 75, 162, 0.22), transparent 25%),
                radial-gradient(circle at bottom left, rgba(102, 126, 234, 0.20), transparent 25%),
                #f5f7fa;
            padding: 20px 14px;
        }

        .reset-shell {
            width: min(100%, 460px);
        }

        .reset-card {
            background: #fff;
            border: 1px solid rgba(44, 62, 80, 0.10);
            border-radius: 24px;
            box-shadow: 0 18px 50px rgba(28, 37, 54, 0.12);
            padding: 28px;
        }

        .brand-badge {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            font-size: 1.4rem;
            box-shadow: 0 14px 24px rgba(118, 75, 162, 0.24);
        }

        .reset-title {
            margin: 18px 0 10px;
            font-size: clamp(1.7rem, 2vw + 1rem, 2.25rem);
            font-weight: 800;
            color: #203040;
        }

        .reset-text {
            color: #667085;
            line-height: 1.8;
            margin-bottom: 24px;
        }

        .form-label {
            font-weight: 700;
            color: #2c3e50;
        }

        .form-control {
            min-height: 50px;
            border-radius: 14px;
            border: 1px solid rgba(44, 62, 80, 0.16);
            padding: 0.85rem 0.95rem;
        }

        .form-control:focus {
            border-color: rgba(118, 75, 162, 0.45);
            box-shadow: 0 0 0 0.25rem rgba(118, 75, 162, 0.12);
        }

        .btn-primary {
            min-height: 50px;
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            font-weight: 700;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background: linear-gradient(135deg, #5e75e0, #6d4397);
        }

        .helper-link {
            color: #764ba2;
            text-decoration: none;
            font-weight: 700;
        }

        .helper-link:hover {
            color: #5e3b8a;
        }

        @media (max-width: 420px) {
            .reset-card {
                padding: 22px 18px;
                border-radius: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="reset-shell">
        <div class="reset-card">
            <div class="brand-badge">
                <i class="bi bi-shield-lock"></i>
            </div>

            <h1 class="reset-title">Reset your password</h1>
            <p class="reset-text">
                Enter your account email and choose a new password to complete the reset process.
            </p>

            @if (session('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update.web') }}">
                @csrf
                <input type="hidden" name="token" value="{{ old('token', $token) }}">

                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" id="email" name="email" class="form-control"
                        value="{{ old('email', $email) }}" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">New password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Confirm new password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check2-circle me-2"></i>
                    Update password
                </button>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('dashboard.login') }}" class="helper-link">
                    Back to login
                </a>
            </div>
        </div>
    </div>
</body>

</html>
