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
<html class="light" lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Dashboard | Nexus Procurement</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-primary": "#ffffff",
                        "secondary-container": "#2170e4",
                        "on-tertiary-container": "#00a472",
                        "inverse-on-surface": "#f3f0f2",
                        "primary-fixed": "#d8e3fb",
                        "secondary-fixed": "#d8e2ff",
                        "surface-container-highest": "#e4e2e3",
                        "on-tertiary-fixed-variant": "#005236",
                        "outline": "#75777d",
                        "surface-container-lowest": "#ffffff",
                        "on-primary-fixed-variant": "#3c475a",
                        "on-error": "#ffffff",
                        "inverse-primary": "#bcc7de",
                        "on-surface": "#1b1b1d",
                        "secondary-fixed-dim": "#adc6ff",
                        "on-secondary-fixed": "#001a42",
                        "surface": "#fbf8fa",
                        "on-error-container": "#93000a",
                        "on-surface-variant": "#45474c",
                        "surface-tint": "#545f73",
                        "secondary": "#0058be",
                        "primary": "#091426",
                        "on-secondary-container": "#fefcff",
                        "tertiary-container": "#00301e",
                        "primary-fixed-dim": "#bcc7de",
                        "tertiary-fixed-dim": "#4edea3",
                        "on-tertiary-fixed": "#002113",
                        "error": "#ba1a1a",
                        "inverse-surface": "#303032",
                        "on-secondary": "#ffffff",
                        "on-background": "#1b1b1d",
                        "on-primary-container": "#8590a6",
                        "outline-variant": "#c5c6cd",
                        "surface-dim": "#dcd9db",
                        "tertiary": "#00190e",
                        "error-container": "#ffdad6",
                        "on-primary-fixed": "#111c2d",
                        "tertiary-fixed": "#6ffbbe",
                        "surface-variant": "#e4e2e3",
                        "surface-container-high": "#eae7e9",
                        "primary-container": "#1e293b",
                        "surface-container": "#f0edef",
                        "surface-bright": "#fbf8fa",
                        "background": "#fbf8fa",
                        "on-tertiary": "#ffffff",
                        "on-secondary-fixed-variant": "#004395",
                        "surface-container-low": "#f5f3f4"
                    },
                    borderRadius: {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    spacing: {
                        "container-margin": "24px",
                        "xs": "4px",
                        "unit": "4px",
                        "lg": "24px",
                        "sidebar-width": "260px",
                        "sm": "8px",
                        "xl": "32px",
                        "md": "16px",
                        "gutter": "16px"
                    },
                    fontFamily: {
                        "label-md": ["Inter"],
                        "headline-md": ["Inter"],
                        "display-lg": ["Inter"],
                        "body-sm": ["Inter"],
                        "body-lg": ["Inter"],
                        "data-tabular": ["Inter"],
                        "label-sm": ["Inter"],
                        "body-md": ["Inter"],
                        "headline-sm": ["Inter"]
                    },
                    fontSize: {
                        "label-md": ["14px", {"lineHeight": "20px", "fontWeight": "600"}],
                        "headline-md": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                        "display-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "body-sm": ["12px", {"lineHeight": "16px", "fontWeight": "400"}],
                        "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "data-tabular": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                        "label-sm": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}],
                        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "headline-sm": ["20px", {"lineHeight": "28px", "fontWeight": "600"}]
                    }
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #fbf8fa; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .sidebar-active-indicator {
            position: absolute;
            left: 0;
            width: 4px;
            height: 100%;
        }
    </style>
