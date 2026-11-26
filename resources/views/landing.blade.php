<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Piggery Management — herd, breeding, feed and health records</title>
    <meta name="description" content="Free piggery records for small farms: track sows, boars, breeding, farrowing, feed stock and medical schedules.">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('pigfarm-theme');
                var dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.dataset.theme = dark ? 'dark' : 'light';
            } catch (e) {
                document.documentElement.dataset.theme = 'light';
            }
        })();
    </script>
    <style>
        :root {
            --bg: #fff8f5;
            --surface: #ffffff;
            --surface-2: #fff1f5;
            --ink: #2b1a21;
            --muted: #6d5560;
            --line: #f1dde4;
            --primary: #d6336c;
            --primary-dark: #a61e4d;
            --primary-soft: #f9c6d3;
            --green: #4f9e2f;
            --green-soft: #dcf0cf;
            --heart: #d92c2c;
            --sky: #bfe3fa;
            --shadow: 0 20px 40px -30px rgba(120, 25, 60, .55);
            --shadow-lift: 0 28px 50px -28px rgba(120, 25, 60, .45);
            color-scheme: light;
        }

        :root[data-theme="dark"] {
            --bg: #171114;
            --surface: #221a1f;
            --surface-2: #2b2026;
            --ink: #f8eef2;
            --muted: #c3aeb8;
            --line: #3a2a32;
            --primary: #f06a97;
            --primary-dark: #d6336c;
            --primary-soft: #5a2438;
            --green: #7fc45c;
            --green-soft: #24361c;
            --heart: #f06060;
            --sky: #24445c;
            --shadow: 0 20px 40px -26px rgba(0, 0, 0, .85);
            --shadow-lift: 0 30px 55px -25px rgba(0, 0, 0, .9);
            color-scheme: dark;
        }

        * { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(900px 460px at 105% -8%, color-mix(in oklab, var(--primary-soft) 70%, transparent), transparent 62%),
                radial-gradient(700px 380px at -10% 8%, color-mix(in oklab, var(--green-soft) 55%, transparent), transparent 60%),
                var(--bg);
            background-attachment: fixed;
            transition: background-color 250ms ease, color 250ms ease;
        }

        a { color: inherit; text-decoration: none; }

        .wrap { max-width: 1080px; margin: 0 auto; padding: 0 24px; }

        /* ------------------------------------------------------------ header */

        header { padding: 22px 0; }

        .bar { display: flex; align-items: center; justify-content: space-between; gap: 16px; }

        .brand { display: flex; align-items: center; gap: 12px; font-weight: 750; letter-spacing: -0.025em; font-size: 17px; }

        .brand img {
            width: 42px; height: 42px; border-radius: 9999px;
            box-shadow: 0 0 0 3px color-mix(in oklab, var(--primary-soft) 65%, transparent);
            transition: transform 300ms cubic-bezier(.34, 1.56, .64, 1);
        }

        .brand:hover img { transform: rotate(-8deg) scale(1.08); }

        .actions { display: flex; gap: 10px; align-items: center; }

        .theme-toggle {
            display: inline-grid; place-items: center;
            width: 40px; height: 40px; padding: 0;
            border-radius: 12px; cursor: pointer;
            border: 1px solid var(--line);
            background: var(--surface); color: var(--ink);
            transition: transform 160ms ease, border-color 160ms ease, background-color 160ms ease;
        }

        .theme-toggle:hover { transform: translateY(-2px); border-color: var(--primary); }
        .theme-toggle svg { width: 18px; height: 18px; }
        .theme-toggle .icon-moon { display: none; }
        :root[data-theme="dark"] .theme-toggle .icon-sun { display: none; }
        :root[data-theme="dark"] .theme-toggle .icon-moon { display: block; }

        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 11px 20px; border-radius: 12px;
            font-weight: 650; font-size: 15px; line-height: 1;
            border: 1px solid transparent; cursor: pointer;
            transition: transform 160ms ease, box-shadow 160ms ease, background-color 160ms ease, border-color 160ms ease;
        }

        .btn:hover { transform: translateY(-2px); }
        .btn:active { transform: translateY(0) scale(.985); }

        .btn-ghost { border-color: var(--line); background: var(--surface); color: var(--ink); }
        .btn-ghost:hover { border-color: var(--primary); box-shadow: var(--shadow); }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            box-shadow: 0 14px 26px -16px color-mix(in oklab, var(--primary-dark) 85%, transparent);
        }

        .btn-primary:hover { box-shadow: 0 20px 34px -16px color-mix(in oklab, var(--primary-dark) 80%, transparent); }

        /* -------------------------------------------------------------- hero */

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
            align-items: center;
            gap: 48px;
            padding: 56px 0 36px;
        }

        .eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 7px 13px; margin-bottom: 18px;
            border-radius: 9999px; font-size: 13px; font-weight: 650;
            color: var(--primary-dark);
            background: color-mix(in oklab, var(--primary-soft) 70%, transparent);
            border: 1px solid color-mix(in oklab, var(--primary) 25%, transparent);
        }

        :root[data-theme="dark"] .eyebrow { color: var(--primary); }

        .eyebrow .pulse {
            width: 8px; height: 8px; border-radius: 9999px; background: var(--primary);
            box-shadow: 0 0 0 0 color-mix(in oklab, var(--primary) 60%, transparent);
            animation: pulse 2.4s ease-out infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 color-mix(in oklab, var(--primary) 55%, transparent); }
            70% { box-shadow: 0 0 0 9px transparent; }
            100% { box-shadow: 0 0 0 0 transparent; }
        }

        .hero h1 {
            font-size: clamp(34px, 5vw, 56px);
            line-height: 1.03; margin: 0 0 18px;
            letter-spacing: -0.035em;
        }

        .hero h1 .accent { color: var(--primary); }

        .hero p {
            font-size: 18px; line-height: 1.65;
            color: var(--muted); max-width: 560px; margin: 0 0 28px;
        }

        .hero-actions { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }

        .hero-note { margin-top: 18px; font-size: 13.5px; color: var(--muted); }

        .hero-art { display: grid; place-items: center; }

        .badge-frame {
            position: relative;
            width: clamp(220px, 30vw, 330px);
            aspect-ratio: 1;
            border-radius: 9999px;
            display: grid; place-items: center;
            background:
                radial-gradient(circle at 32% 24%, color-mix(in oklab, #ffffff 75%, transparent), transparent 55%),
                color-mix(in oklab, var(--primary-soft) 55%, var(--surface));
            box-shadow: var(--shadow), inset 0 0 0 1px color-mix(in oklab, var(--primary) 18%, transparent);
            animation: float 7s ease-in-out infinite;
            transition: transform 260ms ease, box-shadow 260ms ease;
        }

        .badge-frame::after {
            content: "";
            position: absolute; inset: -18px;
            border-radius: 9999px;
            border: 1px dashed color-mix(in oklab, var(--primary) 30%, transparent);
            animation: spin 40s linear infinite;
            pointer-events: none;
        }

        .badge-frame:hover { transform: translateY(-6px) scale(1.02); box-shadow: var(--shadow-lift); }

        .badge-frame img {
            width: 86%; height: 86%; border-radius: 9999px;
            transition: transform 300ms cubic-bezier(.34, 1.56, .64, 1);
        }

        .badge-frame:hover img { transform: rotate(5deg) scale(1.04); }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ------------------------------------------------------------- cards */

        .section-title { margin: 26px 0 16px; font-size: 14px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--muted); }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(225px, 1fr)); gap: 18px; padding: 0 0 64px; }

        .card {
            position: relative;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 1px 0 color-mix(in oklab, var(--line) 60%, transparent);
            transition: transform 200ms ease, box-shadow 200ms ease, border-color 200ms ease;
        }

        .card::before {
            content: "";
            position: absolute; inset: 0;
            border-radius: inherit;
            background: linear-gradient(140deg, color-mix(in oklab, var(--primary) 8%, transparent), transparent 55%);
            opacity: 0;
            transition: opacity 200ms ease;
            pointer-events: none;
        }

        .card:hover { transform: translateY(-6px); border-color: color-mix(in oklab, var(--primary) 45%, var(--line)); box-shadow: var(--shadow-lift); }
        .card:hover::before { opacity: 1; }

        .chip {
            width: 44px; height: 44px; border-radius: 13px;
            display: grid; place-items: center; margin-bottom: 14px;
            color: var(--primary-dark);
            background: color-mix(in oklab, var(--primary-soft) 75%, transparent);
            transition: transform 200ms ease;
        }

        :root[data-theme="dark"] .chip { color: var(--primary); }

        .card:hover .chip { transform: scale(1.08) rotate(-4deg); }
        .chip svg { width: 22px; height: 22px; }

        .card h3 { margin: 0 0 7px; font-size: 16.5px; letter-spacing: -0.01em; }
        .card p { margin: 0; color: var(--muted); font-size: 14.5px; line-height: 1.6; }

        /* ------------------------------------------------------------ footer */

        footer {
            border-top: 1px solid var(--line);
            padding: 26px 0 42px;
            color: var(--muted);
            font-size: 14px;
            display: flex; flex-wrap: wrap; gap: 10px;
            align-items: center; justify-content: space-between;
        }

        footer .foot-brand { display: inline-flex; align-items: center; gap: 8px; }
        footer img { width: 22px; height: 22px; border-radius: 9999px; }

        @media (max-width: 820px) {
            .hero { grid-template-columns: 1fr; gap: 34px; padding-top: 34px; text-align: center; }
            .hero p { margin-inline: auto; }
            .hero-actions { justify-content: center; }
            .hero-art { order: -1; }
            .badge-frame { width: min(58vw, 260px); }
        }

        @media (max-width: 560px) {
            .brand span.name { display: none; }
            .btn { padding: 10px 15px; font-size: 14px; }
            .hero h1 { font-size: clamp(30px, 9vw, 40px); }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <header>
            <div class="bar">
                <a class="brand" href="{{ url('/') }}">
                    <img src="{{ asset('images/joyful-pig-badge.png') }}" alt="Joyful Pig Farm badge">
                    <span class="name">Piggery Management</span>
                </a>
                <div class="actions">
                    <button type="button" class="theme-toggle" id="theme-toggle" aria-label="Switch between light and dark mode">
                        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <circle cx="12" cy="12" r="4"/>
                            <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
                        </svg>
                        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </button>
                    <a class="btn btn-ghost" href="{{ url('/admin/login') }}">Sign in</a>
                    <a class="btn btn-primary" href="{{ url('/admin/register') }}">Create account</a>
                </div>
            </div>
        </header>

        <section class="hero">
            <div>
                <span class="eyebrow"><span class="pulse"></span> Free for every small farm</span>
                <h1>Every pig, breeding and bag of feed in <span class="accent">one happy place.</span></h1>
                <p>
                    A straightforward record system for small piggeries. Register free, keep your own
                    herd private, and see what needs attention today — upcoming farrowings, low feed
                    stock and medical boosters that are due.
                </p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="{{ url('/admin/register') }}">Start your piggery</a>
                    <a class="btn btn-ghost" href="{{ url('/admin/login') }}">I already have an account</a>
                </div>
                <p class="hero-note">No email verification needed. Your records stay private to your account.</p>
            </div>

            <div class="hero-art">
                <div class="badge-frame">
                    <img src="{{ asset('images/joyful-pig-badge.png') }}" alt="Joyful Pig Farm badge">
                </div>
            </div>
        </section>

        <p class="section-title">Everything the farm needs</p>

        <section class="grid">
            <div class="card">
                <span class="chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </span>
                <h3>Herd records</h3>
                <p>Sows, boars and gilts with tags, breeds, birth dates and cage assignments.</p>
            </div>
            <div class="card">
                <span class="chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z"/>
                    </svg>
                </span>
                <h3>Breeding &amp; farrowing</h3>
                <p>Log matings, get an expected farrowing date automatically, record born alive and stillborn.</p>
            </div>
            <div class="card">
                <span class="chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m7.5 4.27 9 5.15"/>
                        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                        <path d="m3.3 7 8.7 5 8.7-5"/>
                        <path d="M12 22V12"/>
                    </svg>
                </span>
                <h3>Feed inventory</h3>
                <p>Stock in and out in bags or kilograms, with low-stock warnings before you run out.</p>
            </div>
            <div class="card">
                <span class="chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m18 2 4 4"/>
                        <path d="m17 7 3-3"/>
                        <path d="M19 9 8.7 19.3a2.4 2.4 0 0 1-3.4 0l-.6-.6a2.4 2.4 0 0 1 0-3.4L15 5"/>
                        <path d="m9 11 4 4"/>
                        <path d="m5 19-3 3"/>
                        <path d="m14 4 6 6"/>
                    </svg>
                </span>
                <h3>Health schedule</h3>
                <p>Vaccines, vitamins and treatments for each animal or litter, with booster reminders.</p>
            </div>
        </section>

        <footer>
            <span class="foot-brand">
                <img src="{{ asset('images/joyful-pig-badge.png') }}" alt="">
                Piggery Management
            </span>
            <span>Your records are private to your account.</span>
        </footer>
    </div>

    <script>
        (function () {
            var root = document.documentElement;
            var button = document.getElementById('theme-toggle');

            if (! button) {
                return;
            }

            button.addEventListener('click', function () {
                var next = root.dataset.theme === 'dark' ? 'light' : 'dark';
                root.dataset.theme = next;

                try {
                    localStorage.setItem('pigfarm-theme', next);
                } catch (e) {
                    // Storage can be unavailable in private mode; the toggle still works.
                }
            });
        })();
    </script>
</body>
</html>
