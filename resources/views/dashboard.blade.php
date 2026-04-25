<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;500;600;700;800;900;1000&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Cairo", "Tahoma", "Arial", sans-serif;
            background: #fffdf7;
            color: #111827;
            font-weight: 400;
        }

        .top-bar {
            background: #f4cc4f;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .top-bar h1 {
            margin: 0;
            font-size: 20px;
        }

        .logout-btn {
            border: none;
            background: #c1121f;
            color: #fff;
            padding: 10px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
        }

        .content {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #f2e2a7;
            padding: 20px;
        }
    </style>
</head>
<body>
    <header class="top-bar">
        <h1>لوحة تحكم Mujeza</h1>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-btn" type="submit">تسجيل الخروج</button>
        </form>
    </header>

    <main class="content">
        <div class="card">
            <h2>أهلًا {{ auth()->user()->name }}</h2>
            <p>تم تسجيل الدخول بنجاح. هذه نسخة أولية من لوحة التحكم.</p>
        </div>
    </main>
</body>
</html>
