<?php
session_start();
require_once 'api/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'user';
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Purchase Order Registry | Nexus Procurement</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "tertiary-fixed-dim": "#4edea3",
                        "secondary-container": "#2170e4",
                        "on-tertiary-fixed": "#002113",
                        "error": "#ba1a1a",
                        "outline-variant": "#c5c6cd",
                        "on-primary-fixed-variant": "#3c475a",
                        "inverse-on-surface": "#f3f0f2",
                        "surface": "#fbf8fa",
                        "on-primary": "#ffffff",
                        "tertiary": "#00190e",
                        "tertiary-container": "#00301e",
                        "on-tertiary": "#ffffff",
                        "surface-container-highest": "#e4e2e3",
                        "outline": "#75777d",
                        "on-secondary-container": "#fefcff",
                        "on-secondary": "#ffffff",
                        "background": "#fbf8fa",
                        "on-primary-container": "#8590a6",
                        "inverse-surface": "#303032",
                        "surface-container-lowest": "#ffffff",
                        "on-error": "#ffffff",
                        "surface-container-low": "#f5f3f4",
                        "inverse-primary": "#bcc7de",
                        "primary-fixed-dim": "#bcc7de",
                        "surface-container": "#f0edef",
                        "primary-container": "#1e293b",
                        "on-primary-fixed": "#111c2d",
                        "secondary-fixed-dim": "#adc6ff",
                        "on-tertiary-fixed-variant": "#005236",
                        "on-secondary-fixed": "#001a42",
                        "secondary-fixed": "#d8e2ff",
                        "on-secondary-fixed-variant": "#004395",
                        "on-tertiary-container": "#00a472",
                        "surface-dim": "#dcd9db",
                        "surface-bright": "#fbf8fa",
                        "on-background": "#1b1b1d",
                        "on-surface": "#1b1b1d",
                        "on-error-container": "#93000a",
                        "surface-variant": "#e4e2e3",
                        "surface-container-high": "#eae7e9",
                        "secondary": "#0058be",
                        "primary-fixed": "#d8e3fb",
                        "primary": "#091426",
                        "tertiary-fixed": "#6ffbbe",
                        "error-container": "#ffdad6",
                        "surface-tint": "#545f73",
                        "on-surface-variant": "#45474c"
                    },
                    borderRadius: {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    spacing: {
                        "md": "16px",
                        "lg": "24px",
                        "unit": "4px",
                        "sidebar-width": "260px",
                        "xs": "4px",
                        "xl": "32px",
                        "sm": "8px",
                        "gutter": "16px",
                        "container-margin": "24px"
                    },
                    fontFamily: {
                        "headline-md": ["Inter"],
                        "headline-sm": ["Inter"],
                        "data-tabular": ["Inter"],
                        "body-sm": ["Inter"],
                        "label-md": ["Inter"],
                        "body-lg": ["Inter"],
                        "label-sm": ["Inter"],
                        "display-lg": ["Inter"],
                        "body-md": ["Inter"]
                    },
                    fontSize: {
                        "headline-md": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                        "headline-sm": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
                        "data-tabular": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                        "body-sm": ["12px", {"lineHeight": "16px", "fontWeight": "400"}],
                        "label-md": ["14px", {"lineHeight": "20px", "fontWeight": "600"}],
                        "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "label-sm": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}],
                        "display-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}]
                    }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }
        .table-row-hover:hover { background-color: rgba(9, 20, 38, 0.02); }
    </style>
