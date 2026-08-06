<?php
/**
 * @var string $client_name
 * @var string|null $error
 */
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login - SSO Engine</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f4f4f4;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .card {
            background: #fff;
            padding: 32px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
            width: 320px;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 4px;
        }

        p.sub {
            color: #666;
            font-size: 13px;
            margin-top: 0;
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            margin-bottom: 4px;
        }

        input[type=email],
        input[type=password] {
            width: 100%;
            padding: 8px;
            margin-bottom: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 8px;
            border-radius: 4px;
            font-size: 13px;
            margin-bottom: 14px;
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>SSO Engine</h1>
        <p class="sub">Login untuk melanjutkan ke <strong><?= esc($client_name) ?></strong></p>

        <?php if (!empty($error)): ?>
            <div class="error"><?= esc($error) ?></div>
        <?php endif; ?>

        <form action="<?= site_url('login') ?>" method="post">
            <?= csrf_field() ?>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" required value="<?= old('email') ?>">

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>

</html>