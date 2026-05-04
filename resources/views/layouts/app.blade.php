<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'لوحة التحكم')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;500;600;700;800;900;1000&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #d4af37;
            --gold-soft: #f6e8b5;
            --ink: #111827;
            --muted: #6b7280;
            --red: #c1121f;
            --bg: #fffdf7;
            --white: #ffffff;
            --line: #efe3b7;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Cairo", "Tahoma", "Arial", sans-serif;
            font-weight: 400;
            background: var(--bg);
            color: var(--ink);
        }

        .app {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: var(--white);
            border-left: 1px solid var(--line);
            padding: 22px 16px;
        }

        .brand {
            text-align: center;
            margin-bottom: 20px;
        }

        .brand img {
            max-width: 220px;
            margin: 0 auto;
            display: block;
        }

        .nav a {
            display: block;
            text-decoration: none;
            color: var(--ink);
            font-weight: 600;
            padding: 10px 12px;
            border-radius: 10px;
            margin-bottom: 8px;
            transition: 0.2s ease;
        }

        .nav a:hover {
            background: #f8f2de;
        }

        .nav a.active {
            background: var(--gold-soft);
            border: 1px solid #e9d891;
        }

        .nav-group {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed var(--line);
        }

        .nav-group-title {
            display: block;
            margin-bottom: 8px;
            padding: 8px 12px;
            border-radius: 10px;
            background: #f8f2de;
            color: var(--ink);
            font-weight: 800;
            font-size: 15px;
        }

        .nav-group a {
            padding-right: 18px;
        }

        .main {
            display: grid;
            grid-template-rows: auto 1fr auto;
            min-height: 100vh;
        }

        .header {
            background: var(--white);
            border-bottom: 1px solid var(--line);
            padding: 16px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }

        .header p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            font-weight: 500;
        }

        .logout-btn {
            border: none;
            background: var(--red);
            color: #fff;
            padding: 10px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-family: inherit;
        }

        .content {
            padding: 24px;
        }

        .card {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 20px;
        }

        .footer {
            background: var(--white);
            border-top: 1px solid var(--line);
            padding: 12px 22px;
            font-size: 13px;
            color: var(--muted);
            font-weight: 500;
            text-align: center;
        }

        @media (max-width: 900px) {
            .app {
                grid-template-columns: 1fr;
            }

            .sidebar {
                border-left: 0;
                border-bottom: 1px solid var(--line);
            }
        }
    </style>
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <div class="brand">
                <img src="{{ asset('images/a2m-logo.png') }}" alt="A2M Logo">
            </div>

            <nav class="nav">
                <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">الرئيسية</a>
                <a class="{{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">المنتجات</a>
                <a class="{{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">الطلبات</a>
                <a class="{{ request()->routeIs('branches.*') ? 'active' : '' }}" href="{{ route('branches.index') }}">الفروع</a>
                <a class="{{ request()->routeIs('complaints.*') ? 'active' : '' }}" href="{{ route('complaints.index') }}">الشكاوى والاستفسارات</a>

                <div class="nav-group">
                    <span class="nav-group-title">بيانات النظام</span>
                    <a class="{{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">الإعدادات</a>
                    <a class="{{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">الفئات</a>
                    <a class="{{ request()->routeIs('diseases.*') ? 'active' : '' }}" href="{{ route('diseases.index') }}">الأمراض</a>
                    <a class="{{ request()->routeIs('conversations.*') ? 'active' : '' }}" href="{{ route('conversations.index') }}">المحادثات</a>
                    <a class="{{ request()->is('api/documentation*') ? 'active' : '' }}" href="{{ url('/api/documentation') }}">Swagger</a>
                    <a class="{{ request()->is('telescope*') ? 'active' : '' }}" href="{{ url('/telescope') }}">Laravel Telescope</a>
                </div>
            </nav>
        </aside>

        <div class="main">
            <header class="header">
                <div>
                    <h1>@yield('page_title', 'لوحة التحكم')</h1>
                    <p>مرحبًا {{ auth()->user()->name }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-btn" type="submit">تسجيل الخروج</button>
                </form>
            </header>

            <main class="content">
                @yield('content')
            </main>

            <footer class="footer">
                Mujeza Admin Dashboard - جميع الحقوق محفوظة - A2M Marketing & Advertising Agenc
            </footer>
        </div>
    </div>
</body>
</html>
