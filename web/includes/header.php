<?php require_once __DIR__ . '/functions.php'; ?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars(APP_NAME); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/UniSmartPay/assets/css/style.css">
  <style>
    /* ─── RESET & BASE ─────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:            #05081a;
      --bg2:           #0b0f28;
      --surface:       rgba(255,255,255,0.040);
      --surface-hover: rgba(255,255,255,0.065);
      --border:        rgba(148,163,255,0.10);
      --border-hover:  rgba(148,163,255,0.22);
      --text:          #dde4ff;
      --text-strong:   #f4f6ff;
      --muted:         #6b7a99;
      --accent:        #6366f1;
      --accent-light:  #818cf8;
      --accent-glow:   rgba(99,102,241,0.28);
      --cyan:          #22d3ee;
      --success:       #10b981;
      --success-bg:    rgba(16,185,129,0.10);
      --success-bd:    rgba(16,185,129,0.28);
      --danger:        #f43f5e;
      --danger-bg:     rgba(244,63,94,0.10);
      --danger-bd:     rgba(244,63,94,0.28);
      --warning:       #f59e0b;
      --warning-bg:    rgba(245,158,11,0.10);
      --warning-bd:    rgba(245,158,11,0.28);
      --radius:        16px;
      --radius-sm:     10px;
      --radius-xs:     7px;
      --font-head:     'Outfit', sans-serif;
      --font-body:     'Plus Jakarta Sans', sans-serif;
      --shadow:        0 4px 24px rgba(0,0,0,0.4);
    }

    html { scroll-behavior: smooth; }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: var(--font-body);
      font-size: 14px;
      line-height: 1.65;
      min-height: 100vh;
      background-image:
        radial-gradient(ellipse 90% 55% at 15% -10%, rgba(99,102,241,0.13) 0%, transparent 60%),
        radial-gradient(ellipse 55% 40% at 85% 105%, rgba(34,211,238,0.07) 0%, transparent 50%);
      -webkit-font-smoothing: antialiased;
    }

    /* ─── TOPBAR ────────────────────────────────────────── */
    .topbar {
      position: sticky;
      top: 0;
      z-index: 100;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 14px 28px;
      background: rgba(5,8,26,0.82);
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
      border-bottom: 1px solid var(--border);
    }
    .topbar-right {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }

    .brand {
      font-family: var(--font-head);
      font-size: 19px;
      font-weight: 800;
      background: linear-gradient(130deg, #818cf8 0%, #22d3ee 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -0.4px;
      white-space: nowrap;
    }
    .brand i { -webkit-text-fill-color: initial; color: var(--accent-light); margin-right: 6px; }

    /* ─── CONTAINER ─────────────────────────────────────── */
    .container {
      max-width: 1100px;
      margin: 0 auto;
      padding: 36px 20px 72px;
    }

    /* ─── PAGE HEADER ───────────────────────────────────── */
    .page-header { margin-bottom: 28px; }
    .page-header .h1 { margin-bottom: 4px; }

    /* ─── GRID ──────────────────────────────────────────── */
    .grid {
      display: grid;
      grid-template-columns: repeat(12, 1fr);
      gap: 18px;
    }
    .col-12 { grid-column: span 12; }
    .col-8  { grid-column: span 8; }
    .col-6  { grid-column: span 6; }
    .col-4  { grid-column: span 4; }
    .col-3  { grid-column: span 3; }

    /* ─── CARD ──────────────────────────────────────────── */
    .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 24px;
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      transition: border-color 0.22s, background 0.22s, transform 0.22s;
      animation: fadeUp 0.45s ease both;
    }
    .card:hover {
      border-color: var(--border-hover);
      background: var(--surface-hover);
    }
    .card-accent {
      border-color: rgba(99,102,241,0.28);
      background: rgba(99,102,241,0.06);
    }
    .card-success {
      border-color: var(--success-bd);
      background: var(--success-bg);
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(16px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .grid > .card:nth-child(1) { animation-delay: 0.04s; }
    .grid > .card:nth-child(2) { animation-delay: 0.09s; }
    .grid > .card:nth-child(3) { animation-delay: 0.14s; }
    .grid > .card:nth-child(4) { animation-delay: 0.19s; }

    /* ─── TYPOGRAPHY ─────────────────────────────────────── */
    .h1 {
      font-family: var(--font-head);
      font-size: 26px;
      font-weight: 800;
      color: var(--text-strong);
      letter-spacing: -0.5px;
      line-height: 1.2;
    }
    .h2 {
      font-family: var(--font-head);
      font-size: 13px;
      font-weight: 600;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 0.07em;
      margin-bottom: 16px;
    }
    .card-title {
      font-family: var(--font-head);
      font-size: 17px;
      font-weight: 700;
      color: var(--text-strong);
      margin-bottom: 18px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .card-title i {
      width: 32px;
      height: 32px;
      background: var(--accent-glow);
      border: 1px solid rgba(99,102,241,0.25);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      color: var(--accent-light);
      flex-shrink: 0;
    }
    .stat-value {
      font-family: var(--font-head);
      font-size: 38px;
      font-weight: 800;
      color: var(--text-strong);
      letter-spacing: -1.5px;
      line-height: 1;
      margin: 8px 0 4px;
    }
    .stat-label {
      font-size: 12px;
      color: var(--muted);
      font-weight: 500;
    }

    /* ─── BUTTONS ────────────────────────────────────────── */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
      padding: 9px 18px;
      border-radius: var(--radius-sm);
      font-family: var(--font-body);
      font-size: 13px;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      border: 1px solid var(--border);
      background: var(--surface);
      color: var(--text);
      transition: all 0.18s;
      white-space: nowrap;
      line-height: 1;
    }
    .btn:hover {
      background: var(--surface-hover);
      border-color: var(--border-hover);
      transform: translateY(-1px);
    }
    .btn:active { transform: translateY(0); }

    .btn-primary {
      background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
      border-color: transparent;
      color: #fff;
      box-shadow: 0 2px 14px rgba(99,102,241,0.38);
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #818cf8 0%, #6366f1 100%);
      box-shadow: 0 4px 20px rgba(99,102,241,0.50);
      border-color: transparent;
    }
    .btn-success {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      border-color: transparent;
      color: #fff;
      box-shadow: 0 2px 14px rgba(16,185,129,0.32);
    }
    .btn-success:hover {
      background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
      box-shadow: 0 4px 20px rgba(16,185,129,0.44);
      border-color: transparent;
    }
    .btn-danger {
      background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
      border-color: transparent;
      color: #fff;
      box-shadow: 0 2px 12px rgba(244,63,94,0.28);
    }
    .btn-danger:hover {
      background: linear-gradient(135deg, #fb7185 0%, #f43f5e 100%);
      box-shadow: 0 4px 18px rgba(244,63,94,0.4);
      border-color: transparent;
    }
    .btn-sm { padding: 6px 12px; font-size: 12px; }
    .btn-lg { padding: 12px 24px; font-size: 15px; border-radius: 12px; }

    /* ─── FORM ───────────────────────────────────────────── */
    .form-group { margin-bottom: 18px; }
    .form-label {
      display: block;
      font-size: 12px;
      font-weight: 600;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 0.07em;
      margin-bottom: 7px;
    }
    .input {
      width: 100%;
      padding: 11px 14px;
      background: rgba(255,255,255,0.045);
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      color: var(--text-strong);
      font-family: var(--font-body);
      font-size: 14px;
      transition: border-color 0.18s, background 0.18s, box-shadow 0.18s;
    }
    .input::placeholder { color: var(--muted); }
    .input:focus {
      outline: none;
      border-color: var(--accent);
      background: rgba(99,102,241,0.055);
      box-shadow: 0 0 0 3px rgba(99,102,241,0.14);
    }

    /* ─── BADGE ──────────────────────────────────────────── */
    .badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 13px;
      background: rgba(99,102,241,0.11);
      border: 1px solid rgba(99,102,241,0.24);
      border-radius: 999px;
      color: var(--accent-light);
      font-size: 12.5px;
      font-weight: 600;
      white-space: nowrap;
    }
    .badge-blue { }
    .badge-success {
      background: var(--success-bg);
      border-color: var(--success-bd);
      color: #6ee7b7;
    }
    .badge-danger {
      background: var(--danger-bg);
      border-color: var(--danger-bd);
      color: #fda4af;
    }
    .badge-warning {
      background: var(--warning-bg);
      border-color: var(--warning-bd);
      color: #fcd34d;
    }
    .badge-muted {
      background: rgba(107,122,153,0.12);
      border-color: rgba(107,122,153,0.22);
      color: var(--muted);
    }

    /* ─── ALERTS ─────────────────────────────────────────── */
    .alert {
      padding: 13px 16px;
      border-radius: var(--radius-sm);
      font-size: 13.5px;
      font-weight: 500;
      border: 1px solid;
      display: flex;
      align-items: flex-start;
      gap: 10px;
      line-height: 1.5;
    }
    .alert { background: rgba(148,163,255,0.07); border-color: rgba(148,163,255,0.18); color: var(--text); }
    .alert-success { background: var(--success-bg); border-color: var(--success-bd); color: #6ee7b7; }
    .alert-danger  { background: var(--danger-bg);  border-color: var(--danger-bd);  color: #fda4af; }
    .alert-warning { background: var(--warning-bg); border-color: var(--warning-bd); color: #fcd34d; }
    .alert i { margin-top: 1px; flex-shrink: 0; }

    /* ─── TABLE ──────────────────────────────────────────── */
    .table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }
    .table th {
      text-align: left;
      padding: 10px 14px;
      color: var(--muted);
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      border-bottom: 1px solid var(--border);
      white-space: nowrap;
    }
    .table td {
      padding: 13px 14px;
      border-bottom: 1px solid rgba(255,255,255,0.035);
      color: var(--text);
      vertical-align: middle;
    }
    .table tbody tr { transition: background 0.15s; }
    .table tbody tr:hover { background: rgba(255,255,255,0.025); }
    .table tbody tr:last-child td { border-bottom: none; }

    /* ─── INFO ROW ───────────────────────────────────────── */
    .info-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 11px 0;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      font-size: 13.5px;
    }
    .info-row:last-child { border-bottom: none; padding-bottom: 0; }
    .info-row:first-child { padding-top: 0; }
    .info-row .key { color: var(--muted); font-weight: 500; }
    .info-row .val { color: var(--text-strong); font-weight: 600; text-align: right; }

    /* ─── DIVIDER ─────────────────────────────────────────── */
    .divider {
      height: 1px;
      background: var(--border);
      margin: 20px 0;
    }

    /* ─── SCANNER BOX ────────────────────────────────────── */
    .scanner-box {
      width: 100%;
      height: 280px;
      border: 1.5px dashed rgba(99,102,241,0.30);
      border-radius: 12px;
      overflow: hidden;
      background: rgba(99,102,241,0.04);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--muted);
      font-size: 13px;
    }
    .scanner-box video { width: 100%; height: 100%; object-fit: contain; background: var(--bg); }
    .scanner-box canvas { width: 100%; height: 100%; object-fit: contain; }

    /* ─── PRE / SERIAL ───────────────────────────────────── */
    pre.serial-monitor {
      margin-top: 10px;
      background: rgba(0,0,0,0.35);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 14px 16px;
      max-height: 260px;
      overflow: auto;
      white-space: pre-wrap;
      font-family: 'Courier New', monospace;
      font-size: 12px;
      color: #a5b4fc;
      line-height: 1.6;
    }

    /* ─── BUTTON GROUPS ──────────────────────────────────── */
    .btn-group {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 16px;
    }

    /* ─── LOGIN CARD ─────────────────────────────────────── */
    .login-wrap {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 16px;
    }
    .login-card {
      width: 100%;
      max-width: 420px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 36px 32px;
      backdrop-filter: blur(14px);
      animation: fadeUp 0.5s ease both;
    }
    .login-logo {
      font-family: var(--font-head);
      font-size: 22px;
      font-weight: 800;
      background: linear-gradient(130deg, #818cf8 0%, #22d3ee 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -0.4px;
      margin-bottom: 28px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .login-logo::before {
      content: '';
      display: inline-block;
      width: 8px;
      height: 8px;
      background: var(--accent-light);
      border-radius: 50%;
      box-shadow: 0 0 8px var(--accent-light);
      -webkit-text-fill-color: initial;
    }
    .login-title {
      font-family: var(--font-head);
      font-size: 24px;
      font-weight: 800;
      color: var(--text-strong);
      letter-spacing: -0.4px;
      margin-bottom: 4px;
    }
    .login-sub {
      color: var(--muted);
      font-size: 13.5px;
      margin-bottom: 28px;
    }

    /* ─── HERO ───────────────────────────────────────────── */
    .hero {
      text-align: center;
      padding: 48px 20px 36px;
    }
    .hero .h1 { font-size: 36px; margin-bottom: 10px; }
    .hero .sub { font-size: 15px; color: var(--muted); max-width: 480px; margin: 0 auto 28px; }

    /* ─── SMALL HELPERS (used by index) ─────────────────── */
    .text-muted { color: var(--muted); }
    .section-title {
      font-family: var(--font-head);
      font-size: 32px;
      font-weight: 800;
      color: var(--text-strong);
      letter-spacing: -0.6px;
      line-height: 1.2;
    }
    .section-sub { color: var(--muted); font-size: 14px; margin-top: 6px; }
    .demo-hint {
      margin-top: 16px;
      padding: 12px 14px;
      border: 1px solid var(--border);
      border-radius: 12px;
      background: rgba(255,255,255,0.03);
      font-size: 12px;
      color: var(--muted);
    }
    code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }

    /* ─── RESPONSIVE ─────────────────────────────────────── */
    @media (max-width: 760px) {
      .col-6, .col-8 { grid-column: span 12; }
      .col-4  { grid-column: span 12; }
      .col-3  { grid-column: span 6; }
      .container { padding: 22px 14px 50px; }
      .topbar { padding: 12px 16px; }
      .stat-value { font-size: 30px; }
      .h1, .hero .h1 { font-size: 22px; }
      .login-card { padding: 28px 20px; }
      .section-title { font-size: 24px; }
    }
    @media (max-width: 400px) {
      .col-3 { grid-column: span 12; }
    }
  </style>
</head>
<body>
