<?php
header('Content-Type: text/css; charset=UTF-8');
?>
:root {
    --bg: #0f1419;
    --card: #1a2027;
    --ink: #e5e7eb;
    --muted: #9ca3af;
    --primary: #2d6a4f;
    --primary-2: #1b4332;
    --danger: #ef4444;
    --warning: #f59e0b;
    --success: #10b981;
    --border: #374151;
    --shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

* { box-sizing: border-box; }
body {
    margin: 0;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    color: var(--ink);
    background: var(--bg);
}

.container {
    width: min(1100px, 94%);
    margin: 0 auto;
}

.topbar {
    background: linear-gradient(120deg, #1b4332, #0f2818);
    color: #d1f0e8;
    box-shadow: var(--shadow);
    border-bottom: 2px solid #2d6a4f;
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
    color: #d1f0e8;
    text-decoration: none;
    padding: 8px 12px;
    border-radius: 8px;
    transition: 0.2s ease;
}

.menu a:hover,
.menu a.active {
    background: rgba(45, 106, 79, 0.4);
    color: #40916c;
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
    background: linear-gradient(135deg, #1b4332, #0f2818);
    color: #d1f0e8;
    border: 1px solid #2d6a4f;
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
    background: linear-gradient(165deg, #1a2027, #0f1419);
    border: 1px solid #2d6a4f;
    border-radius: 12px;
    padding: 14px;
}

.kpi h3 {
    margin: 0 0 8px;
    font-size: 0.95rem;
    color: #9ca3af;
}

.kpi p {
    margin: 0;
    font-size: 1.6rem;
    font-weight: 700;
    color: #40916c;
}

.table-wrap {
    overflow-x: auto;
}

.table-tools {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.table-filter {
    min-width: 220px;
    max-width: 360px;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: var(--card);
}

th, td {
    border-bottom: 1px solid var(--border);
    text-align: left;
    padding: 11px;
    white-space: nowrap;
}

th {
    background: #0f2818;
    color: #40916c;
    font-weight: 600;
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
    color: #d1e7dd;
}

input, select {
    width: 100%;
    border: 1px solid #2d6a4f;
    border-radius: 8px;
    padding: 10px;
    font-size: 0.95rem;
    background: #0f2818;
    color: #d1e7dd;
}

input:focus, select:focus {
    outline: none;
    border-color: #40916c;
    box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.2);
}

.btn {
    border: none;
    border-radius: 8px;
    padding: 10px 14px;
    color: white;
    background: #52a889;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border: 1px solid #40916c;
}

.btn:hover { 
    background: #40916c;
    border-color: #52a889;
    box-shadow: 0 4px 12px rgba(64, 145, 108, 0.3);
}

.btn-danger { 
    background: #dc2626;
    border-color: #ef4444;
}

.btn-danger:hover {
    background: #b91c1c;
    border-color: #dc2626;
}

.btn-warning { 
    background: #d97706;
    border-color: #f59e0b;
}

.btn-warning:hover {
    background: #b45309;
    border-color: #d97706;
}

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
    background: rgba(16, 185, 129, 0.15);
    border-color: #10b981;
    color: #a7f3d0;
}

.alert-error {
    background: rgba(239, 68, 68, 0.15);
    border-color: #ef4444;
    color: #fca5a5;
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
    background: rgba(45, 106, 79, 0.25);
    color: #a7f3d0;
}

.blur-lock {
    position: relative;
    border: 1px dashed #2d6a4f;
    border-radius: 12px;
    overflow: hidden;
}

.blurred-content {
    filter: blur(4px);
    opacity: 0.5;
    pointer-events: none;
    user-select: none;
    padding: 10px;
}

.blur-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 16px;
    font-weight: 700;
    color: #a7f3d0;
    background: rgba(15, 40, 24, 0.85);
}

.footer {
    border-top: 1px solid var(--border);
    background: #0f1419;
    color: #9ca3af;
    padding: 12px 0;
}

h1, h2, h3, h4, h5, h6 {
    color: #e5e7eb;
}

a {
    color: #40916c;
    text-decoration: none;
}

a:hover {
    color: #5eead4;
}

p {
    color: #d1e7eb;
}

.spacing-top {
    margin-top: 40px;
}

@media (max-width: 768px) {
    .logo { font-size: 1rem; }
    .menu { gap: 6px; }
    .menu a { padding: 7px 9px; font-size: 0.9rem; }
}
