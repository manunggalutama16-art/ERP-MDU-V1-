<?php
session_start();
require_once 'api/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$poId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($poId <= 0) {
    header('Location: po_list.php');
    exit();
}

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

// Get settings
$settings = [];
$settingsResult = $conn->query("SELECT * FROM system_settings");
while ($row = $settingsResult->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Purchase Order <?php echo htmlspecialchars($po['po_number']); ?> | ProcureCorp</title>
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
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: inline-block; vertical-align: middle; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .page-canvas { box-shadow: none !important; border: none !important; margin: 0 !important; padding: 0 !important; }
        }
        .page-canvas { width: 210mm; min-height: 297mm; margin: 20px auto; background: white; box-shadow: 0 4px 12px rgba(30, 41, 59, 0.08); padding: 40px; }
    </style>
</head>
<body class="bg-surface font-body-md text-on-surface">
    <!-- Document Toolbar -->
    <header class="no-print sticky top-0 z-50 bg-surface dark:bg-surface-container-low border-b border-outline-variant flex justify-between items-center h-16 px-container-margin transition-all duration-150">
        <div class="flex items-center gap-md">
            <button class="flex items-center text-on-surface-variant hover:text-secondary transition-colors duration-150" onclick="window.history.back()">
                <span class="material-symbols-outlined mr-xs">arrow_back</span>
                <span class="font-label-md text-label-md">Back to List</span>
            </button>
            <div class="h-6 w-px bg-outline-variant mx-sm"></div>
            <h1 class="font-headline-sm text-headline-sm text-primary"><?php echo htmlspecialchars($po['po_number']); ?></h1>
        </div>
        <div class="flex items-center gap-md">
            <button class="flex items-center gap-xs px-md py-sm bg-surface border border-primary text-primary font-label-md text-label-md hover:bg-surface-container-high transition-colors" onclick="window.print()">
                <span class="material-symbols-outlined">print</span>
                Print
            </button>
            <button class="flex items-center gap-xs px-md py-sm bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity" onclick="window.print()">
                <span class="material-symbols-outlined">download</span>
                Download PDF
            </button>
            <div class="h-6 w-px bg-outline-variant mx-sm"></div>
            <span class="material-symbols-outlined text-on-surface-variant cursor-pointer hover:text-secondary">notifications</span>
            <span class="material-symbols-outlined text-on-surface-variant cursor-pointer hover:text-secondary">help</span>
        </div>
    </header>

    <main class="py-lg px-container-margin">
        <!-- PDF Canvas Container -->
        <div class="page-canvas flex flex-col border border-outline-variant">
            <!-- Document Header -->
            <div class="flex justify-between items-start mb-xl">
                <div class="flex gap-md items-center">
                    <div class="w-16 h-16 bg-primary flex items-center justify-center rounded-lg overflow-hidden">
                        <?php if (!empty($settings['logo_file'])): ?>
                            <img class="w-12 h-12 object-contain" data-alt="Logo" src="<?php echo htmlspecialchars($settings['logo_file']); ?>">
                        <?php else: ?>
                            <span class="text-on-primary font-headline-sm text-headline-sm font-bold"><?php echo htmlspecialchars(userInitials($settings['company_name'] ?? 'ProcureCorp')); ?></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h2 class="font-headline-md text-headline-md text-primary tracking-tight"><?php echo htmlspecialchars($settings['company_name'] ?? 'ProcureCorp'); ?></h2>
                        <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Enterprise Suite</p>
                    </div>
                </div>
                <div class="text-right">
                    <h1 class="font-display-lg text-display-lg text-primary uppercase font-extrabold mb-xs">Purchase Order</h1>
                    <div class="grid grid-cols-2 gap-x-md text-right">
                        <span class="font-label-md text-label-md text-on-surface-variant">PO Number:</span>
                        <span class="font-label-md text-label-md text-primary"><?php echo htmlspecialchars($po['po_number']); ?></span>
                        <span class="font-label-md text-label-md text-on-surface-variant">Date:</span>
                        <span class="font-label-md text-label-md text-primary"><?php echo formatDate($po['created_at']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Parties Info -->
            <div class="grid grid-cols-2 gap-xl mb-xl border-t border-b border-outline-variant py-lg">
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

            <!-- Signature Block -->
            <?php
            // Only an approved PO is signed. If a digital signature was uploaded in Settings, render it;
            // otherwise show a placeholder line. Unapproved POs are explicitly marked as not yet approved.
            $poApproved = (bool)$po['approved'];
            $signatureImage = $poApproved && !empty($settings['signature_file']) ? $settings['signature_file'] : '';
            ?>
            <div class="mt-xl flex justify-end">
                <div class="text-center w-64">
                    <p class="font-label-md text-label-md text-on-surface-variant mb-xl">Authorized By</p>
                    <div class="h-24 flex items-center justify-center relative mb-sm">
                        <?php if ($signatureImage): ?>
                            <img class="max-h-20 max-w-full object-contain" data-alt="Digital Signature" src="<?php echo htmlspecialchars($signatureImage); ?>"/>
                        <?php elseif ($poApproved): ?>
                            <!-- Simulated Signature -->
                            <div class="border-b border-on-surface-variant mx-auto w-48 mb-1"></div>
                        <?php else: ?>
                            <p class="font-label-sm text-label-sm text-error uppercase tracking-wider">Belum Disetujui</p>
                        <?php endif; ?>
                    </div>
                    <p class="font-label-md text-label-md text-primary font-bold"><?php echo htmlspecialchars($settings['signatory_name'] ?? ''); ?></p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant"><?php echo htmlspecialchars($settings['signatory_title'] ?? ''); ?></p>
                </div>
            </div>

            <!-- Document Footer -->
            <div class="mt-auto pt-xl border-t border-outline-variant text-center">
                <p class="font-body-sm text-body-sm text-on-surface-variant">
                    <?php echo htmlspecialchars($settings['company_name'] ?? 'ProcureCorp'); ?> | <?php echo htmlspecialchars($settings['company_address'] ?? ''); ?> | info@procurement.mdutama.com | +62 21 555 0100
                </p>
            </div>
        </div>
    </main>

    <script>
        document.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('mousedown', () => btn.classList.add('scale-[0.98]'));
            btn.addEventListener('mouseup', () => btn.classList.remove('scale-[0.98]'));
            btn.addEventListener('mouseleave', () => btn.classList.remove('scale-[0.98]'));
        });
    </script>
</body>
</html>
