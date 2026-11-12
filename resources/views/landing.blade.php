<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Piggery Management — herd, breeding, feed and health records</title>
    <meta name="description" content="Free piggery records for small farms: track sows, boars, breeding, farrowing, feed stock and medical schedules.">
    <style>
        :root {
            --emerald: #059669;
            --emerald-dark: #047857;
            --ink: #0f172a;
            --muted: #475569;
            --line: #e2e8f0;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            color: var(--ink);
            background: #f8fafc;
        }
        a { color: inherit; text-decoration: none; }
        .wrap { max-width: 1040px; margin: 0 auto; padding: 0 24px; }
        header { padding: 24px 0; }
        .bar { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; letter-spacing: -0.02em; }
        .brand span.dot { width: 28px; height: 28px; border-radius: 9px; background: var(--emerald); display: inline-block; }
        .actions { display: flex; gap: 10px; align-items: center; }
        .btn {
            display: inline-block; padding: 10px 18px; border-radius: 10px;
            font-weight: 600; font-size: 15px; border: 1px solid transparent;
        }
        .btn-ghost { border-color: var(--line); background: #fff; }
        .btn-primary { background: var(--emerald); color: #fff; }
        .btn-primary:hover { background: var(--emerald-dark); }
        .hero { padding: 64px 0 40px; }
        .hero h1 { font-size: clamp(32px, 5vw, 52px); line-height: 1.05; margin: 0 0 18px; letter-spacing: -0.03em; }
        .hero p { font-size: 18px; line-height: 1.6; color: var(--muted); max-width: 620px; margin: 0 0 28px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; padding: 24px 0 72px; }
        .card { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 20px; }
        .card h3 { margin: 0 0 6px; font-size: 16px; }
        .card p { margin: 0; color: var(--muted); font-size: 14px; line-height: 1.55; }
        footer { border-top: 1px solid var(--line); padding: 24px 0 40px; color: var(--muted); font-size: 14px; }
        @media (max-width: 560px) { .btn { padding: 9px 14px; font-size: 14px; } }
    </style>
</head>
<body>
    <div class="wrap">
        <header>
            <div class="bar">
                <div class="brand"><span class="dot"></span> Piggery Management</div>
                <div class="actions">
                    <a class="btn btn-ghost" href="{{ url('/admin/login') }}">Sign in</a>
                    <a class="btn btn-primary" href="{{ url('/admin/register') }}">Create account</a>
                </div>
            </div>
        </header>

        <section class="hero">
            <h1>Every pig, breeding and bag of feed in one place.</h1>
            <p>
                A straightforward record system for small piggeries. Register free, keep your own
                herd private, and see what needs attention today — upcoming farrowings, low feed
                stock and medical boosters that are due.
            </p>
            <a class="btn btn-primary" href="{{ url('/admin/register') }}">Start your piggery</a>
        </section>

        <section class="grid">
            <div class="card">
                <h3>Herd records</h3>
                <p>Sows, boars and gilts with tags, breeds, birth dates and cage assignments.</p>
            </div>
            <div class="card">
                <h3>Breeding &amp; farrowing</h3>
                <p>Log matings, get an expected farrowing date automatically, record born alive and stillborn.</p>
            </div>
            <div class="card">
                <h3>Feed inventory</h3>
                <p>Stock in and out in bags or kilograms, with low-stock warnings before you run out.</p>
            </div>
            <div class="card">
                <h3>Health schedule</h3>
                <p>Vaccines, vitamins and treatments for each animal or litter, with booster reminders.</p>
            </div>
        </section>

        <footer>
            Your records are private to your account.
        </footer>
    </div>
</body>
</html>
