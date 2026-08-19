<?php
/**
 * @var string $client_name
 * @var array $user
 */

$initials = '';
$nameParts = explode(' ', trim($user['username'] ?? $user['email']));
if (!empty($nameParts[0])) {
    $initials .= mb_strtoupper(mb_substr($nameParts[0], 0, 1));
}
if (count($nameParts) > 1 && !empty($nameParts[1])) {
    $initials .= mb_strtoupper(mb_substr($nameParts[1], 0, 1));
}
if (empty($initials)) {
    $initials = 'U';
}
?>
<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Lanjutkan sebagai <?= esc($user['username']) ?> - SSO Engine</title>
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
                    "spacing": { "base": "4px", "xl": "32px", "margin-mobile": "16px", "xs": "8px", "2xl": "48px", "sm": "12px", "lg": "24px", "md": "16px", "gutter": "24px", "margin-desktop": "32px" },
                    "fontFamily": {
                        "headline-md": ["Hanken Grotesk"], "label-md": ["Hanken Grotesk"], "body-lg": ["Hanken Grotesk"],
                        "headline-xl": ["Hanken Grotesk"], "headline-lg-mobile": ["Hanken Grotesk"], "body-md": ["Hanken Grotesk"],
                        "label-sm": ["Hanken Grotesk"], "body-sm": ["Hanken Grotesk"], "headline-lg": ["Hanken Grotesk"]
                    },
                    "fontSize": {
                        "headline-md": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
                        "label-md": ["14px", { "lineHeight": "20px", "letterSpacing": "0.05em", "fontWeight": "500" }],
                        "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }],
                        "headline-xl": ["36px", { "lineHeight": "44px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "headline-lg-mobile": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
                        "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "label-sm": ["12px", { "lineHeight": "16px", "fontWeight": "600" }],
                        "body-sm": ["14px", { "lineHeight": "20px", "fontWeight": "400" }],
                        "headline-lg": ["24px", { "lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600" }]
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Hanken Grotesk', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .login-card-shadow { box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); }
    </style>
</head>

<body class="liquid-bg text-on-surface min-h-screen flex flex-col">
    <!-- Subtle Decorative Background -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[-10%] left-[-5%] w-[40%] h-[40%] rounded-full bg-secondary opacity-[0.03] blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-5%] w-[40%] h-[40%] rounded-full bg-primary opacity-[0.03] blur-[120px]"></div>
    </div>
    
    <main class="relative z-10 flex-grow flex flex-col items-center justify-center px-margin-mobile md:px-margin-desktop py-xl">
        <div class="w-full max-w-[440px]">
            <!-- Header -->
            <div class="text-center mb-xl flex flex-col items-center">
                <div class="flex items-center gap-3 mb-2">
                    <span class="material-symbols-outlined text-[48px] text-secondary">vpn_key</span>
                    <h1 class="font-headline-xl text-headline-xl text-primary tracking-tight">SSO <span class="text-secondary">Engine</span></h1>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant mt-xs">
                    Melanjutkan ke <strong><?= esc($client_name) ?></strong>
                </p>
            </div>

            <!-- Card -->
            <div class="glass-panel rounded-xl p-xl login-card-shadow">
                <!-- User Profile Card -->
                <div class="p-md bg-white/60 border border-black/5 rounded-xl flex items-center gap-md mb-lg shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-secondary to-blue-400 text-white font-bold text-lg flex items-center justify-center shadow-inner flex-shrink-0">
                        <?= esc($initials) ?>
                    </div>
                    <div class="flex-grow min-w-0">
                        <div class="flex items-center gap-2">
                            <h2 class="font-semibold text-on-surface truncate text-base"><?= esc($user['username']) ?></h2>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-100 text-emerald-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Sesi Aktif
                            </span>
                        </div>
                        <p class="text-xs text-on-surface-variant truncate mt-0.5"><?= esc($user['email']) ?></p>
                    </div>
                </div>

                <!-- Primary Action: Continue -->
                <form action="<?= site_url('authorize/continue') ?>" method="post" class="space-y-md">
                    <?= csrf_field() ?>
                    <button class="w-full h-[52px] bg-secondary text-on-secondary font-label-md text-label-md rounded-lg hover:bg-[#004ca5] active:scale-[0.98] transition-all flex items-center justify-center gap-xs shadow-md" type="submit">
                        <span>Lanjutkan sebagai <?= esc($user['username']) ?></span>
                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </button>
                </form>

                <!-- Divider -->
                <div class="relative my-lg flex items-center justify-center">
                    <div class="border-t border-black/10 w-full"></div>
                    <span class="bg-surface px-3 text-xs text-on-surface-variant uppercase tracking-wider relative z-10 font-medium">atau</span>
                </div>

                <!-- Secondary Action: Switch Account -->
                <a href="<?= site_url('authorize/switch') ?>" class="w-full h-[48px] bg-white/80 hover:bg-white text-on-surface border border-outline-variant/60 font-label-md text-label-md rounded-lg active:scale-[0.98] transition-all flex items-center justify-center gap-xs shadow-sm">
                    <span class="material-symbols-outlined text-[20px] text-secondary">switch_account</span>
                    <span>Gunakan Akun Lain / Ganti Akun</span>
                </a>
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
