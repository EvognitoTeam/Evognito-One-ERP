<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale() ?: 'id') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#f7f6f3">

    <title>@yield('title', 'EvognitoOne')</title>

    <style>
        :root {
            --bg:#f7f6f3;
            --surface:#fff;
            --surface-soft:#fbfaf8;
            --text:#252421;
            --muted:#77746d;
            --line:rgba(37,36,33,.08);
            --purple:#6d28d9;
            --purple-soft:#f3e8ff;
            --shadow:0 28px 80px rgba(37,36,33,.09);
        }

        * { box-sizing:border-box; }

        html, body { min-height:100%; }

        body {
            margin:0;
            background:
                radial-gradient(circle at top left, rgba(109,40,217,.07), transparent 30rem),
                var(--bg);
            color:var(--text);
            font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
            -webkit-font-smoothing:antialiased;
        }

        a { color:inherit; text-decoration:none; }

        .page {
            min-height:100vh;
            display:grid;
            place-items:center;
            padding:32px 20px;
        }

        .shell { width:min(100%, 980px); }

        .brand {
            display:inline-flex;
            align-items:center;
            gap:12px;
            margin-bottom:22px;
        }

        .brand-mark {
            width:42px;
            height:42px;
            display:grid;
            place-items:center;
            border-radius:14px;
            background:linear-gradient(135deg,#6d28d9,#8b5cf6);
            color:#fff;
            font-size:17px;
            font-weight:800;
            box-shadow:0 10px 28px rgba(109,40,217,.18);
        }

        .brand-name {
            font-size:16px;
            font-weight:800;
            letter-spacing:-.02em;
        }

        .brand-subtitle {
            margin-top:2px;
            font-size:12px;
            color:var(--muted);
        }

        .card {
            overflow:hidden;
            border:1px solid var(--line);
            border-radius:28px;
            background:rgba(255,255,255,.97);
            box-shadow:var(--shadow);
        }

        .content { display:grid; }

        @media (min-width:800px) {
            .content { grid-template-columns:1.15fr .85fr; }
        }

        .main { padding:38px 34px 34px; }

        @media (min-width:800px) {
            .main { padding:52px 52px 48px; }
        }

        .side {
            position:relative;
            overflow:hidden;
            padding:32px;
            border-top:1px solid var(--line);
            background:var(--surface-soft);
        }

        @media (min-width:800px) {
            .side {
                border-top:0;
                border-left:1px solid var(--line);
                padding:48px 36px;
            }
        }

        .side::before {
            content:"";
            position:absolute;
            width:280px;
            height:280px;
            right:-120px;
            top:-110px;
            border-radius:999px;
            background:rgba(109,40,217,.055);
        }

        .eyebrow {
            display:inline-flex;
            align-items:center;
            gap:8px;
            min-height:30px;
            padding:0 11px;
            border-radius:999px;
            background:var(--purple-soft);
            color:var(--purple);
            font-size:12px;
            font-weight:800;
            letter-spacing:.05em;
            text-transform:uppercase;
        }

        .dot {
            width:7px;
            height:7px;
            border-radius:999px;
            background:currentColor;
        }

        h1 {
            max-width:620px;
            margin:22px 0 0;
            font-size:clamp(34px,7vw,58px);
            line-height:.98;
            letter-spacing:-.055em;
            font-weight:800;
        }

        .description {
            max-width:620px;
            margin:22px 0 0;
            color:var(--muted);
            font-size:15px;
            line-height:1.8;
        }

        .code {
            display:flex;
            align-items:baseline;
            gap:10px;
            margin-top:30px;
            color:#aaa69e;
        }

        .code strong {
            font-size:30px;
            line-height:1;
            letter-spacing:-.04em;
        }

        .code span {
            font-size:13px;
            font-weight:700;
            letter-spacing:.08em;
            text-transform:uppercase;
        }

        .actions {
            display:flex;
            flex-wrap:wrap;
            gap:10px;
            margin-top:30px;
        }

        .button {
            min-height:44px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:0 17px;
            border:1px solid transparent;
            border-radius:13px;
            font-size:14px;
            font-weight:750;
            cursor:pointer;
            transition:transform .15s ease;
        }

        .button:hover { transform:translateY(-1px); }

        .button-primary {
            background:var(--text);
            color:#fff;
        }

        .button-secondary {
            border-color:var(--line);
            background:#fff;
        }

        .section-label {
            position:relative;
            z-index:1;
            margin-bottom:18px;
            color:#8a867f;
            font-size:12px;
            font-weight:800;
            letter-spacing:.08em;
            text-transform:uppercase;
        }

        .info-list {
            position:relative;
            z-index:1;
            display:grid;
            gap:12px;
        }

        .info-item {
            display:flex;
            align-items:flex-start;
            gap:12px;
            padding:14px;
            border:1px solid var(--line);
            border-radius:16px;
            background:rgba(255,255,255,.75);
        }

        .info-icon {
            flex:0 0 auto;
            width:34px;
            height:34px;
            display:grid;
            place-items:center;
            border:1px solid var(--line);
            border-radius:11px;
            background:#fff;
            font-size:12px;
            font-weight:800;
        }

        .info-title {
            font-size:13px;
            font-weight:750;
        }

        .info-text {
            margin-top:3px;
            color:var(--muted);
            font-size:12px;
            line-height:1.65;
        }

        .footer {
            display:flex;
            flex-wrap:wrap;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            padding:18px 22px;
            border-top:1px solid var(--line);
            background:rgba(255,255,255,.82);
            color:#96918a;
            font-size:12px;
        }

        .request-id {
            font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;
        }

        @media (max-width:520px) {
            .main { padding:30px 22px; }
            .actions { display:grid; }
            .button { width:100%; }
        }
    </style>
</head>
<body>
<main class="page">
    <div class="shell">
        <div class="brand">
            <div class="brand-mark">E</div>
            <div>
                <div class="brand-name">EvognitoOne</div>
                <div class="brand-subtitle">Business Operations Platform</div>
            </div>
        </div>

        <section class="card">
            <div class="content">
                <div class="main">
                    <div class="eyebrow">
                        <span class="dot"></span>
                        @yield('eyebrow', 'System Message')
                    </div>

                    <h1>@yield('heading')</h1>

                    <p class="description">@yield('description')</p>

                    <div class="code">
                        <strong>@yield('code')</strong>
                        <span>@yield('code_label', 'HTTP Error')</span>
                    </div>

                    <div class="actions">
                        @yield('actions')
                    </div>
                </div>

                <aside class="side">
                    <div class="section-label">
                        @yield('side_title', 'Yang dapat dilakukan')
                    </div>

                    <div class="info-list">
                        @yield('side_content')
                    </div>
                </aside>
            </div>

            <footer class="footer">
                <span>© {{ date('Y') }} EvognitoOne</span>

                @if(request()->header('X-Request-ID'))
                    <span class="request-id">
                        Request: {{ request()->header('X-Request-ID') }}
                    </span>
                @endif
            </footer>
        </section>
    </div>
</main>
</body>
</html>
