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
    <title>Master Vendor | Nexus Procurement</title>
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
            <a class="relative flex items-center px-md py-md bg-secondary-container text-on-secondary-container border-l-4 border-secondary-fixed transition-transform active:scale-[0.98] font-label-md text-label-md" href="vendors.php">
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
                    <input class="w-full pl-10 pr-3 py-1.5 bg-surface-container-low border border-outline-variant rounded-lg text-body-md font-body-md focus:ring-2 focus:ring-secondary focus:border-secondary" placeholder="Pencarian Global..." type="text" id="searchInput">
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
                            <li><a href="dashboard.php" class="hover:text-secondary">Dashboard</a></li>
                            <li><span class="material-symbols-outlined text-[14px]">chevron_right</span></li>
                            <li><a href="vendors.php" class="hover:text-secondary">Master Data</a></li>
                            <li><span class="material-symbols-outlined text-[14px]">chevron_right</span></li>
                            <li class="text-primary font-semibold">Vendors</li>
                        </ol>
                    </nav>
                    <h2 class="text-display-lg font-display-lg text-primary">Master Vendor Management</h2>
                </div>
                <button class="bg-secondary text-on-secondary px-lg py-md rounded-lg flex items-center gap-sm font-label-md text-label-md hover:bg-secondary/90 transition-all shadow-sm active:scale-95" onclick="openModal()">
                    <span class="material-symbols-outlined">add</span>
                    Tambah Vendor
                </button>
            </div>

            <!-- Dashboard Content: Data Table Area -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
                <!-- Filters and Search Toolbar -->
                <div class="p-md border-b border-outline-variant flex flex-col md:flex-row justify-between items-center gap-md bg-white">
                    <div class="relative w-full md:w-96">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-on-surface-variant">
                            <span class="material-symbols-outlined">search</span>
                        </span>
                        <input class="w-full pl-10 pr-3 py-2 bg-surface-container-low border border-outline-variant rounded-lg text-body-md font-body-md focus:ring-2 focus:ring-secondary focus:border-secondary" placeholder="Cari nama, NPWP, atau email..." type="text" id="tableSearch">
                    </div>
                    <div class="flex items-center gap-sm w-full md:w-auto">
                        <button class="flex items-center gap-sm border border-outline-variant px-md py-2 rounded-lg text-body-md font-body-md hover:bg-surface-variant transition-colors">
                            <span class="material-symbols-outlined">filter_list</span>
                            Filter
                        </button>
                        <button class="flex items-center gap-sm border border-outline-variant px-md py-2 rounded-lg text-body-md font-body-md hover:bg-surface-variant transition-colors" onclick="exportData()">
                            <span class="material-symbols-outlined">download</span>
                            Export
                        </button>
                    </div>
                </div>

                <!-- Vendor Data Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-high border-b border-outline-variant">
                                <th class="px-lg py-md text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider">Nama Vendor</th>
                                <th class="px-lg py-md text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider">Alamat</th>
                                <th class="px-lg py-md text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider">NAMA PIC</th>
                                <th class="px-lg py-md text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider">NPWP</th>
                                <th class="px-lg py-md text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider">Kontak</th>
                                <th class="px-lg py-md text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider">Email</th>
                                <th class="px-lg py-md text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant" id="vendorTableBody">
                            <!-- Data will be loaded via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer Pagination -->
                <div class="p-md bg-surface-container border-t border-outline-variant flex flex-col md:flex-row justify-between items-center gap-md">
                    <span class="text-body-sm font-body-sm text-on-surface-variant" id="vendorCount">Memuat data...</span>
                    <div class="flex items-center gap-sm" id="pagination">
                        <!-- Pagination will be loaded via JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Contextual Insight (Bento Element) -->
            <div class="mt-lg grid grid-cols-1 md:grid-cols-3 gap-lg">
                <div class="bg-primary p-lg rounded-xl flex items-center justify-between shadow-sm overflow-hidden relative group">
                    <div class="z-10">
                        <p class="text-on-primary-container text-label-sm font-label-sm uppercase opacity-80 mb-xs">Active Vendors</p>
                        <h3 class="text-on-primary text-display-lg font-display-lg" id="activeVendors">248</h3>
                        <p class="text-on-tertiary-container text-body-sm font-body-sm mt-sm flex items-center gap-xs">
                            <span class="material-symbols-outlined text-[14px]">trending_up</span>
                            +12 bulan ini
                        </p>
                    </div>
                    <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                        <span class="material-symbols-outlined text-[120px] text-white">groups</span>
                    </div>
                </div>
                <div class="bg-white border border-outline-variant p-lg rounded-xl flex items-center justify-between shadow-sm overflow-hidden relative group">
                    <div class="z-10">
                        <p class="text-on-surface-variant text-label-sm font-label-sm uppercase opacity-80 mb-xs">Top Category</p>
                        <h3 class="text-primary text-display-lg font-display-lg">Logistics</h3>
                        <p class="text-on-surface-variant text-body-sm font-body-sm mt-sm">42% dari total procurement</p>
                    </div>
                    <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-500">
                        <span class="material-symbols-outlined text-[120px] text-primary">local_shipping</span>
                    </div>
                </div>
                <div class="bg-white border border-outline-variant p-lg rounded-xl flex items-center justify-between shadow-sm overflow-hidden relative group">
                    <div class="z-10">
                        <p class="text-on-surface-variant text-label-sm font-label-sm uppercase opacity-80 mb-xs">Compliance Rate</p>
                        <h3 class="text-primary text-display-lg font-display-lg">94.2%</h3>
                        <div class="w-32 h-2 bg-surface-container-high rounded-full mt-sm">
                            <div class="bg-on-tertiary-container h-full rounded-full w-[94.2%]"></div>
                        </div>
                    </div>
                    <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-500">
                        <span class="material-symbols-outlined text-[120px] text-primary">verified</span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Form -->
    <div id="vendorModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
        <div class="bg-white rounded-xl p-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-lg">
                <h3 class="font-headline-sm text-headline-sm text-primary">Tambah / Edit Vendor</h3>
                <button onclick="closeModal()" class="text-on-surface-variant hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="vendorForm" class="space-y-md">
                <input type="hidden" id="vendorId" value="">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                    <div class="flex flex-col gap-xs">
                        <label class="text-label-sm font-label-sm text-on-surface-variant">Nama Vendor</label>
                        <input class="p-2 border border-outline-variant rounded-lg text-body-md" placeholder="PT Example Jaya" type="text" id="vendorName" required>
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="text-label-sm font-label-sm text-on-surface-variant">Nama PIC (Contact Person)</label>
                        <input class="p-2 border border-outline-variant rounded-lg text-body-md" placeholder="Nama Lengkap PIC" type="text" id="vendorPic">
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="text-label-sm font-label-sm text-on-surface-variant">NPWP</label>
                        <input class="p-2 border border-outline-variant rounded-lg text-body-md" placeholder="00.000.000.0-000.000" type="text" id="vendorNpwp">
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="text-label-sm font-label-sm text-on-surface-variant">Upload Dokumen NPWP (Opsional)</label>
                        <div class="flex items-center gap-sm border border-dashed border-outline-variant p-2 rounded-lg bg-surface-container-low cursor-pointer" onclick="document.getElementById('npwpFile').click()">
                            <span class="material-symbols-outlined text-on-surface-variant">upload_file</span>
                            <span class="text-body-sm text-on-surface-variant" id="npwpFileName">Pilih file atau drag & drop</span>
                            <input type="file" id="npwpFile" class="hidden" accept="image/*,application/pdf" onchange="handleFileSelect(this)">
                        </div>
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="text-label-sm font-label-sm text-on-surface-variant">No. Telepon</label>
                        <input class="p-2 border border-outline-variant rounded-lg text-body-md" placeholder="+62 812 3456 7890" type="text" id="vendorPhone">
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="text-label-sm font-label-sm text-on-surface-variant">Email</label>
                        <input class="p-2 border border-outline-variant rounded-lg text-body-md" placeholder="info@vendor.com" type="email" id="vendorEmail">
                    </div>
                    <div class="flex flex-col gap-xs md:col-span-2">
                        <label class="text-label-sm font-label-sm text-on-surface-variant">Alamat</label>
                        <textarea class="p-2 border border-outline-variant rounded-lg text-body-md" placeholder="Alamat lengkap vendor..." rows="2" id="vendorAddress"></textarea>
                    </div>
                </div>
                <div class="mt-lg flex justify-end gap-sm">
                    <button type="button" class="px-md py-2 border border-outline-variant rounded-lg text-label-md" onclick="closeModal()">Batal</button>
                    <button type="submit" class="px-md py-2 bg-secondary text-on-secondary rounded-lg text-label-md">Simpan Vendor</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let searchTimeout;

        document.addEventListener('DOMContentLoaded', function() {
            loadVendors();
        });

        document.getElementById('tableSearch')?.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentPage = 1;
                loadVendors(e.target.value);
            }, 300);
        });

        document.getElementById('vendorForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            saveVendor();
        });

        async function loadVendors(search = '') {
            try {
                let url = 'api/vendors.php?page=' + currentPage + '&limit=10';
                if (search) {
                    url += '&search=' + encodeURIComponent(search);
                }

                const response = await fetch(url);
                const result = await response.json();

                if (result.success) {
                    renderVendors(result.data.vendors);
                    renderPagination(result.data.pagination);
                    document.getElementById('vendorCount').textContent = 
                        'Menampilkan ' + result.data.vendors.length + ' dari ' + result.data.pagination.total + ' vendor';
                } else {
                    console.error('Failed to load vendors:', result.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function renderVendors(vendors) {
            const tbody = document.getElementById('vendorTableBody');
            if (!tbody) return;

            if (vendors.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="px-lg py-md text-center text-on-surface-variant">Tidak ada data vendor</td></tr>';
                return;
            }

            tbody.innerHTML = vendors.map(vendor => `
                <tr class="hover:bg-primary/5 transition-colors group">
                    <td class="px-lg py-md">
                        <div class="flex items-center gap-md">
                            <div class="w-8 h-8 rounded bg-secondary/10 flex items-center justify-center text-secondary font-bold">
                                ${vendor.name.substring(0, 2).toUpperCase()}
                            </div>
                            <div>
                                <p class="font-label-md text-label-md text-on-surface">${escapeHtml(vendor.name)}</p>
                                <p class="text-body-sm font-body-sm text-on-surface-variant">${escapeHtml(vendor.email || '')}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-lg py-md text-body-md font-body-md text-on-surface-variant max-w-xs truncate">${escapeHtml(vendor.address || '')}</td>
                    <td class="px-lg py-md text-body-md font-body-md text-on-surface">${escapeHtml(vendor.contact_person || '-')}</td>
                    <td class="px-lg py-md font-data-tabular text-data-tabular">
                        ${escapeHtml(vendor.npwp || '-')}
                        ${vendor.npwp_file ? `<button class="ml-xs text-on-surface-variant hover:text-secondary" title="Lihat Dokumen NPWP" onclick="viewNPWP('${vendor.npwp_file}')">
                            <span class="material-symbols-outlined text-[16px]">attachment</span>
                        </button>` : ''}
                    </td>
                    <td class="px-lg py-md text-body-md font-body-md text-on-surface-variant">${escapeHtml(vendor.phone || '-')}</td>
                    <td class="px-lg py-md text-body-md font-body-md text-secondary">${escapeHtml(vendor.email || '-')}</td>
                    <td class="px-lg py-md">
                        <div class="flex items-center justify-center gap-sm">
                            <button class="p-1.5 text-on-surface-variant hover:text-secondary hover:bg-secondary/10 rounded transition-all" title="Edit" onclick="editVendor(${vendor.id})">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <button class="p-1.5 text-on-surface-variant hover:text-error hover:bg-error/10 rounded transition-all" title="Delete" onclick="deleteVendor(${vendor.id})">
                                <span class="material-symbols-outlined">delete</span>
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
                <button class="p-2 rounded hover:bg-surface-variant text-on-surface-variant disabled:opacity-50" 
                    ${pagination.page <= 1 ? 'disabled' : ''} onclick="changePage(${pagination.page - 1})">
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
            `;

            for (let i = 1; i <= Math.min(pagination.total_pages, 5); i++) {
                html += `<button class="w-8 h-8 rounded ${i === pagination.page ? 'bg-secondary text-on-secondary' : 'hover:bg-surface-variant text-on-surface-variant'} font-label-md text-label-md" onclick="changePage(${i})">${i}</button>`;
            }

            html += `
                <button class="p-2 rounded hover:bg-surface-variant text-on-surface-variant" 
                    ${pagination.page >= pagination.total_pages ? 'disabled' : ''} onclick="changePage(${pagination.page + 1})">
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>
            `;

            container.innerHTML = html;
        }

        function changePage(page) {
            currentPage = page;
            const search = document.getElementById('tableSearch')?.value || '';
            loadVendors(search);
        }

        function openModal() {
            document.getElementById('vendorForm').reset();
            document.getElementById('vendorId').value = '';
            document.getElementById('vendorModal').classList.remove('hidden');
            document.getElementById('vendorModal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('vendorModal').classList.add('hidden');
            document.getElementById('vendorModal').classList.remove('flex');
        }

        async function saveVendor() {
            const id = document.getElementById('vendorId').value;
            const name = document.getElementById('vendorName').value;
            const address = document.getElementById('vendorAddress').value;
            const npwp = document.getElementById('vendorNpwp').value;
            const phone = document.getElementById('vendorPhone').value;
            const contact_person = document.getElementById('vendorPic').value;
            const email = document.getElementById('vendorEmail').value;

            const data = { name, address, npwp, phone, contact_person, email };

            const url = id ? 'api/vendors.php' : 'api/vendors.php';
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
                    closeModal();
                    loadVendors();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal menyimpan data');
            }
        }

        async function editVendor(id) {
            try {
                const response = await fetch('api/vendors.php?id=' + id);
                const result = await response.json();

                if (result.success) {
                    const vendor = result.data;
                    document.getElementById('vendorId').value = vendor.id;
                    document.getElementById('vendorName').value = vendor.name;
                    document.getElementById('vendorAddress').value = vendor.address || '';
                    document.getElementById('vendorNpwp').value = vendor.npwp || '';
                    document.getElementById('vendorPhone').value = vendor.phone || '';
                    document.getElementById('vendorPic').value = vendor.contact_person || '';
                    document.getElementById('vendorEmail').value = vendor.email || '';
                    
                    if (vendor.npwp_file) {
                        document.getElementById('npwpFileName').textContent = 'File terunggah';
                    }
                    
                    openModal();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function deleteVendor(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus vendor ini?')) {
                return;
            }

            try {
                const response = await fetch('api/vendors.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });

                const result = await response.json();
                
                if (result.success) {
                    loadVendors();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal menghapus data');
            }
        }

        function handleFileSelect(input) {
            if (input.files && input.files[0]) {
                document.getElementById('npwpFileName').textContent = input.files[0].name;
            }
        }

        function viewNPWP(filePath) {
            window.open(filePath, '_blank');
        }

        function exportData() {
            alert('Fitur export akan mengunduh file Excel dengan data vendor.');
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
