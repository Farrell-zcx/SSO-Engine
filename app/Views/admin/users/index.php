<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Kelola Pengguna - SSO Engine</title>
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
        .glass-input { background: rgba(255, 255, 255, 0.5); border: 1px solid rgba(0, 0, 0, 0.1); backdrop-filter: blur(4px); }
        .glass-input:focus { background: rgba(255, 255, 255, 0.8); border-color: #000; box-shadow: 0 0 0 1px #000; }
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

    <main class="relative z-10 flex-grow container mx-auto px-4 py-8 max-w-6xl">
        <div class="mb-8">
            <h2 class="text-2xl font-bold mb-2">Kelola Pengguna SSO Engine</h2>
            <p class="text-on-surface-variant text-sm">Pusat manajemen akun dan akses untuk seluruh aplikasi yang terhubung.</p>
        </div>
        
        <!-- Alerts -->
        <?php if (session()->getFlashdata('message')): ?>
            <div class="mb-6 p-4 bg-success-container text-on-success-container border border-green-200 rounded-lg flex items-start gap-3 shadow-sm animate-fade-in">
                <span class="material-symbols-outlined text-[20px] flex-shrink-0">check_circle</span>
                <div class="flex-grow text-sm font-medium">
                    <?= session()->getFlashdata('message') ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 p-4 bg-error-container text-on-error-container border border-error/10 rounded-lg flex items-start gap-3 shadow-sm animate-fade-in">
                <span class="material-symbols-outlined text-[20px] text-error flex-shrink-0">error</span>
                <div class="flex-grow text-sm font-medium">
                    <?= session()->getFlashdata('error') ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Tambah Pengguna Baru -->
            <div class="lg:col-span-1">
                <div class="glass-panel rounded-xl p-6 relative group overflow-hidden">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-primary/5 rounded-full blur-2xl group-hover:bg-primary/10 transition-all duration-500"></div>
                    
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-lg bg-surface border border-outline-variant/30 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary">person_add</span>
                        </div>
                        <h3 class="font-bold text-lg text-on-surface">Tambah Pengguna</h3>
                    </div>
                    
                    <form action="<?= site_url('admin/users') ?>" method="POST" class="space-y-4">
                        <?= csrf_field() ?>
                        
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Email SSO</label>
                            <div class="relative group-input">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[18px]">mail</span>
                                </div>
                                <input type="email" name="email" placeholder="email@perusahaan.com" required 
                                    class="w-full h-[44px] pl-10 pr-4 glass-input rounded-lg text-sm focus:outline-none transition-all outline-none" autocomplete="off" />
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Username</label>
                            <div class="relative group-input">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[18px]">badge</span>
                                </div>
                                <input type="text" name="username" placeholder="Username" required 
                                    class="w-full h-[44px] pl-10 pr-4 glass-input rounded-lg text-sm focus:outline-none transition-all outline-none" autocomplete="off" />
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full h-[44px] mt-2 bg-primary text-white font-medium text-sm rounded-lg hover:bg-[#333] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-md">
                            <span class="material-symbols-outlined text-[18px]">add</span>
                            Tambahkan
                        </button>
                    </form>
                </div>
            </div>

            <!-- Daftar Pengguna -->
            <div class="lg:col-span-2">
                <div class="glass-panel rounded-xl overflow-hidden flex flex-col h-full">
                    <div class="p-6 border-b border-outline-variant/20 flex justify-between items-center bg-white/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-surface border border-outline-variant/30 flex items-center justify-center">
                                <span class="material-symbols-outlined text-secondary">group</span>
                            </div>
                            <h3 class="font-bold text-lg text-on-surface">Daftar Pengguna</h3>
                        </div>
                        <span class="bg-primary/10 text-primary text-xs font-bold px-2 py-1 rounded-full border border-primary/20"><?= count($users) ?> Total</span>
                    </div>
                    
                    <div class="flex-1 overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-outline-variant/30 text-on-surface-variant text-xs uppercase tracking-wider bg-surface/50">
                                    <th class="p-4 font-semibold">User Info</th>
                                    <th class="p-4 font-semibold">Status</th>
                                    <th class="p-4 font-semibold">Tanggal Dibuat</th>
                                    <th class="p-4 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr class="border-b border-outline-variant/10 hover:bg-white/40 transition-colors">
                                        <td class="p-4">
                                            <div class="font-medium text-on-surface"><?= esc($user['email']) ?></div>
                                            <div class="text-xs text-on-surface-variant mt-0.5">@<?= esc($user['username']) ?></div>
                                        </td>
                                        <td class="p-4">
                                            <?php if (empty($user['password_hash'])): ?>
                                                <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 text-[11px] font-bold px-2 py-0.5 rounded-full border border-yellow-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span>
                                                    Belum Aktif
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 text-[11px] font-bold px-2 py-0.5 rounded-full border border-green-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                                    Aktif
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 text-sm text-on-surface-variant">
                                            <?= date('d M Y', strtotime($user['created_at'])) ?>
                                        </td>
                                        <td class="p-4 text-right flex justify-end gap-2">
                                            <a href="<?= site_url('admin/users/' . $user['id'] . '/access') ?>" 
                                               class="inline-flex items-center justify-center gap-1.5 bg-white border border-outline-variant/40 text-secondary font-medium text-xs px-3 py-1.5 rounded-md hover:bg-secondary hover:text-white hover:border-secondary transition-colors shadow-sm">
                                                <span class="material-symbols-outlined text-[16px]">manage_accounts</span>
                                                Kelola Akses
                                            </a>
                                            <form action="<?= site_url('admin/users/' . $user['id'] . '/delete') ?>" method="POST" class="inline-block delete-form">
                                                <?= csrf_field() ?>
                                                <button type="button" onclick="confirmDelete(this)" class="inline-flex items-center justify-center gap-1.5 bg-white border border-error/40 text-error font-medium text-xs px-3 py-1.5 rounded-md hover:bg-error hover:text-white hover:border-error transition-colors shadow-sm cursor-pointer">
                                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <?php if(empty($users)): ?>
                                    <tr>
                                        <td colspan="4" class="p-8 text-center text-on-surface-variant opacity-70">
                                            <span class="material-symbols-outlined text-4xl mb-2">inbox</span>
                                            <p class="text-sm">Belum ada pengguna terdaftar.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(button) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Yakin ingin menghapus akun sso ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ba1a1a',
                cancelButtonColor: '#45464d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        }
    </script>
</body>
</html>
