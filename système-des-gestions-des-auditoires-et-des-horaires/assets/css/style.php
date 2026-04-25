<?php
header('Content-Type: text/css; charset=UTF-8');
?>
:root {
    --bg: #f4f7f5;
    --card: #ffffff;
    --ink: #1f2a2e;
    --muted: #627179;
    --primary: #0f766e;
    --primary-2: #115e59;
    --danger: #b91c1c;
    --warning: #b45309;
    --success: #166534;
    --border: #d7e0dc;
    --shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

* { box-sizing: border-box; }
body {
    margin: 0;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    color: var(--ink);
    background:
        radial-gradient(circle at 10% -20%, #d1fae5 0%, transparent 35%),
        radial-gradient(circle at 90% -10%, #ccfbf1 0%, transparent 30%),
        var(--bg);
}

.container {
    width: min(1100px, 94%);
    margin: 0 auto;
}

.topbar {
    background: linear-gradient(120deg, #0f766e, #134e4a);
    color: #fff;
    box-shadow: var(--shadow);
}

.topbar-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 16px 0;
}

.logo {
    margin: 0;
    font-size: 1.25rem;
}

.menu {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.menu a {
    color: #e8fffc;
    text-decoration: none;
    padding: 8px 12px;
    border-radius: 8px;
    transition: 0.2s ease;
}

.menu a:hover,
.menu a.active {
    background: rgba(255, 255, 255, 0.18);
}

.main-content {
    padding: 24px 0 40px;
    min-height: calc(100vh - 140px);
}

.card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    box-shadow: var(--shadow);
    padding: 18px;
    margin-bottom: 16px;
}

.hero {
    background: linear-gradient(135deg, #0f766e, #134e4a);
    color: #f0fdf4;
}

.hero h2,
.hero p {
    color: inherit;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 14px;
}

.kpi {
    background: linear-gradient(165deg, #ffffff, #edf7f4);
    border: 1px solid #cde3db;
    border-radius: 12px;
    padding: 14px;
}

.kpi h3 {
    margin: 0 0 8px;
    font-size: 0.95rem;
    color: var(--muted);
}

.kpi p {
    margin: 0;
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--primary-2);
}

.table-wrap {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

th, td {
    border-bottom: 1px solid var(--border);
    text-align: left;
    padding: 11px;
    white-space: nowrap;
}

th {
    background: #effaf8;
    color: #134e4a;
}

form {
    display: grid;
    gap: 10px;
}

.input-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
}

label {
    font-size: 0.9rem;
    color: var(--muted);
}

input, select {
    width: 100%;
    border: 1px solid #cbd5d1;
    border-radius: 8px;
    padding: 10px;
    font-size: 0.95rem;
}

.btn {
    border: none;
    border-radius: 8px;
    padding: 10px 14px;
    color: white;
    background: var(--primary);
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn:hover { background: var(--primary-2); }
.btn-danger { background: var(--danger); }
.btn-warning { background: var(--warning); }

.actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.alert {
    border-radius: 10px;
    padding: 10px 12px;
    margin-bottom: 14px;
    border: 1px solid transparent;
}

.alert-success {
    background: #dcfce7;
    border-color: #86efac;
    color: #166534;
}

.alert-error {
    background: #fee2e2;
    border-color: #fca5a5;
    color: #991b1b;
}

.auth-card {
    max-width: 520px;
    margin: 32px auto;
}

.auth-form {
    margin-top: 18px;
}

.muted {
    color: var(--muted);
    margin-top: 14px;
}

.badge {
    display: inline-block;
    padding: 4px 9px;
    border-radius: 99px;
    font-size: 0.8rem;
    background: #ecfeff;
    color: #155e75;
}

.footer {
    border-top: 1px solid var(--border);
    background: #f0f7f4;
    color: #4b5c62;
    padding: 12px 0;
}

@media (max-width: 768px) {
    .logo { font-size: 1rem; }
    .menu { gap: 6px; }
    .menu a { padding: 7px 9px; font-size: 0.9rem; }
}
