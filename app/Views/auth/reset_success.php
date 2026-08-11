<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Sukses - SSO Engine</title>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="<?= base_url('css/liquidglass.css') ?>" rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-background": "#191c1e", "inverse-primary": "#bec6e0", "surface-variant": "#e0e3e5",
                        "primary-fixed-dim": "#bec6e0", "inverse-on-surface": "#eff1f3", "inverse-surface": "#2d3133",
                        "surface-container": "#eceef0", "surface-container-lowest": "#ffffff", "surface-container-low": "#f2f4f6",
                        "primary": "#000000", "surface-dim": "#d8dadc", "tertiary-fixed-dim": "#b7c8e1",
                        "on-secondary": "#ffffff", "surface-container-highest": "#e0e3e5", "on-surface-variant": "#45464d",
                        "primary-fixed": "#dae2fd", "secondary": "#0058be", "background": "#f7f9fb",
                        "primary-container": "#131b2e", "error-container": "#ffdad6", "surface": "#f7f9fb",
                        "on-primary-fixed": "#131b2e", "on-error-container": "#93000a", "outline-variant": "#c6c6cd",
                        "on-secondary-fixed": "#001a42", "on-secondary-fixed-variant": "#004395", "on-primary-container": "#7c839b",
                        "tertiary-fixed": "#d3e4fe", "surface-container-high": "#e6e8ea", "on-error": "#ffffff",
                        "tertiary-container": "#0b1c30", "on-secondary-container": "#fefcff", "on-surface": "#191c1e",
                        "error": "#ba1a1a", "on-tertiary-fixed": "#0b1c30", "on-tertiary": "#ffffff",
                        "on-tertiary-container": "#75859d", "surface-bright": "#f7f9fb", "tertiary": "#000000",
                        "on-primary": "#ffffff", "on-primary-fixed-variant": "#3f465c", "secondary-fixed-dim": "#adc6ff",
                        "on-tertiary-fixed-variant": "#38485d", "outline": "#76777d", "secondary-container": "#2170e4",
                        "secondary-fixed": "#d8e2ff", "surface-tint": "#565e74"
                    },
                    "borderRadius": { "DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem" },
                    "spacing": { "base": "4px", "xl": "32px", "margin-mobile": "16px", "xs": "8px", "2xl": "48px", "sm": "12px", "lg": "24px", "md": "16px", "gutter": "24px", "margin-desktop": "32px" }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Hanken Grotesk', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .login-card-shadow { box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); }
        .glass-effect { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(8px); }
    </style>
</head>

<body class="liquid-bg text-on-surface min-h-screen flex flex-col">
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[-10%] left-[-5%] w-[40%] h-[40%] rounded-full bg-emerald-500 opacity-[0.03] blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-5%] w-[40%] h-[40%] rounded-full bg-emerald-500 opacity-[0.03] blur-[120px]"></div>
    </div>
    
    <main class="relative z-10 flex-grow flex flex-col items-center justify-center px-margin-mobile md:px-margin-desktop py-xl">
        <div class="w-full max-w-[440px]">
            <div class="glass-panel rounded-xl p-xl login-card-shadow text-center">
                <span class="material-symbols-outlined text-[64px] text-emerald-500 mb-4">check_circle</span>
                <h1 class="font-headline-xl text-[24px] text-primary tracking-tight mb-2">Password Berhasil Diubah!</h1>
                <p class="font-body-md text-body-md text-on-surface-variant mb-6">
                    Anda sudah bisa menggunakan password baru untuk masuk ke semua aplikasi yang terhubung.
                </p>
                <div class="p-4 bg-emerald-50 rounded-lg border border-emerald-100 text-emerald-800 text-sm mb-6 inline-block w-full">
                    Silakan tutup halaman ini dan kembali ke aplikasi Anda untuk login.
                </div>
            </div>
        </div>
    </main>
    <footer class="relative z-10 py-lg px-margin-desktop text-center">
        <p class="font-label-sm text-label-sm text-outline tracking-wider">
            © <?= date('Y') ?> SSO Engine.
        </p>
    </footer>
</body>
</html>
