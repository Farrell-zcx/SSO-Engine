<?php
/**
 * @var string $message
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .error-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 400px; text-align: center; }
        h1 { color: #e53e3e; font-size: 1.5rem; margin-top: 0; }
        p { color: #4a5568; margin-bottom: 1.5rem; }
        .btn { display: inline-block; padding: 0.5rem 1rem; background: #3182ce; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="error-card">
        <h1>Akses Ditolak</h1>
        <p><?= esc($message) ?></p>
        <a href="<?= site_url('logout-web') ?>" class="btn">Kembali</a>
    </div>
</body>
</html>
