<?php
session_start();
require_once 'api/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi';
    } else {
        $data = [
            'action' => 'login',
            'email' => $email,
            'password' => $password
        ];
        
        $ch = curl_init(APP_URL . '/api/auth.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_COOKIEJAR, sys_get_temp_dir() . '/cookie.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, sys_get_temp_dir() . '/cookie.txt');
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($result && $result['success']) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error = isset($result['message']) ? $result['message'] : 'Login gagal';
        }
    }
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html class="h-full" lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Nexus Procurement - Login</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-tertiary-container": "#00a472",
                        "tertiary-fixed": "#6ffbbe",
                        "secondary-fixed": "#d8e2ff",
                        "surface-container-low": "#f5f3f4",
                        "inverse-on-surface": "#f3f0f2",
                        "surface-bright": "#fbf8fa",
                        "primary-fixed": "#d8e3fb",
                        "surface-variant": "#e4e2e3",
                        "background": "#fbf8fa",
                        "on-tertiary-fixed-variant": "#005236",
                        "on-error-container": "#93000a",
                        "outline": "#75777d",
                        "inverse-surface": "#303032",
                        "tertiary-fixed-dim": "#4edea3",
                        "on-primary": "#ffffff",
                        "primary-fixed-dim": "#bcc7de",
                        "on-background": "#1b1b1d",
                        "on-tertiary": "#ffffff",
                        "surface-container-lowest": "#ffffff",
                        "secondary-fixed-dim": "#adc6ff",
                        "outline-variant": "#c5c6cd",
                        "surface-dim": "#dcd9db",
                        "on-secondary-fixed": "#001a42",
                        "error": "#ba1a1a",
                        "on-secondary-container": "#fefcff",
                        "on-primary-fixed-variant": "#3c475a",
                        "tertiary-container": "#00301e",
                        "surface-container-high": "#eae7e9",
                        "surface-container": "#f0edef",
                        "primary-container": "#1e293b",
                        "on-primary-container": "#8590a6",
                        "tertiary": "#00190e",
                        "secondary-container": "#2170e4",
                        "secondary": "#0058be",
                        "on-secondary": "#ffffff",
                        "on-surface-variant": "#45474c",
                        "surface-container-highest": "#e4e2e3",
                        "primary": "#091426",
                        "on-primary-fixed": "#111c2d",
                        "on-tertiary-fixed": "#002113",
                        "on-surface": "#1b1b1d",
                        "on-error": "#ffffff",
                        "error-container": "#ffdad6",
                        "surface-tint": "#545f73",
                        "on-secondary-fixed-variant": "#004395",
                        "surface": "#fbf8fa",
                        "inverse-primary": "#bcc7de"
                    },
                    borderRadius: {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    spacing: {
                        "sm": "8px",
                        "xs": "4px",
                        "xl": "32px",
                        "unit": "4px",
                        "md": "16px",
                        "lg": "24px",
                        "gutter": "16px",
                        "container-margin": "24px",
                        "sidebar-width": "260px"
                    },
                    fontFamily: {
                        "label-sm": ["Inter"],
                        "body-sm": ["Inter"],
                        "data-tabular": ["Inter"],
                        "headline-sm": ["Inter"],
                        "body-md": ["Inter"],
                        "headline-md": ["Inter"],
                        "display-lg": ["Inter"],
                        "body-lg": ["Inter"],
                        "label-md": ["Inter"]
                    },
                    fontSize: {
                        "label-sm": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}],
                        "body-sm": ["12px", {"lineHeight": "16px", "fontWeight": "400"}],
                        "data-tabular": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                        "headline-sm": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
                        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "headline-md": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                        "display-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "label-md": ["14px", {"lineHeight": "20px", "fontWeight": "600"}]
                    }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body { font-family: 'Inter', sans-serif; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .animate-subtle-drift {
            animation: drift 60s linear infinite;
        }
        @keyframes drift {
            from { background-position: 0% 0%; }
            to { background-position: 100% 100%; }
        }
    </style>
</head>
<body class="h-full bg-surface text-on-surface flex flex-col overflow-hidden">
    <!-- Background Layer -->
    <div class="fixed inset-0 z-0 overflow-hidden">
        <div class="absolute inset-0 opacity-10 animate-subtle-drift" style="background-image: radial-gradient(#091426 1px, transparent 1px); background-size: 32px 32px;">
        </div>
        <div class="absolute inset-0 bg-gradient-to-br from-surface via-surface-container-lowest to-primary-fixed-dim/20"></div>
        <div class="absolute right-0 bottom-0 w-[60vw] h-[614px] opacity-30 blur-[120px] bg-secondary-container rounded-full transform translate-x-1/2 translate-y-1/2"></div>
    </div>

    <!-- Main Content Canvas -->
    <main class="relative z-10 flex-grow flex items-center justify-center p-gutter">
        <div class="w-full max-w-[440px] flex flex-col items-center">
            <!-- Branding Header -->
            <div class="mb-xl text-center">
                <div class="flex items-center justify-center gap-sm mb-xs">
                    <div class="w-10 h-10 bg-primary flex items-center justify-center rounded-lg shadow-lg shadow-primary/20">
                        <span class="material-symbols-outlined text-on-primary" style="font-variation-settings: 'FILL' 1;">hub</span>
                    </div>
                    <h1 class="font-headline-md text-headline-md text-primary tracking-tight">ProcureCorp</h1>
                </div>
                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Enterprise Procurement</p>
            </div>

            <!-- Login Card -->
            <div class="glass-panel w-full p-xl rounded-xl border border-outline-variant/30 shadow-[0px_4px_24px_rgba(9,20,38,0.06)]">
                <div class="mb-lg">
                    <h2 class="font-headline-sm text-headline-sm text-on-surface">Selamat Datang</h2>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-xs">Masukkan kredensial Anda untuk mengakses Nexus.</p>
                </div>

                <?php if ($error): ?>
                <div class="mb-lg p-md bg-error-container/10 border border-error/20 rounded-lg text-error font-body-sm">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <form class="space-y-md" method="POST" action="">
                    <!-- Email Input -->
                    <div class="space-y-xs">
                        <label class="font-label-sm text-label-sm text-on-surface-variant" for="email">Alamat Email</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-md flex items-center pointer-events-none text-outline group-focus-within:text-secondary transition-colors">
                                <span class="material-symbols-outlined text-[20px]">mail</span>
                            </div>
                            <input class="w-full pl-11 pr-md py-3 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all placeholder:text-outline/50" id="email" name="email" placeholder="name@company.com" type="email" required>
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-xs">
                        <div class="flex justify-between items-center">
                            <label class="font-label-sm text-label-sm text-on-surface-variant" for="password">Password</label>
                            <a class="font-label-sm text-label-sm text-secondary hover:underline transition-all" href="#">Lupa Password?</a>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-md flex items-center pointer-events-none text-outline group-focus-within:text-secondary transition-colors">
                                <span class="material-symbols-outlined text-[20px]">lock</span>
                            </div>
                            <input class="w-full pl-11 pr-11 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all placeholder:text-outline/50" id="password" name="password" placeholder="••••••••" type="password" required>
                            <button class="absolute inset-y-0 right-0 pr-md flex items-center text-outline hover:text-on-surface transition-colors" onclick="togglePassword()" type="button">
                                <span class="material-symbols-outlined text-[20px]" id="password-toggle-icon">visibility</span>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center space-x-sm">
                        <input class="w-4 h-4 text-secondary border-outline-variant rounded focus:ring-secondary focus:ring-offset-0 transition-all cursor-pointer" id="remember" name="remember" type="checkbox">
                        <label class="font-body-sm text-body-sm text-on-surface-variant select-none cursor-pointer" for="remember">Ingat perangkat ini selama 30 hari</label>
                    </div>

                    <!-- Action Button -->
                    <button class="w-full bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md py-lg rounded-lg shadow-lg shadow-primary/10 transition-all active:scale-[0.98] flex items-center justify-center gap-sm" type="submit">
                        Masuk
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </button>
                </form>

                <div class="mt-xl pt-lg border-t border-outline-variant/30 text-center">
                    <p class="font-body-sm text-body-sm text-on-surface-variant">
                        Belum memiliki akun? <a class="text-secondary font-label-sm hover:underline" href="#">Hubungi Administrator</a>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('password-toggle-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerText = 'visibility_off';
            } else {
                input.type = 'password';
                icon.innerText = 'visibility';
            }
        }

        document.addEventListener('mousemove', (e) => {
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            const card = document.querySelector('.glass-panel');
            const moveX = (x - 0.5) * 10;
            const moveY = (y - 0.5) * 10;
            card.style.transform = `translate(${moveX}px, ${moveY}px)`;
        });
    </script>
</body>
</html>
