<?php
// index.php
require_once 'config/database.php';
requireLogin();

$session = getSession();
$user = $session['user'];
$page = $_GET['page'] ?? 'dashboard';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FrutasPH - Inventory Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #F5F3EE;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        .sidebar {
            width: 220px;
            background: #fff;
            border-right: 1px solid #E8E5DF;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            transition: width 0.3s ease;
            overflow: hidden;
        }
        .sidebar.collapsed { width: 64px; }
        
        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 16px;
            border-bottom: 1px solid #E8E5DF;
            min-height: 60px;
        }
        .sidebar-header .logo { font-size: 22px; }
        .sidebar-header .title {
            font-weight: 800;
            font-size: 15px;
            color: #2D5A27;
            white-space: nowrap;
        }
        .sidebar-header .toggle {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: #888;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.2s;
            margin-left: auto;
        }
        .sidebar-header .toggle:hover {
            background: #F0F7EE;
            color: #4F7942;
        }
        
        .sidebar.collapsed .title { display: none; }
        .sidebar.collapsed .nav-item span:not(.icon) { display: none; }
        .sidebar.collapsed .user-info { display: none; }
        .sidebar.collapsed .logout-btn { display: none; }
        .sidebar.collapsed .sidebar-header { justify-content: center; }
        .sidebar.collapsed .sidebar-header .logo { margin: 0; }
        .sidebar.collapsed .sidebar-header .toggle { 
            margin-left: 0 !important; 
            margin-right: auto !important;
            display: inline-block !important;
        }
        .sidebar:not(.collapsed) .sidebar-header .toggle { 
            margin-left: auto !important; 
            margin-right: 0 !important;
        }
        .sidebar.collapsed .nav-item { justify-content: center; padding: 10px; }

        .nav { flex: 1; padding: 10px 0; }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 10px 16px;
            background: none;
            border: none;
            cursor: pointer;
            border-left: 3px solid transparent;
            color: #555;
            font-size: 14px;
            text-align: left;
            white-space: nowrap;
            text-decoration: none;
        }
        .nav-item:hover { background: #F5F3EE; }
        .nav-item.active {
            background: #F0F7EE;
            border-left-color: #4F7942;
            color: #4F7942;
            font-weight: 600;
        }
        .nav-item .icon { font-size: 16px; width: 20px; text-align: center; }
        
        .sidebar-footer {
            padding: 12px 16px;
            border-top: 1px solid #E8E5DF;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #4F7942;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
            flex-shrink: 0;
        }
        .sidebar-footer .user-info { flex: 1; min-width: 0; }
        .sidebar-footer .user-name {
            font-size: 13px;
            font-weight: 600;
            color: #222;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-footer .user-role {
            font-size: 11px;
            color: #888;
        }
        .logout-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #aaa;
            text-decoration: none;
        }
        .logout-btn:hover {
            color: #EF4444;
        }

        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .header {
            background: #fff;
            border-bottom: 1px solid #E8E5DF;
            padding: 0 28px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }
        .header .page-title { font-size: 16px; font-weight: 700; color: #222; }
        .header-right { display: flex; align-items: center; gap: 12px; }
        .badge {
            background: #FEE2E2;
            color: #991B1B;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 600;
        }
        .content { flex: 1; overflow: auto; padding: 28px; }

        .stats { display: flex; gap: 16px; flex-wrap: wrap; }
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px 22px;
            flex: 1;
            min-width: 150px;
        }
        .stat-card .label { font-size: 12px; color: #888; font-weight: 500; margin-bottom: 6px; }
        .stat-card .value { font-size: 24px; font-weight: 800; color: #1a1a1a; }
        .stat-card .sub { font-size: 12px; color: #888; margin-top: 4px; }
        .stat-card .icon { font-size: 26px; float: right; }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 22px;
        }
        .card-title { margin: 0 0 16px; font-size: 15px; font-weight: 700; color: #222; }
        .card-title.mb-18 { margin-bottom: 18px; }

        .table-wrap {
            background: #fff;
            border-radius: 12px;
            overflow: auto;
        }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th {
            padding: 11px 14px;
            text-align: left;
            color: #888;
            font-weight: 600;
            font-size: 12px;
            background: #F9F8F5;
            border-bottom: 1px solid #E8E5DF;
        }
        td { padding: 10px 14px; border-top: 1px solid #F0EDE8; }
        .fw-600 { font-weight: 600; }
        .fw-700 { font-weight: 700; }
        .text-danger { color: #EF4444; }
        .text-success { color: #166534; }
        .text-muted { color: #888; }

        .badge-status {
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-green { background: #DCFCE7; color: #166534; }
        .badge-red { background: #FEE2E2; color: #991B1B; }
        .badge-yellow { background: #FEF9C3; color: #854D0E; }
        .badge-blue { background: #DBEAFE; color: #1E40AF; }
        .badge-purple { background: #EDE9FE; color: #5B21B6; }

        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary { background: #4F7942; color: #fff; }
        .btn-primary:hover { background: #3d6133; }
        .btn-outline {
            background: #fff;
            border: 1.5px solid #E0DDD8;
            color: #555;
        }
        .btn-outline:hover { background: #F5F3EE; }
        .btn-danger {
            background: #fff;
            border: 1px solid #FCA5A5;
            color: #EF4444;
        }
        .btn-danger:hover { background: #FEE2E2; }
        .btn-sm { padding: 4px 10px; font-size: 12px; }
        .btn-group { display: flex; gap: 6px; flex-wrap: wrap; }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }
        .modal {
            background: #fff;
            border-radius: 14px;
            padding: 28px;
            width: 480px;
            max-height: 90vh;
            overflow: auto;
        }
        .modal h3 { margin: 0 0 20px; font-size: 17px; font-weight: 700; }
        .modal .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .modal .form-grid .full { grid-column: span 2; }
        .modal label {
            font-size: 12px;
            font-weight: 600;
            color: #666;
            display: block;
            margin-bottom: 4px;
        }
        .modal input, .modal select, .modal textarea {
            width: 100%;
            padding: 8px 10px;
            border-radius: 7px;
            border: 1.5px solid #E0DDD8;
            font-size: 13px;
            box-sizing: border-box;
        }
        .modal .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 22px;
            justify-content: flex-end;
        }

        .flex { display: flex; }
        .gap-12 { gap: 12px; }
        .gap-16 { gap: 16px; }
        .gap-8 { gap: 8px; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .justify-end { justify-content: flex-end; }
        .flex-1 { flex: 1; }
        .min-w-0 { min-width: 0; }
        .mt-12 { margin-top: 12px; }
        .mb-16 { margin-bottom: 16px; }
        .mb-20 { margin-bottom: 20px; }
        .w-full { width: 100%; }
        .text-center { text-align: center; }

        .product-bar {
            margin-top: 4px;
            height: 4px;
            background: #E8E5DF;
            border-radius: 4px;
            overflow: hidden;
        }
        .product-bar .fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s;
        }
        .product-bar .fill-danger { background: #EF4444; }
        .product-bar .fill-success { background: #4F7942; }

        .stock-item {
            background: #F9F8F5;
            border-radius: 10px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .stock-item .emoji { font-size: 28px; }

        .chart-container {
            height: 220px;
            position: relative;
            width: 100%;
        }

        @media (max-width: 768px) {
            .sidebar { width: 64px; }
            .sidebar .title, .sidebar .nav-item span:not(.icon),
            .sidebar .user-info, .sidebar .logout-btn { display: none; }
            .sidebar .nav-item { justify-content: center; padding: 10px; }
            .grid-2 { grid-template-columns: 1fr; }
            .grid-3 { grid-template-columns: 1fr; }
            .modal { width: 95%; padding: 20px; }
            .stats { flex-direction: column; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <span class="logo">🍎</span>
        <span class="title">FrutasPH</span>
        <button class="toggle" onclick="toggleSidebar()" id="toggleBtn">☰</button>
    </div>
    <nav class="nav">
        <a href="?page=dashboard" class="nav-item <?= $page === 'dashboard' ? 'active' : '' ?>">
            <span class="icon">⬡</span><span>Dashboard</span>
        </a>
        <a href="?page=inventory" class="nav-item <?= $page === 'inventory' ? 'active' : '' ?>">
            <span class="icon">📦</span><span>Inventory</span>
        </a>
        <a href="?page=transactions" class="nav-item <?= $page === 'transactions' ? 'active' : '' ?>">
            <span class="icon">↕</span><span>Transactions</span>
        </a>
        <a href="?page=reports" class="nav-item <?= $page === 'reports' ? 'active' : '' ?>">
            <span class="icon">📊</span><span>Reports</span>
        </a>
        <?php if ($user['role'] === 'Admin'): ?>
        <a href="?page=users" class="nav-item <?= $page === 'users' ? 'active' : '' ?>">
            <span class="icon">👥</span><span>User Management</span>
        </a>
        <?php endif; ?>
        <a href="?page=profile" class="nav-item <?= $page === 'profile' ? 'active' : '' ?>">
            <span class="icon">👤</span><span>Profile</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="avatar"><?= htmlspecialchars($user['avatar']) ?></div>
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
            <div class="user-role"><?= htmlspecialchars($user['role']) ?></div>
        </div>
        <a href="logout.php" class="logout-btn" title="Logout">🚪</a>
    </div>
</aside>

<!-- Main -->
<main class="main">
    <header class="header">
        <span class="page-title">
            <?php
            $titles = ['dashboard' => 'Dashboard', 'inventory' => 'Inventory', 'transactions' => 'Transactions', 
                      'reports' => 'Reports', 'users' => 'User Management', 'profile' => 'Profile'];
            echo $titles[$page] ?? 'Dashboard';
            ?>
        </span>
        <div class="header-right">
            <span class="badge" id="lowStockBadge">⚠ Loading...</span>
            <div class="avatar"><?= htmlspecialchars($user['avatar']) ?></div>
        </div>
    </header>

    <div class="content" id="pageContent">
        <?php if ($error === 'unauthorized'): ?>
            <div style="background:#FEE2E2;color:#991B1B;border-radius:8px;padding:12px 16px;margin-bottom:16px;">
                You don't have permission to access this page.
            </div>
        <?php endif; ?>
        <div id="content-area">
            <div style="text-align:center;padding:40px;color:#888;">Loading...</div>
        </div>
    </div>
</main>

<script>
// Global state
let currentUser = <?= json_encode($user) ?>;
let currentPage = '<?= $page ?>';
let products = [];
let transactions = [];
let users = [];
let chartInstances = { monthly: null, category: null };

function $(sel, el = document) { return el.querySelector(sel); }
function $$(sel, el = document) { return el.querySelectorAll(sel); }

// Toggle Sidebar - Fixed
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');
    
    sidebar.classList.toggle('collapsed');
    
    // Update toggle button position
    if (sidebar.classList.contains('collapsed')) {
        toggleBtn.style.marginLeft = '0';
        toggleBtn.style.marginRight = 'auto';
    } else {
        toggleBtn.style.marginLeft = 'auto';
        toggleBtn.style.marginRight = '0';
    }
}

async function fetchAPI(endpoint, options = {}) {
    const res = await fetch(endpoint, {
        ...options,
        headers: { 'Content-Type': 'application/json', ...options.headers }
    });
    if (!res.ok) throw new Error(await res.text());
    return res.json();
}

function destroyCharts() {
    if (chartInstances.monthly) {
        chartInstances.monthly.destroy();
        chartInstances.monthly = null;
    }
    if (chartInstances.category) {
        chartInstances.category.destroy();
        chartInstances.category = null;
    }
}

async function loadPage(page) {
    const area = $('#content-area');
    area.innerHTML = '<div style="text-align:center;padding:40px;color:#888;">Loading...</div>';
    
    switch(page) {
        case 'dashboard': await loadDashboard(area); break;
        case 'inventory': await loadInventory(area); break;
        case 'transactions': await loadTransactions(area); break;
        case 'reports': await loadReports(area); break;
        case 'users': await loadUsers(area); break;
        case 'profile': await loadProfile(area); break;
        default: area.innerHTML = '<div style="text-align:center;padding:40px;color:#888;">Page not found</div>';
    }
}

// ========== DASHBOARD ==========
async function loadDashboard(area) {
    try {
        destroyCharts();
        
        console.log('Loading dashboard...');
        const response = await fetch('api.php?action=dashboard');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Dashboard data received:', data);
        
        if (data.error) {
            area.innerHTML = `<div style="background:#FEE2E2;color:#991B1B;border-radius:8px;padding:12px 16px;">
                Error: ${data.error}
            </div>`;
            return;
        }
        
        const totalProducts = data.totalProducts || 0;
        const totalValue = data.totalValue || 0;
        const totalCost = data.totalCost || 0;
        const lowStockCount = data.lowStockCount || 0;
        const categories = data.categories || 0;
        const topProducts = data.topProducts || [];
        const lowStockProducts = data.lowStockProducts || [];
        const categoryData = data.categoryData || [];
        const monthlySales = data.monthlySales || [];
        
        function formatCurrency(value) {
            if (value >= 1000000) {
                return '₱' + (value / 1000000).toFixed(1) + 'M';
            } else if (value >= 1000) {
                return '₱' + (value / 1000).toFixed(0) + 'K';
            }
            return '₱' + value.toFixed(0);
        }
        
        area.innerHTML = `
            <div class="stats" style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:22px;">
                <div class="stat-card"><div class="icon">🍎</div>
                    <div class="label">Total Products</div>
                    <div class="value">${totalProducts}</div>
                    <div class="sub">${totalProducts - lowStockProducts.length} healthy stock</div>
                </div>
                <div class="stat-card"><div class="icon">💰</div>
                    <div class="label">Total Stock Value</div>
                    <div class="value" style="font-size:22px;">${formatCurrency(totalValue)}</div>
                    <div class="sub">Cost: ${formatCurrency(totalCost)}</div>
                </div>
                <div class="stat-card"><div class="icon">⚠️</div>
                    <div class="label">Low Stock Alerts</div>
                    <div class="value">${lowStockCount}</div>
                    <div class="sub">${lowStockCount > 0 ? 'Need restocking' : 'All good!'}</div>
                </div>
                <div class="stat-card"><div class="icon">📂</div>
                    <div class="label">Categories</div>
                    <div class="value">${categories}</div>
                    <div class="sub">Fruit types tracked</div>
                </div>
            </div>
            <div class="grid-2">
                <div class="card">
                    <div class="card-title mb-18">Monthly Revenue vs Expenses</div>
                    <div class="chart-container">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
                <div class="card">
                    <div class="card-title mb-18">Stock by Category</div>
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="card" style="margin-top:20px;">
                <div class="card-title">Top Products by Stock</div>
                <div class="grid-3">
                    ${topProducts.length > 0 ? topProducts.map(p => `
                        <div class="stock-item">
                            <span class="emoji">${p.image || '🍎'}</span>
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:700;font-size:13px;color:#222;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${p.name}</div>
                                <div style="font-size:12px;color:#888;">${p.stock} ${p.unit}s · ₱${p.price}/${p.unit}</div>
                                <div class="product-bar">
                                    <div class="fill ${p.stock <= p.min_stock ? 'fill-danger' : 'fill-success'}" style="width:${Math.min(100, (p.stock/350)*100)}%"></div>
                                </div>
                            </div>
                        </div>
                    `).join('') : '<div style="color:#888;">No products found</div>'}
                </div>
            </div>
            <div class="card" style="margin-top:20px;">
                <div class="card-title">Low Stock Alerts</div>
                ${lowStockProducts.length === 0 ? 
                    '<p style="color:#888;font-size:14px;">✅ All products are sufficiently stocked.</p>' :
                    `<div class="table-wrap"><table>
                        <thead><tr><th>Product</th><th>Category</th><th>Current Stock</th><th>Min Stock</th><th>Status</th></tr></thead>
                        <tbody>${lowStockProducts.map(p => `
                            <tr>
                                <td class="fw-600">${p.image || '🍎'} ${p.name}</td>
                                <td class="text-muted">${p.category}</td>
                                <td class="fw-700 text-danger">${p.stock} ${p.unit}s</td>
                                <td class="text-muted">${p.min_stock}</td>
                                <td><span class="badge-status ${p.stock === 0 ? 'badge-red' : 'badge-yellow'}">${p.stock === 0 ? 'Out of Stock' : 'Low Stock'}</span></td>
                            </tr>
                        `).join('')}</tbody>
                    </table></div>`
                }
            </div>
        `;

        setTimeout(() => {
            try {
                const monthlyData = monthlySales || [];
                const ctx1 = document.getElementById('monthlyChart')?.getContext('2d');
                
                if (ctx1 && monthlyData.length > 0) {
                    chartInstances.monthly = new Chart(ctx1, {
                        type: 'bar',
                        data: {
                            labels: monthlyData.map(d => d.month),
                            datasets: [
                                { 
                                    label: 'Revenue', 
                                    data: monthlyData.map(d => parseFloat(d.sales) || 0), 
                                    backgroundColor: '#4F7942', 
                                    borderRadius: 4 
                                },
                                { 
                                    label: 'Expenses', 
                                    data: monthlyData.map(d => parseFloat(d.expenses) || 0), 
                                    backgroundColor: '#A8D5A2', 
                                    borderRadius: 4 
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { 
                                legend: { 
                                    position: 'top', 
                                    labels: { boxWidth: 12, font: { size: 11 } } 
                                } 
                            },
                            scales: { 
                                y: { 
                                    beginAtZero: true, 
                                    ticks: { callback: v => '₱' + (v/1000).toFixed(0) + 'k' } 
                                },
                                x: {
                                    grid: { display: false }
                                }
                            }
                        }
                    });
                } else if (ctx1) {
                    chartInstances.monthly = new Chart(ctx1, {
                        type: 'bar',
                        data: {
                            labels: ['No Data'],
                            datasets: [
                                { label: 'Revenue', data: [0], backgroundColor: '#E5E7EB' },
                                { label: 'Expenses', data: [0], backgroundColor: '#E5E7EB' }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { 
                                legend: { position: 'top', labels: { boxWidth: 12 } },
                                tooltip: { enabled: false }
                            },
                            scales: { 
                                y: { beginAtZero: true, max: 1, grid: { display: false } },
                                x: { grid: { display: false } }
                            }
                        },
                        plugins: [{
                            id: 'noDataText',
                            afterDraw: function(chart) {
                                const ctx = chart.ctx;
                                chart.width = chart.width || chart.canvas.width;
                                chart.height = chart.height || chart.canvas.height;
                                ctx.save();
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';
                                ctx.font = '14px Inter, sans-serif';
                                ctx.fillStyle = '#9CA3AF';
                                ctx.fillText('No monthly sales data available', chart.width/2, chart.height/2);
                                ctx.restore();
                            }
                        }]
                    });
                }

                const catData = categoryData || [];
                const ctx2 = document.getElementById('categoryChart')?.getContext('2d');
                
                if (ctx2 && catData.length > 0) {
                    const colors = { Mango:'#F59E0B', Banana:'#EAB308', Pineapple:'#84CC16', Exotic:'#8B5CF6', Papaya:'#F97316', Citrus:'#22C55E' };
                    chartInstances.category = new Chart(ctx2, {
                        type: 'bar',
                        data: {
                            labels: catData.map(d => d.name),
                            datasets: [{ 
                                label: 'Stock Units', 
                                data: catData.map(d => parseInt(d.value) || 0), 
                                backgroundColor: catData.map(d => colors[d.name] || '#4F7942'), 
                                borderRadius: 4 
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            plugins: { legend: { display: false } },
                            scales: { 
                                x: { beginAtZero: true, grid: { display: false } },
                                y: { grid: { display: false } }
                            }
                        }
                    });
                } else if (ctx2) {
                    chartInstances.category = new Chart(ctx2, {
                        type: 'bar',
                        data: {
                            labels: ['No Data'],
                            datasets: [{ label: 'Stock Units', data: [0], backgroundColor: '#E5E7EB' }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            plugins: { legend: { display: false }, tooltip: { enabled: false } },
                            scales: { 
                                x: { beginAtZero: true, max: 1, grid: { display: false } },
                                y: { grid: { display: false } }
                            }
                        },
                        plugins: [{
                            id: 'noDataText',
                            afterDraw: function(chart) {
                                const ctx = chart.ctx;
                                chart.width = chart.width || chart.canvas.width;
                                chart.height = chart.height || chart.canvas.height;
                                ctx.save();
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';
                                ctx.font = '14px Inter, sans-serif';
                                ctx.fillStyle = '#9CA3AF';
                                ctx.fillText('No category data available', chart.width/2, chart.height/2);
                                ctx.restore();
                            }
                        }]
                    });
                }
            } catch (chartError) {
                console.error('Chart error:', chartError);
            }
        }, 300);

        updateLowStockBadge(lowStockCount);
        
    } catch (error) {
        console.error('Dashboard error:', error);
        area.innerHTML = `
            <div style="background:#FEE2E2;color:#991B1B;border-radius:8px;padding:20px;text-align:center;">
                <h3>⚠️ Error Loading Dashboard</h3>
                <p>${error.message}</p>
                <button onclick="loadPage('dashboard')" style="margin-top:10px;padding:8px 16px;background:#4F7942;color:#fff;border:none;border-radius:8px;cursor:pointer;">
                    Retry
                </button>
                <div style="margin-top:10px;font-size:12px;color:#666;">
                    Check that your database has data and api.php is working.
                    <br>Open browser console (F12) for more details.
                </div>
            </div>
        `;
    }
}

// ========== INVENTORY ==========
let inventoryState = { search: '', filter: 'All', modal: null, form: {}, adjModal: null, adjQty: '', adjType: 'IN' };

async function loadInventory(area) {
    products = await fetchAPI('api.php?action=products');
    const cats = ['All', ...new Set(products.map(p => p.category))];
    const filtered = products.filter(p => 
        (inventoryState.filter === 'All' || p.category === inventoryState.filter) &&
        (p.name.toLowerCase().includes(inventoryState.search.toLowerCase()) || 
         (p.origin || '').toLowerCase().includes(inventoryState.search.toLowerCase()))
    );

    area.innerHTML = `
        <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
            <input id="invSearch" placeholder="Search fruits..." style="flex:1;min-width:200px;padding:9px 14px;border-radius:8px;border:1.5px solid #E0DDD8;font-size:14px;outline:none;" value="${inventoryState.search}">
            <select id="invFilter" style="padding:9px 12px;border-radius:8px;border:1.5px solid #E0DDD8;font-size:14px;background:#fff;cursor:pointer;">
                ${cats.map(c => `<option value="${c}" ${c === inventoryState.filter ? 'selected' : ''}>${c}</option>`).join('')}
            </select>
            <button onclick="openAddProduct()" class="btn btn-primary">+ Add Fruit</button>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th></th><th>Product</th><th>Category</th><th>Origin</th><th>Stock</th><th>Price</th><th>Status</th><th>Actions</th>
                </tr></thead>
                <tbody>
                    ${filtered.map(p => `
                        <tr>
                            <td style="font-size:22px;">${p.image || '🍎'}</td>
                            <td><div class="fw-600">${p.name}</div><div style="font-size:11px;color:#aaa;">${p.season || ''}</div></td>
                            <td><span class="badge-status ${p.category === 'Exotic' ? 'badge-purple' : 'badge-green'}">${p.category}</span></td>
                            <td class="text-muted">${p.origin || '-'}</td>
                            <td class="fw-700 ${p.stock <= p.min_stock ? 'text-danger' : 'text-success'}">${p.stock} ${p.unit}s</td>
                            <td>₱${p.price}</td>
                            <td><span class="badge-status ${p.stock === 0 ? 'badge-red' : p.stock <= p.min_stock ? 'badge-yellow' : 'badge-green'}">${p.stock === 0 ? 'Out' : p.stock <= p.min_stock ? 'Low' : 'Good'}</span></td>
                            <td>
                                <div class="btn-group">
                                    <button onclick="openAdjustStock(${p.id})" class="btn btn-outline btn-sm" style="color:#4F7942;font-weight:600;">Adjust</button>
                                    <button onclick="openEditProduct(${p.id})" class="btn btn-outline btn-sm">Edit</button>
                                    ${currentUser.role === 'Admin' ? `<button onclick="deleteProduct(${p.id})" class="btn btn-danger btn-sm">Del</button>` : ''}
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <div id="invModals"></div>
    `;

    document.getElementById('invSearch').addEventListener('input', e => {
        inventoryState.search = e.target.value;
        loadInventory(area);
    });
    document.getElementById('invFilter').addEventListener('change', e => {
        inventoryState.filter = e.target.value;
        loadInventory(area);
    });

    updateLowStockBadge();
}

function openAddProduct() {
    inventoryState.modal = 'add';
    inventoryState.form = { name: '', category: 'Mango', origin: '', unit: 'kg', price: '', cost: '', stock: '', min_stock: '', description: '', season: '', image: '🍎' };
    renderInventoryModal();
}

function openEditProduct(id) {
    const p = products.find(x => x.id === id);
    if (!p) return;
    inventoryState.modal = 'edit';
    inventoryState.form = { ...p };
    renderInventoryModal();
}

function openAdjustStock(id) {
    const p = products.find(x => x.id === id);
    if (!p) return;
    inventoryState.adjModal = p;
    inventoryState.adjQty = '';
    inventoryState.adjType = 'IN';
    renderAdjustModal();
}

async function saveProduct() {
    const f = inventoryState.form;
    if (!f.name || !f.price || !f.stock) return;
    const data = { ...f, price: parseFloat(f.price), cost: parseFloat(f.cost) || 0, stock: parseInt(f.stock), min_stock: parseInt(f.min_stock) || 0 };
    
    if (inventoryState.modal === 'add') {
        await fetchAPI('api.php?action=products', { method: 'POST', body: JSON.stringify(data) });
    } else {
        await fetchAPI('api.php?action=products', { method: 'PUT', body: JSON.stringify(data) });
    }
    inventoryState.modal = null;
    loadInventory(document.getElementById('content-area'));
}

async function deleteProduct(id) {
    if (!confirm('Delete this product?')) return;
    await fetchAPI(`api.php?action=products&id=${id}`, { method: 'DELETE' });
    loadInventory(document.getElementById('content-area'));
}

async function confirmAdjustStock() {
    const qty = parseInt(inventoryState.adjQty);
    if (!qty || qty <= 0) return;
    const p = inventoryState.adjModal;
    const data = {
        product_id: p.id,
        type: inventoryState.adjType,
        qty: qty,
        date: new Date().toISOString().slice(0,10),
        by_user: currentUser.name,
        note: 'Manual adjustment'
    };
    await fetchAPI('api.php?action=transactions', { method: 'POST', body: JSON.stringify(data) });
    inventoryState.adjModal = null;
    loadInventory(document.getElementById('content-area'));
}

function renderInventoryModal() {
    const f = inventoryState.form;
    const area = document.getElementById('invModals');
    area.innerHTML = `
        <div class="modal-overlay">
            <div class="modal">
                <h3>${inventoryState.modal === 'add' ? 'Add New Fruit' : 'Edit Fruit'}</h3>
                <div class="form-grid">
                    <div class="full"><label>Name</label><input id="invF_name" value="${f.name || ''}"></div>
                    <div class="full"><label>Origin</label><input id="invF_origin" value="${f.origin || ''}"></div>
                    <div class="full"><label>Description</label><input id="invF_description" value="${f.description || ''}"></div>
                    <div class="full"><label>Season (e.g. Mar–Jun)</label><input id="invF_season" value="${f.season || ''}"></div>
                    <div><label>Category</label>
                        <select id="invF_category">
                            ${['Mango','Banana','Pineapple','Exotic','Papaya','Citrus'].map(c => 
                                `<option value="${c}" ${c === f.category ? 'selected' : ''}>${c}</option>`).join('')}
                        </select>
                    </div>
                    <div><label>Unit</label>
                        <select id="invF_unit">
                            ${['kg','pc','bunch','box','crate'].map(u => 
                                `<option value="${u}" ${u === f.unit ? 'selected' : ''}>${u}</option>`).join('')}
                        </select>
                    </div>
                    <div><label>Price (₱)</label><input type="number" id="invF_price" value="${f.price || ''}"></div>
                    <div><label>Cost (₱)</label><input type="number" id="invF_cost" value="${f.cost || ''}"></div>
                    <div><label>Stock</label><input type="number" id="invF_stock" value="${f.stock || ''}"></div>
                    <div><label>Min Stock</label><input type="number" id="invF_minStock" value="${f.min_stock || ''}"></div>
                    <div class="full"><label>Emoji Icon</label><input id="invF_image" value="${f.image || '🍎'}"></div>
                </div>
                <div class="modal-actions">
                    <button onclick="inventoryState.modal=null;loadInventory(document.getElementById('content-area'));" class="btn btn-outline">Cancel</button>
                    <button onclick="saveProduct()" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    `;
    const fields = ['name','origin','description','season','category','unit','price','cost','stock','minStock','image'];
    fields.forEach(key => {
        const el = document.getElementById(`invF_${key}`);
        if (el) el.addEventListener('input', e => {
            const k = key === 'minStock' ? 'min_stock' : key;
            inventoryState.form[k] = e.target.value;
        });
    });
}

function renderAdjustModal() {
    const p = inventoryState.adjModal;
    const area = document.getElementById('invModals');
    area.innerHTML = `
        <div class="modal-overlay">
            <div class="modal" style="width:360px;">
                <h3>Adjust Stock</h3>
                <p style="color:#888;font-size:13px;margin:0 0 20px;">${p.image || '🍎'} ${p.name} — Current: <strong>${p.stock} ${p.unit}s</strong></p>
                <div style="display:flex;gap:8px;margin-bottom:14px;">
                    ${['IN','OUT'].map(t => `
                        <button onclick="inventoryState.adjType='${t}';renderAdjustModal();" 
                            style="flex:1;padding:9px;border-radius:8px;border:2px solid ${inventoryState.adjType === t ? '#4F7942' : '#E0DDD8'};
                            background:${inventoryState.adjType === t ? '#F0F7EE' : '#fff'};color:${inventoryState.adjType === t ? '#4F7942' : '#666'};
                            font-weight:700;cursor:pointer;font-size:14px;">
                            ${t === 'IN' ? '➕ Stock In' : '➖ Stock Out'}
                        </button>
                    `).join('')}
                </div>
                <input id="adjQtyInput" type="number" placeholder="Quantity" value="${inventoryState.adjQty}" 
                    style="width:100%;padding:10px 12px;border-radius:8px;border:1.5px solid #E0DDD8;font-size:15px;box-sizing:border-box;margin-bottom:16px;">
                <div class="modal-actions">
                    <button onclick="inventoryState.adjModal=null;loadInventory(document.getElementById('content-area'));" class="btn btn-outline">Cancel</button>
                    <button onclick="confirmAdjustStock()" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    `;
    document.getElementById('adjQtyInput').addEventListener('input', e => {
        inventoryState.adjQty = e.target.value;
    });
}

// ========== TRANSACTIONS ==========
async function loadTransactions(area) {
    const data = await fetchAPI('api.php?action=transactions');
    area.innerHTML = `
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>Date</th><th>Product</th><th>Type</th><th>Quantity</th><th>Handled By</th><th>Note</th>
                </tr></thead>
                <tbody>
                    ${data.map(t => `
                        <tr>
                            <td class="text-muted">${t.date}</td>
                            <td class="fw-600">${t.product_name || 'Unknown'}</td>
                            <td><span class="badge-status ${t.type === 'IN' ? 'badge-green' : 'badge-red'}">${t.type === 'IN' ? 'Stock In' : 'Stock Out'}</span></td>
                            <td class="fw-700 ${t.type === 'IN' ? 'text-success' : 'text-danger'}">${t.type === 'IN' ? '+' : '-'}${t.qty} ${t.unit || ''}s</td>
                            <td class="text-muted">${t.by_user}</td>
                            <td class="text-muted">${t.note || ''}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

// ========== REPORTS ==========
async function loadReports(area) {
    const data = await fetchAPI('api.php?action=reports');
    const margin = data.margin || 0;
    area.innerHTML = `
        <div style="display:flex;gap:12px;justify-content:flex-end;margin-bottom:20px;">
            <button onclick="exportCSV()" class="btn btn-outline" style="border-color:#4F7942;color:#4F7942;">Export CSV</button>
            <button onclick="exportJSON()" class="btn btn-primary">Export JSON</button>
        </div>
        <div class="stats" style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:22px;">
            <div class="stat-card"><div class="icon">📈</div>
                <div class="label">Inventory Value</div>
                <div class="value">₱${(data.totalValue || 0).toLocaleString()}</div>
                <div class="sub">At selling price</div>
            </div>
            <div class="stat-card"><div class="icon">💸</div>
                <div class="label">Total Cost</div>
                <div class="value">₱${(data.totalCost || 0).toLocaleString()}</div>
                <div class="sub">At purchase cost</div>
            </div>
            <div class="stat-card"><div class="icon">💹</div>
                <div class="label">Gross Margin</div>
                <div class="value">₱${(data.profit || 0).toLocaleString()}</div>
                <div class="sub">${margin.toFixed(1)}% margin</div>
            </div>
            <div class="stat-card"><div class="icon">📋</div>
                <div class="label">Transactions</div>
                <div class="value">${data.transactions || 0}</div>
                <div class="sub">Total movements</div>
            </div>
        </div>
        <div class="grid-2">
            <div class="card"><div class="card-title mb-18">Revenue by Month</div>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            <div class="card"><div class="card-title mb-18">Stock Distribution by Category</div>
                <div class="chart-container">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>
        <div class="card" style="margin-top:20px;">
            <div class="card-title">Full Product Report</div>
            <div class="table-wrap">
                <table>
                    <thead><tr>
                        <th>Product</th><th>Category</th><th>Origin</th><th>Unit</th><th>Price</th><th>Cost</th><th>Stock</th><th>Value</th><th>Status</th>
                    </tr></thead>
                    <tbody>
                        ${(data.products || []).map(p => `
                            <tr>
                                <td class="fw-600">${p.image || '🍎'} ${p.name}</td>
                                <td class="text-muted">${p.category}</td>
                                <td class="text-muted">${p.origin || '-'}</td>
                                <td class="text-muted">${p.unit}</td>
                                <td>₱${p.price}</td>
                                <td class="text-muted">₱${p.cost}</td>
                                <td class="fw-700 ${p.stock <= p.min_stock ? 'text-danger' : 'text-success'}">${p.stock}</td>
                                <td class="fw-600">₱${(p.stock * p.price).toLocaleString()}</td>
                                <td><span class="badge-status ${p.stock === 0 ? 'badge-red' : p.stock <= p.min_stock ? 'badge-yellow' : 'badge-green'}">${p.stock === 0 ? 'Out' : p.stock <= p.min_stock ? 'Low' : 'Good'}</span></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;

    setTimeout(() => {
        const monthly = data.monthlySales || [];
        const ctx1 = document.getElementById('revenueChart')?.getContext('2d');
        if (ctx1) {
            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: monthly.map(d => d.month),
                    datasets: [
                        { label: 'Revenue', data: monthly.map(d => d.sales), borderColor: '#4F7942', backgroundColor: 'transparent', tension: 0.3, pointRadius: 4 },
                        { label: 'Expenses', data: monthly.map(d => d.expenses), borderColor: '#A8D5A2', backgroundColor: 'transparent', tension: 0.3, pointRadius: 3 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
                    scales: { y: { beginAtZero: true, ticks: { callback: v => '₱' + (v/1000).toFixed(0) + 'k' } } }
                }
            });
        }

        const catData = data.categoryData || [];
        const colors = { Mango:'#F59E0B', Banana:'#EAB308', Pineapple:'#84CC16', Exotic:'#8B5CF6', Papaya:'#F97316', Citrus:'#22C55E' };
        const ctx2 = document.getElementById('pieChart')?.getContext('2d');
        if (ctx2 && catData.length) {
            new Chart(ctx2, {
                type: 'pie',
                data: {
                    labels: catData.map(d => d.name),
                    datasets: [{ data: catData.map(d => d.value), 
                        backgroundColor: catData.map(d => colors[d.name] || '#4F7942') }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                    }
                }
            });
        }
    }, 100);
}

// ========== USERS ==========
let userState = { modal: false, form: { name: '', email: '', password: '', role: 'Staff' } };

async function loadUsers(area) {
    if (currentUser.role !== 'Admin') {
        area.innerHTML = '<div style="background:#FEE2E2;color:#991B1B;border-radius:8px;padding:12px 16px;">You don\'t have permission to view this page.</div>';
        return;
    }
    users = await fetchAPI('api.php?action=users');
    area.innerHTML = `
        <div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
            <button onclick="openAddUser()" class="btn btn-primary">+ Add User</button>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>User</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
                <tbody>
                    ${users.map(u => `
                        <tr>
                            <td><div class="flex items-center gap-8"><div class="avatar" style="width:32px;height:32px;font-size:11px;">${u.avatar}</div><span class="fw-600">${u.name}</span></div></td>
                            <td class="text-muted">${u.email}</td>
                            <td><span class="badge-status ${u.role === 'Admin' ? 'badge-blue' : 'badge-green'}">${u.role}</span></td>
                            <td class="text-muted">${u.joined}</td>
                            <td>
                                ${u.id !== currentUser.id ? 
                                    `<button onclick="deleteUser(${u.id})" class="btn btn-danger btn-sm">Remove</button>` : 
                                    '<span class="text-muted" style="font-size:12px;">You</span>'}
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <div id="userModals"></div>
    `;
}

function openAddUser() {
    userState.modal = true;
    userState.form = { name: '', email: '', password: '', role: 'Staff' };
    renderUserModal();
}

async function addUser() {
    const f = userState.form;
    if (!f.name || !f.email || !f.password) return;
    await fetchAPI('api.php?action=users', { method: 'POST', body: JSON.stringify(f) });
    userState.modal = false;
    loadUsers(document.getElementById('content-area'));
}

async function deleteUser(id) {
    if (!confirm('Remove this user?')) return;
    await fetchAPI(`api.php?action=users&id=${id}`, { method: 'DELETE' });
    loadUsers(document.getElementById('content-area'));
}

function renderUserModal() {
    const f = userState.form;
    const area = document.getElementById('userModals');
    area.innerHTML = `
        <div class="modal-overlay">
            <div class="modal" style="width:380px;">
                <h3>Add New User</h3>
                <div style="margin-bottom:14px;"><label>Full Name</label><input id="userF_name" value="${f.name}"></div>
                <div style="margin-bottom:14px;"><label>Email</label><input id="userF_email" value="${f.email}"></div>
                <div style="margin-bottom:14px;"><label>Password</label><input type="password" id="userF_password" value="${f.password}"></div>
                <div style="margin-bottom:20px;"><label>Role</label>
                    <select id="userF_role">
                        <option value="Admin" ${f.role === 'Admin' ? 'selected' : ''}>Admin</option>
                        <option value="Staff" ${f.role === 'Staff' ? 'selected' : ''}>Staff</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button onclick="userState.modal=false;loadUsers(document.getElementById('content-area'));" class="btn btn-outline">Cancel</button>
                    <button onclick="addUser()" class="btn btn-primary">Add User</button>
                </div>
            </div>
        </div>
    `;
    ['name','email','password','role'].forEach(key => {
        const el = document.getElementById(`userF_${key}`);
        if (el) el.addEventListener('input', e => { userState.form[key] = e.target.value; });
    });
}

// ========== PROFILE ==========
let profileState = { edit: false, form: {}, pwForm: { current: '', next: '', confirm: '', error: '', success: '' } };

async function loadProfile(area) {
    const u = currentUser;
    profileState.form = { name: u.name, email: u.email };
    area.innerHTML = `
        <div style="max-width:560px;">
            <div style="background:#fff;border-radius:12px;padding:28px;margin-bottom:20px;">
                <div class="flex items-center" style="gap:18px;margin-bottom:24px;">
                    <div class="avatar" style="width:64px;height:64px;font-size:22px;">${u.avatar}</div>
                    <div>
                        <h2 style="margin:0;font-size:20px;font-weight:800;color:#222;">${u.name}</h2>
                        <p style="margin:4px 0 0;color:#888;font-size:14px;">${u.role} · Joined ${u.joined}</p>
                    </div>
                    <button onclick="profileState.edit=!profileState.edit;loadProfile(document.getElementById('content-area'));" 
                        style="margin-left:auto;padding:8px 16px;border-radius:8px;border:1.5px solid #E0DDD8;background:#fff;font-size:13px;font-weight:600;cursor:pointer;">
                        ${profileState.edit ? 'Cancel' : 'Edit Profile'}
                    </button>
                </div>
                ${profileState.edit ? `
                    <div style="margin-bottom:14px;">
                        <label style="font-size:12px;font-weight:600;color:#666;display:block;margin-bottom:4px;">Full Name</label>
                        <input id="profName" value="${profileState.form.name}" 
                            style="width:100%;padding:9px 11px;border-radius:7px;border:1.5px solid #E0DDD8;font-size:14px;box-sizing:border-box;">
                    </div>
                    <div style="margin-bottom:14px;">
                        <label style="font-size:12px;font-weight:600;color:#666;display:block;margin-bottom:4px;">Email</label>
                        <input id="profEmail" value="${profileState.form.email}" 
                            style="width:100%;padding:9px 11px;border-radius:7px;border:1.5px solid #E0DDD8;font-size:14px;box-sizing:border-box;">
                    </div>
                    <div style="display:flex;gap:10px;margin-top:16px;">
                        <button onclick="profileState.edit=false;loadProfile(document.getElementById('content-area'));" 
                            class="btn btn-outline">Cancel</button>
                        <button onclick="saveProfile()" class="btn btn-primary">Save Changes</button>
                    </div>
                ` : `
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        ${[['Name', u.name],['Email', u.email],['Role', u.role],['Member Since', u.joined]].map(([k,v]) => `
                            <div style="background:#F9F8F5;border-radius:8px;padding:12px 14px;">
                                <div style="font-size:11px;color:#aaa;font-weight:600;margin-bottom:4px;">${k}</div>
                                <div style="font-size:14px;font-weight:600;color:#222;">${v}</div>
                            </div>
                        `).join('')}
                    </div>
                `}
            </div>
            <div style="background:#fff;border-radius:12px;padding:28px;">
                <h3 style="margin:0 0 18px;font-size:15px;font-weight:700;">Change Password</h3>
                ${profileState.pwForm.error ? `<div style="background:#FEE2E2;color:#991B1B;border-radius:8px;padding:9px 13px;font-size:13px;margin-bottom:14px;">${profileState.pwForm.error}</div>` : ''}
                ${profileState.pwForm.success ? `<div style="background:#DCFCE7;color:#166534;border-radius:8px;padding:9px 13px;font-size:13px;margin-bottom:14px;">${profileState.pwForm.success}</div>` : ''}
                <div style="margin-bottom:14px;"><label>Current Password</label><input type="password" id="pwCurrent" style="width:100%;padding:9px 11px;border-radius:7px;border:1.5px solid #E0DDD8;font-size:14px;box-sizing:border-box;"></div>
                <div style="margin-bottom:14px;"><label>New Password</label><input type="password" id="pwNext" style="width:100%;padding:9px 11px;border-radius:7px;border:1.5px solid #E0DDD8;font-size:14px;box-sizing:border-box;"></div>
                <div style="margin-bottom:14px;"><label>Confirm New Password</label><input type="password" id="pwConfirm" style="width:100%;padding:9px 11px;border-radius:7px;border:1.5px solid #E0DDD8;font-size:14px;box-sizing:border-box;"></div>
                <button onclick="changePassword()" class="btn btn-primary">Update Password</button>
            </div>
        </div>
    `;
    
    if (profileState.edit) {
        document.getElementById('profName').addEventListener('input', e => { 
            profileState.form.name = e.target.value; 
        });
        document.getElementById('profEmail').addEventListener('input', e => { 
            profileState.form.email = e.target.value; 
        });
    }
    
    document.getElementById('pwCurrent').addEventListener('input', e => { 
        profileState.pwForm.current = e.target.value; 
    });
    document.getElementById('pwNext').addEventListener('input', e => { 
        profileState.pwForm.next = e.target.value; 
    });
    document.getElementById('pwConfirm').addEventListener('input', e => { 
        profileState.pwForm.confirm = e.target.value; 
    });
}

async function saveProfile() {
    try {
        const data = { 
            id: currentUser.id, 
            name: profileState.form.name, 
            email: profileState.form.email 
        };
        
        console.log('Saving profile:', data);
        
        const response = await fetch('api.php?action=users', { 
            method: 'PUT', 
            body: JSON.stringify(data),
            headers: { 'Content-Type': 'application/json' }
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to update profile');
        }
        
        const result = await response.json();
        console.log('Profile update result:', result);
        
        // Update the current user object with new data
        currentUser.name = profileState.form.name;
        currentUser.email = profileState.form.email;
        
        // Regenerate avatar from new name
        const nameParts = profileState.form.name.split(' ');
        let initials = '';
        if (nameParts.length >= 2) {
            initials = (nameParts[0][0] + nameParts[nameParts.length - 1][0]).toUpperCase();
        } else {
            initials = nameParts[0].substring(0, 2).toUpperCase();
        }
        currentUser.avatar = initials;
        
        // Reload to update session and UI
        window.location.reload();
    } catch (error) {
        console.error('Error saving profile:', error);
        alert('Error saving profile: ' + error.message);
    }
}

async function changePassword() {
    const pw = profileState.pwForm;
    if (pw.next.length < 6) {
        pw.error = 'New password must be at least 6 characters.';
        pw.success = '';
        loadProfile(document.getElementById('content-area'));
        return;
    }
    if (pw.next !== pw.confirm) {
        pw.error = 'Passwords do not match.';
        pw.success = '';
        loadProfile(document.getElementById('content-area'));
        return;
    }
    try {
        const data = { id: currentUser.id, password: pw.next };
        await fetchAPI('api.php?action=users', { method: 'PUT', body: JSON.stringify(data) });
        pw.error = '';
        pw.success = 'Password updated successfully!';
        pw.current = '';
        pw.next = '';
        pw.confirm = '';
        loadProfile(document.getElementById('content-area'));
    } catch(e) {
        pw.error = 'Failed to update password.';
        pw.success = '';
        loadProfile(document.getElementById('content-area'));
    }
}

// ========== EXPORTS ==========
function exportCSV() {
    window.location.href = 'export.php?format=csv';
}

function exportJSON() {
    window.location.href = 'export.php?format=json';
}

// ========== HELPERS ==========
function updateLowStockBadge(count) {
    const badge = document.getElementById('lowStockBadge');
    if (!badge) return;
    
    if (count !== undefined && count > 0) {
        badge.textContent = `⚠ ${count} Low Stock`;
        badge.style.background = '#FEE2E2';
        badge.style.color = '#991B1B';
    } else if (count !== undefined && count === 0) {
        badge.textContent = '✓ All stocked';
        badge.style.background = '#DCFCE7';
        badge.style.color = '#166534';
    } else {
        badge.textContent = '⚠ Loading...';
        badge.style.background = '#FEF3C7';
        badge.style.color = '#92400E';
    }
}

// ========== INIT ==========
async function initLowStock() {
    try {
        const data = await fetchAPI('api.php?action=dashboard');
        updateLowStockBadge(data.lowStockCount || 0);
    } catch(e) {
        console.error('Failed to load low stock count:', e);
        const badge = document.getElementById('lowStockBadge');
        if (badge) {
            badge.textContent = '⚠ Error';
            badge.style.background = '#FEE2E2';
            badge.style.color = '#991B1B';
        }
    }
}
initLowStock();

loadPage(currentPage);

document.querySelectorAll('.nav-item').forEach(el => {
    el.addEventListener('click', function(e) {
        const url = new URL(this.href);
        const page = url.searchParams.get('page');
        if (page) {
            e.preventDefault();
            currentPage = page;
            loadPage(page);
            document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
            this.classList.add('active');
            const titles = { dashboard:'Dashboard', inventory:'Inventory', transactions:'Transactions', 
                           reports:'Reports', users:'User Management', profile:'Profile' };
            document.querySelector('.page-title').textContent = titles[page] || 'Dashboard';
            history.pushState({ page }, '', `?page=${page}`);
        }
    });
});

window.addEventListener('popstate', (e) => {
    const params = new URLSearchParams(window.location.search);
    const page = params.get('page') || 'dashboard';
    currentPage = page;
    loadPage(page);
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    document.querySelector(`.nav-item[href*="page=${page}"]`)?.classList.add('active');
});
</script>
</body>
</html>