</head>
<body class="font-body-md text-on-background antialiased">
    <!-- SideNavBar -->
    <aside class="fixed h-screen w-sidebar-width left-0 top-0 bg-primary flex flex-col z-50">
        <div class="p-lg flex items-center gap-sm">
            <div class="w-8 h-8 bg-on-primary rounded flex items-center justify-center">
                <span class="material-symbols-outlined text-primary" data-icon="database">database</span>
            </div>
            <div>
                <h1 class="text-headline-sm font-headline-sm text-on-primary leading-tight">ProcureCorp</h1>
                <p class="text-label-sm text-on-primary-container opacity-80 uppercase tracking-widest">Enterprise Suite</p>
            </div>
        </div>
        <nav class="mt-md flex-1 overflow-y-auto custom-scrollbar px-sm">
            <div class="space-y-1">
                <a class="flex items-center gap-md px-md py-3 font-label-md text-label-md text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors duration-200 rounded" href="dashboard.php">
                    <span class="material-symbols-outlined" data-icon="dashboard">dashboard</span>
                    <span class="">Dashboard</span>
                </a>
                <a class="flex items-center gap-md px-md py-3 font-label-md text-label-md text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors duration-200 rounded" href="vendors.php">
                    <span class="material-symbols-outlined" data-icon="database">database</span>
                    <span class="">Master Data</span>
                </a>
                <a class="flex items-center gap-md px-md py-3 font-label-md text-label-md bg-secondary-container text-on-secondary-container border-l-4 border-secondary-fixed transition-transform active:scale-[0.98] rounded-r" href="po_list.php">
                    <span class="material-symbols-outlined" data-icon="shopping_cart">shopping_cart</span>
                    <span class="">Purchase Orders</span>
                </a>
                <a class="flex items-center gap-md px-md py-3 font-label-md text-label-md text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors duration-200 rounded" href="reports.php">
                    <span class="material-symbols-outlined" data-icon="analytics">analytics</span>
                    <span class="">Reports</span>
                </a>
                <a class="flex items-center gap-md px-md py-3 font-label-md text-label-md text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors duration-200 rounded" href="settings.php">
                    <span class="material-symbols-outlined" data-icon="settings">settings</span>
                    <span class="">Settings</span>
                </a>
            </div>
        </nav>
        <div class="p-lg border-t border-on-primary-container/10">
            <div class="flex items-center gap-sm">
                <div class="w-10 h-10 shrink-0 rounded-full border border-on-primary-container/20 bg-secondary-container text-on-secondary-container flex items-center justify-center font-label-md font-bold" title="<?php echo htmlspecialchars($userName); ?>"><?php echo htmlspecialchars(userInitials($userName)); ?></div>
                <div class="overflow-hidden">
                    <p class="text-label-md text-on-primary truncate"><?php echo htmlspecialchars($userName); ?></p>
                    <p class="text-label-sm text-on-primary-container truncate"><?php echo htmlspecialchars($userRole); ?></p>
                </div>
            </div>
            <a href="api/auth.php?action=logout" class="mt-2 flex items-center gap-sm text-on-primary-container hover:text-error text-body-sm transition-colors">
                <span class="material-symbols-outlined text-[16px]">logout</span>
                Logout
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="ml-sidebar-width min-h-screen flex flex-col">
        <!-- TopNavBar -->
        <header class="sticky top-0 z-40 bg-surface flex justify-between items-center h-16 px-container-margin border-b border-outline-variant transition-all duration-150">
            <div class="flex items-center gap-md flex-1">
                <div class="relative w-full max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" data-icon="search">search</span>
                    <input class="w-full bg-surface-container-low border-outline-variant rounded-full pl-10 pr-md py-2 text-body-md focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition-all" placeholder="Search PO Number, Vendor..." type="text" id="searchInput"/>
                </div>
            </div>
            <div class="flex items-center gap-lg">
                <div class="flex items-center gap-md text-on-surface-variant">
                    <button class="hover:text-secondary transition-all"><span class="material-symbols-outlined" data-icon="notifications">notifications</span></button>
                    <button class="hover:text-secondary transition-all"><span class="material-symbols-outlined" data-icon="help">help</span></button>
                </div>
                <div class="h-8 w-[1px] bg-outline-variant"></div>
                <h2 class="font-headline-sm text-headline-sm text-primary">Purchase Orders</h2>
            </div>
        </header>

        <!-- Content Canvas -->
        <div class="p-lg space-y-lg flex-1">
            <!-- Hero / Header Section -->
            <div class="flex justify-between items-end">
                <div>
                    <nav class="flex items-center gap-xs text-label-sm text-on-surface-variant mb-xs">
                        <span class="">Procurement</span>
                        <span class="material-symbols-outlined text-[12px]" data-icon="chevron_right">chevron_right</span>
                        <span class="text-secondary font-bold">PO Registry</span>
                    </nav>
                    <h1 class="font-display-lg text-display-lg text-primary tracking-tight">Purchase Order Registry</h1>
                    <p class="text-body-md text-on-surface-variant max-w-2xl mt-xs">Manage and track all organizational procurement requests from drafting through to invoicing.</p>
                </div>
                <a href="po_create.php" class="bg-secondary text-on-secondary px-lg py-md rounded-lg font-label-md flex items-center gap-sm hover:opacity-90 transition-opacity shadow-sm active:scale-[0.98]">
                    <span class="material-symbols-outlined" data-icon="add">add</span>
                    Buat PO Baru
                </a>
            </div>

            <!-- Dashboard / Summary Stats (Bento Style) -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-md">
                <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm flex flex-col justify-between h-32 hover:border-secondary/30 transition-colors">
                    <div class="flex justify-between items-start">
                        <span class="text-label-sm uppercase text-on-surface-variant tracking-wider">Total Active PO</span>
                        <span class="material-symbols-outlined text-secondary" data-icon="receipt_long">receipt_long</span>
                    </div>
                    <div>
                        <p class="text-headline-md font-headline-md" id="totalActivePO">1,284</p>
                        <p class="text-label-sm text-tertiary-fixed-dim flex items-center gap-xs">
                            <span class="material-symbols-outlined text-[14px]" data-icon="trending_up">trending_up</span> 12% from last month
                        </p>
                    </div>
                </div>
                <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm flex flex-col justify-between h-32 hover:border-secondary/30 transition-colors">
                    <div class="flex justify-between items-start">
                        <span class="text-label-sm uppercase text-on-surface-variant tracking-wider">Pending Signed</span>
                        <span class="material-symbols-outlined text-error" data-icon="signature">signature</span>
                    </div>
                    <div>
                        <p class="text-headline-md font-headline-md" id="pendingSigned">42</p>
                        <p class="text-label-sm text-on-surface-variant italic">Awaiting approval</p>
                    </div>
                </div>
                <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm flex flex-col justify-between h-32 hover:border-secondary/30 transition-colors">
                    <div class="flex justify-between items-start">
                        <span class="text-label-sm uppercase text-on-surface-variant tracking-wider">Total Value</span>
                        <span class="material-symbols-outlined text-tertiary-fixed-dim" data-icon="payments">payments</span>
                    </div>
                    <div>
                        <p class="text-headline-md font-headline-md">Rp 2,4M</p>
                        <p class="text-label-sm text-on-surface-variant">FY 2024 Q3</p>
                    </div>
                </div>
                <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm flex flex-col justify-between h-32 hover:border-secondary/30 transition-colors">
                    <div class="flex justify-between items-start">
                        <span class="text-label-sm uppercase text-on-surface-variant tracking-wider">Vendors</span>
                        <span class="material-symbols-outlined text-secondary" data-icon="group">group</span>
                    </div>
                    <div>
                        <p class="text-headline-md font-headline-md">156</p>
                        <p class="text-label-sm text-on-surface-variant flex items-center gap-xs">Active partners</p>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="bg-white p-md rounded-xl border border-outline-variant shadow-sm flex flex-wrap items-center gap-md">
                <div class="flex-1 min-w-[200px] relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]" data-icon="filter_list">filter_list</span>
                    <input class="w-full border-none bg-surface-container-low rounded py-2 pl-10 focus:ring-0 text-body-md" placeholder="Quick filter by vendor or project..." type="text" id="filterInput"/>
                </div>
                <div class="flex items-center gap-sm">
                    <select class="border-outline-variant rounded bg-white text-label-md py-2 px-lg focus:ring-secondary/20 transition-all" id="statusFilter">
                        <option value="">Status: All</option>
                        <option value="Draft">Draft</option>
                        <option value="Printed">Printed</option>
                        <option value="Signed">Signed</option>
                        <option value="Invoiced">Invoiced</option>
                    </select>
                    <select class="border-outline-variant rounded bg-white text-label-md py-2 px-lg focus:ring-secondary/20 transition-all" id="dateFilter">
                        <option value="">Date: All</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
                <div class="h-6 w-[1px] bg-outline-variant mx-sm"></div>
                <button class="text-secondary font-label-md hover:underline flex items-center gap-xs" onclick="resetFilters()">
                    <span class="material-symbols-outlined text-[18px]" data-icon="refresh">refresh</span> Reset Filters
                </button>
            </div>

            <!-- Data Table -->
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low border-b border-outline-variant">
                                <th class="px-lg py-md text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold">PO Number</th>
                                <th class="px-lg py-md text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold">Date</th>
                                <th class="px-lg py-md text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold">Vendor</th>
                                <th class="px-lg py-md text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold">Project</th>
                                <th class="px-lg py-md text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold text-right">Total Amount</th>
                                <th class="px-lg py-md text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold text-center">Status</th>
                                <th class="px-lg py-md text-label-sm uppercase tracking-wider text-on-surface-variant font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant font-data-tabular" id="poTableBody">
                            <!-- Data will be loaded via JavaScript -->
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="px-lg py-md bg-surface-container-low border-t border-outline-variant flex items-center justify-between">
                    <p class="text-label-sm text-on-surface-variant" id="poCount">Loading...</p>
                    <div class="flex items-center gap-xs" id="pagination">
                        <!-- Pagination will be loaded via JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Contextual Insights Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-lg mt-xl">
                <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm flex items-center gap-lg">
                    <div class="relative w-24 h-24 flex-shrink-0">
                        <svg class="w-full h-full transform -rotate-90">
                            <circle class="text-surface-container-highest" cx="48" cy="48" fill="transparent" r="40" stroke="currentColor" stroke-width="8"></circle>
                            <circle class="text-secondary" cx="48" cy="48" fill="transparent" r="40" stroke="currentColor" stroke-dasharray="251.2" stroke-dashoffset="62.8" stroke-width="8"></circle>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center font-bold text-headline-sm">75%</div>
                    </div>
                    <div>
                        <h3 class="font-headline-sm text-headline-sm text-primary">Budget Utilization</h3>
                        <p class="text-body-md text-on-surface-variant">Infrastructure department has utilized 75% of its allocated quarterly budget. 3 high-value POs are pending approval.</p>
                        <button class="mt-md text-secondary font-label-md hover:underline flex items-center gap-xs">View Budget Report <span class="material-symbols-outlined text-[18px]" data-icon="arrow_forward">arrow_forward</span></button>
                    </div>
                </div>
                <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm relative overflow-hidden">
                    <div class="relative z-10">
                        <h3 class="font-headline-sm text-headline-sm text-primary mb-sm">Recent Activity</h3>
                        <ul class="space-y-md">
                            <li class="flex items-start gap-md">
                                <div class="w-8 h-8 rounded-full bg-secondary-fixed flex items-center justify-center flex-shrink-0 mt-1">
                                    <span class="material-symbols-outlined text-[18px] text-on-secondary-fixed-variant" data-icon="check_circle">check_circle</span>
                                </div>
                                <div>
                                    <p class="text-label-md text-on-surface">PO-2023-0891 was signed by CFO</p>
                                    <p class="text-label-sm text-on-surface-variant">2 hours ago</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-md">
                                <div class="w-8 h-8 rounded-full bg-primary-fixed flex items-center justify-center flex-shrink-0 mt-1">
                                    <span class="material-symbols-outlined text-[18px] text-on-primary-fixed-variant" data-icon="note_add">note_add</span>
                                </div>
                                <div>
                                    <p class="text-label-md text-on-surface">New Purchase Order draft PO-2023-0896</p>
                                    <p class="text-label-sm text-on-surface-variant">5 hours ago • By Admin</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="absolute top-0 right-0 p-lg opacity-[0.03]">
                        <span class="material-symbols-outlined text-[120px]" data-icon="history">history</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        let currentPage = 1;
        let searchTimeout;
        let currentFilters = {
            search: '',
            status: '',
            date: ''
        };

        document.addEventListener('DOMContentLoaded', function() {
            loadPOs();
        });

        document.getElementById('searchInput')?.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentPage = 1;
                currentFilters.search = e.target.value;
                loadPOs();
            }, 300);
        });

        document.getElementById('filterInput')?.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentPage = 1;
                currentFilters.search = e.target.value;
                loadPOs();
            }, 300);
        });

        document.getElementById('statusFilter')?.addEventListener('change', function(e) {
            currentPage = 1;
            currentFilters.status = e.target.value;
            loadPOs();
        });

        document.getElementById('dateFilter')?.addEventListener('change', function(e) {
            currentPage = 1;
            currentFilters.date = e.target.value;
            loadPOs();
        });

        async function loadPOs() {
            try {
                let url = 'api/po.php?page=' + currentPage + '&limit=10';
                
                if (currentFilters.search) {
                    url += '&search=' + encodeURIComponent(currentFilters.search);
                }
                if (currentFilters.status) {
                    url += '&status=' + encodeURIComponent(currentFilters.status);
                }

                const response = await fetch(url);
                const result = await response.json();

                if (result.success) {
                    renderPOs(result.data.pos);
                    renderPagination(result.data.pagination);
                    document.getElementById('poCount').textContent = 
                        'Showing 1 to ' + result.data.pos.length + ' of ' + result.data.pagination.total + ' results';
                    
                    // Update summary stats
                    document.getElementById('totalActivePO').textContent = result.data.pagination.total.toLocaleString();
                } else {
                    console.error('Failed to load POs:', result.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function renderPOs(pos) {
            const tbody = document.getElementById('poTableBody');
            if (!tbody) return;

            if (pos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="px-lg py-md text-center text-on-surface-variant">No data available</td></tr>';
                return;
            }

            const statusColors = {
                'Draft': 'bg-surface-container-highest text-on-surface-variant',
                'Printed': 'bg-primary-fixed text-on-primary-fixed-variant',
                'Signed': 'bg-secondary-fixed text-on-secondary-fixed-variant',
                'Invoiced': 'bg-tertiary-fixed text-on-tertiary-fixed-variant',
                'Completed': 'bg-on-tertiary-container/10 text-on-tertiary-container'
            };

            tbody.innerHTML = pos.map(po => `
                <tr class="table-row-hover transition-colors">
                    <td class="px-lg py-md font-bold text-secondary">${escapeHtml(po.po_number)}</td>
                    <td class="px-lg py-md text-on-surface-variant">${formatDate(po.created_at)}</td>
                    <td class="px-lg py-md font-semibold">${escapeHtml(po.vendor_name || 'N/A')}</td>
                    <td class="px-lg py-md">${escapeHtml(po.project_name || 'N/A')}</td>
                    <td class="px-lg py-md text-right font-bold">${formatCurrency(po.grand_total)}</td>
                    <td class="px-lg py-md text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${statusColors[po.status] || 'bg-gray-100 text-gray-800'}">${po.status}</span>
                    </td>
                    <td class="px-lg py-md text-right">
                        <div class="flex items-center justify-end gap-md">
                            <button class="text-on-surface-variant hover:text-secondary transition-colors" title="Edit" onclick="editPO('${po.id}')">
                                <span class="material-symbols-outlined" data-icon="edit">edit</span>
                            </button>
                            <button class="text-on-surface-variant hover:text-secondary transition-colors" title="Detail" onclick="viewPO('${po.id}')">
                                <span class="material-symbols-outlined" data-icon="visibility">visibility</span>
                            </button>
                            <button class="text-on-surface-variant hover:text-secondary transition-colors" title="Cetak" onclick="printPO('${po.id}')">
                                <span class="material-symbols-outlined" data-icon="print">print</span>
                            </button>
                            <button class="text-on-surface-variant hover:text-secondary transition-colors" title="Upload Attachment" onclick="uploadAttachment('${po.id}')">
                                <span class="material-symbols-outlined" data-icon="upload_file">upload_file</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function renderPagination(pagination) {
            const container = document.getElementById('pagination');
            if (!container) return;

            let html = `
                <button class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant bg-white text-on-surface-variant hover:bg-surface-container transition-colors ${pagination.page <= 1 ? 'opacity-50' : ''}" 
                    ${pagination.page <= 1 ? 'disabled' : ''} onclick="changePage(${pagination.page - 1})">
                    <span class="material-symbols-outlined text-[18px]" data-icon="chevron_left">chevron_left</span>
                </button>
            `;

            for (let i = 1; i <= Math.min(pagination.total_pages, 5); i++) {
                html += `<button class="w-8 h-8 flex items-center justify-center rounded ${i === pagination.page ? 'bg-secondary text-on-secondary' : 'border border-outline-variant bg-white text-on-surface-variant hover:bg-surface-container'} transition-colors font-label-sm" onclick="changePage(${i})">${i}</button>`;
            }

            html += `
                <button class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant bg-white text-on-surface-variant hover:bg-surface-container transition-colors ${pagination.page >= pagination.total_pages ? 'opacity-50' : ''}" 
                    ${pagination.page >= pagination.total_pages ? 'disabled' : ''} onclick="changePage(${pagination.page + 1})">
                    <span class="material-symbols-outlined text-[18px]" data-icon="chevron_right">chevron_right</span>
                </button>
            `;

            container.innerHTML = html;
        }

        function changePage(page) {
            currentPage = page;
            loadPOs();
        }

        function resetFilters() {
            currentPage = 1;
            currentFilters = { search: '', status: '', date: '' };
            document.getElementById('searchInput').value = '';
            document.getElementById('filterInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('dateFilter').value = '';
            loadPOs();
        }

        function viewPO(id) {
            window.location.href = 'po_detail.php?id=' + id;
        }

        function editPO(id) {
            window.location.href = 'po_create.php?id=' + id;
        }

        function printPO(id) {
            window.open('pratinjau_cetak_po_pdf.php?id=' + id, '_blank');
        }

        function uploadAttachment(id) {
            window.location.href = 'po_detail.php?id=' + id + '&tab=documents';
        }

        function formatCurrency(value) {
            if (!value) return 'Rp 0';
            return 'Rp ' + Number(value).toLocaleString('id-ID');
        }

        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
