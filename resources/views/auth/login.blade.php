<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mujeza</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;500;600;700;800;900;1000&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #d4af37;
            --deep-gold: #b8912f;
            --red: #c1121f;
            --ink: #111827;
            --paper: #fffdf7;
            --card: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Cairo", "Tahoma", "Arial", sans-serif;
            background: radial-gradient(circle at top right, #fff5d6, var(--paper));
            color: var(--ink);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-weight: 400;
        }

        .login-card {
            width: 100%;
            max-width: 460px;
            background: var(--card);
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(17, 24, 39, 0.12);
            padding: 28px;
            border-top: 6px solid var(--gold);
        }

        .brand {
            text-align: center;
            margin-bottom: 24px;
        }

        .brand img {
            width: 100%;
            max-width: 320px;
            margin: 0 auto 14px;
            display: block;
        }

        .brand h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }

        .brand p {
            margin: 8px 0 0;
            color: #4b5563;
            font-size: 15px;
            font-weight: 500;
        }

        label {
            display: block;
            font-size: 14px;
            margin-bottom: 6px;
            font-weight: 700;
        }

        .field {
            margin-bottom: 16px;
        }

        input {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            background: #fffcf2;
        }

        input:focus {
            border-color: var(--deep-gold);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.22);
        }

        .error-box {
            background: #fff1f2;
            color: var(--red);
            border: 1px solid #fecdd3;
            padding: 10px 12px;
            border-radius: 10px;
            margin-bottom: 14px;
            font-size: 14px;
        }

        .btn {
            width: 100%;
            border: none;
            border-radius: 12px;
            background: linear-gradient(90deg, var(--gold), var(--deep-gold));
            color: #111827;
            font-weight: 700;
            font-size: 16px;
            padding: 12px;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(184, 145, 47, 0.25);
        }

        .hint {
            margin-top: 14px;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand">
            <img src="{{ asset('images/mujeza-logo.png') }}" alt="Mujeza Logo">
            <h1>تسجيل الدخول</h1>
            <p>تسجيل الدخول إلى لوحة التحكم</p>
        </div>

        @if ($errors->any())
            <div class="error-box">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <div class="field">
                <label for="username">اسم المستخدم</label>
                <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus>
            </div>

            <div class="field">
                <label for="password">كلمة المرور</label>
                <input id="password" name="password" type="password" required>
            </div>

            <button type="submit" class="btn">دخول</button>
        </form>

        <p class="hint">بيانات الدخول الحالية: admin / admin</p>
    </div>
</body>
</html>
