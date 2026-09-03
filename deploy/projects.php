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
    <title>Master Project | Nexus Procurement</title>
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
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .code-pill { background: linear-gradient(135deg, #1e293b 0%, #091426 100%); color: #d8e2ff; }
    </style>
</head>
<body class="bg-background text-on-background antialiased">
    <!-- Global Layout Wrapper -->
    <div class="flex min-h-screen">
        <!-- SideNavBar -->
        <aside class="fixed h-screen w-sidebar-width left-0 top-0 bg-primary flex flex-col h-full overflow-y-auto z-50">
            <div class="p-6 flex flex-col items-start gap-1">
                <span class="text-headline-sm font-headline-sm text-on-primary">ProcureCorp</span>
                <span class="text-label-sm font-label-sm text-on-primary-container opacity-80">Enterprise Suite</span>
            </div>
            <nav class="flex-1 mt-4 px-4 space-y-1">
                <a class="flex items-center gap-3 px-4 py-3 rounded text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors duration-200" href="dashboard.php">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="font-label-md text-label-md">Dashboard</span>
                </a>
                <a class="flex items-center gap-3 px-4 py-3 rounded bg-secondary-container text-on-secondary-container border-l-4 border-secondary-fixed transition-transform active:scale-[0.98]" href="projects.php">
                    <span class="material-symbols-outlined">database</span>
                    <span class="font-label-md text-label-md">Master Data</span>
                </a>
                <a class="flex items-center gap-3 px-4 py-3 rounded text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors duration-200" href="po_list.php">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    <span class="font-label-md text-label-md">Purchase Orders</span>
                </a>
                <a class="flex items-center gap-3 px-4 py-3 rounded text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors duration-200" href="reports.php">
                    <span class="material-symbols-outlined">analytics</span>
                    <span class="font-label-md text-label-md">Reports</span>
                </a>
                <a class="flex items-center gap-3 px-4 py-3 rounded text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors duration-200" href="settings.php">
                    <span class="material-symbols-outlined">settings</span>
                    <span class="font-label-md text-label-md">Settings</span>
                </a>
            </nav>
            <div class="p-4 mt-auto border-t border-primary-container">
                <div class="flex items-center gap-3 px-2 py-3">
                    <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container font-bold">
                        <?php echo strtoupper(substr($userName, 0, 2)); ?>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-on-primary font-label-md text-label-md"><?php echo htmlspecialchars($userName); ?></span>
                        <span class="text-on-primary-container text-body-sm font-body-sm"><?php echo htmlspecialchars($userRole); ?></span>
                    </div>
                </div>
                <a href="api/auth.php?action=logout" class="flex items-center gap-sm text-on-primary-container hover:text-error text-body-sm transition-colors">
                    <span class="material-symbols-outlined text-[16px]">logout</span>
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 ml-sidebar-width flex flex-col min-h-screen">
            <!-- TopNavBar -->
            <header class="flex justify-between items-center h-16 px-container-margin sticky top-0 z-40 bg-surface border-b border-outline-variant transition-all duration-150">
                <div class="flex items-center gap-4">
                    <h1 class="font-headline-sm text-headline-sm text-primary">Procurement Management</h1>
                    <div class="hidden md:flex items-center bg-surface-container-low rounded-lg px-3 py-1.5 border border-outline-variant group focus-within:border-secondary transition-all">
                        <span class="material-symbols-outlined text-on-surface-variant text-[20px]">search</span>
                        <input class="bg-transparent border-none focus:ring-0 text-body-md font-body-md w-64 text-on-surface" placeholder="Cari project..." type="text" id="projectSearch">
                    </div>
                </div>
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-4">
                        <button class="material-symbols-outlined text-primary hover:text-secondary transition-all">notifications</button>
                        <button class="material-symbols-outlined text-primary hover:text-secondary transition-all">help</button>
                    </div>
                </div>
            </header>

            <!-- Page Canvas -->
            <section class="p-lg flex-1">
                <!-- Breadcrumbs & Header Actions -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-lg">
                    <div>
                        <nav class="flex items-center gap-2 text-on-surface-variant font-label-sm text-label-sm mb-1">
                            <span class="hover:text-secondary cursor-pointer">Master Data</span>
                            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                            <span class="text-secondary font-bold">Kode Project</span>
                        </nav>
                        <h2 class="font-display-lg text-display-lg text-primary tracking-tight">Project Registry</h2>
                    </div>
                    <button class="inline-flex items-center gap-2 bg-secondary text-on-secondary px-6 py-2.5 rounded-lg font-label-md text-label-md hover:brightness-110 active:scale-95 transition-all shadow-sm" onclick="openProjectModal()">
                        <span class="material-symbols-outlined text-[20px]">add_circle</span>
                        Tambah Project
                    </button>
                </div>

                <!-- Bento-style Project Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-lg">
                    <div class="bg-white p-md rounded-xl border border-outline-variant flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-secondary-container/10 flex items-center justify-center text-secondary">
                            <span class="material-symbols-outlined">folder_open</span>
                        </div>
                        <div>
                            <p class="text-body-sm font-body-sm text-on-surface-variant">Active Projects</p>
                            <p class="text-headline-md font-headline-md text-primary" id="activeProjects">124</p>
                        </div>
                    </div>
                    <div class="bg-white p-md rounded-xl border border-outline-variant flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-tertiary-container/10 flex items-center justify-center text-on-tertiary-container">
                            <span class="material-symbols-outlined">location_on</span>
                        </div>
                        <div>
                            <p class="text-body-sm font-body-sm text-on-surface-variant">Total Locations</p>
                            <p class="text-headline-md font-headline-md text-primary">18</p>
                        </div>
                    </div>
                    <div class="bg-white p-md rounded-xl border border-outline-variant flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-error-container/10 flex items-center justify-center text-error">
                            <span class="material-symbols-outlined">pending_actions</span>
                        </div>
                        <div>
                            <p class="text-body-sm font-body-sm text-on-surface-variant">Pending Approval</p>
                            <p class="text-headline-md font-headline-md text-primary">7</p>
                        </div>
                    </div>
                    <div class="bg-white p-md rounded-xl border border-outline-variant flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-primary-container/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined">group</span>
                        </div>
                        <div>
                            <p class="text-body-sm font-body-sm text-on-surface-variant">Key Clients</p>
                            <p class="text-headline-md font-headline-md text-primary">42</p>
                        </div>
                    </div>
                </div>

                <!-- Main Data Table Container -->
                <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                    <!-- Table Search & Filter Bar -->
                    <div class="px-md py-4 border-b border-outline-variant flex flex-col md:flex-row justify-between items-center gap-4 bg-surface-container-lowest">
                        <div class="relative w-full md:w-96">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">search</span>
                            <input class="w-full pl-10 pr-4 py-2 border border-outline-variant rounded-lg text-body-md font-body-md focus:border-secondary focus:ring-1 focus:ring-secondary transition-all" placeholder="Filter by project code, name, or client..." type="text" id="tableSearch">
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="flex items-center gap-2 px-3 py-2 border border-outline-variant rounded-lg text-body-md font-body-md text-on-surface hover:bg-surface-container-low transition-colors">
                                <span class="material-symbols-outlined text-[18px]">filter_list</span>
                                Filters
                            </button>
                            <button class="flex items-center gap-2 px-3 py-2 border border-outline-variant rounded-lg text-body-md font-body-md text-on-surface hover:bg-surface-container-low transition-colors">
                                <span class="material-symbols-outlined text-[18px]">download</span>
                                Export
                            </button>
                        </div>
                    </div>

                    <!-- High-Density Data Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-container-low">
                                    <th class="px-md py-3 text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider">Kode Project</th>
                                    <th class="px-md py-3 text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider">Nama Project</th>
                                    <th class="px-md py-3 text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider">Tanggal</th>
                                    <th class="px-md py-3 text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider">PIC</th>
                                    <th class="px-md py-3 text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider">Sebelum PPN</th>
                                    <th class="px-md py-3 text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider">incl PPN</th>
                                    <th class="px-md py-3 text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider">Status</th>
                                    <th class="px-md py-3 text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant" id="projectTableBody">
                                <!-- Data will be loaded via JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-md py-4 border-t border-outline-variant flex items-center justify-between bg-surface-container-low">
                        <span class="text-body-sm font-body-sm text-on-surface-variant" id="projectCount">Memuat data...</span>
                        <div class="flex items-center gap-1" id="pagination">
                            <!-- Pagination will be loaded via JavaScript -->
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal Form -->
    <div id="projectModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
        <div class="bg-white rounded-xl p-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-lg">
                <h3 class="font-headline-sm text-headline-sm text-primary">Tambah / Edit Project</h3>
                <button onclick="closeProjectModal()" class="text-on-surface-variant hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="projectForm" class="space-y-md">
                <input type="hidden" id="projectId" value="">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-label-sm font-label-sm text-on-surface-variant">Kode Project</label>
                        <input class="w-full border-outline-variant rounded-lg p-3 text-body-md focus:border-secondary focus:ring-2 focus:ring-secondary/10 transition-all" placeholder="PRJ-2024-001" type="text" id="projectCode" required>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-label-sm font-label-sm text-on-surface-variant">Nama Project</label>
                        <input class="w-full border-outline-variant rounded-lg p-3 text-body-md focus:border-secondary focus:ring-2 focus:ring-secondary/10 transition-all" placeholder="Nama project..." type="text" id="projectName" required>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-label-sm font-label-sm text-on-surface-variant">Client</label>
                        <input class="w-full border-outline-variant rounded-lg p-3 text-body-md focus:border-secondary focus:ring-2 focus:ring-secondary/10 transition-all" placeholder="Nama client..." type="text" id="projectClient">
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-label-sm font-label-sm text-on-surface-variant">PIC</label>
                        <input class="w-full border-outline-variant rounded-lg p-3 text-body-md focus:border-secondary focus:ring-2 focus:ring-secondary/10 transition-all" placeholder="Nama PIC..." type="text" id="projectPic">
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-label-sm font-label-sm text-on-surface-variant">Nilai Sebelum PPN</label>
                        <input class="w-full border-outline-variant rounded-lg p-3 text-body-md focus:border-secondary focus:ring-2 focus:ring-secondary/10 transition-all" placeholder="0" type="number" id="projectValueBeforePpn">
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-label-sm font-label-sm text-on-surface-variant">Nilai incl PPN</label>
                        <input class="w-full border-outline-variant rounded-lg p-3 text-body-md focus:border-secondary focus:ring-2 focus:ring-secondary/10 transition-all" placeholder="0" type="number" id="projectValueIncPpn">
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-label-sm font-label-sm text-on-surface-variant">Status</label>
                        <select class="w-full border-outline-variant rounded-lg p-3 text-body-md focus:border-secondary focus:ring-2 focus:ring-secondary/10 transition-all" id="projectStatus">
                            <option value="PENDING">Pending</option>
                            <option value="ACTIVE">Active</option>
                            <option value="ON HOLD">On Hold</option>
                            <option value="COMPLETED">Completed</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-label-sm font-label-sm text-on-surface-variant">Lokasi</label>
                        <input class="w-full border-outline-variant rounded-lg p-3 text-body-md focus:border-secondary focus:ring-2 focus:ring-secondary/10 transition-all" placeholder="Lokasi project..." type="text" id="projectLocation">
                    </div>
                </div>
                <div class="mt-lg flex justify-end gap-sm">
                    <button type="button" class="px-md py-2 border border-outline-variant rounded-lg text-label-md" onclick="closeProjectModal()">Batal</button>
                    <button type="submit" class="px-md py-2 bg-secondary text-on-secondary rounded-lg text-label-md">Simpan Project</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let searchTimeout;

        document.addEventListener('DOMContentLoaded', function() {
            loadProjects();
        });

        document.getElementById('projectSearch')?.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentPage = 1;
                loadProjects(e.target.value);
            }, 300);
        });

        document.getElementById('tableSearch')?.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentPage = 1;
                loadProjects(e.target.value);
            }, 300);
        });

        document.getElementById('projectForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            saveProject();
        });

        async function loadProjects(search = '') {
            try {
                let url = 'api/projects.php?page=' + currentPage + '&limit=10';
                if (search) {
                    url += '&search=' + encodeURIComponent(search);
                }

                const response = await fetch(url);
                const result = await response.json();

                if (result.success) {
                    renderProjects(result.data.projects);
                    renderPagination(result.data.pagination);
                    document.getElementById('projectCount').textContent = 
                        'Menampilkan ' + result.data.projects.length + ' dari ' + result.data.pagination.total + ' project';
                } else {
                    console.error('Failed to load projects:', result.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function renderProjects(projects) {
            const tbody = document.getElementById('projectTableBody');
            if (!tbody) return;

            if (projects.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="px-md py-4 text-center text-on-surface-variant">Tidak ada data project</td></tr>';
                return;
            }

            const statusColors = {
                'ACTIVE': 'bg-on-tertiary-container/10 text-on-tertiary-container',
                'ON HOLD': 'bg-secondary-fixed-dim/30 text-on-secondary-fixed-variant',
                'PENDING': 'bg-error-container/20 text-error',
                'COMPLETED': 'bg-surface-container-highest text-on-surface-variant'
            };

            tbody.innerHTML = projects.map(project => `
                <tr class="hover:bg-primary/5 transition-colors group">
                    <td class="px-md py-4">
                        <span class="code-pill px-3 py-1 rounded font-bold font-data-tabular text-[13px] inline-block tracking-wide">${escapeHtml(project.code)}</span>
                    </td>
                    <td class="px-md py-4 font-label-md text-label-md text-primary">${escapeHtml(project.name)}</td>
                    <td class="px-md py-4 font-body-md text-body-md text-on-surface-variant">${formatDate(project.created_at)}</td>
                    <td class="px-md py-4 font-body-md text-body-md ${project.pic ? 'text-primary' : 'text-on-surface-variant italic'}">${escapeHtml(project.pic || 'Not Assigned')}</td>
                    <td class="px-md py-4 font-data-tabular text-body-md text-primary">${formatCurrency(project.value_before_ppn)}</td>
                    <td class="px-md py-4 font-data-tabular text-body-md text-primary">${formatCurrency(project.value_inc_ppn)}</td>
                    <td class="px-md py-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full ${statusColors[project.status] || 'bg-surface-container-highest text-on-surface-variant'} font-label-sm text-[11px]">${project.status}</span>
                    </td>
                    <td class="px-md py-4 text-right">
                        <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button class="p-2 text-on-surface-variant hover:text-secondary hover:bg-secondary/10 rounded transition-all" title="Edit" onclick="editProject(${project.id})">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </button>
                            <button class="p-2 text-on-surface-variant hover:text-error hover:bg-error/10 rounded transition-all" title="Delete" onclick="deleteProject(${project.id})">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
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
                <button class="p-1 rounded hover:bg-surface-container-highest transition-colors disabled:opacity-30" 
                    ${pagination.page <= 1 ? 'disabled' : ''} onclick="changePage(${pagination.page - 1})">
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
            `;

            for (let i = 1; i <= Math.min(pagination.total_pages, 5); i++) {
                html += `<button class="w-8 h-8 rounded ${i === pagination.page ? 'bg-secondary text-on-secondary' : 'hover:bg-surface-container-highest'} font-label-sm text-label-sm" onclick="changePage(${i})">${i}</button>`;
            }

            html += `
                <button class="p-1 rounded hover:bg-surface-container-highest transition-colors" 
                    ${pagination.page >= pagination.total_pages ? 'disabled' : ''} onclick="changePage(${pagination.page + 1})">
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>
            `;

            container.innerHTML = html;
        }

        function changePage(page) {
            currentPage = page;
            const search = document.getElementById('tableSearch')?.value || '';
            loadProjects(search);
        }

        function openProjectModal() {
            document.getElementById('projectForm').reset();
            document.getElementById('projectId').value = '';
            document.getElementById('projectModal').classList.remove('hidden');
            document.getElementById('projectModal').classList.add('flex');
        }

        function closeProjectModal() {
            document.getElementById('projectModal').classList.add('hidden');
            document.getElementById('projectModal').classList.remove('flex');
        }

        async function saveProject() {
            const id = document.getElementById('projectId').value;
            const code = document.getElementById('projectCode').value;
            const name = document.getElementById('projectName').value;
            const client = document.getElementById('projectClient').value;
            const pic = document.getElementById('projectPic').value;
            const value_before_ppn = document.getElementById('projectValueBeforePpn').value || 0;
            const value_inc_ppn = document.getElementById('projectValueIncPpn').value || 0;
            const status = document.getElementById('projectStatus').value;
            const location = document.getElementById('projectLocation').value;

            const data = { code, name, client, pic, value_before_ppn, value_inc_ppn, status, location };

            const url = id ? 'api/projects.php' : 'api/projects.php';
            const method = id ? 'PUT' : 'POST';
            
            if (id) {
                data.id = id;
            }

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                if (result.success) {
                    closeProjectModal();
                    loadProjects();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal menyimpan data');
            }
        }

        async function editProject(id) {
            try {
                const response = await fetch('api/projects.php?id=' + id);
                const result = await response.json();

                if (result.success) {
                    const project = result.data;
                    document.getElementById('projectId').value = project.id;
                    document.getElementById('projectCode').value = project.code;
                    document.getElementById('projectName').value = project.name;
                    document.getElementById('projectClient').value = project.client || '';
                    document.getElementById('projectPic').value = project.pic || '';
                    document.getElementById('projectValueBeforePpn').value = project.value_before_ppn;
                    document.getElementById('projectValueIncPpn').value = project.value_inc_ppn;
                    document.getElementById('projectStatus').value = project.status;
                    document.getElementById('projectLocation').value = project.location || '';
                    
                    openProjectModal();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function deleteProject(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus project ini?')) {
                return;
            }

            try {
                const response = await fetch('api/projects.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });

                const result = await response.json();
                
                if (result.success) {
                    loadProjects();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal menghapus data');
            }
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
