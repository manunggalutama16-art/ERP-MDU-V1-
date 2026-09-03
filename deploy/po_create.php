<?php
session_start();
require_once 'api/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'user';

// Fetch vendors and projects for dropdowns
$conn = getConnection();
$vendorsResult = $conn->query("SELECT id, name FROM vendors ORDER BY name ASC");
$vendors = [];
while ($row = $vendorsResult->fetch_assoc()) {
    $vendors[] = $row;
}

$projectsResult = $conn->query("SELECT id, code, name FROM projects ORDER BY name ASC");
$projects = [];
while ($row = $projectsResult->fetch_assoc()) {
    $projects[] = $row;
}

// Edit mode: load existing PO + its items when an id is provided
$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editPO = null;
$editItems = [];

if ($editId > 0) {
    $stmt = $conn->prepare("SELECT * FROM purchase_orders WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header('Location: po_list.php');
        exit();
    }

    $editPO = $result->fetch_assoc();

    $itemsStmt = $conn->prepare("SELECT * FROM po_items WHERE po_id = ? ORDER BY sort_order ASC, id ASC");
    $itemsStmt->bind_param('i', $editId);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    while ($row = $itemsResult->fetch_assoc()) {
        $editItems[] = $row;
    }
}

// Default units offered in the item row select
$defaultUnits = ['Pcs', 'Box', 'Lot', 'Roll'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title><?php echo $editPO ? 'Edit Purchase Order' : 'Buat PO Baru'; ?> | Nexus Procurement</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
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
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .sidebar-active { background-color: #2170e4; color: #fefcff; border-left: 4px solid #d8e2ff; }
    </style>
</head>
<body class="bg-background text-on-background font-body-md text-body-md overflow-x-hidden">
    <!-- SideNavBar -->
    <aside class="fixed h-screen w-sidebar-width left-0 top-0 bg-primary flex flex-col h-full overflow-y-auto z-50">
        <div class="p-lg flex items-center gap-md border-b border-primary-container">
            <div class="w-10 h-10 bg-secondary-container rounded flex items-center justify-center">
                <span class="material-symbols-outlined text-on-secondary-container" style="font-variation-settings: 'FILL' 1;">corporate_fare</span>
            </div>
            <div>
                <h1 class="font-headline-sm text-headline-sm text-on-primary">ProcureCorp</h1>
                <p class="font-label-sm text-label-sm text-on-primary-container opacity-80 uppercase tracking-wider">Enterprise Suite</p>
            </div>
        </div>
        <nav class="flex-1 py-md">
            <ul class="space-y-1">
                <li><a class="flex items-center gap-md px-lg py-md text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors duration-200" href="dashboard.php"><span class="material-symbols-outlined">dashboard</span><span class="font-label-md text-label-md">Dashboard</span></a></li>
                <li><a class="flex items-center gap-md px-lg py-md text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors duration-200" href="vendors.php"><span class="material-symbols-outlined">database</span><span class="font-label-md text-label-md">Master Data</span></a></li>
                <li><a class="sidebar-active flex items-center gap-md px-lg py-md bg-secondary-container text-on-secondary-container border-l-4 border-secondary-fixed transition-transform active:scale-[0.98]" href="po_list.php"><span class="material-symbols-outlined" data-icon="shopping_cart" style="font-variation-settings: 'FILL' 1;">shopping_cart</span><span class="font-label-md text-label-md">Purchase Orders</span></a></li>
                <li><a class="flex items-center gap-md px-lg py-md text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors duration-200" href="reports.php"><span class="material-symbols-outlined">analytics</span><span class="font-label-md text-label-md">Reports</span></a></li>
                <li><a class="flex items-center gap-md px-lg py-md text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors duration-200" href="settings.php"><span class="material-symbols-outlined">settings</span><span class="font-label-md text-label-md">Settings</span></a></li>
            </ul>
        </nav>
        <div class="p-lg mt-auto bg-primary-container/20 border-t border-primary-container">
            <div class="flex items-center gap-md">
                <div class="w-8 h-8 rounded-full overflow-hidden bg-outline">
                    <div class="w-full h-full flex items-center justify-center bg-secondary-container text-on-secondary-container font-label-md font-bold" title="<?php echo htmlspecialchars($userName); ?>"><?php echo htmlspecialchars(userInitials($userName)); ?></div>
                </div>
                <div class="flex-1 overflow-hidden">
                    <p class="text-on-primary font-label-md truncate"><?php echo htmlspecialchars($userName); ?></p>
                    <p class="text-on-primary-container text-[10px] uppercase font-bold opacity-60">Admin Procurement</p>
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
        <header class="h-16 px-container-margin sticky top-0 z-40 bg-surface border-b border-outline-variant flex justify-between items-center">
            <div class="flex items-center gap-xl">
                <h2 class="font-headline-sm text-headline-sm text-primary">Procurement Management</h2>
                <div class="relative w-80">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="search">search</span>
                    <input class="w-full pl-10 pr-4 py-2 bg-surface-container-low border border-outline-variant rounded-lg focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition-all" placeholder="Search POs, Vendors..." type="text"/>
                </div>
            </div>
            <div class="flex items-center gap-lg">
                <button class="relative p-2 text-on-surface-variant hover:text-secondary transition-all">
                    <span class="material-symbols-outlined" data-icon="notifications">notifications</span>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-error rounded-full border-2 border-surface"></span>
                </button>
                <button class="p-2 text-on-surface-variant hover:text-secondary transition-all">
                    <span class="material-symbols-outlined" data-icon="help">help</span>
                </button>
            </div>
        </header>

        <!-- Content Canvas -->
        <div class="p-lg flex-1">
            <div class="max-w-6xl mx-auto space-y-lg">
                <!-- Page Header -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-md">
                    <div>
                        <div class="flex items-center gap-sm text-secondary mb-1">
                            <span class="material-symbols-outlined text-[18px]"><?php echo $editPO ? 'edit' : 'add_circle'; ?></span>
                            <span class="font-label-sm uppercase tracking-widest"><?php echo $editPO ? 'Edit Procurement' : 'New Procurement'; ?></span>
                        </div>
                        <h3 class="font-display-lg text-display-lg text-primary"><?php echo $editPO ? 'Edit Purchase Order' : 'Create Purchase Order'; ?></h3>
                        <p class="text-on-surface-variant mt-1"><?php echo $editPO ? 'Update the vendor information and item details, then save your changes.' : 'Please fill out the vendor information and item details to generate a formal PO.'; ?></p>
                    </div>
                    <div class="flex gap-md">
                        <button class="px-lg py-md border border-primary text-primary font-label-md rounded hover:bg-surface-container transition-colors" onclick="saveDraft()">
                            <?php echo $editPO ? 'Simpan Perubahan' : 'Simpan Draft'; ?>
                        </button>
                        <button class="px-lg py-md bg-secondary text-on-secondary font-label-md rounded flex items-center gap-sm hover:opacity-90 transition-opacity shadow-lg shadow-secondary/20" onclick="saveAndPrint()">
                            <span class="material-symbols-outlined text-[20px]">print</span>
                            Simpan & Cetak PDF
                        </button>
                    </div>
                </div>

                <form class="space-y-lg" id="po-form">
                    <!-- Section 1: Basic Information -->
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
                        <div class="bg-surface-container px-lg py-md border-b border-outline-variant">
                            <h4 class="font-label-md text-label-md text-primary uppercase tracking-wider">General Information</h4>
                        </div>
                        <div class="p-lg grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-lg">
                            <!-- PO Number (Auto) -->
                            <div class="space-y-xs">
                                <label class="font-label-sm text-label-sm text-on-surface-variant block uppercase">PO Number (System Generated)</label>
                                <div class="space-y-xs">
                                    <input class="w-full px-md py-md bg-surface border border-outline-variant rounded font-data-tabular text-primary focus:border-secondary focus:ring-2 focus:ring-secondary/10 outline-none transition-all" id="po-number" type="text" value="<?php echo $editPO ? htmlspecialchars($editPO['po_number']) : ''; ?>" readonly>
                                    <p class="text-[10px] text-secondary flex items-center gap-xs">
                                        <span class="material-symbols-outlined text-[14px]">info</span>
                                        Nomor PO harus unik dalam sistem.
                                    </p>
                                </div>
                            </div>
                            <!-- Vendor Selection -->
                            <div class="space-y-xs">
                                <label class="font-label-sm text-label-sm text-on-surface-variant block uppercase" for="vendor">Pilih Vendor <span class="text-error">*</span></label>
                                <select class="w-full px-md py-md border border-outline-variant rounded bg-surface focus:border-secondary focus:ring-2 focus:ring-secondary/10 outline-none transition-all" id="vendor" required>
                                    <option disabled <?php echo (!$editPO || !$editPO['vendor_id']) ? 'selected' : ''; ?> value="">Pilih Vendor dari Master Data</option>
                                    <?php foreach ($vendors as $vendor): ?>
                                    <option value="<?php echo $vendor['id']; ?>" <?php echo ($editPO && (int)$editPO['vendor_id'] === (int)$vendor['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($vendor['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- Project Selection -->
                            <div class="space-y-xs">
                                <label class="font-label-sm text-label-sm text-on-surface-variant block uppercase" for="project">Pilih Project <span class="text-error">*</span></label>
                                <select class="w-full px-md py-md border border-outline-variant rounded bg-surface focus:border-secondary focus:ring-2 focus:ring-secondary/10 outline-none transition-all" id="project" required>
                                    <option disabled <?php echo (!$editPO || !$editPO['project_id']) ? 'selected' : ''; ?> value="">Pilih Project dari Master Data</option>
                                    <?php foreach ($projects as $project): ?>
                                    <option value="<?php echo $project['id']; ?>" <?php echo ($editPO && (int)$editPO['project_id'] === (int)$project['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($project['code'] . ' - ' . $project['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- TOP -->
                            <div class="space-y-xs">
                                <label class="font-label-sm text-label-sm text-on-surface-variant block uppercase" for="top">Term of Payment (TOP)</label>
                                <input class="w-full px-md py-md border border-outline-variant rounded bg-surface focus:border-secondary focus:ring-2 focus:ring-secondary/10 outline-none transition-all" id="top" placeholder="e.g. Net 30, COD" type="text" value="<?php echo $editPO ? htmlspecialchars($editPO['top']) : ''; ?>"/>
                            </div>
                            <!-- Delivery Location -->
                            <div class="space-y-xs lg:col-span-2">
                                <label class="font-label-sm text-label-sm text-on-surface-variant block uppercase" for="delivery">Delivery Location</label>
                                <input class="w-full px-md py-md border border-outline-variant rounded bg-surface focus:border-secondary focus:ring-2 focus:ring-secondary/10 outline-none transition-all" id="delivery" placeholder="Detailed shipping address..." type="text" value="<?php echo $editPO ? htmlspecialchars($editPO['delivery_location']) : ''; ?>"/>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Items Table -->
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
                        <div class="bg-surface-container px-lg py-md border-b border-outline-variant flex justify-between items-center">
                            <h4 class="font-label-md text-label-md text-primary uppercase tracking-wider">Purchase Items</h4>
                            <button class="flex items-center gap-xs text-secondary font-label-sm hover:underline" onclick="addRow()" type="button">
                                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                                ADD NEW ITEM
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse" id="items-table">
                                <thead class="bg-surface-container-low">
                                    <tr>
                                        <th class="px-md py-sm font-label-sm text-label-sm text-on-surface-variant border-b border-outline-variant w-12 text-center">#</th>
                                        <th class="px-md py-sm font-label-sm text-label-sm text-on-surface-variant border-b border-outline-variant">ITEM NAME</th>
                                        <th class="px-md py-sm font-label-sm text-label-sm text-on-surface-variant border-b border-outline-variant w-24">QTY</th>
                                        <th class="px-md py-sm font-label-sm text-label-sm text-on-surface-variant border-b border-outline-variant w-24">UNIT</th>
                                        <th class="px-md py-sm font-label-sm text-label-sm text-on-surface-variant border-b border-outline-variant text-right">UNIT PRICE</th>
                                        <th class="px-md py-sm font-label-sm text-label-sm text-on-surface-variant border-b border-outline-variant text-right">TOTAL</th>
                                        <th class="px-md py-sm font-label-sm text-label-sm text-on-surface-variant border-b border-outline-variant w-16"></th>
                                    </tr>
                                </thead>
                                <tbody id="table-body">
                                    <?php if ($editPO && !empty($editItems)): ?>
                                    <?php foreach ($editItems as $index => $item): ?>
                                    <tr class="group hover:bg-surface-container-low/50 transition-colors">
                                        <td class="px-md py-md border-b border-outline-variant text-center text-on-surface-variant font-data-tabular"><?php echo $index + 1; ?></td>
                                        <td class="px-md py-md border-b border-outline-variant">
                                            <input class="w-full bg-transparent border-none p-0 focus:ring-0 text-primary font-body-md" placeholder="Item description" type="text" value="<?php echo htmlspecialchars($item['item_name']); ?>"/>
                                        </td>
                                        <td class="px-md py-md border-b border-outline-variant">
                                            <input class="qty-input w-full bg-transparent border-none p-0 focus:ring-0 text-primary font-data-tabular" min="1" onchange="calculateTotal()" type="number" value="<?php echo htmlspecialchars($item['quantity']); ?>"/>
                                        </td>
                                        <td class="px-md py-md border-b border-outline-variant">
                                            <select class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant font-body-md appearance-none">
                                                <?php $units = in_array($item['unit'], $defaultUnits) ? $defaultUnits : array_merge([$item['unit']], $defaultUnits); ?>
                                                <?php foreach ($units as $unit): ?>
                                                <option <?php echo $unit === $item['unit'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($unit); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="px-md py-md border-b border-outline-variant text-right">
                                            <div class="flex items-center justify-end">
                                                <span class="text-on-surface-variant mr-1">Rp</span>
                                                <input class="price-input w-32 bg-transparent border-none p-0 focus:ring-0 text-primary text-right font-data-tabular" min="0" onchange="calculateTotal()" type="number" value="<?php echo htmlspecialchars($item['price']); ?>"/>
                                            </div>
                                        </td>
                                        <td class="px-md py-md border-b border-outline-variant text-right font-data-tabular text-primary">
                                            Rp <span class="row-total">0</span>
                                        </td>
                                        <td class="px-md py-md border-b border-outline-variant text-center">
                                            <button class="text-on-surface-variant opacity-0 group-hover:opacity-100 hover:text-error transition-all" onclick="removeRow(this)" type="button">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <!-- Row Template -->
                                    <tr class="group hover:bg-surface-container-low/50 transition-colors">
                                        <td class="px-md py-md border-b border-outline-variant text-center text-on-surface-variant font-data-tabular">1</td>
                                        <td class="px-md py-md border-b border-outline-variant">
                                            <input class="w-full bg-transparent border-none p-0 focus:ring-0 text-primary font-body-md" placeholder="Item description" type="text"/>
                                        </td>
                                        <td class="px-md py-md border-b border-outline-variant">
                                            <input class="qty-input w-full bg-transparent border-none p-0 focus:ring-0 text-primary font-data-tabular" min="1" onchange="calculateTotal()" type="number" value="1"/>
                                        </td>
                                        <td class="px-md py-md border-b border-outline-variant">
                                            <select class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant font-body-md appearance-none">
                                                <option>Pcs</option>
                                                <option>Box</option>
                                                <option>Lot</option>
                                                <option>Roll</option>
                                            </select>
                                        </td>
                                        <td class="px-md py-md border-b border-outline-variant text-right">
                                            <div class="flex items-center justify-end">
                                                <span class="text-on-surface-variant mr-1">Rp</span>
                                                <input class="price-input w-32 bg-transparent border-none p-0 focus:ring-0 text-primary text-right font-data-tabular" min="0" onchange="calculateTotal()" type="number" value="0"/>
                                            </div>
                                        </td>
                                        <td class="px-md py-md border-b border-outline-variant text-right font-data-tabular text-primary">
                                            Rp <span class="row-total">0</span>
                                        </td>
                                        <td class="px-md py-md border-b border-outline-variant text-center">
                                            <button class="text-on-surface-variant opacity-0 group-hover:opacity-100 hover:text-error transition-all" onclick="removeRow(this)" type="button">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="border-t border-outline-variant">
                                        <td class="px-lg py-md text-right" colspan="5">
                                            <div class="flex items-center justify-end gap-lg">
                                                <span class="font-label-sm text-on-surface-variant uppercase">Pajak (PPN 11%)</span>
                                                <div class="flex items-center gap-md">
                                                    <label class="flex items-center gap-sm cursor-pointer group">
                                                        <input <?php echo ($editPO && (float)$editPO['ppn_amount'] > 0) ? '' : 'checked'; ?> class="w-4 h-4 text-secondary border-outline-variant focus:ring-secondary/20" name="ppn_type" type="radio" value="non"/>
                                                        <span class="text-label-sm text-on-surface group-hover:text-secondary transition-colors">Non PPN</span>
                                                    </label>
                                                    <label class="flex items-center gap-sm cursor-pointer group">
                                                        <input <?php echo ($editPO && (float)$editPO['ppn_amount'] > 0) ? 'checked' : ''; ?> class="w-4 h-4 text-secondary border-outline-variant focus:ring-secondary/20" name="ppn_type" type="radio" value="ppn"/>
                                                        <span class="text-label-sm text-on-surface group-hover:text-secondary transition-colors">PPN</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-md py-md text-right font-data-tabular text-on-surface-variant" id="tax-amount">Rp 0</td>
                                        <td></td>
                                    </tr>
                                    <tr class="bg-surface-container-low">
                                        <td class="px-lg py-md text-right font-label-md text-primary" colspan="5">Grand Total (Inc. Pajak)</td>
                                        <td class="px-md py-md text-right font-headline-sm text-secondary" id="grand-total">Rp 0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Section 3: Attachment & Confirmation -->
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-lg flex flex-col md:flex-row md:items-center justify-between gap-lg">
                        <div class="space-y-sm">
                            <h4 class="font-label-md text-label-md text-primary uppercase tracking-wider">Quotation Checklist</h4>
                            <p class="text-on-surface-variant text-body-sm">Apakah Penawaran sudah dilampirkan?</p>
                            <div class="flex items-center gap-xl mt-md">
                                <label class="flex items-center gap-sm cursor-pointer group">
                                    <input <?php echo ($editPO && $editPO['quotation_attached']) ? 'checked' : ''; ?> class="w-5 h-5 text-secondary border-outline-variant focus:ring-secondary/20" name="quotation" type="radio" value="yes"/>
                                    <span class="text-on-surface group-hover:text-secondary transition-colors">Ya, Sudah</span>
                                </label>
                                <label class="flex items-center gap-sm cursor-pointer group">
                                    <input <?php echo (!$editPO || !$editPO['quotation_attached']) ? 'checked' : ''; ?> class="w-5 h-5 text-secondary border-outline-variant focus:ring-secondary/20" name="quotation" type="radio" value="no"/>
                                    <span class="text-on-surface group-hover:text-secondary transition-colors">Belum</span>
                                </label>
                            </div>
                        </div>
                        <div class="bg-secondary/5 border border-secondary/20 p-md rounded-lg max-w-sm">
                            <div class="flex items-start gap-md">
                                <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">info</span>
                                <p class="text-body-sm text-on-surface-variant italic">Note: Semua data yang diinput akan diverifikasi oleh departemen keuangan sebelum PO diterbitkan secara resmi.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
                        <div class="bg-surface-container px-lg py-md border-b border-outline-variant">
                            <h4 class="font-label-md text-label-md text-primary uppercase tracking-wider">Catatan Tambahan</h4>
                        </div>
                        <div class="p-lg">
                            <textarea class="w-full px-md py-md border border-outline-variant rounded bg-surface focus:border-secondary focus:ring-2 focus:ring-secondary/10 outline-none transition-all min-h-[100px]" placeholder="Tambahkan catatan internal atau instruksi khusus untuk vendor..."><?php echo $editPO ? htmlspecialchars($editPO['notes']) : ''; ?></textarea>
                        </div>
                    </div>

                    <!-- Approval Status -->
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-lg flex flex-col md:flex-row md:items-center justify-between gap-lg">
                        <div class="space-y-sm">
                            <h4 class="font-label-md text-label-md text-primary uppercase tracking-wider">Approval Status</h4>
                            <p class="text-on-surface-variant text-body-sm">Apakah Purchase Order ini sudah disetujui oleh atasan?</p>
                            <div class="flex items-center gap-xl mt-md">
                                <label class="flex items-center gap-sm cursor-pointer group">
                                    <input <?php echo (!$editPO || !$editPO['approved']) ? 'checked' : ''; ?> class="w-5 h-5 text-secondary border-outline-variant focus:ring-secondary/20" name="approval_status" type="radio" value="no"/>
                                    <span class="text-on-surface group-hover:text-secondary transition-colors">Belum Disetujui</span>
                                </label>
                                <label class="flex items-center gap-sm cursor-pointer group">
                                    <input <?php echo ($editPO && $editPO['approved']) ? 'checked' : ''; ?> class="w-5 h-5 text-secondary border-outline-variant focus:ring-secondary/20" name="approval_status" type="radio" value="yes"/>
                                    <span class="text-on-surface group-hover:text-secondary transition-colors">Sudah Disetujui</span>
                                </label>
                            </div>
                        </div>
                        <div class="bg-secondary/5 border border-secondary/20 p-md rounded-lg max-w-sm">
                            <div class="flex items-start gap-md">
                                <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">info</span>
                                <p class="text-body-sm text-on-surface-variant italic">Note: Memilih "Sudah Disetujui" akan menyertakan tanda tangan digital pada dokumen PDF yang dihasilkan.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-md pb-xl">
                        <button class="px-xl py-md border border-primary text-primary font-label-md rounded-lg hover:bg-surface-container transition-all" type="button" onclick="saveDraft()">
                            <?php echo $editPO ? 'Simpan Perubahan' : 'Simpan Draft'; ?>
                        </button>
                        <button class="px-xl py-md bg-primary text-on-primary font-label-md rounded-lg shadow-xl hover:bg-primary/90 transition-all" type="button" onclick="saveAndPrint()">Simpan & Cetak PDF (Dengan Tanda Tangan)</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        let rowCount = <?php echo $editPO ? max(count($editItems), 1) : 1; ?>;
        const editingId = <?php echo $editPO ? (int)$editPO['id'] : 'null'; ?>;
        const currentStatus = '<?php echo $editPO ? htmlspecialchars($editPO['status']) : ''; ?>';

        document.addEventListener('DOMContentLoaded', function() {
            if (!editingId) {
                generatePONumber();
            }
            calculateTotal();
        });

        async function generatePONumber() {
            try {
                const response = await fetch('api/generate_po_number.php');
                const result = await response.json();
                if (result.success) {
                    document.getElementById('po-number').value = result.data.po_number;
                }
            } catch (error) {
                console.error('Error generating PO number:', error);
                // Fallback: generate client-side
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                document.getElementById('po-number').value = `PO-${year}${month}-001`;
            }
        }

        function calculateTotal() {
            const rows = document.querySelectorAll('#table-body tr');
            let grandTotal = 0;

            rows.forEach(row => {
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                const total = qty * price;
                
                row.querySelector('.row-total').textContent = total.toLocaleString('id-ID');
                grandTotal += total;
            });

            const ppnType = document.querySelector('input[name="ppn_type"]:checked').value;
            let taxAmount = 0;
            
            if (ppnType === 'ppn') {
                taxAmount = grandTotal * 0.11;
            }

            document.getElementById('grand-total').textContent = 'Rp ' + (grandTotal + taxAmount).toLocaleString('id-ID');
            document.getElementById('tax-amount').textContent = 'Rp ' + taxAmount.toLocaleString('id-ID');
        }

        function addRow() {
            const tbody = document.getElementById('table-body');
            rowCount++;
            const newRow = document.createElement('tr');
            newRow.className = 'group hover:bg-surface-container-low/50 transition-colors';
            newRow.innerHTML = `
                <td class="px-md py-md border-b border-outline-variant text-center text-on-surface-variant font-data-tabular">${rowCount}</td>
                <td class="px-md py-md border-b border-outline-variant">
                    <input type="text" placeholder="Item description" class="w-full bg-transparent border-none p-0 focus:ring-0 text-primary font-body-md">
                </td>
                <td class="px-md py-md border-b border-outline-variant">
                    <input type="number" value="1" min="1" onchange="calculateTotal()" class="qty-input w-full bg-transparent border-none p-0 focus:ring-0 text-primary font-data-tabular">
                </td>
                <td class="px-md py-md border-b border-outline-variant">
                    <select class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant font-body-md appearance-none">
                        <option>Pcs</option>
                        <option>Box</option>
                        <option>Lot</option>
                        <option>Roll</option>
                    </select>
                </td>
                <td class="px-md py-md border-b border-outline-variant text-right">
                    <div class="flex items-center justify-end">
                        <span class="text-on-surface-variant mr-1">Rp</span>
                        <input type="number" value="0" min="0" onchange="calculateTotal()" class="price-input w-32 bg-transparent border-none p-0 focus:ring-0 text-primary text-right font-data-tabular">
                    </div>
                </td>
                <td class="px-md py-md border-b border-outline-variant text-right font-data-tabular text-primary">
                    Rp <span class="row-total">0</span>
                </td>
                <td class="px-md py-md border-b border-outline-variant text-center">
                    <button type="button" class="text-on-surface-variant opacity-0 group-hover:opacity-100 hover:text-error transition-all" onclick="removeRow(this)">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </button>
                </td>
            `;
            tbody.appendChild(newRow);
            calculateTotal();
        }

        function removeRow(btn) {
            const row = btn.closest('tr');
            const tbody = document.getElementById('table-body');
            if (tbody.rows.length > 1) {
                row.remove();
                Array.from(tbody.rows).forEach((r, idx) => {
                    r.cells[0].textContent = idx + 1;
                });
                calculateTotal();
            } else {
                alert('At least one item is required.');
            }
        }

        function getFormData() {
            const vendor_id = document.getElementById('vendor').value;
            const project_id = document.getElementById('project').value;
            const top = document.getElementById('top').value;
            const delivery_location = document.getElementById('delivery').value;
            const approval_status = document.querySelector('input[name="approval_status"]:checked').value;
            const quotation = document.querySelector('input[name="quotation"]:checked').value;
            const ppn_type = document.querySelector('input[name="ppn_type"]:checked').value;
            
            const items = [];
            const rows = document.querySelectorAll('#table-body tr');
            rows.forEach(row => {
                const inputs = row.querySelectorAll('input, select');
                if (inputs.length >= 5) {
                    items.push({
                        item_name: inputs[0].value,
                        quantity: parseFloat(inputs[1].value) || 0,
                        unit: inputs[2].value,
                        price: parseFloat(inputs[3].value) || 0
                    });
                }
            });

            return {
                vendor_id: vendor_id ? parseInt(vendor_id) : null,
                project_id: project_id ? parseInt(project_id) : null,
                top: top,
                delivery_location: delivery_location,
                status: 'Draft',
                ppn_type: ppn_type,
                quotation_attached: quotation === 'yes',
                approved: approval_status === 'yes',
                notes: document.querySelector('textarea').value,
                items: items
            };
        }

        async function savePO(status = 'Draft') {
            const formData = getFormData();
            formData.status = status;

            const isEdit = !!editingId;
            if (isEdit) {
                formData.id = editingId;
            }

            try {
                const response = await fetch('api/po.php', {
                    method: isEdit ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();
                
                if (result.success) {
                    if (isEdit) {
                        alert('Purchase Order berhasil diperbarui!');
                        window.location.href = 'po_detail.php?id=' + editingId;
                    } else {
                        alert('Purchase Order berhasil disimpan! PO Number: ' + result.data.po_number);
                        window.location.href = 'po_list.php';
                    }
                } else {
                    alert('Gagal menyimpan PO: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal menyimpan PO');
            }
        }

        function saveDraft() {
            // Saat mengedit, simpan perubahan tanpa mengubah status PO yang sudah ada
            if (editingId) {
                savePO(currentStatus || 'Draft');
            } else {
                savePO('Draft');
            }
        }

        function saveAndPrint() {
            // Jangan menurunkan status PO yang sudah melewati tahap Printed
            if (editingId && ['Signed', 'Invoiced', 'Completed'].indexOf(currentStatus) !== -1) {
                savePO(currentStatus);
            } else {
                savePO('Printed');
            }
        }
    </script>
</body>
</html>
