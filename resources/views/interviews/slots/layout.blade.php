{{--
    Shell for the three candidate-facing pages.

    Deliberately self-contained — no @vite, no font CDN, no JavaScript. This
    page is the single point where an invitation either converts into a booking
    or is lost, and it is opened on unknown devices and networks, often from a
    mail client's in-app browser. A stale asset manifest or a blocked CDN must
    not be able to turn it into an unstyled or broken page.
--}}
<!DOCTYPE html>
<html lang="{{ $htmlLang ?? str_replace('_', '-', app()->getLocale()) }}">
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

        /* ── Calendar ──────────────────────────────────────────────────────
           The day strip and the time grid are driven by radio buttons and
           sibling selectors, with no script of any kind. This page is opened
           inside mail clients' embedded browsers on phones nobody has a list
           of, and a date picker that needs JavaScript to render is a date
           picker that is sometimes a blank rectangle. The radios themselves
           are moved off-screen rather than `display:none`, which would take
           them out of the tab order and off screen readers. */
        .daytab {
            position: absolute; width: 1px; height: 1px;
            opacity: 0; pointer-events: none;
        }
        .daystrip {
            display: flex; gap: 8px; overflow-x: auto; padding: 2px 2px 10px;
            margin-bottom: 4px; -webkit-overflow-scrolling: touch;
        }
        .dayblock {
            flex: 0 0 auto; width: 62px; text-align: center; cursor: pointer;
            border: 1px solid var(--line); border-radius: 10px;
            padding: 8px 4px; background: var(--card); line-height: 1.25;
        }
        .dayblock .wd { display: block; font-size: 11px; color: var(--muted); text-transform: uppercase; }
        .dayblock .dn { display: block; font-size: 19px; font-weight: 600; }
        .dayblock .mo { display: block; font-size: 11px; color: var(--muted); }
        .dayblock.full { opacity: .45; }
        .dayblock.full .dn { text-decoration: line-through; }
        .daytab:focus-visible + .daystrip .dayblock,
        .dayblock:hover { border-color: var(--accent); }

        .panel { display: none; }
        .times {
            display: grid; gap: 8px; margin: 4px 0 20px;
            grid-template-columns: repeat(auto-fill, minmax(78px, 1fr));
        }
        .time {
            display: block; position: relative; text-align: center;
            border: 1px solid var(--line); border-radius: 9px;
            /* 44px tall: a tap target a thumb hits first time. */
            padding: 12px 4px; font-size: 15px; font-variant-numeric: tabular-nums;
            background: var(--card);
        }
        .time input {
            position: absolute; width: 1px; height: 1px; opacity: 0;
        }
        label.time { cursor: pointer; }
        label.time:hover, label.time:focus-within { border-color: var(--accent); }
        .time input:checked ~ span {
            /* The whole cell, not a dot beside it — on a phone the dot is the
               part that does not get looked at. */
            position: absolute; inset: 0; display: flex;
            align-items: center; justify-content: center;
            background: var(--accent); color: var(--accent-ink);
            border-radius: 8px; font-weight: 600;
        }
        .time.gone {
            color: var(--muted); opacity: .4; text-decoration: line-through;
            border-style: dashed;
        }
        .dayempty { color: var(--muted); font-size: 14px; margin: 4px 0 20px; }

        @media (prefers-color-scheme: dark) {
            :root {
                --ink: #e8eaed; --muted: #9aa4b2; --line: #2c313a;
                --bg: #16191e; --card: #1d2127;
            }
            .slot label { background: #1d2127; }
            .slot label:hover, .slot label:focus-within { background: #232a36; }
        }
    </style>
    @stack('styles')
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