</head>
<body class="bg-background text-on-surface">
    <!-- Side Navigation Bar -->
    <aside class="fixed h-screen w-sidebar-width left-0 top-0 bg-primary flex flex-col h-full overflow-y-auto z-50">
        <div class="px-md py-xl">
            <h1 class="text-headline-sm font-headline-sm text-on-primary">ProcureCorp</h1>
            <p class="text-on-primary-container font-label-md text-label-md opacity-70">Enterprise Suite</p>
        </div>
        <nav class="flex-grow">
            <a class="flex items-center px-md py-md text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors duration-200 font-label-md text-label-md" href="dashboard.php">
                <span class="material-symbols-outlined mr-md">dashboard</span>
                <span>Dashboard</span>
            </a>
            <a class="flex items-center px-md py-md text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors duration-200 font-label-md text-label-md" href="vendors.php">
                <span class="material-symbols-outlined mr-md">database</span>
                <span>Master Data</span>
            </a>
            <a class="flex items-center px-md py-md text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors duration-200 font-label-md text-label-md" href="po_list.php">
                <span class="material-symbols-outlined mr-md">shopping_cart</span>
                <span>Purchase Orders</span>
            </a>
            <a class="flex items-center px-md py-md text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors duration-200 font-label-md text-label-md" href="reports.php">
                <span class="material-symbols-outlined mr-md">analytics</span>
                <span>Reports</span>
            </a>
            <a class="flex items-center px-md py-md text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors duration-200 font-label-md text-label-md" href="settings.php">
                <span class="material-symbols-outlined mr-md">settings</span>
                <span>Settings</span>
            </a>
        </nav>
        <div class="mt-auto p-md border-t border-on-primary-container/20">
            <div class="flex items-center space-x-sm">
                <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container font-bold">
                    <?php echo strtoupper(substr($userName, 0, 2)); ?>
                </div>
                <div class="overflow-hidden">
                    <p class="text-on-primary font-label-md text-label-md truncate"><?php echo htmlspecialchars($userName); ?></p>
                    <p class="text-on-primary-container text-body-sm font-body-sm truncate"><?php echo htmlspecialchars($userRole); ?></p>
                </div>
            </div>
            <a href="api/auth.php?action=logout" class="mt-2 flex items-center gap-sm text-on-primary-container hover:text-error text-body-sm transition-colors">
                <span class="material-symbols-outlined text-[16px]">logout</span>
                Logout
            </a>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="ml-sidebar-width min-h-screen flex flex-col">
        <!-- Top Navigation Bar -->
        <header class="sticky top-0 z-40 bg-surface border-b border-outline-variant flex justify-between items-center h-16 px-container-margin">
            <div class="flex items-center gap-xl">
                <span class="font-headline-sm text-headline-sm text-primary">Procurement Management</span>
                <div class="relative w-80">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-on-surface-variant">
                        <span class="material-symbols-outlined">search</span>
                    </span>
                    <input class="w-full pl-10 pr-3 py-1.5 bg-surface-container-low border border-outline-variant rounded-lg text-body-md font-body-md focus:ring-2 focus:ring-secondary focus:border-secondary" placeholder="Pencarian Global..." type="text">
                </div>
            </div>
            <div class="flex items-center gap-md">
                <button class="p-2 text-on-surface-variant hover:text-secondary transition-all duration-150">
                    <span class="material-symbols-outlined">notifications</span>
                </button>
                <button class="p-2 text-on-surface-variant hover:text-secondary transition-all duration-150">
                    <span class="material-symbols-outlined">help</span>
                </button>
            </div>
        </header>

        <!-- Page Canvas -->
        <main class="p-container-margin flex-grow bg-background">
            <!-- Breadcrumbs and Action Header -->
            <div class="mb-xl flex flex-col md:flex-row md:items-center justify-between gap-md">
                <div>
                    <nav aria-label="Breadcrumb" class="flex text-body-sm font-body-sm text-on-surface-variant mb-xs">
                        <ol class="flex items-center space-x-2">
                            <li><span>Home</span></li>
                            <li><span class="material-symbols-outlined text-[14px]">chevron_right</span></li>
                            <li class="text-primary font-semibold">Dashboard</li>
                        </ol>
                    </nav>
                    <h2 class="text-display-lg font-display-lg text-primary">Dashboard</h2>
                </div>
                <a href="po_create.php" class="bg-secondary text-on-secondary px-lg py-md rounded-lg flex items-center gap-sm font-label-md text-label-md hover:bg-secondary/90 transition-all shadow-sm active:scale-95">
                    <span class="material-symbols-outlined">add</span>
                    Buat PO Baru
                </a>
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-lg mb-xl">
                <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm flex flex-col justify-between h-32 hover:border-secondary/30 transition-colors">
                    <div class="flex justify-between items-start">
                        <span class="text-label-sm uppercase text-on-surface-variant tracking-wider">Total Active PO</span>
                        <span class="material-symbols-outlined text-secondary">receipt_long</span>
                    </div>
                    <div>
                        <p class="text-headline-md font-headline-md" id="stat-active-po">-</p>
                        <p class="text-label-sm text-tertiary-fixed-dim flex items-center gap-xs">
                            <span class="material-symbols-outlined text-[14px]">trending_up</span> <span id="stat-active-label">Memuat data...</span>
                        </p>
                    </div>
                </div>
                <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm flex flex-col justify-between h-32 hover:border-secondary/30 transition-colors">
                    <div class="flex justify-between items-start">
                        <span class="text-label-sm uppercase text-on-surface-variant tracking-wider">Pending Signed</span>
                        <span class="material-symbols-outlined text-error">signature</span>
                    </div>
                    <div>
                        <p class="text-headline-md font-headline-md" id="stat-pending-signed">-</p>
                        <p class="text-label-sm text-on-surface-variant italic">Menunggu persetujuan</p>
                    </div>
                </div>
                <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm flex flex-col justify-between h-32 hover:border-secondary/30 transition-colors">
                    <div class="flex justify-between items-start">
                        <span class="text-label-sm uppercase text-on-surface-variant tracking-wider">Total Value</span>
                        <span class="material-symbols-outlined text-tertiary-fixed-dim">payments</span>
                    </div>
                    <div>
                        <p class="text-headline-md font-headline-md" id="stat-total-value">-</p>
                        <p class="text-label-sm text-on-surface-variant">Nilai total PO aktif</p>
                    </div>
                </div>
                <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm flex flex-col justify-between h-32 hover:border-secondary/30 transition-colors">
                    <div class="flex justify-between items-start">
                        <span class="text-label-sm uppercase text-on-surface-variant tracking-wider">Total Vendors</span>
                        <span class="material-symbols-outlined text-secondary">group</span>
                    </div>
                    <div>
                        <p class="text-headline-md font-headline-md" id="stat-total-vendors">-</p>
                        <p class="text-label-sm text-on-surface-variant flex items-center gap-xs">Mitra terdaftar</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-lg">
                <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm">
                    <h3 class="font-headline-sm text-headline-sm text-primary mb-md">Akses Cepat</h3>
                    <div class="grid grid-cols-2 gap-md">
                        <a href="po_create.php" class="flex items-center gap-md p-md border border-outline-variant rounded-lg hover:bg-surface-container-low transition-colors">
                            <span class="material-symbols-outlined text-secondary">add_circle</span>
                            <span class="font-label-md">Buat PO Baru</span>
                        </a>
                        <a href="vendors.php" class="flex items-center gap-md p-md border border-outline-variant rounded-lg hover:bg-surface-container-low transition-colors">
                            <span class="material-symbols-outlined text-secondary">person_add</span>
                            <span class="font-label-md">Tambah Vendor</span>
                        </a>
                        <a href="projects.php" class="flex items-center gap-md p-md border border-outline-variant rounded-lg hover:bg-surface-container-low transition-colors">
                            <span class="material-symbols-outlined text-secondary">folder_open</span>
                            <span class="font-label-md">Tambah Project</span>
                        </a>
                        <a href="reports.php" class="flex items-center gap-md p-md border border-outline-variant rounded-lg hover:bg-surface-container-low transition-colors">
                            <span class="material-symbols-outlined text-secondary">download</span>
                            <span class="font-label-md">Ekspor Laporan</span>
                        </a>
                    </div>
                </div>
                <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm">
                    <h3 class="font-headline-sm text-headline-sm text-primary mb-md">Aktivitas Terakhir</h3>
                    <div class="space-y-md" id="recent-activity">
                        <div class="text-center text-on-surface-variant py-4">
                            <span class="material-symbols-outlined animate-spin text-secondary">refresh</span>
                            <p class="text-body-sm mt-2">Memuat aktivitas...</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('mousedown', function(e) {
                this.classList.add('scale-[0.98]');
            });
            btn.addEventListener('mouseup', function(e) {
                this.classList.remove('scale-[0.98]');
            });
            btn.addEventListener('mouseleave', function(e) {
                this.classList.remove('scale-[0.98]');
            });
        });

        // Load dashboard stats
        document.addEventListener('DOMContentLoaded', loadDashboardStats);

        async function loadDashboardStats() {
            try {
                const response = await fetch('api/dashboard.php');
                const result = await response.json();

                if (result.success) {
                    const stats = result.data.summary;
                    
                    document.getElementById('stat-active-po').textContent = stats.total_active_po.toLocaleString();
                    document.getElementById('stat-active-label').textContent = 'PO aktif saat ini';
                    
                    document.getElementById('stat-pending-signed').textContent = stats.pending_signed.toLocaleString();
                    
                    document.getElementById('stat-total-value').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(stats.total_value);
                    
                    document.getElementById('stat-total-vendors').textContent = stats.total_vendors.toLocaleString();

                    // Render recent activity
                    renderRecentActivity(result.data.recent_activity);
                }
            } catch (error) {
                console.error('Error loading dashboard stats:', error);
            }
        }

        function renderRecentActivity(activities) {
            const container = document.getElementById('recent-activity');
            if (!container) return;

            if (activities.length === 0) {
                container.innerHTML = '<p class="text-center text-on-surface-variant py-4">Belum ada aktivitas</p>';
                return;
            }

            const actionIcons = {
                'created': 'note_add',
                'updated': 'edit',
                'status_changed': 'swap_horiz',
                'attachment_uploaded': 'upload_file'
            };

            container.innerHTML = activities.map(activity => {
                const icon = actionIcons[activity.action] || 'info';
                const timeAgo = getTimeAgo(activity.created_at);
                return `
                    <div class="flex items-start gap-md">
                        <div class="w-8 h-8 rounded-full bg-secondary-fixed flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-[18px] text-on-secondary-fixed-variant">${icon}</span>
                        </div>
                        <div>
                            <p class="text-label-md text-on-surface">${activity.description || activity.action}</p>
                            <p class="text-label-sm text-on-surface-variant">${timeAgo} • Oleh ${activity.user_name || 'System'}</p>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function getTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);

            if (diff < 60) return 'Baru saja';
            if (diff < 3600) return Math.floor(diff / 60) + ' menit yang lalu';
            if (diff < 86400) return Math.floor(diff / 3600) + ' jam yang lalu';
            if (diff < 604800) return Math.floor(diff / 86400) + ' hari yang lalu';
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        }
    </script>
</body>
</html>
