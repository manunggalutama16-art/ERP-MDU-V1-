<?php
session_start();
require_once 'api/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'user';

// Fetch current settings
$conn = getConnection();
$settingsResult = $conn->query("SELECT * FROM system_settings");
$settings = [];
while ($row = $settingsResult->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$sigPositionSetting = $settings['signature_position'] ?? 'right';
?>
<!DOCTYPE html>
<html class="light" lang="id" style="">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Pengaturan Template PO | Nexus Procurement</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-primary-fixed-variant": "#3c475a",
                        "primary": "#091426",
                        "inverse-on-surface": "#f3f0f2",
                        "surface-dim": "#dcd9db",
                        "tertiary-fixed": "#6ffbbe",
                        "on-tertiary-fixed": "#002113",
                        "inverse-primary": "#bcc7de",
                        "surface-tint": "#545f73",
                        "error-container": "#ffdad6",
                        "surface": "#fbf8fa",
                        "secondary-container": "#2170e4",
                        "surface-bright": "#fbf8fa",
                        "primary-fixed": "#d8e3fb",
                        "outline-variant": "#c5c6cd",
                        "surface-container-highest": "#e4e2e3",
                        "error": "#ba1a1a",
                        "surface-container": "#f0edef",
                        "primary-container": "#1e293b",
                        "on-error-container": "#93000a",
                        "tertiary-fixed-dim": "#4edea3",
                        "on-surface": "#1b1b1d",
                        "on-secondary": "#ffffff",
                        "on-background": "#1b1b1d",
                        "secondary-fixed-dim": "#adc6ff",
                        "secondary": "#0058be",
                        "on-secondary-fixed-variant": "#004395",
                        "on-error": "#ffffff",
                        "inverse-surface": "#303032",
                        "tertiary-container": "#00301e",
                        "surface-container-low": "#f5f3f4",
                        "on-secondary-container": "#fefcff",
                        "outline": "#75777d",
                        "surface-variant": "#e4e2e3",
                        "on-tertiary-container": "#00a472",
                        "surface-container-high": "#eae7e9",
                        "primary-fixed-dim": "#bcc7de",
                        "on-secondary-fixed": "#001a42",
                        "secondary-fixed": "#d8e2ff",
                        "on-primary": "#ffffff",
                        "on-tertiary-fixed-variant": "#005236",
                        "tertiary": "#00190e",
                        "on-primary-fixed": "#111c2d",
                        "background": "#fbf8fa",
                        "surface-container-lowest": "#ffffff",
                        "on-tertiary": "#ffffff",
                        "on-primary-container": "#8590a6",
                        "on-surface-variant": "#45474c"
                    },
                    borderRadius: {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    spacing: {
                        "gutter": "16px",
                        "unit": "4px",
                        "sidebar-width": "260px",
                        "md": "16px",
                        "sm": "8px",
                        "xl": "32px",
                        "xs": "4px",
                        "container-margin": "24px",
                        "lg": "24px"
                    },
                    fontFamily: {
                        "body-lg": ["Inter"],
                        "label-md": ["Inter"],
                        "display-lg": ["Inter"],
                        "data-tabular": ["Inter"],
                        "body-md": ["Inter"],
                        "label-sm": ["Inter"],
                        "headline-sm": ["Inter"],
                        "body-sm": ["Inter"],
                        "headline-md": ["Inter"]
                    },
                    fontSize: {
                        "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "label-md": ["14px", {"lineHeight": "20px", "fontWeight": "600"}],
                        "display-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "data-tabular": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "label-sm": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}],
                        "headline-sm": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
                        "body-sm": ["12px", {"lineHeight": "16px", "fontWeight": "400"}],
                        "headline-md": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600"}]
                    }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: inline-block; line-height: 1; }
        .pdf-preview-canvas { aspect-ratio: 1 / 1.414; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); }
        .custom-radio:checked + label { border-color: #0058be; background-color: #f0f7ff; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c5c6cd; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #75777d; }
    </style>
</head>
<body class="bg-background font-body-md text-on-surface antialiased overflow-hidden h-screen flex">
    <!-- SideNavBar Anchor -->
    <aside class="fixed h-screen w-sidebar-width left-0 top-0 bg-primary flex flex-col h-full overflow-y-auto z-50">
        <div class="p-lg flex flex-col items-center border-b border-on-primary-container/10">
            <div class="text-headline-sm font-headline-sm text-on-primary mb-1">ProcureCorp</div>
            <div class="text-label-sm font-label-sm text-on-primary-container opacity-70">Enterprise Suite</div>
        </div>
        <nav class="flex-1 py-md px-sm space-y-1">
            <a class="flex items-center px-md py-sm rounded transition-colors duration-200 text-on-primary-container hover:bg-primary-container hover:text-on-primary" href="dashboard.php">
                <span class="material-symbols-outlined mr-3">dashboard</span>
                <span class="font-label-md text-label-md">Dashboard</span>
            </a>
            <a class="flex items-center px-md py-sm rounded transition-colors duration-200 text-on-primary-container hover:bg-primary-container hover:text-on-primary" href="vendors.php">
                <span class="material-symbols-outlined mr-3">database</span>
                <span class="font-label-md text-label-md">Master Data</span>
            </a>
            <a class="flex items-center px-md py-sm rounded transition-colors duration-200 text-on-primary-container hover:bg-primary-container hover:text-on-primary" href="po_list.php">
                <span class="material-symbols-outlined mr-3">shopping_cart</span>
                <span class="font-label-md text-label-md">Purchase Orders</span>
            </a>
            <a class="flex items-center px-md py-sm rounded transition-colors duration-200 text-on-primary-container hover:bg-primary-container hover:text-on-primary" href="reports.php">
                <span class="material-symbols-outlined mr-3">analytics</span>
                <span class="font-label-md text-label-md">Reports</span>
            </a>
            <a class="flex items-center px-md py-sm rounded transition-colors duration-200 bg-secondary-container text-on-secondary-container border-l-4 border-secondary-fixed" href="settings.php">
                <span class="material-symbols-outlined mr-3">settings</span>
                <span class="font-label-md text-label-md">Settings</span>
            </a>
        </nav>
        <div class="p-md border-t border-on-primary-container/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container font-bold">
                    <?php echo strtoupper(substr($userName, 0, 2)); ?>
                </div>
                <div class="flex flex-col">
                    <span class="text-on-primary font-label-md text-label-md"><?php echo htmlspecialchars($userName); ?></span>
                    <span class="text-on-primary-container text-[10px] uppercase font-bold opacity-60"><?php echo htmlspecialchars($userRole); ?></span>
                </div>
            </div>
            <a href="api/auth.php?action=logout" class="mt-2 flex items-center gap-sm text-on-primary-container hover:text-error text-body-sm transition-colors">
                <span class="material-symbols-outlined text-[16px]">logout</span>
                Logout
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col ml-sidebar-width h-screen">
        <!-- TopNavBar Anchor -->
        <header class="flex justify-between items-center h-16 px-container-margin sticky top-0 z-40 bg-surface border-b border-outline-variant transition-all duration-150">
            <div class="flex items-center gap-xl">
                <h1 class="font-headline-sm text-headline-sm text-primary">Procurement Management</h1>
                <div class="relative w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
                    <input class="w-full bg-surface-container-low border-none rounded-lg pl-10 pr-4 py-2 text-body-sm focus:ring-2 focus:ring-secondary/20" placeholder="Search orders..." type="text">
                </div>
            </div>
            <div class="flex items-center gap-md">
                <button class="w-10 h-10 flex items-center justify-center text-on-surface-variant hover:text-secondary transition-all">
                    <span class="material-symbols-outlined">notifications</span>
                </button>
                <button class="w-10 h-10 flex items-center justify-center text-on-surface-variant hover:text-secondary transition-all">
                    <span class="material-symbols-outlined">help</span>
                </button>
            </div>
        </header>

        <!-- Page Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-container-margin">
            <!-- Breadcrumbs & Header -->
            <div class="mb-lg">
                <div class="flex items-center gap-2 text-label-sm font-label-sm text-on-surface-variant mb-2">
                    <span class="">Settings</span>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <span class="">Templates</span>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <span class="text-primary font-bold">PO Settings</span>
                </div>
                <div class="flex justify-between items-end">
                    <div>
                        <h2 class="font-display-lg text-display-lg text-primary">Pengaturan Template PO</h2>
                        <p class="text-body-md text-on-surface-variant">Langkah 4: Konfigurasi visual dan otorisasi dokumen Purchase Order.</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" class="px-6 py-2.5 bg-surface border border-primary text-primary font-label-md text-label-md rounded hover:bg-surface-container-high transition-colors" onclick="resetSettings()">Batal</button>
                        <button type="button" class="px-6 py-2.5 bg-secondary text-on-secondary font-label-md text-label-md rounded shadow-sm hover:brightness-110 transition-all" onclick="saveSettings()">Simpan Perubahan</button>
                    </div>
                </div>
            </div>

            <!-- Bento Layout Grid -->
            <div class="grid grid-cols-12 gap-lg items-start">
                <!-- Left Column: Configuration Forms -->
                <div class="col-span-12 lg:col-span-5 space-y-lg">
                    <!-- Logo Upload Section -->
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-lg">
                        <div class="flex items-center gap-3 mb-md">
                            <span class="material-symbols-outlined text-secondary">image</span>
                            <h3 class="font-headline-sm text-headline-sm text-on-surface">Logo Perusahaan</h3>
                        </div>
                        <p class="text-body-sm text-on-surface-variant mb-6">Logo akan muncul di bagian pojok kiri atas dokumen PO. Gunakan format PNG atau JPG (Max 2MB).</p>
                        <div class="flex items-center gap-6">
                            <div class="w-32 h-32 bg-surface-container-low border-2 border-dashed border-outline rounded-lg flex items-center justify-center overflow-hidden relative group">
                                <?php $hasLogo = !empty($settings['logo_file']); ?>
                                <img class="w-full h-full object-contain p-4" data-alt="Logo" id="logoPreview" src="<?php echo $hasLogo ? htmlspecialchars($settings['logo_file']) : 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=='; ?>">
                                <?php if (!$hasLogo): ?><p class="absolute text-label-sm text-on-surface-variant" id="logoPlaceholder">Belum ada logo</p><?php endif; ?>
                                <div class="absolute inset-0 bg-primary/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer" onclick="document.getElementById('logoFile').click()">
                                    <span class="material-symbols-outlined text-white">edit</span>
                                </div>
                            </div>
                            <div class="flex-1 flex flex-col gap-2">
                                <button class="w-full py-2 bg-secondary/10 text-secondary border border-secondary/20 rounded font-label-md text-label-md hover:bg-secondary/20 transition-all flex items-center justify-center gap-2" onclick="document.getElementById('logoFile').click()">
                                    <span class="material-symbols-outlined text-sm">upload</span>
                                    Ganti Gambar
                                </button>
                                <button class="w-full py-2 bg-transparent text-error font-label-md text-label-md hover:bg-error/5 transition-all flex items-center justify-center gap-2" onclick="deleteLogo()">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                    Hapus Logo
                                </button>
                                <input type="file" id="logoFile" class="hidden" accept="image/png,image/jpeg" onchange="handleLogoUpload(this)">
                            </div>
                        </div>
                    </div>

                    <!-- Company Identity -->
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-lg mt-lg">
                        <div class="flex items-center gap-3 mb-md">
                            <span class="material-symbols-outlined text-secondary">business</span>
                            <h3 class="font-headline-sm text-headline-sm text-on-surface">Identitas Perusahaan</h3>
                        </div>
                        <div class="space-y-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-label-sm font-label-sm text-on-surface-variant">Nama Perusahaan</label>
                                <input class="w-full border-outline-variant rounded-lg p-3 text-body-md focus:border-secondary focus:ring-2 focus:ring-secondary/10 transition-all" placeholder="Masukkan nama perusahaan..." type="text" id="companyName" value="<?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>">
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-label-sm font-label-sm text-on-surface-variant">Alamat Perusahaan</label>
                                <textarea class="w-full border-outline-variant rounded-lg p-3 text-body-md focus:border-secondary focus:ring-2 focus:ring-secondary/10 transition-all" placeholder="Masukkan alamat lengkap perusahaan..." rows="3" id="companyAddress"><?php echo htmlspecialchars($settings['company_address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Authorization Details -->
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-lg">
                        <div class="flex items-center gap-3 mb-md">
                            <span class="material-symbols-outlined text-secondary">verified_user</span>
                            <h3 class="font-headline-sm text-headline-sm text-on-surface">Detail Otorisasi</h3>
                        </div>
                        <div class="space-y-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-label-sm font-label-sm text-on-surface-variant">Nama Pejabat Penandatangan</label>
                                <input class="w-full border-outline-variant rounded-lg p-3 text-body-md focus:border-secondary focus:ring-2 focus:ring-secondary/10 transition-all" placeholder="Masukkan nama lengkap..." type="text" id="signatoryName" value="<?php echo htmlspecialchars($settings['signatory_name'] ?? ''); ?>">
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-label-sm font-label-sm text-on-surface-variant">Jabatan Resmi</label>
                                <input class="w-full border-outline-variant rounded-lg p-3 text-body-md focus:border-secondary focus:ring-2 focus:ring-secondary/10 transition-all" placeholder="Masukkan jabatan..." type="text" id="signatoryTitle" value="<?php echo htmlspecialchars($settings['signatory_title'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Signature Alignment -->
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-lg">
                        <div class="flex items-center gap-3 mb-md">
                            <span class="material-symbols-outlined text-secondary">format_align_right</span>
                            <h3 class="font-headline-sm text-headline-sm text-on-surface">Otorisasi & Tanda Tangan</h3>
                        </div>
                        <p class="text-body-sm text-on-surface-variant mb-6">Tentukan posisi blok tanda tangan pada halaman terakhir dokumen.</p>
                        <div class="mb-6">
                            <label class="text-label-sm font-label-sm text-on-surface-variant block mb-2">Unggah Tanda Tangan (PNG)</label>
                            <div class="flex items-center gap-6">
                                <div class="w-32 h-16 bg-surface-container-low border-2 border-dashed border-outline rounded-lg flex items-center justify-center overflow-hidden relative group" id="signaturePreview">
                                    <?php if (!empty($settings['signature_file'])): ?>
                                        <img class="max-h-full max-w-full object-contain" data-alt="Tanda tangan" src="<?php echo htmlspecialchars($settings['signature_file']); ?>">
                                    <?php else: ?>
                                        <div class="text-on-surface-variant opacity-40">
                                            <span class="material-symbols-outlined text-3xl">draw</span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="absolute inset-0 bg-primary/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer" onclick="document.getElementById('signatureFile').click()">
                                        <span class="material-symbols-outlined text-white">edit</span>
                                    </div>
                                </div>
                                <div class="flex-1 flex flex-col gap-2">
                                    <button class="w-full py-2 bg-secondary/10 text-secondary border border-secondary/20 rounded font-label-md text-label-md hover:bg-secondary/20 transition-all flex items-center justify-center gap-2" onclick="document.getElementById('signatureFile').click()">
                                        <span class="material-symbols-outlined text-sm">upload</span>
                                        Ganti Gambar
                                    </button>
                                    <button class="w-full py-2 bg-transparent text-error font-label-md text-label-md hover:bg-error/5 transition-all flex items-center justify-center gap-2" onclick="deleteSignature()">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                        Hapus
                                    </button>
                                    <input type="file" id="signatureFile" class="hidden" accept="image/png" onchange="handleSignatureUpload(this)">
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="relative">
                                <input class="hidden custom-radio" id="pos-left" name="sig_pos" type="radio" value="left" <?php echo ($settings['signature_position'] ?? 'right') === 'left' ? 'checked' : ''; ?>>
                                <label class="flex flex-col items-center justify-center p-4 border border-outline-variant rounded-xl cursor-pointer hover:border-secondary/50 transition-all" for="pos-left">
                                    <span class="material-symbols-outlined text-3xl mb-2 text-on-surface-variant">align_horizontal_left</span>
                                    <span class="text-label-md font-label-md text-on-surface">Sisi Kiri</span>
                                </label>
                            </div>
                            <div class="relative">
                                <input class="hidden custom-radio" id="pos-right" name="sig_pos" type="radio" value="right" <?php echo ($settings['signature_position'] ?? 'right') === 'right' ? 'checked' : ''; ?>>
                                <label class="flex flex-col items-center justify-center p-4 border border-outline-variant rounded-xl cursor-pointer hover:border-secondary/50 transition-all" for="pos-right">
                                    <span class="material-symbols-outlined text-3xl mb-2 text-on-surface-variant">align_horizontal_right</span>
                                    <span class="text-label-md font-label-md text-on-surface">Sisi Kanan</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Notes -->
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-lg mt-lg">
                        <div class="flex items-center gap-3 mb-md">
                            <span class="material-symbols-outlined text-secondary">notes</span>
                            <h3 class="font-headline-sm text-headline-sm text-on-surface">Catatan Kaki & Footer</h3>
                        </div>
                        <p class="text-body-sm text-on-surface-variant mb-6">Tambahkan informasi tambahan seperti syarat & ketentuan atau detail rekening bank.</p>
                        <div class="space-y-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-label-sm font-label-sm text-on-surface-variant">Catatan Kaki / Footnote</label>
                                <textarea class="w-full border-outline-variant rounded-lg p-3 text-body-md focus:border-secondary focus:ring-2 focus:ring-secondary/10 transition-all" placeholder="Contoh: Pembayaran dilakukan maksimal 30 hari setelah invoice diterima..." rows="3" id="footerNote"><?php echo htmlspecialchars($settings['footer_note'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Visual Preview -->
                <div class="col-span-12 lg:col-span-7 sticky top-24">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-label-md font-label-md text-on-surface-variant flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">visibility</span>
                            PRATINJAU LAYOUT PDF
                        </h3>
                        <div class="flex gap-2">
                            <button class="p-2 hover:bg-surface-container-high rounded-full transition-colors"><span class="material-symbols-outlined text-sm">zoom_in</span></button>
                            <button class="p-2 hover:bg-surface-container-high rounded-full transition-colors"><span class="material-symbols-outlined text-sm">zoom_out</span></button>
                            <button class="p-2 hover:bg-surface-container-high rounded-full transition-colors"><span class="material-symbols-outlined text-sm">print</span></button>
                        </div>
                    </div>
                    <!-- Paper Surface -->
                    <div class="pdf-preview-canvas bg-white mx-auto w-full max-w-2xl border border-outline-variant p-xl flex flex-col">
                        <!-- PDF Header -->
                        <div class="flex justify-between items-start mb-12">
                            <div class="flex gap-md items-center">
                                <div class="w-24 h-12 bg-slate-50 flex items-center justify-center">
                                    <span class="w-10 h-10 bg-primary rounded flex items-center justify-center"><span class="text-white text-xs font-bold"><?php echo htmlspecialchars(userInitials($settings['company_name'] ?? 'ProcureCorp')); ?></span></span>
                                </div>
                                <div class="mt-2 max-w-[200px]">
                                    <p class="text-[10px] font-bold" id="previewCompanyName"><?php echo htmlspecialchars($settings['company_name'] ?? 'ProcureCorp'); ?></p>
                                    <p class="text-[8px] text-on-surface-variant leading-tight" id="previewCompanyAddress"><?php echo htmlspecialchars($settings['company_address'] ?? 'Jl. Sudirman No. 45, Kav 12, Jakarta Selatan, Indonesia 12190'); ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <h4 class="font-bold text-xl text-primary leading-none mb-1">PURCHASE ORDER</h4>
                                <p class="text-xs text-on-surface-variant">PO #2023-00982-INT</p>
                                <p class="text-xs text-on-surface-variant">Date: 24 Oct 2023</p>
                            </div>
                        </div>
                        <!-- Dummy Info Blocks -->
                        <div class="grid grid-cols-2 gap-8 mb-12">
                            <div>
                                <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-2">Vendor</p>
                                <p class="text-xs font-bold">Global Tech Solutions Inc.</p>
                                <p class="text-xs text-on-surface-variant">123 Industrial Park, Block C<br>Singapore 118223</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-2">Ship To</p>
                                <p class="text-xs font-bold">ProcureCorp HQ - Warehouse A</p>
                                <p class="text-xs text-on-surface-variant">Jl. Sudirman No. 45, Kav 12<br>Jakarta Selatan, Indonesia 12190</p>
                            </div>
                        </div>
                        <!-- Table Skeleton -->
                        <div class="flex-1 mb-8">
                            <table class="w-full text-xs">
                                <thead class="border-y border-slate-200">
                                    <tr>
                                        <th class="py-3 text-left font-bold w-12">No</th>
                                        <th class="py-3 text-left font-bold">Description</th>
                                        <th class="py-3 text-right font-bold w-16">Qty</th>
                                        <th class="py-3 text-right font-bold w-24">Price</th>
                                        <th class="py-3 text-right font-bold w-24">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-slate-100">
                                        <td class="py-3">01</td>
                                        <td class="py-3">Enterprise Cloud Storage Annual Subscription</td>
                                        <td class="py-3 text-right">5</td>
                                        <td class="py-3 text-right">$1,200</td>
                                        <td class="py-3 text-right">$6,000</td>
                                    </tr>
                                    <tr class="border-b border-slate-100">
                                        <td class="py-3">02</td>
                                        <td class="py-3">Advanced Security Implementation Kit</td>
                                        <td class="py-3 text-right">1</td>
                                        <td class="py-3 text-right">$2,500</td>
                                        <td class="py-3 text-right">$2,500</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Summary Block -->
                        <div class="flex justify-end mb-16">
                            <div class="w-48 space-y-2">
                                <div class="flex justify-between text-xs">
                                    <span class="text-on-surface-variant">Subtotal</span>
                                    <span class="">$8,500.00</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-on-surface-variant">PPN (11%)</span>
                                    <span class="">$935.00</span>
                                </div>
                                <div class="flex justify-between font-bold border-t border-slate-200 pt-2 text-sm">
                                    <span class="">Grand Total</span>
                                    <span class="text-secondary">$9,435.00</span>
                                </div>
                            </div>
                        </div>
                        <!-- Authorization Block -->
                        <div class="flex <?php echo $sigPositionSetting === 'left' ? 'justify-start' : 'justify-end'; ?> transition-all duration-300" id="preview-auth-block">
                            <div class="text-center w-64">
                                <p class="text-xs mb-16">Authorized Signature,</p>
                                <div class="border-b border-slate-900 mx-auto w-48 mb-1"></div>
                                <p class="text-xs font-bold text-on-surface" id="preview-name"><?php echo htmlspecialchars($settings['signatory_name'] ?? 'Nama Pejabat'); ?></p>
                                <p class="text-[10px] text-on-surface-variant italic" id="preview-title"><?php echo htmlspecialchars($settings['signatory_title'] ?? 'Jabatan Resmi'); ?></p>
                            </div>
                        </div>
                        <div class="mt-12 pt-4 border-t border-slate-200">
                            <p class="text-[8px] text-on-surface-variant italic leading-relaxed">
                                <?php echo nl2br(htmlspecialchars($settings['footer_note'] ?? '')); ?>
                            </p>
                        </div>
                    </div>
                    <!-- Info Alert -->
                    <div class="mt-6 p-4 bg-secondary-container/10 border border-secondary/20 rounded-xl flex items-start gap-4">
                        <span class="material-symbols-outlined text-secondary mt-0.5">info</span>
                        <div>
                            <p class="text-label-md font-label-md text-secondary">Sinkronisasi Pratinjau</p>
                            <p class="text-body-sm text-on-surface-variant">Pratinjau visual ini diperbarui secara real-time saat Anda mengubah detail di panel kiri. Pastikan informasi otorisasi sudah benar sebelum menyimpan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const nameInput = document.getElementById('signatoryName');
        const titleInput = document.getElementById('signatoryTitle');
        const previewName = document.getElementById('preview-name');
        const previewTitle = document.getElementById('preview-title');
        const authBlock = document.getElementById('preview-auth-block');
        const radioLeft = document.getElementById('pos-left');
        const radioRight = document.getElementById('pos-right');
        const companyNameInput = document.getElementById('companyName');
        const companyAddressInput = document.getElementById('companyAddress');
        const previewCompanyName = document.getElementById('previewCompanyName');
        const previewCompanyAddress = document.getElementById('previewCompanyAddress');

        companyNameInput?.addEventListener('input', (e) => {
            if (previewCompanyName) previewCompanyName.textContent = e.target.value || 'ProcureCorp';
        });

        companyAddressInput?.addEventListener('input', (e) => {
            if (previewCompanyAddress) previewCompanyAddress.textContent = e.target.value;
        });

        nameInput?.addEventListener('input', (e) => {
            previewName.textContent = e.target.value || "Nama Pejabat";
        });

        titleInput?.addEventListener('input', (e) => {
            previewTitle.textContent = e.target.value || "Jabatan Resmi";
        });

        radioLeft?.addEventListener('change', () => {
            if(radioLeft.checked) {
                authBlock.classList.remove('justify-end');
                authBlock.classList.add('justify-start');
            }
        });

        radioRight?.addEventListener('change', () => {
            if(radioRight.checked) {
                authBlock.classList.remove('justify-start');
                authBlock.classList.add('justify-end');
            }
        });

        document.querySelectorAll('nav a').forEach(link => {
            link.addEventListener('mousedown', function() {
                this.classList.add('scale-[0.98]');
            });
            link.addEventListener('mouseup', function() {
                this.classList.remove('scale-[0.98]');
            });
        });

        async function saveSettings() {
            const settings = {
                company_name: document.getElementById('companyName').value,
                company_address: document.getElementById('companyAddress').value,
                signatory_name: document.getElementById('signatoryName').value,
                signatory_title: document.getElementById('signatoryTitle').value,
                signature_position: document.querySelector('input[name="sig_pos"]:checked')?.value || 'right',
                footer_note: document.getElementById('footerNote').value
            };

            try {
                const response = await fetch('api/settings.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(settings)
                });

                const result = await response.json();
                
                if (result.success) {
                    alert('Settings saved successfully!');
                    // Muat ulang agar seluruh field + pratinjau menampilkan nilai yang baru disimpan
                    location.reload();
                } else {
                    alert('Failed to save settings: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to save settings');
            }
        }

        function resetSettings() {
            if (confirm('Reset all changes?')) {
                location.reload();
            }
        }

        async function handleLogoUpload(input) {
            if (!input.files || !input.files[0]) return;

            const formData = new FormData();
            formData.append('file', input.files[0]);

            try {
                const response = await fetch('api/uploads.php?type=logo', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    const preview = document.getElementById('logoPreview');
                    const placeholder = document.getElementById('logoPlaceholder');
                    preview.src = result.data.file_path;
                    if (placeholder) placeholder.style.display = 'none';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function handleSignatureUpload(input) {
            if (!input.files || !input.files[0]) return;

            const formData = new FormData();
            formData.append('file', input.files[0]);

            try {
                const response = await fetch('api/uploads.php?type=signatures', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    alert('Signature uploaded successfully!');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function deleteLogo() {
            if (!confirm('Hapus logo?')) return;
            try {
                const response = await fetch('api/settings.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ logo_file: '' })
                });
                const result = await response.json();
                if (result.success) {
                    const preview = document.getElementById('logoPreview');
                    const placeholder = document.getElementById('logoPlaceholder');
                    preview.src = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
                    if (placeholder) placeholder.style.display = '';
                } else {
                    alert('Gagal menghapus logo: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function deleteSignature() {
            if (!confirm('Hapus tanda tangan?')) return;
            try {
                const response = await fetch('api/settings.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ signature_file: '' })
                });
                const result = await response.json();
                if (result.success) {
                    document.getElementById('signaturePreview').innerHTML = '<div class="text-on-surface-variant opacity-40"><span class="material-symbols-outlined text-3xl">draw</span></div>';
                } else {
                    alert('Gagal menghapus tanda tangan: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
    </script>
</body>
</html>
