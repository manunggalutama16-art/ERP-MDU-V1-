<?php
session_start();
require_once 'api/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'user';

// Fetch projects for filter dropdown
$conn = getConnection();
$projectsResult = $conn->query("SELECT id, code, name FROM projects ORDER BY name ASC");
$projects = [];
while ($row = $projectsResult->fetch_assoc()) {
    $projects[] = $row;
}
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Laporan & Ekspor Data | Nexus Procurement</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "secondary-container": "#2170e4",
                        "surface-container-highest": "#e4e2e3",
                        "surface-container": "#f0edef",
                        "outline-variant": "#c5c6cd",
                        "inverse-primary": "#bcc7de",
                        "primary-fixed-dim": "#bcc7de",
                        "on-primary": "#ffffff",
                        "on-surface-variant": "#45474c",
                        "tertiary": "#00190e",
                        "surface": "#fbf8fa",
                        "surface-container-low": "#f5f3f4",
                        "on-primary-fixed": "#111c2d",
                        "surface-bright": "#fbf8fa",
                        "tertiary-container": "#00301e",
                        "on-surface": "#1b1b1d",
                        "on-secondary-fixed": "#001a42",
                        "on-secondary": "#ffffff",
                        "on-tertiary-fixed": "#002113",
                        "on-secondary-fixed-variant": "#004395",
                        "on-error-container": "#93000a",
                        "surface-dim": "#dcd9db",
                        "secondary-fixed": "#d8e2ff",
                        "inverse-surface": "#303032",
                        "inverse-on-surface": "#f3f0f2",
                        "secondary": "#0058be",
                        "on-error": "#ffffff",
                        "on-secondary-container": "#fefcff",
                        "surface-container-high": "#eae7e9",
                        "tertiary-fixed-dim": "#4edea3",
                        "primary": "#091426",
                        "outline": "#75777d",
                        "on-tertiary": "#ffffff",
                        "primary-fixed": "#d8e3fb",
                        "secondary-fixed-dim": "#adc6ff",
                        "on-tertiary-fixed-variant": "#005236",
                        "error": "#ba1a1a",
                        "surface-variant": "#e4e2e3",
                        "on-tertiary-container": "#00a472",
                        "error-container": "#ffdad6",
                        "background": "#fbf8fa",
                        "on-background": "#1b1b1d",
                        "on-primary-fixed-variant": "#3c475a",
                        "surface-tint": "#545f73"
                    },
                    borderRadius: {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    spacing: {
                        "xl": "32px",
                        "unit": "4px",
                        "md": "16px",
                        "sidebar-width": "260px",
                        "sm": "8px",
                        "lg": "24px",
                        "container-margin": "24px",
                        "xs": "4px",
                        "gutter": "16px"
                    },
                    fontFamily: {
                        "data-tabular": ["Inter"],
                        "headline-sm": ["Inter"],
                        "headline-md": ["Inter"],
                        "body-sm": ["Inter"],
                        "label-sm": ["Inter"],
                        "body-lg": ["Inter"],
                        "display-lg": ["Inter"],
                        "body-md": ["Inter"],
                        "label-md": ["Inter"]
                    },
                    fontSize: {
                        "data-tabular": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                        "headline-sm": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
                        "headline-md": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                        "body-sm": ["12px", {"lineHeight": "16px", "fontWeight": "400"}],
                        "label-sm": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}],
                        "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "display-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "label-md": ["14px", {"lineHeight": "20px", "fontWeight": "600"}]
                    }
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .sidebar-active { background-color: #0058be; border-left: 4px solid #adc6ff; color: #ffffff; }
        .table-row-hover:hover { background-color: rgba(9, 20, 38, 0.02); }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: #c5c6cd; border-radius: 10px; }
    </style>
</head>
<body class="flex overflow-hidden">
    <!-- Side Navigation Shell -->
    <aside class="fixed left-0 top-0 h-full flex flex-col py-md z-40 bg-primary dark:bg-primary-container w-[260px] text-on-primary">
        <div class="px-lg mb-xl">
            <h1 class="font-headline-sm text-headline-sm text-on-primary font-bold">ProcureCorp</h1>
            <p class="font-body-sm text-body-sm opacity-70">Enterprise Suite</p>
        </div>
        <nav class="flex-grow space-y-unit mt-md">
            <a class="flex items-center px-lg py-md text-on-primary-container/70 hover:bg-secondary-container/10 transition-all font-label-md text-label-md" href="dashboard.php">
                <span class="material-symbols-outlined mr-md">dashboard</span>
                <span>Dashboard</span>
            </a>
            <a class="flex items-center px-lg py-md text-on-primary-container/70 hover:bg-secondary-container/10 transition-all font-label-md text-label-md" href="vendors.php">
                <span class="material-symbols-outlined mr-md">database</span>
                <span>Master Data</span>
            </a>
            <a class="flex items-center px-lg py-md text-on-primary-container/70 hover:bg-secondary-container/10 transition-all font-label-md text-label-md" href="po_list.php">
                <span class="material-symbols-outlined mr-md">shopping_cart</span>
                <span>Purchase Orders</span>
            </a>
            <a class="sidebar-active flex items-center px-lg py-md transition-all font-label-md text-label-md" href="reports.php">
                <span class="material-symbols-outlined mr-md">analytics</span>
                <span>Reports</span>
            </a>
        </nav>
        <div class="mt-auto px-lg mb-md">
            <a class="flex items-center px-lg py-sm text-on-primary-container/70 hover:bg-secondary-container/10 font-label-md text-label-md" href="settings.php">
                <span class="material-symbols-outlined mr-md">settings</span>
                <span>Settings</span>
            </a>
            <a class="flex items-center px-lg py-sm text-on-primary-container/70 hover:bg-secondary-container/10 font-label-md text-label-md" href="api/auth.php?action=logout">
                <span class="material-symbols-outlined mr-md">logout</span>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Canvas -->
    <main class="ml-[260px] flex-grow flex flex-col h-screen overflow-hidden">
        <!-- Top Navigation Bar -->
        <header class="flex justify-between items-center w-full px-lg h-16 bg-surface-container-lowest border-b border-outline-variant">
            <div class="flex items-center gap-md">
                <span class="font-headline-sm text-headline-sm font-bold text-primary">Procurement Engine</span>
                <div class="ml-xl flex gap-lg">
                    <a class="text-on-surface-variant font-medium font-label-md text-label-md hover:text-secondary transition-colors" href="reports.php">Overview</a>
                    <a class="text-secondary font-bold border-b-2 border-secondary font-label-md text-label-md" href="reports.php">Reports</a>
                </div>
            </div>
            <div class="flex items-center gap-md">
                <div class="w-8 h-8 rounded-full overflow-hidden border border-outline-variant ml-sm cursor-pointer">
                    <div class="w-full h-full flex items-center justify-center bg-secondary-container text-on-secondary-container font-label-md font-bold" title="<?php echo htmlspecialchars($userName); ?>"><?php echo htmlspecialchars(userInitials($userName)); ?></div>
                </div>
            </div>
        </header>

        <!-- Page Content Scrollable -->
        <div class="flex-grow overflow-y-auto p-lg space-y-lg bg-surface">
            <!-- Header Section -->
            <div class="flex justify-between items-end">
                <div>
                    <nav class="flex text-body-sm font-body-sm text-on-surface-variant opacity-60 mb-xs">
                        <span>Main</span>
                        <span class="mx-xs">/</span>
                        <span>Reports</span>
                    </nav>
                    <h2 class="font-headline-md text-headline-md text-primary">Laporan Pengadaan</h2>
                </div>
                <button class="flex items-center gap-sm bg-secondary text-on-secondary px-lg py-md rounded-lg font-label-md text-label-md shadow-sm hover:opacity-90 transition-opacity active:scale-[0.98]" onclick="exportExcel()">
                    <span class="material-symbols-outlined text-[20px]">download</span>
                    <span>Download Excel (.xlsx)</span>
                </button>
            </div>

            <!-- Summary Cards Section - Bento Style -->
            <section class="grid grid-cols-1 md:grid-cols-3 gap-lg">
                <div class="bg-surface-container-lowest border border-outline-variant p-lg rounded-xl shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
                    <div class="flex items-center justify-between mb-md">
                        <div class="p-sm bg-secondary-fixed text-on-secondary-fixed-variant rounded-lg">
                            <span class="material-symbols-outlined">payments</span>
                        </div>
                    </div>
                    <p class="text-on-surface-variant font-label-md text-label-md">Total Nilai PO</p>
                    <h3 class="font-display-lg text-display-lg text-primary mt-xs" id="totalValue">-</h3>
                </div>
                <div class="bg-surface-container-lowest border border-outline-variant p-lg rounded-xl shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
                    <div class="flex items-center justify-between mb-md">
                        <div class="p-sm bg-primary-fixed text-primary rounded-lg">
                            <span class="material-symbols-outlined">description</span>
                        </div>
                    </div>
                    <p class="text-on-surface-variant font-label-md text-label-md">Jumlah PO Diterbitkan</p>
                    <h3 class="font-display-lg text-display-lg text-primary mt-xs" id="totalPO">-</h3>
                </div>
                <div class="bg-surface-container-lowest border border-outline-variant p-lg rounded-xl shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
                    <div class="flex items-center justify-between mb-md">
                        <div class="p-sm bg-surface-container-highest text-on-surface-variant rounded-lg">
                            <span class="material-symbols-outlined">badge</span>
                        </div>
                    </div>
                    <p class="text-on-surface-variant font-label-md text-label-md">Vendor Aktif</p>
                    <h3 class="font-display-lg text-display-lg text-primary mt-xs" id="activeVendors">-</h3>
                </div>
            </section>

            <!-- Filtering Section -->
            <section class="bg-surface-container-lowest border border-outline-variant p-md rounded-xl">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-md items-end">
                    <div class="space-y-xs">
                        <label class="font-label-sm text-label-sm text-on-surface-variant ml-1">Tanggal Mulai</label>
                        <input class="w-full bg-surface border border-outline-variant rounded-lg px-md py-sm text-body-md font-body-md focus:ring-1 focus:ring-secondary focus:border-secondary outline-none" type="date" id="startDate">
                    </div>
                    <div class="space-y-xs">
                        <label class="font-label-sm text-label-sm text-on-surface-variant ml-1">Tanggal Akhir</label>
                        <input class="w-full bg-surface border border-outline-variant rounded-lg px-md py-sm text-body-md font-body-md focus:ring-1 focus:ring-secondary focus:border-secondary outline-none" type="date" id="endDate">
                    </div>
                    <div class="space-y-xs">
                        <label class="font-label-sm text-label-sm text-on-surface-variant ml-1">Proyek</label>
                        <select class="w-full bg-surface border border-outline-variant rounded-lg px-md py-sm text-body-md font-body-md focus:ring-1 focus:ring-secondary focus:border-secondary outline-none appearance-none" id="projectFilter">
                            <option value="">Semua Proyek</option>
                            <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['code'] . ' - ' . $project['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex gap-sm">
                        <div class="space-y-xs flex-1">
                            <label class="font-label-sm text-label-sm text-on-surface-variant ml-1">Status</label>
                            <select class="w-full bg-surface border border-outline-variant rounded-lg px-md py-sm text-body-md font-body-md focus:ring-1 focus:ring-secondary focus:border-secondary outline-none appearance-none" id="statusFilter">
                                <option value="">Semua Status</option>
                                <option value="Draft">Draft</option>
                                <option value="Printed">Printed</option>
                                <option value="Signed">Signed</option>
                                <option value="Invoiced">Invoiced</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                        <button class="bg-secondary text-on-secondary px-md py-sm rounded-lg font-label-md text-label-md hover:opacity-90 transition-all self-end" onclick="applyFilters()">
                            Filter
                        </button>
                    </div>
                </div>
            </section>

            <!-- Data Table Section -->
            <section class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-sm">
                <div class="p-md border-b border-outline-variant flex justify-between items-center">
                    <h4 class="font-label-md text-label-md text-primary uppercase tracking-wider">Daftar Purchase Order</h4>
                    <span class="text-body-sm font-body-sm text-on-surface-variant" id="tableInfo">Memuat data...</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-surface-container-low">
                            <tr>
                                <th class="px-md py-sm text-left font-label-sm text-label-sm text-on-surface-variant uppercase">PO Number</th>
                                <th class="px-md py-sm text-left font-label-sm text-label-sm text-on-surface-variant uppercase">Tanggal</th>
                                <th class="px-md py-sm text-left font-label-sm text-label-sm text-on-surface-variant uppercase">Project</th>
                                <th class="px-md py-sm text-left font-label-sm text-label-sm text-on-surface-variant uppercase">Vendor</th>
                                <th class="px-md py-sm text-right font-label-sm text-label-sm text-on-surface-variant uppercase">Subtotal</th>
                                <th class="px-md py-sm text-right font-label-sm text-label-sm text-on-surface-variant uppercase">PPN</th>
                                <th class="px-md py-sm text-right font-label-sm text-label-sm text-on-surface-variant uppercase">Grand Total</th>
                                <th class="px-md py-sm text-center font-label-sm text-label-sm text-on-surface-variant uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30" id="reportTableBody">
                            <tr>
                                <td colspan="8" class="px-md py-8 text-center text-on-surface-variant">
                                    <span class="material-symbols-outlined animate-spin text-secondary">refresh</span>
                                    <p class="mt-2">Memuat data laporan...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="p-md bg-surface-container-low border-t border-outline-variant flex justify-between items-center">
                    <p class="text-label-sm text-on-surface-variant" id="paginationInfo">-</p>
                    <div class="flex gap-xs" id="paginationControls"></div>
                </div>
            </section>
        </div>
    </main>

    <script>
        let currentPage = 1;
        let allReportData = [];
        let currentFilters = {};

        document.addEventListener('DOMContentLoaded', function() {
            // Set default date range: first day of current month to today
            const now = new Date();
            const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
            document.getElementById('startDate').value = firstDay.toISOString().split('T')[0];
            document.getElementById('endDate').value = now.toISOString().split('T')[0];
            
            loadReportData();
        });

        function applyFilters() {
            currentPage = 1;
            currentFilters = {
                start_date: document.getElementById('startDate').value,
                end_date: document.getElementById('endDate').value,
                project_id: document.getElementById('projectFilter').value,
                status: document.getElementById('statusFilter').value
            };
            loadReportData();
        }

        async function loadReportData() {
            try {
                let url = 'api/reports.php?';
                if (currentFilters.start_date) url += 'start_date=' + currentFilters.start_date + '&';
                if (currentFilters.end_date) url += 'end_date=' + currentFilters.end_date + '&';
                if (currentFilters.project_id) url += 'project_id=' + currentFilters.project_id + '&';
                if (currentFilters.status) url += 'status=' + currentFilters.status;

                const response = await fetch(url);
                const result = await response.json();

                if (result.success) {
                    const summary = result.data.summary;
                    const details = result.data.details;
                    allReportData = details;

                    // Update summary cards
                    document.getElementById('totalValue').textContent = 'Rp ' + Number(summary.total_value || 0).toLocaleString('id-ID');
                    document.getElementById('totalPO').textContent = Number(summary.total_po || 0).toLocaleString();
                    document.getElementById('activeVendors').textContent = Number(summary.active_vendors || 0).toLocaleString();

                    // Render table
                    renderReportTable(details);
                }
            } catch (error) {
                console.error('Error loading report data:', error);
            }
        }

        function renderReportTable(data) {
            const tbody = document.getElementById('reportTableBody');
            if (!tbody) return;

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="px-md py-8 text-center text-on-surface-variant">Tidak ada data untuk rentang tanggal ini</td></tr>';
                document.getElementById('tableInfo').textContent = '0 data';
                document.getElementById('paginationInfo').textContent = '0 data ditemukan';
                return;
            }

            const statusColors = {
                'Draft': 'bg-surface-container-highest text-on-surface-variant',
                'Printed': 'bg-primary-fixed text-on-primary-fixed-variant',
                'Signed': 'bg-secondary-fixed text-on-secondary-fixed-variant',
                'Invoiced': 'bg-tertiary-fixed text-on-tertiary-fixed-variant',
                'Completed': 'bg-on-tertiary-container/10 text-on-tertiary-container'
            };

            const itemsPerPage = 10;
            const totalPages = Math.ceil(data.length / itemsPerPage);
            const startIdx = (currentPage - 1) * itemsPerPage;
            const pageData = data.slice(startIdx, startIdx + itemsPerPage);

            tbody.innerHTML = pageData.map(po => `
                <tr class="table-row-hover transition-colors">
                    <td class="px-md py-md font-data-tabular text-data-tabular text-primary font-medium">${escapeHtml(po.po_number)}</td>
                    <td class="px-md py-md font-data-tabular text-data-tabular text-on-surface-variant">${formatDate(po.created_at)}</td>
                    <td class="px-md py-md font-body-md text-body-md text-on-surface-variant">${escapeHtml(po.project_name || 'N/A')}</td>
                    <td class="px-md py-md font-body-md text-body-md text-on-surface-variant">${escapeHtml(po.vendor_name || 'N/A')}</td>
                    <td class="px-md py-md font-data-tabular text-data-tabular text-right text-on-surface">${formatCurrency(po.subtotal)}</td>
                    <td class="px-md py-md font-data-tabular text-data-tabular text-right text-on-surface-variant">${po.ppn_amount > 0 ? formatCurrency(po.ppn_amount) : '-'}</td>
                    <td class="px-md py-md font-data-tabular text-data-tabular text-right text-primary font-bold">${formatCurrency(po.grand_total)}</td>
                    <td class="px-md py-md text-center">
                        <span class="inline-flex px-sm py-1 rounded-full ${statusColors[po.status] || 'bg-gray-100 text-gray-800'} font-label-sm text-[10px] uppercase">${po.status}</span>
                    </td>
                </tr>
            `).join('');

            // Update info
            document.getElementById('tableInfo').textContent = `Menampilkan ${startIdx + 1}-${Math.min(startIdx + itemsPerPage, data.length)} dari ${data.length} data`;
            document.getElementById('paginationInfo').textContent = `${data.length} data ditemukan`;

            // Render pagination
            renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
            const container = document.getElementById('paginationControls');
            if (!container || totalPages <= 1) {
                if (container) container.innerHTML = '';
                return;
            }

            let html = `<button class="px-md py-xs rounded font-label-md text-label-md ${currentPage === 1 ? 'opacity-50' : 'hover:bg-surface-container'} border border-outline-variant" ${currentPage === 1 ? 'disabled' : ''} onclick="goToPage(${currentPage - 1})">Prev</button>`;

            for (let i = 1; i <= Math.min(totalPages, 5); i++) {
                html += `<button class="px-md py-xs rounded font-label-md text-label-md ${i === currentPage ? 'bg-secondary text-on-secondary' : 'hover:bg-surface-container border border-outline-variant'}" onclick="goToPage(${i})">${i}</button>`;
            }

            if (totalPages > 5) {
                html += `<span class="px-xs">...</span>`;
                html += `<button class="px-md py-xs rounded font-label-md text-label-md ${currentPage === totalPages ? 'bg-secondary text-on-secondary' : 'hover:bg-surface-container border border-outline-variant'}" onclick="goToPage(${totalPages})">${totalPages}</button>`;
            }

            html += `<button class="px-md py-xs rounded font-label-md text-label-md ${currentPage === totalPages ? 'opacity-50' : 'hover:bg-surface-container'} border border-outline-variant" ${currentPage === totalPages ? 'disabled' : ''} onclick="goToPage(${currentPage + 1})">Next</button>`;

            container.innerHTML = html;
        }

        function goToPage(page) {
            currentPage = page;
            renderReportTable(allReportData);
        }

        function exportExcel() {
            if (allReportData.length === 0) {
                alert('Tidak ada data untuk diekspor');
                return;
            }

            // Prepare data for Excel
            const excelData = allReportData.map(po => ({
                'PO Number': po.po_number,
                'Tanggal': formatDate(po.created_at),
                'Project': po.project_name || 'N/A',
                'Kode Project': po.project_code || 'N/A',
                'Vendor': po.vendor_name || 'N/A',
                'Subtotal': po.subtotal,
                'PPN (%)': po.ppn_percent,
                'PPN Amount': po.ppn_amount,
                'Grand Total': po.grand_total,
                'Status': po.status,
                'Notes': po.notes || ''
            }));

            // Create workbook
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);

            // Set column widths
            ws['!cols'] = [
                { wch: 20 },  // PO Number
                { wch: 15 },  // Date
                { wch: 30 },  // Project
                { wch: 15 },  // Project Code
                { wch: 30 },  // Vendor
                { wch: 18 },  // Subtotal
                { wch: 10 },  // PPN %
                { wch: 18 },  // PPN Amount
                { wch: 18 },  // Grand Total
                { wch: 12 },  // Status
                { wch: 40 }   // Notes
            ];

            XLSX.utils.book_append_sheet(wb, ws, 'Laporan PO');

            // Add summary sheet
            const summaryData = [
                { 'Metric': 'Total PO', 'Value': allReportData.length },
                { 'Metric': 'Total Nilai', 'Value': allReportData.reduce((sum, po) => sum + parseFloat(po.grand_total || 0), 0) },
                { 'Metric': 'Total PPN', 'Value': allReportData.reduce((sum, po) => sum + parseFloat(po.ppn_amount || 0), 0) },
                { 'Metric': 'PO Draft', 'Value': allReportData.filter(po => po.status === 'Draft').length },
                { 'Metric': 'PO Signed', 'Value': allReportData.filter(po => po.status === 'Signed').length },
                { 'Metric': 'PO Completed', 'Value': allReportData.filter(po => po.status === 'Completed').length }
            ];
            const wsSummary = XLSX.utils.json_to_sheet(summaryData);
            XLSX.utils.book_append_sheet(wb, wsSummary, 'Ringkasan');

            // Generate and download
            const fileName = `Laporan_PO_${new Date().toISOString().split('T')[0]}.xlsx`;
            XLSX.writeFile(wb, fileName);
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
