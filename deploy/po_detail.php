<?php
session_start();
require_once 'api/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'user';

$poId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'detail-po';

if ($poId <= 0) {
    header('Location: po_list.php');
    exit();
}

// Fetch PO data
$conn = getConnection();
$stmt = $conn->prepare("SELECT po.*, v.name as vendor_name, v.address as vendor_address, v.npwp as vendor_npwp, v.contact_person, v.phone, v.email as vendor_email,
                               p.name as project_name, p.code as project_code, p.location as project_location, p.client as project_client,
                               u.name as created_by_name
                        FROM purchase_orders po 
                        LEFT JOIN vendors v ON po.vendor_id = v.id 
                        LEFT JOIN projects p ON po.project_id = p.id 
                        LEFT JOIN users u ON po.created_by = u.id
                        WHERE po.id = ? LIMIT 1");
$stmt->bind_param('i', $poId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: po_list.php');
    exit();
}

$po = $result->fetch_assoc();

// Get items
$itemsStmt = $conn->prepare("SELECT * FROM po_items WHERE po_id = ? ORDER BY sort_order ASC, id ASC");
$itemsStmt->bind_param('i', $poId);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();
$po['items'] = [];
while ($item = $itemsResult->fetch_assoc()) {
    $po['items'][] = $item;
}

// Get attachments
$attStmt = $conn->prepare("SELECT * FROM po_attachments WHERE po_id = ?");
$attStmt->bind_param('i', $poId);
$attStmt->execute();
$attResult = $attStmt->get_result();
$po['attachments'] = [];
while ($att = $attResult->fetch_assoc()) {
    $po['attachments'][] = $att;
}

// Get activity log (table is created on demand)
$po['activity'] = getPoActivity($conn, $poId);
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title><?php echo htmlspecialchars($po['po_number']); ?> | ProcureCorp Enterprise</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-surface": "#1b1b1d",
                        "background": "#fbf8fa",
                        "on-primary-fixed-variant": "#3c475a",
                        "on-error": "#ffffff",
                        "primary": "#091426",
                        "tertiary-container": "#00301e",
                        "surface-container-low": "#f5f3f4",
                        "tertiary-fixed": "#6ffbbe",
                        "surface-container-lowest": "#ffffff",
                        "on-primary": "#ffffff",
                        "on-tertiary-fixed-variant": "#005236",
                        "on-surface-variant": "#45474c",
                        "on-secondary-fixed": "#001a42",
                        "primary-fixed-dim": "#bcc7de",
                        "inverse-surface": "#303032",
                        "surface": "#fbf8fa",
                        "on-secondary-fixed-variant": "#004395",
                        "on-secondary": "#ffffff",
                        "secondary-fixed-dim": "#adc6ff",
                        "inverse-primary": "#bcc7de",
                        "surface-tint": "#545f73",
                        "on-background": "#1b1b1d",
                        "outline": "#75777d",
                        "secondary-fixed": "#d8e2ff",
                        "on-primary-fixed": "#111c2d",
                        "error-container": "#ffdad6",
                        "surface-variant": "#e4e2e3",
                        "on-secondary-container": "#fefcff",
                        "outline-variant": "#c5c6cd",
                        "on-tertiary-container": "#00a472",
                        "on-error-container": "#93000a",
                        "secondary": "#0058be",
                        "primary-fixed": "#d8e3fb",
                        "on-tertiary": "#ffffff",
                        "inverse-on-surface": "#f3f0f2",
                        "surface-container-highest": "#e4e2e3",
                        "error": "#ba1a1a",
                        "tertiary-fixed-dim": "#4edea3",
                        "secondary-container": "#2170e4",
                        "surface-dim": "#dcd9db",
                        "surface-bright": "#fbf8fa",
                        "primary-container": "#1e293b",
                        "on-primary-container": "#8590a6",
                        "surface-container": "#f0edef",
                        "tertiary": "#00190e",
                        "surface-container-high": "#eae7e9",
                        "on-tertiary-fixed": "#002113"
                    },
                    borderRadius: {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    spacing: {
                        "unit": "4px",
                        "container-margin": "24px",
                        "sm": "8px",
                        "md": "16px",
                        "sidebar-width": "260px",
                        "lg": "24px",
                        "gutter": "16px",
                        "xl": "32px",
                        "xs": "4px"
                    },
                    fontFamily: {
                        "label-sm": ["Inter"],
                        "headline-md": ["Inter"],
                        "body-lg": ["Inter"],
                        "data-tabular": ["Inter"],
                        "label-md": ["Inter"],
                        "body-md": ["Inter"],
                        "display-lg": ["Inter"],
                        "body-sm": ["Inter"],
                        "headline-sm": ["Inter"]
                    },
                    fontSize: {
                        "label-sm": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}],
                        "headline-md": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                        "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "data-tabular": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                        "label-md": ["14px", {"lineHeight": "20px", "fontWeight": "600"}],
                        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "display-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "body-sm": ["12px", {"lineHeight": "16px", "fontWeight": "400"}],
                        "headline-sm": ["20px", {"lineHeight": "28px", "fontWeight": "600"}]
                    }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
        .active-tab-indicator { height: 2px; bottom: -1px; }
        .sidebar-active-gradient { background: linear-gradient(90deg, rgba(33, 112, 228, 0.1) 0%, rgba(33, 112, 228, 0) 100%); }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="bg-background font-body-md text-on-surface custom-scrollbar">
    <!-- Side Navigation Bar -->
    <aside class="fixed h-screen w-sidebar-width left-0 top-0 bg-primary flex flex-col z-50">
        <div class="px-lg py-xl flex items-center gap-md">
            <div class="w-8 h-8 bg-secondary-container rounded flex items-center justify-center">
                <span class="material-symbols-outlined text-white" style="font-variation-settings: 'FILL' 1;">package_2</span>
            </div>
            <div>
                <h1 class="text-headline-sm font-headline-sm text-on-primary">ProcureCorp</h1>
                <p class="text-label-sm font-label-sm text-on-primary-container">Enterprise Suite</p>
            </div>
        </div>
        <nav class="flex-1 px-sm space-y-xs overflow-y-auto custom-scrollbar">
            <a class="flex items-center gap-md px-md py-sm rounded transition-colors duration-200 text-on-primary-container hover:bg-primary-container hover:text-on-primary" href="dashboard.php">
                <span class="material-symbols-outlined" data-icon="dashboard">dashboard</span>
                <span class="font-label-md text-label-md">Dashboard</span>
            </a>
            <a class="flex items-center gap-md px-md py-sm rounded transition-colors duration-200 text-on-primary-container hover:bg-primary-container hover:text-on-primary" href="vendors.php">
                <span class="material-symbols-outlined" data-icon="database">database</span>
                <span class="font-label-md text-label-md">Master Data</span>
            </a>
            <a class="flex items-center gap-md px-md py-sm rounded transition-colors duration-200 bg-secondary-container text-on-secondary-container border-l-4 border-secondary-fixed scale-[0.98] transition-transform" href="po_list.php">
                <span class="material-symbols-outlined" data-icon="shopping_cart" style="font-variation-settings: 'FILL' 1;">shopping_cart</span>
                <span class="font-label-md text-label-md">Purchase Orders</span>
            </a>
            <a class="flex items-center gap-md px-md py-sm rounded transition-colors duration-200 text-on-primary-container hover:bg-primary-container hover:text-on-primary" href="reports.php">
                <span class="material-symbols-outlined" data-icon="analytics">analytics</span>
                <span class="font-label-md text-label-md">Reports</span>
            </a>
            <a class="flex items-center gap-md px-md py-sm rounded transition-colors duration-200 text-on-primary-container hover:bg-primary-container hover:text-on-primary" href="settings.php">
                <span class="material-symbols-outlined" data-icon="settings">settings</span>
                <span class="font-label-md text-label-md">Settings</span>
            </a>
        </nav>
        <div class="p-lg border-t border-primary-container">
            <div class="flex items-center gap-md">
                <div class="w-10 h-10 shrink-0 rounded-full border-2 border-primary-container bg-secondary-container text-on-secondary-container flex items-center justify-center font-label-md font-bold" title="<?php echo htmlspecialchars($userName); ?>"><?php echo htmlspecialchars(userInitials($userName)); ?></div>
                <div class="overflow-hidden">
                    <p class="text-label-md font-label-md text-on-primary truncate"><?php echo htmlspecialchars($userName); ?></p>
                    <p class="text-body-sm font-body-sm text-on-primary-container truncate"><?php echo htmlspecialchars($userRole); ?></p>
                </div>
            </div>
            <a href="api/auth.php?action=logout" class="mt-2 flex items-center gap-sm text-on-primary-container hover:text-error text-body-sm transition-colors">
                <span class="material-symbols-outlined text-[16px]">logout</span>
                Logout
            </a>
        </div>
    </aside>

    <!-- Top Navigation Bar -->
    <header class="flex justify-between items-center h-16 px-container-margin ml-sidebar-width bg-surface sticky top-0 z-40 border-b border-outline-variant">
        <div class="flex items-center gap-lg">
            <h2 class="font-headline-sm text-headline-sm text-primary">Procurement Management</h2>
            <div class="h-6 w-px bg-outline-variant"></div>
            <div class="flex items-center bg-surface-container-low px-md py-sm rounded-lg border border-outline-variant w-96">
                <span class="material-symbols-outlined text-outline" data-icon="search">search</span>
                <input class="bg-transparent border-none focus:ring-0 text-body-md w-full ml-sm outline-none" placeholder="Search POs, Vendors, or Files..." type="text">
            </div>
        </div>
        <div class="flex items-center gap-md">
            <button class="w-10 h-10 flex items-center justify-center rounded-full text-on-surface-variant hover:text-secondary transition-all">
                <span class="material-symbols-outlined" data-icon="notifications">notifications</span>
            </button>
            <button class="w-10 h-10 flex items-center justify-center rounded-full text-on-surface-variant hover:text-secondary transition-all">
                <span class="material-symbols-outlined" data-icon="help">help</span>
            </button>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="ml-sidebar-width p-container-margin min-h-screen">
        <!-- PO Header Section -->
        <section class="bg-surface-container-lowest p-lg rounded-xl border border-outline-variant mb-lg shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-xl">
                <div class="flex flex-col">
                    <div class="flex items-center gap-md mb-xs">
                        <span class="text-label-sm font-label-sm text-on-surface-variant tracking-wider">PURCHASE ORDER</span>
                        <span class="bg-tertiary-container/10 text-on-tertiary-container px-sm py-xs rounded-full text-[10px] font-bold border border-tertiary-container/20 uppercase tracking-tighter"><?php echo htmlspecialchars($po['status']); ?></span>
                    </div>
                    <h3 class="font-display-lg text-display-lg text-primary tracking-tight"><?php echo htmlspecialchars($po['po_number']); ?></h3>
                </div>
                <div class="flex gap-lg border-l border-outline-variant pl-xl">
                    <div>
                        <p class="text-label-sm text-on-surface-variant">Vendor Name</p>
                        <p class="font-label-md text-primary"><?php echo htmlspecialchars($po['vendor_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-label-sm text-on-surface-variant">Creation Date</p>
                        <p class="font-label-md text-primary"><?php echo formatDate($po['created_at']); ?></p>
                    </div>
                    <div>
                        <p class="text-label-sm text-on-surface-variant">Total Amount</p>
                        <p class="font-label-md text-primary"><?php echo formatCurrency($po['grand_total']); ?></p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-md">
                <div class="flex items-center gap-sm">
                    <select id="po-status" class="border border-outline-variant rounded-lg bg-white text-label-md py-sm px-md focus:ring-secondary/20 outline-none" title="Ubah status PO">
                        <?php foreach (['Draft', 'Printed', 'Signed', 'Invoiced', 'Completed'] as $statusOption): ?>
                        <option value="<?php echo $statusOption; ?>" <?php echo $po['status'] === $statusOption ? 'selected' : ''; ?>><?php echo $statusOption; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="flex items-center gap-sm px-md py-sm bg-surface text-primary border border-outline-variant rounded-lg font-label-md hover:bg-surface-container transition-all" onclick="updatePOStatus()">
                        <span class="material-symbols-outlined text-[18px]" data-icon="swap_horiz">swap_horiz</span>
                        Simpan Status
                    </button>
                </div>
                <a href="pratinjau_cetak_po_pdf.php?id=<?php echo $po['id']; ?>" target="_blank" class="flex items-center gap-sm px-md py-sm bg-white border border-primary text-primary rounded-lg font-label-md hover:bg-surface-container transition-all">
                    <span class="material-symbols-outlined text-[18px]" data-icon="print">print</span>
                    Print PDF
                </a>
                <a href="po_create.php?id=<?php echo $po['id']; ?>" class="flex items-center gap-sm px-xl py-sm bg-secondary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-md">
                    <span class="material-symbols-outlined text-[18px]" data-icon="edit">edit</span>
                    Edit PO
                </a>
            </div>
        </section>

        <!-- Main Content Tabs -->
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <nav class="flex border-b border-outline-variant px-lg">
                <button class="px-xl py-md font-label-md <?php echo $activeTab === 'detail-po' ? 'text-secondary' : 'text-on-surface-variant'; ?> hover:text-primary transition-all relative" onclick="switchTab('detail-po')">
                    Detail PO
                    <div class="absolute active-tab-indicator left-0 right-0 <?php echo $activeTab === 'detail-po' ? '' : 'hidden'; ?> bg-secondary" id="indicator-detail-po"></div>
                </button>
                <button class="px-xl py-md font-label-md <?php echo $activeTab === 'dokumen' ? 'text-secondary' : 'text-on-surface-variant'; ?> hover:text-primary transition-all relative" onclick="switchTab('dokumen')">
                    Dokumen & Lampiran
                    <div class="absolute active-tab-indicator left-0 right-0 <?php echo $activeTab === 'dokumen' ? '' : 'hidden'; ?> bg-secondary" id="indicator-dokumen"></div>
                </button>
            </nav>

            <!-- Tab Content: Dokumen & Lampiran -->
            <div class="p-lg <?php echo $activeTab === 'dokumen' ? '' : 'hidden'; ?>" id="content-dokumen">
                <div class="grid grid-cols-12 gap-lg">
                    <!-- Left Column: Document Uploads -->
                    <div class="col-span-12 lg:col-span-8 space-y-lg">
                        <?php
                        $uploads = [
                            ['type' => 'invoice_supplier', 'title' => 'Invoice Supplier', 'desc' => 'Upload the final invoice provided by the vendor.', 'icon' => 'receipt_long', 'required' => false],
                            ['type' => 'quotation', 'title' => 'Dokumen Penawaran (Quotation)', 'desc' => 'Original quotation or proposal provided by the vendor.', 'icon' => 'file_present', 'required' => false],
                            ['type' => 'wet_signature', 'title' => 'PO yang sudah di-TTD Basah', 'desc' => 'Scanned copy of the physical signed and stamped PO document.', 'icon' => 'draw', 'required' => false],
                            ['type' => 'supporting', 'title' => 'Dokumen Pendukung Lainnya', 'desc' => 'Delivery notes, warranties, or technical specifications.', 'icon' => 'attachment', 'required' => false],
                        ];
                        
                        foreach ($uploads as $upload):
                            $attachment = null;
                            foreach ($po['attachments'] as $att) {
                                if ($att['type'] === $upload['type']) {
                                    $attachment = $att;
                                    break;
                                }
                            }
                        ?>
                        <div class="p-lg bg-surface border border-outline-variant rounded-xl group hover:border-secondary transition-colors">
                            <div class="flex items-start justify-between mb-md">
                                <div class="flex items-center gap-md">
                                    <div class="w-12 h-12 rounded-lg bg-surface-container-highest flex items-center justify-center text-primary">
                                        <span class="material-symbols-outlined text-[28px]" data-icon="<?php echo $upload['icon']; ?>"><?php echo $upload['icon']; ?></span>
                                    </div>
                                    <div>
                                        <h4 class="font-headline-sm text-headline-sm text-primary"><?php echo $upload['title']; ?></h4>
                                        <p class="text-body-sm text-on-surface-variant"><?php echo $upload['desc']; ?></p>
                                    </div>
                                </div>
                                <span class="bg-on-surface-variant/10 text-on-surface-variant px-sm py-xs rounded text-[10px] font-bold uppercase">Optional</span>
                            </div>
                            <div class="border-2 border-dashed border-outline-variant rounded-lg p-xl flex flex-col items-center justify-center bg-surface-container-low/30 hover:bg-surface-container-low transition-all cursor-pointer" onclick="document.getElementById('file-<?php echo $upload['type']; ?>').click()">
                                <?php if ($attachment): ?>
                                    <div class="text-center">
                                        <span class="material-symbols-outlined text-on-tertiary-container text-[40px] mb-sm">check_circle</span>
                                        <p class="text-body-md font-label-md text-primary"><?php echo htmlspecialchars($attachment['file_name']); ?></p>
                                        <p class="text-body-sm text-on-surface-variant">Uploaded on <?php echo formatDate($attachment['uploaded_at']); ?></p>
                                        <a href="<?php echo htmlspecialchars($attachment['file_path']); ?>" target="_blank" class="text-secondary font-label-sm hover:underline mt-2 inline-block">View File</a>
                                    </div>
                                <?php else: ?>
                                    <span class="material-symbols-outlined text-outline text-[40px] mb-sm" data-icon="cloud_upload">cloud_upload</span>
                                    <p class="text-body-md font-label-md text-primary">Click to upload or drag and drop</p>
                                    <p class="text-body-sm text-outline">PDF, JPG or PNG (max. 10MB)</p>
                                <?php endif; ?>
                                <input type="file" id="file-<?php echo $upload['type']; ?>" class="hidden" accept=".pdf,.jpg,.jpeg,.png" onchange="uploadFile(<?php echo $po['id']; ?>, '<?php echo $upload['type']; ?>', this)">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Right Column: Metadata & Status -->
                    <div class="col-span-12 lg:col-span-4 space-y-lg">
                        <!-- Quotation Checklist Card -->
                        <div class="p-lg bg-surface-container rounded-xl border border-outline-variant">
                            <h5 class="font-label-md text-label-md text-primary mb-md flex items-center gap-sm">
                                <span class="material-symbols-outlined text-on-tertiary-container" style="font-variation-settings: 'FILL' 1;">verified</span>
                                Validation Status
                            </h5>
                            <div class="space-y-md">
                                <div class="flex justify-between items-center p-sm bg-white rounded border border-outline-variant/30">
                                    <span class="text-body-md text-on-surface-variant">Quotation Checklist</span>
                                    <span class="flex items-center gap-xs text-on-tertiary-container font-bold text-label-sm">
                                        <span class="material-symbols-outlined text-[16px]" data-icon="check_circle" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                        <?php echo $po['quotation_attached'] ? 'Ya, Sudah Dilampirkan' : 'Belum Dilampirkan'; ?>
                                    </span>
                                </div>
                                <div class="flex justify-between items-center p-sm bg-white rounded border border-outline-variant/30">
                                    <span class="text-body-md text-on-surface-variant">Approval Status</span>
                                    <span class="flex items-center gap-xs text-on-tertiary-container font-bold text-label-sm">
                                        <span class="material-symbols-outlined text-[16px]" data-icon="check_circle" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                        <?php echo $po['approved'] ? 'Disetujui' : 'Belum Disetujui'; ?>
                                    </span>
                                </div>
                                <div class="flex justify-between items-center p-sm bg-white rounded border border-outline-variant/30">
                                    <span class="text-body-md text-on-surface-variant">PO Status</span>
                                    <span class="flex items-center gap-xs font-bold text-label-sm"><?php echo htmlspecialchars($po['status']); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- System Logs / History -->
                        <div class="p-lg bg-surface border border-outline-variant rounded-xl">
                            <h5 class="font-label-md text-label-md text-primary mb-md">Document Activity</h5>
                            <div class="space-y-lg relative before:content-[''] before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-[2px] before:bg-outline-variant">
                                <?php
                                $hasCreatedLog = false;
                                foreach ($po['activity'] as $activityLog) {
                                    if ($activityLog['action'] === 'created') { $hasCreatedLog = true; break; }
                                }
                                // Fallback for POs created before the activity log existed
                                if (!$hasCreatedLog): ?>
                                <div class="relative pl-xl">
                                    <div class="absolute left-0 top-1 w-[24px] h-[24px] bg-secondary-fixed rounded-full flex items-center justify-center border-4 border-surface">
                                        <div class="w-2 h-2 bg-secondary rounded-full"></div>
                                    </div>
                                    <p class="text-label-md text-primary">PO Dibuat</p>
                                    <p class="text-body-sm text-on-surface-variant"><?php echo formatDate($po['created_at']); ?></p>
                                </div>
                                <?php endif; ?>
                                <?php foreach ($po['activity'] as $activityLog): ?>
                                <div class="relative pl-xl">
                                    <div class="absolute left-0 top-1 w-[24px] h-[24px] bg-secondary-fixed rounded-full flex items-center justify-center border-4 border-surface">
                                        <div class="w-2 h-2 bg-secondary rounded-full"></div>
                                    </div>
                                    <p class="text-label-md text-primary"><?php echo htmlspecialchars($activityLog['description'] ?: ucwords(str_replace('_', ' ', $activityLog['action']))); ?></p>
                                    <p class="text-body-sm text-on-surface-variant"><?php echo htmlspecialchars($activityLog['user_name'] ?? 'System'); ?> • <?php echo formatDate($activityLog['created_at']); ?></p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Info Alert -->
                        <div class="p-md bg-secondary-container/10 border border-secondary/20 rounded-lg flex gap-md">
                            <span class="material-symbols-outlined text-secondary" data-icon="info">info</span>
                            <p class="text-body-sm text-secondary">Make sure all uploaded files are clearly legible. Unclear documents may result in payment delays.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Detail PO -->
            <div class="p-lg <?php echo $activeTab === 'detail-po' ? '' : 'hidden'; ?>" id="content-detail-po">
                <div class="grid grid-cols-2 gap-xl mb-xl">
                    <div>
                        <h3 class="font-label-sm text-label-sm text-on-surface-variant uppercase mb-sm tracking-wider">Vendor</h3>
                        <div class="font-body-md text-body-md text-primary">
                            <p class="font-bold"><?php echo htmlspecialchars($po['vendor_name']); ?></p>
                            <p><?php echo htmlspecialchars($po['vendor_address']); ?></p>
                            <p>NPWP: <?php echo htmlspecialchars($po['vendor_npwp']); ?></p>
                            <p class="mt-xs">Attn: <?php echo htmlspecialchars($po['contact_person']); ?></p>
                            <p>Email: <?php echo htmlspecialchars($po['vendor_email']); ?></p>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-label-sm text-label-sm text-on-surface-variant uppercase mb-sm tracking-wider">Ship To</h3>
                        <div class="font-body-md text-body-md text-primary">
                            <p class="font-bold"><?php echo htmlspecialchars($po['project_name']); ?></p>
                            <p><?php echo htmlspecialchars($po['project_location']); ?></p>
                            <p><?php echo htmlspecialchars($po['project_client']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="flex-grow">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low border-b border-outline">
                                <th class="px-sm py-md font-label-sm text-label-sm text-on-surface-variant uppercase text-center w-12">No.</th>
                                <th class="px-md py-md font-label-sm text-label-sm text-on-surface-variant uppercase">Description</th>
                                <th class="px-md py-md font-label-sm text-label-sm text-on-surface-variant uppercase text-right">Qty</th>
                                <th class="px-md py-md font-label-sm text-label-sm text-on-surface-variant uppercase text-center">Unit</th>
                                <th class="px-md py-md font-label-sm text-label-sm text-on-surface-variant uppercase text-right">Price (Rp)</th>
                                <th class="px-md py-md font-label-sm text-label-sm text-on-surface-variant uppercase text-right">Total (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="font-data-tabular text-data-tabular">
                            <?php foreach ($po['items'] as $index => $item): ?>
                            <tr class="border-b border-outline-variant">
                                <td class="px-sm py-md text-center"><?php echo $index + 1; ?></td>
                                <td class="px-md py-md font-semibold text-primary"><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td class="px-md py-md text-right"><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td class="px-md py-md text-center"><?php echo htmlspecialchars($item['unit']); ?></td>
                                <td class="px-md py-md text-right"><?php echo formatCurrency($item['price']); ?></td>
                                <td class="px-md py-md text-right"><?php echo formatCurrency($item['total']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Summary & Signature -->
                <div class="mt-xl grid grid-cols-2 gap-xl">
                    <div class="flex flex-col justify-end">
                        <div class="p-md bg-surface-container-low border border-outline-variant rounded-lg">
                            <h4 class="font-label-sm text-label-sm text-on-surface-variant uppercase mb-xs">Notes</h4>
                            <p class="font-body-sm text-body-sm text-on-surface-variant">
                                Delivery should be completed within 30 working days from the date of this Purchase Order. Please confirm receipt of this order and coordinate with the warehouse contact person.
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-sm">
                        <div class="flex justify-between items-center px-md py-xs">
                            <span class="font-label-md text-label-md text-on-surface-variant">Subtotal</span>
                            <span class="font-data-tabular text-data-tabular text-primary"><?php echo formatCurrency($po['subtotal']); ?></span>
                        </div>
                        <?php if ((float)$po['ppn_amount'] > 0): ?>
                        <div class="flex justify-between items-center px-md py-xs">
                            <span class="font-label-md text-label-md text-on-surface-variant">PPN (<?php echo (float)$po['ppn_percent']; ?>%)</span>
                            <span class="font-data-tabular text-data-tabular text-primary"><?php echo formatCurrency($po['ppn_amount']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between items-center px-md py-md bg-primary text-on-primary rounded-sm">
                            <span class="font-headline-sm text-headline-sm">Grand Total (Rp)</span>
                            <span class="font-headline-sm text-headline-sm"><?php echo formatCurrency($po['grand_total']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-xl flex justify-end gap-md">
            <a href="po_list.php" class="px-xl py-md border border-outline-variant bg-white text-primary rounded-lg font-label-md hover:bg-surface-container transition-all">Cancel Changes</a>
            <button class="px-xl py-md bg-primary text-white rounded-lg font-label-md hover:opacity-95 transition-all shadow-lg flex items-center gap-sm" onclick="saveAll()">
                <span class="material-symbols-outlined" data-icon="save">save</span>
                Save All Documents
            </button>
        </div>
    </main>

    <script>
        function switchTab(tabId) {
            const detailTab = document.getElementById('tab-detail-po');
            const dokTab = document.getElementById('tab-dokumen');
            const detailContent = document.getElementById('content-detail-po');
            const dokContent = document.getElementById('content-dokumen');
            const detailIndicator = document.getElementById('indicator-detail-po');
            const dokIndicator = document.getElementById('indicator-dokumen');

            if (tabId === 'detail-po') {
                detailTab.classList.add('text-secondary');
                detailTab.classList.remove('text-on-surface-variant');
                dokTab.classList.remove('text-secondary');
                dokTab.classList.add('text-on-surface-variant');
                detailContent.classList.remove('hidden');
                dokContent.classList.add('hidden');
                detailIndicator.classList.remove('hidden');
                dokIndicator.classList.add('hidden');
            } else {
                dokTab.classList.add('text-secondary');
                dokTab.classList.remove('text-on-surface-variant');
                detailTab.classList.remove('text-secondary');
                detailTab.classList.add('text-on-surface-variant');
                dokContent.classList.remove('hidden');
                detailContent.classList.add('hidden');
                dokIndicator.classList.remove('hidden');
                detailIndicator.classList.add('hidden');
            }
        }

        async function uploadFile(poId, type, input) {
            if (!input.files || !input.files[0]) return;

            const formData = new FormData();
            formData.append('po_id', poId);
            formData.append('type', type);
            formData.append('file', input.files[0]);

            try {
                const response = await fetch('api/uploads.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    alert('File uploaded successfully!');
                    location.reload();
                } else {
                    alert('Upload failed: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Upload failed');
            }
        }

        function saveAll() {
            alert('Documents saved successfully!');
        }

        async function updatePOStatus() {
            const poId = <?php echo (int)$po['id']; ?>;
            const status = document.getElementById('po-status').value;
            try {
                const response = await fetch('api/po.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'status', id: poId, status: status })
                });
                const result = await response.json();
                if (result.success) {
                    alert('Status PO diperbarui menjadi: ' + result.data.status);
                    location.reload();
                } else {
                    alert('Gagal memperbarui status: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal memperbarui status');
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
    </script>
</body>
</html>
