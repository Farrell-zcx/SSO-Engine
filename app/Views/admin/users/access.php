<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Kelola Akses - <?= esc($user['email']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <link href="<?= base_url('css/liquidglass.css') ?>" rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary": "#000000",
                        "secondary": "#0058be",
                        "surface": "#f7f9fb",
                        "on-surface": "#191c1e",
                        "on-surface-variant": "#45464d",
                        "outline-variant": "#c6c6cd",
                        "error-container": "#ffdad6",
                        "on-error-container": "#93000a",
                        "error": "#ba1a1a",
                        "success-container": "#c6f6d5",
                        "on-success-container": "#22543d"
                    },
                    "fontFamily": {
                        "headline-md": ["Hanken Grotesk"],
                        "body-md": ["Hanken Grotesk"],
                        "label-sm": ["Hanken Grotesk"]
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Hanken Grotesk', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .glass-panel { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.4); box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); }
    </style>
</head>

<body class="liquid-bg text-on-surface min-h-screen flex flex-col relative overflow-x-hidden">
    <!-- Subtle Decorative Background -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[-10%] left-[-5%] w-[40%] h-[40%] rounded-full bg-secondary opacity-[0.03] blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-5%] w-[40%] h-[40%] rounded-full bg-primary opacity-[0.03] blur-[120px]"></div>
    </div>
    
    <!-- Header -->
    <header class="relative z-20 glass-panel border-b sticky top-0 px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-[32px] text-primary">admin_panel_settings</span>
            <h1 class="text-xl font-bold tracking-tight text-primary">SSO <span class="text-secondary">Admin</span></h1>
        </div>
        <div class="flex items-center gap-4">
            <a href="<?= site_url('authorize-admin/logout') ?>" class="flex items-center gap-2 text-on-surface-variant hover:text-error transition-colors text-sm font-medium">
                <span class="material-symbols-outlined text-[20px]">logout</span>
                Logout
            </a>
        </div>
    </header>

    <main class="relative z-10 flex-grow container mx-auto px-4 py-8 max-w-4xl">
        <!-- Back Button -->
        <a href="<?= site_url('admin/users') ?>" class="inline-flex items-center gap-2 text-on-surface-variant hover:text-secondary transition-colors text-sm font-semibold mb-6">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            Kembali ke Daftar Pengguna
        </a>

        <!-- Alerts -->
        <?php if (session()->getFlashdata('message')): ?>
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition.duration.500ms class="mb-6 p-4 bg-success-container text-on-success-container border border-green-200 rounded-lg flex items-start gap-3 shadow-sm animate-fade-in">
                <span class="material-symbols-outlined text-[20px] flex-shrink-0">check_circle</span>
                <div class="flex-grow text-sm font-medium">
                    <?= session()->getFlashdata('message') ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- User Profile Card -->
        <div class="glass-panel rounded-xl p-8 mb-8 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-primary/5 rounded-full blur-2xl group-hover:bg-primary/10 transition-all duration-500"></div>
            
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-full bg-surface border border-outline-variant/30 flex items-center justify-center shadow-sm">
                    <span class="material-symbols-outlined text-[32px] text-secondary">person</span>
                </div>
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-on-surface mb-1">Kelola Akses Aplikasi</h2>
                    <p class="text-sm font-medium text-on-surface-variant flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">mail</span>
                        <?= esc($user['email']) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Apps List -->
        <div class="glass-panel rounded-xl overflow-hidden flex flex-col">
            <div class="p-6 border-b border-outline-variant/20 flex items-center gap-3 bg-white/30">
                <div class="w-10 h-10 rounded-lg bg-surface border border-outline-variant/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">apps</span>
                </div>
                <h3 class="font-bold text-lg text-on-surface">Daftar Aplikasi</h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($apps as $app): ?>
                        <?php $isGranted = in_array($app['id'], $grantedAppIds); ?>
                        <div class="bg-white/60 border border-outline-variant/30 rounded-xl p-5 hover:bg-white/80 transition-colors shadow-sm flex flex-col justify-between h-full group">
                            
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="font-bold text-on-surface text-lg mb-1 group-hover:text-secondary transition-colors"><?= esc($app['name']) ?></h4>
                                    <p class="text-xs font-mono text-on-surface-variant opacity-80"><?= esc($app['client_id']) ?></p>
                                </div>
                                <div class="shrink-0">
                                    <?php if ($isGranted): ?>
                                        <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 text-[11px] font-bold px-2 py-0.5 rounded-full border border-green-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                            Akses Diberikan
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-800 text-[11px] font-bold px-2 py-0.5 rounded-full border border-gray-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                            Belum Ada Akses
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-auto pt-4 border-t border-outline-variant/20">
                                <?php if ($isGranted): ?>
                                    <form action="<?= site_url("admin/users/{$user['id']}/access/{$app['id']}/revoke") ?>" method="POST" class="m-0 relative z-50">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="w-full h-[40px] bg-red-50 text-error border border-red-200 font-semibold text-sm rounded-lg hover:bg-error hover:text-white transition-all flex items-center justify-center gap-2 relative z-50 cursor-pointer">
                                            <span class="material-symbols-outlined text-[18px]">cancel</span>
                                            Cabut Akses
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?= site_url("admin/users/{$user['id']}/access") ?>" method="POST" class="m-0 relative z-50">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="application_id" value="<?= esc($app['id']) ?>">
                                        <button type="submit" class="w-full h-[40px] bg-secondary text-white font-semibold text-sm rounded-lg hover:bg-secondary/90 transition-all flex items-center justify-center gap-2 shadow-sm relative z-50 cursor-pointer">
                                            <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                            Berikan Akses
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </main>
</body>
</html>

