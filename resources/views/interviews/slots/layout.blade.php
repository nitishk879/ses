{{--
    Shell for the three candidate-facing pages.

    Deliberately self-contained — no @vite, no font CDN, no JavaScript. This
    page is the single point where an invitation either converts into a booking
    or is lost, and it is opened on unknown devices and networks, often from a
    mail client's in-app browser. A stale asset manifest or a blocked CDN must
    not be able to turn it into an unstyled or broken page.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    <title>{{ $title ?? __('interview.page.title') }} — {{ config('app.name') }}</title>
    <style>
        :root {
            --ink: #1f2430;
            --muted: #667085;
            --line: #e4e7ec;
            --bg: #f6f7f9;
            --card: #ffffff;
            --accent: #1a56db;
            --accent-ink: #ffffff;
            --ok: #067647;
            --ok-bg: #ecfdf3;
            --warn: #b54708;
            --warn-bg: #fffaeb;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px 16px 48px;
            background: var(--bg);
            color: var(--ink);
            font: 16px/1.6 -apple-system, BlinkMacSystemFont, "Segoe UI", "Hiragino Sans",
                  "Noto Sans JP", Roboto, Arial, sans-serif;
            -webkit-text-size-adjust: 100%;
        }
        .wrap { max-width: 560px; margin: 0 auto; }
        .brand {
            font-size: 13px; letter-spacing: .08em; text-transform: uppercase;
            color: var(--muted); margin-bottom: 14px;
        }
        .card {
            background: var(--card); border: 1px solid var(--line);
            border-radius: 12px; padding: 28px 24px;
        }
        h1 { font-size: 22px; line-height: 1.35; margin: 0 0 10px; }
        p { margin: 0 0 14px; }
        .muted { color: var(--muted); font-size: 14px; }
        .meta {
            border-top: 1px solid var(--line); margin-top: 20px;
            padding-top: 16px; font-size: 14px; color: var(--muted);
        }
        .notice {
            border-radius: 8px; padding: 12px 14px; font-size: 14px; margin-bottom: 18px;
        }
        .notice-warn { background: var(--warn-bg); color: var(--warn); }
        .notice-ok { background: var(--ok-bg); color: var(--ok); }
        .slots { list-style: none; margin: 0 0 20px; padding: 0; }
        .slot { margin-bottom: 10px; }
        .slot label {
            display: flex; align-items: center; gap: 12px;
            border: 1px solid var(--line); border-radius: 10px;
            padding: 14px 16px; cursor: pointer; background: #fff;
        }
        /* Focus-within so keyboard users see the same affordance as a tap. */
        .slot label:hover, .slot label:focus-within {
            border-color: var(--accent); background: #f5f8ff;
        }
        .slot input { width: 18px; height: 18px; margin: 0; flex: none; accent-color: var(--accent); }
        .slot .when { font-weight: 600; }
        .btn {
            display: block; width: 100%; border: 0; border-radius: 10px;
            background: var(--accent); color: var(--accent-ink);
            font-size: 16px; font-weight: 600; padding: 14px 18px; cursor: pointer;
        }
        .btn:hover { filter: brightness(1.06); }
        .tz { font-size: 13px; color: var(--muted); margin: -6px 0 16px; }
        @media (prefers-color-scheme: dark) {
            :root {
                --ink: #e8eaed; --muted: #9aa4b2; --line: #2c313a;
                --bg: #16191e; --card: #1d2127;
            }
            .slot label { background: #1d2127; }
            .slot label:hover, .slot label:focus-within { background: #232a36; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand">{{ config('app.name') }}</div>
    <div class="card">
        @yield('content')
    </div>
    <p class="meta">{{ __('interview.page.footer_note') }}</p>
</div>
</body>
</html>
